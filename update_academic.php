<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Staff' && $_SESSION['role'] != 'Director')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$staff_id = $_SESSION['staff_id'];
$position = trim($_POST['position']);
$qualifications = trim($_POST['qualifications']);

try {
    $stmt = $conn->prepare("UPDATE staff SET position=?, qualifications=? WHERE staff_id=?");
    $stmt->bind_param("sss", $position, $qualifications, $staff_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Academic info updated!']);
    } else {
        throw new Exception('Failed to update: ' . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>