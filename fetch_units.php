<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    echo '<div class="alert alert-danger">Unauthorized</div>';
    exit();
}

$regno = isset($_POST['regno']) ? trim($_POST['regno']) : '';
$year = isset($_POST['year']) ? trim($_POST['year']) : '';
$semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';

if (empty($regno) || empty($semester) || empty($year)) {
    echo '<div class="alert alert-warning">Please select all fields</div>';
    exit();
}

// Get student's course
$stmt = $conn->prepare("SELECT course_id FROM students WHERE regno = ?");
$stmt->bind_param("s", $regno);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found</div>';
    $stmt->close();
    exit();
}

$course_id = $student['course_id'];
$stmt->close();

// Fetch course units for this course and semester (without unit_code)
$stmt = $conn->prepare("SELECT unit_id, unit_name FROM course_units WHERE course_id = ? AND semester = ? ORDER BY unit_name");

if (!$stmt) {
    echo '<div class="alert alert-danger">Database error: ' . $conn->error . '</div>';
    exit();
}

$stmt->bind_param("is", $course_id, $semester);
$stmt->execute();
$units = $stmt->get_result();

if ($units->num_rows > 0) {
    echo '<div class="mt-4">';
    echo '<h5 class="mb-3"><i class="fas fa-book me-2"></i>Course Units - Year ' . htmlspecialchars($year) . ', Semester ' . htmlspecialchars($semester) . '</h5>';
    
    while ($unit = $units->fetch_assoc()) {
        // Check if mark already exists
        $checkStmt = $conn->prepare("SELECT score FROM marks WHERE regno = ? AND unit_id = ? AND semester = ? AND year = ?");
        $checkStmt->bind_param("ssss", $regno, $unit['unit_id'], $semester, $year);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $existingMark = '';
        
        if ($checkResult->num_rows > 0) {
            $existingMark = $checkResult->fetch_assoc()['score'];
        }
        $checkStmt->close();
        
        echo '<div class="unit-card">';
        echo '<div class="unit-name">' . htmlspecialchars($unit['unit_name']) . '</div>';
        echo '<label class="form-label">Marks (0-100)</label>';
        echo '<input type="number" class="form-control mark-input" ';
        echo 'data-unit-id="' . htmlspecialchars($unit['unit_id']) . '" ';
        echo 'min="0" max="100" step="0.1" placeholder="Enter marks" ';
        echo 'value="' . htmlspecialchars($existingMark) . '">';
        echo '</div>';
    }
    
    echo '</div>';
} else {
    echo '<div class="alert alert-info mt-4">';
    echo '<i class="fas fa-info-circle me-2"></i>';
    echo 'No course units found for this course in Semester ' . htmlspecialchars($semester);
    echo '</div>';
}

$stmt->close();
$conn->close();
?>