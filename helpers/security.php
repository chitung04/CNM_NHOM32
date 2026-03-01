<?php
/**
 * Security Helper Functions
 */

/**
 * Sanitize input để tránh XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output để hiển thị an toàn
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate số điện thoại Việt Nam
 */
function validatePhone($phone) {
    // Format: 0xxxxxxxxx (10 số)
    return preg_match('/^0[0-9]{9}$/', $phone);
}

/**
 * Validate mật khẩu mạnh
 * - Ít nhất 8 ký tự
 * - Có chữ hoa
 * - Có chữ thường
 * - Có số
 */
function validateStrongPassword($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 8 ký tự'];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 chữ hoa'];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 chữ thường'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 chữ số'];
    }
    
    return ['valid' => true, 'message' => 'Mật khẩu hợp lệ'];
}

/**
 * Sanitize SQL input (dùng với prepared statements)
 */
function sanitizeSql($input) {
    return trim(strip_tags($input));
}

/**
 * Validate date format
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validate số dương
 */
function validatePositiveNumber($number) {
    return is_numeric($number) && $number > 0;
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    $charsLength = strlen($chars);
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $charsLength - 1)];
    }
    
    return $password;
}

/**
 * Rate limiting - giới hạn số lần thử
 */
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $now = time();
    $rateLimitKey = "rate_limit_$key";
    
    if (!isset($_SESSION[$rateLimitKey])) {
        $_SESSION[$rateLimitKey] = ['count' => 1, 'start' => $now];
        return true;
    }
    
    $data = $_SESSION[$rateLimitKey];
    
    // Reset nếu đã hết time window
    if ($now - $data['start'] > $timeWindow) {
        $_SESSION[$rateLimitKey] = ['count' => 1, 'start' => $now];
        return true;
    }
    
    // Kiểm tra số lần thử
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$rateLimitKey]['count']++;
    return true;
}

/**
 * Lấy thời gian còn lại của rate limit
 */
function getRateLimitRemaining($key, $timeWindow = 300) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $rateLimitKey = "rate_limit_$key";
    
    if (!isset($_SESSION[$rateLimitKey])) {
        return 0;
    }
    
    $data = $_SESSION[$rateLimitKey];
    $elapsed = time() - $data['start'];
    $remaining = $timeWindow - $elapsed;
    
    return max(0, $remaining);
}
