<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    header("Location: login.php");
    exit();
}

$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : 'uploads/default.png';
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'User';

// Fetch existing departments
$departmentsQuery = "SELECT * FROM departments ORDER BY dept_name";
$departments = $conn->query($departmentsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Department - Dan4Christ Institute</title>
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
      padding: 0;
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
    
    .profile-section {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .profile-img {
      border: 3px solid rgba(255,255,255,0.3);
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      transition: transform 0.3s;
    }
    
    .profile-img:hover {
      transform: scale(1.1);
    }
    
    /* Main Container */
    .main-container {
      padding: 0 30px 50px;
      max-width: 1000px;
      margin: 0 auto;
    }
    
    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, var(--warning) 0%, #dda20a 100%);
      border-radius: 20px;
      padding: 40px;
      color: white;
      margin-bottom: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      text-align: center;
    }
    
    .page-header h2 {
      font-weight: 700;
      margin-bottom: 10px;
      font-size: 2.5rem;
    }
    
    .page-header p {
      font-size: 1.1rem;
      opacity: 0.9;
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
      background: linear-gradient(135deg, var(--warning) 0%, #dda20a 100%);
      color: white;
      padding: 25px 30px;
      font-size: 1.3rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .card-body-custom {
      padding: 40px 30px;
    }
    
    /* Form Styling */
    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .form-label i {
      color: var(--warning);
    }
    
    .form-control {
      border-radius: 12px;
      padding: 14px 18px;
      border: 2px solid #e3e6f0;
      font-size: 1rem;
      transition: all 0.3s;
    }
    
    .form-control:focus {
      border-color: var(--warning);
      box-shadow: 0 0 0 3px rgba(246, 194, 62, 0.15);
      outline: none;
    }
    
    /* Buttons */
    .btn-group-custom {
      display: flex;
      gap: 15px;
      margin-top: 30px;
      flex-wrap: wrap;
    }
    
    .btn-save {
      background: linear-gradient(135deg, var(--warning) 0%, #dda20a 100%);
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
      box-shadow: 0 10px 25px rgba(246, 194, 62, 0.4);
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
      text-decoration: none;
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(133, 135, 150, 0.4);
      color: white;
    }
    
    /* Departments List */
    .dept-list {
      margin-top: 40px;
    }
    
    .dept-item {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      border-left: 4px solid var(--warning);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.3s;
    }
    
    .dept-item:hover {
      transform: translateX(5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .dept-name {
      font-weight: 600;
      color: var(--dark);
      font-size: 1.1rem;
    }
    
    .dept-id {
      color: var(--secondary);
      font-size: 0.9rem;
      margin-top: 5px;
    }
    
    .btn-delete {
      background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);
      border: none;
      color: white;
      padding: 8px 20px;
      border-radius: 50px;
      font-size: 0.9rem;
      transition: all 0.3s;
    }
    
    .btn-delete:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(231, 74, 59, 0.4);
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
      border-top-color: var(--warning);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--secondary);
    }
    
    .empty-state i {
      font-size: 80px;
      margin-bottom: 20px;
      opacity: 0.3;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .main-container {
        padding: 0 15px 30px;
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
  <p class="text-muted">Processing...</p>
</div>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<!-- Navbar -->
<nav class="navbar navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="director_dashboard.php">
      <i class="fas fa-university me-2"></i>Dan 4 Christ Institute of Science and Technology
    </a>
    <div class="profile-section">
      <img src="<?php echo htmlspecialchars($photo); ?>" 
           alt="Profile" class="rounded-circle profile-img" 
           width="45" height="45"
           onerror="this.src='uploads/default.png'">
      <span class="text-white d-none d-md-inline">
        <?php echo htmlspecialchars($fullname); ?>
      </span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<div class="main-container">
  <!-- Page Header -->
  <div class="page-header">
    <div class="header-icon">
      <i class="fas fa-building"></i>
    </div>
    <h2>Create Department</h2>
    <p>Manage academic departments</p>
  </div>

  <!-- Main Card -->
  <div class="main-card">
    <div class="card-header-custom">
      <i class="fas fa-plus-circle"></i>
      <span>Add New Department</span>
    </div>
    <div class="card-body-custom">
      <form id="deptForm">
        <div class="mb-4">
          <label class="form-label">
            <i class="fas fa-building"></i>
            Department Name *
          </label>
          <input type="text" name="dept_name" id="dept_name" class="form-control" 
                 placeholder="e.g., Information Technology" required>
        </div>

        <div class="btn-group-custom">
          <button type="submit" class="btn-save" id="saveBtn">
            <i class="fas fa-save"></i>
            <span>Save Department</span>
          </button>
          <a href="director.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
          </a>
        </div>
      </form>

      <!-- Departments List -->
      <div class="dept-list">
        <h5 class="mb-3">
          <i class="fas fa-list me-2" style="color: var(--warning);"></i>
          Existing Departments
        </h5>
        <div id="departmentsList">
          <?php if($departments && $departments->num_rows > 0): ?>
            <?php while($dept = $departments->fetch_assoc()): ?>
              <div class="dept-item" data-id="<?php echo $dept['dept_id']; ?>">
                <div>
                  <div class="dept-name"><?php echo htmlspecialchars($dept['dept_name']); ?></div>
                  <div class="dept-id">ID: <?php echo $dept['dept_id']; ?></div>
                </div>
                <button class="btn-delete" onclick="deleteDepartment(<?php echo $dept['dept_id']; ?>, '<?php echo htmlspecialchars($dept['dept_name']); ?>')">
                  <i class="fas fa-trash me-1"></i>Delete
                </button>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-building"></i>
              <p>No departments found. Create your first department above!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Form submission via AJAX
$('#deptForm').on('submit', function(e) {
  e.preventDefault();
  
  const saveBtn = $('#saveBtn');
  const originalText = saveBtn.html();
  const deptName = $('#dept_name').val().trim();
  
  if (!deptName) {
    showAlert('Please enter a department name', 'warning');
    return;
  }
  
  saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
  $('#loadingOverlay').addClass('show');
  
  $.ajax({
    url: 'save_department.php',
    type: 'POST',
    data: { dept_name: deptName },
    dataType: 'json',
    success: function(response) {
      $('#loadingOverlay').removeClass('show');
      saveBtn.prop('disabled', false).html(originalText);
      
      if (response.success) {
        showAlert(response.message, 'success');
        $('#dept_name').val('');
        // Reload page to show new department
        setTimeout(function() {
          location.reload();
        }, 1500);
      } else {
        showAlert(response.message || 'Failed to create department', 'danger');
      }
    },
    error: function(xhr, status, error) {
      $('#loadingOverlay').removeClass('show');
      saveBtn.prop('disabled', false).html(originalText);
      showAlert('Error: ' + (xhr.responseText || error), 'danger');
    }
  });
});

// Delete Department
function deleteDepartment(deptId, deptName) {
  if (!confirm(`Are you sure you want to delete "${deptName}"?\n\nThis action cannot be undone!`)) {
    return;
  }
  
  $('#loadingOverlay').addClass('show');
  
  $.ajax({
    url: 'delete_department.php',
    type: 'POST',
    data: { dept_id: deptId },
    dataType: 'json',
    success: function(response) {
      $('#loadingOverlay').removeClass('show');
      
      if (response.success) {
        showAlert(response.message, 'success');
        // Remove the department item from the list
        $(`.dept-item[data-id="${deptId}"]`).fadeOut(300, function() {
          $(this).remove();
          
          // Show empty state if no departments left
          if ($('.dept-item').length === 0) {
            $('#departmentsList').html(`
              <div class="empty-state">
                <i class="fas fa-building"></i>
                <p>No departments found. Create your first department above!</p>
              </div>
            `);
          }
        });
      } else {
        showAlert(response.message || 'Failed to delete department', 'danger');
      }
    },
    error: function(xhr, status, error) {
      $('#loadingOverlay').removeClass('show');
      showAlert('Error: ' + (xhr.responseText || error), 'danger');
    }
  });
}

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