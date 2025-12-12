<?php
session_start();

// Check if the user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.html");
    exit();
}

// Get form data
$leave_type = $_POST['leave_type'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$reason = $_POST['reason'];
$employee_email = $_SESSION['email'];  // Get employee's email from session

// Handle file upload for proof image
$proof_image = null;
if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
    $upload_dir = "uploads/";  // Folder to store uploaded files
    $file_name = basename($_FILES['proof_image']['name']);
    $file_path = $upload_dir . $file_name;

    // Move uploaded file to server folder
    if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $file_path)) {
        $proof_image = $file_path;
    } else {
        echo "Error uploading the file.";
        exit();
    }
}

// Database connection
$conn = new mysqli("localhost", "root", "", "lms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute the insert query
$sql = "INSERT INTO leave_requests (employee_email, leave_type, start_date, end_date, reason, proof_image) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $employee_email, $leave_type, $start_date, $end_date, $reason, $proof_image);

if ($stmt->execute()) {
    header("Location: leave_history.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
