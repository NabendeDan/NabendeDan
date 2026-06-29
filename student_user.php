<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    header("Location: login.php");
    exit();
}

// Determine dashboard URL based on role
$dashboard_url = ($_SESSION['role'] == 'Director') ? 'director.php' : 'staff.php';

// Fetch courses and departments
$courses = $conn->query("SELECT * FROM courses ORDER BY course_name");
$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Student - Dan4Christ Institute</title>
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
    
    .main-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
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
    
    .card-body-custom {
      padding: 40px 30px;
    }
    
    .section-header {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      padding: 15px 20px;
      border-radius: 10px;
      margin: 30px 0 20px 0;
      border-left: 4px solid var(--info);
    }
    
    .section-header h6 {
      margin: 0;
      font-weight: 700;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.95rem;
    }
    
    .form-control, .form-select {
      border-radius: 12px;
      padding: 14px 18px;
      border: 2px solid #e3e6f0;
      transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--info);
      box-shadow: 0 0 0 3px rgba(54, 185, 204, 0.15);
    }
    
    .photo-upload-wrapper {
      position: relative;
      display: inline-block;
    }
    
    .photo-preview {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--info);
      background: #f8f9fc;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 60px;
      color: #ddd;
      overflow: hidden;
    }
    
    .photo-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .photo-upload-btn {
      position: absolute;
      bottom: 0;
      right: 0;
      background: var(--info);
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border: 3px solid white;
    }
    
    .btn-save {
      background: linear-gradient(135deg, var(--info) 0%, #2a99a9 100%);
      border: none;
      color: white;
      padding: 14px 40px;
      border-radius: 50px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      justify-content: center;
    }
    
    .btn-back {
      background: linear-gradient(135deg, var(--secondary) 0%, #5a5c69 100%);
      border: none;
      color: white;
      padding: 14px 40px;
      border-radius: 50px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      justify-content: center;
    }
    
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
    }
    
    @media (max-width: 768px) {
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
  <p class="text-muted">Creating student account...</p>
</div>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<!-- Navbar -->
<nav class="navbar navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="<?php echo $dashboard_url; ?>">
      <i class="fas fa-university me-2"></i>Dan4Christ Institute
    </a>
    <a href="<?php echo $dashboard_url; ?>" class="btn btn-outline-light btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
    </a>
  </div>
</nav>

<div class="main-container">
  <!-- Page Header -->
  <div class="page-header">
    <div class="header-icon">
      <i class="fas fa-user-graduate"></i>
    </div>
    <h2>Create Student Account</h2>
    <p>Register new students to the institute</p>
  </div>

  <!-- Main Card -->
  <div class="main-card">
    <div class="card-header-custom">
      <i class="fas fa-user-plus"></i>
      <span>Student Information</span>
    </div>
    <div class="card-body-custom">
      <form id="studentForm" enctype="multipart/form-data">
        
        <!-- Photo Upload -->
        <div class="text-center mb-4">
          <div class="photo-upload-wrapper">
            <div class="photo-preview" id="photoPreview">
              <i class="fas fa-user"></i>
            </div>
            <label for="photo" class="photo-upload-btn">
              <i class="fas fa-camera"></i>
            </label>
            <input type="file" id="photo" name="photo" accept="image/*" style="display: none;">
          </div>
          <p class="text-muted mt-2">Click camera icon to upload photo (max 5MB)</p>
        </div>

        <!-- Personal Information -->
        <div class="section-header">
          <h6><i class="fas fa-user"></i>Personal Information</h6>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-id-card"></i>Registration Number *
            </label>
            <input type="text" name="regno" class="form-control" 
                   placeholder="e.g., 26/ICT/001" required>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-user"></i>Full Name *
            </label>
            <input type="text" name="fullname" class="form-control" 
                   placeholder="Enter full name" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-calendar"></i>Date of Birth
            </label>
            <input type="date" name="dob" class="form-control">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-phone"></i>Phone Number
            </label>
            <input type="tel" name="phone" class="form-control" 
                   placeholder="e.g., +256 700 000 000">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-envelope"></i>Email Address
            </label>
            <input type="email" name="email" class="form-control" 
                   placeholder="e.g., student@example.com">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-map-marker-alt"></i>District
            </label>
            <input type="text" name="district" class="form-control" 
                   placeholder="e.g., Kampala">
          </div>
        </div>

        <!-- Academic Information -->
        <div class="section-header">
          <h6><i class="fas fa-graduation-cap"></i>Academic Information</h6>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-book"></i>Course *
            </label>
            <select name="course_id" class="form-select" required>
              <option value="">-- Select Course --</option>
              <?php while ($row = $courses->fetch_assoc()): ?>
                <option value="<?php echo $row['course_id']; ?>">
                  <?php echo htmlspecialchars($row['course_name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-building"></i>Department *
            </label>
            <select name="department_id" class="form-select" required>
              <option value="">-- Select Department --</option>
              <?php while ($row = $departments->fetch_assoc()): ?>
                <option value="<?php echo $row['dept_id']; ?>">
                  <?php echo htmlspecialchars($row['dept_name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-layer-group"></i>Year of Study *
            </label>
            <select name="year_of_study" class="form-select" required>
              <option value="">-- Select Year --</option>
              <option value="1">Year 1</option>
              <option value="2">Year 2</option>
              <option value="3">Year 3</option>
              <option value="4">Year 4</option>
              <option value="5">Year 5</option>
              <option value="6">Year 6</option>
            </select>
          </div>
        </div>

        <!-- Account Credentials -->
        <div class="section-header">
          <h6><i class="fas fa-lock"></i>Account Credentials</h6>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-user-circle"></i>Username *
            </label>
            <input type="text" name="username" class="form-control" 
                   placeholder="Enter username for login" required>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-key"></i>Password *
            </label>
            <input type="password" name="password" class="form-control" 
                   placeholder="Enter secure password" required>
          </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex gap-3">
          <button type="submit" class="btn-save" id="saveBtn">
            <i class="fas fa-user-plus"></i>Create Student Account
          </button>
          <button type="button" class="btn-back" onclick="window.location.href='<?php echo $dashboard_url; ?>'">
            <i class="fas fa-arrow-left"></i>Back
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Get the dashboard URL from PHP
var dashboardUrl = '<?php echo $dashboard_url; ?>';

// Photo preview
$('#photo').change(function(e) {
  const file = e.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      alert('File too large. Maximum size is 5MB');
      $(this).val('');
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      $('#photoPreview').html(`<img src="${e.target.result}" alt="Preview">`);
    };
    reader.readAsDataURL(file);
  }
});

// Form submission
$('#studentForm').on('submit', function(e) {
  e.preventDefault();
  
  const saveBtn = $('#saveBtn');
  const originalText = saveBtn.html();
  
  const password = $('input[name="password"]').val();
  if (password.length < 6) {
    alert('Password must be at least 6 characters');
    return;
  }
  
  saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');
  $('#loadingOverlay').addClass('show');
  
  const formData = new FormData(this);
  
  $.ajax({
    url: 'save_student.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(response) {
      $('#loadingOverlay').removeClass('show');
      saveBtn.prop('disabled', false).html(originalText);
      
      if (response.success) {
        alert(response.message);
        // Redirect based on role
        window.location.href = dashboardUrl;
      } else {
        alert('Error: ' + response.message);
      }
    },
    error: function(xhr, status, error) {
      $('#loadingOverlay').removeClass('show');
      saveBtn.prop('disabled', false).html(originalText);
      alert('Error: ' + (xhr.responseText || error));
    }
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>