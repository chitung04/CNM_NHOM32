<?php
// Load environment variables
require_once __DIR__ . '/../helpers/env.php';
loadEnv(__DIR__ . '/../.env');

// Cấu hình chung hệ thống
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error reporting dựa trên environment
if (env('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Cấu hình session (chỉ set nếu session chưa start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Strict');
}

// Session timeout
define('SESSION_TIMEOUT', env('SESSION_TIMEOUT', 1800));

// Đường dẫn gốc
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', env('BASE_URL', 'http://localhost/pharmacy'));

// Thư mục uploads
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('BACKUP_PATH', UPLOAD_PATH . '/backups');
define('QRCODE_PATH', BASE_PATH . '/assets/qrcodes');

// Cấu hình phân trang
define('ITEMS_PER_PAGE', 20);

// Cấu hình cảnh báo
define('LOW_STOCK_THRESHOLD', 10);
define('EXPIRY_WARNING_DAYS', 30);
