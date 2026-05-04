<?php
/**
 * Chạy cập nhật QR codes tự động
 */

echo "<h2>🚀 Đang cập nhật tất cả QR codes...</h2>";

// IP của máy tính
$serverIP = '192.168.100.98';

// Danh sách tất cả QR codes
$allQRCodes = [
    'BATCH_1735000101_2001', 'BATCH_1735000102_2002', 'BATCH_1735000103_2003', 'BATCH_1735000104_2004', 'BATCH_1735000105_2005',
    'BATCH_1735000106_2006', 'BATCH_1735000107_2007', 'BATCH_1735000108_2008', 'BATCH_1735000109_2009', 'BATCH_1735000110_2010',
    'BATCH_1735000111_2011', 'BATCH_1735000112_2012', 'BATCH_1735000113_2013', 'BATCH_1735000114_2014', 'BATCH_1735000115_2015',
    'BATCH_1735000116_2016', 'BATCH_1735000117_2017', 'BATCH_1735000118_2018', 'BATCH_1735000119_2019', 'BATCH_1735000120_2020',
    'BATCH_1735000121_2021', 'BATCH_1735000122_2022', 'BATCH_1735000123_2023', 'BATCH_1735000124_2024', 'BATCH_1735000125_2025',
    'BATCH_1735000126_2026', 'BATCH_1735000127_2027', 'BATCH_1735000128_2028', 'BATCH_1735000129_2029', 'BATCH_1735000130_2030',
    'BATCH_1735000131_2031', 'BATCH_1735000132_2032', 'BATCH_1735000133_2033', 'BATCH_1735000134_2034', 'BATCH_1735000135_2035',
    'BATCH_1735000136_2036', 'BATCH_1735000137_2037', 'BATCH_1735000138_2038', 'BATCH_1735000139_2039', 'BATCH_1735000140_2040',
    'BATCH_1735000141_2041', 'BATCH_1735000142_2042', 'BATCH_1735000143_2043', 'BATCH_1735000144_2044', 'BATCH_1735000145_2045',
    'BATCH_1735000146_2046', 'BATCH_1735000147_2047', 'BATCH_1735000148_2048', 'BATCH_1735000149_2049', 'BATCH_1735000150_2050',
    'BATCH_1735000151_2051', 'BATCH_1735000152_2052', 'BATCH_1735000153_2053', 'BATCH_1735000154_2054', 'BATCH_1735000155_2055',
    'BATCH_1735000156_2056', 'BATCH_1735000157_2057', 'BATCH_1735000158_2058', 'BATCH_1735000159_2059', 'BATCH_1735000160_2060',
    'BATCH_1735000161_2061', 'BATCH_1735000162_2062'
];

$success = 0;
$failed = 0;

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

echo "<p>📋 Đang cập nhật " . count($allQRCodes) . " QR codes với IP: <strong>{$serverIP}</strong></p>";
echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;'>";

foreach ($allQRCodes as $index => $qrCode) {
    try {
        // URL với IP trỏ đến trang medicine_info.php gốc
        $url = "http://{$serverIP}/CNM_NHOM32/medicine_info.php?qr=" . $qrCode;
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($url);
        
        $image = @file_get_contents($apiUrl, false, $context);
        
        if ($image && strlen($image) > 100) {
            $path = 'assets/qrcodes/' . $qrCode . '.png';
            file_put_contents($path, $image);
            $success++;
            echo "<p style='color: green;'>✅ " . ($index + 1) . "/62: {$qrCode}</p>";
        } else {
            $failed++;
            echo "<p style='color: red;'>❌ " . ($index + 1) . "/62: {$qrCode}</p>";
        }
        
        // Flush output để hiển thị real-time
        if (ob_get_level()) ob_flush();
        flush();
        
        usleep(200000); // Delay 0.2s
        
    } catch (Exception $e) {
        $failed++;
        echo "<p style='color: red;'>❌ " . ($index + 1) . "/62: {$qrCode} - " . $e->getMessage() . "</p>";
    }
}

echo "</div>";

echo "<hr>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 5px; text-align: center;'>";
echo "<h3 style='color: #155724;'>🎉 HOÀN THÀNH!</h3>";
echo "<p style='font-size: 18px;'>";
echo "✅ Thành công: <strong>{$success}</strong> QR codes<br>";
echo "❌ Thất bại: <strong>{$failed}</strong> QR codes";
echo "</p>";
echo "<h4 style='color: #155724;'>🎯 Tất cả QR codes giờ hoạt động từ điện thoại!</h4>";
echo "</div>";

// Test một QR code
if ($success > 0) {
    $testQR = $allQRCodes[0];
    $testUrl = "http://{$serverIP}/CNM_NHOM32/medicine_info.php?qr=" . $testQR;
    
    echo "<div style='background: #cce5ff; border: 1px solid #007bff; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;'>";
    echo "<h4>🧪 Test QR Code</h4>";
    echo "<p>Quét QR code này để test:</p>";
    echo "<img src='assets/qrcodes/{$testQR}.png' style='width: 200px; height: 200px; border: 2px solid #007bff;'>";
    echo "<p><strong>URL:</strong> {$testUrl}</p>";
    echo "<p><a href='{$testUrl}' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Test trực tiếp</a></p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Về trang chủ</a></p>";
?>