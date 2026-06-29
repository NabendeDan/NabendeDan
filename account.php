<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    header("Location: login.php");
    exit();
}

$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : 'uploads/default.png';
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Director';

// Fetch total income (from student payments)
$incomeQuery = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments");
$totalIncome = $incomeQuery->fetch_assoc()['total'];

// Fetch total expenses (staff payments)
$expenseQuery = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM staff_payments WHERE status='completed'");
$totalExpenses = $expenseQuery->fetch_assoc()['total'];

$netBalance = $totalIncome - $totalExpenses;

// Fetch staff with auto-payment settings
$staffQuery = "SELECT s.staff_id, s.fullname, s.phone, s.position, 
               COALESCE(aps.amount, 0) as auto_amount,
               COALESCE(aps.payment_day, 28) as payment_day,
               COALESCE(aps.is_active, 0) as auto_enabled
               FROM staff s 
               LEFT JOIN auto_payment_settings aps ON s.staff_id = aps.staff_id
               ORDER BY s.fullname";
$staff = $conn->query($staffQuery);

// Fetch recent transactions
$transactionsQuery = "SELECT * FROM (
    SELECT 'income' as type, amount, 'Student Payment' as description, date_paid as transaction_date, regno as reference
    FROM payments
    UNION ALL
    SELECT 'expense' as type, amount, CONCAT('Staff Payment - ', s.fullname) as description, payment_date as transaction_date, sp.reference
    FROM staff_payments sp
    JOIN staff s ON sp.staff_id = s.staff_id
) as all_transactions
ORDER BY transaction_date DESC LIMIT 20";
$transactions = $conn->query($transactionsQuery);

// Fetch monthly data for chart (last 6 months)
$monthlyDataQuery = "SELECT 
    DATE_FORMAT(date_paid, '%Y-%m') as month,
    SUM(amount) as income
FROM payments 
WHERE date_paid >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(date_paid, '%Y-%m')
ORDER BY month";
$monthlyIncome = $conn->query($monthlyDataQuery);

$monthlyExpenseQuery = "SELECT 
    DATE_FORMAT(payment_date, '%Y-%m') as month,
    SUM(amount) as expenses
