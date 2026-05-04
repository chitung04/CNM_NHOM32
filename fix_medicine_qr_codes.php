<?php
/**
 * Sửa lại tất cả QR codes cho medicines
 */

require_once 'config/database.php';
require_once 'helpers/qrcode.php';

echo "<h2>🔧 Sửa lại QR Codes cho Medicines</h2>";
echo "<p>Đang sửa lại tất cả QR codes bị lỗi...</p>";

try {
    $db = Database::getInstance();
    
    // Lấy tất cả medicines có QR code
    $sql = "SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NOT NULL AND qr_code != ''";
    $stmt = $db->query($sql);
    $medicines = $stmt->fetchAll();
    
    $fixed = 0;
    $created = 0;
    $errors = 0;
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🔧 Tiến trình sửa QR:</h4>";
    
    foreach ($medicines as $medicine) {
        $medicineId = $medicine['medicine_id'];
        $medicineName = $medicine['medicine_name'];
        $oldQrCode = $medicine['qr_code'];
        
        try {
            // Tạo QR code mới với format đúng
            $newQrCode = 'MED_' . time() . '_' . $medicineId;
            
            // Tạo URL cho QR code
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI']);
            
            $qrUrl = $baseUrl . '/medicine_info.php?medicine_id=' . $medicineId . '&qr=' . $newQrCode;
            
            // Tạo QR code image mới
            $qrImagePath = "assets/qrcodes/{$newQrCode}.png";
            
            if (generateQRCode($qrUrl, $newQrCode)) {
                // Cập nhật QR code mới vào database
                $updateSql = "UPDATE medicines SET qr_code = ? WHERE medicine_id = ?";
                $updateStmt = $db->prepare($updateSql);
                
                if ($updateStmt->execute([$newQrCode, $medicineId])) {
                    // Xóa QR code cũ nếu khác
                    if ($oldQrCode !== $newQrCode) {
                        $oldImagePath = "assets/qrcodes/{$oldQrCode}.png";
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $fixed++;
                    echo "<div style='color: green; margin: 5px 0;'>✅ Sửa QR: {$medicineName} (ID: {$medicineId})</div>";
                    echo "<div style='color: blue; margin: 5px 0; margin-left: 20px;'>🔄 {$oldQrCode} → {$newQrCode}</div>";
                } else {
                    $errors++;
                    echo "<div style='color: red; margin: 5px 0;'>❌ Lỗi cập nhật DB: {$medicineName}</div>";
                }
            } else {
                $errors++;
                echo "<div style='color: red; margin: 5px 0;'>❌ Lỗi tạo QR image: {$medicineName}</div>";
            }
            
            // Delay nhỏ để tránh spam API
            usleep(100000); // 0.1 giây
            
        } catch (Exception $e) {
            $errors++;
            echo "<div style='color: red; margin: 5px 0;'>❌ Lỗi xử lý {$medicineName}: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "</div>";
    
    // Thống kê
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📈 Kết quả:</h4>";
    echo "<ul>";
    echo "<li><strong>Medicines cần sửa:</strong> " . count($medicines) . "</li>";
    echo "<li><strong>QR codes đã sửa:</strong> <span style='color: green;'>{$fixed}</span></li>";
    echo "<li><strong>Lỗi:</strong> <span style='color: red;'>{$errors}</span></li>";
    echo "</ul>";
    echo "</div>";
    
    // Test links
    if ($fixed > 0) {
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h4>🧪 Test QR Codes đã sửa:</h4>";
        
        // Lấy 5 medicines vừa sửa QR
        $testSql = "SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code LIKE 'MED_%' ORDER BY medicine_id DESC LIMIT 5";
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
    
    // Hướng dẫn
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📋 Hướng dẫn tiếp theo:</h4>";
    echo "<ol>";
    echo "<li>Vào trang <strong>Tra cứu thuốc</strong> để kiểm tra QR codes</li>";
    echo "<li>Click vào icon QR để test QR code</li>";
    echo "<li>Quét QR bằng điện thoại để xem thông tin thuốc</li>";
    echo "<li>QR codes bây giờ sẽ có format: <code>MED_timestamp_medicineID</code></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Lỗi hệ thống:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='index.php?page=medicines' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Kiểm tra tra cứu thuốc</a>";
echo "</div>";
?>