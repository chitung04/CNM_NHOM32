<?php
/**
 * Tool tái tạo tất cả QR codes với URL đúng
 * Sửa lỗi QR code không quét được
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Database.php';
require_once '../helpers/qrcode.php';

echo "<h2>🔧 Tái tạo QR Codes với URL đúng</h2>\n";
echo "<p>Đang sửa lỗi QR code không quét được...</p>\n";

try {
    $db = Database::getInstance();
    
    // 1. Kiểm tra BASE_URL hiện tại
    echo "<h3>📋 Thông tin cấu hình:</h3>\n";
    echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>\n";
    echo "<p><strong>QRCODE_PATH:</strong> " . QRCODE_PATH . "</p>\n";
    
    // 2. Lấy tất cả batches có QR code
    $sql = "SELECT batch_id, medicine_id, qr_code, batch_number FROM batches WHERE qr_code IS NOT NULL ORDER BY batch_id";
    $stmt = $db->query($sql);
    $batches = $stmt->fetchAll();
    
    echo "<p><strong>Tổng số QR codes cần tái tạo:</strong> " . count($batches) . "</p>\n";
    
    $success = 0;
    $failed = 0;
    
    echo "<h3>🔄 Đang tái tạo QR codes...</h3>\n";
    echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;'>\n";
    
    foreach ($batches as $batch) {
        try {
            // Tạo URL đúng cho QR code
            $qrData = BASE_URL . '/medicine_info.php?qr=' . urlencode($batch['qr_code']);
            
            echo "<p>🔄 Batch #{$batch['batch_id']} ({$batch['batch_number']}) - QR: {$batch['qr_code']}</p>\n";
            echo "<p style='margin-left: 20px; color: #666;'>URL: {$qrData}</p>\n";
            
            // Tạo QR code mới
            $result = generateQRCode($qrData, $batch['qr_code']);
            
            if ($result) {
                echo "<p style='margin-left: 20px; color: green;'>✅ Thành công</p>\n";
                $success++;
            } else {
                echo "<p style='margin-left: 20px; color: red;'>❌ Thất bại</p>\n";
                $failed++;
            }
            
            // Flush output để hiển thị real-time
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
            
        } catch (Exception $e) {
            echo "<p style='margin-left: 20px; color: red;'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>\n";
            $failed++;
        }
    }
    
    echo "</div>\n";
    
    // 3. Tái tạo QR codes cho medicines
    echo "<h3>🔄 Tái tạo QR codes cho thuốc...</h3>\n";
    
    $sql = "SELECT medicine_id, qr_code, medicine_name FROM medicines WHERE qr_code IS NOT NULL ORDER BY medicine_id";
    $stmt = $db->query($sql);
    $medicines = $stmt->fetchAll();
    
    echo "<p><strong>Tổng số QR codes thuốc:</strong> " . count($medicines) . "</p>\n";
    echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;'>\n";
    
    foreach ($medicines as $medicine) {
        try {
            // Tạo URL đúng cho QR code thuốc (có thể dẫn đến trang tìm kiếm)
            $qrData = BASE_URL . '/medicine_info.php?qr=' . urlencode($medicine['qr_code']);
            
            echo "<p>🔄 Medicine #{$medicine['medicine_id']} ({$medicine['medicine_name']}) - QR: {$medicine['qr_code']}</p>\n";
            
            $result = generateQRCode($qrData, $medicine['qr_code']);
            
            if ($result) {
                echo "<p style='margin-left: 20px; color: green;'>✅ Thành công</p>\n";
                $success++;
            } else {
                echo "<p style='margin-left: 20px; color: red;'>❌ Thất bại</p>\n";
                $failed++;
            }
            
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
            
        } catch (Exception $e) {
            echo "<p style='margin-left: 20px; color: red;'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>\n";
            $failed++;
        }
    }
    
    echo "</div>\n";
    
    // 4. Kết quả
    echo "<h3>📊 Kết quả:</h3>\n";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px;'>\n";
    echo "<p><strong>✅ Thành công:</strong> {$success} QR codes</p>\n";
    echo "<p><strong>❌ Thất bại:</strong> {$failed} QR codes</p>\n";
    echo "<p><strong>📁 Thư mục QR:</strong> " . QRCODE_PATH . "</p>\n";
    echo "</div>\n";
    
    // 5. Test một QR code
    echo "<h3>🧪 Test QR Code:</h3>\n";
    if (!empty($batches)) {
        $testBatch = $batches[0];
        $testUrl = BASE_URL . '/medicine_info.php?qr=' . urlencode($testBatch['qr_code']);
        
        echo "<p><strong>Test URL:</strong> <a href='{$testUrl}' target='_blank'>{$testUrl}</a></p>\n";
        echo "<p><strong>QR Code file:</strong> " . QRCODE_PATH . '/' . $testBatch['qr_code'] . '.png</p>\n";
        
        if (file_exists(QRCODE_PATH . '/' . $testBatch['qr_code'] . '.png')) {
            echo "<p style='color: green;'>✅ File QR code tồn tại</p>\n";
            echo "<img src='" . BASE_URL . "/assets/qrcodes/{$testBatch['qr_code']}.png' alt='Test QR' style='border: 1px solid #ddd;'>\n";
        } else {
            echo "<p style='color: red;'>❌ File QR code không tồn tại</p>\n";
        }
    }
    
    echo "<h3>✅ Hoàn thành!</h3>\n";
    echo "<p>Bây giờ hãy thử quét lại QR code để kiểm tra.</p>\n";
    
} catch (Exception $e) {
    echo "<div style='background: #ffe6e6; padding: 15px; border-radius: 5px; color: red;'>\n";
    echo "<h3>❌ Lỗi:</h3>\n";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</div>\n";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

h2, h3 {
    color: #333;
}

p {
    margin: 5px 0;
}

a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>