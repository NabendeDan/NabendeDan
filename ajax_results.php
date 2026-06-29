<?php
session_start();
include 'db.php';

// Check if user is a student
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Student') {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Unauthorized access</div>';
    exit();
}

// Get student registration number from POST or session
$regno = isset($_POST['regno']) ? $_POST['regno'] : (isset($_SESSION['regno']) ? $_SESSION['regno'] : '');

if (empty($regno)) {
    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Student registration number not found. Please login again.</div>';
    exit();
}

// Fetch student information
$studentQuery = $conn->prepare("SELECT fullname FROM students WHERE regno = ?");
if (!$studentQuery) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Database error: ' . $conn->error . '</div>';
    exit();
}

$studentQuery->bind_param("s", $regno);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();

if ($studentResult->num_rows === 0) {
    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Student not found</div>';
    exit();
}

$student = $studentResult->fetch_assoc();
$studentQuery->close();

// Function to convert score to grade points
function getGradePoints($score) {
    if ($score >= 70) return 5.0;  // A
    if ($score >= 60) return 4.0;  // B
    if ($score >= 50) return 3.0;  // C
    if ($score >= 40) return 2.0;  // D
    return 0.0;  // F
}

// Function to get grade letter
function getGradeLetter($score) {
    if ($score >= 70) return 'A';
    if ($score >= 60) return 'B';
    if ($score >= 50) return 'C';
    if ($score >= 40) return 'D';
    return 'F';
}

// Function to get grade class for styling
function getGradeClass($score) {
    if ($score >= 70) return 'success';
    if ($score >= 60) return 'primary';
    if ($score >= 50) return 'info';
    if ($score >= 40) return 'warning';
    return 'danger';
}

// Fetch all marks for this student
$marksQuery = $conn->prepare("
    SELECT m.unit_id, m.semester, m.year, m.score, cu.unit_name
    FROM marks m
    LEFT JOIN course_units cu ON m.unit_id = cu.unit_id
    WHERE m.regno = ?
    ORDER BY m.year ASC, m.semester ASC, cu.unit_name ASC
");

if (!$marksQuery) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Database error: ' . $conn->error . '</div>';
    exit();
}

$marksQuery->bind_param("s", $regno);
$marksQuery->execute();
$marksResult = $marksQuery->get_result();

if ($marksResult->num_rows === 0) {
    echo '<div class="alert alert-info">';
    echo '<i class="fas fa-info-circle me-2"></i>';
    echo 'No marks found for your registration. Please contact administration if you believe this is an error.';
    echo '</div>';
    $marksQuery->close();
    $conn->close();
    exit();
}

// Group marks by year, then by semester
$groupedMarks = [];
$totalGradePoints = 0;
$totalUnits = 0;

while ($mark = $marksResult->fetch_assoc()) {
    $year = $mark['year'];
    $semester = $mark['semester'];
    $score = floatval($mark['score']);
    
    // Create year group if it doesn't exist
    if (!isset($groupedMarks[$year])) {
        $groupedMarks[$year] = [];
    }
    
    // Create semester group if it doesn't exist
    if (!isset($groupedMarks[$year][$semester])) {
        $groupedMarks[$year][$semester] = [
            'year' => $year,
            'semester' => $semester,
            'units' => [],
            'totalScore' => 0,
            'unitCount' => 0,
            'totalGradePoints' => 0
        ];
    }
    
    // Add grade points to semester total
    $gradePoints = getGradePoints($score);
    $groupedMarks[$year][$semester]['totalGradePoints'] += $gradePoints;
    $groupedMarks[$year][$semester]['totalScore'] += $score;
    $groupedMarks[$year][$semester]['unitCount']++;
    
    // Add to overall totals
    $totalGradePoints += $gradePoints;
    $totalUnits++;
    
    $groupedMarks[$year][$semester]['units'][] = $mark;
}

$marksQuery->close();

// Calculate overall CGPA
$cgpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

// Calculate overall average percentage
$overallQuery = $conn->prepare("SELECT AVG(score) as overall_avg FROM marks WHERE regno = ?");
$overallQuery->bind_param("s", $regno);
$overallQuery->execute();
$overallResult = $overallQuery->get_result();
$overall = $overallResult->fetch_assoc();
$overallAvg = isset($overall['overall_avg']) ? floatval($overall['overall_avg']) : 0;
$overallQuery->close();

