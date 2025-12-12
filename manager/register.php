<?php
// manager/register.php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../db/db_connection.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $role = $conn->real_escape_string(trim($_POST['role']));

    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";
        if ($conn->query($sql) === TRUE) {
            $success_message = 'Registration successful. You can now login.';
        } else {
            $error_message = 'Error: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register | Leave Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .main-content {
            margin-left: 240px; /* width of sidebar */
            padding: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/manager_sidebar.php'; ?>
    <?php include 'includes/manager_navbar.php'; ?>

    <div class="main-content">
        <h2>Register</h2>
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST" class="mt-4" style="max-width: 500px;">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Register As</label>
                <select name="role" class="form-select" required>
                    <option value="employee" <?php if (($_POST['role'] ?? '') === 'employee') echo 'selected'; ?>>Employee</option>
                    <option value="manager" <?php if (($_POST['role'] ?? '') === 'manager') echo 'selected'; ?>>Manager</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</body>
</html>
