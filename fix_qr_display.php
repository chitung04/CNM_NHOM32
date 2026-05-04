<?php
/**
 * Fix QR Code Display Issues
 * Sửa lỗi hiển thị QR codes trong trang tra cứu thuốc
 */

require_once 'config/database.php';
require_once 'helpers/qrcode.php';

echo "<h2>🔧 Sửa lỗi hiển thị QR Codes</h2>";

try {
    $db = Database::getInstance();
    
    // Kiểm tra thư mục QR codes
    if (!file_exists('assets/qrcodes')) {
        mkdir('assets/qrcodes', 0777, true);
        echo "<p>✅ Đã tạo thư mục assets/qrcodes</p>";
    }
    
    // Lấy tất cả medicines
    echo "<h4>📊 Kiểm tra QR codes hiện tại:</h4>";
    
    $sql = "SELECT m.medicine_id, m.medicine_name, m.qr_code as medicine_qr,
                   (SELECT b.qr_code FROM batches b WHERE b.medicine_id = m.medicine_id AND b.qr_code IS NOT NULL LIMIT 1) as batch_qr
            FROM medicines m 
            ORDER BY m.medicine_id ASC";
    $stmt = $db->query($sql);
    $medicines = $stmt->fetchAll();
    
    $needsQR = 0;
    $hasQR = 0;
    $fixedQR = 0;
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    
    foreach ($medicines as $medicine) {
        $medicineId = $medicine['medicine_id'];
        $medicineName = $medicine['medicine_name'];
        $currentQR = $medicine['medicine_qr'] ?: $medicine['batch_qr'];
        
        echo "<div style='margin: 10px 0; padding: 10px; background: white; border-radius: 5px;'>";
        echo "<strong>ID {$medicineId}: {$medicineName}</strong><br>";
        
        if ($currentQR) {
            $qrFile = "assets/qrcodes/{$currentQR}.png";
            if (file_exists($qrFile)) {
                echo "<span style='color: green;'>✅ QR: {$currentQR} (File tồn tại)</span>";
                $hasQR++;
            } else {
                echo "<span style='color: orange;'>⚠️ QR: {$currentQR} (File không tồn tại - sẽ tạo lại)</span>";
                
                // Tạo lại QR code
                $qrUrl = "http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id={$medicineId}&qr={$currentQR}";
                
                if (generateQRCode($qrUrl, $currentQR)) {
                    echo "<br><span style='color: green; margin-left: 20px;'>✅ Đã tạo lại QR code</span>";
                    $fixedQR++;
                } else {
                    echo "<br><span style='color: red; margin-left: 20px;'>❌ Lỗi tạo QR code</span>";
                }
            }
        } else {
            echo "<span style='color: red;'>❌ Chưa có QR code - sẽ tạo mới</span>";
            
            // Tạo QR code mới
            $newQR = 'MED_' . time() . '_' . $medicineId;
            $qrUrl = "http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id={$medicineId}&qr={$newQR}";
            
            if (generateQRCode($qrUrl, $newQR)) {
                // Cập nhật QR code vào database
                $updateSql = "UPDATE medicines SET qr_code = ? WHERE medicine_id = ?";
                $updateStmt = $db->prepare($updateSql);
                
                if ($updateStmt->execute([$newQR, $medicineId])) {
                    echo "<br><span style='color: green; margin-left: 20px;'>✅ Đã tạo QR: {$newQR}</span>";
                    $fixedQR++;
                } else {
                    echo "<br><span style='color: red; margin-left: 20px;'>❌ Lỗi cập nhật database</span>";
                }
            } else {
                echo "<br><span style='color: red; margin-left: 20px;'>❌ Lỗi tạo QR code</span>";
            }
            
            $needsQR++;
            
            // Delay để tránh spam API
            usleep(200000); // 0.2 giây
        }
        
        echo "</div>";
    }
    
    echo "</div>";
    
    // Thống kê
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📈 Kết quả:</h4>";
    echo "<ul>";
    echo "<li><strong>Tổng medicines:</strong> " . count($medicines) . "</li>";
    echo "<li><strong>Đã có QR:</strong> <span style='color: green;'>{$hasQR}</span></li>";
    echo "<li><strong>Cần tạo QR:</strong> <span style='color: orange;'>{$needsQR}</span></li>";
    echo "<li><strong>Đã sửa/tạo:</strong> <span style='color: blue;'>{$fixedQR}</span></li>";
    echo "</ul>";
    echo "</div>";
    
    // Test một vài QR codes
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🧪 Test QR Codes:</h4>";
    
    $testSql = "SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NOT NULL LIMIT 5";
    $testStmt = $db->query($testSql);
    $testMedicines = $testStmt->fetchAll();
    
    foreach ($testMedicines as $med) {
        $testUrl = "http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id={$med['medicine_id']}&qr={$med['qr_code']}";
        $qrImagePath = "assets/qrcodes/{$med['qr_code']}.png";
        
        echo "<div style='margin: 10px 0; padding: 10px; background: white; border-radius: 5px;'>";
        echo "<strong>{$med['medicine_name']}</strong> (QR: {$med['qr_code']})<br>";
        echo "<a href='{$testUrl}' target='_blank' style='color: #007bff;'>{$testUrl}</a><br>";
        
        if (file_exists($qrImagePath)) {
            echo "<img src='{$qrImagePath}' alt='QR Code' style='width: 100px; height: 100px; margin-top: 10px; border: 1px solid #ddd;'>";
        } else {
            echo "<span style='color: red;'>❌ QR image không tồn tại</span>";
        }
        
        echo "</div>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='index.php?page=medicines' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Kiểm tra tra cứu thuốc</a>";
echo "</div>";
?>