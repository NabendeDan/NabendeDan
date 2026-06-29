<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Staff') {
    header("Location: login.html");
    exit();
}

// Prevent undefined index errors
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Staff Member';
$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : 'uploads/default.png';

// Fetch students from database
$studentsQuery = "SELECT regno, fullname, dob, phone, district, course_id, department_id, year_of_study FROM students";
$students = $conn->query($studentsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Dashboard - Dan 4 Christ Institute  of Science & Technology </title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    :root {
      --primary: #667eea;
      --secondary: #764ba2;
      --accent: #f093fb;
      --dark: #2d3748;
      --light: #f7fafc;
    }
    
    * {
      font-family: 'Poppins', sans-serif;
    }
    
    body {
      background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      min-height: 100vh;
    }
    
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    /* Modern Navbar */
    .navbar {
      background: rgba(255, 255, 255, 0.95) !important;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      padding: 15px 0;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .profile-img-wrapper {
      position: relative;
      cursor: pointer;
      transition: transform 0.3s;
    }
    
    .profile-img-wrapper:hover {
      transform: scale(1.1);
    }
    
    .profile-img {
      border: 3px solid var(--primary);
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .photo-upload-overlay {
      position: absolute;
      bottom: 0;
      right: 0;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid white;
      cursor: pointer;
      transition: transform 0.3s;
    }
    
    .photo-upload-overlay:hover {
      transform: scale(1.2) rotate(15deg);
    }
    
    .upload-loading {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.6);
      border-radius: 50%;
      display: none;
      align-items: center;
      justify-content: center;
    }
    
    /* Modern Cards */
    .dashboard-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border: none;
      height: 100%;
      position: relative;
      overflow: hidden;
    }
    
    .dashboard-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }
    
    .dashboard-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }
    
    .card-icon {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 32px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .dashboard-card h5 {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 10px;
    }
    
    .dashboard-card p {
      color: #718096;
      font-size: 0.9rem;
      margin-bottom: 20px;
    }
    
    .btn-modern {
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 0.85rem;
      transition: all 0.3s;
      border: none;
      margin: 5px;
    }
    
    .btn-primary-modern {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-primary-modern:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
      color: white;
    }
    
    .btn-success-modern {
      background: linear-gradient(135deg, #11998e, #38ef7d);
      color: white;
      box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
    }
    
    .btn-success-modern:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(17, 153, 142, 0.6);
      color: white;
    }
    
    .btn-info-modern {
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      color: white;
      box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
    }
    
    .btn-info-modern:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(79, 172, 254, 0.6);
      color: white;
    }
    
    /* Search Section */
    .search-section {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      margin-top: 30px;
    }
    
    .search-section h4 {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 20px;
    }
    
    .search-input {
      border-radius: 50px;
      padding: 15px 25px;
      border: 2px solid #e2e8f0;
      transition: all 0.3s;
    }
    
    .search-input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .search-btn {
      border-radius: 50px;
      padding: 15px 35px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border: none;
      color: white;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .search-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    /* Table Section */
    .table-section {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      margin-top: 30px;
    }
    
    .table-section h4 {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 20px;
    }
    
    .table {
      border-radius: 10px;
      overflow: hidden;
    }
    
    .table thead {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
    }
    
    .table thead th {
      border: none;
      padding: 15px;
      font-weight: 600;
    }
    
    .table tbody tr {
      transition: all 0.3s;
    }
    
    .table tbody tr:hover {
      background: rgba(102, 126, 234, 0.05);
      transform: scale(1.01);
    }
    
    .table tbody td {
      padding: 15px;
      vertical-align: middle;
    }
    
    .refresh-btn {
      background: linear-gradient(135deg, #11998e, #38ef7d);
      border: none;
      color: white;
      padding: 8px 20px;
      border-radius: 50px;
      font-size: 0.85rem;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .refresh-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
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
      padding: 15px 25px;
      border-radius: 15px;
      margin-bottom: 10px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      animation: slideIn 0.4s ease;
      backdrop-filter: blur(10px);
    }
    
    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    /* Loading Animation */
    .spinner-custom {
      width: 40px;
      height: 40px;
      border: 4px solid rgba(255,255,255,0.3);
      border-top-color: white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .dashboard-card {
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body>

<!-- Hidden file input for photo upload -->
<input type="file" id="photoUpload" accept="image/*" style="display: none;">

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<!-- Modern Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="#">
      <i class="fas fa-university me-2"></i>Dan 4 Christ Institute of science & Technology
    </a>
    <div class="d-flex align-items-center">
      <div class="profile-img-wrapper me-3">
        <img src="<?php echo htmlspecialchars($photo); ?>" 
             id="profileImg"
             class="rounded-circle profile-img" 
             width="45" 
             height="45" 
             alt="Profile"
             onerror="this.src='uploads/default.png'">
        <div class="photo-upload-overlay" title="Change photo">
          <i class="fas fa-camera" style="color: white; font-size: 10px;"></i>
        </div>
        <div class="upload-loading" id="uploadLoading">
          <div class="spinner-custom"></div>
        </div>
      </div>
      <span class="text-dark fw-semibold d-none d-md-block me-3">
        <?php echo htmlspecialchars($fullname); ?>
      </span>
      <a href="profile.php" class="btn btn-outline-light btn-sm me-2">
  <i class="fas fa-user-circle me-1"></i>My Profile
</a>
      <a href="logout.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<div class="container mt-5 mb-5">
  <!-- Welcome Message -->
  <div class="text-center mb-5">
    <h1 class="display-5 fw-bold text-white mb-2" style="text-shadow: 2px 2px 10px rgba(0,0,0,0.2);">
      Welcome back, <?php echo htmlspecialchars(explode(' ', $fullname)[0]); ?>! 👋
    </h1>
    <p class="text-white-50 fs-5">Manage your institute efficiently</p>
  </div>

  <!-- Dashboard Cards -->
  <div class="row g-4 mb-5">
    <!-- Staff Control -->
    <div class="col-md-4">
      <div class="dashboard-card text-center">
        <div class="card-icon">
          <i class="fas fa-user-cog"></i>
        </div>
        <h5>Staff Control</h5>
        <p>Manage staff and student accounts with ease</p>
        <a href="staff_user.php" class="btn btn-modern btn-primary-modern">
          <i class="fas fa-user-plus me-2"></i>Add New Staff
        </a>
        <a href="student_user.php" class="btn btn-modern btn-success-modern">
          <i class="fas fa-user-graduate me-2"></i>Add New Students
        </a>
      </div>
    </div>

    <!-- Manage Students -->
    <div class="col-md-4">
      <div class="dashboard-card text-center">
        <div class="card-icon" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
          <i class="fas fa-users"></i>
        </div>
        <h5>Manage Students</h5>
        <p>View and manage all student records</p>
        <a href="#studentsSection" class="btn btn-modern btn-success-modern" id="viewStudentsBtn">
          <i class="fas fa-database me-2"></i>View Database
        </a>
      </div>
    </div>

    <!-- Enter Marks -->
    <div class="col-md-4">
      <div class="dashboard-card text-center">
        <div class="card-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
          <i class="fas fa-chart-line"></i>
        </div>
        <h5>Enter Marks</h5>
        <p>Record student marks for each course unit</p>
        <a href="enter_marks.php" class="btn btn-modern btn-info-modern">
          <i class="fas fa-edit me-2"></i>Enter Marks
        </a>
      </div>
    </div>
  </div>

  <!-- Search Student Section -->
  <div class="search-section">
    <h4><i class="fas fa-search me-2 text-primary"></i>Search Student by RegNo</h4>
    <form id="searchForm" class="mb-3">
      <div class="input-group">
        <input type="text" name="regno" id="regno" class="form-control search-input" placeholder="Enter Student Registration Number..." required>
        <button type="submit" class="btn search-btn">
          <i class="fas fa-search me-2"></i>Search
        </button>
      </div>
    </form>
    <div id="searchResults"></div>
  </div>

  <!-- Students Database Section -->
  <div id="studentsSection" class="table-section d-none">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4><i class="fas fa-database me-2 text-success"></i>Students Database</h4>
      <button class="btn refresh-btn" id="refreshStudentsBtn">
        <i class="fas fa-sync-alt me-2"></i>Refresh Data
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>RegNo</th>
            <th>Full Name</th>
            <th>DOB</th>
            <th>Phone</th>
            <th>District</th>
            <th>Course ID</th>
            <th>Dept ID</th>
            <th>Year</th>
          </tr>
        </thead>
        <tbody id="studentsTableBody">
          <?php if($students && $students->num_rows > 0): ?>
            <?php while($row = $students->fetch_assoc()): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($row['regno']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                <td><?php echo htmlspecialchars($row['dob']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['district']); ?></td>
                <td><span class="badge bg-primary"><?php echo htmlspecialchars($row['course_id']); ?></span></td>
                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['department_id']); ?></span></td>
                <td><span class="badge bg-info text-dark">Year <?php echo htmlspecialchars($row['year_of_study']); ?></span></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No student records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  // Toggle students section with smooth scroll
  $("#viewStudentsBtn").on("click", function(e){
    e.preventDefault();
    $("#studentsSection").toggleClass("d-none");
    $('html, body').animate({ 
      scrollTop: $("#studentsSection").offset().top - 100 
    }, 800, 'swing');
  });

  // Refresh students data via AJAX
  $("#refreshStudentsBtn").on("click", function(){
    var btn = $(this);
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...');
    
    $.ajax({
      url: "refresh_students_data.php",
      method: "GET",
      dataType: "json",
      success: function(response){
        if(response.success){
          updateStudentsTable(response.data);
          showAlert('Students data refreshed successfully!', 'success');
        } else {
          showAlert('Error: ' + response.message, 'danger');
        }
      },
      error: function(){
        showAlert('Failed to refresh data', 'danger');
      },
      complete: function(){
        btn.prop('disabled', false).html(originalHtml);
      }
    });
  });

  // Search form AJAX
  $("#searchForm").on("submit", function(e){
    e.preventDefault();
    var regno = $("#regno").val();
    
    if(!regno){
      showAlert('Please enter a registration number', 'warning');
      return;
    }
    
    var btn = $(this).find('button[type="submit"]');
    var originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Searching...');
    
    $.ajax({
      url: "search_student.php",
      method: "POST",
      data: { regno: regno },
      success: function(data){
        $("#searchResults").html(data);
        btn.prop('disabled', false).html(originalHtml);
      },
      error: function(){
        $("#searchResults").html('<div class="alert alert-danger mt-3">Error searching student</div>');
        btn.prop('disabled', false).html(originalHtml);
      }
    });
  });

  // Photo upload functionality
  $('#profileImg, .photo-upload-overlay').on('click', function() {
    $('#photoUpload').click();
  });
  
  $('#photoUpload').on('change', function() {
    const file = this.files[0];
    
    if (!file) return;
    
    // Validate file type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
      showAlert('Please select an image file (JPG, PNG, GIF, WEBP)', 'warning');
      return;
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      showAlert('File too large. Maximum size is 5MB', 'warning');
      return;
    }
    
    // Show preview immediately
    const reader = new FileReader();
    reader.onload = function(e) {
      $('#profileImg').attr('src', e.target.result);
    };
    reader.readAsDataURL(file);
    
    // Show loading
    $('#uploadLoading').css('display', 'flex');
    
    // Upload via AJAX
    const formData = new FormData();
    formData.append('photo', file);
    
    $.ajax({
      url: 'upload_photo.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        $('#uploadLoading').hide();
        
        if (response.success) {
          $('#profileImg').attr('src', response.photo);
          showAlert('Photo uploaded successfully!', 'success');
        } else {
          showAlert(response.message || 'Upload failed', 'danger');
          location.reload();
        }
      },
      error: function() {
        $('#uploadLoading').hide();
        showAlert('Upload error. Please try again.', 'danger');
        location.reload();
      }
    });
  });
});

