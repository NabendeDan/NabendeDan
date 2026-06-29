<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];

try {
    $role = $_SESSION['role'];
    
    // Get current hashed password
    if ($role == 'Student') {
        $regno = $_SESSION['regno'];
        $stmt = $conn->prepare("SELECT password FROM students WHERE regno = ?");
        $stmt->bind_param("s", $regno);
    } else {
        $staff_id = $_SESSION['staff_id'];
        $stmt = $conn->prepare("SELECT password FROM staff WHERE staff_id = ?");
        $stmt->bind_param("s", $staff_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result || !password_verify($current_password, $result['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit();
    }
    
    // Update password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    
    if ($role == 'Student') {
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE regno = ?");
        $stmt->bind_param("ss", $hashed, $regno);
    } else {
        $stmt = $conn->prepare("UPDATE staff SET password = ? WHERE staff_id = ?");
        $stmt->bind_param("ss", $hashed, $staff_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
    } else {
        throw new Exception('Failed to update password');
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>