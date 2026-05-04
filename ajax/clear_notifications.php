<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/secure_session.php';
require_once $basePath . '/models/Notification.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$notificationModel = new Notification();

try {
    // Đánh dấu tất cả thông báo là đã đọc
    $result = $notificationModel->markAllAsRead();
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa tất cả thông báo'
    ]);
    
} catch (Exception $e) {
    error_log("Clear notifications error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi xóa thông báo'
    ]);
}