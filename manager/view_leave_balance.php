<?php
session_start();
include('../db/db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

$search_email = isset($_GET['search_email']) ? trim($_GET['search_email']) : '';
$search_month = isset($_GET['search_month']) ? trim($_GET['search_month']) : '';

// Base Query
$sql = "SELECT * FROM leave_available_balance WHERE 1";

// Apply filters
if ($search_email != '') {
    $sql .= " AND employee_email LIKE '%" . $conn->real_escape_string($search_email) . "%'";
}

if ($search_month != '') {
    $search_month = str_replace('-', '', $search_month); // convert YYYY-MM to YYYYMM
    $sql .= " AND leave_month = '" . $conn->real_escape_string($search_month) . "'";
}

$sql .= " ORDER BY leave_month DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Leave Balance</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include('includes/manager_sidebar.php'); ?>
<div class="content">
<?php include('includes/manager_navbar.php'); ?>

<div class="container mt-4">
    <h3>Leave Balances</h3>

    <!-- Search Form -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search_email" class="form-control" placeholder="Search by Employee Email" value="<?= htmlspecialchars($search_email) ?>">
        </div>
        <div class="col-md-3">
            <input type="month" name="search_month" class="form-control" value="<?= htmlspecialchars(substr($search_month, 0, 4) . '-' . substr($search_month, 4, 2)) ?>">
        </div>
    
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="view_leave_balance.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- Table Data -->
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Employee Email</th>
                    <th>Month</th>
                    <th>Casual Leave</th>
                    <th>Medical Leave</th>
                    <th>Earned Leave</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['employee_email']) ?></td>
                        <td><?= substr($row['leave_month'], 0, 4) . '-' . substr($row['leave_month'], 4, 2) ?></td>
                        <td><?= $row['casual_leave'] ?></td>
                        <td><?= $row['sick_leave'] ?></td>
                        <td><?= $row['earned_leave'] ?></td>
                        <td>
                            <a href="edit_leave_balance.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info mt-3">No leave balances found.</div>
    <?php endif; ?>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
