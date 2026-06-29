<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] != 0) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['photo'];
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
    exit();
}

$targetDir = "uploads/profiles/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = time() . '_' . uniqid() . '.' . $fileExt;
$targetFile = $targetDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    $director_id = isset($_SESSION['director_id']) ? $_SESSION['director_id'] : '';
    
    if (!empty($director_id)) {
        $stmt = $conn->prepare("UPDATE directors SET photo = ? WHERE director_id = ?");
        $stmt->bind_param("si", $targetFile, $director_id);
        
        if ($stmt->execute()) {
            $_SESSION['photo'] = $targetFile;
            echo json_encode(['success' => true, 'photo' => $targetFile]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Director ID not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Upload failed']);
}

$conn->close();
?>