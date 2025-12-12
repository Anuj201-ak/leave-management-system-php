<?php
session_start();
include('../db/db_connection.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require '../vendor/autoload.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}

$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selectedDate = isset($_GET['date']) ? $_GET['date'] : '';

// Fetch leave requests
if ($selectedDate != '') {
    $stmt = $conn->prepare("SELECT * FROM leave_requests WHERE DATE(applied_on) = ?");
    $stmt->bind_param("s", $selectedDate);
} else {
    $stmt = $conn->prepare("SELECT * FROM leave_requests WHERE DATE_FORMAT(applied_on, '%Y-%m') = ?");
    $stmt->bind_param("s", $selectedMonth);
}
$stmt->execute();
$result = $stmt->get_result();
$leaves = $result->fetch_all(MYSQLI_ASSOC);

// Export to Excel
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $sheet->setCellValue('A1', 'Employee Email');
    $sheet->setCellValue('B1', 'Leave Type');
    $sheet->setCellValue('C1', 'From Date');
    $sheet->setCellValue('D1', 'To Date');
    $sheet->setCellValue('E1', 'Status');
    $sheet->setCellValue('F1', 'Reason');
    $sheet->setCellValue('G1', 'Applied At');
    $sheet->setCellValue('H1', 'updated At');

    $row = 2;
    foreach ($leaves as $leave) {
        $sheet->setCellValue("A$row", $leave['employee_email']);
        $sheet->setCellValue("B$row", ucfirst($leave['leave_type']));
        $sheet->setCellValue("C$row", $leave['start_date']);
        $sheet->setCellValue("D$row", $leave['end_date']);
        $sheet->setCellValue("E$row", $leave['status']);
        $sheet->setCellValue("F$row", $leave['reason']);
        $sheet->setCellValue("G$row", $leave['applied_on']);
        $sheet->setCellValue("H$row", $leave['updated_at']);
        $row++;
    }

    $fileName = 'leave_report_';
    if ($selectedDate != '') {
        $fileName .= str_replace('-', '', $selectedDate);
    } else {
        $fileName .= str_replace('-', '', $selectedMonth);
    }
    $fileName .= '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Report</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include('includes/manager_sidebar.php'); ?>
<div class="content">
    <?php include('includes/manager_navbar.php'); ?>

    <div class="container mt-4">
        <h3 class="mb-3">Leave Report</h3>

        <form method="GET" class="row g-3 mb-3">
            <div class="col-auto">
                <label for="month" class="form-label">Filter by Month</label>
                <input type="month" id="month" name="month" value="<?= htmlspecialchars($selectedMonth) ?>" class="form-control">
            </div>
            <div class="col-auto">
                <label for="date" class="form-label">Filter by Date</label>
                <input type="date" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" class="form-control">
            </div>
            <div class="col-auto mt-4">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="?month=<?= urlencode($selectedMonth) ?><?php if ($selectedDate != '') { echo '&date=' . urlencode($selectedDate); } ?>&export=excel" class="btn btn-success">Export to Excel</a>
            </div>
        </form>

        <?php if (!empty($leaves)): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Employee Email</th>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Applied At</th>
                        <th>updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $leave): ?>
                        <tr>
                            <td><?= htmlspecialchars($leave['employee_email']) ?></td>
                            <td><?= ucfirst($leave['leave_type']) ?></td>
                            <td><?= $leave['start_date'] ?></td>
                            <td><?= $leave['end_date'] ?></td>
                            <td><?= $leave['status'] ?></td>
                            <td><?= htmlspecialchars($leave['reason']) ?></td>
                            <td><?= $leave['applied_on'] ?></td>
                            <td><?= $leave['updated_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No leave records found for the selected month.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
