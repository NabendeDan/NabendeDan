<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$regno = isset($_POST['regno']) ? trim($_POST['regno']) : '';

if (empty($regno)) {
    echo json_encode(['success' => false, 'message' => 'Registration number required']);
    exit();
}

// Fetch student information
$studentQuery = $conn->prepare("
    SELECT regno, fullname, dob, phone, district, course_id, department_id, year_of_study 
    FROM students 
    WHERE regno = ?
");
$studentQuery->bind_param("s", $regno);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();

if ($studentResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit();
}

$student = $studentResult->fetch_assoc();
$studentQuery->close();

// Fetch student results
$marksQuery = $conn->prepare("
    SELECT m.unit_id, m.semester, m.year, m.score, cu.unit_name
    FROM marks m
    LEFT JOIN course_units cu ON m.unit_id = cu.unit_id
    WHERE m.regno = ?
    ORDER BY m.year ASC, m.semester ASC, cu.unit_name ASC
");
$marksQuery->bind_param("s", $regno);
$marksQuery->execute();
$marksResult = $marksQuery->get_result();

$results = [];
while ($mark = $marksResult->fetch_assoc()) {
    $results[] = $mark;
}
$marksQuery->close();

// Fetch payment history
$paymentsQuery = $conn->prepare("
    SELECT payment_id, amount, purpose, semester, year, date_paid
    FROM payments
    WHERE regno = ?
    ORDER BY date_paid DESC
");
$paymentsQuery->bind_param("s", $regno);
$paymentsQuery->execute();
$paymentsResult = $paymentsQuery->get_result();

$payments = [];
while ($payment = $paymentsResult->fetch_assoc()) {
    $payments[] = $payment;
}
$paymentsQuery->close();

$conn->close();

echo json_encode([
    'success' => true,
    'student' => $student,
    'results' => $results,
    'payments' => $payments
]);
?>