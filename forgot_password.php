<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $new_plain = "123456"; // default reset password
    $new_hashed = password_hash($new_plain, PASSWORD_DEFAULT);

    // Helper function to reset password in a given table
    function resetPassword($conn, $table, $username, $new_hashed) {
        $sql = "SELECT username FROM $table WHERE username=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("SQL prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $update = $conn->prepare("UPDATE $table SET password=? WHERE username=?");
            if (!$update) {
                die("SQL prepare failed: " . $conn->error);
            }
            $update->bind_param("ss", $new_hashed, $username);
            $update->execute();
            return true;
        }
        return false;
    }

    // Try each table
    if (resetPassword($conn, "directors", $username, $new_hashed)) {
        echo "<script>alert('Director password reset to 123456. Please login and change it.');window.location='login.html';</script>";
        exit();
    } elseif (resetPassword($conn, "staff", $username, $new_hashed)) {
        echo "<script>alert('Staff password reset to 123456. Please login and change it.');window.location='login.html';</script>";
        exit();
    } elseif (resetPassword($conn, "students", $username, $new_hashed)) {
        echo "<script>alert('Student password reset to 123456. Please login and change it.');window.location='login.html';</script>";
        exit();
    } else {
        echo "<script>alert('Username not found in any account!');window.location='forgot_password.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #74ebd5 0%, #9face6 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      border-radius: 12px;
      background-color: #fff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    h2 {
      color: #333;
    }
  </style>
</head>
<body>
  <div class="col-md-6">
    <form method="POST" class="card p-4 shadow">
      <h2 class="mb-3 text-center">Forgot Password</h2>
      <p class="text-muted text-center">Enter your username to reset your password.</p>
      <div class="mb-3">
        <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Reset Password</button>
      <a href="login.html" class="btn btn-link w-100 mt-2">Back to Login</a>
    </form>
  </div>
</body>
</html>
