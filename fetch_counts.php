<?php
include 'db.php';

// Fetch counts
$studentCount = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$staffCount   = $conn->query("SELECT COUNT(*) AS total FROM staff")->fetch_assoc()['total'];

echo json_encode([
    "students" => $studentCount,
    "staff"    => $staffCount
]);
