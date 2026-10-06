<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// =============================================
// ADD CLASS - SAVES TO DATABASE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $class_name = $_POST['class_name'];
    $class_code = $_POST['class_code'];
    $academic_year = $_POST['academic_year'];
    $capacity = $_POST['capacity'];
    $class_teacher_id = $_POST['class_teacher_id'] ?: null;
    $description = $_POST['description'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO classes (class_name, class_code, academic_year, capacity, class_teacher_id, description, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        if ($stmt->execute([$class_name, $class_code, $academic_year, $capacity, $class_teacher_id, $description])) {
            logActivity($pdo, $_SESSION['user_id'], 'Add Class', "Added class: $class_name ($class_code)");
            header('Location: classes.php?msg=Class added successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// =============================================
// DELETE CLASS
// =============================================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE class_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            $error = "Cannot delete class with students assigned!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
            if ($stmt->execute([$id])) {
                logActivity($pdo, $_SESSION['user_id'], 'Delete Class', "Deleted class ID: $id");
                header('Location: classes.php?msg=Class deleted successfully');
                exit();
            }
        }
    } catch(PDOException $e) {
        $error = "Error deleting class";
    }
}

// =============================================
// EDIT CLASS - UPDATES DATABASE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_class'])) {
    $id = $_POST['id'];
    $class_name = $_POST['class_name'];
    $class_code = $_POST['class_code'];
    $academic_year = $_POST['academic_year'];
    $capacity = $_POST['capacity'];
    $class_teacher_id = $_POST['class_teacher_id'] ?: null;
    $description = $_POST['description'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE classes SET class_name=?, class_code=?, academic_year=?, capacity=?, class_teacher_id=?, description=?, status=? WHERE id=?");
        if ($stmt->execute([$class_name, $class_code, $academic_year, $capacity, $class_teacher_id, $description, $status, $id])) {
            logActivity($pdo, $_SESSION['user_id'], 'Edit Class', "Updated class: $class_name ($class_code)");
            header('Location: classes.php?msg=Class updated successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// =============================================
// FETCH CLASSES WITH TEACHER NAMES
// =============================================
// Now joining with teachers table instead of users table
$classes = $pdo->query("
    SELECT c.*, t.full_name as teacher_name 
    FROM classes c 
    LEFT JOIN teachers t ON c.class_teacher_id = t.id 
    ORDER BY c.id DESC
")->fetchAll();

// =============================================
// FETCH TEACHERS - FIXED: Using teachers table
// =============================================
$teachers = $pdo->query("SELECT id, full_name FROM teachers WHERE status='Active' ORDER BY full_name")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School - Classes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>🏫 Abi-Jordan School</h2>
        </div>
        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="students.php">👨‍🎓 Students</a>
            <a href="teachers.php">👨‍🏫 Teachers</a>
            <a href="classes.php" class="active">🏫 Classes</a>
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
            <h1>🏫 Class Management</h1>
            <button class="btn btn-add" onclick="document.getElementById('addForm').style.display='block'">➕ Add Class</button>
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
                <h3>➕ Add New Class</h3>
                <div class="form-grid">
                    <input type="text" name="class_name" placeholder="Class Name (e.g., Grade 1A)" required>
                    <input type="text" name="class_code" placeholder="Class Code (e.g., G1A)" required>
                    <input type="text" name="academic_year" placeholder="Academic Year (e.g., 2026)" required>
                    <input type="number" name="capacity" placeholder="Capacity" required>
                    <select name="class_teacher_id">
                        <option value="">Select Class Teacher</option>
                        <?php foreach($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="description" placeholder="Description" style="grid-column: span 2;">
                </div>
                <button type="submit" name="add_class" class="btn btn-add">💾 Save Class</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
            
            <?php if(count($teachers) == 0): ?>
                <div class="message info" style="margin-top: 10px;">
                    ⚠️ No teachers found. Please <a href="teachers.php">add teachers</a> first before assigning them to classes.
                </div>
            <?php endif; ?>
        </div>

        <!-- Class List -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Class Name</th>
                        <th>Code</th>
                        <th>Teacher</th>
                        <th>Capacity</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($classes) > 0): ?>
                        <?php foreach($classes as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><strong><?= htmlspecialchars($c['class_name']) ?></strong></td>
                            <td><?= $c['class_code'] ?></td>
                            <td><?= htmlspecialchars($c['teacher_name'] ?? 'Not Assigned') ?></td>
                            <td><?= $c['capacity'] ?></td>
                            <td><?= $c['academic_year'] ?></td>
                            <td><span class="status <?= strtolower($c['status']) ?>"><?= $c['status'] ?></span></td>
                            <td>
                                <button class="btn btn-edit" onclick="editClass(
                                    <?= $c['id'] ?>,
                                    '<?= addslashes($c['class_name']) ?>',
                                    '<?= addslashes($c['class_code']) ?>',
                                    '<?= $c['academic_year'] ?>',
                                    <?= $c['capacity'] ?>,
                                    <?= $c['class_teacher_id'] ?: 'null' ?>,
                                    '<?= addslashes($c['description']) ?>',
                                    '<?= $c['status'] ?>'
                                )">Edit</button>
                                <a href="?delete=<?= $c['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this class?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No classes found. Add your first class!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Form -->
        <div id="editForm" style="display:none;">
            <form method="POST">
                <h3>✏️ Edit Class</h3>
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <input type="text" name="class_name" id="edit_class_name" placeholder="Class Name" required>
                    <input type="text" name="class_code" id="edit_class_code" placeholder="Class Code" required>
                    <input type="text" name="academic_year" id="edit_academic_year" placeholder="Academic Year" required>
                    <input type="number" name="capacity" id="edit_capacity" placeholder="Capacity" required>
                    <select name="class_teacher_id" id="edit_class_teacher">
                        <option value="">Select Class Teacher</option>
                        <?php foreach($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" id="edit_status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <input type="text" name="description" id="edit_description" placeholder="Description" style="grid-column: span 2;">
                </div>
                <button type="submit" name="edit_class" class="btn btn-edit">💾 Update Class</button>
                <button type="button" onclick="document.getElementById('editForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <script>
        function editClass(id, name, code, year, capacity, teacher, description, status) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_class_name').value = name;
            document.getElementById('edit_class_code').value = code;
            document.getElementById('edit_academic_year').value = year;
            document.getElementById('edit_capacity').value = capacity;
            document.getElementById('edit_class_teacher').value = teacher || '';
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit_status').value = status || 'Active';
            document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
        }
        </script>
    </div>
</div>
<script src="theme-toggle.js"></script>

</body>
</html>