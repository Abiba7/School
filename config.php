<?php
$host = 'localhost';
$dbname = 'schoolhub';
$user = 'root';
$pass = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// =============================================
// HELPER FUNCTIONS
// =============================================

function getTotalStudents($pdo) {
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM students WHERE status='Active'");
        return $result ? $result->fetchColumn() : 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function getTotalTeachers($pdo) {
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM teachers WHERE status='Active'");
        return $result ? $result->fetchColumn() : 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function getTotalClasses($pdo) {
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM classes WHERE status='Active'");
        return $result ? $result->fetchColumn() : 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function getTotalSubjects($pdo) {
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM subjects WHERE status='Active'");
        return $result ? $result->fetchColumn() : 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function getTodayAttendance($pdo) {
    try {
        $today = date('Y-m-d');
        $result = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = '$today' AND status='Present'");
        return $result ? $result->fetchColumn() : 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function getTotalFeesCollected($pdo) {
    try {
        $result = $pdo->query("SELECT SUM(amount_paid) FROM fees WHERE status='Paid' OR status='Partial'");
        return $result ? ($result->fetchColumn() ?? 0) : 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function logActivity($pdo, $user_id, $action, $description = '') {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $description, $ip]);
    } catch(PDOException $e) {
        // Silently fail
    }
}
?>