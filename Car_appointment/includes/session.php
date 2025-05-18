<?php
session_start();

// USER SESSION FUNCTIONS
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, full_name, phone, address, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
}

function logoutUser() {
    unset($_SESSION['user_id'], $_SESSION['user_username'], $_SESSION['user_role']);
    header('Location: login.php');
    exit;
}

// ADMIN SESSION FUNCTIONS
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentAdmin() {
    if (!isAdmin()) {
        return null;
    }
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, full_name FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $admin;
}

function loginAdmin($admin) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
}

function logoutAdmin() {
    unset($_SESSION['admin_id'], $_SESSION['admin_username']);
    header('Location: login.php');
    exit;
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?> 