// Update Students Table
function updateStudentsTable(data) {
  let html = '';
  if (data.length === 0) {
    html = '<tr><td colspan="8" class="text-center text-muted py-4">No student records found.</td></tr>';
  } else {
    data.forEach(function(row) {
      html += `
        <tr>
          <td><strong>${escapeHtml(row.regno)}</strong></td>
          <td>${escapeHtml(row.fullname)}</td>
          <td>${escapeHtml(row.dob)}</td>
          <td>${escapeHtml(row.phone)}</td>
          <td>${escapeHtml(row.district)}</td>
          <td><span class="badge bg-primary">${escapeHtml(row.course_id)}</span></td>
          <td><span class="badge bg-secondary">${escapeHtml(row.department_id)}</span></td>
          <td><span class="badge bg-info text-dark">Year ${escapeHtml(row.year_of_study)}</span></td>
        </tr>
      `;
    });
  }
  $('#studentsTableBody').html(html);
}

// Show Alert
function showAlert(message, type) {
  const icons = {
    success: 'fa-check-circle',
    danger: 'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle'
  };
  
  const colors = {
    success: 'rgba(17, 153, 142, 0.95)',
    danger: 'rgba(231, 76, 60, 0.95)',
    warning: 'rgba(241, 196, 15, 0.95)',
    info: 'rgba(52, 152, 219, 0.95)'
  };
  
  const alertHtml = `
    <div class="custom-alert" style="background: ${colors[type]}; color: white;">
      <i class="fas ${icons[type]} me-2"></i>
      <span>${message}</span>
      <button type="button" class="btn-close btn-close-white float-end ms-3" onclick="this.parentElement.remove()"></button>
    </div>
  `;
  
  $('#alertContainer').html(alertHtml);
  
  setTimeout(function() {
    $('.custom-alert').fadeOut(300, function() {
      $(this).remove();
    });
  }, 4000);
}

// Escape HTML
function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>