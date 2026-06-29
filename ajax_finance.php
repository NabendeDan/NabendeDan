<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'load_all') {
    // Get all payments
    $query = "SELECT p.payment_id, p.regno, p.amount, p.purpose, p.semester, p.year, p.date_paid, s.fullname 
              FROM payments p 
              JOIN students s ON p.regno = s.regno 
              ORDER BY p.date_paid DESC";
    
    $result = $conn->query($query);
    $payments = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
    }
    
    // Get statistics
    $statsQuery = "SELECT 
        COALESCE(SUM(amount), 0) as total_revenue,
        COUNT(*) as total_payments,
        COUNT(DISTINCT regno) as unique_students,
        COALESCE(AVG(amount), 0) as avg_payment
        FROM payments";
    
    $statsResult = $conn->query($statsQuery);
    $stats = $statsResult->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'data' => $payments,
        'stats' => $stats
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>