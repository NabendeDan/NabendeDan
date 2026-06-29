<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    header("Location: login.php");
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $dept_id = intval($_POST['dept_id']);
    $course_id = $_POST['course_id'];
    
    try {
        $conn->begin_transaction();
        
        // If user selected "new", create new course
        if ($course_id == "new") {
            $course_name = trim($_POST['new_course_name']);
            
            if (empty($course_name)) {
                throw new Exception('Course name is required');
            }
            
            $sql = "INSERT INTO courses (course_name, dept_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $course_name, $dept_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create course: ' . $stmt->error);
            }
            
            $course_id = $conn->insert_id;
            $stmt->close();
        } else {
            $course_id = intval($course_id);
        }
        
        // Save course units if provided
        $units_saved = 0;
        if (!empty($_POST['unit_name'])) {
            for ($i = 0; $i < count($_POST['unit_name']); $i++) {
                $unit_name = trim($_POST['unit_name'][$i]);
                $year      = intval($_POST['year'][$i]);
                $semester  = intval($_POST['semester'][$i]);
                
                if (!empty($unit_name)) {
                    $unit_sql = "INSERT INTO course_units (unit_name, course_id, year, semester) VALUES (?, ?, ?, ?)";
                    $unit_stmt = $conn->prepare($unit_sql);
                    $unit_stmt->bind_param("siii", $unit_name, $course_id, $year, $semester);
                    
                    if ($unit_stmt->execute()) {
                        $units_saved++;
                    }
                    $unit_stmt->close();
                }
            }
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Course and $units_saved unit(s) saved successfully!",
            'course_id' => $course_id,
            'units_saved' => $units_saved
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    $conn->close();
    exit();
}

