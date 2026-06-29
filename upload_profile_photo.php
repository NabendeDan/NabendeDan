<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] != 0) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['photo'];
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
    exit();
}

$upload_dir = 'uploads/profiles/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$new_filename = time() . '_' . uniqid() . '.' . $ext;
$photo_path = $upload_dir . $new_filename;

if (move_uploaded_file($file['tmp_name'], $photo_path)) {
    $_SESSION['photo'] = $photo_path;
    
    // Update database based on role
    $role = $_SESSION['role'];
    
    if ($role == 'Student') {
        $regno = $_SESSION['regno'];
        $stmt = $conn->prepare("UPDATE students SET photo = ? WHERE regno = ?");
        $stmt->bind_param("ss", $photo_path, $regno);
    } else {
        $staff_id = $_SESSION['staff_id'];
        $stmt = $conn->prepare("UPDATE staff SET photo = ? WHERE staff_id = ?");
        $stmt->bind_param("ss", $photo_path, $staff_id);
    }
    
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'Photo updated', 'photo' => $photo_path]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload']);
}

$conn->close();
?>