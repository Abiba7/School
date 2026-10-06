<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// =============================================
// ADD TEACHER - SAVES TO DATABASE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    $teacher_id = $_POST['teacher_id'];
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $qualification = $_POST['qualification'];
    $specialization = $_POST['specialization'];
    $hire_date = $_POST['hire_date'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO teachers (teacher_id, full_name, date_of_birth, gender, address, phone, email, qualification, specialization, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
        if ($stmt->execute([$teacher_id, $full_name, $date_of_birth, $gender, $address, $phone, $email, $qualification, $specialization, $hire_date])) {
            logActivity($pdo, $_SESSION['user_id'], 'Add Teacher', "Added teacher: $full_name ($teacher_id)");
            header('Location: teachers.php?msg=Teacher added successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// =============================================
// DELETE TEACHER
// =============================================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
        if ($stmt->execute([$id])) {
            logActivity($pdo, $_SESSION['user_id'], 'Delete Teacher', "Deleted teacher ID: $id");
            header('Location: teachers.php?msg=Teacher deleted successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error deleting teacher";
    }
}

// =============================================
// EDIT TEACHER - UPDATES DATABASE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_teacher'])) {
    $id = $_POST['id'];
    $teacher_id = $_POST['teacher_id'];
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $qualification = $_POST['qualification'];
    $specialization = $_POST['specialization'];
    $hire_date = $_POST['hire_date'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE teachers SET teacher_id=?, full_name=?, date_of_birth=?, gender=?, address=?, phone=?, email=?, qualification=?, specialization=?, hire_date=?, status=? WHERE id=?");
        if ($stmt->execute([$teacher_id, $full_name, $date_of_birth, $gender, $address, $phone, $email, $qualification, $specialization, $hire_date, $status, $id])) {
            logActivity($pdo, $_SESSION['user_id'], 'Edit Teacher', "Updated teacher: $full_name ($teacher_id)");
            header('Location: teachers.php?msg=Teacher updated successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// =============================================
// FETCH TEACHERS
// =============================================
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY id DESC")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Teachers</title>
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
            <a href="teachers.php" class="active">👨‍🏫 Teachers</a>
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
            <h1>👨‍🏫 Teacher Management</h1>
            <button class="btn btn-add" onclick="document.getElementById('addForm').style.display='block'">➕ Add Teacher</button>
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
                <h3>➕ Add New Teacher</h3>
                <div class="form-grid">
                    <input type="text" name="teacher_id" placeholder="Teacher ID (e.g., TCH001)" required>
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
                    <input type="text" name="qualification" placeholder="Qualification">
                    <input type="text" name="specialization" placeholder="Specialization">
                    <input type="date" name="hire_date" required>
                </div>
                <button type="submit" name="add_teacher" class="btn btn-add">💾 Save Teacher</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <!-- Teacher List -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teacher ID</th>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($teachers) > 0): ?>
                        <?php foreach($teachers as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><strong><?= $t['teacher_id'] ?></strong></td>
                            <td><?= htmlspecialchars($t['full_name']) ?></td>
                            <td><?= htmlspecialchars($t['specialization'] ?? 'N/A') ?></td>
                            <td><?= $t['phone'] ?></td>
                            <td><span class="status <?= strtolower($t['status']) ?>"><?= $t['status'] ?></span></td>
                            <td>
                                <button class="btn btn-edit" onclick="editTeacher(
                                    <?= $t['id'] ?>,
                                    '<?= addslashes($t['teacher_id']) ?>',
                                    '<?= addslashes($t['full_name']) ?>',
                                    '<?= $t['date_of_birth'] ?>',
                                    '<?= $t['gender'] ?>',
                                    '<?= addslashes($t['address']) ?>',
                                    '<?= $t['phone'] ?>',
                                    '<?= addslashes($t['email']) ?>',
                                    '<?= addslashes($t['qualification']) ?>',
                                    '<?= addslashes($t['specialization']) ?>',
                                    '<?= $t['hire_date'] ?>',
                                    '<?= $t['status'] ?>'
                                )">Edit</button>
                                <a href="?delete=<?= $t['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this teacher?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No teachers found. Add your first teacher!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Form -->
        <div id="editForm" style="display:none;">
            <form method="POST">
                <h3>✏️ Edit Teacher</h3>
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <input type="text" name="teacher_id" id="edit_teacher_id" placeholder="Teacher ID" required>
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
                    <input type="text" name="qualification" id="edit_qualification" placeholder="Qualification">
                    <input type="text" name="specialization" id="edit_specialization" placeholder="Specialization">
                    <input type="date" name="hire_date" id="edit_hire_date" required>
                    <select name="status" id="edit_status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>
                <button type="submit" name="edit_teacher" class="btn btn-edit">💾 Update Teacher</button>
                <button type="button" onclick="document.getElementById('editForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <script>
        function editTeacher(id, teacher_id, name, dob, gender, address, phone, email, qualification, specialization, hire_date, status) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_teacher_id').value = teacher_id;
            document.getElementById('edit_full_name').value = name;
            document.getElementById('edit_dob').value = dob || '';
            document.getElementById('edit_gender').value = gender || '';
            document.getElementById('edit_address').value = address || '';
            document.getElementById('edit_phone').value = phone || '';
            document.getElementById('edit_email').value = email || '';
            document.getElementById('edit_qualification').value = qualification || '';
            document.getElementById('edit_specialization').value = specialization || '';
            document.getElementById('edit_hire_date').value = hire_date;
            document.getElementById('edit_status').value = status || 'Active';
            document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
        }
        </script>
    </div>
</div>
<script src="theme-toggle.js"></script>

</body>
</html>