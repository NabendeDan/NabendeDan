<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    header("Location: login.php");
    exit();
}

$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : 'uploads/default.png';
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Director';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Director - Dan4Christ Institute</title>
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
      max-width: 900px;
      margin: 0 auto;
    }
    
    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, #5a5c69 0%, #3a3b45 100%);
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
      background: linear-gradient(135deg, #5a5c69 0%, #3a3b45 100%);
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
    
    /* Section Headers */
    .section-header {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      padding: 15px 20px;
      border-radius: 10px;
      margin: 30px 0 20px 0;
      border-left: 4px solid var(--dark);
    }
    
    .section-header h6 {
      margin: 0;
      font-weight: 700;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .section-header h6 i {
      color: var(--dark);
    }
    
    /* Form Styling */
    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.95rem;
    }
    
    .form-label i {
      color: var(--dark);
      font-size: 0.9rem;
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
      border-color: var(--dark);
      box-shadow: 0 0 0 3px rgba(90, 92, 105, 0.15);
      outline: none;
    }
    
    .form-text {
      font-size: 0.85rem;
      color: var(--secondary);
      margin-top: 5px;
    }
    
    /* Photo Upload */
    .photo-upload-wrapper {
      position: relative;
      display: inline-block;
      margin-bottom: 20px;
    }
    
    .photo-preview {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--dark);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
      background: var(--dark);
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border: 3px solid white;
      transition: all 0.3s;
    }
    
    .photo-upload-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 5px 15px rgba(90, 92, 105, 0.4);
    }
    
    /* Buttons */
    .btn-group-custom {
      display: flex;
      gap: 15px;
      margin-top: 40px;
      flex-wrap: wrap;
    }
    
    .btn-save {
      background: linear-gradient(135deg, #5a5c69 0%, #3a3b45 100%);
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
      box-shadow: 0 10px 25px rgba(90, 92, 105, 0.4);
      color: white;
    }
    
    .btn-save:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
    
    .btn-back {
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
      text-decoration: none;
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(246, 194, 62, 0.4);
      color: white;
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
      border-top-color: var(--dark);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
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
      
      .photo-preview {
        width: 120px;
        height: 120px;
      }
    }
  </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner"></div>
  <p class="text-muted">Creating director account...</p>
</div>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<!-- Navbar -->
<nav class="navbar navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="director_dashboard.php">
      <i class="fas fa-university me-2"></i>Dan4Christ Institute
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
      <i class="fas fa-user-tie"></i>
    </div>
    <h2>Create Director Account</h2>
    <p>Add new director with full administrative privileges</p>
  </div>

  <!-- Main Card -->
  <div class="main-card">
    <div class="card-header-custom">
      <i class="fas fa-user-shield"></i>
      <span>Director Information</span>
    </div>
    <div class="card-body-custom">
      <form id="directorForm" enctype="multipart/form-data">
        
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
              <i class="fas fa-id-badge"></i>Full Name *
            </label>
            <input type="text" name="fullname" class="form-control" 
                   placeholder="Enter full name" required>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-calendar"></i>Date of Birth
            </label>
            <input type="date" name="dob" class="form-control">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-phone"></i>Phone Number
            </label>
            <input type="tel" name="phone" class="form-control" 
                   placeholder="e.g., +256 700 000 000">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-envelope"></i>Email Address
            </label>
            <input type="email" name="email" class="form-control" 
                   placeholder="e.g., director@example.com">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-map-marker-alt"></i>District
            </label>
            <input type="text" name="district" class="form-control" 
                   placeholder="e.g., Kampala">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-home"></i>Address
            </label>
            <input type="text" name="address" class="form-control" 
                   placeholder="Enter address">
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
            <small class="form-text">This will be used to login to the system</small>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-key"></i>Password *
            </label>
            <input type="password" name="password" class="form-control" 
                   placeholder="Enter secure password" required>
            <small class="form-text">Minimum 6 characters recommended</small>
          </div>
        </div>

        <!-- Buttons -->
        <div class="btn-group-custom">
          <button type="submit" class="btn-save" id="saveBtn">
            <i class="fas fa-user-plus"></i>
            <span>Create Director Account</span>
          </button>
          <a href="create_user.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Photo preview
$('#photo').change(function(e) {
  const file = e.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      showAlert('File too large. Maximum size is 5MB', 'warning');
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

// Form submission via AJAX
$('#directorForm').on('submit', function(e) {
  e.preventDefault();
  
  const saveBtn = $('#saveBtn');
  const originalText = saveBtn.html();
  
  const password = $('input[name="password"]').val();
  if (password.length < 6) {
    showAlert('Password must be at least 6 characters', 'warning');
    return;
  }
  
  saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');
  $('#loadingOverlay').addClass('show');
  
  const formData = new FormData(this);
  formData.append('action', 'create_director');
  
  $.ajax({
    url: 'create_director.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(response) {
      $('#loadingOverlay').removeClass('show');
      saveBtn.prop('disabled', false).html(originalText);
      
      if (response.success) {
        showAlert(response.message, 'success');
        
        // Reset form after 2 seconds
        setTimeout(function() {
          $('#directorForm')[0].reset();
          $('#photoPreview').html('<i class="fas fa-user"></i>');
          window.location.href = 'create_user.php';
        }, 2000);
      } else {
        showAlert(response.message || 'Failed to create director account', 'danger');
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