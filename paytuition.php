<?php
session_start();
include 'db.php';

// Check if student is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Student') {
    header("Location: login.php");
    exit();
}

$regno = $_SESSION['regno'];
$student = null;
$payment_codes = [];
$selected_code = null;
$error = '';

// Fetch student details
$studentQuery = $conn->prepare("SELECT * FROM students WHERE regno = ?");
$studentQuery->bind_param("s", $regno);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();

if ($studentResult->num_rows > 0) {
    $student = $studentResult->fetch_assoc();
} else {
    $error = 'Student information not found!';
}
$studentQuery->close();

// Fetch all payment codes for this student
$codesQuery = $conn->prepare("SELECT code_id, code, created_at FROM payment_codes WHERE regno = ? ORDER BY code_id DESC");
$codesQuery->bind_param("s", $regno);
$codesQuery->execute();
$codesResult = $codesQuery->get_result();

while ($code = $codesResult->fetch_assoc()) {
    $payment_codes[] = $code;
}
$codesQuery->close();

// If a code is selected via GET parameter
if (isset($_GET['code_id']) && !empty($payment_codes)) {
    $selected_id = intval($_GET['code_id']);
    foreach ($payment_codes as $code) {
        if ($code['code_id'] == $selected_id) {
            $selected_code = $code;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Pay Tuition - Dan4Christ Institute</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 30px 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .main-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      overflow: hidden;
      max-width: 900px;
      margin: 0 auto;
    }
    
    .card-header {
      background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
      color: white;
      padding: 25px 30px;
      border: none;
    }
    
    .card-body {
      padding: 35px;
    }
    
    .code-card {
      border: 2px solid #e3e6f0;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 15px;
      transition: all 0.3s;
      cursor: pointer;
      background: #f8f9fc;
    }
    
    .code-card:hover {
      border-color: #1cc88a;
      background: #e8f5e9;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .code-card.selected {
      border-color: #1cc88a;
      background: #d4edda;
      box-shadow: 0 5px 20px rgba(28, 200, 138, 0.3);
    }
    
    .code-number {
      font-size: 24px;
      font-weight: bold;
      color: #1cc88a;
    }
    
    .code-id {
      background: #1cc88a;
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
    }
    
    .form-label {
      font-weight: 600;
      color: #5a5c69;
      margin-bottom: 8px;
    }
    
    .form-control, .form-select {
      border-radius: 10px;
      padding: 12px 15px;
      border: 2px solid #e3e6f0;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #1cc88a;
      box-shadow: 0 0 0 3px rgba(28, 200, 138, 0.15);
    }
    
    .btn-success {
      background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
      border: none;
      padding: 12px 35px;
      border-radius: 50px;
      font-weight: 600;
      color: white;
    }
    
    .btn-success:hover {
      background: linear-gradient(135deg, #17a673 0%, #138a63 100%);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(28, 200, 138, 0.4);
      color: white;
    }
    
    .btn-secondary {
      background: #6c757d;
      border: none;
      padding: 12px 35px;
      border-radius: 50px;
      font-weight: 600;
      color: white;
    }
    
    .list-group-item {
      border-left: 3px solid #1cc88a;
      margin-bottom: 5px;
      border-radius: 5px !important;
    }
    
    .alert {
      border-radius: 10px;
    }
    
    .no-codes {
      text-align: center;
      padding: 40px;
      background: #f8f9fc;
      border-radius: 10px;
    }
    
    .no-codes i {
      font-size: 64px;
      color: #ddd;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="main-card">
    <div class="card-header">
      <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Pay Tuition</h4>
    </div>
    <div class="card-body">

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <?php if ($student): ?>
        <!-- Student Information -->
        <div class="alert alert-info">
          <h5><i class="fas fa-user me-2"></i>Student Information</h5>
          <div class="row">
            <div class="col-md-6">
              <strong>Name:</strong> <?php echo htmlspecialchars($student['fullname']); ?><br>
              <strong>RegNo:</strong> <?php echo htmlspecialchars($student['regno']); ?>
            </div>
            <div class="col-md-6">
              <strong>Course ID:</strong> <?php echo htmlspecialchars($student['course_id']); ?><br>
              <strong>Year of Study:</strong> Year <?php echo htmlspecialchars($student['year_of_study']); ?>
            </div>
          </div>
        </div>

        <!-- Payment Codes Section -->
        <h5 class="mb-3"><i class="fas fa-key me-2"></i>Your Payment Codes</h5>
        
        <?php if (count($payment_codes) > 0): ?>
          <div class="row">
            <?php foreach ($payment_codes as $code): ?>
              <div class="col-md-6 mb-3">
                <div class="code-card <?php echo ($selected_code && $selected_code['code_id'] == $code['code_id']) ? 'selected' : ''; ?>" 
                     onclick="selectCode(<?php echo $code['code_id']; ?>)">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <div class="code-number"><?php echo htmlspecialchars($code['code']); ?></div>
                      <small class="text-muted">Created: <?php echo date('M d, Y H:i', strtotime($code['created_at'])); ?></small>
                    </div>
                    <div class="code-id">ID: <?php echo $code['code_id']; ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if ($selected_code): ?>
            <!-- Payment Form -->
            <div id="paymentForm" style="margin-top: 30px;">
              <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Selected Code:</strong> <?php echo htmlspecialchars($selected_code['code']); ?> (ID: <?php echo $selected_code['code_id']; ?>)
              </div>
              
              <h5 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
              <form action="process_payment.php" method="POST">
                <input type="hidden" name="code_id" value="<?php echo $selected_code['code_id']; ?>">
                
                <div class="mb-3">
                  <label class="form-label">Amount (UGX) *</label>
                  <input type="number" name="amount" class="form-control" 
                         placeholder="Enter amount to pay" required min="1" step="0.01">
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Purpose *</label>
                  <input type="text" name="purpose" class="form-control" 
                         placeholder="e.g., Tuition fees, Registration fees" required maxlength="100">
                </div>
                
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Semester *</label>
                    <select name="semester" class="form-select" required>
                      <option value="">-- Select Semester --</option>
                      <option value="1">Semester 1</option>
                      <option value="2">Semester 2</option>
                  
                    </select>
                  </div>
                  
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Year *</label>
                    <input type="number" name="year" class="form-control" 
                           placeholder="e.g., 1" required min="1" max="5">
                  </div>
                </div>
                
                <div class="mt-4">
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane me-2"></i>Submit Payment
                  </button>
                  <a href="paytuition.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-2"></i>Cancel
                  </a>
                </div>
              </form>
            </div>
          <?php else: ?>
            <div class="alert alert-warning">
              <i class="fas fa-hand-pointer me-2"></i>
              <strong>Click on a payment code above to proceed with payment</strong>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <div class="no-codes">
            <i class="fas fa-ticket-alt"></i>
            <h5>No Payment Codes Available</h5>
            <p class="text-muted">You don't have any payment codes yet.</p>
            <p>Please contact the director to generate a payment code for you.</p>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Unable to load student information. Please login again.
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function selectCode(codeId) {
  // Remove selected class from all cards
  document.querySelectorAll('.code-card').forEach(card => {
    card.classList.remove('selected');
  });
  
  // Add selected class to clicked card
  event.currentTarget.classList.add('selected');
  
  // Redirect to show payment form
  window.location.href = 'paytuition.php?code_id=' + codeId;
}
</script>

</body>
</html>