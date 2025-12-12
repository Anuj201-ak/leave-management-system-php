<?php
// Start session
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "lms");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form values
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

// Check if role is valid
if ($role !== 'employee' && $role !== 'manager') {
    die("Invalid role selected.");
}

// Query the database to check user credentials
$sql = "SELECT * FROM users WHERE email = ? AND role = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email']=$user['email'];
        // Redirect based on role
        if ($role == 'employee') {
            header("Location: employee/employee_dashboard.php");
        } else {
            header("Location: manager/manager_dashboard.php");
        }
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found or incorrect role.";
}

$stmt->close();
$conn->close();
?>
