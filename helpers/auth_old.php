<?php
/**
 * Helper functions cho authentication
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role']
    ];
}

function isManager() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
}

function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=auth&action=login');
        exit;
    }
}

function requireManager() {
    requireLogin();
    if (!isManager()) {
        http_response_code(403);
        echo '<div style="text-align: center; margin-top: 50px;"><h3>403 - Không có quyền truy cập</h3><p>Bạn không có quyền truy cập chức năng này.</p></div>';
        exit;
    }
}

// Kiểm tra session timeout đơn giản
if (isLoggedIn() && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > 1800) { // 30 phút
        session_destroy();
        header('Location: index.php?page=auth&action=login&timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}
