<?php
/**
 * Test QR Code Generation
 */

require_once 'helpers/qrcode.php';

echo "<h2>🧪 Test QR Code Generation</h2>";

// Tạo thư mục nếu chưa có
if (!file_exists('assets/qrcodes')) {
    mkdir('assets/qrcodes', 0777, true);
    echo "<p>✅ Đã tạo thư mục assets/qrcodes</p>";
}

// Test tạo một vài QR codes
$testCases = [
    [
        'filename' => 'TEST_MED_001',
        'url' => 'http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id=1&qr=TEST_MED_001'
    ],
    [
        'filename' => 'TEST_MED_002', 
        'url' => 'http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id=2&qr=TEST_MED_002'
    ],
    [
        'filename' => 'TEST_MED_003',
        'url' => 'http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id=3&qr=TEST_MED_003'
    ]
];

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🔧 Tạo QR codes test:</h4>";

foreach ($testCases as $test) {
    echo "<div style='margin: 10px 0; padding: 10px; background: white; border-radius: 5px;'>";
    echo "<strong>Tạo QR: {$test['filename']}</strong><br>";
    echo "<small>URL: {$test['url']}</small><br>";
    
    $result = generateQRCode($test['url'], $test['filename']);
    
    if ($result) {
        $filePath = "assets/qrcodes/{$test['filename']}.png";
        if (file_exists($filePath)) {
            $fileSize = filesize($filePath);
            echo "<span style='color: green;'>✅ Thành công - File: {$filePath} ({$fileSize} bytes)</span><br>";
            echo "<img src='{$filePath}' alt='QR Code' style='width: 100px; height: 100px; border: 1px solid #ddd; margin-top: 5px;'>";
        } else {
            echo "<span style='color: red;'>❌ File không tồn tại sau khi tạo</span>";
        }
    } else {
        echo "<span style='color: red;'>❌ Lỗi tạo QR code</span>";
    }
    
    echo "</div>";
}

echo "</div>";

// Kiểm tra thư mục
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>📁 Kiểm tra thư mục assets/qrcodes:</h4>";

if (is_dir('assets/qrcodes')) {
    $files = scandir('assets/qrcodes');
    $qrFiles = array_filter($files, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'png';
    });
    
    echo "<p><strong>Tổng files QR:</strong> " . count($qrFiles) . "</p>";
    
    if (count($qrFiles) > 0) {
        echo "<div style='max-height: 200px; overflow-y: auto;'>";
        foreach ($qrFiles as $file) {
            $fileSize = filesize('assets/qrcodes/' . $file);
            echo "<div style='margin: 5px 0;'>";
            echo "<strong>{$file}</strong> ({$fileSize} bytes)";
            echo "</div>";
        }
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ Thư mục assets/qrcodes không tồn tại!</p>";
}

echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='run_qr_fix.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>🔧 Chạy fix QR cho tất cả medicines</a>";
echo "<a href='index.php?page=medicines' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>📋 Xem tra cứu thuốc</a>";
echo "</div>";
?>