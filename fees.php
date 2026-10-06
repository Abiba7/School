<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config.php';

// Handle Add Fee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_fee'])) {
    $student_id = $_POST['student_id'];
    $fee_type = $_POST['fee_type'];
    $amount = $_POST['amount'];
    $amount_paid = $_POST['amount_paid'];
    $term = $_POST['term'];
    $academic_year = $_POST['academic_year'];
    $due_date = $_POST['due_date'];
    $payment_date = $_POST['payment_date'] ?: null;
    $payment_method = $_POST['payment_method'];
    $receipt_number = $_POST['receipt_number'];
    $notes = $_POST['notes'];
    
    $balance = $amount - $amount_paid;
    $status = $balance <= 0 ? 'Paid' : ($amount_paid > 0 ? 'Partial' : 'Pending');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO fees (student_id, fee_type, amount, amount_paid, balance, term, academic_year, due_date, payment_date, payment_method, receipt_number, status, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$student_id, $fee_type, $amount, $amount_paid, $balance, $term, $academic_year, $due_date, $payment_date, $payment_method, $receipt_number, $status, $notes, $_SESSION['user_id']])) {
            header('Location: fees.php?msg=Fee recorded successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Delete Fee
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM fees WHERE id = ?");
        if ($stmt->execute([$id])) {
            header('Location: fees.php?msg=Fee deleted successfully');
            exit();
        }
    } catch(PDOException $e) {
        $error = "Error deleting fee";
    }
}

// Fetch fees with student names
$fees = $pdo->query("SELECT f.*, s.full_name as student_name FROM fees f JOIN students s ON f.student_id = s.id ORDER BY f.id DESC")->fetchAll();
$students = $pdo->query("SELECT id, full_name FROM students WHERE status='Active'")->fetchAll();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Fees</title>
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
            <a href="fees.php" class="active">💰 Fees</a>
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
            <h1>Fee Management</h1>
            <button class="btn btn-add" onclick="document.getElementById('addForm').style.display='block'">💰 Record Payment</button>
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
                <h3>💰 Record Fee Payment</h3>
                <div class="form-grid">
                    <select name="student_id" required>
                        <option value="">Select Student</option>
                        <?php foreach($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="fee_type" placeholder="Fee Type (e.g., Tuition Fee)" required>
                    <input type="number" step="0.01" name="amount" placeholder="Total Amount" required>
                    <input type="number" step="0.01" name="amount_paid" placeholder="Amount Paid" required>
                    <select name="term" required>
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2">Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                    <input type="text" name="academic_year" placeholder="Academic Year" required>
                    <input type="date" name="due_date" required>
                    <input type="date" name="payment_date">
                    <select name="payment_method">
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Card">Card</option>
                    </select>
                    <input type="text" name="receipt_number" placeholder="Receipt Number">
                    <input type="text" name="notes" placeholder="Notes" style="grid-column: span 2;">
                </div>
                <button type="submit" name="add_fee" class="btn btn-add">Record Payment</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-delete">Cancel</button>
            </form>
        </div>

        <!-- Fee List -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Fee Type</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($fees) > 0): ?>
                        <?php foreach($fees as $f): ?>
                        <tr>
                            <td><?= $f['id'] ?></td>
                            <td><strong><?= htmlspecialchars($f['student_name']) ?></strong></td>
                            <td><?= htmlspecialchars($f['fee_type']) ?></td>
                            <td>$<?= number_format($f['amount'], 2) ?></td>
                            <td>$<?= number_format($f['amount_paid'], 2) ?></td>
                            <td><strong style="color: <?= $f['balance'] > 0 ? '#e53e3e' : '#48bb78' ?>">$<?= number_format($f['balance'], 2) ?></strong></td>
                            <td><span class="status <?= strtolower($f['status']) ?>"><?= $f['status'] ?></span></td>
                            <td>
                                <a href="#" class="btn btn-view">Receipt</a>
                                <a href="?delete=<?= $f['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this record?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No fee records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="theme-toggle.js"></script>

</body>
</html>