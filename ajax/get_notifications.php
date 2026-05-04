<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/secure_session.php';
require_once $basePath . '/models/Notification.php';

// Kiểm tra đăng nhập - nếu không đăng nhập thì vẫn trả về empty data
if (!isLoggedIn()) {
    echo json_encode([
        'success' => true,
        'count' => 0,
        'notifications' => []
    ]);
    exit;
}

$notificationModel = new Notification();

// Kiểm tra và tạo thông báo mới (cho cả manager và staff)
try {
    $notificationModel->checkLowStock();
    $notificationModel->checkExpiring();
} catch (Exception $e) {
    error_log("Notification check error: " . $e->getMessage());
}

// Lấy danh sách thông báo chưa đọc với thông tin chi tiết
$db = Database::getInstance();

try {
    // Lấy pharmacy_id từ session
    require_once $basePath . '/helpers/pharmacy.php';
    $pharmacyId = getCurrentPharmacyId();
    
    if (!$pharmacyId) {
        echo json_encode([
            'success' => true,
            'count' => 0,
            'notifications' => []
        ]);
        exit;
    }
    
    // Query lấy thông báo chưa đọc
    $sql = "SELECT notification_id, type, message, created_at
            FROM notifications 
            WHERE pharmacy_id = ? AND is_read = 0
            ORDER BY created_at DESC 
            LIMIT 50";

    $stmt = $db->query($sql, [$pharmacyId]);
    $notifications = $stmt->fetchAll();

    // Nếu KHÔNG có thông báo chưa đọc, tạo thông báo mặc định
    if (count($notifications) == 0) {
        // Thông báo mặc định
        $defaultNotifications = [
            [
                'id' => 'default_1',
                'type' => 'info',
                'message' => 'Hệ thống hoạt động bình thường',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 'default_2',
                'type' => 'info',
                'message' => 'Kiểm tra hàng tồn kho định kỳ',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 'default_3',
                'type' => 'info',
                'message' => 'Theo dõi hạn sử dụng thuốc',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 'default_4',
                'type' => 'info',
                'message' => 'Cập nhật thông tin sản phẩm mới',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'count' => count($defaultNotifications),
            'notifications' => $defaultNotifications
        ]);
        exit;
    }

    // Xử lý thông báo thật
    $processedNotifications = [];
    foreach ($notifications as $notif) {
        $processedNotifications[] = [
            'id' => $notif['notification_id'],
            'type' => $notif['type'],
            'message' => $notif['message'],
            'created_at' => $notif['created_at']
        ];
    }

    $count = count($processedNotifications);

    echo json_encode([
        'success' => true,
        'count' => $count,
        'notifications' => $processedNotifications
    ]);
    
} catch (Exception $e) {
    error_log("Get notifications error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'count' => 0,
        'notifications' => []
    ]);
}