FROM staff_payments 
WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status='completed'
GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
ORDER BY month";
$monthlyExpenses = $conn->query($monthlyExpenseQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Management - Dan4Christ Institute</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary: #4e73df;
      --success: #1cc88a;
      --danger: #e74a3b;
      --warning: #f6c23e;
      --info: #36b9cc;
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
    
    .profile-img {
      border: 3px solid rgba(255,255,255,0.3);
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .main-container {
      padding: 30px;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    /* Balance Cards */
    .balance-card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-bottom: 30px;
      transition: all 0.3s;
      border-left: 5px solid;
    }
    
    .balance-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .balance-card.income {
      border-left-color: var(--success);
    }
    
    .balance-card.expense {
      border-left-color: var(--danger);
    }
    
    .balance-card.net {
      border-left-color: var(--primary);
    }
    
    .balance-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin-bottom: 15px;
    }
    
    .balance-card.income .balance-icon {
      background: rgba(28, 200, 138, 0.1);
      color: var(--success);
    }
    
    .balance-card.expense .balance-icon {
      background: rgba(231, 74, 59, 0.1);
      color: var(--danger);
    }
    
    .balance-card.net .balance-icon {
      background: rgba(78, 115, 223, 0.1);
      color: var(--primary);
    }
    
    .balance-label {
      color: var(--dark);
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }
    
    .balance-amount {
      font-size: 2rem;
      font-weight: 700;
      color: var(--dark);
    }
    
    .balance-card.income .balance-amount { color: var(--success); }
    .balance-card.expense .balance-amount { color: var(--danger); }
    .balance-card.net .balance-amount { color: var(--primary); }
    
    /* Chart Section */
    .chart-card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }
    
    .chart-card h5 {
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    /* Staff Payment Section */
    .payment-section {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }
    
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #e3e6f0;
    }
    
    .section-header h5 {
      font-weight: 700;
      color: var(--dark);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .staff-card {
      background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 15px;
      border-left: 4px solid var(--primary);
      transition: all 0.3s;
    }
    
    .staff-card:hover {
      transform: translateX(5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .staff-card.selected {
      border-left-color: var(--success);
      background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    }
    
    .staff-card.auto-pay {
      border-left-color: var(--warning);
    }
    
    .staff-checkbox {
      width: 20px;
      height: 20px;
      cursor: pointer;
    }
    
    .staff-info {
      flex: 1;
    }
    
    .staff-name {
      font-weight: 600;
      color: var(--dark);
      font-size: 1.1rem;
      margin-bottom: 5px;
    }
    
    .staff-details {
      color: var(--dark);
      font-size: 0.85rem;
      opacity: 0.8;
    }
    
    .staff-details i {
      width: 20px;
    }
    
    .payment-amount {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--success);
    }
    
    .auto-badge {
      background: var(--warning);
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    /* Buttons */
    .btn-pay {
      background: linear-gradient(135deg, var(--success) 0%, #17a673 100%);
      border: none;
      color: white;
      padding: 12px 35px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .btn-pay:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(28, 200, 138, 0.4);
      color: white;
    }
    
    .btn-pay:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    .btn-auto {
      background: linear-gradient(135deg, var(--warning) 0%, #dda20a 100%);
      border: none;
      color: white;
      padding: 8px 20px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.9rem;
    }
    
    /* Transactions Table */
    .transactions-card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }
    
    .table {
      border-radius: 10px;
      overflow: hidden;
    }
    
    .table thead {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
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
      background: rgba(78, 115, 223, 0.05);
    }
    
    .badge-income {
      background: rgba(28, 200, 138, 0.1);
      color: var(--success);
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
    }
    
    .badge-expense {
      background: rgba(231, 74, 59, 0.1);
      color: var(--danger);
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
    }
    
    /* Modal */
    .modal-content {
      border-radius: 20px;
      border: none;
    }
    
    .modal-header {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      color: white;
      border-radius: 20px 20px 0 0;
    }
    
    .modal-header .btn-close {
      filter: brightness(0) invert(1);
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
    }
    
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    
    /* Loading */
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
    
    /* Summary Bar */
    .summary-bar {
      background: white;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .summary-item {
      text-align: center;
    }
    
    .summary-label {
      font-size: 0.85rem;
      color: var(--dark);
      opacity: 0.7;
      margin-bottom: 5px;
    }
    
    .summary-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
    }
    
    @media (max-width: 768px) {
      .main-container {
        padding: 15px;
      }
      
      .balance-amount {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner"></div>
  <p class="text-muted mt-3">Processing payment...</p>
</div>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

<!-- Navbar -->
<nav class="navbar navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="director_dashboard.php">
      <i class="fas fa-university me-2"></i>Dan4Christ Institute
    </a>
    <div class="d-flex align-items-center gap-3">
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
  <!-- Balance Cards -->
  <div class="row">
    <div class="col-md-4">
      <div class="balance-card income">
        <div class="balance-icon">
          <i class="fas fa-arrow-down"></i>
        </div>
        <div class="balance-label">Total Income</div>
        <div class="balance-amount">UGX <?php echo number_format($totalIncome, 0); ?></div>
        <small class="text-muted">From student payments</small>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="balance-card expense">
        <div class="balance-icon">
          <i class="fas fa-arrow-up"></i>
        </div>
        <div class="balance-label">Total Expenses</div>
        <div class="balance-amount">UGX <?php echo number_format($totalExpenses, 0); ?></div>
        <small class="text-muted">Staff payments</small>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="balance-card net">
        <div class="balance-icon">
          <i class="fas fa-wallet"></i>
        </div>
        <div class="balance-label">Net Balance</div>
        <div class="balance-amount">UGX <?php echo number_format($netBalance, 0); ?></div>
        <small class="text-muted">Available funds</small>
      </div>
    </div>
  </div>

  <!-- Chart Section -->
  <div class="chart-card">
    <h5>
      <i class="fas fa-chart-line text-primary"></i>
      Income vs Expenses (Last 6 Months)
    </h5>
    <canvas id="financialChart" height="100"></canvas>
  </div>

  <!-- Staff Payment Section -->
  <div class="payment-section">
    <div class="section-header">
      <h5>
        <i class="fas fa-money-bill-wave text-success"></i>
        Pay Staff
      </h5>
      <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#autoPaymentModal">
        <i class="fas fa-clock me-1"></i>Auto-Payment Settings
      </button>
    </div>
    
    <!-- Summary Bar -->
    <div class="summary-bar">
      <div class="summary-item">
        <div class="summary-label">Selected Staff</div>
        <div class="summary-value" id="selectedCount">0</div>
      </div>
      <div class="summary-item">
        <div class="summary-label">Total Amount</div>
        <div class="summary-value" id="totalAmount">UGX 0</div>
      </div>
      <div class="summary-item">
        <button class="btn btn-pay" id="payNowBtn" disabled>
          <i class="fas fa-paper-plane me-2"></i>Pay Now
        </button>
      </div>
    </div>
    
    <!-- Staff List -->
    <div id="staffList">
      <?php while($s = $staff->fetch_assoc()): ?>
        <div class="staff-card <?php echo $s['auto_enabled'] ? 'auto-pay' : ''; ?>" 
             data-staff-id="<?php echo $s['staff_id']; ?>"
             data-amount="<?php echo $s['auto_amount']; ?>"
             data-phone="<?php echo htmlspecialchars($s['phone']); ?>">
          <div class="d-flex align-items-center gap-3">
            <input type="checkbox" class="staff-checkbox" 
                   data-staff-id="<?php echo $s['staff_id']; ?>"
                   data-amount="<?php echo $s['auto_amount']; ?>"
                   data-phone="<?php echo htmlspecialchars($s['phone']); ?>"
                   data-name="<?php echo htmlspecialchars($s['fullname']); ?>">
            
            <div class="staff-info">
              <div class="staff-name">
                <?php echo htmlspecialchars($s['fullname']); ?>
                <?php if($s['auto_enabled']): ?>
                  <span class="auto-badge">
                    <i class="fas fa-clock me-1"></i>Auto (Day <?php echo $s['payment_day']; ?>)
                  </span>
                <?php endif; ?>
              </div>
              <div class="staff-details">
                <div><i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($s['staff_id']); ?></div>
                <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($s['phone'] ?: 'No phone'); ?></div>
                <div><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($s['position'] ?: 'Staff'); ?></div>
              </div>
            </div>
            
            <div class="text-end">
              <div class="payment-amount">
                UGX <?php echo number_format($s['auto_amount'], 0); ?>
              </div>
              <button class="btn btn-sm btn-outline-warning mt-2" 
                      onclick="setAutoPayment('<?php echo $s['staff_id']; ?>', '<?php echo htmlspecialchars($s['fullname']); ?>', <?php echo $s['auto_amount']; ?>, <?php echo $s['payment_day']; ?>)">
                <i class="fas fa-cog me-1"></i>Configure
              </button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="transactions-card">
    <div class="section-header">
      <h5>
        <i class="fas fa-history text-info"></i>
        Recent Transactions
      </h5>
      <button class="btn btn-sm btn-outline-info" onclick="refreshTransactions()">
        <i class="fas fa-sync-alt me-1"></i>Refresh
      </button>
    </div>
    
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Description</th>
            <th>Reference</th>
            <th class="text-end">Amount</th>
          </tr>
        </thead>
        <tbody id="transactionsBody">
          <?php while($t = $transactions->fetch_assoc()): ?>
            <tr>
              <td><?php echo date('M d, Y H:i', strtotime($t['transaction_date'])); ?></td>
              <td>
                <?php if($t['type'] == 'income'): ?>
                  <span class="badge-income">
                    <i class="fas fa-arrow-down me-1"></i>Income
                  </span>
                <?php else: ?>
                  <span class="badge-expense">
                    <i class="fas fa-arrow-up me-1"></i>Expense
                  </span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($t['description']); ?></td>
              <td><code><?php echo htmlspecialchars($t['reference']); ?></code></td>
              <td class="text-end">
                <strong class="<?php echo $t['type'] == 'income' ? 'text-success' : 'text-danger'; ?>">
                  <?php echo $t['type'] == 'income' ? '+' : '-'; ?>
                  UGX <?php echo number_format($t['amount'], 0); ?>
                </strong>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Auto Payment Settings Modal -->
<div class="modal fade" id="autoPaymentModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-clock me-2"></i>Auto-Payment Settings
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="autoPaymentContent">
        <div class="text-center py-5">
          <div class="spinner-border text-primary"></div>
          <p class="mt-3">Loading...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Configure Auto Payment Modal -->
<div class="modal fade" id="configureAutoModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-cog me-2"></i>Configure Auto-Payment
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="autoPaymentForm">
          <input type="hidden" name="staff_id" id="auto_staff_id">
          
          <div class="mb-3">
            <label class="form-label">Staff Member</label>
            <input type="text" class="form-control" id="auto_staff_name" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Payment Amount (UGX) *</label>
            <input type="number" name="amount" id="auto_amount" class="form-control" required min="0" step="0.01">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Payment Day of Month *</label>
            <select name="payment_day" id="auto_payment_day" class="form-select" required>
              <?php for($i = 1; $i <= 31; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
              <?php endfor; ?>
            </select>
            <small class="text-muted">Staff will be paid automatically on this day each month</small>
          </div>
          
          <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" id="auto_is_active" class="form-check-input" checked>
            <label class="form-check-label" for="auto_is_active">
              Enable auto-payment
            </label>
          </div>
          
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary flex-fill">
              <i class="fas fa-save me-2"></i>Save Settings
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Initialize Chart
const ctx = document.getElementById('financialChart').getContext('2d');
const financialChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: [],
    datasets: [
      {
        label: 'Income',
        data: [],
        borderColor: '#1cc88a',
        backgroundColor: 'rgba(28, 200, 138, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Expenses',
        data: [],
        borderColor: '#e74a3b',
        backgroundColor: 'rgba(231, 74, 59, 0.1)',
        tension: 0.4,
        fill: true
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        position: 'top',
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return 'UGX ' + value.toLocaleString();
          }
        }
      }
    }
  }
});

// Load chart data
function loadChartData() {
  $.ajax({
    url: 'fetch_account_data.php',
    type: 'GET',
    data: { action: 'chart_data' },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        financialChart.data.labels = response.labels;
        financialChart.data.datasets[0].data = response.income;
        financialChart.data.datasets[1].data = response.expenses;
        financialChart.update();
      }
    }
  });
}

