<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$type = isset($_POST['type']) ? $_POST['type'] : '';

if ($type === 'students') {
    $query = "SELECT regno, fullname, dob, phone, district, course_id, department_id, year_of_study FROM students ORDER BY fullname";
    $result = $conn->query($query);
    
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
}
elseif ($type === 'staff') {
    $query = "SELECT staff_id, fullname, dob, phone, district, department_id, username FROM staff ORDER BY fullname";
    $result = $conn->query($query);
    
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request type']);
}

$conn->close();
?>