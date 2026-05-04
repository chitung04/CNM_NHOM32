<?php
/**
 * Test notification popup - Tạo thông báo test
 */

session_start();

require_once 'config/database.php';
require_once 'models/Database.php';

// Set pharmacy_id
if (!isset($_SESSION['pharmacy_id'])) {
    $_SESSION['pharmacy_id'] = 1;
}

$db = Database::getInstance();

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'>";
echo "<title>Test Notification Popup</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
.success { background: #d4edda; border: 2px solid #28a745; padding: 20px; margin: 20px 0; }
.info { background: #d1ecf1; border: 2px solid #17a2b8; padding: 20px; margin: 20px 0; }
</style></head><body>";

echo "<h2>🧪 Test Notification Popup</h2>";
echo "<hr>";

try {
    // Xóa thông báo cũ
    echo "<h3>1️⃣ Xóa thông báo cũ...</h3>";
    $db->query("DELETE FROM notifications WHERE pharmacy_id = 1");
    echo "<p style='color: green;'>✅ Đã xóa</p>";
    
    // Tạo 3 thông báo test
    echo "<h3>2️⃣ Tạo 3 thông báo test...</h3>";
    
    $notifications = [
        ['low_stock', 'Thuốc Paracetamol 500mg sắp hết hàng (còn 25 viên)', 1],
        ['expiry_warning', 'Lô thuốc Amoxicillin 500mg (Lô: BATCH_001) sắp hết hạn (hết hạn: 15/06/2026)', 2],
        ['low_stock', 'Thuốc Vitamin C 1000mg sắp hết hàng (còn 10 viên)', 3]
    ];
    
    foreach ($notifications as $notif) {
        $sql = "INSERT INTO notifications (pharmacy_id, type, message, reference_id, is_read, created_at) 
                VALUES (1, ?, ?, ?, 0, NOW())";
        $db->execute($sql, $notif);
        echo "<p style='color: orange;'>⚠️ {$notif[1]}</p>";
    }
    
    echo "<div class='success'>";
    echo "<h3>✅ HOÀN TẤT!</h3>";
    echo "<p>Đã tạo 3 thông báo test.</p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>🎯 Bước tiếp theo:</h3>";
    echo "<ol style='font-size: 16px;'>";
    echo "<li><strong>Mở trang web</strong> (hoặc refresh nếu đang mở)</li>";
    echo "<li>Xem <strong>popup màu vàng/cam</strong> xuất hiện bên cạnh chuông 🔔</li>";
    echo "<li>Popup sẽ <strong>nháy và hiển thị thông báo tuần tự</strong>:</li>";
    echo "<ul>";
    echo "<li>⚠️ Thuốc Paracetamol 500mg sắp hết hàng...</li>";
    echo "<li>⏰ Lô thuốc Amoxicillin 500mg sắp hết hạn...</li>";
    echo "<li>⚠️ Thuốc Vitamin C 1000mg sắp hết hàng...</li>";
    echo "</ul>";
    echo "<li>Chuông sẽ có <strong>badge số 3</strong> màu đỏ</li>";
    echo "<li>Chuông sẽ <strong>nhảy và nháy</strong></li>";
    echo "</ol>";
    
    echo "<p style='margin-top: 20px;'>";
    echo "<a href='index.php?page=dashboard' style='display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>🏠 Mở trang web ngay</a>";
    echo "</p>";
    echo "</div>";
    
    // Hiển thị thông báo trong database
    echo "<hr>";
    echo "<h3>📊 Thông báo trong database:</h3>";
    $stmt = $db->query("SELECT * FROM notifications WHERE pharmacy_id = 1 ORDER BY created_at DESC");
    $allNotifications = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Type</th><th>Message</th><th>Created</th></tr>";
    
    foreach ($allNotifications as $n) {
        echo "<tr>";
        echo "<td>{$n['notification_id']}</td>";
        echo "<td><strong>{$n['type']}</strong></td>";
        echo "<td>{$n['message']}</td>";
        echo "<td>{$n['created_at']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 20px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>
