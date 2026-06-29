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

try {
    $conn->begin_transaction();
    
    $regno = trim($_POST['regno']);
    $fullname = trim($_POST['fullname']);
    $dob = trim($_POST['dob']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $district = trim($_POST['district']);
    $course_id = intval($_POST['course_id']);
    $department_id = intval($_POST['department_id']);
    $year_of_study = intval($_POST['year_of_study']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate required fields
    if (empty($regno) || empty($fullname) || empty($username) || empty($password)) {
        throw new Exception('Registration Number, Full Name, Username, and Password are required');
    }
    
    if (empty($year_of_study) || $year_of_study < 1 || $year_of_study > 5) {
        throw new Exception('Valid year of study is required (1-5)');
    }
    
    // Check if regno already exists
    $check = $conn->prepare("SELECT regno FROM students WHERE regno = ?");
    $check->bind_param("s", $regno);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        throw new Exception('Registration number already exists');
    }
    $check->close();
    
    // Check if username already exists
    $check = $conn->prepare("SELECT username FROM students WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        throw new Exception('Username already exists');
    }
    $check->close();
    
    // Handle photo upload
    $photo_path = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed');
        }
        
        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File too large. Maximum size is 10MB');
        }
        
        $upload_dir = 'uploads/students/';
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
    
    // Insert student
    $sql = "INSERT INTO students (regno, fullname, dob, phone, email, district, course_id, department_id, year_of_study, username, password, photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("ssssssiissss", $regno, $fullname, $dob, $phone, $email, $district, $course_id, $department_id, $year_of_study, $username, $hashed_password, $photo_path);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create student: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Student account created successfully!'
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