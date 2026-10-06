<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// =============================================
// ADD SUBJECT - SAVES TO DATABASE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'];
    $subject_code = $_POST['subject_code'];
    $description = $_POST['description'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, subject_code, description, status) VALUES (?, ?, ?, 'Active')");
        if ($stmt->execute([$subject_name, $subject_code, $description])) {
            logActivity($pdo, $_SESSION['user_id'], 'Add Subject', "Added subject: $subject_name ($subject_code)");
            header('Location: subjects.php?msg=Subject added successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// =============================================
// DELETE SUBJECT
// =============================================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM class_subjects WHERE subject_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            $error = "Cannot delete subject assigned to classes!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            if ($stmt->execute([$id])) {
                logActivity($pdo, $_SESSION['user_id'], 'Delete Subject', "Deleted subject ID: $id");
                header('Location: subjects.php?msg=Subject deleted successfully');
                exit();
            }
        }
    } catch(PDOException $e) {
        $error = "Error deleting subject";
    }
}

// =============================================
// EDIT SUBJECT - UPDATES DATABASE
// =============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_subject'])) {
    $id = $_POST['id'];
    $subject_name = $_POST['subject_name'];
    $subject_code = $_POST['subject_code'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE subjects SET subject_name=?, subject_code=?, description=?, status=? WHERE id=?");
        if ($stmt->execute([$subject_name, $subject_code, $description, $status, $id])) {
            logActivity($pdo, $_SESSION['user_id'], 'Edit Subject', "Updated subject: $subject_name ($subject_code)");
            header('Location: subjects.php?msg=Subject updated successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// =============================================
// FETCH SUBJECTS
// =============================================
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Subjects</title>
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
            <a href="subjects.php" class="active">📚 Subjects</a>
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
            <h1>📚 Subject Management</h1>
            <button class="btn btn-add" onclick="document.getElementById('addForm').style.display='block'">➕ Add Subject</button>
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
                <h3>➕ Add New Subject</h3>
                <div class="form-grid">
                    <input type="text" name="subject_name" placeholder="Subject Name" required>
                    <input type="text" name="subject_code" placeholder="Subject Code" required>
                    <input type="text" name="description" placeholder="Description" style="grid-column: span 2;">
                </div>
                <button type="submit" name="add_subject" class="btn btn-add">💾 Save Subject</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <!-- Subject List -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($subjects) > 0): ?>
                        <?php foreach($subjects as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><strong><?= htmlspecialchars($s['subject_name']) ?></strong></td>
                            <td><?= $s['subject_code'] ?></td>
                            <td><?= htmlspecialchars($s['description'] ?? '') ?></td>
                            <td><span class="status <?= strtolower($s['status']) ?>"><?= $s['status'] ?></span></td>
                            <td>
                                <button class="btn btn-edit" onclick="editSubject(
                                    <?= $s['id'] ?>,
                                    '<?= addslashes($s['subject_name']) ?>',
                                    '<?= addslashes($s['subject_code']) ?>',
                                    '<?= addslashes($s['description']) ?>',
                                    '<?= $s['status'] ?>'
                                )">Edit</button>
                                <a href="?delete=<?= $s['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this subject?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No subjects found. Add your first subject!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Form -->
        <div id="editForm" style="display:none;">
            <form method="POST">
                <h3>✏️ Edit Subject</h3>
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <input type="text" name="subject_name" id="edit_subject_name" placeholder="Subject Name" required>
                    <input type="text" name="subject_code" id="edit_subject_code" placeholder="Subject Code" required>
                    <input type="text" name="description" id="edit_description" placeholder="Description" style="grid-column: span 2;">
                    <select name="status" id="edit_status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" name="edit_subject" class="btn btn-edit">💾 Update Subject</button>
                <button type="button" onclick="document.getElementById('editForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <script>
        function editSubject(id, name, code, description, status) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_subject_name').value = name;
            document.getElementById('edit_subject_code').value = code;
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