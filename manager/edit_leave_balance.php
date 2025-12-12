<?php
session_start();
include('../db/db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

// Check if employee email and month are passed
if (!isset($_GET['id'])) {
    die("Invalid Request.");
}

$id = intval($_GET['id']);

// Fetch current balance
$stmt = $conn->prepare("SELECT * FROM leave_available_balance WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$balance = $result->fetch_assoc();

if (!$balance) {
    die("Leave balance record not found.");
}

// Update leave balance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $casual_leave = intval($_POST['casual_leave']);
    $sick_leave = intval($_POST['sick_leave']);
    $earned_leave = intval($_POST['earned_leave']);

    $update = $conn->prepare("UPDATE leave_available_balance SET casual_leave = ?, sick_leave = ?, earned_leave = ? WHERE id = ?");
    $update->bind_param("iiii", $casual_leave, $sick_leave, $earned_leave, $id);

    if ($update->execute()) {
        $_SESSION['success'] = "Leave balance updated successfully.";
        header("Location: view_leave_balance.php"); // Redirect to leave balance list page
        exit();
    } else {
        $error = "Failed to update leave balance.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Leave Balance</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include('includes/manager_sidebar.php'); ?>
<div class="content">
<?php include('includes/manager_navbar.php'); ?>

<div class="container mt-5">
    <h3>Edit Leave Balance</h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="p-4 shadow bg-light rounded">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($balance['employee_email']) ?>" disabled>
        </div>

        <div class="mb-3">
            <label>Month (YYYY-MM)</label>
            <input type="text" class="form-control" value="<?= substr($balance['leave_month'], 0, 4) . '-' . substr($balance['leave_month'], 4, 2) ?>" disabled>
        </div>

        <div class="mb-3">
            <label>Casual Leave</label>
            <input type="number" name="casual_leave" class="form-control" value="<?= $balance['casual_leave'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Sick Leave</label>
            <input type="number" name="sick_leave" class="form-control" value="<?= $balance['sick_leave'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Earned Leave</label>
            <input type="number" name="earned_leave" class="form-control" value="<?= $balance['earned_leave'] ?>" required>
        </div>

        <button type="submit" class="btn btn-success">Update Balance</button>
        <a href="view_leave_balance.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
