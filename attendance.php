<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// Handle Add Attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_attendance'])) {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $student_ids = $_POST['student_ids'] ?? [];
    $statuses = $_POST['statuses'] ?? [];
    $remarks = $_POST['remarks'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        // Delete existing attendance for this class and date
        $stmt = $pdo->prepare("DELETE FROM attendance WHERE class_id = ? AND date = ?");
        $stmt->execute([$class_id, $date]);
        
        // Insert new attendance records
        foreach($student_ids as $index => $student_id) {
            $status = $statuses[$index] ?? 'Present';
            $remark = $remarks[$index] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO attendance (student_id, class_id, date, status, remarks, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $class_id, $date, $status, $remark, $_SESSION['user_id']]);
        }
        
        $pdo->commit();
        header('Location: attendance.php?msg=Attendance recorded successfully');
        exit();
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch classes and students
$classes = $pdo->query("SELECT id, class_name FROM classes WHERE status='Active'")->fetchAll();

// Default to first class
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : ($classes[0]['id'] ?? null);
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$students = [];
$attendance_records = [];
if ($selected_class) {
    $students = $pdo->query("SELECT id, full_name FROM students WHERE class_id = $selected_class AND status='Active' ORDER BY full_name")->fetchAll();
    
    // Get existing attendance for this class and date
    $attendance_records = $pdo->query("SELECT * FROM attendance WHERE class_id = $selected_class AND date = '$selected_date'")->fetchAll();
}

// Fetch recent attendance records
$recent_attendance = $pdo->query("SELECT a.*, s.full_name as student_name, c.class_name FROM attendance a JOIN students s ON a.student_id = s.id JOIN classes c ON a.class_id = c.id ORDER BY a.id DESC LIMIT 20")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Attendance</title>
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
            <a href="attendance.php" class="active">📋 Attendance</a>
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
            <h1>Attendance Management</h1>
        </div>
        
        <?php if($msg): ?>
            <div class="message success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>
        
        <!-- Attendance Form -->
        <div class="card">
            <h3>📋 Take Attendance</h3>
            <form method="GET" style="box-shadow: none; padding: 0; margin-bottom: 15px;">
                <div class="form-grid" style="grid-template-columns: 1fr 1fr auto;">
                    <select name="class_id" required>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $selected_class == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="date" value="<?= $selected_date ?>" required>
                    <button type="submit" class="btn btn-add">Load Class</button>
                </div>
            </form>
            
            <?php if($selected_class && count($students) > 0): ?>
                <form method="POST">
                    <input type="hidden" name="class_id" value="<?= $selected_class ?>">
                    <input type="hidden" name="date" value="<?= $selected_date ?>">
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $index => $s): ?>
                                    <?php 
                                    $existing = array_filter($attendance_records, function($a) use ($s) {
                                        return $a['student_id'] == $s['id'];
                                    });
                                    $existing = array_values($existing);
                                    $status = count($existing) > 0 ? $existing[0]['status'] : 'Present';
                                    $remark = count($existing) > 0 ? $existing[0]['remarks'] : '';
                                    ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                                        <td>
                                            <input type="hidden" name="student_ids[]" value="<?= $s['id'] ?>">
                                            <select name="statuses[]" required>
                                                <option value="Present" <?= $status == 'Present' ? 'selected' : '' ?>>Present</option>
                                                <option value="Absent" <?= $status == 'Absent' ? 'selected' : '' ?>>Absent</option>
                                                <option value="Late" <?= $status == 'Late' ? 'selected' : '' ?>>Late</option>
                                                <option value="Excused" <?= $status == 'Excused' ? 'selected' : '' ?>>Excused</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="remarks[]" value="<?= htmlspecialchars($remark) ?>" placeholder="Remarks" style="width: 100%;"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" name="add_attendance" class="btn btn-add">Save Attendance</button>
                </form>
            <?php elseif($selected_class): ?>
                <p class="text-muted">No students found in this class.</p>
            <?php endif; ?>
        </div>
        
        <!-- Recent Attendance Records -->
        <div class="card">
            <h3>📋 Recent Attendance Records</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_attendance) > 0): ?>
                            <?php foreach($recent_attendance as $a): ?>
                            <tr>
                                <td><?= $a['date'] ?></td>
                                <td><strong><?= htmlspecialchars($a['student_name']) ?></strong></td>
                                <td><?= htmlspecialchars($a['class_name']) ?></td>
                                <td><span class="status <?= strtolower($a['status']) ?>"><?= $a['status'] ?></span></td>
                                <td><?= htmlspecialchars($a['remarks'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No attendance records found.</td></tr>
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