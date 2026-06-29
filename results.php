<?php
session_start();
include 'db.php';

// Ensure only students can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Student') {
    header("Location: login.php");
    exit();
}

// Safely get regno from session
$regno = isset($_SESSION['regno']) ? $_SESSION['regno'] : null;

if ($regno) {
    // Fetch student details
    $student_sql = "SELECT fullname, course_id, year_of_study FROM students WHERE regno=?";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("s", $regno);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    $student = $student_result->fetch_assoc();

    // Fetch marks joined with course units
    $marks_sql = "SELECT cu.unit_name, m.score, m.semester, m.year
                  FROM marks m
                  JOIN course_units cu ON m.unit_id = cu.unit_id
                  WHERE m.regno=?";
    $marks_stmt = $conn->prepare($marks_sql);
    $marks_stmt->bind_param("s", $regno);
    $marks_stmt->execute();
    $marks_result = $marks_stmt->get_result();
} else {
    $student = null;
    $marks_result = null;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Results</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-success text-white">
      <h4>My Results</h4>
    </div>
    <div class="card-body">
      <?php if ($regno && $student): ?>
        <h5 class="mb-3">Student Information</h5>
        <ul class="list-group mb-4">
          <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($student['fullname']); ?></li>
          <li class="list-group-item"><strong>RegNo:</strong> <?php echo htmlspecialchars($regno); ?></li>
          <li class="list-group-item"><strong>Course ID:</strong> <?php echo htmlspecialchars($student['course_id']); ?></li>
          <li class="list-group-item"><strong>Year of Study:</strong> <?php echo htmlspecialchars($student['year_of_study']); ?></li>
        </ul>

        <h5 class="mb-3">Marks</h5>
        <table class="table table-bordered table-striped">
          <thead class="table-success">
            <tr>
              <th>Course Unit</th>
              <th>Score</th>
              <th>Semester</th>
              <th>Year</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($marks_result && $marks_result->num_rows > 0) {
                while ($row = $marks_result->fetch_assoc()) {
                    echo "<tr>
                            <td>".htmlspecialchars($row['unit_name'])."</td>
                            <td>".htmlspecialchars($row['score'])."</td>
                            <td>".htmlspecialchars($row['semester'])."</td>
                            <td>".htmlspecialchars($row['year'])."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center text-danger'>No marks found for your account.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-danger">No student information found. Please log in again.</p>
      <?php endif; ?>
      <a href="student.php" class="btn btn-warning">Back to Dashboard</a>
    </div>
  </div>
</div>
</body>
</html>
