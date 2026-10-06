<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

$total_students = getTotalStudents($pdo);
$total_teachers = getTotalTeachers($pdo);
$total_classes = getTotalClasses($pdo);
$total_subjects = getTotalSubjects($pdo);
$today_attendance = getTodayAttendance($pdo);
$total_fees = getTotalFeesCollected($pdo);

$announcements = $pdo->query("SELECT * FROM announcements WHERE status='Published' ORDER BY id DESC LIMIT 5")->fetchAll();
$recent_students = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abi-Jordan School - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>🏫 SchoolHub</h2>
        </div>
        <nav>
            <a href="dashboard.php" class="active">📊 Dashboard</a>
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
            <a href="reports.php">📈 Reports</a>
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
            <h1>Dashboard</h1>
            <div class="user-info">
                <span>👋 <?= htmlspecialchars($_SESSION['full_name']) ?></span>
                <span class="role-badge <?= $_SESSION['role'] ?>"><?= $_SESSION['role'] ?></span>
                <button class="theme-toggle-mobile theme-toggle-btn" title="Toggle Theme">☀️</button>
            </div>
        </div>
        
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
            <div class="stat-card gold">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3>Today's Attendance</h3>
                    <p><?= $today_attendance ?></p>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>Fees Collected</h3>
                    <p>$<?= number_format($total_fees, 2) ?></p>
                </div>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="students.php?action=add" class="action-btn">👨‍🎓 Add Student</a>
            <a href="teachers.php?action=add" class="action-btn">👨‍🏫 Add Teacher</a>
            <a href="classes.php?action=add" class="action-btn">🏫 Add Class</a>
            <a href="subjects.php?action=add" class="action-btn">📚 Add Subject</a>
            <a href="attendance.php" class="action-btn">📋 Take Attendance</a>
            <a href="fees.php" class="action-btn">💰 Record Payment</a>
            <a href="announcements.php" class="action-btn">📢 Post Announcement</a>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>📢 Recent Announcements</h3>
                <?php if(count($announcements) > 0): ?>
                    <?php foreach($announcements as $a): ?>
                    <div class="announcement-item">
                        <h4><?= htmlspecialchars($a['title']) ?></h4>
                        <p><?= htmlspecialchars(substr($a['content'], 0, 100)) ?>...</p>
                        <small class="text-muted"><?= date('M d, Y', strtotime($a['created_at'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No announcements yet.</p>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h3>👨‍🎓 Recent Students</h3>
                <table>
                    <thead>
                        <tr><th>Name</th><th>ID</th><th>Class</th></tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_students) > 0): ?>
                            <?php foreach($recent_students as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['full_name']) ?></td>
                                <td><?= $s['student_id'] ?></td>
                                <td><?= $s['class_id'] ? "Class ID: {$s['class_id']}" : 'Not Assigned' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">No students found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="theme-toggle.js"></script>
</body>
</html>