<?php
session_start();

// Check if the user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.html");
    exit();
}

// Get employee details
$employeeEmail = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Leave | Leave Management System</title>
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
        <!-- Apply for Leave Form -->
        <h2>Apply for Leave</h2>
        <form action="apply_leave1.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="leave_type" class="form-label">Leave Type</label>
                <select name="leave_type" class="form-select" required>
                    <option value="Casual">Casual Leave</option>
                    <option value="Earned">Earned Leave</option>
                    <option value="Sick">Medical Leave</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" required>
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" required>
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason</label>
                <textarea class="form-control" name="reason" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="proof_image" class="form-label">Upload Proof Image (Optional)</label>
                <input type="file" class="form-control" name="proof_image" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Submit Leave Request</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
