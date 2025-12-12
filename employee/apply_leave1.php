<?php
session_start();
include('../db/db_connection.php');

// Check if employee is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeEmail = $_SESSION['email'];
    $leaveType = $_POST['leave_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $reason = trim($_POST['reason']);
    $proofImage = '';

    // Validate dates
    if (strtotime($startDate) > strtotime($endDate)) {
        echo "<script>alert('Start date cannot be after end date.'); window.history.back();</script>";
        exit();
    }

    // Validate reason length
    if (strlen($reason) < 5) {
        echo "<script>alert('Reason must be at least 5 characters long.'); window.history.back();</script>";
        exit();
    }

    // Handle file upload
    if (!empty($_FILES['proof_image']['name'])) {
        $targetDir = __DIR__ . "/uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $proofImage = basename($_FILES['proof_image']['name']);
        $targetFile = $targetDir . $proofImage;

        if (!move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetFile)) {
            echo "<script>alert('Failed to upload proof image.'); window.history.back();</script>";
            exit();
        }
    }

    // Calculate the number of leave days
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    $leaveDays = $interval->days + 1; // Including both start and end date

    // Get current month (YYYYMM format)
    $currentMonth = date('Ym');

    // Fetch available leave balance
    $query = "SELECT casual_leave, sick_leave, earned_leave FROM leave_available_balance WHERE employee_email = ? AND leave_month = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $employeeEmail, $currentMonth);
    $stmt->execute();
    $balanceResult = $stmt->get_result();

    if ($balanceResult->num_rows > 0) {
        $balance = $balanceResult->fetch_assoc();

        // Check available balance
        $availableLeave = 0;
        if ($leaveType == 'Casual') {
            $availableLeave = $balance['casual_leave'];
        } elseif ($leaveType == 'Sick') {
            $availableLeave = $balance['sick_leave'];
        } elseif ($leaveType == 'Earned') {
            $availableLeave = $balance['earned_leave'];
        } else {
            echo "<script>alert('Invalid leave type selected.'); window.history.back();</script>";
            exit();
        }

        if ($availableLeave >= $leaveDays) {
            // Enough leave available -> Insert into leave_requests table
            $insertQuery = "INSERT INTO leave_requests (employee_email, leave_type, start_date, end_date, reason, proof_image, status, applied_on)
                            VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("ssssss", $employeeEmail, $leaveType, $startDate, $endDate, $reason, $proofImage);

            if ($insertStmt->execute()) {
                echo "<script>alert('Leave Applied Successfully!'); window.location.href='apply_leave.php';</script>";
            } else {
                echo "<script>alert('Error applying for leave.'); window.history.back();</script>";
            }
        } else {
            // Not enough leave available
            echo "<script>alert('Not enough available $leaveType leaves. You have only $availableLeave day(s) left.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Leave balance not available for this month. Contact HR.'); window.history.back();</script>";
    }
}
?>
