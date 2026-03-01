<?php
/**
 * Helper functions cho authentication
 */

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Lấy thông tin user hiện tại
 */
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

/**
 * Kiểm tra role của user
 */
function checkRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return $_SESSION['role'] === $role;
}

/**
 * Kiểm tra user có phải Manager không
 */
function isManager() {
    return checkRole('manager');
}

/**
 * Kiểm tra user có phải Staff không
 */
function isStaff() {
    return checkRole('staff');
}

/**
 * Yêu cầu đăng nhập
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=auth&action=login');
        exit;
    }
}

/**
 * Yêu cầu role Manager
 */
function requireManager() {
    requireLogin();
    if (!isManager()) {
        // Chuyển đến trang lỗi 403
        http_response_code(403);
        require_once 'views/errors/403.php';
        exit;
    }
}

/**
 * Kiểm tra quyền truy cập chức năng
 */
function checkPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Manager có tất cả quyền
    if (isManager()) {
        return true;
    }
    
    // Danh sách quyền của Staff
    $staffPermissions = [
        'sales.view',
        'sales.create',
        'medicines.view',
        'dashboard.view'
    ];
    
    return in_array($permission, $staffPermissions);
}

/**
 * Kiểm tra quyền và trả về boolean (không redirect)
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return checkPermission($permission);
}

/**
 * Yêu cầu quyền cụ thể (redirect nếu không có quyền)
 * Note: Hàm này có thể được định nghĩa trong `helpers/permissions.php` với logic chi tiết hơn.
 * Tránh redeclare nếu hàm đã tồn tại.
 */
if (!function_exists('requirePermission')) {
    function requirePermission($permission) {
        requireLogin();

        if (!checkPermission($permission)) {
            http_response_code(403);
            require_once 'views/errors/403.php';
            exit;
        }
    }
}

/**
 * Kiểm tra session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            if ($elapsed > SESSION_TIMEOUT) {
                session_destroy();
                header('Location: index.php?page=auth&action=login&timeout=1');
                exit;
            }
        }
        $_SESSION['last_activity'] = time();
    }
}

// Gọi check timeout mỗi request
checkSessionTimeout();
