<?php
session_start();
include('../db/db_connection.php');

// Check if logged in as manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit();
}

// Fetch data for dashboard
$totalEmployees = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'employee'")->fetch_assoc()['total'];
$pendingLeaves = $conn->query("SELECT COUNT(*) AS total FROM leave_requests WHERE status = 'Pending'")->fetch_assoc()['total'];
$approvedLeaves = $conn->query("SELECT COUNT(*) AS total FROM leave_requests WHERE status = 'Approved'")->fetch_assoc()['total'];
$rejectedLeaves = $conn->query("SELECT COUNT(*) AS total FROM leave_requests WHERE status = 'Rejected'")->fetch_assoc()['total'];
// Pie Chart Data
$statusCounts = [
    'Approved' => $approvedLeaves,
    'Pending' => $pendingLeaves,
    'Rejected' => $rejectedLeaves,
];

// Bar Chart Data (Monthly leave count for last 6 months)
$monthlyLeaves = [];
$months = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date("Y-m", strtotime("-$i months"));
    $label = date("M Y", strtotime("-$i months"));
    $months[] = $label;

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM leave_requests WHERE DATE_FORMAT(applied_on, '%Y-%m') = ?");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['total'];
    $monthlyLeaves[] = $count;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include('includes/manager_sidebar.php'); ?>
<div class="content">
    <?php include('includes/manager_navbar.php'); ?>

    <div class="container mt-4">
        <h2 class="mb-4">Manager Dashboard</h2>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Employees</h5>
                        <h3><?= $totalEmployees ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-warning shadow">
                    <div class="card-body">
                        <h5 class="card-title">Pending Leaves</h5>
                        <h3><?= $pendingLeaves ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-success shadow">
                    <div class="card-body">
                        <h5 class="card-title">Approved Leaves</h5>
                        <h3><?= $approvedLeaves ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-danger shadow">
                    <div class="card-body">
                        <h5 class="card-title">Rejected Leaves</h5>
                        <h3><?= $rejectedLeaves ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie Chart Section -->
  
        <div class="row mt-5">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h5 class="card-title text-center">Leave Status Distribution</h5>
                <canvas id="statusPieChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h5 class="card-title text-center">Monthly Leave Trends</h5>
                <canvas id="monthlyLeaveBarChart"></canvas>
            </div>
        </div>
    </div>
</div>

    </div>

</div>

<script>
    // Pie Chart Data
    const pieCtx = document.getElementById('statusPieChart').getContext('2d');
    const statusPieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                label: 'Leave Status',
                data: [<?= $statusCounts['Approved'] ?>, <?= $statusCounts['Pending'] ?>, <?= $statusCounts['Rejected'] ?>],
                backgroundColor: ['#198754', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Bar Chart Data
    const barCtx = document.getElementById('monthlyLeaveBarChart').getContext('2d');
    const monthlyLeaveBarChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'Leave Requests',
                data: <?= json_encode($monthlyLeaves) ?>,
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision:0 }
                }
            }
        }
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
