<?php
/**
 * Cập nhật tất cả QR codes để sử dụng hệ thống public access
 * Không cần đăng nhập để xem thông tin thuốc
 */

require_once 'config/database.php';
require_once 'helpers/public_access.php';
require_once 'helpers/qrcode.php';

// Bắt đầu session
session_start();

echo "<h2>🔄 Cập nhật QR Codes sang hệ thống Public Access</h2>";
echo "<p>Đang cập nhật tất cả QR codes để có thể truy cập công khai mà không cần đăng nhập...</p>";

try {
    $db = Database::getInstance();
    
    // Lấy tất cả batches có QR code
    $sql = "SELECT batch_id, qr_code, medicine_id FROM batches WHERE qr_code IS NOT NULL AND qr_code != ''";
    $stmt = $db->query($sql);
    $batches = $stmt->fetchAll();
    
    $updated = 0;
    $created = 0;
    $errors = 0;
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📊 Tiến trình cập nhật:</h4>";
    
    foreach ($batches as $batch) {
        $qrCode = $batch['qr_code'];
        $batchId = $batch['batch_id'];
        
        try {
            // Tạo public access token cho QR code này
            $token = generatePublicAccessToken($qrCode, 86400 * 30); // 30 ngày
            
            // Tạo URL công khai mới
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI']);
            
            $publicUrl = $baseUrl . '/public_medicine_info.php?qr=' . urlencode($qrCode) . '&token=' . $token;
            
            // Tạo QR code mới với URL công khai
            $qrImagePath = "assets/qrcodes/{$qrCode}.png";
            
            if (generateQRCode($publicUrl, $qrImagePath)) {
                $updated++;
                echo "<div style='color: green; margin: 5px 0;'>✅ Cập nhật QR: {$qrCode} (Batch #{$batchId})</div>";
            } else {
                $errors++;
                echo "<div style='color: red; margin: 5px 0;'>❌ Lỗi tạo QR: {$qrCode}</div>";
            }
            
        } catch (Exception $e) {
            $errors++;
            echo "<div style='color: red; margin: 5px 0;'>❌ Lỗi xử lý {$qrCode}: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "</div>";
    
    // Thống kê
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📈 Kết quả:</h4>";
    echo "<ul>";
    echo "<li><strong>Tổng số QR codes:</strong> " . count($batches) . "</li>";
    echo "<li><strong>Cập nhật thành công:</strong> <span style='color: green;'>{$updated}</span></li>";
    echo "<li><strong>Lỗi:</strong> <span style='color: red;'>{$errors}</span></li>";
    echo "</ul>";
    echo "</div>";
    
    // Hướng dẫn sử dụng
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📱 Cách sử dụng:</h4>";
    echo "<ol>";
    echo "<li><strong>Quét QR code bằng điện thoại</strong> - Sẽ mở trực tiếp thông tin thuốc</li>";
    echo "<li><strong>Không cần đăng nhập</strong> - Truy cập công khai trong 30 ngày</li>";
    echo "<li><strong>Chia sẻ link</strong> - Copy link và gửi cho người khác xem</li>";
    echo "<li><strong>Tự động hết hạn</strong> - Token sẽ hết hạn sau 30 ngày để bảo mật</li>";
    echo "</ol>";
    echo "</div>";
    
    // Test links
    if ($updated > 0) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h4>🧪 Test Links (5 QR codes đầu tiên):</h4>";
        
        $testBatches = array_slice($batches, 0, 5);
        foreach ($testBatches as $batch) {
            $qrCode = $batch['qr_code'];
            $token = generatePublicAccessToken($qrCode, 86400 * 30);
            $testUrl = $baseUrl . '/public_medicine_info.php?qr=' . urlencode($qrCode) . '&token=' . $token;
            
            echo "<div style='margin: 10px 0; padding: 10px; background: white; border-radius: 5px;'>";
            echo "<strong>QR: {$qrCode}</strong><br>";
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
echo "<a href='index.php?page=batches' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Quay lại quản lý lô thuốc</a>";
echo "</div>";
?>