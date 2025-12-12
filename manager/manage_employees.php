<?php
session_start();
include('../db/db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}



// Get all employees
$employees = $conn->query("SELECT * FROM users WHERE role = 'employee' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include('includes/manager_sidebar.php'); ?>
<div class="content">
    <?php include('includes/manager_navbar.php'); ?>

    <div class="container mt-4">
        <h3 class="mb-3">Manage Employees</h3>
        <a href="register.php" class="btn btn-primary">➕ Add Employee</a>

        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($emp = $employees->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($emp['name']) ?></td>
                    <td><?= htmlspecialchars($emp['email']) ?></td>
                    <td><?= date('d M Y', strtotime($emp['created_at'])) ?></td>
                    <td>
                        <a href="edit_employee.php?email=<?= urlencode($emp['email']) ?>" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
