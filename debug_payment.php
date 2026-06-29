<?php
session_start();
include 'db.php';

echo "<h1>Payment Debug Information</h1>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;} .box{background:white;padding:20px;margin:10px 0;border-radius:10px;box-shadow:0 2px 5px rgba(0,0,0,0.1);} .error{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;} .success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;} table{width:100%;border-collapse:collapse;} th,td{padding:10px;border:1px solid #ddd;text-align:left;} th{background:#1cc88a;color:white;} code{background:#f4f4f4;padding:2px 6px;border-radius:3px;}</style>";

// Check session
echo "<div class='box'>";
echo "<h2>1. Session Information</h2>";
if (isset($_SESSION['regno'])) {
    echo "<div class='success'>✓ Session regno is set: <strong>" . htmlspecialchars($_SESSION['regno']) . "</strong></div>";
} else {
    echo "<div class='error'>✗ Session regno is NOT set!</div>";
}
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "</div>";

// Check if student exists
if (isset($_SESSION['regno'])) {
    echo "<div class='box'>";
    echo "<h2>2. Student Existence Check</h2>";
    $regno = $_SESSION['regno'];
    
    $checkStudent = $conn->prepare("SELECT regno, fullname FROM students WHERE regno = ?");
    if (!$checkStudent) {
        echo "<div class='error'>✗ Prepare failed: " . $conn->error . "</div>";
    } else {
        $checkStudent->bind_param("s", $regno);
        $checkStudent->execute();
        $studentResult = $checkStudent->get_result();
        
        if ($studentResult->num_rows > 0) {
            $student = $studentResult->fetch_assoc();
            echo "<div class='success'>✓ Student found: " . htmlspecialchars($student['fullname']) . "</div>";
        } else {
            echo "<div class='error'>✗ Student NOT found in database!</div>";
        }
        $checkStudent->close();
    }
    echo "</div>";
    
    // Check payment codes for this student - DIRECT QUERY WITHOUT PREPARE
    echo "<div class='box'>";
    echo "<h2>3. Payment Codes for This Student</h2>";
    
    // First check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'payment_codes'");
    if ($tableCheck->num_rows === 0) {
        echo "<div class='error'>✗ payment_codes table does NOT exist!</div>";
    } else {
        echo "<div class='success'>✓ payment_codes table exists</div>";
        
        // Check table structure
        echo "<h3>Table Structure:</h3>";
        $structure = $conn->query("DESCRIBE payment_codes");
        if ($structure) {
            echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            while ($col = $structure->fetch_assoc()) {
                echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
            }
            echo "</table>";
        }
        
        // Try direct query instead of prepared statement
        $safeRegno = $conn->real_escape_string($regno);
        $codesQuery = $conn->query("SELECT code_id, code, regno, created_at, used_at FROM payment_codes WHERE regno = '$safeRegno' ORDER BY code_id DESC");
        
        if ($codesQuery) {
            if ($codesQuery->num_rows > 0) {
                echo "<div class='success'>✓ Found " . $codesQuery->num_rows . " payment code(s)</div>";
                echo "<table>";
                echo "<tr><th>Code ID</th><th>Code</th><th>RegNo</th><th>Created</th><th>Used</th></tr>";
                while ($code = $codesQuery->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><strong>" . htmlspecialchars($code['code_id']) . "</strong> ← Enter this number</td>";
                    echo "<td>" . htmlspecialchars($code['code']) . "</td>";
                    echo "<td>" . htmlspecialchars($code['regno']) . "</td>";
                    echo "<td>" . htmlspecialchars($code['created_at']) . "</td>";
                    echo "<td>" . ($code['used_at'] ? htmlspecialchars($code['used_at']) : 'Not used') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='error'>✗ No payment codes found for regno: " . htmlspecialchars($regno) . "</div>";
                echo "<p>You need to ask the director to generate a payment code for you.</p>";
            }
        } else {
            echo "<div class='error'>✗ Query failed: " . $conn->error . "</div>";
        }
    }
    echo "</div>";
    
    // Check all payment codes
    echo "<div class='box'>";
    echo "<h2>4. All Payment Codes in Database (Last 10)</h2>";
    $allCodes = $conn->query("SELECT code_id, code, regno, created_at FROM payment_codes ORDER BY code_id DESC LIMIT 10");
    
    if ($allCodes) {
        if ($allCodes->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Code ID</th><th>Code</th><th>RegNo</th><th>Created</th></tr>";
            while ($row = $allCodes->fetch_assoc()) {
                $highlight = ($row['regno'] == $regno) ? "style='background:#d4edda;'" : "";
                echo "<tr $highlight>";
                echo "<td>" . htmlspecialchars($row['code_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['code']) . "</td>";
                echo "<td>" . htmlspecialchars($row['regno']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p><em>Rows highlighted in green are for your account</em></p>";
        } else {
            echo "<div class='error'>✗ No payment codes in database at all!</div>";
        }
    } else {
        echo "<div class='error'>✗ Query failed: " . $conn->error . "</div>";
    }
    echo "</div>";
}

// Test section
if (isset($_SESSION['regno']) && isset($_POST['test_code_id'])) {
    $test_code_id = intval($_POST['test_code_id']);
    $test_regno = $_SESSION['regno'];
    
    echo "<div class='box'>";
    echo "<h2>5. Testing Code ID: $test_code_id</h2>";
    
    // Direct query
    $safeRegno = $conn->real_escape_string($test_regno);
    $testQuery = $conn->query("SELECT s.fullname, s.regno, pc.code, pc.code_id
            FROM payment_codes pc
            JOIN students s ON pc.regno = s.regno
            WHERE pc.code_id = $test_code_id AND pc.regno = '$safeRegno'");
    
    if ($testQuery) {
        if ($testQuery->num_rows > 0) {
            $data = $testQuery->fetch_assoc();
            echo "<div class='success'>✓ Query returned result!</div>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else {
            echo "<div class='error'>✗ Query returned NO results for code_id=$test_code_id and regno=" . htmlspecialchars($test_regno) . "</div>";
            
            // Check if code exists at all
            $checkAny = $conn->query("SELECT code_id, regno FROM payment_codes WHERE code_id = $test_code_id");
            if ($checkAny && $checkAny->num_rows > 0) {
                $anyData = $checkAny->fetch_assoc();
                echo "<div class='error'>⚠ Code ID $test_code_id EXISTS but belongs to: <strong>" . htmlspecialchars($anyData['regno']) . "</strong></div>";
                echo "<p>Your regno is: <strong>" . htmlspecialchars($test_regno) . "</strong></p>";
                if ($anyData['regno'] != $test_regno) {
                    echo "<p style='color:red;font-weight:bold;font-size:18px;'>⚠ MISMATCH! This code belongs to a different student!</p>";
                }
            } else {
                echo "<div class='error'>✗ Code ID $test_code_id does NOT exist in database!</div>";
            }
        }
    } else {
        echo "<div class='error'>✗ Query failed: " . $conn->error . "</div>";
    }
    echo "</div>";
}
?>

<div class='box'>
    <h2>6. Test Payment Code Lookup</h2>
    <form method="POST">
        <label><strong>Enter a Code ID to test:</strong></label>
        <input type="number" name="test_code_id" required placeholder="e.g., 1, 2, 3..." style="padding:10px;font-size:16px;width:200px;">
        <button type="submit" style="padding:10px 20px;font-size:16px;background:#1cc88a;color:white;border:none;border-radius:5px;cursor:pointer;">Test Query</button>
    </form>
</div>

<div class='box'>
    <h2>7. Fix Suggestions</h2>
    <ul>
        <li>If no codes exist for your account, ask the director to generate one</li>
        <li>If codes exist but don't match your regno, there's a session issue - try logging out and back in</li>
        <li>Make sure you're using the correct code_id from the table above</li>
        <li>The code_id is the NUMBER (1, 2, 3...), NOT the code (1001, 1002...)</li>
    </ul>
    <p><a href="student_dashboard.php" style="background:#6c757d;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;">Back to Dashboard</a></p>
</div>