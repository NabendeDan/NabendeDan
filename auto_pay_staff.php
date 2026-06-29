<?php
include 'db.php';

// Get today's day
$today = date('j'); // Day of month without leading zeros

// Find staff who should be paid today
$query = "SELECT s.staff_id, s.fullname, s.phone, aps.amount 
          FROM auto_payment_settings aps
          JOIN staff s ON aps.staff_id = s.staff_id
          WHERE aps.payment_day = ? AND aps.is_active = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $today);
$stmt->execute();
$result = $stmt->get_result();

while ($staff = $result->fetch_assoc()) {
    // Process auto payment
    $reference = 'AUTO-' . date('YmdHis') . '-' . rand(1000, 9999);
    
    $insert = $conn->prepare("INSERT INTO staff_payments 
        (staff_id, amount, payment_method, phone_number, payment_type, status, reference) 
        VALUES (?, ?, 'Mobile Money', ?, 'auto', 'completed', ?)");
    $insert->bind_param("sdss", $staff['staff_id'], $staff['amount'], $staff['phone'], $reference);
    $insert->execute();
    $insert->close();
    
    // Add to transactions
    $description = "Auto Staff Payment - " . $staff['fullname'];
    $trans = $conn->prepare("INSERT INTO transactions (type, amount, description, reference, category) 
        VALUES ('expense', ?, ?, ?, 'Auto Staff Payment')");
    $trans->bind_param("dss", $staff['amount'], $description, $reference);
    $trans->execute();
    $trans->close();
}

$stmt->close();
$conn->close();

echo "Auto-payment processed for " . date('Y-m-d') . "\n";
?>