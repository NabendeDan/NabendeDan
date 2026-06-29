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
    
    try {
        $conn->begin_transaction();
        
        // Get form data
        $staff_id = trim($_POST['staff_id']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $fullname = trim($_POST['fullname']);
        $gender = trim($_POST['gender']);
        $dob = trim($_POST['dob']);
        $national_id = trim($_POST['national_id']);
        $marital_status = trim($_POST['marital_status']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $district = trim($_POST['district']);
        $emergency_contact = trim($_POST['emergency_contact']);
        $emergency_relationship = trim($_POST['emergency_relationship']);
        $department_id = intval($_POST['department_id']);
        $position = trim($_POST['position']);
        $qualifications = trim($_POST['qualifications']);
        $date_hired = trim($_POST['date_hired']);
        $salary = floatval($_POST['salary']);
        $contract_type = trim($_POST['contract_type']);
        $role = trim($_POST['role']);
        
        // Validate required fields
        if (empty($staff_id) || empty($username) || empty($password) || empty($fullname)) {
            throw new Exception('Staff ID, Username, Password, and Full Name are required');
        }
        
        // Check if staff_id already exists
        $check = $conn->prepare("SELECT staff_id FROM staff WHERE staff_id = ?");
        $check->bind_param("s", $staff_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            throw new Exception('Staff ID already exists');
        }
        $check->close();
        
        // Check if username already exists
        $check = $conn->prepare("SELECT username FROM staff WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            throw new Exception('Username already exists');
        }
        $check->close();
        
        // Handle photo upload (optional - stored separately if you have a photo column)
        $photo_path = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception('Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed');
            }
            
            if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                throw new Exception('File too large. Maximum size is 5MB');
            }
            
            $upload_dir = 'uploads/staff/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = time() . '_' . uniqid() . '.' . $ext;
            $photo_path = $upload_dir . $new_filename;
            
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                throw new Exception('Failed to upload photo');
            }
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert staff into database (matching your exact table structure)
        $sql = "INSERT INTO staff (staff_id, username, password, fullname, gender, dob, national_id, marital_status, phone, email, address, district, emergency_contact, emergency_relationship, department_id, position, qualifications, date_hired, salary, contract_type, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        // Bind parameters matching the column order
        $stmt->bind_param("ssssssssssssssisssdss", 
            $staff_id,              // s - staff_id
            $username,              // s - username
            $hashed_password,       // s - password
            $fullname,              // s - fullname
            $gender,                // s - gender
            $dob,                   // s - dob
            $national_id,           // s - national_id
            $marital_status,        // s - marital_status
            $phone,                 // s - phone
            $email,                 // s - email
            $address,               // s - address
            $district,              // s - district
            $emergency_contact,     // s - emergency_contact
            $emergency_relationship,// s - emergency_relationship
            $department_id,         // i - department_id
            $position,              // s - position
            $qualifications,        // s - qualifications
            $date_hired,            // s - date_hired
            $salary,                // d - salary
            $contract_type,         // s - contract_type
            $role                   // s - role
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create staff: ' . $stmt->error);
        }
        
        $stmt->close();
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Staff account created successfully!'
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

