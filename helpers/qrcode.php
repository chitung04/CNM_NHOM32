<?php
/**
 * QR Code Helper Functions
 * Sử dụng thư viện phpqrcode
 */

/**
 * Tạo QR code cho thuốc hoặc lô thuốc
 */
function generateQRCode($data, $filename) {
    // Load thư viện QR code
    require_once __DIR__ . '/phpqrcode.php';
    
    // Đảm bảo thư mục tồn tại
    if (!file_exists(QRCODE_PATH)) {
        mkdir(QRCODE_PATH, 0777, true);
    }
    
    $filepath = QRCODE_PATH . '/' . $filename . '.png';
    
    // Tạo QR code sử dụng thư viện
    try {
        QRcode::png($data, $filepath, 'L', 3, 2);
        return $filename . '.png';
    } catch (Exception $e) {
        error_log("QR Code generation failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Tạo mã QR code unique
 */
function generateUniqueQRCode($prefix = 'MED') {
    return $prefix . '_' . time() . '_' . rand(1000, 9999);
}

/**
 * Lấy đường dẫn QR code
 */
function getQRCodePath($filename) {
    if (empty($filename)) {
        return null;
    }
    return BASE_URL . '/assets/qrcodes/' . $filename;
}

/**
 * Xóa file QR code
 */
function deleteQRCode($filename) {
    if (empty($filename)) {
        return false;
    }
    
    $filepath = QRCODE_PATH . '/' . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return false;
}
