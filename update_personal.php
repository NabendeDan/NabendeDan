<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$role = $_SESSION['role'];
$fullname = trim($_POST['fullname']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$district = trim($_POST['district']);

if (empty($fullname)) {
    echo json_encode(['success' => false, 'message' => 'Full name is required']);
    exit();
}

try {
    if ($role == 'Student') {
        $regno = $_SESSION['regno'];
        $stmt = $conn->prepare("UPDATE students SET fullname=?, phone=?, email=?, district=? WHERE regno=?");
        $stmt->bind_param("sssss", $fullname, $phone, $email, $district, $regno);
    } else {
        $staff_id = $_SESSION['staff_id'];
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $stmt = $conn->prepare("UPDATE staff SET fullname=?, phone=?, email=?, district=?, address=? WHERE staff_id=?");
        $stmt->bind_param("ssssss", $fullname, $phone, $email, $district, $address, $staff_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['fullname'] = $fullname;
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'fullname' => $fullname
        ]);
    } else {
        throw new Exception('Failed to update: ' . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>