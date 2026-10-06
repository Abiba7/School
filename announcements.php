<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// Handle Add Announcement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_announcement'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $target_roles = $_POST['target_roles'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, category, priority, target_roles, start_date, end_date, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $content, $category, $priority, $target_roles, $start_date, $end_date, $status, $_SESSION['user_id']])) {
            header('Location: announcements.php?msg=Announcement posted successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Delete Announcement
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        if ($stmt->execute([$id])) {
            header('Location: announcements.php?msg=Announcement deleted successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error deleting announcement";
    }
}

// Handle Edit Announcement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_announcement'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $target_roles = $_POST['target_roles'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE announcements SET title=?, content=?, category=?, priority=?, target_roles=?, start_date=?, end_date=?, status=? WHERE id=?");
        if ($stmt->execute([$title, $content, $category, $priority, $target_roles, $start_date, $end_date, $status, $id])) {
            header('Location: announcements.php?msg=Announcement updated successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch announcements
$announcements = $pdo->query("SELECT a.*, u.full_name as author FROM announcements a JOIN users u ON a.created_by = u.id ORDER BY a.id DESC")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Announcements</title>
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
            <a href="announcements.php" class="active">📢 Announcements</a>
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
            <h1>📢 Announcements</h1>
            <button class="btn btn-add" onclick="document.getElementById('addForm').style.display='block'">➕ Post Announcement</button>
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
                <h3>📢 Post New Announcement</h3>
                <div class="form-grid">
                    <input type="text" name="title" placeholder="Title" required style="grid-column: span 2;">
                    <textarea name="content" placeholder="Content" required style="grid-column: span 2; min-height: 80px;"></textarea>
                    <select name="category">
                        <option value="General">General</option>
                        <option value="Academic">Academic</option>
                        <option value="Event">Event</option>
                        <option value="Exam">Exam</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                    <select name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                    <input type="text" name="target_roles" placeholder="Target Roles (e.g., admin,teacher,student)">
                    <input type="date" name="start_date">
                    <input type="date" name="end_date">
                    <select name="status">
                        <option value="Published">Published</option>
                        <option value="Draft">Draft</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>
                <button type="submit" name="add_announcement" class="btn btn-add">Post Announcement</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <!-- Announcement List -->
        <div class="announcements-list">
            <?php if(count($announcements) > 0): ?>
                <?php foreach($announcements as $a): ?>
                    <div class="announcement-card" style="border-left: 5px solid <?= $a['priority'] == 'Urgent' ? '#e53e3e' : ($a['priority'] == 'High' ? '#ed8936' : ($a['priority'] == 'Medium' ? '#4299e1' : '#a0aec0')) ?>;">
                        <div class="announcement-header">
                            <div>
                                <h3><?= htmlspecialchars($a['title']) ?></h3>
                                <div class="announcement-meta">
                                    <span>📅 <?= date('M d, Y', strtotime($a['created_at'])) ?></span>
                                    <span> | 👤 <?= htmlspecialchars($a['author']) ?></span>
                                    <span> | 🏷️ <?= $a['category'] ?></span>
                                </div>
                            </div>
                            <div class="announcement-actions">
                                <span class="status <?= strtolower($a['priority']) ?>"><?= $a['priority'] ?></span>
                                <span class="status <?= strtolower($a['status']) ?>"><?= $a['status'] ?></span>
                                <button class="btn btn-edit" onclick="editAnnouncement(
                                    <?= $a['id'] ?>,
                                    '<?= addslashes($a['title']) ?>',
                                    '<?= addslashes($a['content']) ?>',
                                    '<?= $a['category'] ?>',
                                    '<?= $a['priority'] ?>',
                                    '<?= addslashes($a['target_roles']) ?>',
                                    '<?= $a['start_date'] ?>',
                                    '<?= $a['end_date'] ?>',
                                    '<?= $a['status'] ?>'
                                )">Edit</button>
                                <a href="?delete=<?= $a['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this announcement?')">Delete</a>
                            </div>
                        </div>
                        <div class="announcement-content">
                            <p><?= nl2br(htmlspecialchars($a['content'])) ?></p>
                        </div>
                        <?php if($a['target_roles']): ?>
                            <div class="announcement-target">
                                <small>Target: <?= htmlspecialchars($a['target_roles']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted" style="padding: 40px;">
                    <p>No announcements found. Post your first announcement!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Edit Form -->
        <div id="editForm" style="display:none;">
            <form method="POST">
                <h3>✏️ Edit Announcement</h3>
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <input type="text" name="title" id="edit_title" placeholder="Title" required style="grid-column: span 2;">
                    <textarea name="content" id="edit_content" placeholder="Content" required style="grid-column: span 2; min-height: 80px;"></textarea>
                    <select name="category" id="edit_category">
                        <option value="General">General</option>
                        <option value="Academic">Academic</option>
                        <option value="Event">Event</option>
                        <option value="Exam">Exam</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                    <select name="priority" id="edit_priority">
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                    <input type="text" name="target_roles" id="edit_target_roles" placeholder="Target Roles">
                    <input type="date" name="start_date" id="edit_start_date">
                    <input type="date" name="end_date" id="edit_end_date">
                    <select name="status" id="edit_status">
                        <option value="Published">Published</option>
                        <option value="Draft">Draft</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>
                <button type="submit" name="edit_announcement" class="btn btn-edit">Update Announcement</button>
                <button type="button" onclick="document.getElementById('editForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <script>
        function editAnnouncement(id, title, content, category, priority, target_roles, start_date, end_date, status) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_content').value = content;
            document.getElementById('edit_category').value = category || 'General';
            document.getElementById('edit_priority').value = priority || 'Medium';
            document.getElementById('edit_target_roles').value = target_roles || '';
            document.getElementById('edit_start_date').value = start_date || '';
            document.getElementById('edit_end_date').value = end_date || '';
            document.getElementById('edit_status').value = status || 'Published';
            document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
        }
        </script>
    </div>
</div>
<script src="theme-toggle.js"></script>
</body>
</html>