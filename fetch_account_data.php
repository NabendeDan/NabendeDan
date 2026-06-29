<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'chart_data') {
    // Get last 6 months of data
    $labels = [];
    $incomeData = [];
    $expenseData = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $labels[] = date('M Y', strtotime("-$i months"));
        
        // Income
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total 
            FROM payments WHERE DATE_FORMAT(date_paid, '%Y-%m') = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $incomeData[] = floatval($result['total']);
        $stmt->close();
        
        // Expenses
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total 
            FROM staff_payments 
            WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? AND status='completed'");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $expenseData[] = floatval($result['total']);
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'income' => $incomeData,
        'expenses' => $expenseData
    ]);
    
} elseif ($action == 'transactions') {
    $query = "SELECT * FROM (
        SELECT 'income' as type, amount, 'Student Payment' as description, 
               date_paid as transaction_date, regno as reference
        FROM payments
        UNION ALL
        SELECT 'expense' as type, amount, 
               CONCAT('Staff Payment - ', s.fullname) as description, 
               payment_date as transaction_date, sp.reference
        FROM staff_payments sp
        JOIN staff s ON sp.staff_id = s.staff_id
    ) as all_transactions
    ORDER BY transaction_date DESC LIMIT 20";
    
    $result = $conn->query($query);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $row['transaction_date'] = date('M d, Y H:i', strtotime($row['transaction_date']));
        $data[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}

$conn->close();
?>