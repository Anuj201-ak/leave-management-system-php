<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light" style="margin-left: 240px;">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Leave Management System</span>
        <div class="d-flex ms-auto align-items-center">
            <div class="me-3 text-end">
                <strong><?= $_SESSION['name'] ?? 'Manager' ?></strong><br>
                <small><?= $_SESSION['email'] ?? '' ?></small>
            </div>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>
