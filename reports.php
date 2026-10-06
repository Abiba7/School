<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// =============================================
// FETCH ALL REPORT DATA
// =============================================

// Total counts
try {
    $total_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status='Active'")->fetchColumn() ?: 0;
    $total_teachers = $pdo->query("SELECT COUNT(*) FROM teachers WHERE status='Active'")->fetchColumn() ?: 0;
    $total_classes = $pdo->query("SELECT COUNT(*) FROM classes WHERE status='Active'")->fetchColumn() ?: 0;
    $total_subjects = $pdo->query("SELECT COUNT(*) FROM subjects WHERE status='Active'")->fetchColumn() ?: 0;
} catch(PDOException $e) {
    $total_students = $total_teachers = $total_classes = $total_subjects = 0;
}

// Today's attendance
$today = date('Y-m-d');
try {
    $today_present = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='Present'")->fetchColumn() ?: 0;
    $today_absent = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='Absent'")->fetchColumn() ?: 0;
    $today_late = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='Late'")->fetchColumn() ?: 0;
} catch(PDOException $e) {
    $today_present = $today_absent = $today_late = 0;
}

// Attendance by class
try {
    $attendance_by_class = $pdo->query("
        SELECT c.class_name, 
        COUNT(CASE WHEN a.status='Present' THEN 1 END) as present,
        COUNT(CASE WHEN a.status='Absent' THEN 1 END) as absent,
        COUNT(CASE WHEN a.status='Late' THEN 1 END) as late
        FROM attendance a 
        JOIN classes c ON a.class_id = c.id 
        WHERE a.date='$today' 
        GROUP BY c.id
    ")->fetchAll();
} catch(PDOException $e) {
    $attendance_by_class = [];
}

// Fee collection report
try {
    $fee_report = $pdo->query("
        SELECT 
            SUM(CASE WHEN status='Paid' THEN amount END) as total_paid,
            SUM(CASE WHEN status='Pending' THEN balance END) as total_pending,
            SUM(CASE WHEN status='Partial' THEN balance END) as total_partial,
            COUNT(*) as total_records
        FROM fees
    ")->fetch();
} catch(PDOException $e) {
    $fee_report = ['total_paid' => 0, 'total_pending' => 0, 'total_partial' => 0, 'total_records' => 0];
}

// Student distribution by class
try {
    $students_by_class = $pdo->query("
        SELECT c.class_name, COUNT(s.id) as count 
        FROM classes c 
        LEFT JOIN students s ON c.id = s.class_id AND s.status='Active' 
        GROUP BY c.id
    ")->fetchAll();
} catch(PDOException $e) {
    $students_by_class = [];
}

// Recent results
try {
    $recent_results = $pdo->query("
        SELECT r.*, s.full_name as student_name, e.exam_name, e.max_marks 
        FROM results r 
        JOIN students s ON r.student_id = s.id 
        JOIN exams e ON r.exam_id = e.id 
        ORDER BY r.id DESC LIMIT 10
    ")->fetchAll();
} catch(PDOException $e) {
    $recent_results = [];
}

// Fee payment history
try {
    $payment_history = $pdo->query("
        SELECT f.*, s.full_name as student_name 
        FROM fees f 
        JOIN students s ON f.student_id = s.id 
        ORDER BY f.id DESC LIMIT 10
    ")->fetchAll();
} catch(PDOException $e) {
    $payment_history = [];
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>🏫 SchoolHub</h2>
        </div>
        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="students.php">👨‍🎓 Students</a>
            <a href="teachers.php">👨‍🏫 Teachers</a>
            <a href="classes.php">🏫 Classes</a>
            <a href="subjects.php">📚 Subjects</a>
            <a href="attendance.php">📋 Attendance</a>
            <a href="exams.php">📝 Exams</a>
            <a href="results.php">📊 Results</a>
            <a href="fees.php">💰 Fees</a>
            <a href="timetable.php">📅 Timetable</a>
            <a href="announcements.php">📢 Announcements</a>
            <a href="reports.php" class="active">📈 Reports</a>
            <a href="profile.php">👤 Profile</a>
            <a href="logout.php" class="logout">🚪 Logout</a>
        </nav>
        <div class="theme-toggle-sidebar">
            <span>🌓 Dark Mode</span>
            <label class="switch">
                <input type="checkbox" class="theme-toggle" id="themeToggleSidebar">
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>📈 Reports Dashboard</h1>
        </div>
        
        <?php if($msg): ?>
            <div class="message success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        
        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">👨‍🎓</div>
                <div class="stat-info">
                    <h3>Total Students</h3>
                    <p><?= $total_students ?></p>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-info">
                    <h3>Total Teachers</h3>
                    <p><?= $total_teachers ?></p>
                </div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon">🏫</div>
                <div class="stat-info">
                    <h3>Total Classes</h3>
                    <p><?= $total_classes ?></p>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3>Total Subjects</h3>
                    <p><?= $total_subjects ?></p>
                </div>
            </div>
        </div>
        
        <div class="reports-grid">
            <!-- Today's Attendance Report -->
            <div class="card">
                <h3>📋 Today's Attendance</h3>
                <div class="attendance-stats">
                    <div class="attendance-item present">
                        <span class="label">Present</span>
                        <span class="value"><?= $today_present ?></span>
                    </div>
                    <div class="attendance-item absent">
                        <span class="label">Absent</span>
                        <span class="value"><?= $today_absent ?></span>
                    </div>
                    <div class="attendance-item late">
                        <span class="label">Late</span>
                        <span class="value"><?= $today_late ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Fee Collection Report -->
            <div class="card">
                <h3>💰 Fee Collection</h3>
                <div class="fee-stats">
                    <div class="fee-item paid">
                        <span class="label">Total Paid</span>
                        <span class="value">$<?= number_format($fee_report['total_paid'] ?? 0, 2) ?></span>
                    </div>
                    <div class="fee-item pending">
                        <span class="label">Pending</span>
                        <span class="value">$<?= number_format($fee_report['total_pending'] ?? 0, 2) ?></span>
                    </div>
                    <div class="fee-item partial">
                        <span class="label">Partial</span>
                        <span class="value">$<?= number_format($fee_report['total_partial'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Students by Class -->
            <div class="card">
                <h3>👨‍🎓 Students by Class</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Class</th><th>Students</th></tr>
                        </thead>
                        <tbody>
                            <?php if(count($students_by_class) > 0): ?>
                                <?php foreach($students_by_class as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['class_name']) ?></td>
                                    <td><strong><?= $c['count'] ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center">No data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Attendance by Class -->
            <div class="card">
                <h3>📋 Attendance by Class</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Class</th><th>Present</th><th>Absent</th><th>Late</th></tr>
                        </thead>
                        <tbody>
                            <?php if(count($attendance_by_class) > 0): ?>
                                <?php foreach($attendance_by_class as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['class_name']) ?></td>
                                    <td><span class="status present"><?= $a['present'] ?></span></td>
                                    <td><span class="status absent"><?= $a['absent'] ?></span></td>
                                    <td><span class="status late"><?= $a['late'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No attendance data for today.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Results -->
            <div class="card" style="grid-column: span 2;">
                <h3>📊 Recent Exam Results</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Student</th><th>Exam</th><th>Marks</th><th>Grade</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if(count($recent_results) > 0): ?>
                                <?php foreach($recent_results as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['student_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($r['exam_name']) ?></td>
                                    <td><?= $r['marks_obtained'] ?> / <?= $r['max_marks'] ?></td>
                                    <td><strong style="color: <?= $r['grade'] == 'F' ? '#e53e3e' : '#2b6cb0' ?>"><?= $r['grade'] ?></strong></td>
                                    <td><span class="status <?= $r['remarks'] == 'Pass' ? 'paid' : 'pending' ?>"><?= $r['remarks'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No results available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Payments -->
            <div class="card" style="grid-column: span 2;">
                <h3>💰 Recent Fee Payments</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>Student</th><th>Fee Type</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if(count($payment_history) > 0): ?>
                                <?php foreach($payment_history as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['student_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['fee_type']) ?></td>
                                    <td>$<?= number_format($p['amount'], 2) ?></td>
                                    <td>$<?= number_format($p['amount_paid'], 2) ?></td>
                                    <td><strong style="color: <?= $p['balance'] > 0 ? '#e53e3e' : '#48bb78' ?>">$<?= number_format($p['balance'], 2) ?></strong></td>
                                    <td><span class="status <?= strtolower($p['status']) ?>"><?= $p['status'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No payment records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Export Section -->
        <div class="export-section">
            <h3>📤 Export Reports</h3>
            <div class="export-buttons">
                <a href="#" class="btn btn-add" onclick="alert('Export feature coming soon!')">📊 Export Students</a>
                <a href="#" class="btn btn-add" onclick="alert('Export feature coming soon!')">📊 Export Attendance</a>
                <a href="#" class="btn btn-add" onclick="alert('Export feature coming soon!')">📊 Export Fees</a>
                <a href="#" class="btn btn-add" onclick="alert('Export feature coming soon!')">📊 Export Results</a>
            </div>
        </div>
    </div>
</div>
<script src="theme-toggle.js"></script>

</body>
</html>