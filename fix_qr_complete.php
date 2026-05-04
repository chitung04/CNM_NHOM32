<?php
/**
 * Sửa hoàn toàn hệ thống QR code
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/qrcode.php';

echo "<h2>🔧 Sửa hoàn toàn hệ thống QR Code</h2>";

try {
    $db = Database::getInstance();
    
    // 1. Kiểm tra cấu hình
    echo "<h3>📋 Kiểm tra cấu hình</h3>";
    echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
    echo "<p><strong>QRCODE_PATH:</strong> " . QRCODE_PATH . "</p>";
    
    // Kiểm tra thư mục QR codes
    if (!file_exists(QRCODE_PATH)) {
        mkdir(QRCODE_PATH, 0777, true);
        echo "<p style='color: green;'>✅ Đã tạo thư mục QR codes</p>";
    } else {
        echo "<p style='color: green;'>✅ Thư mục QR codes đã tồn tại</p>";
    }
    
    // 2. Lấy tất cả batches có QR code
    $sql = "SELECT b.batch_id, b.qr_code, b.batch_number, m.medicine_name 
            FROM batches b 
            LEFT JOIN medicines m ON b.medicine_id = m.medicine_id 
            WHERE b.qr_code IS NOT NULL 
            ORDER BY b.batch_id DESC LIMIT 10";
    $stmt = $db->query($sql);
    $batches = $stmt->fetchAll();
    
    echo "<h3>🗄️ Tìm thấy " . count($batches) . " lô thuốc có QR code (hiển thị 10 mới nhất)</h3>";
    
    // 3. Test một QR code
    if (!empty($batches)) {
        $testBatch = $batches[0];
        $testQR = $testBatch['qr_code'];
        $correctURL = BASE_URL . '/medicine_info.php?qr=' . urlencode($testQR);
        
        echo "<h3>🧪 Test QR Code: {$testBatch['medicine_name']}</h3>";
        echo "<p><strong>QR Code:</strong> {$testQR}</p>";
        echo "<p><strong>URL:</strong> <a href='{$correctURL}' target='_blank'>{$correctURL}</a></p>";
        
        // Kiểm tra file QR có tồn tại không
        $qrFile = QRCODE_PATH . '/' . $testQR . '.png';
        if (file_exists($qrFile)) {
            echo "<p style='color: green;'>✅ File QR code tồn tại</p>";
            echo "<img src='assets/qrcodes/{$testQR}.png' style='max-width: 150px; border: 2px solid #28a745;'>";
        } else {
            echo "<p style='color: red;'>❌ File QR code không tồn tại</p>";
        }
    }
    
    // 4. Nút sửa tất cả
    $action = $_GET['action'] ?? '';
    
    if ($action === 'fix_all') {
        echo "<h3>🔄 Đang sửa tất cả QR codes...</h3>";
        
        $sql = "SELECT b.batch_id, b.qr_code, b.medicine_id 
                FROM batches b 
                WHERE b.qr_code IS NOT NULL";
        $stmt = $db->query($sql);
        $allBatches = $stmt->fetchAll();
        
        $success = 0;
        $failed = 0;
        
        echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;'>";
        
        foreach ($allBatches as $batch) {
            $qrCode = $batch['qr_code'];
            $url = BASE_URL . '/medicine_info.php?qr=' . urlencode($qrCode);
            
            // Tạo QR code mới
            $result = generateQRCode($url, $qrCode);
            
            if ($result) {
                echo "<p style='color: green;'>✅ {$qrCode}</p>";
                $success++;
            } else {
                echo "<p style='color: red;'>❌ {$qrCode}</p>";
                $failed++;
            }
            
            if (ob_get_level()) ob_flush();
            flush();
            usleep(100000); // 0.1 giây
        }
        
        echo "</div>";
        echo "<hr>";
        echo "<p style='color: green; font-weight: bold;'>✅ Hoàn thành! Đã sửa {$success} QR codes, thất bại {$failed}</p>";
        
    } else {
        echo "<p><a href='?action=fix_all' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Sửa tất cả QR codes</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php?page=batches'>🏠 Về quản lý lô thuốc</a></p>";
?>