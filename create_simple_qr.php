<?php
/**
 * Tạo QR code đơn giản để test
 */

echo "<h2>🔧 Tạo QR Code Đơn Giản</h2>";

// Tạo QR code trỏ đến trang đơn giản
$simpleUrl = 'http://localhost/CNM_NHOM32/medicine_info_simple.php?qr=BATCH_1735000101_2001';
$qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($simpleUrl);

try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $qrImage = file_get_contents($qrApiUrl, false, $context);
    
    if ($qrImage && strlen($qrImage) > 100) {
        // Lưu QR code
        $qrPath = 'assets/qrcodes/SIMPLE_TEST.png';
        file_put_contents($qrPath, $qrImage);
        
        echo "<div style='border: 2px solid #28a745; padding: 20px; text-align: center; margin: 20px 0;'>";
        echo "<h3>📱 QR Code Test Đơn Giản</h3>";
        echo "<img src='{$qrPath}' style='width: 250px; height: 250px;'>";
        echo "<p><strong>URL:</strong> {$simpleUrl}</p>";
        echo "<p style='color: green;'>✅ Quét QR này sẽ mở trang thông tin thuốc đơn giản (không cần database)</p>";
        echo "</div>";
        
        // Test trực tiếp
        echo "<p><a href='{$simpleUrl}' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test trực tiếp</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Không thể tạo QR code</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

// Thay thế QR code gốc bằng QR code đơn giản
echo "<hr>";
echo "<h3>🔄 Thay thế QR codes gốc</h3>";

$action = $_GET['action'] ?? '';
if ($action === 'replace_all') {
    $replaced = 0;
    
    // Danh sách QR codes cần thay thế
    $qrCodes = [
        'BATCH_1735000101_2001', 'BATCH_1735000102_2002', 'BATCH_1735000103_2003', 
        'BATCH_1735000104_2004', 'BATCH_1735000105_2005'
    ];
    
    foreach ($qrCodes as $qrCode) {
        $url = "http://localhost/CNM_NHOM32/medicine_info_simple.php?qr=" . $qrCode;
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($url);
        
        $image = @file_get_contents($apiUrl, false, $context);
        
        if ($image && strlen($image) > 100) {
            $path = 'assets/qrcodes/' . $qrCode . '.png';
            file_put_contents($path, $image);
            $replaced++;
            echo "<p style='color: green;'>✅ Đã thay thế: {$qrCode}</p>";
        }
        
        usleep(200000); // Delay 0.2s
    }
    
    echo "<p style='color: green; font-weight: bold;'>✅ Đã thay thế {$replaced} QR codes</p>";
    echo "<p>Bây giờ QR codes sẽ mở trang đơn giản không cần database</p>";
    
} else {
    echo "<p><a href='?action=replace_all' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Thay thế 5 QR codes đầu tiên</a></p>";
    echo "<p style='color: orange;'>⚠️ Điều này sẽ thay thế QR codes để trỏ đến trang đơn giản</p>";
}

echo "<hr>";
echo "<p><a href='simple_qr_test.html'>🧪 Trang test QR</a> | <a href='index.php'>🏠 Về trang chủ</a></p>";
?>