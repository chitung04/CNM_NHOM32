<?php
/**
 * Tạo thông báo cho thuốc sắp hết hàng và sắp hết hạn
 */

session_start();

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'models/Notification.php';

// Set pharmacy_id cho session (giả lập đăng nhập)
if (!isset($_SESSION['pharmacy_id'])) {
    $_SESSION['pharmacy_id'] = 1; // Pharmacy ID mặc định
}

$db = Database::getInstance();
$notificationModel = new Notification();

echo "<h2>🔔 Tạo thông báo tự động</h2>";
echo "<hr>";

try {
    // 1. Xóa thông báo cũ chưa đọc
    echo "<h3>🗑️ Xóa thông báo cũ...</h3>";
    $db->query("DELETE FROM notifications WHERE pharmacy_id = 1 AND is_read = 0");
    echo "<p style='color: green;'>✅ Đã xóa thông báo cũ</p>";
    
    echo "<hr>";
    
    // 2. Tạo thông báo thuốc sắp hết hàng
    echo "<h3>📦 Kiểm tra thuốc sắp hết hàng...</h3>";
    
    $stmt = $db->query("
        SELECT m.medicine_id, m.medicine_name, u.unit_name,
        COALESCE(SUM(b.quantity), 0) as total_stock
        FROM medicines m
        LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
        LEFT JOIN units u ON m.unit_id = u.unit_id
        WHERE m.pharmacy_id = 1
        GROUP BY m.medicine_id, m.medicine_name, u.unit_name
        HAVING total_stock < 50
        ORDER BY total_stock ASC
    ");
    
    $lowStockMedicines = $stmt->fetchAll();
    
    if (count($lowStockMedicines) > 0) {
        foreach ($lowStockMedicines as $medicine) {
            $message = "Thuốc {$medicine['medicine_name']} sắp hết hàng (còn {$medicine['total_stock']} {$medicine['unit_name']})";
            
            $sql = "INSERT INTO notifications (pharmacy_id, type, message, reference_id, is_read, created_at) 
                    VALUES (1, 'low_stock', ?, ?, 0, NOW())";
            $db->execute($sql, [$message, $medicine['medicine_id']]);
            
            echo "<p style='color: orange;'>⚠️ Tạo thông báo: $message</p>";
        }
        echo "<p style='color: green;'><strong>✅ Đã tạo " . count($lowStockMedicines) . " thông báo hết hàng</strong></p>";
    } else {
        echo "<p style='color: green;'>✅ Không có thuốc nào sắp hết hàng</p>";
    }
    
    echo "<hr>";
    
    // 3. Tạo thông báo thuốc sắp hết hạn
    echo "<h3>⏰ Kiểm tra thuốc sắp hết hạn...</h3>";
    
    $stmt = $db->query("
        SELECT b.batch_id, b.expiry_date, m.medicine_name, b.batch_number,
        DATEDIFF(b.expiry_date, CURDATE()) as days_left
        FROM batches b
        JOIN medicines m ON b.medicine_id = m.medicine_id
        WHERE b.pharmacy_id = 1
        AND b.status = 'active' 
        AND DATEDIFF(b.expiry_date, CURDATE()) <= 90
        AND DATEDIFF(b.expiry_date, CURDATE()) > 0
        ORDER BY days_left ASC
    ");
    
    $expiringBatches = $stmt->fetchAll();
    
    if (count($expiringBatches) > 0) {
        foreach ($expiringBatches as $batch) {
            $expiryDate = date('d/m/Y', strtotime($batch['expiry_date']));
            $message = "Lô thuốc {$batch['medicine_name']} (Lô: {$batch['batch_number']}) sắp hết hạn (hết hạn: {$expiryDate})";
            
            $sql = "INSERT INTO notifications (pharmacy_id, type, message, reference_id, is_read, created_at) 
                    VALUES (1, 'expiry_warning', ?, ?, 0, NOW())";
            $db->execute($sql, [$message, $batch['batch_id']]);
            
            echo "<p style='color: orange;'>⏰ Tạo thông báo: $message</p>";
        }
        echo "<p style='color: green;'><strong>✅ Đã tạo " . count($expiringBatches) . " thông báo hết hạn</strong></p>";
    } else {
        echo "<p style='color: green;'>✅ Không có thuốc nào sắp hết hạn</p>";
    }
    
    echo "<hr>";
    
    // 4. Hiển thị kết quả
    $stmt = $db->query("SELECT COUNT(*) as count FROM notifications WHERE pharmacy_id = 1 AND is_read = 0");
    $totalNotifications = $stmt->fetch()['count'];
    
    echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>🎉 HOÀN TẤT!</h3>";
    echo "<p style='font-size: 18px;'>Đã tạo <strong>$totalNotifications</strong> thông báo mới.</p>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; border: 2px solid #17a2b8; padding: 20px;'>";
    echo "<h3>🎯 Bước tiếp theo:</h3>";
    echo "<ol>";
    echo "<li>Refresh trang web</li>";
    echo "<li>Kiểm tra thanh thông báo màu cam ở trên</li>";
    echo "<li>Kiểm tra chuông thông báo trên navbar</li>";
    echo "</ol>";
    echo "<p><a href='index.php?page=dashboard' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>🏠 Về trang chủ</a></p>";
    echo "<p><a href='check_notifications.php' style='display: inline-block; background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>🔍 Xem chi tiết thông báo</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 20px;'>";
    echo "<h3 style='color: #721c24;'>❌ LỖI:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}
</style>
