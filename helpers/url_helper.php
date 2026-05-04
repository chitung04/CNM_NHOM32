<?php
/**
 * URL Helper - Xử lý URL và kiểm tra môi trường
 */

/**
 * Lấy base URL của ứng dụng
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = str_replace(basename($scriptName), '', $scriptName);
    
    return $protocol . '://' . $host . $basePath;
}

/**
 * Kiểm tra xem có đang chạy trên localhost không
 */
function isLocalhost() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    
    // Các pattern của localhost
    $localhostPatterns = [
        'localhost',
        '127.0.0.1',
        '::1',
        '0.0.0.0'
    ];
    
    foreach ($localhostPatterns as $pattern) {
        if (stripos($host, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Kiểm tra xem QR code có thể hoạt động không
 * QR code cần IP thực hoặc domain để quét từ điện thoại
 */
function canQRCodeWork() {
    return !isLocalhost();
}

/**
 * Lấy IP address của server
 */
function getServerIP() {
    // Thử lấy IP từ các nguồn khác nhau
    $ip = $_SERVER['SERVER_ADDR'] ?? '';
    
    if (empty($ip) || $ip === '::1' || $ip === '127.0.0.1') {
        // Thử lấy IP từ hostname
        $hostname = gethostname();
        $ip = gethostbyname($hostname);
    }
    
    return $ip;
}

/**
 * Lấy URL có thể truy cập từ mobile
 */
function getMobileAccessibleUrl() {
    if (isLocalhost()) {
        $ip = getServerIP();
        if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $basePath = str_replace(basename($scriptName), '', $scriptName);
            return $protocol . '://' . $ip . $basePath;
        }
        return null; // Không có URL truy cập được từ mobile
    }
    
    return getBaseUrl();
}

/**
 * Tạo URL đầy đủ cho QR code
 */
function getQRCodeUrl($qrCode) {
    $baseUrl = getMobileAccessibleUrl();
    if (!$baseUrl) {
        return null;
    }
    return $baseUrl . 'medicine_info.php?qr=' . urlencode($qrCode);
}

/**
 * Lấy thông báo cảnh báo về localhost
 */
function getLocalhostWarning() {
    $ip = getServerIP();
    $message = "⚠️ Đang chạy trên localhost - QR code không thể quét từ điện thoại.";
    
    if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
        $message .= "<br>💡 Để QR code hoạt động, truy cập qua IP: <strong>http://" . $ip . "</strong>";
    } else {
        $message .= "<br>💡 Để QR code hoạt động, cần deploy lên server hoặc dùng IP thực.";
    }
    
    return $message;
}
?>
