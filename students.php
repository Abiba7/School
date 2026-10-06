<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// =============================================
// ADD STUDENT - SAVES TO DATABASE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];
    $guardian_email = $_POST['guardian_email'];
    $class_id = $_POST['class_id'] ?: null;
    $admission_date = $_POST['admission_date'];
    $roll_number = $_POST['roll_number'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO students (student_id, full_name, date_of_birth, gender, address, phone, email, guardian_name, guardian_phone, guardian_email, class_id, admission_date, roll_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
        if ($stmt->execute([$student_id, $full_name, $date_of_birth, $gender, $address, $phone, $email, $guardian_name, $guardian_phone, $guardian_email, $class_id, $admission_date, $roll_number])) {
            logActivity($pdo, $_SESSION['user_id'], 'Add Student', "Added student: $full_name ($student_id)");
            header('Location: students.php?msg=Student added successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// =============================================
// DELETE STUDENT
// =============================================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        if ($stmt->execute([$id])) {
            logActivity($pdo, $_SESSION['user_id'], 'Delete Student', "Deleted student ID: $id");
            header('Location: students.php?msg=Student deleted successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error deleting student";
    }
}

// =============================================
// EDIT STUDENT - UPDATES DATABASE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $id = $_POST['id'];
    $student_id = $_POST['student_id'];
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];
    $guardian_email = $_POST['guardian_email'];
    $class_id = $_POST['class_id'] ?: null;
    $admission_date = $_POST['admission_date'];
    $roll_number = $_POST['roll_number'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE students SET student_id=?, full_name=?, date_of_birth=?, gender=?, address=?, phone=?, email=?, guardian_name=?, guardian_phone=?, guardian_email=?, class_id=?, admission_date=?, roll_number=?, status=? WHERE id=?");
        if ($stmt->execute([$student_id, $full_name, $date_of_birth, $gender, $address, $phone, $email, $guardian_name, $guardian_phone, $guardian_email, $class_id, $admission_date, $roll_number, $status, $id])) {
            logActivity($pdo, $_SESSION['user_id'], 'Edit Student', "Updated student: $full_name ($student_id)");
            header('Location: students.php?msg=Student updated successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// =============================================
// FETCH STUDENTS
// =============================================
$students = $pdo->query("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.id DESC")->fetchAll();
$classes = $pdo->query("SELECT id, class_name FROM classes WHERE status='Active'")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Students</title>
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
            <a href="students.php" class="active">👨‍🎓 Students</a>
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
            <h1>👨‍🎓 Student Management</h1>
            <button class="btn btn-add" onclick="document.getElementById('addForm').style.display='block'">➕ Add Student</button>
        </div>
        
        <?php if($msg): ?>
            <div class="message success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>
        
        <!-- Add Student Form -->
        <div id="addForm" style="display:none;">
            <form method="POST">
                <h3>➕ Add New Student</h3>
                <div class="form-grid">
                    <input type="text" name="student_id" placeholder="Student ID (e.g., STU001)" required>
                    <input type="text" name="full_name" placeholder="Full Name" required>
                    <input type="date" name="date_of_birth" placeholder="Date of Birth">
                    <select name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" name="address" placeholder="Address">
                    <input type="text" name="phone" placeholder="Phone">
                    <input type="email" name="email" placeholder="Email">
                    <input type="text" name="guardian_name" placeholder="Guardian Name">
                    <input type="text" name="guardian_phone" placeholder="Guardian Phone">
                    <input type="email" name="guardian_email" placeholder="Guardian Email">
                    <select name="class_id">
                        <option value="">Select Class</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="admission_date" required>
                    <input type="text" name="roll_number" placeholder="Roll Number">
                </div>
                <button type="submit" name="add_student" class="btn btn-add">💾 Save Student</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <!-- Student List -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Guardian</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($students) > 0): ?>
                        <?php foreach($students as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><strong><?= $s['student_id'] ?></strong></td>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td><?= htmlspecialchars($s['class_name'] ?? 'Not Assigned') ?></td>
                            <td><?= htmlspecialchars($s['guardian_name'] ?? 'N/A') ?></td>
                            <td><span class="status <?= strtolower($s['status']) ?>"><?= $s['status'] ?></span></td>
                            <td>
                                <button class="btn btn-edit" onclick="editStudent(
                                    <?= $s['id'] ?>,
                                    '<?= addslashes($s['student_id']) ?>',
                                    '<?= addslashes($s['full_name']) ?>',
                                    '<?= $s['date_of_birth'] ?>',
                                    '<?= $s['gender'] ?>',
                                    '<?= addslashes($s['address']) ?>',
                                    '<?= $s['phone'] ?>',
                                    '<?= addslashes($s['email']) ?>',
                                    '<?= addslashes($s['guardian_name']) ?>',
                                    '<?= $s['guardian_phone'] ?>',
                                    '<?= addslashes($s['guardian_email']) ?>',
                                    <?= $s['class_id'] ?: 'null' ?>,
                                    '<?= $s['admission_date'] ?>',
                                    '<?= $s['roll_number'] ?>',
                                    '<?= $s['status'] ?>'
                                )">Edit</button>
                                <a href="?delete=<?= $s['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this student?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No students found. Add your first student!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Form -->
        <div id="editForm" style="display:none;">
            <form method="POST">
                <h3>✏️ Edit Student</h3>
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <input type="text" name="student_id" id="edit_student_id" placeholder="Student ID" required>
                    <input type="text" name="full_name" id="edit_full_name" placeholder="Full Name" required>
                    <input type="date" name="date_of_birth" id="edit_dob">
                    <select name="gender" id="edit_gender">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" name="address" id="edit_address" placeholder="Address">
                    <input type="text" name="phone" id="edit_phone" placeholder="Phone">
                    <input type="email" name="email" id="edit_email" placeholder="Email">
                    <input type="text" name="guardian_name" id="edit_guardian_name" placeholder="Guardian Name">
                    <input type="text" name="guardian_phone" id="edit_guardian_phone" placeholder="Guardian Phone">
                    <input type="email" name="guardian_email" id="edit_guardian_email" placeholder="Guardian Email">
                    <select name="class_id" id="edit_class_id">
                        <option value="">Select Class</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="admission_date" id="edit_admission_date" required>
                    <input type="text" name="roll_number" id="edit_roll_number" placeholder="Roll Number">
                    <select name="status" id="edit_status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Graduated">Graduated</option>
                        <option value="Transferred">Transferred</option>
                    </select>
                </div>
                <button type="submit" name="edit_student" class="btn btn-edit">💾 Update Student</button>
                <button type="button" onclick="document.getElementById('editForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <script>
        function editStudent(id, student_id, name, dob, gender, address, phone, email, guardian, guardianPhone, guardianEmail, classId, admission, roll, status) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_student_id').value = student_id;
            document.getElementById('edit_full_name').value = name;
            document.getElementById('edit_dob').value = dob || '';
            document.getElementById('edit_gender').value = gender || '';
            document.getElementById('edit_address').value = address || '';
            document.getElementById('edit_phone').value = phone || '';
            document.getElementById('edit_email').value = email || '';
            document.getElementById('edit_guardian_name').value = guardian || '';
            document.getElementById('edit_guardian_phone').value = guardianPhone || '';
            document.getElementById('edit_guardian_email').value = guardianEmail || '';
            document.getElementById('edit_class_id').value = classId || '';
            document.getElementById('edit_admission_date').value = admission;
            document.getElementById('edit_roll_number').value = roll || '';
            document.getElementById('edit_status').value = status || 'Active';
            document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
        }
        </script>
    </div>
</div>
<script src="theme-toggle.js"></script>

</body>
</html>