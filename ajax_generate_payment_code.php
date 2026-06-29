<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$regno = isset($_POST['regno']) ? trim($_POST['regno']) : '';

if (empty($regno)) {
    echo json_encode(['success' => false, 'message' => 'Registration number is required']);
    exit();
}

// Verify student exists
$checkStudent = $conn->prepare("SELECT regno, fullname FROM students WHERE regno = ?");
$checkStudent->bind_param("s", $regno);
$checkStudent->execute();
$studentResult = $checkStudent->get_result();

if ($studentResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit();
}

$student = $studentResult->fetch_assoc();
$checkStudent->close();

// Generate payment code starting with 100
$lastCodeQuery = $conn->query("SELECT code FROM payment_codes WHERE code LIKE '100%' ORDER BY code_id DESC LIMIT 1");
$nextNumber = 1;

if ($lastCodeQuery && $lastCodeQuery->num_rows > 0) {
    $lastCode = $lastCodeQuery->fetch_assoc()['code'];
    $numberPart = substr($lastCode, 3);
    $nextNumber = intval($numberPart) + 1;
}

$paymentCode = '100' . $nextNumber;

// Insert payment code
$insertStmt = $conn->prepare("INSERT INTO payment_codes (regno, code) VALUES (?, ?)");

if (!$insertStmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$insertStmt->bind_param("ss", $regno, $paymentCode);

if ($insertStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Payment code generated successfully',
        'code' => $paymentCode,
        'regno' => $regno,
        'student_name' => $student['fullname']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save: ' . $insertStmt->error]);
}

$insertStmt->close();
$conn->close();
?>