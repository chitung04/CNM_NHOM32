<?php
/**
 * Tạo QR code với IP thay vì localhost
 */

echo "<h2>🌐 Tạo QR Code với IP</h2>";

// IP của máy tính
$serverIP = '192.168.100.98';
$port = '80'; // XAMPP mặc định port 80

echo "<p><strong>IP máy tính:</strong> {$serverIP}</p>";

// Test URL với IP
$ipUrl = "http://{$serverIP}/CNM_NHOM32/medicine_info_simple.php?qr=BATCH_1735000101_2001";

echo "<p><strong>URL với IP:</strong> <a href='{$ipUrl}' target='_blank'>{$ipUrl}</a></p>";

// Tạo QR code với IP
$qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($ipUrl);

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
        $qrPath = 'assets/qrcodes/IP_TEST.png';
        file_put_contents($qrPath, $qrImage);
        
        echo "<div style='border: 2px solid #28a745; padding: 20px; text-align: center; margin: 20px 0;'>";
        echo "<h3>📱 QR Code với IP</h3>";
        echo "<img src='{$qrPath}' style='width: 250px; height: 250px;'>";
        echo "<p><strong>URL:</strong> {$ipUrl}</p>";
        echo "<p style='color: green;'>✅ Quét QR này từ điện thoại!</p>";
        echo "</div>";
        
        // Test trực tiếp
        echo "<p><a href='{$ipUrl}' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test trực tiếp trên máy tính</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Không thể tạo QR code</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

// Tạo QR codes cho hệ thống thật với IP
echo "<hr>";
echo "<h3>🔄 Tạo QR codes thật với IP</h3>";

$action = $_GET['action'] ?? '';
if ($action === 'create_ip_qrs') {
    $created = 0;
    
    // Tạo 5 QR codes đầu tiên với IP
    $qrCodes = [
        'BATCH_1735000101_2001', 'BATCH_1735000102_2002', 'BATCH_1735000103_2003', 
        'BATCH_1735000104_2004', 'BATCH_1735000105_2005'
    ];
    
    foreach ($qrCodes as $qrCode) {
        $url = "http://{$serverIP}/CNM_NHOM32/medicine_info_simple.php?qr=" . $qrCode;
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($url);
        
        $image = @file_get_contents($apiUrl, false, $context);
        
        if ($image && strlen($image) > 100) {
            $path = 'assets/qrcodes/' . $qrCode . '.png';
            file_put_contents($path, $image);
            $created++;
            echo "<p style='color: green;'>✅ Đã tạo: {$qrCode} → {$url}</p>";
        }
        
        usleep(200000); // Delay 0.2s
    }
    
    echo "<p style='color: green; font-weight: bold;'>✅ Đã tạo {$created} QR codes với IP</p>";
    echo "<p>Bây giờ QR codes sẽ hoạt động từ điện thoại!</p>";
    
} else {
    echo "<p><a href='?action=create_ip_qrs' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Tạo QR codes với IP</a></p>";
    echo "<p style='color: blue;'>💡 Điều này sẽ tạo QR codes dùng IP thay vì localhost</p>";
}

// Hướng dẫn
echo "<hr>";
echo "<h3>📋 Hướng dẫn:</h3>";
echo "<ol>";
echo "<li><strong>Đảm bảo cùng WiFi:</strong> Điện thoại và máy tính phải cùng mạng WiFi</li>";
echo "<li><strong>Test IP trước:</strong> Nhấn nút 'Test trực tiếp' ở trên</li>";
echo "<li><strong>Nếu test OK:</strong> Nhấn 'Tạo QR codes với IP'</li>";
echo "<li><strong>Quét thử:</strong> Quét QR code mới tạo</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='index.php'>🏠 Về trang chủ</a></p>";
?>