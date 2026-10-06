<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND password = ? AND status = 'active'");
    $stmt->execute([$username, $username, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolHub - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h1>🏫 ABI-JORDAN </h1>
            <p>School Management System</p>
        </div>
        <form method="POST">
            <h2>Login</h2>
            <?php if(isset($error)): ?>
                <div class="message error"><?= $error ?></div>
            <?php endif; ?>
            <input type="text" name="username" placeholder="Username or Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
        <div class="login-info">
            <p><strong>Admin:</strong> admin / admin123</p>
            <p><strong>Teacher:</strong> teacher1 / teacher123</p>
            <p><strong>Student:</strong> student1 / student123</p>
        </div>
    </div>
    <script src="theme-toggle.js"></script>

</body>
</html>