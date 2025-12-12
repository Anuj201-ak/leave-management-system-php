<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Sidebar -->
<div class="sidebar bg-dark text-white p-3" style="width: 240px; height: 100vh; position: fixed;">
    <h4 class="text-center mb-4">Manager Panel</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-white" href="manager_dashboard.php">🏠 Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="Manage_employees.php">👥 Manage Employees</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="manage_leaves.php">📄 Manage Leave Requests</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="leave_balance_report.php">📊 Leave Balance Report</a>
        </li>
    </ul>
</div>
