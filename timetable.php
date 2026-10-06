<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// Handle Add Timetable Entry
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_timetable'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO timetable (class_id, subject_id, teacher_id, day_of_week, start_time, end_time, room) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$class_id, $subject_id, $teacher_id, $day_of_week, $start_time, $end_time, $room])) {
            header('Location: timetable.php?msg=Timetable entry added successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Delete Timetable Entry
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM timetable WHERE id = ?");
        if ($stmt->execute([$id])) {
            header('Location: timetable.php?msg=Timetable entry deleted successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error deleting timetable entry";
    }
}

// Fetch timetable entries with details
$timetable = $pdo->query("SELECT t.*, c.class_name, s.subject_name, u.full_name as teacher_name FROM timetable t JOIN classes c ON t.class_id = c.id JOIN subjects s ON t.subject_id = s.id JOIN users u ON t.teacher_id = u.id ORDER BY FIELD(t.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.start_time")->fetchAll();

$classes = $pdo->query("SELECT id, class_name FROM classes WHERE status='Active'")->fetchAll();
$subjects = $pdo->query("SELECT id, subject_name FROM subjects WHERE status='Active'")->fetchAll();
$teachers = $pdo->query("SELECT id, full_name FROM users WHERE role='teacher' AND status='active'")->fetchAll();

// Group by day of week for display
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Timetable</title>
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
            <a href="timetable.php" class="active">📅 Timetable</a>
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
            <h1>Timetable Management</h1>
            <button class="btn btn-add" onclick="document.getElementById('addForm').style.display='block'">📅 Add Entry</button>
        </div>
        
        <?php if($msg): ?>
            <div class="message success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>
        
        <!-- Add Form -->
        <div id="addForm" style="display:none;">
            <form method="POST">
                <h3>📅 Add Timetable Entry</h3>
                <div class="form-grid">
                    <select name="class_id" required>
                        <option value="">Select Class</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="teacher_id" required>
                        <option value="">Select Teacher</option>
                        <?php foreach($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="day_of_week" required>
                        <option value="">Select Day</option>
                        <?php foreach($days as $day): ?>
                            <option value="<?= $day ?>"><?= $day ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="time" name="start_time" required>
                    <input type="time" name="end_time" required>
                    <input type="text" name="room" placeholder="Room Number">
                </div>
                <button type="submit" name="add_timetable" class="btn btn-add">Add Entry</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <!-- Timetable Display -->
        <div class="card">
            <h3>📅 Class Timetable</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($timetable) > 0): ?>
                            <?php foreach($timetable as $t): ?>
                            <tr>
                                <td><strong><?= $t['day_of_week'] ?></strong></td>
                                <td><?= date('h:i A', strtotime($t['start_time'])) ?> - <?= date('h:i A', strtotime($t['end_time'])) ?></td>
                                <td><?= htmlspecialchars($t['class_name']) ?></td>
                                <td><?= htmlspecialchars($t['subject_name']) ?></td>
                                <td><?= htmlspecialchars($t['teacher_name']) ?></td>
                                <td><?= $t['room'] ?? '--' ?></td>
                                <td>
                                    <a href="?delete=<?= $t['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this entry?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No timetable entries found.</td></tr>
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