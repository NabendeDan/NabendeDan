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

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['action']) || $_POST['action'] != 'create_director') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit();
}

try {
    $conn->begin_transaction();
    
    // Get form data
    $fullname = trim($_POST['fullname']);
    $dob = isset($_POST['dob']) && !empty($_POST['dob']) ? $_POST['dob'] : null;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate required fields
    if (empty($fullname) || empty($username) || empty($password)) {
        throw new Exception('Full Name, Username, and Password are required');
    }
    
    // Check if username already exists
    $check = $conn->prepare("SELECT username FROM directors WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        throw new Exception('Username already exists');
    }
    $check->close();
    
    // Handle photo upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed');
        }
        
        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File too large. Maximum size is 5MB');
        }
        
        $upload_dir = 'uploads/staff/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $new_filename = time() . '_' . uniqid() . '.' . $ext;
        $photo_path = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
            throw new Exception('Failed to upload photo');
        }
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert director into directors table
    $sql = "INSERT INTO directors (fullname, dob, phone, username, password, photo) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("ssssss", 
        $fullname, 
        $dob, 
        $phone, 
        $username, 
        $hashed_password, 
        $photo_path
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create director: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Director account created successfully!'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>