<?php
session_start();

// Check if the user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$employeeEmail = $_SESSION['email'];

// Database connection
include('../db/db_connection.php');

// Get the current month in YYYYMM format
$currentMonth = date('Ym');

// Query to fetch leave balance for the current month
$query = "SELECT casual_leave, sick_leave, earned_leave FROM leave_available_balance WHERE employee_email = ? AND leave_month = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $employeeEmail, $currentMonth);
$stmt->execute();
$result = $stmt->get_result();

// Check if data exists for the current month
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $casualLeave = $row['casual_leave'];
    $sickLeave = $row['sick_leave'];
    $earnedLeave = $row['earned_leave'];
} else {
    // Default values if no data exists (This could happen for new employees)
    $casualLeave = 0;
    $sickLeave = 0;
    $earnedLeave = 0;
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance | Leave Management System</title>
    <link href="css/style.css" rel="stylesheet"> <!-- Include the common CSS file -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('includes/sidebar.php'); ?>

    <!-- Main Content -->
    <div class="content">
        <!-- Include Navbar -->
        <?php include('includes/navbar.php'); ?>

        <!-- Leave Balance Content -->
        <h2>Leave Balance for the Current Month</h2>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Casual Leave</h5>
                        <p class="card-text"><?= $casualLeave ?> Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Medical Leave</h5>
                        <p class="card-text"><?= $sickLeave ?> Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Earned Leave</h5>
                        <p class="card-text"><?= $earnedLeave ?> Days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
