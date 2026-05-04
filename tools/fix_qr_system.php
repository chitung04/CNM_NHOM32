<?php
/**
 * Tool sửa QR codes trong hệ thống chính
 * Truy cập: tools/fix_qr_system.php
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Database.php';

echo "<h2>🔧 Sửa QR Codes trong hệ thống</h2>";

try {
    $db = Database::getInstance();
    
    // 1. Kiểm tra BASE_URL
    echo "<p><strong>BASE_URL hiện tại:</strong> " . BASE_URL . "</p>";
    
    // 2. Lấy một QR code để test
    $sql = "SELECT qr_code, batch_number FROM batches WHERE qr_code IS NOT NULL LIMIT 1";
    $stmt = $db->query($sql);
    $testBatch = $stmt->fetch();
    
    if ($testBatch) {
        $testQR = $testBatch['qr_code'];
        $correctURL = BASE_URL . '/medicine_info.php?qr=' . urlencode($testQR);
        
        echo "<h3>🧪 Test QR Code</h3>";
        echo "<p><strong>QR Code:</strong> {$testQR}</p>";
        echo "<p><strong>URL đúng:</strong> <a href='{$correctURL}' target='_blank'>{$correctURL}</a></p>";
        
        // 3. Tạo QR code mới
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($correctURL);
        $qrImage = file_get_contents($qrApiUrl);
        
        if ($qrImage) {
            $qrPath = QRCODE_PATH . '/' . $testQR . '.png';
            file_put_contents($qrPath, $qrImage);
            
            echo "<p style='color: green;'>✅ Đã tạo QR code mới cho test</p>";
            echo "<img src='../assets/qrcodes/{$testQR}.png?v=" . time() . "' alt='Test QR' style='max-width: 200px; border: 2px solid #28a745;'>";
        }
        
        // 4. Tạo tất cả QR codes
        $action = $_GET['action'] ?? '';
        if ($action === 'fix_all') {
            echo "<h3>🔄 Đang sửa tất cả QR codes...</h3>";
            
            $sql = "SELECT qr_code FROM batches WHERE qr_code IS NOT NULL";
            $stmt = $db->query($sql);
            $allBatches = $stmt->fetchAll();
            
            $success = 0;
            $failed = 0;
            
            foreach ($allBatches as $batch) {
                $qrCode = $batch['qr_code'];
                $url = BASE_URL . '/medicine_info.php?qr=' . urlencode($qrCode);
                $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($url);
                
                $image = @file_get_contents($apiUrl);
                
                if ($image && strlen($image) > 100) {
                    $path = QRCODE_PATH . '/' . $qrCode . '.png';
                    file_put_contents($path, $image);
                    echo "<p style='color: green;'>✅ {$qrCode}</p>";
                    $success++;
                } else {
                    echo "<p style='color: red;'>❌ {$qrCode}</p>";
                    $failed++;
                }
                
                if (ob_get_level()) ob_flush();
                flush();
                usleep(200000);
            }
            
            echo "<hr>";
            echo "<p style='color: green; font-weight: bold;'>✅ Hoàn thành! Đã sửa {$success} QR codes</p>";
            echo "<p>Bây giờ tất cả QR codes sẽ dẫn đến trang medicine_info.php đã được sửa.</p>";
            
        } else {
            echo "<p><a href='?action=fix_all' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Sửa tất cả QR codes</a></p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Không tìm thấy QR code nào trong database</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='../index.php'>🏠 Về trang chủ</a></p>";
?>