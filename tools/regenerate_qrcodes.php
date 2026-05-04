<?php
// Tool để tạo lại QR code cho tất cả lô thuốc
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../helpers/qrcode.php';
require_once '../models/Database.php';

echo "<h2>Tạo lại QR Code cho tất cả lô thuốc</h2>";

try {
    // Tạo lại QR code cho tất cả lô thuốc
    $result = regenerateAllQRCodes();
    
    echo "<div style='color: green;'>";
    echo "✅ Thành công: {$result['success']} QR codes<br>";
    echo "</div>";
    
    if ($result['failed'] > 0) {
        echo "<div style='color: red;'>";
        echo "❌ Thất bại: {$result['failed']} QR codes<br>";
        echo "</div>";
    }
    
    echo "<br><strong>Hoàn thành!</strong><br>";
    echo "<a href='../index.php?page=batches'>← Quay lại quản lý lô thuốc</a>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "Lỗi: " . $e->getMessage();
    echo "</div>";
}

// Hiển thị một số QR code mẫu
echo "<br><br><h3>QR Code mẫu:</h3>";

$db = Database::getInstance();
$sql = "SELECT b.batch_id, b.qr_code, b.medicine_id, m.medicine_name 
        FROM batches b 
        LEFT JOIN medicines m ON b.medicine_id = m.medicine_id 
        WHERE b.qr_code IS NOT NULL 
        LIMIT 5";
$stmt = $db->query($sql);
$samples = $stmt->fetchAll();

foreach ($samples as $sample) {
    echo "<div style='display: inline-block; margin: 10px; text-align: center;'>";
    echo "<img src='../assets/qrcodes/{$sample['qr_code']}.png' style='width: 150px; height: 150px; border: 1px solid #ccc;'><br>";
    echo "<small>Lô #{$sample['batch_id']}<br>{$sample['medicine_name']}</small>";
    echo "</div>";
}
?>