// Fetch departments
$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Staff - Dan4Christ Institute</title>
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
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .page-header {
      background: linear-gradient(135deg, var(--success) 0%, #17a673 100%);
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
      background: linear-gradient(135deg, var(--success) 0%, #17a673 100%);
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
      border-left: 4px solid var(--success);
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
    
    .form-label i {
      color: var(--success);
      font-size: 0.9rem;
    }
    
    .form-control, .form-select {
      border-radius: 12px;
      padding: 14px 18px;
      border: 2px solid #e3e6f0;
      transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--success);
      box-shadow: 0 0 0 3px rgba(28, 200, 138, 0.15);
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
      border: 4px solid var(--success);
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
      background: var(--success);
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
      background: linear-gradient(135deg, var(--success) 0%, #17a673 100%);
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
      border-top-color: var(--success);
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
  <p class="text-muted">Creating staff account...</p>
</div>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<!-- Navbar -->
<nav class="navbar navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="staff.php">
      <i class="fas fa-university me-2"></i>Dan4Christ Institute
    </a>
    <a href="staff.php" class="btn btn-outline-light btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
    </a>
  </div>
</nav>

<div class="main-container">
  <!-- Page Header -->
  <div class="page-header">
    <div class="header-icon">
      <i class="fas fa-user-plus"></i>
    </div>
    <h2>Create Staff Account</h2>
    <p>Add new staff members to the institute</p>
  </div>

  <!-- Main Card -->
  <div class="main-card">
    <div class="card-header-custom">
      <i class="fas fa-chalkboard-teacher"></i>
      <span>Staff Information</span>
    </div>
    <div class="card-body-custom">
      <form id="staffForm" enctype="multipart/form-data">
        
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
              <i class="fas fa-id-badge"></i>Staff ID *
            </label>
            <input type="text" name="staff_id" class="form-control" placeholder="e.g., STAFF001" required>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-user"></i>Full Name *
            </label>
            <input type="text" name="fullname" class="form-control" placeholder="Enter full name" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">
              <i class="fas fa-venus-mars"></i>Gender
            </label>
            <select name="gender" class="form-select">
              <option value="">-- Select Gender --</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          
          <div class="col-md-4 mb-3">
            <label class="form-label">
              <i class="fas fa-calendar"></i>Date of Birth
            </label>
            <input type="date" name="dob" class="form-control">
          </div>
          
          <div class="col-md-4 mb-3">
            <label class="form-label">
              <i class="fas fa-ring"></i>Marital Status
            </label>
            <select name="marital_status" class="form-select">
              <option value="">-- Select --</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Divorced">Divorced</option>
              <option value="Widowed">Widowed</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-id-card"></i>National ID
            </label>
            <input type="text" name="national_id" class="form-control" placeholder="e.g., CM1234567890">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-phone"></i>Phone
            </label>
            <input type="text" name="phone" class="form-control" placeholder="e.g., +256 700 000 000">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-envelope"></i>Email
            </label>
            <input type="email" name="email" class="form-control" placeholder="e.g., staff@example.com">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-map-marker-alt"></i>District
            </label>
            <input type="text" name="district" class="form-control" placeholder="e.g., Kampala">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">
            <i class="fas fa-home"></i>Address
          </label>
          <textarea name="address" class="form-control" rows="2" placeholder="Enter full address"></textarea>
        </div>

        <!-- Emergency Contact -->
        <div class="section-header">
          <h6><i class="fas fa-phone-alt"></i>Emergency Contact</h6>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-user-friends"></i>Emergency Contact
            </label>
            <input type="text" name="emergency_contact" class="form-control" placeholder="Contact name and phone">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-heart"></i>Relationship
            </label>
            <input type="text" name="emergency_relationship" class="form-control" placeholder="e.g., Spouse, Parent">
          </div>
        </div>

        <!-- Employment Information -->
        <div class="section-header">
          <h6><i class="fas fa-briefcase"></i>Employment Information</h6>
        </div>

        <div class="row">
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
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-user-tie"></i>Position
            </label>
            <input type="text" name="position" class="form-control" placeholder="e.g., Lecturer, Administrator">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-calendar-check"></i>Date Hired
            </label>
            <input type="date" name="date_hired" class="form-control">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-file-contract"></i>Contract Type
            </label>
            <select name="contract_type" class="form-select">
              <option value="">-- Select --</option>
              <option value="Full-time">Full-time</option>
              <option value="Part-time">Part-time</option>
              <option value="Contract">Contract</option>
              <option value="Temporary">Temporary</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-money-bill-wave"></i>Salary (UGX)
            </label>
            <input type="number" name="salary" class="form-control" placeholder="e.g., 500000" step="0.01">
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-user-shield"></i>Role
            </label>
            <select name="role" class="form-select">
              <option value="">-- Select Role --</option>
              <option value="Staff">Staff</option>
              <option value="Admin">Admin</option>
              <option value="Lecturer">Lecturer</option>
              <option value="Supervisor">Supervisor</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">
            <i class="fas fa-graduation-cap"></i>Qualifications
          </label>
          <textarea name="qualifications" class="form-control" rows="2" placeholder="e.g., Bachelor's Degree in Computer Science"></textarea>
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
            <input type="text" name="username" class="form-control" placeholder="Enter username for login" required>
            <small class="text-muted">This will be used to login to the system</small>
          </div>
          
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="fas fa-key"></i>Password *
            </label>
            <input type="password" name="password" class="form-control" placeholder="Enter secure password" required>
            <small class="text-muted">Minimum 6 characters recommended</small>
          </div>
        </div>

        <!-- Buttons -->
        <div class="d-flex gap-3 mt-4">
          <button type="submit" class="btn-save" id="saveBtn">
            <i class="fas fa-user-plus"></i>Create Staff Account
          </button>
          <button type="button" class="btn-back" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>Back
          </button>
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
$('#staffForm').on('submit', function(e) {
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
  formData.append('ajax', 1);
  
  $.ajax({
    url: 'staff_user.php',
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