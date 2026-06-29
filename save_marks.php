<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$regno = isset($_POST['regno']) ? trim($_POST['regno']) : '';
$year = isset($_POST['year']) ? trim($_POST['year']) : '';
$semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';
$marks = isset($_POST['marks']) ? $_POST['marks'] : [];

if (empty($regno) || empty($year) || empty($semester)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

if (empty($marks) || !is_array($marks)) {
    echo json_encode(['success' => false, 'message' => 'No marks provided']);
    exit();
}

$saved = 0;
$errors = [];

foreach ($marks as $markData) {
    $unit_id = isset($markData['unit_id']) ? $markData['unit_id'] : '';
    $score = isset($markData['score']) ? $markData['score'] : '';
    
    if (empty($unit_id) || $score === '') {
        continue;
    }
    
    if (!is_numeric($score) || $score < 0 || $score > 100) {
        $errors[] = "Invalid mark for unit $unit_id";
        continue;
    }
    
    $score = floatval($score);
    
    // Use REPLACE INTO to insert or update (works with composite primary key)
    $stmt = $conn->prepare("REPLACE INTO marks (regno, unit_id, semester, year, score) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        $errors[] = "Database error: " . $conn->error;
        continue;
    }
    
    $stmt->bind_param("ssssd", $regno, $unit_id, $semester, $year, $score);
    
    if ($stmt->execute()) {
        $saved++;
    } else {
        $errors[] = "Failed to save mark for unit $unit_id: " . $stmt->error;
    }
    
    $stmt->close();
}

if (empty($errors)) {
    echo json_encode([
        'success' => true,
        'message' => "Marks saved successfully for $saved unit(s)!"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Errors: " . implode(", ", $errors)
    ]);
}

$conn->close();
?>