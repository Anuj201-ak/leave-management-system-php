<?php
session_start();

// Check if the user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.html");
    exit();
}

// Get employee details
$employeeName = $_SESSION['name'];
$employeeEmail = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | Leave Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <?php include('includes/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <?php include('includes/navbar.php'); ?>

        <!-- Dashboard Content -->
        <h2>Employee Dashboard</h2>
        <p>Welcome to your Employee Dashboard. From here you can apply for leave, view your leave history, and check your leave balance.</p>

        <!-- Add additional content for the employee panel here -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
