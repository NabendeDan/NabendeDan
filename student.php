<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Student') {
    header("Location: login.html");
    exit();
}

// Get session data
$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Student';
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '';
$regno = isset($_SESSION['regno']) ? $_SESSION['regno'] : '';
$photo = (!empty($_SESSION['photo']) && file_exists($_SESSION['photo'])) ? $_SESSION['photo'] : 'uploads/default.png';
$student_id = isset($_SESSION['student_id']) ? htmlspecialchars($_SESSION['student_id']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    :root {
      --primary-green: #198754;
      --dark-green: #146c43;
    }
    
    body {
      background-color: #f8f9fa;
      min-height: 100vh;
    }
    
    .navbar {
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .profile-img {
      border: 3px solid rgba(255,255,255,0.3);
      object-fit: cover;
    }
    
    .dashboard-card {
      transition: all 0.3s ease;
      border: none;
      border-radius: 15px;
      height: 100%;
      background: white;
    }
    
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
    
    .card-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 15px;
      font-size: 24px;
    }
    
    .icon-results { background: #e3f2fd; color: #1976d2; }
    .icon-payment { background: #e8f5e9; color: #388e3c; }
    .icon-profile { background: #fff3e0; color: #f57c00; }
    
    .btn-custom {
      border-radius: 8px;
      padding: 10px 25px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .modal-header {
      background: var(--primary-green);
      color: white;
    }
    
    .modal-header .btn-close {
      filter: brightness(0) invert(1);
    }
    
    .loading-spinner {
      display: none;
      text-align: center;
      padding: 40px;
    }
    
    .modal-content-area {
      min-height: 300px;
    }
    
    @media (max-width: 768px) {
      .navbar-brand {
        font-size: 1rem;
      }
      
      .dashboard-card {
        margin-bottom: 20px;
      }
      
      .card-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
      }
    }
    
    .welcome-banner {
      background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
      color: white;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 30px;
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
      color: white;
    }
    
    .user-name {
      font-weight: 600;
      font-size: 0.95rem;
    }
    
    .user-role {
      font-size: 0.75rem;
      opacity: 0.8;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-success">
  <div class="container-fluid">
    <span class="navbar-brand mb-0 h1">
      <i class="fas fa-graduation-cap me-2"></i>Student Dashboard
    </span>
    <div class="d-flex align-items-center">
      <div class="user-info me-3">
        <img src="<?php echo $photo; ?>" 
             alt="Profile" class="rounded-circle profile-img" 
             width="40" height="40"
             onerror="this.src='uploads/default.png'">
        <div class="d-none d-md-block">
          <div class="user-name"><?php echo $fullname; ?></div>
          <div class="user-role">Reg No: <?php echo htmlspecialchars($regno); ?></div>
        </div>
      </div>

      <a href="profile.php" class="btn btn-outline-light btn-sm me-2">
  <i class="fas fa-user-circle me-1"></i>My Profile
</a>

      <a href="logout.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
      </a>
      
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">
  <!-- Welcome Banner -->
  <div class="welcome-banner">
    <h4 class="mb-1">Welcome back, <?php echo explode(' ', $fullname)[0]; ?>! 👋</h4>
    <p class="mb-0 opacity-75">Manage your academic activities from here</p>
  </div>

  <!-- Dashboard Cards -->
  <div class="row g-4">
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card dashboard-card shadow h-100">
        <div class="card-body text-center p-4">
          <div class="card-icon icon-results mx-auto">
            <i class="fas fa-chart-bar"></i>
          </div>
          <h5 class="card-title mb-3">View Results</h5>
          <p class="text-muted mb-3">Check your academic performance and grades</p>
          <button class="btn btn-primary btn-custom" onclick="loadResults()">
            <i class="fas fa-eye me-2"></i>View Results
          </button>
        </div>
      </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card dashboard-card shadow h-100">
        <div class="card-body text-center p-4">
          <div class="card-icon icon-payment mx-auto">
            <i class="fas fa-credit-card"></i>
          </div>
          <h5 class="card-title mb-3">Pay Tuition</h5>
          <p class="text-muted mb-3">Make tuition payments and view history</p>
          <a href="paytuition.php" class="btn btn-success btn-custom text-white text-decoration-none">
            <i class="fas fa-money-bill-wave me-2"></i>Pay Tuition
          </a>
        </div>
      </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card dashboard-card shadow h-100">
        <div class="card-body text-center p-4">
          <div class="card-icon icon-profile mx-auto">
            <i class="fas fa-user-edit"></i>
          </div>
            </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for AJAX Content -->
<div class="modal fade" id="contentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Loading...</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="loading-spinner" id="modalLoader">
          <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Please wait...</p>
        </div>
        <div id="modalContent" class="modal-content-area">
          <!-- AJAX content will be loaded here -->
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Initialize Bootstrap Modal
const contentModal = new bootstrap.Modal(document.getElementById('contentModal'));

// Load Results via AJAX
function loadResults() {
  $('#modalTitle').text('Academic Results');
  $('#modalContent').html('');
  $('#modalLoader').show();
  contentModal.show();
  
  $.ajax({
    url: 'ajax_results.php',
    type: 'POST',
    data: { regno: '<?php echo $regno; ?>' },
    success: function(response) {
      $('#modalLoader').hide();
      $('#modalContent').html(response);
    },
    error: function(xhr, status, error) {
      $('#modalLoader').hide();
      $('#modalContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading results. Please try again.</div>');
    }
  });
}

// Load Profile Update via AJAX
function loadProfile() {
  $('#modalTitle').text('Update Bio Data');
  $('#modalContent').html('');
  $('#modalLoader').show();
  contentModal.show();
  
  $.ajax({
    url: 'ajax_profile.php',
    type: 'POST',
    data: { regno: '<?php echo $regno; ?>' },
    success: function(response) {
      $('#modalLoader').hide();
      $('#modalContent').html(response);
    },
    error: function(xhr, status, error) {
      $('#modalLoader').hide();
      $('#modalContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading profile. Please try again.</div>');
    }
  });
}
</script>

</body>
</html>