<?php
session_start();
include('../db/db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_id'])) {
    $leave_id = $_POST['leave_id'];
    $new_status = $_POST['status'];

    // Fetch leave request details
    $leaveStmt = $conn->prepare("SELECT employee_email, leave_type, start_date, end_date FROM leave_requests WHERE id = ?");
    $leaveStmt->bind_param("i", $leave_id);
    $leaveStmt->execute();
    $leaveResult = $leaveStmt->get_result();
    $leaveData = $leaveResult->fetch_assoc();

    if (!$leaveData) {
        echo "<script>alert('Invalid leave request.'); window.history.back();</script>";
        exit();
    }

    $email = $leaveData['employee_email'];
    $leaveType = $leaveData['leave_type'];

    // Calculate leave days
    $start = new DateTime($leaveData['start_date']);
    $end = new DateTime($leaveData['end_date']);
    $leaveDays = $start->diff($end)->days + 1;

    $leaveMonth = date('Ym', strtotime($leaveData['start_date']));

    // If approving, check and update balance first
    if ($new_status === 'Approved') {
        // Normalize leave type to lowercase for comparison
        $leaveTypeLower = strtolower(trim($leaveType));

        $balanceStmt = $conn->prepare("SELECT casual_leave, sick_leave, earned_leave FROM leave_available_balance WHERE employee_email = ? AND leave_month = ?");
        $balanceStmt->bind_param("ss", $email, $leaveMonth);
        $balanceStmt->execute();
        $balanceResult = $balanceStmt->get_result();
        $balance = $balanceResult->fetch_assoc();

        if (!$balance) {
            echo "<script>alert('No leave balance record found.'); window.history.back();</script>";
            exit();
        }

        $leaveSufficient = false;
        $updateBalanceQuery = "";
        $updateBalanceStmt = null;

        if ($leaveTypeLower === 'casual' && $balance['casual_leave'] >= $leaveDays) {
            $newBalance = $balance['casual_leave'] - $leaveDays;
            $updateBalanceQuery = "UPDATE leave_available_balance SET casual_leave = ? WHERE employee_email = ? AND leave_month = ?";
            $updateBalanceStmt = $conn->prepare($updateBalanceQuery);
            $updateBalanceStmt->bind_param("iss", $newBalance, $email, $leaveMonth);
            $leaveSufficient = true;
        } elseif ($leaveTypeLower === 'sick' && $balance['sick_leave'] >= $leaveDays) {
            $newBalance = $balance['sick_leave'] - $leaveDays;
            $updateBalanceQuery = "UPDATE leave_available_balance SET sick_leave = ? WHERE employee_email = ? AND leave_month = ?";
            $updateBalanceStmt = $conn->prepare($updateBalanceQuery);
            $updateBalanceStmt->bind_param("iss", $newBalance, $email, $leaveMonth);
            $leaveSufficient = true;
        } elseif ($leaveTypeLower === 'earned' && $balance['earned_leave'] >= $leaveDays) {
            $newBalance = $balance['earned_leave'] - $leaveDays;
            $updateBalanceQuery = "UPDATE leave_available_balance SET earned_leave = ? WHERE employee_email = ? AND leave_month = ?";
            $updateBalanceStmt = $conn->prepare($updateBalanceQuery);
            $updateBalanceStmt->bind_param("iss", $newBalance, $email, $leaveMonth);
            $leaveSufficient = true;
        }

        if ($leaveSufficient) {
            if (!$updateBalanceStmt->execute()) {
                echo "<script>alert('Failed to update leave balance.'); window.history.back();</script>";
                exit();
            }

            // Now update leave status only if balance was sufficient
            $stmt = $conn->prepare("UPDATE leave_requests SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $new_status, $leave_id);
            if (!$stmt->execute()) {
                echo "<script>alert('Failed to update leave status.'); window.history.back();</script>";
                exit();
            }

            require 'send_mail.php';
            sendEmail($email, $new_status);
        } else {
            echo "<script>alert('Insufficient balance to approve leave.'); window.history.back();</script>";
            exit();
        }
    } else {
        // If status is Rejected
        $stmt = $conn->prepare("UPDATE leave_requests SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $new_status, $leave_id);
        $stmt->execute();

        require 'send_mail.php';
        sendEmail($email, $new_status);
    }
}
$sql = "SELECT lr.*, lab.casual_leave, lab.sick_leave, lab.earned_leave 
        FROM leave_requests lr
        LEFT JOIN leave_available_balance lab 
        ON lr.employee_email = lab.employee_email AND lab.leave_month = DATE_FORMAT(NOW(), '%Y%m')
        WHERE lr.status = 'Pending'
        ORDER BY lr.applied_on DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Leave Requests</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('includes/manager_sidebar.php'); ?>
<div class="content">
    <?php include('includes/manager_navbar.php'); ?>

    <div class="container mt-4">
        <h3 class="mb-4">Manage Leave Requests</h3>
        <a href="view_leave_balance.php" class="btn btn-primary">➕ View Leaves Balance</a>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Reason</th>
                        <th>Proof</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['employee_email'] ?></td>
                            <td><?= ucfirst($row['leave_type']) ?></td>
                            <td><?= $row['start_date'] ?></td>
                            <td><?= $row['end_date'] ?></td>
                            <td><?= $row['reason'] ?></td>
                            <td>
                                <?php if (!empty($row['proof_image'])): ?>
                                    <a href="../employee/<?= $row['proof_image'] ?>" target="_blank">View</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
                                    <button name="status" value="Approved" class="btn btn-success btn-sm">Approve</button>
                                    <button name="status" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No pending leave requests.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
