<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// Handle Add Exam
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_exam'])) {
    $exam_name = $_POST['exam_name'];
    $exam_code = $_POST['exam_code'];
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $term = $_POST['term'];
    $academic_year = $_POST['academic_year'];
    $max_marks = $_POST['max_marks'];
    $passing_marks = $_POST['passing_marks'];
    $exam_date = $_POST['exam_date'];
    $description = $_POST['description'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO exams (exam_name, exam_code, class_id, subject_id, term, academic_year, max_marks, passing_marks, exam_date, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$exam_name, $exam_code, $class_id, $subject_id, $term, $academic_year, $max_marks, $passing_marks, $exam_date, $description])) {
            header('Location: exams.php?msg=Exam added successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Delete Exam
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
        if ($stmt->execute([$id])) {
            header('Location: exams.php?msg=Exam deleted successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error deleting exam";
    }
}

// Fetch exams
$exams = $pdo->query("SELECT e.*, c.class_name, s.subject_name FROM exams e JOIN classes c ON e.class_id = c.id JOIN subjects s ON e.subject_id = s.id ORDER BY e.id DESC")->fetchAll();
$classes = $pdo->query("SELECT id, class_name FROM classes WHERE status='Active'")->fetchAll();
$subjects = $pdo->query("SELECT id, subject_name FROM subjects WHERE status='Active'")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Exams</title>
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
            <a href="exams.php" class="active">📝 Exams</a>
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
            <h1>Exam Management</h1>
            <button class="btn btn-add" onclick="document.getElementById('addForm').style.display='block'">📝 Add Exam</button>
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
                <h3>📝 Add New Exam</h3>
                <div class="form-grid">
                    <input type="text" name="exam_name" placeholder="Exam Name" required>
                    <input type="text" name="exam_code" placeholder="Exam Code" required>
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
                    <select name="term" required>
                        <option value="">Select Term</option>
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2">Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                    <input type="text" name="academic_year" placeholder="Academic Year" required>
                    <input type="number" name="max_marks" placeholder="Max Marks" value="100" required>
                    <input type="number" name="passing_marks" placeholder="Passing Marks" value="40" required>
                    <input type="date" name="exam_date">
                    <input type="text" name="description" placeholder="Description" style="grid-column: span 2;">
                </div>
                <button type="submit" name="add_exam" class="btn btn-add">Add Exam</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <!-- Exam List -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Exam Name</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Term</th>
                        <th>Max Marks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($exams) > 0): ?>
                        <?php foreach($exams as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><strong><?= htmlspecialchars($e['exam_name']) ?></strong></td>
                            <td><?= htmlspecialchars($e['class_name']) ?></td>
                            <td><?= htmlspecialchars($e['subject_name']) ?></td>
                            <td><?= $e['term'] ?></td>
                            <td><?= $e['max_marks'] ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="editExam(
                                    <?= $e['id'] ?>,
                                    '<?= addslashes($e['exam_name']) ?>',
                                    '<?= addslashes($e['exam_code']) ?>',
                                    <?= $e['class_id'] ?>,
                                    <?= $e['subject_id'] ?>,
                                    '<?= $e['term'] ?>',
                                    '<?= $e['academic_year'] ?>',
                                    <?= $e['max_marks'] ?>,
                                    <?= $e['passing_marks'] ?>,
                                    '<?= $e['exam_date'] ?>',
                                    '<?= addslashes($e['description']) ?>'
                                )">Edit</button>
                                <a href="?delete=<?= $e['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this exam?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No exams found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Form -->
        <div id="editForm" style="display:none;">
            <form method="POST">
                <h3>✏️ Edit Exam</h3>
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <input type="text" name="exam_name" id="edit_exam_name" placeholder="Exam Name" required>
                    <input type="text" name="exam_code" id="edit_exam_code" placeholder="Exam Code" required>
                    <select name="class_id" id="edit_class_id" required>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="subject_id" id="edit_subject_id" required>
                        <?php foreach($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="term" id="edit_term" required>
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2">Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                    <input type="text" name="academic_year" id="edit_academic_year" placeholder="Academic Year" required>
                    <input type="number" name="max_marks" id="edit_max_marks" placeholder="Max Marks" required>
                    <input type="number" name="passing_marks" id="edit_passing_marks" placeholder="Passing Marks" required>
                    <input type="date" name="exam_date" id="edit_exam_date">
                    <input type="text" name="description" id="edit_description" placeholder="Description" style="grid-column: span 2;">
                </div>
                <button type="submit" name="edit_exam" class="btn btn-edit">Update Exam</button>
                <button type="button" onclick="document.getElementById('editForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <script>
        function editExam(id, name, code, classId, subjectId, term, year, maxMarks, passingMarks, date, description) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_exam_name').value = name;
            document.getElementById('edit_exam_code').value = code;
            document.getElementById('edit_class_id').value = classId;
            document.getElementById('edit_subject_id').value = subjectId;
            document.getElementById('edit_term').value = term;
            document.getElementById('edit_academic_year').value = year;
            document.getElementById('edit_max_marks').value = maxMarks;
            document.getElementById('edit_passing_marks').value = passingMarks;
            document.getElementById('edit_exam_date').value = date || '';
            document.getElementById('edit_description').value = description || '';
            document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
        }
        </script>
    </div>
</div>
<script src="theme-toggle.js"></script>

</body>
</html>