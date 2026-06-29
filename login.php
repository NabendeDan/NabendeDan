<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Helper function to check login in a given table
    function checkLogin($conn, $table, $username, $password, $role) {
        $sql = "SELECT * FROM $table WHERE username=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("SQL prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Verify hashed password
            if (password_verify($password, $row['password'])) {
                // Store ALL necessary session data
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $role;
                $_SESSION['fullname'] = isset($row['fullname']) ? $row['fullname'] : $row['username'];
                $_SESSION['photo'] = isset($row['photo']) ? $row['photo'] : '';
                
                // Store role-specific IDs
                if ($role == 'Student') {
                    $_SESSION['regno'] = $row['regno'];
                    $_SESSION['student_id'] = isset($row['id']) ? $row['id'] : $row['regno'];
                } elseif ($role == 'Staff') {
                    $_SESSION['staff_id'] = $row['staff_id'];
                } elseif ($role == 'Director') {
                    $_SESSION['director_id'] = $row['director_id'];
                }
                
                return true;
            }
        }
        return false;
    }

    // Try each role table
    if (checkLogin($conn, "directors", $username, $password, "Director")) {
        echo "Director";
        exit();
    } elseif (checkLogin($conn, "staff", $username, $password, "Staff")) {
        echo "Staff";
        exit();
    } elseif (checkLogin($conn, "students", $username, $password, "Student")) {
        echo "Student";
        exit();
    } else {
        echo "Invalid";
    }
}
?>