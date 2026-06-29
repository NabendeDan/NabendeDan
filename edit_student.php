<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    header("Location: login.php");
    exit();
}

$regno = isset($_GET['regno']) ? trim($_GET['regno']) : '';

if (empty($regno)) {
    header("Location: director_dashboard.php");
    exit();
}

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE regno = ?");
$stmt->bind_param("s", $regno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: director_dashboard.php");
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_regno = trim($_POST['regno']);
    $fullname = trim($_POST['fullname']);
    $dob = trim($_POST['dob']);
    $phone = trim($_POST['phone']);
    $district = trim($_POST['district']);
    $year_of_study = intval($_POST['year_of_study']);
    $course_id = trim($_POST['course_id']);
    $department_id = trim($_POST['department_id']);
    $password = trim($_POST['password']);
    
    // Update query
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET regno=?, fullname=?, dob=?, phone=?, district=?, year_of_study=?, course_id=?, department_id=?, password=? WHERE regno=?");
        $stmt->bind_param("sssssissss", $new_regno, $fullname, $dob, $phone, $district, $year_of_study, $course_id, $department_id, $hashed, $regno);
    } else {
        $stmt = $conn->prepare("UPDATE students SET regno=?, fullname=?, dob=?, phone=?, district=?, year_of_study=?, course_id=?, department_id=? WHERE regno=?");
        $stmt->bind_param("sssssisss", $new_regno, $fullname, $dob, $phone, $district, $year_of_study, $course_id, $department_id, $regno);
    }
    
    if ($stmt->execute()) {
        // If regno changed, update related tables
        if ($new_regno !== $regno) {
            $conn->query("UPDATE payment_codes SET regno='$new_regno' WHERE regno='$regno'");
            $conn->query("UPDATE payments SET regno='$new_regno' WHERE regno='$regno'");
            $conn->query("UPDATE marks SET regno='$new_regno' WHERE regno='$regno'");
        }
        
        echo "<script>alert('Student updated successfully!'); window.location='director_dashboard.php';</script>";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Student - Dan4Christ Institute</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Student Information</h4>
    </div>
    <div class="card-body">
      <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Registration Number</label>
            <input type="text" name="regno" class="form-control" value="<?php echo htmlspecialchars($student['regno']); ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($student['fullname']); ?>" required>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($student['dob']); ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>">
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">District</label>
            <input type="text" name="district" class="form-control" value="<?php echo htmlspecialchars($student['district']); ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Year of Study</label>
            <select name="year_of_study" class="form-select" required>
              <option value="1" <?php echo $student['year_of_study'] == 1 ? 'selected' : ''; ?>>Year 1</option>
              <option value="2" <?php echo $student['year_of_study'] == 2 ? 'selected' : ''; ?>>Year 2</option>
              <option value="3" <?php echo $student['year_of_study'] == 3 ? 'selected' : ''; ?>>Year 3</option>
              <option value="4" <?php echo $student['year_of_study'] == 4 ? 'selected' : ''; ?>>Year 4</option>
            </select>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Course ID</label>
            <input type="text" name="course_id" class="form-control" value="<?php echo htmlspecialchars($student['course_id']); ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Department ID</label>
            <input type="text" name="department_id" class="form-control" value="<?php echo htmlspecialchars($student['department_id']); ?>">
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">New Password (leave blank to keep current)</label>
          <input type="password" name="password" class="form-control" placeholder="Enter new password or leave blank">
        </div>
        
        <div class="d-flex justify-content-end gap-2">
          <a href="director.php" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>