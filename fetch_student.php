<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Unauthorized access</div>';
    exit();
}

$regno = isset($_GET['regno']) ? trim($_GET['regno']) : '';

if (empty($regno)) {
    echo '';
    exit();
}

// Use SELECT * to avoid column name issues
$stmt = $conn->prepare("SELECT * FROM students WHERE regno = ?");

if ($stmt === false) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Database error: ' . $conn->error . '</div>';
    exit();
}

$stmt->bind_param("s", $regno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    
    // Safely get values with isset checks
    $regno_val = isset($student['regno']) ? htmlspecialchars($student['regno']) : 'N/A';
    $fullname = isset($student['fullname']) ? htmlspecialchars($student['fullname']) : 'N/A';
    $phone = isset($student['phone']) ? htmlspecialchars($student['phone']) : 'N/A';
    $email = isset($student['email']) && !empty($student['email']) ? htmlspecialchars($student['email']) : 'N/A';
    $year_of_study = isset($student['year_of_study']) ? htmlspecialchars($student['year_of_study']) : 'N/A';
    $course_id = isset($student['course_id']) ? htmlspecialchars($student['course_id']) : 'N/A';
    $department_id = isset($student['department_id']) ? htmlspecialchars($student['department_id']) : 'N/A';
    $dob = isset($student['dob']) ? htmlspecialchars($student['dob']) : 'N/A';
    $district = isset($student['district']) ? htmlspecialchars($student['district']) : 'N/A';
    
    ?>
    <div class="student-details-card">
      <h5>
        <i class="fas fa-user-circle"></i>
        Student Information
      </h5>
      <div class="detail-row">
        <span class="detail-label">Registration No:</span>
        <span class="detail-value"><?php echo $regno_val; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Full Name:</span>
        <span class="detail-value"><?php echo $fullname; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Date of Birth:</span>
        <span class="detail-value"><?php echo $dob; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Phone:</span>
        <span class="detail-value"><?php echo $phone; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Email:</span>
        <span class="detail-value"><?php echo $email; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">District:</span>
        <span class="detail-value"><?php echo $district; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Course ID:</span>
        <span class="detail-value"><?php echo $course_id; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Department ID:</span>
        <span class="detail-value"><?php echo $department_id; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Year of Study:</span>
        <span class="detail-value">Year <?php echo $year_of_study; ?></span>
      </div>
    </div>
    <?php
} else {
    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Student not found</div>';
}

$stmt->close();
$conn->close();
?>