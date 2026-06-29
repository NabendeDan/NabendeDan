<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Director') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['staff'])) {
    echo json_encode(['success' => false, 'message' => 'No staff selected']);
    exit();
}

$staffList = json_decode($_POST['staff'], true);

if (empty($staffList)) {
    echo json_encode(['success' => false, 'message' => 'Invalid staff data']);
    exit();
}

try {
    $conn->begin_transaction();
    
    $paidCount = 0;
    $totalAmount = 0;
    
    foreach ($staffList as $staff) {
        $staff_id = $staff['staff_id'];
        $amount = floatval($staff['amount']);
        $phone = $staff['phone'];
        
        if ($amount <= 0) continue;
        
        // Generate reference
        $reference = 'PAY-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        // Insert payment record - FIXED bind_param
        $stmt = $conn->prepare("INSERT INTO staff_payments 
            (staff_id, amount, payment_method, phone_number, payment_type, status, reference, paid_by) 
            VALUES (?, ?, 'Mobile Money', ?, 'manual', 'completed', ?, ?)");
        
        $paid_by = $_SESSION['fullname'];
        // Changed from "sdssss" to "sdsss" (5 parameters, not 6)
        $stmt->bind_param("sdsss", $staff_id, $amount, $phone, $reference, $paid_by);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert payment for staff $staff_id: " . $stmt->error);
        }
        $stmt->close();
        
        // Insert into transactions
        $description = "Staff Payment - " . $staff['name'];
        $stmt = $conn->prepare("INSERT INTO transactions (type, amount, description, reference, category) 
            VALUES ('expense', ?, ?, ?, 'Staff Payment')");
        $stmt->bind_param("dss", $amount, $description, $reference);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert transaction: " . $stmt->error);
        }
        $stmt->close();
        
        $paidCount++;
        $totalAmount += $amount;
        
        // TODO: Integrate with Mobile Money API here
        // This is where you'd call MTN/Airtel API to send money to $phone
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully paid $paidCount staff member(s). Total: UGX " . number_format($totalAmount, 0)
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>