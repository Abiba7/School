<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch additional user data based on role
$student_data = null;
$teacher_data = null;
$role_data = null;

if ($_SESSION['role'] == 'student') {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $student_data = $stmt->fetch();
    $role_data = $student_data;
} elseif ($_SESSION['role'] == 'teacher') {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $teacher_data = $stmt->fetch();
    $role_data = $teacher_data;
}

// Handle Update Profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$full_name, $email, $phone, $user_id])) {
            $_SESSION['full_name'] = $full_name;
            $message = "Profile updated successfully!";
            $message_type = 'success';
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if ($current != $user['password']) {
        $message = "Current password is incorrect!";
        $message_type = 'error';
    } elseif ($new != $confirm) {
        $message = "New passwords do not match!";
        $message_type = 'error';
    } elseif (strlen($new) < 4) {
        $message = "Password must be at least 4 characters!";
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$new, $user_id])) {
                $message = "Password changed successfully!";
                $message_type = 'success';
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Profile</title>
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
            <a href="reports.php">📈 Reports</a>
            <a href="profile.php" class="active">👤 Profile</a>
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
            <h1>👤 Profile Management</h1>
        </div>
        
        <?php if($message): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <!-- Profile Information -->
            <div class="card profile-card">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <div class="profile-role">
                        <span class="role-badge <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                    </div>
                </div>
                
                <h3>👤 Profile Information</h3>
                <form method="POST">
                    <div class="form-grid">
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" placeholder="Full Name" required>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email" required>
                        <input type="text" name="phone" value="<?= $user['phone'] ?? '' ?>" placeholder="Phone">
                        <input type="text" value="<?= $user['username'] ?>" disabled style="background: var(--bg-hover); color: var(--text-muted);">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-edit">Update Profile</button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="card">
                <h3>🔑 Change Password</h3>
                <form method="POST">
                    <div class="form-grid" style="grid-template-columns: 1fr;">
                        <input type="password" name="current_password" placeholder="Current Password" required>
                        <input type="password" name="new_password" placeholder="New Password (min 4 characters)" required>
                        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-add">Change Password</button>
                </form>
                
                <div class="password-hint" style="margin-top: 10px; padding: 10px; background: var(--bg-hover); border-radius: 8px;">
                    <small class="text-muted">🔒 Password must be at least 4 characters long.</small>
                </div>
            </div>
        </div>
        
        <!-- Account Information -->
        <div class="card">
            <h3>📋 Account Information</h3>
            <div class="info-grid">
                <div>
                    <strong>Username:</strong>
                    <span><?= $user['username'] ?></span>
                </div>
                <div>
                    <strong>Role:</strong>
                    <span class="role-badge <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                </div>
                <div>
                    <strong>Joined:</strong>
                    <span><?= date('F d, Y', strtotime($user['created_at'])) ?></span>
                </div>
                <div>
                    <strong>Last Login:</strong>
                    <span><?= $user['last_login'] ? date('F d, Y H:i', strtotime($user['last_login'])) : 'Never' ?></span>
                </div>
                <div>
                    <strong>Status:</strong>
                    <span class="status <?= strtolower($user['status']) ?>"><?= ucfirst($user['status']) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Role Specific Information -->
        <?php if ($_SESSION['role'] == 'student' && $student_data): ?>
        <div class="card">
            <h3>🎓 Student Information</h3>
            <div class="info-grid">
                <div>
                    <strong>Student ID:</strong>
                    <span><?= $student_data['student_id'] ?? 'N/A' ?></span>
                </div>
                <div>
                    <strong>Class:</strong>
                    <?php 
                    if ($student_data['class_id']) {
                        $class_stmt = $pdo->prepare("SELECT class_name FROM classes WHERE id = ?");
                        $class_stmt->execute([$student_data['class_id']]);
                        $class = $class_stmt->fetch();
                        echo '<span>' . htmlspecialchars($class['class_name'] ?? 'N/A') . '</span>';
                    } else {
                        echo '<span>Not Assigned</span>';
                    }
                    ?>
                </div>
                <div>
                    <strong>Roll Number:</strong>
                    <span><?= $student_data['roll_number'] ?? 'N/A' ?></span>
                </div>
                <div>
                    <strong>Admission Date:</strong>
                    <span><?= $student_data['admission_date'] ? date('F d, Y', strtotime($student_data['admission_date'])) : 'N/A' ?></span>
                </div>
                <div>
                    <strong>Guardian:</strong>
                    <span><?= htmlspecialchars($student_data['guardian_name'] ?? 'N/A') ?></span>
                </div>
                <div>
                    <strong>Guardian Phone:</strong>
                    <span><?= $student_data['guardian_phone'] ?? 'N/A' ?></span>
                </div>
                <div style="grid-column: span 2;">
                    <strong>Address:</strong>
                    <span><?= htmlspecialchars($student_data['address'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($_SESSION['role'] == 'teacher' && $teacher_data): ?>
        <div class="card">
            <h3>👨‍🏫 Teacher Information</h3>
            <div class="info-grid">
                <div>
                    <strong>Teacher ID:</strong>
                    <span><?= $teacher_data['teacher_id'] ?? 'N/A' ?></span>
                </div>
                <div>
                    <strong>Qualification:</strong>
                    <span><?= htmlspecialchars($teacher_data['qualification'] ?? 'N/A') ?></span>
                </div>
                <div>
                    <strong>Specialization:</strong>
                    <span><?= htmlspecialchars($teacher_data['specialization'] ?? 'N/A') ?></span>
                </div>
                <div>
                    <strong>Hire Date:</strong>
                    <span><?= $teacher_data['hire_date'] ? date('F d, Y', strtotime($teacher_data['hire_date'])) : 'N/A' ?></span>
                </div>
                <div style="grid-column: span 2;">
                    <strong>Address:</strong>
                    <span><?= htmlspecialchars($teacher_data['address'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Activity Log (Recent Actions) -->
        <div class="card">
            <h3>📋 Recent Activity</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $logs = $pdo->prepare("SELECT * FROM activity_logs WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC LIMIT 5");
                            $logs->execute([$user_id]);
                            $activity_logs = $logs->fetchAll();
                        } catch(PDOException $e) {
                            $activity_logs = [];
                        }
                        ?>
                        <?php if(count($activity_logs) > 0): ?>
                            <?php foreach($activity_logs as $log): ?>
                            <tr>
                                <td><span class="status info"><?= htmlspecialchars($log['action']) ?></span></td>
                                <td><?= htmlspecialchars($log['description'] ?? '') ?></td>
                                <td><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">No recent activity found.</td></tr>
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