// Fetch departments and courses for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name");
$courses = $conn->query("SELECT * FROM courses ORDER BY course_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Course & Units - Dan4Christ Institute</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    :root {
      --primary: #4e73df;
      --secondary: #858796;
      --success: #1cc88a;
      --info: #36b9cc;
      --warning: #f6c23e;
      --danger: #e74a3b;
      --dark: #5a5c69;
      --light: #f8f9fc;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 30px 0;
    }
    
    /* Navbar */
    .navbar {
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 15px 0;
      margin-bottom: 30px;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: white !important;
    }
    
    /* Main Container */
    .main-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, var(--info) 0%, #2a99a9 100%);
      border-radius: 20px;
      padding: 40px;
      color: white;
      margin-bottom: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      text-align: center;
    }
    
    .page-header h2 {
      font-weight: 700;
      margin-bottom: 10px;
      font-size: 2.2rem;
    }
    
    .page-header p {
      font-size: 1.1rem;
      opacity: 0.95;
      margin: 0;
    }
    
    .header-icon {
      width: 80px;
      height: 80px;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 40px;
      margin: 0 auto 20px;
      backdrop-filter: blur(10px);
    }
    
    /* Main Card */
    .main-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
      overflow: hidden;
      margin-bottom: 30px;
    }
    
    .card-header-custom {
      background: linear-gradient(135deg, var(--info) 0%, #2a99a9 100%);
      color: white;
      padding: 25px 30px;
      font-size: 1.3rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .card-header-custom i {
      font-size: 1.8rem;
    }
    
    .card-body-custom {
      padding: 40px 30px;
    }
    
    /* Form Styling */
    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .form-label i {
      color: var(--info);
    }
    
    .form-control, .form-select {
      border-radius: 12px;
      padding: 14px 18px;
      border: 2px solid #e3e6f0;
      font-size: 1rem;
      transition: all 0.3s;
      background-color: white;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--info);
      box-shadow: 0 0 0 3px rgba(54, 185, 204, 0.15);
      outline: none;
    }
    
    /* Unit Card */
    .unit-card {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 15px;
      border-left: 4px solid var(--info);
      animation: slideDown 0.4s ease;
      position: relative;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .unit-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .unit-number {
      background: var(--info);
      color: white;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 1rem;
    }
    
    .btn-remove-unit {
      background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);
      border: none;
      color: white;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .btn-remove-unit:hover {
      transform: scale(1.1) rotate(90deg);
      box-shadow: 0 5px 15px rgba(231, 74, 59, 0.4);
    }
    
    .unit-row {
      display: grid;
      grid-template-columns: 2fr 1fr 1fr;
      gap: 15px;
      margin-bottom: 10px;
    }
    
    /* Buttons */
    .btn-group-custom {
      display: flex;
      gap: 15px;
      margin-top: 30px;
      flex-wrap: wrap;
    }
    
    .btn-add-unit {
      background: linear-gradient(135deg, var(--success) 0%, #17a673 100%);
      border: none;
      color: white;
      padding: 14px 30px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .btn-add-unit:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(28, 200, 138, 0.4);
      color: white;
    }
    
    .btn-save {
      background: linear-gradient(135deg, var(--info) 0%, #2a99a9 100%);
      border: none;
      color: white;
      padding: 14px 40px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.05rem;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      justify-content: center;
      min-width: 200px;
    }
    
    .btn-save:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(54, 185, 204, 0.4);
      color: white;
    }
    
    .btn-save:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
    
    .btn-back {
      background: linear-gradient(135deg, var(--secondary) 0%, #5a5c69 100%);
      border: none;
      color: white;
      padding: 14px 40px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.05rem;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      justify-content: center;
      min-width: 200px;
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(133, 135, 150, 0.4);
      color: white;
    }
    
    /* New Course Section */
    .new-course-section {
      background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
      border-radius: 15px;
      padding: 20px;
      margin: 20px 0;
      border-left: 4px solid var(--warning);
      animation: slideDown 0.4s ease;
      display: none;
    }
    
    .new-course-section h6 {
      color: var(--dark);
      font-weight: 700;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .new-course-section h6 i {
      color: var(--warning);
    }
    
    /* Alert Container */
    .alert-container {
      position: fixed;
      top: 100px;
      right: 20px;
      z-index: 9999;
      max-width: 400px;
    }
    
    .custom-alert {
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      animation: slideIn 0.3s;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    
    /* Loading Overlay */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255,255,255,0.9);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      flex-direction: column;
      gap: 20px;
    }
    
    .loading-overlay.show {
      display: flex;
    }
    
    .spinner {
      width: 60px;
      height: 60px;
      border: 4px solid #e3e6f0;
      border-top-color: var(--info);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    /* Units Counter */
    .units-counter {
      background: var(--info);
      color: white;
      padding: 8px 20px;
      border-radius: 50px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .main-container {
        padding: 0 15px;
      }
      
      .page-header {
        padding: 30px 20px;
      }
      
      .page-header h2 {
        font-size: 1.8rem;
      }
      
      .card-body-custom {
        padding: 30px 20px;
      }
      
      .unit-row {
        grid-template-columns: 1fr;
      }
      
      .btn-group-custom {
        flex-direction: column;
      }
      
      .btn-save, .btn-back {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner"></div>
  <p class="text-muted">Saving course and units...</p>
</div>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<!-- Navbar -->
<nav class="navbar navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="director.php">
      <i class="fas fa-university me-2"></i>Dan_4_Christ Institute of Science & Technology
    </a>
    <a href="director.php" class="btn btn-outline-light btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
    </a>
  </div>
</nav>

<div class="main-container">
  <!-- Page Header -->
  <div class="page-header">
    <div class="header-icon">
      <i class="fas fa-book"></i>
    </div>
    <h2>Create Course & Units</h2>
    <p>Add new courses and their course units</p>
  </div>

  <!-- Main Card -->
  <div class="main-card">
    <div class="card-header-custom">
      <i class="fas fa-plus-circle"></i>
      <span>Course Information</span>
    </div>
    <div class="card-body-custom">
      <form id="courseForm">
        <!-- Department Selection -->
        <div class="mb-4">
          <label class="form-label">
            <i class="fas fa-building"></i>
            Department
          </label>
          <select name="dept_id" id="dept_id" class="form-select" required>
            <option value="">-- Select Department --</option>
            <?php while ($row = $departments->fetch_assoc()): ?>
              <option value="<?php echo $row['dept_id']; ?>">
                <?php echo htmlspecialchars($row['dept_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Course Selection -->
        <div class="mb-4">
          <label class="form-label">
            <i class="fas fa-graduation-cap"></i>
            Course
          </label>
          <select name="course_id" id="course_id" class="form-select" required>
            <option value="">-- Select Course --</option>
            <?php while ($row = $courses->fetch_assoc()): ?>
              <option value="<?php echo $row['course_id']; ?>">
                <?php echo htmlspecialchars($row['course_name']); ?>
              </option>
            <?php endwhile; ?>
            <option value="new">+ Add New Course</option>
          </select>
        </div>

        <!-- New Course Section -->
        <div class="new-course-section" id="new-course-div">
          <h6>
            <i class="fas fa-plus-circle"></i>
            Create New Course
          </h6>
          <input type="text" name="new_course_name" id="new_course_name" 
                 class="form-control" placeholder="Enter new course name">
        </div>

        <!-- Course Units Section -->
        <div class="mt-5">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
              <i class="fas fa-list-ul me-2" style="color: var(--info);"></i>
              Course Units
            </h5>
            <div class="units-counter">
              <i class="fas fa-layer-group"></i>
              <span id="unitCount">1</span> Unit(s)
            </div>
          </div>

          <div id="unit-fields">
            <!-- First unit (default) -->
            <div class="unit-card" data-unit-index="1">
              <div class="unit-card-header">
                <div class="unit-number">1</div>
              </div>
              <div class="unit-row">
                <div>
                  <label class="form-label small">Unit Name</label>
                  <input type="text" name="unit_name[]" class="form-control" 
                         placeholder="e.g., Introduction to ICT" required>
                </div>
                <div>
                  <label class="form-label small">Year</label>
                  <input type="number" name="year[]" class="form-control" 
                         placeholder="e.g., 1" min="1" max="6" required>
                </div>
                <div>
                  <label class="form-label small">Semester</label>
                  <select name="semester[]" class="form-select" required>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <button type="button" class="btn-add-unit mt-3" onclick="addUnitField()">
            <i class="fas fa-plus"></i>
            <span>Add Another Unit</span>
          </button>
        </div>

        <!-- Action Buttons -->
        <div class="btn-group-custom">
          <button type="submit" class="btn-save" id="saveBtn">
            <i class="fas fa-save"></i>
            <span>Save Course & Units</span>
          </button>
          <button type="button" class="btn-back" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let unitCounter = 1;

// Toggle new course section
$('#course_id').change(function() {
  if ($(this).val() === 'new') {
    $('#new-course-div').slideDown();
    $('#new_course_name').prop('required', true);
  } else {
    $('#new-course-div').slideUp();
    $('#new_course_name').prop('required', false).val('');
  }
});

// Add new unit field
function addUnitField() {
  unitCounter++;
  
  const unitHtml = `
    <div class="unit-card" data-unit-index="${unitCounter}">
      <div class="unit-card-header">
        <div class="unit-number">${unitCounter}</div>
        <button type="button" class="btn-remove-unit" onclick="removeUnit(this)" title="Remove this unit">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="unit-row">
        <div>
          <label class="form-label small">Unit Name</label>
          <input type="text" name="unit_name[]" class="form-control" 
                 placeholder="e.g., Database Systems" required>
        </div>
        <div>
          <label class="form-label small">Year</label>
          <input type="number" name="year[]" class="form-control" 
                 placeholder="e.g., 2" min="1" max="6" required>
        </div>
        <div>
          <label class="form-label small">Semester</label>
          <select name="semester[]" class="form-select" required>
            <option value="1">Semester 1</option>
            <option value="2">Semester 2</option>
          </select>
        </div>
      </div>
    </div>
  `;
  
  $('#unit-fields').append(unitHtml);
  updateUnitCount();
  
  // Scroll to new unit
  $('html, body').animate({
    scrollTop: $('.unit-card:last').offset().top - 100
  }, 300);
}

// Remove unit field
function removeUnit(btn) {
  if ($('.unit-card').length > 1) {
    $(btn).closest('.unit-card').fadeOut(300, function() {
      $(this).remove();
      updateUnitCount();
      renumberUnits();
    });
  } else {
    showAlert('You must have at least one unit', 'warning');
  }
}

// Update unit count display
function updateUnitCount() {
  const count = $('.unit-card').length;
  $('#unitCount').text(count);
}

// Renumber units after removal
function renumberUnits() {
  $('.unit-card').each(function(index) {
    $(this).find('.unit-number').text(index + 1);
  });
}

// Form submission via AJAX
$('#courseForm').on('submit', function(e) {
  e.preventDefault();
  
  const saveBtn = $('#saveBtn');
  const originalText = saveBtn.html();
  
  // Validate
  const courseId = $('#course_id').val();
  if (courseId === 'new') {
    const newCourseName = $('#new_course_name').val().trim();
    if (!newCourseName) {
      showAlert('Please enter a name for the new course', 'warning');
      return;
    }
  }
  
  // Disable button and show loading
  saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
  $('#loadingOverlay').addClass('show');
  
  // Collect form data
  const formData = {
    ajax: 1,
    dept_id: $('#dept_id').val(),
    course_id: courseId,
    new_course_name: $('#new_course_name').val(),
    unit_name: [],
    year: [],
    semester: []
  };
  
  // Collect all units
  $('.unit-card').each(function() {
    const inputs = $(this).find('input, select');
    formData.unit_name.push(inputs.filter('[name="unit_name[]"]').val());
    formData.year.push(inputs.filter('[name="year[]"]').val());
    formData.semester.push(inputs.filter('[name="semester[]"]').val());
  });
  
  // Submit via AJAX
  $.ajax({
    url: 'courses.php',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
      $('#loadingOverlay').removeClass('show');
      saveBtn.prop('disabled', false).html(originalText);
      
      if (response.success) {
        showAlert(response.message, 'success');
        
        // Reset form after 2 seconds
        setTimeout(function() {
          $('#courseForm')[0].reset();
          $('#new-course-div').slideUp();
          
          // Remove all units except the first one
          $('.unit-card:not(:first)').remove();
          updateUnitCount();
          
          // Scroll to top
          $('html, body').animate({ scrollTop: 0 }, 300);
        }, 2000);
      } else {
        showAlert(response.message || 'Failed to save course', 'danger');
      }
    },
    error: function(xhr, status, error) {
      $('#loadingOverlay').removeClass('show');
      saveBtn.prop('disabled', false).html(originalText);
      showAlert('Error: ' + (xhr.responseText || error), 'danger');
    }
  });
});

// Show Alert
function showAlert(message, type) {
  $('#alertContainer').empty();
  
  const icons = {
    success: 'fa-check-circle',
    danger: 'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle'
  };
  
  const alertHtml = `
    <div class="custom-alert alert alert-${type}">
      <i class="fas ${icons[type]} fa-lg"></i>
      <span>${message}</span>
      <button type="button" class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>
    </div>
  `;
  
  $('#alertContainer').html(alertHtml);
  
  setTimeout(function() {
    $('.custom-alert').fadeOut(300, function() {
      $(this).remove();
    });
  }, 4000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>