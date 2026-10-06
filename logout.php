<?php
session_start();

// Log activity before logout
if (isset($_SESSION['user_id'])) {
    try {
        require_once 'config.php';
        logActivity($pdo, $_SESSION['user_id'], 'Logout', 'User logged out');
    } catch(PDOException $e) {
        // Silently fail
    }
}

// Destroy session
session_destroy();
header('Location: login.php');
exit();
?>