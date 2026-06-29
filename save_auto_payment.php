<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$staff_id = trim($_POST['staff_id']);
$amount = floatval($_POST['amount']);
$payment_day = intval($_POST['payment_day']);
$is_active = isset($_POST['is_active']) ? 1 : 0;

if (empty($staff_id) || $amount <= 0 || $payment_day < 1 || $payment_day > 31) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    // Check if setting exists
    $check = $conn->prepare("SELECT id FROM auto_payment_settings WHERE staff_id = ?");
    $check->bind_param("s", $staff_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE auto_payment_settings 
            SET amount = ?, payment_day = ?, is_active = ? 
            WHERE staff_id = ?");
        $stmt->bind_param("diis", $amount, $payment_day, $is_active, $staff_id);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO auto_payment_settings 
            (staff_id, amount, payment_day, is_active) 
            VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdii", $staff_id, $amount, $payment_day, $is_active);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Auto-payment settings saved successfully!'
        ]);
    } else {
        throw new Exception('Failed to save: ' . $stmt->error);
    }
    
    $stmt->close();
    $check->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>