$conn->close();

// Sort years in ascending order
ksort($groupedMarks);
?>

<div class="student-results">
  <!-- Student Info Header -->
  <div class="alert alert-info mb-4">
    <i class="fas fa-user-graduate me-2"></i>
    <strong>Student:</strong> <?php echo htmlspecialchars($student['fullname']); ?> | 
    <strong>Reg No:</strong> <?php echo htmlspecialchars($regno); ?>
  </div>

  <?php 
  // Year colors for visual distinction
  $yearColors = [
    1 => 'primary',
    2 => 'success',
    3 => 'info',
    4 => 'warning',
    5 => 'danger'
  ];
  
  // Loop through each year
  foreach ($groupedMarks as $year => $semesters): 
    $yearColor = isset($yearColors[$year]) ? $yearColors[$year] : 'secondary';
    
    // Calculate year GPA
    $yearTotalPoints = 0;
    $yearTotalUnits = 0;
    foreach ($semesters as $semData) {
      $yearTotalPoints += $semData['totalGradePoints'];
      $yearTotalUnits += $semData['unitCount'];
    }
    $yearGPA = $yearTotalUnits > 0 ? $yearTotalPoints / $yearTotalUnits : 0;
  ?>
    
    <!-- Year Section -->
    <div class="year-section mb-5">
      <div class="year-header bg-<?php echo $yearColor; ?> text-white p-3 rounded-top">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="mb-0">
            <i class="fas fa-calendar me-2"></i>
            Year <?php echo htmlspecialchars($year); ?>
          </h4>
          <div class="text-end">
            <small>Year GPA:</small>
            <span class="badge bg-white text-<?php echo $yearColor; ?> fs-5">
              <?php echo number_format($yearGPA, 2); ?>
            </span>
          </div>
        </div>
      </div>
      
      <div class="year-content border border-<?php echo $yearColor; ?> border-top-0 rounded-bottom p-3">
        <?php 
        // Sort semesters in ascending order
        ksort($semesters);
        
        // Loop through each semester in this year
        foreach ($semesters as $semester => $data): 
          $semesterGPA = $data['unitCount'] > 0 ? $data['totalGradePoints'] / $data['unitCount'] : 0;
          $semesterAvg = $data['unitCount'] > 0 ? $data['totalScore'] / $data['unitCount'] : 0;
        ?>
          
          <!-- Semester Card -->
          <div class="card mb-4">
            <div class="card-header bg-light">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-<?php echo $yearColor; ?>">
                  <i class="fas fa-book-open me-2"></i>
                  Semester <?php echo htmlspecialchars($semester); ?>
                </h5>
                <div class="text-end">
                  <small class="text-muted">GPA:</small>
                  <span class="badge bg-<?php echo $yearColor; ?> fs-6">
                    <?php echo number_format($semesterGPA, 2); ?>
                  </span>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 5%;">#</th>
                      <th style="width: 45%;">Unit Name</th>
                      <th class="text-center" style="width: 15%;">Score (%)</th>
                      <th class="text-center" style="width: 10%;">Grade</th>
                      <th class="text-center" style="width: 15%;">Grade Points</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $counter = 1;
                    foreach ($data['units'] as $unit): 
                      $score = floatval($unit['score']);
                      $grade = getGradeLetter($score);
                      $gradeClass = getGradeClass($score);
                      $gradePoints = getGradePoints($score);
                      
                      $unitName = isset($unit['unit_name']) && !empty($unit['unit_name']) ? $unit['unit_name'] : 'Unit ' . $unit['unit_id'];
                    ?>
                      <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><strong><?php echo htmlspecialchars($unitName); ?></strong></td>
                        <td class="text-center">
                          <span class="badge bg-<?php echo $gradeClass; ?> fs-6">
                            <?php echo number_format($score, 1); ?>%
                          </span>
                        </td>
                        <td class="text-center">
                          <span class="badge bg-<?php echo $gradeClass; ?>">
                            <?php echo $grade; ?>
                          </span>
                        </td>
                        <td class="text-center">
                          <strong><?php echo number_format($gradePoints, 1); ?></strong>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <tfoot class="table-light">
                    <tr>
                      <th colspan="2" class="text-end">Semester Average:</th>
                      <th class="text-center">
                        <?php echo number_format($semesterAvg, 2); ?>%
                      </th>
                      <th class="text-center">
                        <?php
                        $avgGrade = getGradeLetter($semesterAvg);
                        $avgClass = getGradeClass($semesterAvg);
                        echo '<span class="badge bg-' . $avgClass . '">' . $avgGrade . '</span>';
                        ?>
                      </th>
                      <th class="text-center">
                        <strong><?php echo number_format($semesterGPA, 2); ?></strong>
                      </th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          
        <?php endforeach; ?>
      </div>
    </div>
    
  <?php endforeach; ?>

  <!-- Overall Performance Summary -->
  <div class="card mt-4">
    <div class="card-header bg-dark text-white">
      <h5 class="mb-0">
        <i class="fas fa-trophy me-2"></i>
        Overall Academic Performance
      </h5>
    </div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col-md-4 mb-3">
          <div class="p-3 bg-light rounded">
            <h6 class="text-muted mb-2">CGPA</h6>
            <div class="display-5 fw-bold text-primary">
              <?php echo number_format($cgpa, 2); ?>
            </div>
            <small class="text-muted">out of 5.00</small>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="p-3 bg-light rounded">
            <h6 class="text-muted mb-2">Average Score</h6>
            <div class="display-5 fw-bold text-success">
              <?php echo number_format($overallAvg, 2); ?>%
            </div>
            <small class="text-muted">overall average</small>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="p-3 bg-light rounded">
            <h6 class="text-muted mb-2">Classification</h6>
            <div class="mt-2">
              <?php
              if ($cgpa >= 4.5) {
                echo '<span class="badge bg-success fs-3">First Class</span>';
              } elseif ($cgpa >= 3.5) {
                echo '<span class="badge bg-primary fs-3">Upper Second</span>';
              } elseif ($cgpa >= 2.5) {
                echo '<span class="badge bg-info fs-3">Lower Second</span>';
              } elseif ($cgpa >= 2.0) {
                echo '<span class="badge bg-warning fs-3">Third Class</span>';
              } else {
                echo '<span class="badge bg-danger fs-3">Fail</span>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Grading Scale Reference -->
  <div class="card mt-4">
    <div class="card-header bg-secondary text-white">
      <h6 class="mb-0">
        <i class="fas fa-info-circle me-2"></i>
        Grading Scale Reference
      </h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead class="table-light">
            <tr>
              <th>Grade</th>
              <th>Score Range</th>
              <th>Grade Points</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-success">A</span></td>
              <td>70 - 100%</td>
              <td><strong>5.0</strong></td>
              <td>Excellent</td>
            </tr>
            <tr>
              <td><span class="badge bg-primary">B</span></td>
              <td>60 - 69%</td>
              <td><strong>4.0</strong></td>
              <td>Very Good</td>
            </tr>
            <tr>
              <td><span class="badge bg-info">C</span></td>
              <td>50 - 59%</td>
              <td><strong>3.0</strong></td>
              <td>Good</td>
            </tr>
            <tr>
              <td><span class="badge bg-warning">D</span></td>
              <td>40 - 49%</td>
              <td><strong>2.0</strong></td>
              <td>Pass</td>
            </tr>
            <tr>
              <td><span class="badge bg-danger">F</span></td>
              <td>0 - 39%</td>
              <td><strong>0.0</strong></td>
              <td>Fail</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<style>
.student-results .year-section {
  animation: fadeIn 0.5s ease-in;
}

.student-results .year-header {
  font-weight: 700;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.student-results .year-content {
  background: #f8f9fa;
}

.student-results .card {
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.student-results .card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.student-results .table th {
  font-weight: 600;
  background-color: #f8f9fa;
}

.student-results .badge {
  padding: 8px 12px;
  border-radius: 6px;
  font-weight: 600;
}

.student-results .display-5 {
  font-size: 2.5rem;
}

.student-results .display-3 {
  font-size: 3rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .student-results .display-5 {
    font-size: 1.8rem;
  }
  
  .student-results .table {
    font-size: 0.9rem;
  }
}
</style>