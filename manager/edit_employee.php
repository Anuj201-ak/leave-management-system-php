<?php
session_start();
include('../db/db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['email'])) {
    header("Location: manage_employees.php");
    exit();
}

$email = $_GET['email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'employee'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    echo "Employee not found!";
    exit();
}

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE email = ?");
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();

    header("Location: manage_employees.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include('includes/manager_sidebar.php'); ?>
<div class="content">
    <?php include('includes/manager_navbar.php'); ?>

    <div class="container mt-5">
        <h3>Edit Employee</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Email (read-only)</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Employee Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($employee['name']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="manage_employees.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
