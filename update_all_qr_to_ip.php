<?php
/**
 * Cập nhật tất cả QR codes để dùng IP thay vì localhost
 */

echo "<h2>🔄 Cập nhật tất cả QR codes với IP</h2>";

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

echo "<p>📋 Cần cập nhật " . count($allQRCodes) . " QR codes</p>";
echo "<p>🌐 IP server: <strong>{$serverIP}</strong></p>";

$action = $_GET['action'] ?? '';
if ($action === 'update_all') {
    $success = 0;
    $failed = 0;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    foreach ($allQRCodes as $qrCode) {
        try {
            // URL với IP trỏ đến trang medicine_info.php gốc (có database)
            $url = "http://{$serverIP}/CNM_NHOM32/medicine_info.php?qr=" . $qrCode;
            $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($url);
            
            $image = @file_get_contents($apiUrl, false, $context);
            
            if ($image && strlen($image) > 100) {
                $path = 'assets/qrcodes/' . $qrCode . '.png';
                file_put_contents($path, $image);
                $success++;
                
                if ($success <= 10) {
                    echo "<p style='color: green;'>✅ {$qrCode}</p>";
                }
            } else {
                $failed++;
                echo "<p style='color: red;'>❌ {$qrCode}</p>";
            }
            
            usleep(200000); // Delay 0.2s
            
        } catch (Exception $e) {
            $failed++;
            echo "<p style='color: red;'>❌ {$qrCode}: " . $e->getMessage() . "</p>";
        }
    }
    
    if ($success > 10) {
        echo "<p style='color: green;'>... và " . ($success - 10) . " QR codes khác</p>";
    }
    
    echo "<hr>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: #155724;'>🎉 Hoàn thành!</h4>";
    echo "<p>✅ Thành công: <strong>{$success}</strong> QR codes</p>";
    echo "<p>❌ Thất bại: <strong>{$failed}</strong> QR codes</p>";
    echo "<p>🎯 Tất cả QR codes giờ sẽ hoạt động từ điện thoại!</p>";
    echo "</div>";
    
} else {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Lưu ý quan trọng:</h4>";
    echo "<ul>";
    echo "<li>Điều này sẽ cập nhật TẤT CẢ 62 QR codes</li>";
    echo "<li>QR codes sẽ trỏ đến trang medicine_info.php gốc (có database)</li>";
    echo "<li>Sử dụng IP {$serverIP} thay vì localhost</li>";
    echo "<li>Quá trình mất khoảng 2-3 phút</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<p><a href='?action=update_all' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🚀 Cập nhật tất cả QR codes</a></p>";
}

echo "<hr>";
echo "<p><a href='index.php'>🏠 Về trang chủ</a></p>";
?>