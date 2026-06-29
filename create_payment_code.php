<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    header("Location: login.php");
    exit();
}

$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : 'uploads/default.png';
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Director';

// Fetch all students for dropdown
$students = $conn->query("SELECT regno, fullname FROM students ORDER BY fullname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Generate Payment Code - Dan4Christ Institute</title>
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
    
    .main-container {
      padding: 0 30px 50px;
      max-width: 900px;
      margin: 0 auto;
    }
    
    .page-header {
      background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
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
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .page-header p {
      font-size: 1.1rem;
      opacity: 0.95;
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
    
    .card-header-custom i {
      font-size: 1.8rem;
    }
    
    .card-body-custom {
      padding: 40px 30px;
    }
    
    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .form-label i {
      color: var(--warning);
    }
    
    .form-select {
      border-radius: 12px;
      padding: 14px 18px;
      border: 2px solid #e3e6f0;
      font-size: 1rem;
      transition: all 0.3s;
      background-color: white;
    }
    
    .form-select:focus {
      border-color: var(--warning);
      box-shadow: 0 0 0 3px rgba(246, 194, 62, 0.15);
      outline: none;
    }
    
    .student-details-card {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      border-radius: 15px;
      padding: 25px;
      margin: 25px 0;
      border-left: 4px solid var(--info);
      animation: slideDown 0.4s ease;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .student-details-card h5 {
      color: var(--dark);
      font-weight: 700;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .student-details-card h5 i {
      color: var(--info);
    }
    
    .detail-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .detail-row:last-child {
      border-bottom: none;
    }
    
    .detail-label {
      font-weight: 600;
      color: var(--secondary);
    }
    
    .detail-value {
      color: var(--dark);
      font-weight: 500;
    }
    
    .loading-details {
      text-align: center;
      padding: 30px;
      color: var(--secondary);
    }
    
    .loading-details .spinner-border {
      width: 2rem;
      height: 2rem;
    }
    
    .btn-group-custom {
      display: flex;
      gap: 15px;
      margin-top: 30px;
      flex-wrap: wrap;
    }
    
    .btn-generate {
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
    
    .btn-generate:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(246, 194, 62, 0.4);
      color: white;
    }
    
    .btn-generate:disabled {
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
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(133, 135, 150, 0.4);
      color: white;
    }
    
    .success-card {
      background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
      border-radius: 15px;
      padding: 30px;
      color: white;
      text-align: center;
      margin: 25px 0;
      animation: slideDown 0.4s ease;
      box-shadow: 0 10px 30px rgba(28, 200, 138, 0.3);
    }
    
    .success-card i {
      font-size: 4rem;
      margin-bottom: 15px;
      animation: bounce 0.6s ease;
    }
    
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-15px); }
    }
    
    .success-card h4 {
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .payment-code {
      background: rgba(255,255,255,0.2);
      padding: 15px 25px;
      border-radius: 10px;
      font-size: 1.8rem;
      font-weight: 700;
      letter-spacing: 3px;
      margin: 15px 0;
      display: inline-block;
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255,255,255,0.3);
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
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    
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
      
      .btn-generate, .btn-back {
        width: 100%;
      }
    }
  </style>
</head>
<body>

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
      <i class="fas fa-barcode"></i>
    </div>
    <h2>Generate Payment Code</h2>
    <p>Create secure payment codes for students</p>
  </div>

  <!-- Main Card -->
  <div class="main-card">
    <div class="card-header-custom">
      <i class="fas fa-qrcode"></i>
      <span>Payment Code Generator</span>
    </div>
    <div class="card-body-custom">
      <form id="paymentCodeForm">
        <div class="mb-4">
          <label class="form-label">
            <i class="fas fa-user-graduate"></i>
            Select Student
          </label>
          <select name="regno" id="regno" class="form-select" required>
            <option value="">-- Select Student Registration Number --</option>
            <?php while($row = $students->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($row['regno']); ?>">
                <?php echo htmlspecialchars($row['regno'] . ' - ' . $row['fullname']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Student details will appear here -->
        <div id="studentDetails"></div>

        <!-- Success message will appear here -->
        <div id="successMessage"></div>

        <div class="btn-group-custom">
          <button type="submit" class="btn-generate" id="generateBtn">
            <i class="fas fa-magic"></i>
            <span>Generate Payment Code</span>
          </button>
          <button onclick="history.back()" type="button" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  console.log("Payment code page loaded");
  
  // Fetch student details when selection changes
  $("#regno").change(function(){
    var regno = $(this).val();
    
    // Clear previous success message
    $("#successMessage").html("");
    
    if(regno){
      console.log("Selected student:", regno);
      
      // Show loading
      $("#studentDetails").html(`
        <div class="loading-details">
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading student details...</p>
        </div>
      `);
      
      // Fetch student details via AJAX
      $.ajax({
        url: "fetch_student.php",
        type: "GET",
        data: { regno: regno },
        success: function(data){
          console.log("Student details loaded");
          $("#studentDetails").html(data);
        },
        error: function(xhr, status, error){
          console.error("Error loading student:", error);
          $("#studentDetails").html(`
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Failed to load student details. Please try again.
            </div>
          `);
          showAlert('Error loading student details', 'danger');
        }
      });
    } else {
      $("#studentDetails").html("");
    }
  });

  // Handle form submission via AJAX
  $("#paymentCodeForm").on("submit", function(e){
    e.preventDefault();
    
    var regno = $("#regno").val();
    
    console.log("Generating code for:", regno);
    
    if(!regno){
      showAlert('Please select a student first', 'warning');
      return;
    }
    
    var submitBtn = $("#generateBtn");
    var originalText = submitBtn.html();
    
    // Disable button and show loading
    submitBtn.prop('disabled', true).html(`
      <span class="spinner-border spinner-border-sm me-2"></span>
      Generating Code...
    `);
    
    $.ajax({
      url: "ajax_generate_payment_code.php",
      type: "POST",
      data: { regno: regno },
      dataType: "json",
      success: function(response){
        console.log("Server response:", response);
        
        if(response.success){
          // Show success message with payment code
          $("#successMessage").html(`
            <div class="success-card">
              <i class="fas fa-check-circle"></i>
              <h4>Payment Code Generated Successfully!</h4>
              <p class="mb-2">Payment code for <strong>${response.regno}</strong></p>
              <div class="payment-code">${response.code}</div>
              <p class="mt-3 mb-0">Share this code with the student for payment</p>
            </div>
          `);
          
          showAlert('Payment code generated successfully!', 'success');
          
          // Clear student details
          $("#studentDetails").html("");
          
          // Reset form after 3 seconds
          setTimeout(function(){
            $("#paymentCodeForm")[0].reset();
            $("#successMessage").html("");
          }, 3000);
          
        } else {
          console.error("Error:", response.message);
          showAlert(response.message || 'Failed to generate payment code', 'danger');
        }
      },
      error: function(xhr, status, error){
        console.error("AJAX Error:", status, error);
        console.error("Response:", xhr.responseText);
        showAlert('Error generating payment code: ' + (xhr.responseText || error), 'danger');
      },
      complete: function(){
        submitBtn.prop('disabled', false).html(originalText);
      }
    });
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
  }, 3000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>