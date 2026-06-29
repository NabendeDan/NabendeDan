<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : 'uploads/default.png';
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'User';

// Fetch user data based on role
$userData = null;
$paymentCodes = array();

if ($role == 'Student') {
    $regno = $_SESSION['regno'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE regno = ?");
    $stmt->bind_param("s", $regno);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();
    
    // Fetch payment codes
    $codesQuery = $conn->prepare("SELECT code_id, code, created_at FROM payment_codes WHERE regno = ? ORDER BY code_id DESC");
    $codesQuery->bind_param("s", $regno);
    $codesQuery->execute();
    $codesResult = $codesQuery->get_result();
    $paymentCodes = $codesResult->fetch_all(MYSQLI_ASSOC);
    $codesQuery->close();
    
    // Get course name
    if ($userData && isset($userData['course_id']) && $userData['course_id']) {
        $courseStmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ?");
        $courseStmt->bind_param("i", $userData['course_id']);
        $courseStmt->execute();
        $courseResult = $courseStmt->get_result()->fetch_assoc();
        $userData['course_name'] = isset($courseResult['course_name']) ? $courseResult['course_name'] : 'N/A';
        $courseStmt->close();
    }
    
    // Get department name
    if ($userData && isset($userData['department_id']) && $userData['department_id']) {
        $deptStmt = $conn->prepare("SELECT dept_name FROM departments WHERE dept_id = ?");
        $deptStmt->bind_param("i", $userData['department_id']);
        $deptStmt->execute();
        $deptResult = $deptStmt->get_result()->fetch_assoc();
        $userData['dept_name'] = isset($deptResult['dept_name']) ? $deptResult['dept_name'] : 'N/A';
        $deptStmt->close();
    }
    
} elseif ($role == 'Staff' || $role == 'Director') {
    $staff_id = $_SESSION['staff_id'];
    $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
    $stmt->bind_param("s", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();
    
    // Get department name
    if ($userData && isset($userData['department_id']) && $userData['department_id']) {
        $deptStmt = $conn->prepare("SELECT dept_name FROM departments WHERE dept_id = ?");
        $deptStmt->bind_param("i", $userData['department_id']);
        $deptStmt->execute();
        $deptResult = $deptStmt->get_result()->fetch_assoc();
        $userData['dept_name'] = isset($deptResult['dept_name']) ? $deptResult['dept_name'] : 'N/A';
        $deptStmt->close();
    }
}

// Determine redirect URL
if ($role == 'Director') {
    $dashboard_url = 'director_dashboard.php';
} elseif ($role == 'Staff') {
    $dashboard_url = 'staff.php';
} else {
    $dashboard_url = 'student_dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - Dan4Christ Institute</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    :root {
      --primary: #4e73df;
      --success: #1cc88a;
      --info: #36b9cc;
      --warning: #f6c23e;
      --danger: #e74a3b;
      --dark: #5a5c69;
      --light: #f8f9fc;
    }
    
    * {
      font-family: 'Poppins', sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
    }
    
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
    
    .main-container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    
    .profile-header {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-bottom: 30px;
      position: relative;
      overflow: hidden;
    }
    
    .profile-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 120px;
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
    }
    
    .profile-header-content {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: flex-end;
      gap: 30px;
      margin-top: 60px;
    }
    
    .profile-photo-wrapper {
      position: relative;
      flex-shrink: 0;
    }
    
    .profile-photo {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid white;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      background: white;
    }
    
    .photo-upload-btn {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background: var(--primary);
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
      background: #224abe;
    }
    
    .profile-info {
      flex: 1;
      padding-bottom: 10px;
    }
    
    .profile-name {
      font-size: 2rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 5px;
    }
    
    .profile-role {
      display: inline-block;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      color: white;
      margin-bottom: 10px;
    }
    
    .role-student { background: var(--info); }
    .role-staff { background: var(--success); }
    .role-director { background: var(--dark); }
    
    .profile-meta {
      color: var(--dark);
      opacity: 0.7;
      font-size: 0.9rem;
    }
    
    .profile-meta i {
      width: 20px;
      margin-right: 5px;
    }
    
    .profile-body {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      margin-bottom: 30px;
    }
    
    .profile-tabs {
      display: flex;
      border-bottom: 2px solid #e3e6f0;
      background: #f8f9fc;
    }
    
    .profile-tab {
      padding: 20px 30px;
      font-weight: 600;
      color: var(--dark);
      cursor: pointer;
      transition: all 0.3s;
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
    }
    
    .profile-tab:hover {
      background: white;
    }
    
    .profile-tab.active {
      background: white;
      border-bottom-color: var(--primary);
      color: var(--primary);
    }
    
    .profile-tab i {
      margin-right: 8px;
    }
    
    .tab-content {
      padding: 40px;
      display: none;
    }
    
    .tab-content.active {
      display: block;
      animation: fadeIn 0.4s;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .form-section-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e3e6f0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .form-section-title i {
      color: var(--primary);
    }
    
    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 8px;
      font-size: 0.9rem;
    }
    
    .form-label .required {
      color: var(--danger);
    }
    
    .form-control, .form-select {
      border-radius: 10px;
      padding: 12px 15px;
      border: 2px solid #e3e6f0;
      transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
    }
    
    .form-control[readonly], .form-control:disabled {
      background: #f8f9fc;
      cursor: not-allowed;
      opacity: 0.7;
    }
    
    .frozen-field {
      position: relative;
    }
    
    .frozen-field::after {
      content: '\f023';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--warning);
      font-size: 1rem;
    }
    
    .frozen-hint {
      font-size: 0.8rem;
      color: var(--warning);
      margin-top: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .payment-code-card {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      border-left: 4px solid var(--success);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .payment-code {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--success);
      font-family: 'Courier New', monospace;
    }
    
    .payment-code-id {
      font-size: 0.85rem;
      color: var(--dark);
      opacity: 0.7;
    }
    
    .payment-code-date {
      font-size: 0.85rem;
      color: var(--dark);
      opacity: 0.7;
    }
    
    .btn-edit {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-edit:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(78, 115, 223, 0.4);
      color: white;
    }
    
    .btn-save {
      background: linear-gradient(135deg, var(--success) 0%, #17a673 100%);
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(28, 200, 138, 0.4);
      color: white;
    }
    
    .btn-cancel {
      background: linear-gradient(135deg, var(--secondary) 0%, #5a5c69 100%);
      border: none;
      color: white;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-cancel:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(133, 135, 150, 0.4);
      color: white;
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
      animation: slideIn 0.3s;
    }
    
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
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
      border-top-color: var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    @media (max-width: 768px) {
      .profile-header-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }
      
      .profile-tabs {
        flex-wrap: wrap;
      }
      
      .profile-tab {
        padding: 15px 20px;
        font-size: 0.9rem;
      }
      
      .tab-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner"></div>
  <p class="text-muted mt-3">Saving changes...</p>
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
  <!-- Profile Header -->
  <div class="profile-header">
    <div class="profile-header-content">
      <div class="profile-photo-wrapper">
        <img src="<?php echo htmlspecialchars($photo); ?>" 
             id="profilePhoto"
             class="profile-photo" 
             alt="Profile"
             onerror="this.src='uploads/default.png'">
        <label for="photoUpload" class="photo-upload-btn" title="Change photo">
          <i class="fas fa-camera"></i>
        </label>
        <input type="file" id="photoUpload" accept="image/*" style="display: none;">
      </div>
      
      <div class="profile-info">
        <h1 class="profile-name" id="displayName"><?php echo htmlspecialchars($fullname); ?></h1>
        <span class="profile-role role-<?php echo strtolower($role); ?>">
          <i class="fas fa-<?php echo ($role == 'Student') ? 'user-graduate' : (($role == 'Staff') ? 'chalkboard-teacher' : 'user-tie'); ?> me-1"></i>
          <?php echo $role; ?>
        </span>
        <div class="profile-meta">
          <?php if ($role == 'Student'): ?>
            <div><i class="fas fa-id-card"></i> <?php echo htmlspecialchars(isset($userData['regno']) ? $userData['regno'] : 'N/A'); ?></div>
          <?php else: ?>
            <div><i class="fas fa-id-badge"></i> <?php echo htmlspecialchars(isset($userData['staff_id']) ? $userData['staff_id'] : 'N/A'); ?></div>
          <?php endif; ?>
          <?php if (isset($userData['email']) && !empty($userData['email'])): ?>
            <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($userData['email']); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Body -->
  <div class="profile-body">
    <div class="profile-tabs">
      <div class="profile-tab active" data-tab="personal">
        <i class="fas fa-user"></i>Personal Info
      </div>
      <div class="profile-tab" data-tab="academic">
        <i class="fas fa-graduation-cap"></i>Academic Info
      </div>
      <?php if ($role == 'Student'): ?>
        <div class="profile-tab" data-tab="payments">
          <i class="fas fa-credit-card"></i>Payment Codes
        </div>
      <?php endif; ?>
      <div class="profile-tab" data-tab="security">
        <i class="fas fa-lock"></i>Security
      </div>
    </div>

    <!-- Personal Info Tab -->
    <div class="tab-content active" id="personalTab">
      <form id="personalForm">
        <div class="form-section-title">
          <i class="fas fa-user"></i>Personal Information
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Full Name <span class="required">*</span></label>
            <input type="text" name="fullname" class="form-control editable-field" 
                   value="<?php echo htmlspecialchars(isset($userData['fullname']) ? $userData['fullname'] : ''); ?>" required>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <?php echo ($role == 'Student') ? 'Registration Number' : 'Staff ID'; ?>
            </label>
            <div class="frozen-field">
              <input type="text" class="form-control frozen-field-input" 
                     value="<?php echo htmlspecialchars(($role == 'Student') ? (isset($userData['regno']) ? $userData['regno'] : '') : (isset($userData['staff_id']) ? $userData['staff_id'] : '')); ?>" 
                     readonly>
            </div>
            <?php if ($role == 'Student'): ?>
              <div class="frozen-hint">
                <i class="fas fa-lock"></i> This field cannot be changed
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Date of Birth</label>
            <div class="frozen-field">
              <input type="date" class="form-control frozen-field-input" 
                     value="<?php echo htmlspecialchars(isset($userData['dob']) ? $userData['dob'] : ''); ?>" 
                     readonly>
            </div>
            <?php if ($role == 'Student'): ?>
              <div class="frozen-hint">
                <i class="fas fa-lock"></i> This field cannot be changed
              </div>
            <?php endif; ?>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-control editable-field" 
                   value="<?php echo htmlspecialchars(isset($userData['phone']) ? $userData['phone'] : ''); ?>" 
                   placeholder="e.g., +256 700 000 000">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control editable-field" 
                   value="<?php echo htmlspecialchars(isset($userData['email']) ? $userData['email'] : ''); ?>" 
                   placeholder="e.g., user@example.com">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">District</label>
            <input type="text" name="district" class="form-control editable-field" 
                   value="<?php echo htmlspecialchars(isset($userData['district']) ? $userData['district'] : ''); ?>" 
                   placeholder="e.g., Kampala">
          </div>
        </div>

        <?php if ($role != 'Student'): ?>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control editable-field" rows="2"><?php echo htmlspecialchars(isset($userData['address']) ? $userData['address'] : ''); ?></textarea>
          </div>
        <?php endif; ?>

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn-save">
            <i class="fas fa-save me-2"></i>Save Changes
          </button>
          <button type="button" class="btn-cancel" onclick="location.reload()">
            <i class="fas fa-times me-2"></i>Cancel
          </button>
        </div>
      </form>
    </div>

    <!-- Academic Info Tab -->
    <div class="tab-content" id="academicTab">
      <form id="academicForm">
        <div class="form-section-title">
          <i class="fas fa-graduation-cap"></i>Academic Information
        </div>
        
        <?php if ($role == 'Student'): ?>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Course</label>
              <div class="frozen-field">
                <input type="text" class="form-control frozen-field-input" 
                       value="<?php echo htmlspecialchars(isset($userData['course_name']) ? $userData['course_name'] : 'N/A'); ?>" 
                       readonly>
              </div>
              <div class="frozen-hint">
                <i class="fas fa-lock"></i> Contact administration to change course
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Department</label>
              <div class="frozen-field">
                <input type="text" class="form-control frozen-field-input" 
                       value="<?php echo htmlspecialchars(isset($userData['dept_name']) ? $userData['dept_name'] : 'N/A'); ?>" 
                       readonly>
              </div>
              <div class="frozen-hint">
                <i class="fas fa-lock"></i> Contact administration to change department
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Year of Study</label>
              <div class="frozen-field">
                <input type="text" class="form-control frozen-field-input" 
                       value="Year <?php echo htmlspecialchars(isset($userData['year_of_study']) ? $userData['year_of_study'] : 'N/A'); ?>" 
                       readonly>
              </div>
              <div class="frozen-hint">
                <i class="fas fa-lock"></i> Automatically updated by administration
              </div>
            </div>
          </div>

          <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> Academic information is managed by the administration. 
            Please contact your director or staff member for any changes.
          </div>
        <?php else: ?>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Department</label>
              <div class="frozen-field">
                <input type="text" class="form-control frozen-field-input" 
                       value="<?php echo htmlspecialchars(isset($userData['dept_name']) ? $userData['dept_name'] : 'N/A'); ?>" 
                       readonly>
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Position</label>
              <input type="text" name="position" class="form-control editable-field" 
                     value="<?php echo htmlspecialchars(isset($userData['position']) ? $userData['position'] : ''); ?>" 
                     placeholder="e.g., Lecturer">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Qualifications</label>
              <input type="text" name="qualifications" class="form-control editable-field" 
                     value="<?php echo htmlspecialchars(isset($userData['qualifications']) ? $userData['qualifications'] : ''); ?>" 
                     placeholder="e.g., Bachelor's Degree">
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Date Hired</label>
              <div class="frozen-field">
                <input type="date" class="form-control frozen-field-input" 
                       value="<?php echo htmlspecialchars(isset($userData['date_hired']) ? $userData['date_hired'] : ''); ?>" 
                       readonly>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn-save">
              <i class="fas fa-save me-2"></i>Save Changes
            </button>
            <button type="button" class="btn-cancel" onclick="location.reload()">
              <i class="fas fa-times me-2"></i>Cancel
            </button>
          </div>
        <?php endif; ?>
      </form>
    </div>

    <!-- Payment Codes Tab (Student Only) -->
    <?php if ($role == 'Student'): ?>
      <div class="tab-content" id="paymentsTab">
        <div class="form-section-title">
          <i class="fas fa-credit-card"></i>Your Payment Codes
        </div>
        
        <?php if (count($paymentCodes) > 0): ?>
          <?php foreach ($paymentCodes as $code): ?>
            <div class="payment-code-card">
              <div>
                <div class="payment-code"><?php echo htmlspecialchars($code['code']); ?></div>
                <div class="payment-code-id">Code ID: #<?php echo $code['code_id']; ?></div>
              </div>
              <div class="text-end">
                <div class="payment-code-date">
                  <i class="fas fa-calendar me-1"></i>
                  <?php echo date('M d, Y', strtotime($code['created_at'])); ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-ticket-alt" style="font-size: 60px; opacity: 0.3;"></i>
            <h5 class="mt-3">No Payment Codes Yet</h5>
            <p>Please contact the director to generate a payment code for you.</p>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Security Tab -->
    <div class="tab-content" id="securityTab">
      <form id="securityForm">
        <div class="form-section-title">
          <i class="fas fa-lock"></i>Change Password
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Current Password <span class="required">*</span></label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">New Password <span class="required">*</span></label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
            <small class="text-muted">Minimum 6 characters</small>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">Confirm New Password <span class="required">*</span></label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
          </div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn-save">
            <i class="fas fa-key me-2"></i>Update Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Tab switching
$('.profile-tab').on('click', function() {
  var tab = $(this).data('tab');
  
  $('.profile-tab').removeClass('active');
  $(this).addClass('active');
  
  $('.tab-content').removeClass('active');
  $('#' + tab + 'Tab').addClass('active');
});

// Photo upload
$('#photoUpload').on('change', function() {
  var file = this.files[0];
  if (!file) return;
  
  if (file.size > 5 * 1024 * 1024) {
    showAlert('File too large. Maximum size is 5MB', 'warning');
    return;
  }
  
  var reader = new FileReader();
  reader.onload = function(e) {
    $('#profilePhoto').attr('src', e.target.result);
  };
  reader.readAsDataURL(file);
  
  var formData = new FormData();
  formData.append('photo', file);
  
  $.ajax({
    url: 'upload_profile_photo.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        showAlert('Photo updated successfully!', 'success');
      } else {
        showAlert(response.message || 'Upload failed', 'danger');
      }
    },
    error: function() {
      showAlert('Upload error', 'danger');
    }
  });
});

// Personal form submission
$('#personalForm').on('submit', function(e) {
  e.preventDefault();
  saveProfile('update_personal.php', $(this));
});

// Academic form submission (Staff/Director only)
$('#academicForm').on('submit', function(e) {
  e.preventDefault();
  saveProfile('update_academic.php', $(this));
});

// Security form submission
$('#securityForm').on('submit', function(e) {
  e.preventDefault();
  
  var newPass = $('input[name="new_password"]').val();
  var confirmPass = $('input[name="confirm_password"]').val();
  
  if (newPass !== confirmPass) {
    showAlert('New passwords do not match', 'warning');
    return;
  }
  
  if (newPass.length < 6) {
    showAlert('Password must be at least 6 characters', 'warning');
    return;
  }
  
  saveProfile('update_password.php', $(this));
});

// Save profile via AJAX
function saveProfile(url, form) {
  $('#loadingOverlay').addClass('show');
  
  $.ajax({
    url: url,
    type: 'POST',
    data: form.serialize(),
    dataType: 'json',
    success: function(response) {
      $('#loadingOverlay').removeClass('show');
      
      if (response.success) {
        showAlert(response.message, 'success');
        
        // Update display name if changed
        if (response.fullname) {
          $('#displayName').text(response.fullname);
        }
      } else {
        showAlert(response.message || 'Failed to update', 'danger');
      }
    },
    error: function() {
      $('#loadingOverlay').removeClass('show');
      showAlert('Error updating profile', 'danger');
    }
  });
}

// Show Alert
function showAlert(message, type) {
  var icons = {
    success: 'fa-check-circle',
    danger: 'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle'
  };
  
  var alertHtml = '<div class="custom-alert alert alert-' + type + '">' +
    '<i class="fas ' + icons[type] + ' me-2"></i>' +
    '<span>' + message + '</span>' +
    '<button type="button" class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>' +
    '</div>';
  
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