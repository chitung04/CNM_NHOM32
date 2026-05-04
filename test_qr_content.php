<?php
/**
 * Script kiểm tra nội dung QR code
 */

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Kiểm tra QR Code</title>";
echo "<meta charset='UTF-8'>";
echo "<style>
body { 
    font-family: 'Segoe UI', sans-serif; 
    padding: 20px; 
    background: #f5f5f5;
} 
.container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.qr-test {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
    border-left: 4px solid #3b82f6;
}
.success { color: #10b981; }
.error { color: #ef4444; }
.warning { color: #f59e0b; }
.info { color: #3b82f6; }
</style>";
echo "</head><body>\n";

echo "<div class='container'>\n";
echo "<h1>🔍 Kiểm tra QR Code</h1>\n";
echo "<hr>\n";

// Thông tin hệ thống
echo "<h2>📊 Thông tin hệ thống</h2>\n";
echo "<div class='qr-test'>\n";
echo "<p><strong>Current URL:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>\n";
echo "<p><strong>Protocol:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP') . "</p>\n";
echo "<p><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>\n";
echo "<p><strong>Server IP:</strong> " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "</p>\n";

// Lấy IP thực
$serverIP = '';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $output = shell_exec('ipconfig');
    if (preg_match('/IPv4 Address[^\d]+([\d\.]+)/', $output, $matches)) {
        $serverIP = $matches[1];
    }
} else {
    $output = shell_exec('hostname -I');
    if ($output) {
        $ips = explode(' ', trim($output));
        $serverIP = $ips[0];
    }
}

echo "<p><strong>Detected IP:</strong> " . ($serverIP ?: 'Unknown') . "</p>\n";
echo "</div>\n";

echo "<hr>\n";

// Kiểm tra file qr.php
echo "<h2>📄 Kiểm tra file qr.php</h2>\n";
if (file_exists('qr.php')) {
    echo "<p class='success'>✓ File qr.php tồn tại</p>\n";
    
    // Test URL
    $testURL = "http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/qr.php?c=TEST";
    echo "<p><strong>Test URL:</strong> <a href='{$testURL}' target='_blank'>{$testURL}</a></p>\n";
} else {
    echo "<p class='error'>✗ File qr.php KHÔNG tồn tại!</p>\n";
}

echo "<hr>\n";

// Kiểm tra QR codes trong database
require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<h2>📦 Kiểm tra QR Code trong Database</h2>\n";

// Lấy 5 medicines
$stmt = $db->query('SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NOT NULL LIMIT 5');
$medicines = $stmt->fetchAll();

echo "<h3>Thuốc (Medicines):</h3>\n";
foreach ($medicines as $med) {
    $qrFile = "assets/qrcodes/{$med['qr_code']}.png";
    $fileExists = file_exists($qrFile);
    
    echo "<div class='qr-test'>\n";
    echo "<p><strong>#{$med['medicine_id']}: {$med['medicine_name']}</strong></p>\n";
    echo "<p>QR Code: <code>{$med['qr_code']}</code></p>\n";
    echo "<p>File: " . ($fileExists ? "<span class='success'>✓ Tồn tại</span>" : "<span class='error'>✗ Không tồn tại</span>") . "</p>\n";
    
    if ($fileExists) {
        // Hiển thị QR code
        echo "<p><img src='{$qrFile}' style='width: 150px; height: 150px; border: 1px solid #ddd;'></p>\n";
        
        // Test URLs
        $oldURL = "http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/medicine_info.php?qr=" . urlencode($med['qr_code']);
        $newURL = "http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/qr.php?c=" . urlencode($med['qr_code']);
        
        echo "<p><strong>URL cũ:</strong> <a href='{$oldURL}' target='_blank'>{$oldURL}</a></p>\n";
        echo "<p><strong>URL mới:</strong> <a href='{$newURL}' target='_blank'>{$newURL}</a></p>\n";
        
        // Đọc file size để ước lượng nội dung
        $fileSize = filesize($qrFile);
        echo "<p><strong>File size:</strong> {$fileSize} bytes</p>\n";
    }
    
    echo "</div>\n";
}

// Lấy 5 batches
$stmt = $db->query('SELECT batch_id, qr_code FROM batches WHERE qr_code IS NOT NULL LIMIT 5');
$batches = $stmt->fetchAll();

echo "<h3>Lô thuốc (Batches):</h3>\n";
foreach ($batches as $batch) {
    $qrFile = "assets/qrcodes/{$batch['qr_code']}.png";
    $fileExists = file_exists($qrFile);
    
    echo "<div class='qr-test'>\n";
    echo "<p><strong>Batch #{$batch['batch_id']}</strong></p>\n";
    echo "<p>QR Code: <code>{$batch['qr_code']}</code></p>\n";
    echo "<p>File: " . ($fileExists ? "<span class='success'>✓ Tồn tại</span>" : "<span class='error'>✗ Không tồn tại</span>") . "</p>\n";
    
    if ($fileExists) {
        echo "<p><img src='{$qrFile}' style='width: 150px; height: 150px; border: 1px solid #ddd;'></p>\n";
        
        $newURL = "http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/qr.php?c=" . urlencode($batch['qr_code']);
        echo "<p><strong>URL mới:</strong> <a href='{$newURL}' target='_blank'>{$newURL}</a></p>\n";
    }
    
    echo "</div>\n";
}

echo "<hr>\n";

// Hướng dẫn
echo "<h2>📝 Hướng dẫn sửa lỗi</h2>\n";
echo "<div style='background: #fef3c7; padding: 20px; border-radius: 10px; border: 2px solid #f59e0b;'>\n";
echo "<h3>Nếu QR code không hoạt động:</h3>\n";
echo "<ol style='font-size: 1.1em;'>\n";
echo "<li><strong>Chạy script tạo lại QR code:</strong><br>\n";
echo "<a href='regenerate_qr_smart.php' target='_blank' style='color: blue;'>http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/regenerate_qr_smart.php</a></li>\n";
echo "<li><strong>Đảm bảo điện thoại và máy tính cùng mạng WiFi</strong></li>\n";
echo "<li><strong>Thử quét QR code ở trên để test</strong></li>\n";
echo "<li><strong>Kiểm tra xem URL có mở được không</strong> (click vào link test)</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<hr>\n";
echo "<p style='text-align: center;'>\n";
echo "<a href='regenerate_qr_smart.php' style='padding: 15px 30px; background: #3b82f6; color: white; text-decoration: none; border-radius: 10px; font-size: 1.1em; display: inline-block; margin: 10px;'>🔄 Tạo lại QR Code</a>\n";
echo "<a href='index.php?page=medicines' style='padding: 15px 30px; background: #6b7280; color: white; text-decoration: none; border-radius: 10px; font-size: 1.1em; display: inline-block; margin: 10px;'>← Quay lại</a>\n";
echo "</p>\n";

echo "</div>\n";
echo "</body></html>\n";
