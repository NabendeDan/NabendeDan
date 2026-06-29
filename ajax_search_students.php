<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    echo json_encode(['students' => [], 'error' => 'Unauthorized']);
    exit();
}

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode(['students' => []]);
    exit();
}

// Search students by regno or fullname
$stmt = $conn->prepare("SELECT regno, fullname FROM students WHERE regno LIKE ? OR fullname LIKE ? ORDER BY regno LIMIT 10");
$searchTerm = "%{$query}%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = [
            'regno' => $row['regno'],
            'fullname' => $row['fullname']
        ];
    }
}

echo json_encode(['students' => $students]);

$stmt->close();
$conn->close();
?>