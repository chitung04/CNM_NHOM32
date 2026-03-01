<?php
require_once 'models/User.php';
require_once 'helpers/csrf.php';
require_once 'helpers/logger.php';
require_once 'helpers/security.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Hiển thị trang đăng nhập và xử lý login
     */
    public function login() {
        $error = '';
        
        // Nếu đã đăng nhập, chuyển về dashboard
        if (isLoggedIn()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        // Xử lý form submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $error = 'Vui lòng nhập tên đăng nhập và mật khẩu';
            } else {
                $user = $this->userModel->authenticate($username, $password);
                
                if ($user) {
                    // Regenerate session ID để tránh session fixation
                    session_regenerate_id(true);
                    
                    // Đăng nhập thành công
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();
                    
                    // Log đăng nhập thành công (nếu có)
                    if (function_exists('logLogin')) {
                        logLogin($username, true);
                    }
                    if (function_exists('logAction')) {
                        logAction('LOGIN', "User: $username, Role: {$user['role']}");
                    }
                    if (function_exists('auditLogin')) {
                        auditLogin($username, true);
                    }
                    
                    header('Location: index.php?page=dashboard');
                    exit;
                } else {
                    $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
                    
                    // Log đăng nhập thất bại (nếu có)
                    if (function_exists('logLogin')) {
                        logLogin($username, false);
                    }
                    if (function_exists('auditLogin')) {
                        auditLogin($username, false);
                    }
                }
            }
        }
        
        // Hiển thị form đăng nhập
        require_once 'views/auth/login.php';
    }
    
    /**
     * Xử lý đăng xuất
     */
    public function logout() {
        // Log đăng xuất
        if (isset($_SESSION['username'])) {
            logAction('LOGOUT', "User: {$_SESSION['username']}");
            
            // Audit log đăng xuất
            auditLogout();
        }
        
        // Xóa tất cả session
        $_SESSION = [];
        
        // Xóa session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Hủy session
        session_destroy();
        
        // Chuyển về trang đăng nhập
        header('Location: index.php?page=auth&action=login');
        exit;
    }
}
