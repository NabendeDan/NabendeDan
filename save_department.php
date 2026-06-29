<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

$dept_name = isset($_POST['dept_name']) ? trim($_POST['dept_name']) : '';

if (empty($dept_name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Department name is required'
    ]);
    exit();
}

try {
    // Check if department already exists
    $check = $conn->prepare("SELECT dept_id FROM departments WHERE dept_name = ?");
    $check->bind_param("s", $dept_name);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Department already exists'
        ]);
        exit();
    }
    $check->close();
    
    // Insert department
    $stmt = $conn->prepare("INSERT INTO departments (dept_name) VALUES (?)");
    $stmt->bind_param("s", $dept_name);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Department created successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create department: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>