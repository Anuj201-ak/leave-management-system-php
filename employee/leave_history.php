<?php
session_start();
include('../db/db_connection.php');

// Check if employee is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$employeeEmail = $_SESSION['email'];

// Fetch leave requests
$sql = "SELECT * FROM leave_requests WHERE employee_email = ? ORDER BY applied_on DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employeeEmail);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applied Leave Details</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include('includes/sidebar.php'); ?>
<div class="content">
    <?php include('includes/navbar.php'); ?>

    <div class="container mt-4">
        <h2 class="mb-4">Applied Leave Details</h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Applied At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td><?= htmlspecialchars($row['start_date']) ?></td>
                                <td><?= htmlspecialchars($row['end_date']) ?></td>
                                <td><?= htmlspecialchars($row['reason']) ?></td>
                                <td>
                                    <?php if ($row['proof_image']): ?>
                                        <a href=" <?= $row['proof_image'] ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $status = $row['status'];
                                        $badgeClass = match ($status) {
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            default => 'warning'
                                        };
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= $status ?></span>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($row['applied_on'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">You haven't applied for any leave yet.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
