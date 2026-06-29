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
  <title>Create User - Dan4Christ Institute</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      max-width: 1200px;
      margin: 0 auto;
    }
    
    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    
    /* Role Cards */
    .roles-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin-bottom: 40px;
    }
    
    .role-card {
      background: white;
      border-radius: 20px;
      padding: 40px 30px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      cursor: pointer;
      position: relative;
      overflow: hidden;
      text-decoration: none;
      display: block;
      color: inherit;
    }
    
    .role-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, var(--primary) 0%, var(--info) 100%);
      transform: scaleX(0);
      transition: transform 0.3s;
    }
    
    .role-card:hover::before {
      transform: scaleX(1);
    }
    
    .role-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.2);
      color: inherit;
    }
    
    .role-card.director {
      border-top: 5px solid var(--dark);
    }
    
    .role-card.staff {
      border-top: 5px solid var(--success);
    }
    
    .role-card.student {
      border-top: 5px solid var(--info);
    }
    
    .role-icon {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 45px;
      margin: 0 auto 25px;
      transition: all 0.3s;
    }
    
    .role-card.director .role-icon {
      background: linear-gradient(135deg, #5a5c69 0%, #3a3b45 100%);
      color: white;
    }
    
    .role-card.staff .role-icon {
      background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
      color: white;
    }
    
    .role-card.student .role-icon {
      background: linear-gradient(135deg, #36b9cc 0%, #2a99a9 100%);
      color: white;
    }
    
    .role-card:hover .role-icon {
      transform: scale(1.1) rotate(5deg);
    }
    
    .role-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 15px;
    }
    
    .role-description {
      color: var(--secondary);
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 25px;
    }
    
    .role-btn {
      padding: 12px 35px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }
    
    .role-card.director .role-btn {
      background: linear-gradient(135deg, #5a5c69 0%, #3a3b45 100%);
      color: white;
    }
    
    .role-card.staff .role-btn {
      background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
      color: white;
    }
    
    .role-card.student .role-btn {
      background: linear-gradient(135deg, #36b9cc 0%, #2a99a9 100%);
      color: white;
    }
    
    .role-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    /* Back Button */
    .back-btn-container {
      text-align: center;
      margin-top: 30px;
    }
    
    .btn-back {
      background: linear-gradient(135deg, var(--warning) 0%, #dda20a 100%);
      border: none;
      color: white;
      padding: 12px 35px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(246, 194, 62, 0.4);
      color: white;
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
      
      .roles-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .role-card {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>

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
      <i class="fas fa-user-plus"></i>
    </div>
    <h2>Create User Account</h2>
    <p>Select a role to create a new user account</p>
  </div>

  <!-- Role Cards -->
  <div class="roles-grid">
    <!-- Director Card -->
    <a href="director_user.php" class="role-card director">
      <div class="role-icon">
        <i class="fas fa-user-tie"></i>
      </div>
      <h3 class="role-title">Director</h3>
      <p class="role-description">
        Create a new director account with full administrative privileges and system access.
      </p>
      <button class="role-btn">
        <i class="fas fa-plus-circle"></i>
        Create Director
      </button>
    </a>

    <!-- Staff Card -->
    <a href="staff_user.php" class="role-card staff">
      <div class="role-icon">
        <i class="fas fa-chalkboard-teacher"></i>
      </div>
      <h3 class="role-title">Staff</h3>
      <p class="role-description">
        Create a new staff account for teachers and administrative personnel.
      </p>
      <button class="role-btn">
        <i class="fas fa-plus-circle"></i>
        Create Staff
      </button>
    </a>

    <!-- Student Card -->
    <a href="student_user.php" class="role-card student">
      <div class="role-icon">
        <i class="fas fa-user-graduate"></i>
      </div>
      <h3 class="role-title">Student</h3>
      <p class="role-description">
        Create a new student account with access to academic resources and services.
      </p>
      <button class="role-btn">
        <i class="fas fa-plus-circle"></i>
        Create Student
      </button>
    </a>
  </div>

  <!-- Back Button -->
  <div class="back-btn-container">
    <a href="director.php" class="btn-back">
      <i class="fas fa-arrow-left"></i>
      Back to Dashboard
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>