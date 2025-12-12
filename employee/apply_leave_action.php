<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.html");
    exit();
}

$employeeId = $_SESSION['user_id'];
$employeeName = $_SESSION['name'];
$employeeEmail = $_SESSION['email'];

$conn = new mysqli("localhost", "root", "", "leave_management");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$leaveType = $_POST['leave_type'];
$startDate = $_POST['start_date'];
$endDate = $_POST['end_date'];
$reason = $_POST['reason'];
$status = "Pending";

// Handle file upload
$proofPath = "";
if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $proofPath = $uploadDir . time() . "_" . basename($_FILES['proof_image']['name']);
    move_uploaded_file($_FILES['proof_image']['tmp_name'], $proofPath);
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason, proof_image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $employeeId, $leaveType, $startDate, $endDate, $reason, $proofPath, $status);

if ($stmt->execute()) {
    // Send email to manager
    $to = "manager@example.com"; // Replace with actual manager's email or fetch dynamically
    $subject = "New Leave Request from $employeeName";
    $message = "
        <h3>New Leave Request</h3>
        <p><strong>Employee:</strong> $employeeName ($employeeEmail)</p>
        <p><strong>Type:</strong> $leaveType</p>
        <p><strong>From:</strong> $startDate</p>
        <p><strong>To:</strong> $endDate</p>
        <p><strong>Reason:</strong> $reason</p>
        <p><strong>Status:</strong> $status</p>
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@yourdomain.com";

    mail($to, $subject, $message, $headers);

    echo "<script>alert('Leave request submitted successfully!'); window.location='employee_dashboard.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
