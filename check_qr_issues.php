<?php
/**
 * Kiểm tra tất cả vấn đề với hệ thống QR code
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔍 Kiểm tra vấn đề hệ thống QR Code</h2>";

try {
    $db = Database::getInstance();
    
    echo "<h3>📋 1. Kiểm tra cấu hình</h3>";
    echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
    echo "<p><strong>QRCODE_PATH:</strong> " . QRCODE_PATH . "</p>";
    
    // Kiểm tra thư mục QR codes
    if (!file_exists(QRCODE_PATH)) {
        echo "<p style='color: red;'>❌ Thư mục QR codes không tồn tại: " . QRCODE_PATH . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Thư mục QR codes tồn tại</p>";
        
        // Đếm số file QR
        $qrFiles = glob(QRCODE_PATH . '/*.png');
        echo "<p>📁 Có " . count($qrFiles) . " file QR code trong thư mục</p>";
    }
    
    echo "<h3>🗄️ 2. Kiểm tra database</h3>";
    
    // Đếm tổng số batches
    $sql = "SELECT COUNT(*) as total FROM batches";
    $stmt = $db->query($sql);
    $totalBatches = $stmt->fetch()['total'];
    echo "<p>📦 Tổng số lô thuốc: <strong>{$totalBatches}</strong></p>";
    
    // Đếm batches có QR code
    $sql = "SELECT COUNT(*) as total FROM batches WHERE qr_code IS NOT NULL AND qr_code != ''";
    $stmt = $db->query($sql);
    $batchesWithQR = $stmt->fetch()['total'];
    echo "<p>🏷️ Lô thuốc có QR code: <strong>{$batchesWithQR}</strong></p>";
    
    // Đếm batches không có QR code
    $batchesWithoutQR = $totalBatches - $batchesWithQR;
    echo "<p>❓ Lô thuốc không có QR code: <strong>{$batchesWithoutQR}</strong></p>";
    
    echo "<h3>🔍 3. Kiểm tra QR codes chi tiết</h3>";
    
    // Lấy 5 QR code đầu tiên để test
    $sql = "SELECT b.batch_id, b.qr_code, b.batch_number, m.medicine_name 
            FROM batches b 
            LEFT JOIN medicines m ON b.medicine_id = m.medicine_id 
            WHERE b.qr_code IS NOT NULL 
            ORDER BY b.batch_id DESC 
            LIMIT 5";
    $stmt = $db->query($sql);
    $testBatches = $stmt->fetchAll();
    
    if (empty($testBatches)) {
        echo "<p style='color: red;'>❌ Không có QR code nào để kiểm tra</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th style='padding: 8px;'>Batch ID</th>";
        echo "<th style='padding: 8px;'>QR Code</th>";
        echo "<th style='padding: 8px;'>Tên thuốc</th>";
        echo "<th style='padding: 8px;'>File tồn tại</th>";
        echo "<th style='padding: 8px;'>URL</th>";
        echo "<th style='padding: 8px;'>Test</th>";
        echo "</tr>";
        
        foreach ($testBatches as $batch) {
            $qrCode = $batch['qr_code'];
            $qrFilePath = QRCODE_PATH . '/' . $qrCode . '.png';
            $fileExists = file_exists($qrFilePath);
            $qrUrl = BASE_URL . '/medicine_info.php?qr=' . urlencode($qrCode);
            
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$batch['batch_id']}</td>";
            echo "<td style='padding: 8px; font-family: monospace;'>{$qrCode}</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($batch['medicine_name']) . "</td>";
            echo "<td style='padding: 8px;'>" . ($fileExists ? "✅ Có" : "❌ Không") . "</td>";
            echo "<td style='padding: 8px; font-size: 12px;'><a href='{$qrUrl}' target='_blank'>Test URL</a></td>";
            echo "<td style='padding: 8px;'>";
            if ($fileExists) {
                echo "<img src='assets/qrcodes/{$qrCode}.png' style='width: 50px; height: 50px;'>";
            } else {
                echo "❌";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>🧪 4. Test trang medicine_info.php</h3>";
    
    if (!empty($testBatches)) {
        $firstBatch = $testBatches[0];
        $testQR = $firstBatch['qr_code'];
        $testUrl = BASE_URL . '/medicine_info.php?qr=' . urlencode($testQR);
        
        echo "<p><strong>Test URL:</strong> <a href='{$testUrl}' target='_blank'>{$testUrl}</a></p>";
        
        // Kiểm tra file medicine_info.php có tồn tại không
        if (file_exists('medicine_info.php')) {
            echo "<p style='color: green;'>✅ File medicine_info.php tồn tại</p>";
        } else {
            echo "<p style='color: red;'>❌ File medicine_info.php không tồn tại</p>";
        }
    }
    
    echo "<h3>🔧 5. Các vấn đề phát hiện</h3>";
    
    $issues = [];
    
    // Kiểm tra các vấn đề
    if (!file_exists(QRCODE_PATH)) {
        $issues[] = "Thư mục QR codes không tồn tại";
    }
    
    if ($batchesWithoutQR > 0) {
        $issues[] = "{$batchesWithoutQR} lô thuốc chưa có QR code";
    }
    
    if (!file_exists('medicine_info.php')) {
        $issues[] = "File medicine_info.php không tồn tại";
    }
    
    // Kiểm tra QR files bị thiếu
    if (!empty($testBatches)) {
        $missingFiles = 0;
        foreach ($testBatches as $batch) {
            $qrFilePath = QRCODE_PATH . '/' . $batch['qr_code'] . '.png';
            if (!file_exists($qrFilePath)) {
                $missingFiles++;
            }
        }
        if ($missingFiles > 0) {
            $issues[] = "Có QR code trong database nhưng file ảnh không tồn tại";
        }
    }
    
    if (empty($issues)) {
        echo "<p style='color: green; font-weight: bold;'>✅ Không phát hiện vấn đề nghiêm trọng!</p>";
    } else {
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li style='color: red;'>❌ {$issue}</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>🛠️ 6. Hành động khuyến nghị</h3>";
    echo "<ol>";
    echo "<li><a href='tools/fix_qr_system.php'>Chạy tool sửa QR system</a></li>";
    echo "<li><a href='tools/regenerate_all_qrcodes.php'>Tạo lại tất cả QR codes</a></li>";
    echo "<li>Kiểm tra cấu hình BASE_URL trong file .env</li>";
    echo "<li>Đảm bảo file medicine_info.php hoạt động đúng</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>🏠 Về trang chủ</a></p>";
?>