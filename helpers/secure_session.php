<?php
/**
 * Hệ thống session bảo mật - Ngăn chia sẻ session giữa các tab/cửa sổ
 */

class SecureSession {
    private static $instance = null;
    private $sessionToken = null;
    private $browserFingerprint = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->initSecureSession();
    }
    
    /**
     * Khởi tạo session bảo mật
     */
    private function initSecureSession() {
        // Cấu hình session bảo mật
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_lifetime', 0); // Session cookie - expires when browser closes
            
            session_start();
        }
        
        // Tạo browser fingerprint
        $this->browserFingerprint = $this->generateBrowserFingerprint();
        
        // Kiểm tra session hiện tại
        $this->validateSession();
    }
    
    /**
     * Tạo browser fingerprint để xác định trình duyệt
     */
    private function generateBrowserFingerprint() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Tạo fingerprint từ thông tin trình duyệt
        $fingerprint = hash('sha256', $userAgent . $acceptLanguage . $acceptEncoding . $remoteAddr);
        
        return $fingerprint;
    }
    
    /**
     * Tạo session token unique cho mỗi lần đăng nhập
     */
    private function generateSessionToken() {
        return hash('sha256', uniqid() . time() . $this->browserFingerprint . random_bytes(32));
    }
    
    /**
     * Xác thực session
     */
    private function validateSession() {
        // Nếu chưa có session token, tạo mới
        if (!isset($_SESSION['session_token'])) {
            $_SESSION['session_token'] = $this->generateSessionToken();
            $_SESSION['browser_fingerprint'] = $this->browserFingerprint;
            $_SESSION['created_at'] = time();
        }
        
        // Kiểm tra browser fingerprint - QUAN TRỌNG!
        if (isset($_SESSION['browser_fingerprint']) && $_SESSION['browser_fingerprint'] !== $this->browserFingerprint) {
            // PHÁT HIỆN TRÌNH DUYỆT KHÁC - ĐĂNG XUẤT TẤT CẢ
            $this->logSecurityViolation('Browser fingerprint mismatch - possible session hijacking');
            $this->destroyAllSessions();
            return false;
        }
        
        // Kiểm tra session timeout
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > 1800) { // 30 phút
                $this->destroySession();
                return false;
            }
        }
        
        // Cập nhật last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Đăng nhập user
     */
    public function login($userId, $username, $fullName, $role) {
        // Tạo session mới
        session_regenerate_id(true);
        
        // Tạo session token mới
        $this->sessionToken = $this->generateSessionToken();
        
        // Lưu thông tin user
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['role'] = $role;
        $_SESSION['session_token'] = $this->sessionToken;
        $_SESSION['browser_fingerprint'] = $this->browserFingerprint;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();
        
        // Tạo secure cookie với session token
        $this->setSecureCookie('auth_token', $this->sessionToken);
        
        return true;
    }
    
    /**
     * Kiểm tra đăng nhập
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            return false;
        }
        
        // Kiểm tra session token
        if (!isset($_SESSION['session_token'])) {
            return false;
        }
        
        // Kiểm tra cookie token
        $cookieToken = $_COOKIE['auth_token'] ?? '';
        if ($cookieToken !== $_SESSION['session_token']) {
            $this->destroySession();
            return false;
        }
        
        // Validate session - QUAN TRỌNG: Sẽ đăng xuất nếu phát hiện trình duyệt khác
        if (!$this->validateSession()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Đăng xuất
     */
    public function logout() {
        $this->destroySession();
    }
    
    /**
     * Hủy session
     */
    private function destroySession() {
        // Xóa cookie
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/', '', false, true);
        }
        
        // Xóa session
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Hủy tất cả sessions - khi phát hiện vi phạm bảo mật
     */
    private function destroyAllSessions() {
        // Log security event
        error_log("SECURITY ALERT: Destroying all sessions due to browser fingerprint mismatch");
        
        // Xóa tất cả session files (nếu sử dụng file-based sessions)
        $sessionPath = session_save_path();
        if (empty($sessionPath)) {
            $sessionPath = sys_get_temp_dir();
        }
        
        // Tạo session invalidation flag
        $userId = $_SESSION['user_id'] ?? 'unknown';
        $invalidationFile = $sessionPath . '/session_invalidated_' . $userId . '_' . time();
        file_put_contents($invalidationFile, json_encode([
            'user_id' => $userId,
            'reason' => 'browser_fingerprint_mismatch',
            'timestamp' => time(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]));
        
        // Hủy session hiện tại
        $this->destroySession();
    }
    
    /**
     * Log security violation
     */
    private function logSecurityViolation($reason) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'reason' => $reason,
            'user_id' => $_SESSION['user_id'] ?? 'unknown',
            'username' => $_SESSION['username'] ?? 'unknown',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'session_token' => substr($_SESSION['session_token'] ?? '', 0, 8) . '...',
            'browser_fingerprint_expected' => $_SESSION['browser_fingerprint'] ?? 'none',
            'browser_fingerprint_actual' => $this->browserFingerprint
        ];
        
        error_log("SECURITY VIOLATION: " . json_encode($logData));
        
        // Ghi vào file log riêng
        $logFile = dirname(__DIR__) . '/logs/security_violations.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Tạo secure cookie
     */
    private function setSecureCookie($name, $value, $expire = 0) {
        $expire = $expire ?: 0; // Session cookie
        setcookie($name, $value, $expire, '/', '', false, true);
    }
    
    /**
     * Lấy thông tin user hiện tại
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'login_time' => $_SESSION['login_time'] ?? null,
            'session_token' => $_SESSION['session_token'] ?? null
        ];
    }
    
    /**
     * Kiểm tra role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Require login
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            // Kiểm tra lý do logout
            $reason = 'session_required';
            if (isset($_SESSION['logout_reason'])) {
                $reason = $_SESSION['logout_reason'];
                unset($_SESSION['logout_reason']);
            }
            
            header('Location: index.php?page=auth&action=login&reason=' . $reason);
            exit;
        }
    }
    
    /**
     * Require manager role
     */
    public function requireManager() {
        $this->requireLogin();
        if (!$this->hasRole('manager')) {
            http_response_code(403);
            echo '<div style="text-align: center; margin-top: 50px;">';
            echo '<h3>403 - Không có quyền truy cập</h3>';
            echo '<p>Bạn không có quyền truy cập chức năng này.</p>';
            echo '<a href="index.php" class="btn btn-primary">Về trang chủ</a>';
            echo '</div>';
            exit;
        }
    }
    
    /**
     * Tạo CSRF token (đã chuyển sang csrf.php)
     */
    public function generateCSRFToken() {
        // Sử dụng hàm từ csrf.php
        if (function_exists('generateCsrfToken')) {
            return generateCsrfToken();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Xác thực CSRF token (đã chuyển sang csrf.php)
     */
    public function validateCSRFToken($token) {
        // Sử dụng hàm từ csrf.php
        if (function_exists('verifyCsrfToken')) {
            return verifyCsrfToken($token);
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Lấy session stats
     */
    public function getSessionStats() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'session_duration' => time() - ($_SESSION['login_time'] ?? time()),
            'browser_fingerprint' => substr($this->browserFingerprint, 0, 8) . '...',
            'session_token' => substr($_SESSION['session_token'] ?? '', 0, 8) . '...'
        ];
    }
}

// Helper functions để tương thích với code cũ
function secureSession() {
    return SecureSession::getInstance();
}

function isLoggedIn() {
    return secureSession()->isLoggedIn();
}

function getCurrentUser() {
    return secureSession()->getCurrentUser();
}

function isManager() {
    return secureSession()->hasRole('manager');
}

function isStaff() {
    return secureSession()->hasRole('staff');
}

function hasRole($role) {
    return secureSession()->hasRole($role);
}

function requireLogin() {
    return secureSession()->requireLogin();
}

function requireManager() {
    return secureSession()->requireManager();
}

// CSRF functions - sử dụng từ csrf.php
// Các hàm CSRF đã được định nghĩa trong csrf.php
?>