loadChartData();

// Staff checkbox handling
$('.staff-checkbox').on('change', function() {
  updatePaymentSummary();
  
  if ($(this).is(':checked')) {
    $(this).closest('.staff-card').addClass('selected');
  } else {
    $(this).closest('.staff-card').removeClass('selected');
  }
});

// Update payment summary
function updatePaymentSummary() {
  let count = 0;
  let total = 0;
  
  $('.staff-checkbox:checked').each(function() {
    count++;
    total += parseFloat($(this).data('amount')) || 0;
  });
  
  $('#selectedCount').text(count);
  $('#totalAmount').text('UGX ' + total.toLocaleString());
  
  if (count > 0) {
    $('#payNowBtn').prop('disabled', false);
  } else {
    $('#payNowBtn').prop('disabled', true);
  }
}

// Pay Now button
$('#payNowBtn').on('click', function() {
  const selectedStaff = [];
  
  $('.staff-checkbox:checked').each(function() {
    selectedStaff.push({
      staff_id: $(this).data('staff-id'),
      amount: parseFloat($(this).data('amount')),
      phone: $(this).data('phone'),
      name: $(this).data('name')
    });
  });
  
  if (selectedStaff.length === 0) {
    showAlert('Please select at least one staff member', 'warning');
    return;
  }
  
  if (!confirm(`You are about to pay ${selectedStaff.length} staff member(s). Continue?`)) {
    return;
  }
  
  $('#loadingOverlay').addClass('show');
  
  $.ajax({
    url: 'process_staff_payment.php',
    type: 'POST',
    data: { staff: JSON.stringify(selectedStaff) },
    dataType: 'json',
    success: function(response) {
      $('#loadingOverlay').removeClass('show');
      
      if (response.success) {
        showAlert(response.message, 'success');
        
        setTimeout(function() {
          location.reload();
        }, 2000);
      } else {
        showAlert(response.message, 'danger');
      }
    },
    error: function() {
      $('#loadingOverlay').removeClass('show');
      showAlert('Error processing payment', 'danger');
    }
  });
});

