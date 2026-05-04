<?php
/**
 * Hệ thống truy cập công khai cho QR codes
 * Tạo session/cookie tạm thời để truy cập thông tin thuốc mà không cần đăng nhập
 */

// Tạo public access token
function generatePublicAccessToken($qrCode, $duration = 3600) {
    $token = md5($qrCode . time() . 'DUO_PHARMA_PUBLIC');
    $expiry = time() + $duration; // Mặc định 1 giờ
    
    // Lưu token vào session
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $_SESSION['public_access_tokens'][$token] = [
        'qr_code' => $qrCode,
        'created_at' => time(),
        'expires_at' => $expiry,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Lưu token vào cookie
    setcookie('public_access_token', $token, $expiry, '/', '', false, true);
    
    return $token;
}

// Kiểm tra public access token
function validatePublicAccessToken($token = null) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Lấy token từ parameter hoặc cookie
    if (!$token) {
        $token = $_GET['token'] ?? $_COOKIE['public_access_token'] ?? null;
    }
    
    if (!$token) {
        return false;
    }
    
    // Kiểm tra token trong session
    if (!isset($_SESSION['public_access_tokens'][$token])) {
        return false;
    }
    
    $tokenData = $_SESSION['public_access_tokens'][$token];
    
    // Kiểm tra hết hạn
    if (time() > $tokenData['expires_at']) {
        unset($_SESSION['public_access_tokens'][$token]);
        return false;
    }
    
    return $tokenData;
}

// Tạo URL công khai với token
function createPublicAccessUrl($qrCode, $baseUrl = null) {
    if (!$baseUrl) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI']);
    }
    
    $token = generatePublicAccessToken($qrCode);
    return $baseUrl . '/medicine_info.php?qr=' . urlencode($qrCode) . '&token=' . $token;
}

// Bypass authentication cho public access
function allowPublicAccess() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Kiểm tra nếu có token hợp lệ
    $tokenData = validatePublicAccessToken();
    if ($tokenData) {
        // Tạo session tạm thời cho public access
        $_SESSION['public_access'] = true;
        $_SESSION['public_access_qr'] = $tokenData['qr_code'];
        $_SESSION['public_access_expires'] = $tokenData['expires_at'];
        return true;
    }
    
    return false;
}

// Kiểm tra xem có phải public access không
function isPublicAccess() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['public_access'])) {
        return false;
    }
    
    // Kiểm tra hết hạn
    if (time() > $_SESSION['public_access_expires']) {
        unset($_SESSION['public_access']);
        unset($_SESSION['public_access_qr']);
        unset($_SESSION['public_access_expires']);
        return false;
    }
    
    return true;
}

// Dọn dẹp tokens hết hạn
function cleanupExpiredTokens() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['public_access_tokens'])) {
        return;
    }
    
    $currentTime = time();
    foreach ($_SESSION['public_access_tokens'] as $token => $data) {
        if ($currentTime > $data['expires_at']) {
            unset($_SESSION['public_access_tokens'][$token]);
        }
    }
}

// Tạo QR code với public access
function generateQRWithPublicAccess($qrCode, $medicineId = null) {
    require_once 'qrcode.php';
    
    // Tạo URL công khai
    $publicUrl = createPublicAccessUrl($qrCode);
    
    // Tạo QR code
    $qrImagePath = "assets/qrcodes/{$qrCode}.png";
    
    if (!file_exists($qrImagePath)) {
        generateQRCode($publicUrl, $qrImagePath);
    }
    
    return [
        'qr_code' => $qrCode,
        'public_url' => $publicUrl,
        'image_path' => $qrImagePath
    ];
}
?>