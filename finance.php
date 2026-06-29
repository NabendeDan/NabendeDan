<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    header("Location: login.php");
    exit();
}

$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : 'uploads/default.png';
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Director';

// Get statistics
$totalRevenue = 0;
$totalPayments = 0;
$uniqueStudents = 0;
$averagePayment = 0;

$result = $conn->query("SELECT 
    COALESCE(SUM(amount), 0) as total_revenue,
    COUNT(*) as total_payments,
    COUNT(DISTINCT regno) as unique_students,
    COALESCE(AVG(amount), 0) as avg_payment
    FROM payments");

if ($result && $row = $result->fetch_assoc()) {
    $totalRevenue = number_format($row['total_revenue'], 2);
    $totalPayments = $row['total_payments'];
    $uniqueStudents = $row['unique_students'];
    $averagePayment = number_format($row['avg_payment'], 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Finance Records - Dan4Christ Institute</title>
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
      padding: 0 30px 30px;
    }
    
    /* Header */
    .page-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 20px;
      padding: 30px;
      color: white;
      margin-bottom: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
    }
    
    .page-header h2 {
      font-weight: 700;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .header-icon {
      width: 60px;
      height: 60px;
      background: rgba(255,255,255,0.2);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }
    
    /* Stats Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: all 0.3s;
      border-left: 4px solid;
      position: relative;
      overflow: hidden;
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, rgba(78, 115, 223, 0.1) 0%, rgba(34, 74, 190, 0.1) 100%);
      border-radius: 50%;
      transform: translate(30%, -30%);
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .stat-card.revenue { border-left-color: var(--success); }
    .stat-card.payments { border-left-color: var(--primary); }
    .stat-card.students { border-left-color: var(--warning); }
    .stat-card.average { border-left-color: var(--info); }
    
    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }
    
    .stat-card.revenue .stat-icon { background: rgba(28, 200, 138, 0.1); color: var(--success); }
    .stat-card.payments .stat-icon { background: rgba(78, 115, 223, 0.1); color: var(--primary); }
    .stat-card.students .stat-icon { background: rgba(246, 194, 62, 0.1); color: var(--warning); }
    .stat-card.average .stat-icon { background: rgba(54, 185, 204, 0.1); color: var(--info); }
    
    .stat-value {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 5px;
      position: relative;
      z-index: 1;
    }
    
    .stat-label {
      color: var(--secondary);
      font-size: 0.9rem;
      font-weight: 500;
      position: relative;
      z-index: 1;
    }
    
    /* Controls Section */
    .controls-section {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      margin-bottom: 25px;
    }
    
    .controls-row {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      align-items: center;
    }
    
    .search-box {
      flex: 1;
      min-width: 250px;
      position: relative;
    }
    
    .search-box input {
      width: 100%;
      padding: 12px 45px 12px 15px;
      border: 2px solid #e3e6f0;
      border-radius: 10px;
      font-size: 0.95rem;
      transition: all 0.3s;
    }
    
    .search-box input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
    }
    
    .search-box i {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--secondary);
    }
    
    .filter-select {
      padding: 12px 15px;
      border: 2px solid #e3e6f0;
      border-radius: 10px;
      font-size: 0.95rem;
      min-width: 150px;
      transition: all 0.3s;
    }
    
    .filter-select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
    }
    
    .btn-action {
      padding: 12px 25px;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 8px;
      border: none;
      cursor: pointer;
    }
    
    .btn-refresh {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      color: white;
    }
    
    .btn-refresh:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
    }
    
    .btn-back {
      background: linear-gradient(135deg, var(--warning) 0%, #dda20a 100%);
      color: white;
    }
    
    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(246, 194, 62, 0.4);
    }
    
    /* Table Card */
    .table-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      margin-bottom: 30px;
    }
    
    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #e3e6f0;
    }
    
    .table-title {
      font-weight: 700;
      color: var(--dark);
      font-size: 1.3rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .record-count {
      background: var(--primary);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
    }
    
    .table-container {
      max-height: 600px;
      overflow-y: auto;
      border-radius: 10px;
    }
    
    .table {
      margin-bottom: 0;
    }
    
    .table thead th {
      background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
      color: white;
      font-weight: 600;
      border: none;
      padding: 15px;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    
    .table tbody td {
      padding: 15px;
      vertical-align: middle;
      border-bottom: 1px solid #e3e6f0;
    }
    
    .table tbody tr {
      transition: all 0.3s;
    }
    
    .table tbody tr:hover {
      background: #f8f9fc;
      transform: scale(1.01);
    }
    
    .amount-cell {
      font-weight: 700;
      color: var(--success);
    }
    
    .badge-purpose {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    
    .badge-tuition { background: rgba(78, 115, 223, 0.1); color: var(--primary); }
    .badge-exam { background: rgba(246, 194, 62, 0.1); color: var(--warning); }
    .badge-registration { background: rgba(28, 200, 138, 0.1); color: var(--success); }
    .badge-other { background: rgba(133, 135, 150, 0.1); color: var(--secondary); }
    
    /* Loading Overlay */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255,255,255,0.95);
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
    
    @keyframes spin {
      to { transform: rotate(360deg); }
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
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--secondary);
    }
    
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      opacity: 0.3;
    }
    
    .empty-state h5 {
      font-weight: 600;
      margin-bottom: 10px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .main-container {
        padding: 0 15px 15px;
      }
      
      .page-header {
        padding: 20px;
      }
      
      .page-header h2 {
        font-size: 1.5rem;
      }
      
      .controls-row {
        flex-direction: column;
      }
      
      .search-box, .filter-select {
        width: 100%;
      }
      
      .table-container {
        font-size: 0.85rem;
      }
      
      .table thead th, .table tbody td {
        padding: 10px 8px;
      }
    }
  </style>
