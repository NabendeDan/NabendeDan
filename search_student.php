<?php
include 'db.php';

if (isset($_POST['regno'])) {
    $regno = $_POST['regno'];

    // Fetch student details
    $sql = "SELECT * FROM students WHERE regno=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $regno);
    $stmt->execute();
    $studentResult = $stmt->get_result();

    if ($studentResult->num_rows > 0) {
        $student = $studentResult->fetch_assoc();

        echo "<h5 class='text-success'>Student Details</h5>";
        echo "<table class='table table-bordered'>
                <tr><th>RegNo</th><td>".htmlspecialchars($student['regno'])."</td></tr>
                <tr><th>Full Name</th><td>".htmlspecialchars($student['fullname'])."</td></tr>
                <tr><th>DOB</th><td>".htmlspecialchars($student['dob'])."</td></tr>
                <tr><th>Phone</th><td>".htmlspecialchars($student['phone'])."</td></tr>
                <tr><th>District</th><td>".htmlspecialchars($student['district'])."</td></tr>
                <tr><th>Course ID</th><td>".htmlspecialchars($student['course_id'])."</td></tr>
                <tr><th>Department ID</th><td>".htmlspecialchars($student['department_id'])."</td></tr>
                <tr><th>Year of Study</th><td>".htmlspecialchars($student['year_of_study'])."</td></tr>
              </table>";

        // Fetch marks
        $marksSql = "SELECT cu.unit_name, m.score 
                     FROM marks m 
                     JOIN course_units cu ON m.unit_id = cu.unit_id 
                     WHERE m.regno=?";
        $marksStmt = $conn->prepare($marksSql);
        $marksStmt->bind_param("s", $regno);
        $marksStmt->execute();
        $marksResult = $marksStmt->get_result();

        echo "<h5 class='text-primary mt-3'>Marks</h5>";
        if ($marksResult->num_rows > 0) {
            echo "<table class='table table-bordered table-striped'>
                    <thead class='table-dark'>
                      <tr><th>Course Unit</th><th>Score</th></tr>
                    </thead><tbody>";
            while ($mark = $marksResult->fetch_assoc()) {
                echo "<tr>
                        <td>".htmlspecialchars($mark['unit_name'])."</td>
                        <td>".htmlspecialchars($mark['score'])."</td>
                      </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='text-danger'>No marks found for this student.</p>";
        }
    } else {
        echo "<p class='text-danger'>No student found with RegNo ".htmlspecialchars($regno).".</p>";
    }
}
?>
