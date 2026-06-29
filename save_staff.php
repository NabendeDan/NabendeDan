<?php
session_start();
include 'db.php';

// Check if user is authorized
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->begin_transaction();
        
        // Get and validate form data
        $staff_id = isset($_POST['staff_id']) ? trim($_POST['staff_id']) : '';
        $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
        $dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $district = isset($_POST['district']) ? trim($_POST['district']) : '';
        $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
        $position = isset($_POST['position']) ? trim($_POST['position']) : '';
        $hire_date = isset($_POST['hire_date']) ? trim($_POST['hire_date']) : '';
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        // Validate required fields
        if (empty($staff_id) || empty($fullname) || empty($username) || empty($password)) {
            throw new Exception('Staff ID, Full Name, Username, and Password are required');
        }
        
        // Check if staff_id already exists
        $check = $conn->prepare("SELECT staff_id FROM staff WHERE staff_id = ?");
        $check->bind_param("s", $staff_id);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows > 0) {
            throw new Exception('Staff ID already exists');
        }
        $check->close();
        
        // Check if username already exists
        $check = $conn->prepare("SELECT username FROM staff WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows > 0) {
            throw new Exception('Username already exists');
        }
        $check->close();
        
        // Handle photo upload
        $photo_path = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
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
        
        // Insert staff into database
        $sql = "INSERT INTO staff (staff_id, fullname, dob, phone, email, district, department_id, position, hire_date, address, username, password, photo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $stmt->bind_param("ssssssissssss", 
            $staff_id, 
            $fullname, 
            $dob, 
            $phone, 
            $email, 
            $district, 
            $department_id, 
            $position, 
            $hire_date, 
            $address, 
            $username, 
            $hashed_password, 
            $photo_path
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create staff: ' . $stmt->error);
        }
        
        $stmt->close();
        $conn->commit();
        
        // Success - redirect or show message
        echo "<script>
            alert('Staff account created successfully!');
            window.location.href ='staff.php';
        </script>";
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
        exit();
    }
    
    $conn->close();
} else {
    // If accessed directly without POST
    header("Location: create_staff.php");
    exit();
}
?>