</head>
<body>

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
    <h2>
      <div class="header-icon">
        <i class="fas fa-wallet"></i>
      </div>
      Finance Records
    </h2>
    <button onclick="history.back()" class="btn-action btn-back">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </button>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-grid">
    <div class="stat-card revenue">
      <div class="stat-icon">
        <i class="fas fa-dollar-sign"></i>
      </div>
      <div class="stat-value" id="totalRevenue"><?php echo $totalRevenue; ?></div>
      <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card payments">
      <div class="stat-icon">
        <i class="fas fa-receipt"></i>
      </div>
      <div class="stat-value" id="totalPayments"><?php echo $totalPayments; ?></div>
      <div class="stat-label">Total Payments</div>
    </div>
    <div class="stat-card students">
      <div class="stat-icon">
        <i class="fas fa-user-graduate"></i>
      </div>
      <div class="stat-value" id="uniqueStudents"><?php echo $uniqueStudents; ?></div>
      <div class="stat-label">Paying Students</div>
    </div>
    <div class="stat-card average">
      <div class="stat-icon">
        <i class="fas fa-chart-line"></i>
      </div>
      <div class="stat-value" id="averagePayment"><?php echo $averagePayment; ?></div>
      <div class="stat-label">Average Payment</div>
    </div>
  </div>

  <!-- Controls Section -->
  <div class="controls-section">
    <div class="controls-row">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search by student name, regno, or purpose...">
        <i class="fas fa-search"></i>
      </div>
      <select id="yearFilter" class="filter-select">
        <option value="">All Years</option>
      </select>
      <select id="semesterFilter" class="filter-select">
        <option value="">All Semesters</option>
        <option value="1">Semester 1</option>
        <option value="2">Semester 2</option>
        <option value="3">Semester 3</option>
      </select>
      <button class="btn-action btn-refresh" onclick="refreshData()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>
  </div>

  <!-- Table Card -->
  <div class="table-card">
    <div class="table-header">
      <div class="table-title">
        <i class="fas fa-list"></i>
        Payment Records
      </div>
      <span class="record-count" id="recordCount">0 records</span>
    </div>
    
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Student Name</th>
            <th>RegNo</th>
            <th>Amount</th>
            <th>Purpose</th>
            <th>Semester</th>
            <th>Year</th>
            <th>Date Paid</th>
          </tr>
        </thead>
        <tbody id="paymentsTableBody">
          <!-- Data will be loaded via AJAX -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
let allPayments = [];

