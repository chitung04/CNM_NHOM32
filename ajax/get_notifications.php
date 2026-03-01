<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/auth.php';
require_once $basePath . '/models/Notification.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$notificationModel = new Notification();

// Kiểm tra và tạo thông báo mới
try {
    $notificationModel->checkLowStock();
    $notificationModel->checkExpiring();
} catch (Exception $e) {
    error_log("Notification check error: " . $e->getMessage());
}

// Lấy danh sách thông báo chưa đọc
$notifications = $notificationModel->getUnread();
$count = $notificationModel->countUnread();

echo json_encode([
    'success' => true,
    'count' => $count,
    'notifications' => $notifications
]);
