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

try {
    $conn->begin_transaction();
    
    // Delete from related tables first (to avoid foreign key errors)
    $conn->query("DELETE FROM payment_codes WHERE regno='$regno'");
    $conn->query("DELETE FROM payments WHERE regno='$regno'");
    $conn->query("DELETE FROM marks WHERE regno='$regno'");
    
    // Delete student
    $stmt = $conn->prepare("DELETE FROM students WHERE regno = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $regno);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete student: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Student not found');
    }
    
    $stmt->close();
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Student and all associated data deleted successfully!'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>