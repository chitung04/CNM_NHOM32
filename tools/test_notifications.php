<?php
/**
 * Test script để kiểm tra hệ thống thông báo
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/Notification.php';

echo "<h2>🔔 Test Hệ thống Thông báo Real-time</h2>\n";

try {
    $notificationModel = new Notification();
    
    echo "<h3>1. Tạo thông báo test</h3>\n";
    
    // Tạo thông báo test
    $testNotifications = [
        ['low_stock', 'Thuốc Paracetamol sắp hết hàng (còn 5 viên)', 1],
        ['expiry_warning', 'Lô thuốc Amoxicillin sắp hết hạn (hết hạn: 25/03/2026)', 2],
        ['low_stock', 'Thuốc Vitamin C sắp hết hàng (còn 3 chai)', 3],
    ];
    
    foreach ($testNotifications as $notif) {
        $result = $notificationModel->create($notif[0], $notif[1], $notif[2]);
        echo "✓ Tạo thông báo: {$notif[1]}<br>\n";
    }
    
    echo "<h3>2. Kiểm tra thông báo</h3>\n";
    
    // Kiểm tra số lượng thông báo chưa đọc
    $unreadCount = $notificationModel->countUnread();
    echo "📊 Số thông báo chưa đọc: <strong>{$unreadCount}</strong><br>\n";
    
    // Lấy danh sách thông báo
    $notifications = $notificationModel->getUnread();
    echo "📋 Danh sách thông báo:<br>\n";
    
    foreach ($notifications as $notif) {
        $icon = $notif['type'] === 'low_stock' ? '📦' : '⏰';
        $type = $notif['type'] === 'low_stock' ? 'Sắp hết hàng' : 'Sắp hết hạn';
        echo "   {$icon} [{$type}] {$notif['message']}<br>\n";
    }
    
    echo "<h3>3. Test AJAX endpoint</h3>\n";
    echo "🌐 Endpoint: <a href='../ajax/get_notifications.php' target='_blank'>ajax/get_notifications.php</a><br>\n";
    echo "🧹 Clear endpoint: <a href='../ajax/clear_notifications.php' target='_blank'>ajax/clear_notifications.php</a><br>\n";
    
    echo "<h3>4. Test tự động kiểm tra</h3>\n";
    
    // Test kiểm tra tự động
    echo "🔍 Kiểm tra thuốc sắp hết hàng...<br>\n";
    $notificationModel->checkLowStock();
    echo "✓ Hoàn thành<br>\n";
    
    echo "🔍 Kiểm tra thuốc sắp hết hạn...<br>\n";
    $notificationModel->checkExpiring();
    echo "✓ Hoàn thành<br>\n";
    
    // Kiểm tra lại số lượng
    $newUnreadCount = $notificationModel->countUnread();
    echo "📊 Số thông báo sau khi kiểm tra: <strong>{$newUnreadCount}</strong><br>\n";
    
    echo "<h3>5. Hướng dẫn sử dụng</h3>\n";
    echo "<ul>\n";
    echo "<li>🔔 Thông báo sẽ hiển thị trên navbar (chuông)</li>\n";
    echo "<li>📱 Banner thông báo sẽ cuộn trên đầu trang</li>\n";
    echo "<li>💬 Popup text thông báo ở góc phải màn hình</li>\n";
    echo "<li>🔄 Tự động refresh mỗi 30 giây</li>\n";
    echo "<li>⚙️ Cron job chạy mỗi ngày để kiểm tra</li>\n";
    echo "</ul>\n";
    
    echo "<h3>✅ Test hoàn thành!</h3>\n";
    echo "<p><a href='../index.php?page=dashboard'>← Quay về Dashboard</a></p>\n";
    echo "<p><a href='../index.php?page=notifications'>📋 Xem trang Thông báo</a></p>\n";
    
} catch (Exception $e) {
    echo "<h3>❌ Lỗi</h3>\n";
    echo "<p style='color: red;'>{$e->getMessage()}</p>\n";
    echo "<pre>{$e->getTraceAsString()}</pre>\n";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #2563eb; }
h3 { color: #059669; margin-top: 20px; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
a { color: #2563eb; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>