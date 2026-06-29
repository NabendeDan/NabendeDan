<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
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

$dept_id = isset($_POST['dept_id']) ? intval($_POST['dept_id']) : 0;

if ($dept_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid department ID'
    ]);
    exit();
}

try {
    // Check if department is being used
    $check = $conn->prepare("SELECT COUNT(*) as count FROM staff WHERE department_id = ?");
    $check->bind_param("i", $dept_id);
    $check->execute();
    $result = $check->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete department. It is being used by ' . $row['count'] . ' staff member(s).'
        ]);
        exit();
    }
    $check->close();
    
    // Delete department
    $stmt = $conn->prepare("DELETE FROM departments WHERE dept_id = ?");
    $stmt->bind_param("i", $dept_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Department deleted successfully!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Department not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete department: ' . $stmt->error
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