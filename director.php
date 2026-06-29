<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    header("Location: login.php");
    exit();
}

// Safe defaults
$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : 'uploads/default.png';
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Director';

// Fetch counts
$studentsCount = 0;
$staffCount = 0;
$departmentsCount = 0;
$coursesCount = 0;

if ($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM students");
    if ($result) $studentsCount = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM staff");
    if ($result) $staffCount = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM departments");
    if ($result) $departmentsCount = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM courses");
    if ($result) $coursesCount = $result->fetch_assoc()['count'];
}

// Fetch students
$studentsQuery = "SELECT regno, fullname, dob, phone, district, course_id, department_id, year_of_study FROM students ORDER BY fullname LIMIT 10";
$students = $conn->query($studentsQuery);

// Fetch staff
$staffQuery = "SELECT staff_id, fullname, dob, phone, district, department_id, username FROM staff ORDER BY fullname LIMIT 10";
$staff = $conn->query($staffQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Director Dashboard - Dan_4Christ Institute of Science & Technology</title>
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
    }
    
    /* Navbar */
    .navbar {
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 15px 0;
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
      cursor: pointer;
    }
    
    .profile-img:hover {
      transform: scale(1.15);
      box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    }
    
    .profile-name {
      color: white;
      font-weight: 500;
    }
    
    .profile-upload-wrapper {
      position: relative;
      display: inline-block;
    }
    
    .camera-overlay {
      position: absolute;
      bottom: 0;
      right: 0;
      background: linear-gradient(135deg, #4e73df, #224abe);
      width: 22px;
      height: 22px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid white;
      cursor: pointer;
      transition: transform 0.3s;
    }
    
    .camera-overlay:hover {
      transform: scale(1.2);
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
    
    /* Sidebar */
    .sidebar {
      background: white;
      min-height: calc(100vh - 80px);
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      padding: 30px 20px;
      position: sticky;
      top: 80px;
    }
    
    .sidebar-title {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #e3e6f0;
    }
    
    .nav-link {
      color: var(--dark);
      padding: 12px 20px;
      margin-bottom: 8px;
      border-radius: 10px;
      transition: all 0.3s;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .nav-link:hover {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      color: white;
      transform: translateX(5px);
    }
    
    .nav-link.active-link {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      color: white;
    }
    
    .nav-link i {
      width: 25px;
      text-align: center;
    }
    
    /* Main Content */
    .main-content {
      padding: 30px;
    }
    
    .welcome-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 20px;
      padding: 30px;
      color: white;
      margin-bottom: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .welcome-card h2 {
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    /* Stats Cards */
    .stats-row {
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: all 0.3s;
      border-left: 4px solid;
      margin-bottom: 20px;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .stat-card.students { border-left-color: var(--success); }
    .stat-card.staff { border-left-color: var(--primary); }
    .stat-card.departments { border-left-color: var(--warning); }
    .stat-card.courses { border-left-color: var(--info); }
    
    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      margin-bottom: 15px;
    }
    
    .stat-card.students .stat-icon { background: rgba(28, 200, 138, 0.1); color: var(--success); }
    .stat-card.staff .stat-icon { background: rgba(78, 115, 223, 0.1); color: var(--primary); }
    .stat-card.departments .stat-icon { background: rgba(246, 194, 62, 0.1); color: var(--warning); }
    .stat-card.courses .stat-icon { background: rgba(54, 185, 204, 0.1); color: var(--info); }
    
    .stat-value {
      font-size: 2rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 5px;
    }
    
    .stat-label {
      color: var(--secondary);
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    /* Section Cards */
    .section-card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      display: none;
      animation: fadeIn 0.5s;
    }
    
    .section-card.active {
      display: block;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 2px solid #e3e6f0;
    }
    
    .section-title {
      font-weight: 700;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .btn-refresh {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      border: none;
      color: white;
      padding: 10px 25px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .btn-refresh:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
    }
    
    .btn-refresh:active .fa-sync-alt {
      animation: spin 1s;
    }
    
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    /* Table */
    .table-container {
      max-height: 500px;
      overflow-y: auto;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .table {
      margin-bottom: 0;
      cursor: pointer;
    }
    
    .table thead th {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      color: white;
      font-weight: 600;
      border: none;
      padding: 15px;
      position: sticky;
      top: 0;
    }
    
    .table tbody td {
      padding: 12px 15px;
      vertical-align: middle;
      border-bottom: 1px solid #e3e6f0;
    }
    
    .table tbody tr:hover {
      background: #f8f9fc;
      cursor: pointer;
    }
    
    /* Modal Styles */
    .modal-header {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      color: white;
    }
    
    .modal-header .btn-close {
      filter: brightness(0) invert(1);
    }
    
    .student-info-card {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
    }
    
    .tab-content {
      padding: 20px 0;
    }
    
    .results-table th {
      background-color: var(--light);
      font-weight: 600;
    }
    
    .badge-grade {
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: 600;
    }
    
    .payment-card {
      border-left: 4px solid var(--success);
      margin-bottom: 15px;
    }
    
    /* Action Buttons in Modal */
    .btn-edit-student {
      background: linear-gradient(135deg, var(--warning) 0%, #dda20a 100%);
      border: none;
      color: white;
      font-weight: 600;
    }
    
    .btn-edit-student:hover {
      background: linear-gradient(135deg, #dda20a 0%, #b8860b 100%);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(246, 194, 62, 0.4);
    }
    
    .btn-delete-student {
      background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);
      border: none;
      color: white;
      font-weight: 600;
    }
    
    .btn-delete-student:hover {
      background: linear-gradient(135deg, #c0392b 0%, #922b21 100%);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(231, 74, 59, 0.4);
    }
    
    /* Delete Confirmation Modal */
    .delete-confirm-icon {
      font-size: 80px;
      color: var(--danger);
      animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-10px); }
      75% { transform: translateX(10px); }
    }
    
    /* Print Styles */
    @media print {
      .no-print { display: none !important; }
      .modal-content { border: none; box-shadow: none; }
      body { background: white; }
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
      border-top-color: var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    /* Alert */
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
    
    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    
    .action-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: all 0.3s;
      cursor: pointer;
      text-decoration: none;
      color: var(--dark);
    }
    
    .action-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
      color: var(--primary);
    }
    
    .action-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      margin: 0 auto 15px;
    }
    
    .action-title {
      font-weight: 600;
      margin-bottom: 5px;
    }
    
    .action-desc {
      font-size: 0.85rem;
      color: var(--secondary);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        left: -100%;
        top: 80px;
        width: 280px;
        z-index: 1000;
        transition: left 0.3s;
      }
      
      .sidebar.show {
        left: 0;
      }
      
      .main-content {
        padding: 15px;
      }
      
      .profile-name {
        display: none;
      }
      
      .modal-footer .btn {
        margin-bottom: 5px;
      }
    }
  </style>
</head>
<body>

<!-- Hidden file input for photo upload -->
<input type="file" id="photoUpload" accept="image/*" style="display: none;">

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner"></div>
  <p class="text-muted">Loading data...</p>
</div>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<!-- Navbar -->
<nav class="navbar navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="#">
      <i class="fas fa-university me-2"></i>Dan 4 Christ Institute of Science & Technology
    </a>
    <div class="profile-section">
      <div class="profile-upload-wrapper">
        <img src="<?php echo htmlspecialchars($photo); ?>" 
             id="profileImg" 
             alt="Profile" 
             class="rounded-circle profile-img" 
             width="45" 
             height="45"
             onerror="this.src='uploads/default.png'"
             title="Click to change photo">
        
        <!-- Camera icon overlay -->
        <div class="camera-overlay" title="Change photo">
          <i class="fas fa-camera" style="color: white; font-size: 10px;"></i>
        </div>
        
        <!-- Loading indicator -->
        <div class="upload-loading" id="uploadLoading">
          <div class="spinner-border spinner-border-sm text-white" role="status"></div>
        </div>
      </div>
      
      <span class="profile-name d-none d-md-inline">
        <?php echo htmlspecialchars($fullname); ?>
      </span>
      <a href="profile.php" class="btn btn-outline-light btn-sm me-2">
  <i class="fas fa-user-circle me-1"></i>My Profile
</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2">
      <div class="sidebar">
        <h5 class="sidebar-title">
          <i class="fas fa-bars me-2"></i>Navigation
        </h5>
        <ul class="nav flex-column" id="sidebarNav">
          <li class="nav-item">
            <a href="#" class="nav-link active-link" data-section="dashboard">
              <i class="fas fa-tachometer-alt"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" data-section="studentsSection">
              <i class="fas fa-user-graduate"></i>
              <span>Students</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" data-section="staffSection">
              <i class="fas fa-chalkboard-teacher"></i>
              <span>Staff</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="create_user.php" class="nav-link">
              <i class="fas fa-user-plus"></i>
              <span>Create User</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="create_payment_code.php" class="nav-link">
              <i class="fas fa-barcode"></i>
              <span>Payment Code</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="finance.php" class="nav-link">
              <i class="fas fa-wallet"></i>
              <span>Finance</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="enter_marks.php" class="nav-link">
              <i class="fas fa-pen-to-square"></i>
              <span>Enter Marks</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="account.php" class="nav-link">
              <i class="fas fa-user-gear"></i>
              <span>Account</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="create_departments.php" class="nav-link">
              <i class="fas fa-building"></i>
              <span>Departments</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="courses.php" class="nav-link">
              <i class="fas fa-book"></i>
              <span>Courses</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 col-lg-10">
      <div class="main-content">
        
        <!-- Dashboard Overview -->
        <div id="dashboard" class="section-card active">
          <div class="welcome-card">
            <h2>Welcome back, <?php echo htmlspecialchars(explode(' ', $fullname)[0]); ?>! 👋</h2>
            <p class="mb-0">Here's what's happening at Dan4Christ Institute of sccience & Technology today.</p>
          </div>

          <!-- Stats Row -->
          <div class="row stats-row">
            <div class="col-md-6 col-lg-3">
              <div class="stat-card students">
                <div class="stat-icon">
                  <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-value" id="statStudents"><?php echo $studentsCount; ?></div>
                <div class="stat-label">Total Students</div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="stat-card staff">
                <div class="stat-icon">
                  <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-value" id="statStaff"><?php echo $staffCount; ?></div>
                <div class="stat-label">Total Staff</div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="stat-card departments">
                <div class="stat-icon">
                  <i class="fas fa-building"></i>
                </div>
                <div class="stat-value" id="statDepartments"><?php echo $departmentsCount; ?></div>
                <div class="stat-label">Departments</div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3">
              <div class="stat-card courses">
                <div class="stat-icon">
                  <i class="fas fa-book"></i>
                </div>
                <div class="stat-value" id="statCourses"><?php echo $coursesCount; ?></div>
                <div class="stat-label">Courses</div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <h5 class="mb-3">Quick Actions</h5>
          <div class="quick-actions">
            <a href="create_user.php" class="action-card">
              <div class="action-icon">
                <i class="fas fa-user-plus"></i>
              </div>
              <div class="action-title">Add User</div>
              <div class="action-desc">Create new account</div>
            </a>
            <a href="#" class="action-card" onclick="showSection('studentsSection'); return false;">
              <div class="action-icon">
                <i class="fas fa-users"></i>
              </div>
              <div class="action-title">View Students</div>
              <div class="action-desc">Manage students</div>
            </a>
            <a href="#" class="action-card" onclick="showSection('staffSection'); return false;">
              <div class="action-icon">
                <i class="fas fa-chalkboard-teacher"></i>
              </div>
              <div class="action-title">View Staff</div>
              <div class="action-desc">Manage staff</div>
            </a>
            <a href="finance.php" class="action-card">
              <div class="action-icon">
                <i class="fas fa-chart-line"></i>
              </div>
              <div class="action-title">Finance</div>
              <div class="action-desc">View reports</div>
            </a>
          </div>
        </div>

        <!-- Students Section -->
        <div id="studentsSection" class="section-card">
          <div class="section-header">
            <h4 class="section-title">
              <i class="fas fa-user-graduate text-success"></i>
              Students Database
            </h4>
            <button class="btn-refresh" onclick="refreshData('students')">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>
          <div class="table-container">
            <table class="table">
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
                <?php if($students): ?>
                  <?php while($row = $students->fetch_assoc()): ?>
                    <tr onclick="viewStudentDetails('<?php echo htmlspecialchars($row['regno']); ?>')">
                      <td><?php echo htmlspecialchars($row['regno']); ?></td>
                      <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                      <td><?php echo htmlspecialchars($row['dob']); ?></td>
                      <td><?php echo htmlspecialchars($row['phone']); ?></td>
                      <td><?php echo htmlspecialchars($row['district']); ?></td>
                      <td><?php echo htmlspecialchars($row['course_id']); ?></td>
                      <td><?php echo htmlspecialchars($row['department_id']); ?></td>
                      <td><?php echo htmlspecialchars($row['year_of_study']); ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="8" class="text-center">No students found</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Staff Section -->
        <div id="staffSection" class="section-card">
          <div class="section-header">
            <h4 class="section-title">
              <i class="fas fa-chalkboard-teacher text-primary"></i>
              Staff Database
            </h4>
            <button class="btn-refresh" onclick="refreshData('staff')">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>
          <div class="table-container">
            <table class="table">
              <thead>
                <tr>
                  <th>Staff ID</th>
                  <th>Full Name</th>
                  <th>DOB</th>
                  <th>Phone</th>
                  <th>District</th>
                  <th>Dept ID</th>
                  <th>Username</th>
                </tr>
              </thead>
              <tbody id="staffTableBody">
                <?php if($staff): ?>
                  <?php while($row = $staff->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['staff_id']); ?></td>
                      <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                      <td><?php echo htmlspecialchars($row['dob']); ?></td>
                      <td><?php echo htmlspecialchars($row['phone']); ?></td>
                      <td><?php echo htmlspecialchars($row['district']); ?></td>
                      <td><?php echo htmlspecialchars($row['department_id']); ?></td>
                      <td><?php echo htmlspecialchars($row['username']); ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="7" class="text-center">No staff found</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-user-graduate me-2"></i>Student Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="studentDetailsContent">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading student details...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer no-print">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i>Close
        </button>
        <button type="button" class="btn btn-edit-student" onclick="editStudentFromModal()">
          <i class="fas fa-edit me-2"></i>Edit
        </button>
        <button type="button" class="btn btn-delete-student" onclick="deleteStudentFromModal()">
          <i class="fas fa-trash me-2"></i>Delete
        </button>
        <button type="button" class="btn btn-info" onclick="printStudentDetails()">
          <i class="fas fa-print me-2"></i>Print
        </button>
        <button type="button" class="btn btn-success" onclick="exportToCSV()">
          <i class="fas fa-file-csv me-2"></i>Export CSV
        </button>
        <button type="button" class="btn btn-primary" onclick="exportToExcel()">
          <i class="fas fa-file-excel me-2"></i>Export Excel
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);">
        <h5 class="modal-title text-white">
          <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: brightness(0) invert(1);"></button>
      </div>
      <div class="modal-body text-center py-4">
        <i class="fas fa-exclamation-circle delete-confirm-icon"></i>
        <h4 class="mt-3 mb-3">Are you absolutely sure?</h4>
        <p class="text-muted mb-2">You are about to delete:</p>
        <div class="alert alert-light border">
          <p class="mb-1"><strong>Name:</strong> <span id="deleteStudentName"></span></p>
          <p class="mb-0"><strong>RegNo:</strong> <span id="deleteStudentRegno"></span></p>
        </div>
        <div class="alert alert-warning mt-3 mb-0">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <strong>Warning:</strong> This will permanently delete:
          <ul class="text-start mt-2 mb-0">
            <li>All academic results</li>
            <li>All payment records</li>
            <li>All payment codes</li>
            <li>The student account itself</li>
          </ul>
          <p class="mt-2 mb-0"><strong>This action cannot be undone!</strong></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i>Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="confirmDeleteStudent()">
          <i class="fas fa-trash me-2"></i>Yes, Delete Permanently
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let currentStudentData = {};
let currentResultsData = [];
let studentToDelete = null;

// Show specific section
function showSection(sectionId) {
  document.querySelectorAll('.section-card').forEach(card => {
    card.classList.remove('active');
  });
  
  document.getElementById(sectionId).classList.add('active');
  
  document.querySelectorAll('.nav-link').forEach(link => {
    link.classList.remove('active-link');
    if (link.dataset.section === sectionId) {
      link.classList.add('active-link');
    }
  });
}

// Navigation click handler
$(document).ready(function() {
  $('#sidebarNav .nav-link[data-section]').on('click', function(e) {
    e.preventDefault();
    var section = $(this).data('section');
    showSection(section);
  });
  
  // Photo upload functionality
  $('#profileImg, .camera-overlay').on('click', function() {
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

// View Student Details
function viewStudentDetails(regno) {
  const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
  modal.show();
  
  $('#studentDetailsContent').html(`
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading student details...</p>
    </div>
  `);
  
  $.ajax({
    url: 'fetch_student_details.php',
    type: 'POST',
    data: { regno: regno },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        currentStudentData = response.student;
        currentResultsData = response.results || [];
        displayStudentDetails(response.student, response.results || [], response.payments || []);
      } else {
        $('#studentDetailsContent').html('<div class="alert alert-danger">' + response.message + '</div>');
      }
    },
    error: function() {
      $('#studentDetailsContent').html('<div class="alert alert-danger">Error loading student details</div>');
    }
  });
}

// Edit Student from Modal
function editStudentFromModal() {
  if (!currentStudentData || !currentStudentData.regno) {
    showAlert('No student data available', 'warning');
    return;
  }
  
  // Close the details modal first
  bootstrap.Modal.getInstance(document.getElementById('studentDetailsModal')).hide();
  
  // Redirect to edit page
  window.location.href = 'edit_student.php?regno=' + encodeURIComponent(currentStudentData.regno);
}

// Delete Student from Modal
function deleteStudentFromModal() {
  if (!currentStudentData || !currentStudentData.regno) {
    showAlert('No student data available', 'warning');
    return;
  }
  
  studentToDelete = {
    regno: currentStudentData.regno,
    fullname: currentStudentData.fullname
  };
  
  // Populate delete confirmation modal
  $('#deleteStudentName').text(studentToDelete.fullname);
  $('#deleteStudentRegno').text(studentToDelete.regno);
  
  // Close details modal and open delete confirmation
  bootstrap.Modal.getInstance(document.getElementById('studentDetailsModal')).hide();
  
  setTimeout(function() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
  }, 300);
}

// Confirm Delete Student
function confirmDeleteStudent() {
  if (!studentToDelete) return;
  
  const btn = $('#confirmDeleteBtn');
  const originalText = btn.html();
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
  
  $.ajax({
    url: 'delete_student.php',
    type: 'POST',
    data: { regno: studentToDelete.regno },
    dataType: 'json',
    success: function(response) {
      btn.prop('disabled', false).html(originalText);
      
      if (response.success) {
        showAlert(response.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
        studentToDelete = null;
        
        // Refresh data after 1.5 seconds
        setTimeout(function() {
          refreshData('students');
        }, 1500);
      } else {
        showAlert(response.message || 'Failed to delete student', 'danger');
      }
    },
    error: function(xhr, status, error) {
      btn.prop('disabled', false).html(originalText);
      showAlert('Error deleting student: ' + error, 'danger');
    }
  });
}

// Display Student Details
function displayStudentDetails(student, results, payments) {
  let resultsHtml = '';
  let totalScore = 0;
  let totalUnits = 0;
  let totalGradePoints = 0;
  
  if (results.length > 0) {
    // Group by year and semester
    const grouped = {};
    results.forEach(r => {
      const key = `${r.year}-Sem${r.semester}`;
      if (!grouped[key]) grouped[key] = [];
      grouped[key].push(r);
    });
    
    Object.keys(grouped).sort().forEach(key => {
      const [year, sem] = key.split('-Sem');
      const units = grouped[key];
      
      resultsHtml += `
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Year ${year} - Semester ${sem}</h6>
          </div>
          <div class="card-body">
            <table class="table table-sm results-table">
              <thead>
                <tr>
                  <th>Unit Name</th>
                  <th class="text-center">Score</th>
                  <th class="text-center">Grade</th>
                  <th class="text-center">Points</th>
                </tr>
              </thead>
              <tbody>
      `;
      
      let semTotal = 0;
      let semUnits = 0;
      let semPoints = 0;
      
      units.forEach(unit => {
        const score = parseFloat(unit.score);
        const grade = getGrade(score);
        const points = getGradePoints(score);
        
        totalScore += score;
        totalUnits++;
        totalGradePoints += points;
        
        semTotal += score;
        semUnits++;
        semPoints += points;
        
        resultsHtml += `
          <tr>
            <td>${unit.unit_name || 'Unit ' + unit.unit_id}</td>
            <td class="text-center"><span class="badge bg-${getGradeClass(score)}">${score}%</span></td>
            <td class="text-center"><span class="badge bg-${getGradeClass(score)}">${grade}</span></td>
            <td class="text-center"><strong>${points.toFixed(1)}</strong></td>
          </tr>
        `;
      });
      
      const semAvg = semUnits > 0 ? (semTotal / semUnits).toFixed(2) : 0;
      const semGPA = semUnits > 0 ? (semPoints / semUnits).toFixed(2) : 0;
      
      resultsHtml += `
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <th colspan="2" class="text-end">Semester Average:</th>
                  <th class="text-center">${semAvg}%</th>
                  <th class="text-center">GPA: ${semGPA}</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      `;
    });
  } else {
    resultsHtml = '<p class="text-muted text-center">No results available</p>';
  }
  
  let paymentsHtml = '';
  if (payments.length > 0) {
    payments.forEach(payment => {
      paymentsHtml += `
        <div class="card payment-card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <small class="text-muted">Payment ID</small>
                <p class="mb-0"><strong>#${payment.payment_id}</strong></p>
              </div>
              <div class="col-md-3">
                <small class="text-muted">Amount</small>
                <p class="mb-0 text-success"><strong>UGX ${parseFloat(payment.amount).toLocaleString()}</strong></p>
              </div>
              <div class="col-md-3">
                <small class="text-muted">Purpose</small>
                <p class="mb-0">${payment.purpose || 'Tuition'}</p>
              </div>
              <div class="col-md-3">
                <small class="text-muted">Date</small>
                <p class="mb-0">${payment.date_paid || 'N/A'}</p>
              </div>
            </div>
          </div>
        </div>
      `;
    });
  } else {
    paymentsHtml = '<p class="text-muted text-center">No payment records found</p>';
  }
  
  const cgpa = totalUnits > 0 ? (totalGradePoints / totalUnits).toFixed(2) : 0;
  const overallAvg = totalUnits > 0 ? (totalScore / totalUnits).toFixed(2) : 0;
  
  $('#studentDetailsContent').html(`
    <div class="student-info-card">
      <div class="row">
        <div class="col-md-6">
          <h5 class="mb-3"><i class="fas fa-user me-2"></i>Personal Information</h5>
          <p><strong>Name:</strong> ${student.fullname}</p>
          <p><strong>Registration No:</strong> ${student.regno}</p>
          <p><strong>Date of Birth:</strong> ${student.dob || 'N/A'}</p>
          <p><strong>Phone:</strong> ${student.phone || 'N/A'}</p>
        </div>
        <div class="col-md-6">
          <h5 class="mb-3"><i class="fas fa-graduation-cap me-2"></i>Academic Information</h5>
          <p><strong>Course ID:</strong> ${student.course_id || 'N/A'}</p>
          <p><strong>Department ID:</strong> ${student.department_id || 'N/A'}</p>
          <p><strong>Year of Study:</strong> Year ${student.year_of_study || 'N/A'}</p>
          <p><strong>District:</strong> ${student.district || 'N/A'}</p>
        </div>
      </div>
    </div>
    
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#results" type="button">
          <i class="fas fa-chart-bar me-2"></i>Academic Results
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payments" type="button">
          <i class="fas fa-wallet me-2"></i>Payment History
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#summary" type="button">
          <i class="fas fa-chart-pie me-2"></i>Summary
        </button>
      </li>
    </ul>
    
    <div class="tab-content">
      <div class="tab-pane fade show active" id="results">
        ${resultsHtml}
      </div>
      <div class="tab-pane fade" id="payments">
        ${paymentsHtml}
      </div>
      <div class="tab-pane fade" id="summary">
        <div class="row">
          <div class="col-md-6">
            <div class="card bg-primary text-white mb-3">
              <div class="card-body text-center">
                <h3>${cgpa}</h3>
                <p class="mb-0">CGPA (out of 5.0)</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card bg-success text-white mb-3">
              <div class="card-body text-center">
                <h3>${overallAvg}%</h3>
                <p class="mb-0">Overall Average</p>
              </div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <h6>Performance Classification:</h6>
            <p class="mb-0">
              ${getPerformanceClass(cgpa)}
            </p>
          </div>
        </div>
      </div>
    </div>
  `);
}

// Helper Functions
function getGrade(score) {
  if (score >= 70) return 'A';
  if (score >= 60) return 'B';
  if (score >= 50) return 'C';
  if (score >= 40) return 'D';
  return 'F';
}

function getGradePoints(score) {
  if (score >= 70) return 5.0;
  if (score >= 60) return 4.0;
  if (score >= 50) return 3.0;
  if (score >= 40) return 2.0;
  return 0.0;
}

function getGradeClass(score) {
  if (score >= 70) return 'success';
  if (score >= 60) return 'primary';
  if (score >= 50) return 'info';
  if (score >= 40) return 'warning';
  return 'danger';
}

function getPerformanceClass(cgpa) {
  if (cgpa >= 4.5) return '<span class="badge bg-success fs-6">First Class</span>';
  if (cgpa >= 3.5) return '<span class="badge bg-primary fs-6">Upper Second</span>';
  if (cgpa >= 2.5) return '<span class="badge bg-info fs-6">Lower Second</span>';
  if (cgpa >= 2.0) return '<span class="badge bg-warning fs-6">Third Class</span>';
  return '<span class="badge bg-danger fs-6">Fail</span>';
}

// Print Student Details
function printStudentDetails() {
  window.print();
}

// Export to CSV
function exportToCSV() {
  if (!currentStudentData.regno) {
    showAlert('No student data to export', 'warning');
    return;
  }
  
  let csv = 'Student Details\n\n';
  csv += 'Personal Information\n';
  csv += `Name,${currentStudentData.fullname}\n`;
  csv += `Registration No,${currentStudentData.regno}\n`;
  csv += `Date of Birth,${currentStudentData.dob || 'N/A'}\n`;
  csv += `Phone,${currentStudentData.phone || 'N/A'}\n`;
  csv += `Course ID,${currentStudentData.course_id || 'N/A'}\n`;
  csv += `Department ID,${currentStudentData.department_id || 'N/A'}\n`;
  csv += `Year of Study,${currentStudentData.year_of_study || 'N/A'}\n`;
  csv += `District,${currentStudentData.district || 'N/A'}\n\n`;
  
  csv += 'Academic Results\n';
  csv += 'Year,Semester,Unit Name,Score,Grade,Grade Points\n';
  
  currentResultsData.forEach(r => {
    csv += `${r.year},${r.semester},${r.unit_name || 'Unit ' + r.unit_id},${r.score},${getGrade(r.score)},${getGradePoints(r.score)}\n`;
  });
  
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `Student_${currentStudentData.regno}_Results.csv`;
  a.click();
  
  showAlert('Results exported to CSV successfully!', 'success');
}

// Export to Excel
function exportToExcel() {
  if (!currentStudentData.regno) {
    showAlert('No student data to export', 'warning');
    return;
  }
  
  const wb = XLSX.utils.book_new();
  
  // Student Info Sheet
  const studentInfo = [
    ['Student Details'],
    [],
    ['Personal Information'],
    ['Name', currentStudentData.fullname],
    ['Registration No', currentStudentData.regno],
    ['Date of Birth', currentStudentData.dob || 'N/A'],
    ['Phone', currentStudentData.phone || 'N/A'],
    ['Course ID', currentStudentData.course_id || 'N/A'],
    ['Department ID', currentStudentData.department_id || 'N/A'],
    ['Year of Study', currentStudentData.year_of_study || 'N/A'],
    ['District', currentStudentData.district || 'N/A']
  ];
  
  const wsInfo = XLSX.utils.aoa_to_sheet(studentInfo);
  XLSX.utils.book_append_sheet(wb, wsInfo, 'Student Info');
  
  // Results Sheet
  const resultsData = [['Year', 'Semester', 'Unit Name', 'Score', 'Grade', 'Grade Points']];
  currentResultsData.forEach(r => {
    resultsData.push([
      r.year,
      r.semester,
      r.unit_name || 'Unit ' + r.unit_id,
      r.score,
      getGrade(r.score),
      getGradePoints(r.score)
    ]);
  });
  
  const wsResults = XLSX.utils.aoa_to_sheet(resultsData);
  XLSX.utils.book_append_sheet(wb, wsResults, 'Results');
  
  XLSX.writeFile(wb, `Student_${currentStudentData.regno}_Results.xlsx`);
  showAlert('Results exported to Excel successfully!', 'success');
}

// AJAX Refresh Function
function refreshData(type) {
  $('#loadingOverlay').addClass('show');
  
  $.ajax({
    url: 'ajax_refresh_data.php',
    type: 'POST',
    data: { type: type },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        if (type === 'students') {
          updateStudentsTable(response.data);
          $('#statStudents').text(response.count);
          showAlert('Students data refreshed successfully!', 'success');
        } else if (type === 'staff') {
          updateStaffTable(response.data);
          $('#statStaff').text(response.count);
          showAlert('Staff data refreshed successfully!', 'success');
        }
      } else {
        showAlert('Error: ' + response.message, 'danger');
      }
    },
    error: function(xhr, status, error) {
      showAlert('Failed to refresh data: ' + error, 'danger');
    },
    complete: function() {
      $('#loadingOverlay').removeClass('show');
    }
  });
}

// Update Students Table
function updateStudentsTable(data) {
  let html = '';
  if (data.length === 0) {
    html = '<tr><td colspan="8" class="text-center">No students found</td></tr>';
  } else {
    data.forEach(function(row) {
      html += `
        <tr onclick="viewStudentDetails('${escapeHtml(row.regno)}')">
          <td>${escapeHtml(row.regno)}</td>
          <td>${escapeHtml(row.fullname)}</td>
          <td>${escapeHtml(row.dob)}</td>
          <td>${escapeHtml(row.phone)}</td>
          <td>${escapeHtml(row.district)}</td>
          <td>${escapeHtml(row.course_id)}</td>
          <td>${escapeHtml(row.department_id)}</td>
          <td>${escapeHtml(row.year_of_study)}</td>
        </tr>
      `;
    });
  }
  $('#studentsTableBody').html(html);
}
// Update Staff Table
function updateStaffTable(data) {
  let html = '';
  if (data.length === 0) {
    html = '<tr><td colspan="7" class="text-center">No staff found</td></tr>';
  } else {
    data.forEach(function(row) {
      html += `
        <tr>
          <td>${escapeHtml(row.staff_id)}</td>
          <td>${escapeHtml(row.fullname)}</td>
          <td>${escapeHtml(row.dob)}</td>
          <td>${escapeHtml(row.phone)}</td>
          <td>${escapeHtml(row.district)}</td>
          <td>${escapeHtml(row.department_id)}</td>
          <td>${escapeHtml(row.username)}</td>
        </tr>
      `;
    });
  }
  $('#staffTableBody').html(html);
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
  }, 3000);
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