// Load data on page load
$(document).ready(function() {
  loadPayments();
  
  // Search functionality
  $('#searchInput').on('input', function() {
    filterData();
  });
  
  // Year filter
  $('#yearFilter').on('change', function() {
    filterData();
  });
  
  // Semester filter
  $('#semesterFilter').on('change', function() {
    filterData();
  });
});

// Load Payments via AJAX
function loadPayments() {
  $('#loadingOverlay').addClass('show');
  
  $.ajax({
    url: 'ajax_finance.php',
    type: 'POST',
    data: { action: 'load_all' },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        allPayments = response.data;
        renderTable(allPayments);
        populateYearFilter(allPayments);
        updateStatistics(response.stats);
      } else {
        showAlert('Failed to load payments', 'danger');
      }
    },
    error: function(xhr, status, error) {
      showAlert('Error loading data: ' + error, 'danger');
    },
    complete: function() {
      $('#loadingOverlay').removeClass('show');
    }
  });
}

// Render Table
function renderTable(data) {
  const tbody = $('#paymentsTableBody');
  tbody.empty();
  
  if (data.length === 0) {
    tbody.html(`
      <tr>
        <td colspan="8">
          <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5>No payment records found</h5>
            <p>Try adjusting your search or filters</p>
          </div>
        </td>
      </tr>
    `);
    $('#recordCount').text('0 records');
    return;
  }
  
  data.forEach(function(row) {
    const purposeBadge = getPurposeBadge(row.purpose);
    const formattedAmount = formatCurrency(row.amount);
    
    tbody.append(`
      <tr>
        <td><strong>#${escapeHtml(row.payment_id)}</strong></td>
        <td>${escapeHtml(row.fullname)}</td>
        <td><code>${escapeHtml(row.regno)}</code></td>
        <td class="amount-cell">${formattedAmount}</td>
        <td>${purposeBadge}</td>
        <td>Sem ${escapeHtml(row.semester)}</td>
        <td>${escapeHtml(row.year)}</td>
        <td>${formatDate(row.date_paid)}</td>
      </tr>
    `);
  });
  
  $('#recordCount').text(data.length + ' records');
}

// Filter Data
function filterData() {
  const searchTerm = $('#searchInput').val().toLowerCase();
  const yearFilter = $('#yearFilter').val();
  const semesterFilter = $('#semesterFilter').val();
  
  const filtered = allPayments.filter(function(row) {
    const matchesSearch = !searchTerm || 
      row.fullname.toLowerCase().includes(searchTerm) ||
      row.regno.toLowerCase().includes(searchTerm) ||
      row.purpose.toLowerCase().includes(searchTerm);
    
    const matchesYear = !yearFilter || row.year == yearFilter;
    const matchesSemester = !semesterFilter || row.semester == semesterFilter;
    
    return matchesSearch && matchesYear && matchesSemester;
  });
  
  renderTable(filtered);
}

// Populate Year Filter
function populateYearFilter(data) {
  const years = [...new Set(data.map(row => row.year))].sort().reverse();
  const select = $('#yearFilter');
  
  years.forEach(function(year) {
    if (!select.find(`option[value="${year}"]`).length) {
      select.append(`<option value="${year}">${year}</option>`);
    }
  });
}

// Update Statistics
function updateStatistics(stats) {
  $('#totalRevenue').text(formatCurrency(stats.total_revenue));
  $('#totalPayments').text(stats.total_payments);
  $('#uniqueStudents').text(stats.unique_students);
  $('#averagePayment').text(formatCurrency(stats.avg_payment));
}

// Refresh Data
function refreshData() {
  loadPayments();
  showAlert('Data refreshed successfully!', 'success');
}

// Helper Functions
function getPurposeBadge(purpose) {
  const purposeLower = purpose.toLowerCase();
  let badgeClass = 'badge-other';
  
  if (purposeLower.includes('tuition')) badgeClass = 'badge-tuition';
  else if (purposeLower.includes('exam')) badgeClass = 'badge-exam';
  else if (purposeLower.includes('registration') || purposeLower.includes('reg')) badgeClass = 'badge-registration';
  
  return `<span class="badge-purpose ${badgeClass}">${escapeHtml(purpose)}</span>`;
}

function formatCurrency(amount) {
  return 'UGX ' + parseFloat(amount).toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  });
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
}

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