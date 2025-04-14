<?php
if (!function_exists('isLoggedIn')) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    function requireLogin() {
        if (!isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    function isAdmin() {
        return isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }

    function requireAdmin() {
        if (!isAdmin()) {
            header('Location: index.php');
            exit;
        }
    }
}
?>