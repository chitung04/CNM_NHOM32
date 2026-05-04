<?php
/**
 * CSRF Protection Helper
 */

// Kiểm tra xem hàm đã tồn tại chưa trước khi định nghĩa
if (!function_exists('generateCsrfToken')) {
    /**
     * Tạo CSRF token mới
     */
    function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('getCsrfToken')) {
    /**
     * Lấy CSRF token hiện tại
     */
    function getCsrfToken() {
        return generateCsrfToken();
    }
}

if (!function_exists('csrfField')) {
    /**
     * Tạo input hidden cho CSRF token
     */
    function csrfField() {
        $token = getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('verifyCsrfToken')) {
    /**
     * Verify CSRF token
     */
    function verifyCsrfToken($token = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Lấy token từ POST nếu không truyền vào
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }
        
        // Kiểm tra token có tồn tại trong session không
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        // So sánh token (dùng hash_equals để tránh timing attack)
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('requireCsrfToken')) {
    /**
     * Require CSRF token - throw exception nếu không hợp lệ
     */
    function requireCsrfToken() {
        if (!verifyCsrfToken()) {
            http_response_code(403);
            die('CSRF token không hợp lệ. Vui lòng thử lại.');
        }
    }
}

// Alias functions để tương thích với secure_session.php
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        return generateCsrfToken();
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return verifyCsrfToken($token);
    }
}