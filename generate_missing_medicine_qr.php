<?php
/**
 * Script to generate missing QR code images for medicines
 */

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/qrcode.php';
require_once 'config/config.php';
require_once 'helpers/url_helper.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Generate Missing Medicine QR Codes</title>";
echo "<style>body { font-family: monospace; padding: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; } .warning { color: orange; }</style>";
echo "</head><body>\n";

echo "<h1>Generate Missing Medicine QR Codes</h1>\n";
echo "<hr>\n";

// Kiểm tra xem có đang dùng localhost không
if (isLocalhost()) {
    echo "<div class='warning' style='background: #fff3cd; padding: 15px; border: 2px solid orange; border-radius: 5px; margin-bottom: 20px;'>\n";
    echo "<h3>⚠️ CẢNH BÁO: Đang sử dụng LOCALHOST</h3>\n";
    echo "<p><strong>Hostname hiện tại:</strong> " . $_SERVER['HTTP_HOST'] . "</p>\n";
    echo "<p><strong>QR code sẽ KHÔNG hoạt động</strong> khi quét bằng điện thoại vì điện thoại không thể truy cập localhost của máy tính.</p>\n";
    echo "<p><strong>Giải pháp:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Tìm IP thực của máy tính (ví dụ: 192.168.1.100 hoặc 26.112.182.250)</li>\n";
    echo "<li>Truy cập trang này qua IP: <code>http://[IP]/CNM_NHOM32/generate_missing_medicine_qr.php</code></li>\n";
    echo "<li>Chạy lại script để tạo QR code với URL đúng</li>\n";
    echo "</ol>\n";
    
    $serverIP = getServerIP();
    if ($serverIP) {
        $correctURL = "http://{$serverIP}/CNM_NHOM32/generate_missing_medicine_qr.php";
        echo "<p><strong>URL đề xuất:</strong> <a href='{$correctURL}' style='color: blue; font-size: 1.2em;'>{$correctURL}</a></p>\n";
    }
    
    echo "<p style='margin-top: 15px;'><strong>Bạn có muốn tiếp tục tạo QR code với localhost không?</strong></p>\n";
    echo "<form method='post' style='margin-top: 10px;'>\n";
    echo "<button type='submit' name='force_generate' value='1' style='background: orange; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em;'>⚠️ Tiếp tục (QR sẽ không hoạt động)</button>\n";
    echo "<a href='index.php?page=medicines' style='margin-left: 10px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại</a>\n";
    echo "</form>\n";
    echo "</div>\n";
    
    // Nếu không force generate thì dừng lại
    if (!isset($_POST['force_generate'])) {
        echo "</body></html>\n";
        exit;
    }
}

// Lấy base URL để tạo QR code
$baseURL = getBaseUrl();
echo "<p class='info'><strong>Base URL sử dụng:</strong> {$baseURL}</p>\n";

$db = Database::getInstance();

// Get all medicines with QR codes
$stmt = $db->query('SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NOT NULL');
$medicines = $stmt->fetchAll();

echo "<p class='info'>Found " . count($medicines) . " medicines with QR codes in database</p>\n";
echo "<hr>\n";

$generated = 0;
$skipped = 0;
$failed = 0;

foreach ($medicines as $medicine) {
    $qrCode = $medicine['qr_code'];
    $qrFile = "assets/qrcodes/{$qrCode}.png";
    
    echo "<p><strong>Medicine #{$medicine['medicine_id']}: {$medicine['medicine_name']}</strong><br>\n";
    echo "QR Code: {$qrCode}<br>\n";
    
    // Check if file already exists
    if (file_exists($qrFile)) {
        echo "<span class='info'>✓ QR code file already exists</span><br>\n";
        echo "<span class='warning'>⚠️ Xóa file cũ để tạo lại với URL mới...</span><br>\n";
        unlink($qrFile);
    }
    
    // Generate QR code with correct URL (using IP instead of localhost)
    $qrData = $baseURL . '/public_medicine_info.php?qr=' . urlencode($qrCode);
    
    echo "Generating QR code with URL: <code>{$qrData}</code><br>\n";
    
    try {
        $result = generateQRCode($qrData, $qrCode);
        
        if ($result && file_exists($qrFile)) {
            echo "<span class='success'>✓ QR code generated successfully!</span></p>\n";
            $generated++;
        } else {
            echo "<span class='error'>✗ Failed to generate QR code</span></p>\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "<span class='error'>✗ Error: " . $e->getMessage() . "</span></p>\n";
        $failed++;
    }
    
    // Flush output to show progress
    flush();
}

echo "<hr>\n";
echo "<h2>Summary</h2>\n";
echo "<p class='success'>Generated: {$generated}</p>\n";
echo "<p class='info'>Skipped (already exists): {$skipped}</p>\n";
echo "<p class='error'>Failed: {$failed}</p>\n";
echo "<p><strong>Total: " . count($medicines) . "</strong></p>\n";

echo "<hr>\n";
echo "<p><a href='index.php?page=medicines'>← Back to Medicines</a></p>\n";
echo "</body></html>\n";
