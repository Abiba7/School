<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// Handle Add/Update Result
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_result'])) {
    $exam_id = $_POST['exam_id'];
    $student_id = $_POST['student_id'];
    $marks_obtained = $_POST['marks_obtained'];
    
    // Get exam details for grade calculation
    $exam = $pdo->prepare("SELECT max_marks, passing_marks FROM exams WHERE id = ?");
    $exam->execute([$exam_id]);
    $exam_data = $exam->fetch();
    
    // Calculate grade
    $percentage = ($marks_obtained / $exam_data['max_marks']) * 100;
    if ($percentage >= 80) $grade = 'A';
    elseif ($percentage >= 70) $grade = 'B';
    elseif ($percentage >= 60) $grade = 'C';
    elseif ($percentage >= 50) $grade = 'D';
    elseif ($percentage >= 40) $grade = 'E';
    else $grade = 'F';
    
    $remarks = $percentage >= $exam_data['passing_marks'] ? 'Pass' : 'Fail';
    
    try {
        // Check if result exists
        $check = $pdo->prepare("SELECT id FROM results WHERE exam_id = ? AND student_id = ?");
        $check->execute([$exam_id, $student_id]);
        if ($check->fetch()) {
            // Update
            $stmt = $pdo->prepare("UPDATE results SET marks_obtained = ?, grade = ?, remarks = ?, updated_at = NOW() WHERE exam_id = ? AND student_id = ?");
            $stmt->execute([$marks_obtained, $grade, $remarks, $exam_id, $student_id]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO results (exam_id, student_id, marks_obtained, grade, remarks, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$exam_id, $student_id, $marks_obtained, $grade, $remarks, $_SESSION['user_id']]);
        }
        
        header('Location: results.php?msg=Result recorded successfully');
        exit();
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch exams
$exams = $pdo->query("SELECT e.*, c.class_name, s.subject_name FROM exams e JOIN classes c ON e.class_id = c.id JOIN subjects s ON e.subject_id = s.id ORDER BY e.id DESC")->fetchAll();

// Fetch results with student names
$results = $pdo->query("SELECT r.*, s.full_name as student_name, e.exam_name, e.max_marks FROM results r JOIN students s ON r.student_id = s.id JOIN exams e ON r.exam_id = e.id ORDER BY r.id DESC")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Results</title>
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
            <a href="results.php" class="active">📊 Results</a>
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
            <h1>Exam Results Management</h1>
        </div>
        
        <?php if($msg): ?>
            <div class="message success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>
        
        <!-- Add Result Form -->
        <div class="card">
            <h3>📊 Enter Exam Results</h3>
            <form method="POST">
                <div class="form-grid">
                    <select name="exam_id" required>
                        <option value="">Select Exam</option>
                        <?php foreach($exams as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['exam_name']) ?> - <?= htmlspecialchars($e['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="student_id" required>
                        <option value="">Select Student</option>
                        <?php 
                        $students = $pdo->query("SELECT id, full_name FROM students WHERE status='Active' ORDER BY full_name")->fetchAll();
                        foreach($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" step="0.01" name="marks_obtained" placeholder="Marks Obtained" required>
                </div>
                <button type="submit" name="add_result" class="btn btn-add">Record Result</button>
            </form>
        </div>

        <!-- Results List -->
        <div class="card">
            <h3>📊 Results History</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($results) > 0): ?>
                            <?php foreach($results as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['student_name']) ?></strong></td>
                                <td><?= htmlspecialchars($r['exam_name']) ?></td>
                                <td><?= $r['marks_obtained'] ?> / <?= $r['max_marks'] ?></td>
                                <td><strong style="color: <?= $r['grade'] == 'F' ? '#e53e3e' : '#2b6cb0' ?>"><?= $r['grade'] ?></strong></td>
                                <td><span class="status <?= $r['remarks'] == 'Pass' ? 'paid' : 'pending' ?>"><?= $r['remarks'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No results found.</td></tr>
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