// Set auto payment
function setAutoPayment(staffId, staffName, amount, day) {
  $('#auto_staff_id').val(staffId);
  $('#auto_staff_name').val(staffName);
  $('#auto_amount').val(amount);
  $('#auto_payment_day').val(day);
  
  const modal = new bootstrap.Modal(document.getElementById('configureAutoModal'));
  modal.show();
}

// Auto payment form submission
$('#autoPaymentForm').on('submit', function(e) {
  e.preventDefault();
  
  const formData = $(this).serialize();
  
  $.ajax({
    url: 'save_auto_payment.php',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        showAlert(response.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('configureAutoModal')).hide();
        
        setTimeout(function() {
          location.reload();
        }, 1500);
      } else {
        showAlert(response.message, 'danger');
      }
    },
    error: function() {
      showAlert('Error saving settings', 'danger');
    }
  });
});

// Refresh transactions
function refreshTransactions() {
  $.ajax({
    url: 'fetch_account_data.php',
    type: 'GET',
    data: { action: 'transactions' },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        let html = '';
        response.data.forEach(function(t) {
          html += `
            <tr>
              <td>${t.transaction_date}</td>
              <td>
                ${t.type == 'income' 
                  ? '<span class="badge-income"><i class="fas fa-arrow-down me-1"></i>Income</span>'
                  : '<span class="badge-expense"><i class="fas fa-arrow-up me-1"></i>Expense</span>'}
              </td>
              <td>${t.description}</td>
              <td><code>${t.reference}</code></td>
              <td class="text-end">
                <strong class="${t.type == 'income' ? 'text-success' : 'text-danger'}">
                  ${t.type == 'income' ? '+' : '-'} UGX ${parseFloat(t.amount).toLocaleString()}
                </strong>
              </td>
            </tr>
          `;
        });
        $('#transactionsBody').html(html);
        showAlert('Transactions refreshed', 'success');
      }
    }
  });
}

// Show Alert
function showAlert(message, type) {
  const icons = {
    success: 'fa-check-circle',
    danger: 'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle'
  };
  
  const alertHtml = `
    <div class="custom-alert alert alert-${type}">
      <i class="fas ${icons[type]} me-2"></i>
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