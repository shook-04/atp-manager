<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        header("Location: $base/login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        header("Location: $base/pages/dashboard.php");
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'        => $_SESSION['user_id'],
        'full_name' => $_SESSION['user_name'],
        'role'      => $_SESSION['user_role'],
        'ranking'   => $_SESSION['user_ranking'] ?? null,
    ];
}

function redirect($url) {
    $base = defined('BASE_PATH') ? BASE_PATH : '';
    header("Location: $base$url");
    exit();
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
