<?php
/**
 * Kiểm tra thông báo trong database
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<h2>🔔 Kiểm tra thông báo</h2>";
echo "<hr>";

// Lấy tất cả thông báo
$stmt = $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
$notifications = $stmt->fetchAll();

echo "<h3>📊 Tổng số thông báo: " . count($notifications) . "</h3>";

if (count($notifications) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Pharmacy ID</th><th>Type</th><th>Message</th><th>is_read</th><th>Created</th></tr>";
    
    foreach ($notifications as $n) {
        $bgColor = $n['is_read'] ? '#f9f9f9' : '#ffffcc';
        echo "<tr style='background: $bgColor;'>";
        echo "<td>{$n['notification_id']}</td>";
        echo "<td>{$n['pharmacy_id']}</td>";
        echo "<td><strong>{$n['type']}</strong></td>";
        echo "<td>" . htmlspecialchars($n['message']) . "</td>";
        echo "<td>" . ($n['is_read'] ? '✓ Đã đọc' : '❌ Chưa đọc') . "</td>";
        echo "<td>{$n['created_at']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ Không có thông báo nào trong database</p>";
}

echo "<hr>";

// Kiểm tra thuốc sắp hết hàng
echo "<h3>📦 Kiểm tra thuốc sắp hết hàng (< 50)</h3>";

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
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Tên thuốc</th><th>Tồn kho</th><th>Đơn vị</th></tr>";
    
    foreach ($lowStockMedicines as $med) {
        $color = $med['total_stock'] == 0 ? 'red' : ($med['total_stock'] < 20 ? 'orange' : 'black');
        echo "<tr>";
        echo "<td>{$med['medicine_id']}</td>";
        echo "<td><strong>{$med['medicine_name']}</strong></td>";
        echo "<td style='color: $color; font-weight: bold;'>{$med['total_stock']}</td>";
        echo "<td>{$med['unit_name']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ Không có thuốc nào sắp hết hàng</p>";
}

echo "<hr>";

// Kiểm tra thuốc sắp hết hạn
echo "<h3>⏰ Kiểm tra thuốc sắp hết hạn (< 90 ngày)</h3>";

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
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Batch ID</th><th>Tên thuốc</th><th>Số lô</th><th>Hạn sử dụng</th><th>Còn lại</th></tr>";
    
    foreach ($expiringBatches as $batch) {
        $color = $batch['days_left'] < 30 ? 'red' : ($batch['days_left'] < 60 ? 'orange' : 'black');
        $expiryDate = date('d/m/Y', strtotime($batch['expiry_date']));
        
        echo "<tr>";
        echo "<td>{$batch['batch_id']}</td>";
        echo "<td><strong>{$batch['medicine_name']}</strong></td>";
        echo "<td>{$batch['batch_number']}</td>";
        echo "<td>$expiryDate</td>";
        echo "<td style='color: $color; font-weight: bold;'>{$batch['days_left']} ngày</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ Không có thuốc nào sắp hết hạn</p>";
}

echo "<hr>";

// Nút tạo thông báo
echo "<div style='background: #e8f4f8; border: 2px solid #0066cc; padding: 20px;'>";
echo "<h3>🔧 Tạo thông báo mới</h3>";
echo "<p>Click nút dưới để tạo thông báo cho thuốc sắp hết hàng và sắp hết hạn:</p>";
echo "<p><a href='generate_notifications.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>🔔 Tạo thông báo ngay</a></p>";
echo "</div>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}
table {
    margin: 20px 0;
}
th {
    text-align: left;
}
</style>
