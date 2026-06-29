<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    header("Location: login.php");
    exit();
}

// Fetch all students for dropdown
$studentsQuery = $conn->query("SELECT regno, fullname, year_of_study FROM students ORDER BY fullname ASC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Enter Marks</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 30px 0;
    }
    
    .main-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      overflow: hidden;
    }
    
    .card-header {
      background: linear-gradient(135deg, #36b9cc 0%, #2a99a9 100%);
      color: white;
      padding: 20px 30px;
      border: none;
    }
    
    .card-body {
      padding: 30px;
    }
    
    .form-label {
      font-weight: 600;
      color: #5a5c69;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      padding: 10px 15px;
      border: 2px solid #e3e6f0;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #36b9cc;
      box-shadow: 0 0 0 3px rgba(54, 185, 204, 0.15);
    }
    
    .student-info {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      border-radius: 10px;
      padding: 15px;
      margin: 15px 0;
      border-left: 4px solid #36b9cc;
      display: none;
    }
    
    .unit-card {
      background: white;
      border: 2px solid #e3e6f0;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      transition: all 0.3s;
    }
    
    .unit-card:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transform: translateY(-2px);
    }
    
    .unit-name {
      font-weight: 600;
      color: #5a5c69;
      margin-bottom: 10px;
    }
    
    .btn-info {
      background: linear-gradient(135deg, #36b9cc 0%, #2a99a9 100%);
      border: none;
      padding: 10px 30px;
      border-radius: 50px;
      font-weight: 600;
      color: white;
    }
    
    .btn-info:hover {
      background: linear-gradient(135deg, #2a99a9 0%, #1e7a87 100%);
      color: white;
    }
    
    .btn-warning {
      background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
      border: none;
      padding: 10px 30px;
      border-radius: 50px;
      font-weight: 600;
      color: white;
    }
    
    .btn-warning:hover {
      background: linear-gradient(135deg, #dda20a 0%, #c49008 100%);
      color: white;
    }
    
    .alert-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      max-width: 400px;
    }
    
    .loading {
      text-align: center;
      padding: 20px;
      color: #858796;
    }
  </style>
</head>
<body class="bg-light">

<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="main-card">
        <div class="card-header">
          <h4 class="mb-0"><i class="fas fa-pen-to-square me-2"></i>Enter Student Marks</h4>
        </div>
        <div class="card-body">
          <form id="marks-form">
            <!-- Student RegNo Dropdown -->
            <div class="mb-3">
              <label class="form-label">Select Student</label>
              <select name="regno" id="regno" class="form-select" required>
                <option value="">-- Select Student RegNo --</option>
                <?php while($student = $studentsQuery->fetch_assoc()): ?>
                  <option value="<?php echo htmlspecialchars($student['regno']); ?>" 
                          data-year="<?php echo htmlspecialchars($student['year_of_study']); ?>"
                          data-name="<?php echo htmlspecialchars($student['fullname']); ?>">
                    <?php echo htmlspecialchars($student['regno'] . ' - ' . $student['fullname']); ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- Student Info Display -->
            <div id="studentInfo" class="student-info">
              <div class="row">
                <div class="col-md-6">
                  <strong>Name:</strong> <span id="studentName"></span><br>
                  <strong>Reg No:</strong> <span id="studentRegno"></span>
                </div>
                <div class="col-md-6">
                  <strong>Year of Study:</strong> <span id="studentYear"></span>
                </div>
              </div>
            </div>

            <!-- Year of Study Dropdown -->
            <div class="mb-3">
              <label class="form-label">Year of Study</label>
              <select name="year" id="year" class="form-select" required>
                <option value="">-- Select Year --</option>
                <option value="1">Year 1</option>
                <option value="2">Year 2</option>
                <option value="3">Year 3</option>
                <option value="4">Year 4</option>
              </select>
            </div>

            <!-- Semester Dropdown -->
            <div class="mb-3">
              <label class="form-label">Semester</label>
              <select name="semester" id="semester" class="form-select" required>
                <option value="">-- Select Semester --</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
                <option value="3">Semester 3</option>
              </select>
            </div>

            <!-- Course Units Container -->
            <div id="course-units"></div>

            <!-- Buttons -->
            <div class="mt-4">
              <button type="submit" class="btn btn-info" id="saveBtn">
                <i class="fas fa-save me-2"></i>Save Marks
              </button>
              <button onclick="history.back()" type="button" class="btn btn-warning ms-2">
                <i class="fas fa-arrow-left me-2"></i>Back
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<script>
$(document).ready(function(){
  // When student is selected
  $("#regno").change(function(){
    var selectedOption = $(this).find('option:selected');
    var regno = $(this).val();
    
    if(regno){
      var name = selectedOption.data('name');
      var year = selectedOption.data('year');
      
      $('#studentName').text(name);
      $('#studentRegno').text(regno);
      $('#studentYear').text('Year ' + year);
      $('#studentInfo').show();
      
      // Auto-select year dropdown based on student data
      $('#year').val(year);
      
      loadUnits();
    } else {
      $('#studentInfo').hide();
      $('#year').val('');
      $('#semester').val('');
      $('#course-units').html('');
    }
  });

  // When year or semester changes
  $("#year, #semester").on("change", function(){
    loadUnits();
  });

  // Load course units
  function loadUnits() {
    var regno = $("#regno").val();
    var year = $("#year").val();
    var semester = $("#semester").val();

    if(regno !== "" && year !== "" && semester !== ""){
      $("#course-units").html('<div class="loading"><div class="spinner-border text-info"></div><p class="mt-2">Loading course units...</p></div>');
      
      $.ajax({
        url: "fetch_units.php",
        method: "POST",
        data: {regno: regno, year: year, semester: semester},
        success: function(data){
          $("#course-units").html(data);
        },
        error: function(){
          $("#course-units").html('<div class="alert alert-danger">Error loading course units</div>');
        }
      });
    } else {
      $("#course-units").html("");
    }
  }

  // Form submission - THIS IS THE KEY PART
  $("#marks-form").on("submit", function(e){
    e.preventDefault();
    
    var regno = $("#regno").val();
    var year = $("#year").val();
    var semester = $("#semester").val();
    
    // Collect marks from all inputs with class "mark-input"
    var marksArray = [];
    var hasMarks = false;
    
    $(".mark-input").each(function(){
      var unitId = $(this).attr("data-unit-id");
      var markValue = $(this).val();
      
      if(markValue !== "" && markValue !== null){
        marksArray.push({
          unit_id: unitId,
          score: markValue
        });
        hasMarks = true;
      }
    });
    
    if(!hasMarks){
      showAlert('Please enter at least one mark', 'warning');
      return;
    }
    
    // Disable save button
    $("#saveBtn").prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
    
    // Send data via AJAX
    $.ajax({
      url: "save_marks.php",
      method: "POST",
      data: {
        regno: regno,
        year: year,
        semester: semester,
        marks: marksArray
      },
      dataType: "json",
      success: function(response){
        $("#saveBtn").prop('disabled', false).html('<i class="fas fa-save me-2"></i>Save Marks');
        
        if(response.success){
          showAlert(response.message, 'success');
          setTimeout(function(){
            $("#marks-form")[0].reset();
            $("#studentInfo").hide();
            $("#course-units").html("");
          }, 2000);
        } else {
          showAlert(response.message || 'Failed to save marks', 'danger');
        }
      },
      error: function(xhr, status, error){
        $("#saveBtn").prop('disabled', false).html('<i class="fas fa-save me-2"></i>Save Marks');
        console.log("Error:", xhr.responseText);
        showAlert('Error saving marks: ' + error, 'danger');
      }
    });
  });
});

// Show alert
function showAlert(message, type){
  var iconClass = 'exclamation-triangle';
  if(type === 'success') iconClass = 'check-circle';
  else if(type === 'danger') iconClass = 'exclamation-circle';
  
  var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show shadow" role="alert">' +
    '<i class="fas fa-' + iconClass + ' me-2"></i>' +
    message +
    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
    '</div>';
  
  $('#alertContainer').html(alertHtml);
  
  setTimeout(function(){
    $('.alert').fadeOut(300, function(){
      $(this).remove();
    });
  }, 3000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>