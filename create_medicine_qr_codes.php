<?php
/**
 * Tạo QR codes cho tất cả medicines chưa có QR
 */

require_once 'config/database.php';
require_once 'helpers/qrcode.php';

echo "<h2>🔄 Tạo QR Codes cho Medicines</h2>";
echo "<p>Đang tạo QR codes cho tất cả medicines chưa có QR code...</p>";

try {
    $db = Database::getInstance();
    
    // Lấy tất cả medicines chưa có QR code
    $sql = "SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NULL OR qr_code = ''";
    $stmt = $db->query($sql);
    $medicines = $stmt->fetchAll();
    
    $created = 0;
    $updated = 0;
    $errors = 0;
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📊 Tiến trình tạo QR:</h4>";
    
    foreach ($medicines as $medicine) {
        $medicineId = $medicine['medicine_id'];
        $medicineName = $medicine['medicine_name'];
        
        try {
            // Tạo QR code unique
            $qrCode = 'MED_' . time() . '_' . $medicineId;
            
            // Tạo URL cho QR code - trỏ đến medicine_info.php
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI']);
            
            $qrUrl = $baseUrl . '/medicine_info.php?medicine_id=' . $medicineId . '&qr=' . $qrCode;
            
            // Tạo QR code image
            $qrImagePath = "assets/qrcodes/{$qrCode}.png";
            
            if (generateQRCode($qrUrl, $qrImagePath)) {
                // Cập nhật QR code vào database
                $updateSql = "UPDATE medicines SET qr_code = ? WHERE medicine_id = ?";
                $updateStmt = $db->prepare($updateSql);
                
                if ($updateStmt->execute([$qrCode, $medicineId])) {
                    $created++;
                    echo "<div style='color: green; margin: 5px 0;'>✅ Tạo QR cho: {$medicineName} (ID: {$medicineId}) - QR: {$qrCode}</div>";
                } else {
                    $errors++;
                    echo "<div style='color: red; margin: 5px 0;'>❌ Lỗi cập nhật DB: {$medicineName}</div>";
                }
            } else {
                $errors++;
                echo "<div style='color: red; margin: 5px 0;'>❌ Lỗi tạo QR image: {$medicineName}</div>";
            }
            
        } catch (Exception $e) {
            $errors++;
            echo "<div style='color: red; margin: 5px 0;'>❌ Lỗi xử lý {$medicineName}: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "</div>";
    
    // Bây giờ cập nhật QR codes từ batches cho medicines chưa có QR
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🔄 Cập nhật QR từ Batches:</h4>";
    
    $sql2 = "UPDATE medicines m 
             SET qr_code = (
                 SELECT b.qr_code 
                 FROM batches b 
                 WHERE b.medicine_id = m.medicine_id 
                 AND b.qr_code IS NOT NULL 
                 LIMIT 1
             )
             WHERE (m.qr_code IS NULL OR m.qr_code = '')
             AND EXISTS (
                 SELECT 1 FROM batches b2 
                 WHERE b2.medicine_id = m.medicine_id 
                 AND b2.qr_code IS NOT NULL
             )";
    
    $stmt2 = $db->prepare($sql2);
    $result2 = $stmt2->execute();
    $updatedFromBatches = $stmt2->rowCount();
    
    echo "<div style='color: blue; margin: 10px 0;'>📋 Cập nhật {$updatedFromBatches} medicines với QR từ batches</div>";
    echo "</div>";
    
    // Thống kê
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📈 Kết quả:</h4>";
    echo "<ul>";
    echo "<li><strong>Medicines cần tạo QR:</strong> " . count($medicines) . "</li>";
    echo "<li><strong>QR codes tạo mới:</strong> <span style='color: green;'>{$created}</span></li>";
    echo "<li><strong>Cập nhật từ batches:</strong> <span style='color: blue;'>{$updatedFromBatches}</span></li>";
    echo "<li><strong>Lỗi:</strong> <span style='color: red;'>{$errors}</span></li>";
    echo "</ul>";
    echo "</div>";
    
    // Test links
    if ($created > 0) {
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h4>🧪 Test QR Codes mới tạo:</h4>";
        
        // Lấy 5 medicines vừa tạo QR
        $testSql = "SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NOT NULL ORDER BY medicine_id DESC LIMIT 5";
        $testStmt = $db->query($testSql);
        $testMedicines = $testStmt->fetchAll();
        
        foreach ($testMedicines as $med) {
            $testUrl = $baseUrl . '/medicine_info.php?medicine_id=' . $med['medicine_id'] . '&qr=' . $med['qr_code'];
            
            echo "<div style='margin: 10px 0; padding: 10px; background: white; border-radius: 5px;'>";
            echo "<strong>{$med['medicine_name']}</strong> (QR: {$med['qr_code']})<br>";
            echo "<a href='{$testUrl}' target='_blank' style='color: #007bff;'>{$testUrl}</a>";
            echo "</div>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Lỗi hệ thống:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='index.php?page=medicines' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Quay lại quản lý thuốc</a>";
echo "</div>";
?>