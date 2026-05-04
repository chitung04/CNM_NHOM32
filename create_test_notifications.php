<?php
/**
 * Tạo thông báo test trong database
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Notification.php';

try {
    $notificationModel = new Notification();
    
    // Xóa thông báo cũ
    $db = Database::getInstance();
    $db->execute("DELETE FROM notifications WHERE message LIKE '%test%' OR message LIKE '%Paracetamol%' OR message LIKE '%Amoxicillin%' OR message LIKE '%Vitamin C%'");
    
    // Tạo thông báo mới
    $testNotifications = [
        ['low_stock', 'Thuốc Paracetamol sắp hết hàng (còn 5 viên)', 1],
        ['expiry_warning', 'Lô thuốc Amoxicillin sắp hết hạn (hết hạn: 25/03/2026)', 2],
        ['low_stock', 'Thuốc Vitamin C sắp hết hàng (còn 3 chai)', 3],
        ['expiry_warning', 'Lô thuốc Aspirin sắp hết hạn (hết hạn: 30/03/2026)', 4],
        ['low_stock', 'Thuốc Ibuprofen sắp hết hàng (còn 2 hộp)', 5]
    ];
    
    foreach ($testNotifications as $notif) {
        $result = $notificationModel->create($notif[0], $notif[1], $notif[2]);
        if ($result) {
            echo "✅ Tạo thông báo: {$notif[1]}<br>";
        } else {
            echo "❌ Lỗi tạo thông báo: {$notif[1]}<br>";
        }
    }
    
    // Kiểm tra số lượng thông báo
    $count = $notificationModel->countUnread();
    echo "<br><strong>📊 Tổng số thông báo chưa đọc: {$count}</strong><br>";
    
    echo "<br><a href='index.php?page=dashboard'>← Quay về Dashboard để xem thông báo</a>";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
    echo "<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>