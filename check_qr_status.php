<?php
/**
 * Kiểm tra trạng thái QR code
 */

require_once 'config/database.php';
require_once 'models/Database.php';

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
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.success { color: #10b981; }
.error { color: #ef4444; }
.warning { color: #f59e0b; }
.info { color: #3b82f6; }
.test-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
    border-left: 4px solid #3b82f6;
}
.qr-image {
    max-width: 200px;
    border: 2px solid #ddd;
    border-radius: 5px;
    padding: 5px;
    background: white;
}
</style>";
echo "</head><body>\n";

echo "<div class='container'>\n";
echo "<h1>🔍 Kiểm tra trạng thái QR Code</h1>\n";
echo "<hr>\n";

$db = Database::getInstance();

// Thông tin hệ thống
echo "<h2>📊 Thông tin hệ thống</h2>\n";
echo "<div class='test-box'>\n";
echo "<p><strong>Current URL:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>\n";
echo "<p><strong>Protocol:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP') . "</p>\n";
echo "<p><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>\n";

// Kiểm tra localhost
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) || 
               strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0;

if ($isLocalhost) {
    echo "<p class='warning'><strong>⚠️ ĐANG DÙNG LOCALHOST!</strong> QR code sẽ không hoạt động trên điện thoại.</p>\n";
} else {
    echo "<p class='success'><strong>✓ Đang dùng IP:</strong> " . $_SERVER['HTTP_HOST'] . "</p>\n";
}

echo "</div>\n";

echo "<hr>\n";

// Kiểm tra file qr.php
echo "<h2>📄 Kiểm tra file qr.php</h2>\n";
if (file_exists('qr.php')) {
    echo "<p class='success'>✓ File qr.php tồn tại</p>\n";
} else {
    echo "<p class='error'>✗ File qr.php KHÔNG tồn tại! Cần tạo file này.</p>\n";
}

echo "<hr>\n";

// Test 1: Kiểm tra Medicines
echo "<h2>📦 Test 1: QR Code THUỐC</h2>\n";
$stmt = $db->query('SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NOT NULL LIMIT 3');
$medicines = $stmt->fetchAll();

if (empty($medicines)) {
    echo "<p class='warning'>Không có thuốc nào có QR code</p>\n";
} else {
    foreach ($medicines as $med) {
        $qrCode = $med['qr_code'];
        $qrFile = "assets/qrcodes/{$qrCode}.png";
        $fileExists = file_exists($qrFile);
        
        echo "<div class='test-box'>\n";
        echo "<h4>#{$med['medicine_id']}: {$med['medicine_name']}</h4>\n";
        echo "<p><strong>QR Code:</strong> <code>{$qrCode}</code></p>\n";
        echo "<p><strong>File:</strong> " . ($fileExists ? "<span class='success'>✓ Tồn tại</span>" : "<span class='error'>✗ Không tồn tại</span>") . "</p>\n";
        
        if ($fileExists) {
            echo "<p><img src='{$qrFile}' class='qr-image'></p>\n";
            
            // Test URLs
            $newURL = "http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/qr.php?c=" . urlencode($qrCode);
            $directURL = "http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/medicine_info.php?qr=" . urlencode($qrCode);
            
            echo "<p><strong>URL mới (qua qr.php):</strong><br>";
            echo "<a href='{$newURL}' target='_blank' style='color: blue;'>{$newURL}</a></p>\n";
            
            echo "<p><strong>URL trực tiếp:</strong><br>";
            echo "<a href='{$directURL}' target='_blank' style='color: blue;'>{$directURL}</a></p>\n";
            
            echo "<p><button onclick=\"window.open('{$newURL}', '_blank')\" style='padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer;'>🧪 Test URL mới</button> ";
            echo "<button onclick=\"window.open('{$directURL}', '_blank')\" style='padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer;'>🧪 Test URL trực tiếp</button></p>\n";
        }
        
        echo "</div>\n";
    }
}

echo "<hr>\n";

// Test 2: Kiểm tra Invoices
echo "<h2>📦 Test 2: QR Code HÓA ĐƠN</h2>\n";
$stmt = $db->query('SELECT invoice_id, invoice_number, qr_code FROM invoices WHERE qr_code IS NOT NULL LIMIT 3');
$invoices = $stmt->fetchAll();

if (empty($invoices)) {
    echo "<p class='warning'>Không có hóa đơn nào có QR code</p>\n";
} else {
    foreach ($invoices as $inv) {
        $qrCode = $inv['qr_code'];
        $qrFile = "assets/qrcodes/{$qrCode}.png";
        $fileExists = file_exists($qrFile);
        
        echo "<div class='test-box'>\n";
        echo "<h4>#{$inv['invoice_id']}: {$inv['invoice_number']}</h4>\n";
        echo "<p><strong>QR Code:</strong> <code>{$qrCode}</code></p>\n";
        echo "<p><strong>File:</strong> " . ($fileExists ? "<span class='success'>✓ Tồn tại</span>" : "<span class='error'>✗ Không tồn tại</span>") . "</p>\n";
        
        if ($fileExists) {
            echo "<p><img src='{$qrFile}' class='qr-image'></p>\n";
            
            // Test URLs
            $newURL = "http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/qr.php?c=" . urlencode($qrCode);
            $directURL = "http://" . $_SERVER['HTTP_HOST'] . "/CNM_NHOM32/invoice_info.php?qr=" . urlencode($qrCode) . "&id=" . $inv['invoice_id'];
            
            echo "<p><strong>URL mới (qua qr.php):</strong><br>";
            echo "<a href='{$newURL}' target='_blank' style='color: blue;'>{$newURL}</a></p>\n";
            
            echo "<p><strong>URL trực tiếp:</strong><br>";
            echo "<a href='{$directURL}' target='_blank' style='color: blue;'>{$directURL}</a></p>\n";
            
            echo "<p><button onclick=\"window.open('{$newURL}', '_blank')\" style='padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer;'>🧪 Test URL mới</button> ";
            echo "<button onclick=\"window.open('{$directURL}', '_blank')\" style='padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer;'>🧪 Test URL trực tiếp</button></p>\n";
        }
        
        echo "</div>\n";
    }
}

echo "<hr>\n";

// Hướng dẫn
echo "<h2>📝 Hướng dẫn sửa lỗi</h2>\n";
echo "<div style='background: #fef3c7; padding: 20px; border-radius: 10px; border: 2px solid #f59e0b;'>\n";

if ($isLocalhost) {
    echo "<h3 class='warning'>⚠️ BẠN ĐANG DÙNG LOCALHOST!</h3>\n";
    echo "<p><strong>Vấn đề:</strong> QR code không hoạt động trên điện thoại khi dùng localhost.</p>\n";
    echo "<p><strong>Giải pháp:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Tìm IP của máy tính (ví dụ: 192.168.1.100 hoặc 26.112.182.250)</li>\n";
    echo "<li>Truy cập trang này qua IP: <code>http://[IP]/CNM_NHOM32/check_qr_status.php</code></li>\n";
    echo "<li>Chạy script tạo QR code qua IP: <code>http://[IP]/CNM_NHOM32/regenerate_qr_smart.php</code></li>\n";
    echo "</ol>\n";
} else {
    echo "<h3 class='success'>✓ Đang dùng IP - Tốt!</h3>\n";
    echo "<p><strong>Nếu QR code vẫn không hoạt động:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Chạy script tạo lại QR code: <a href='regenerate_qr_smart.php' target='_blank'>regenerate_qr_smart.php</a></li>\n";
    echo "<li>Đảm bảo điện thoại và máy tính cùng mạng WiFi</li>\n";
    echo "<li>Thử click vào các nút 'Test' ở trên để kiểm tra</li>\n";
    echo "</ol>\n";
}

echo "</div>\n";

echo "<hr>\n";
echo "<p style='text-align: center;'>\n";
echo "<a href='regenerate_qr_smart.php' style='padding: 15px 30px; background: #3b82f6; color: white; text-decoration: none; border-radius: 10px; font-size: 1.1em; display: inline-block; margin: 10px;'>🔄 Tạo lại QR Code</a>\n";
echo "<a href='index.php?page=invoices' style='padding: 15px 30px; background: #6b7280; color: white; text-decoration: none; border-radius: 10px; font-size: 1.1em; display: inline-block; margin: 10px;'>← Quay lại</a>\n";
echo "</p>\n";

echo "</div>\n";
echo "</body></html>\n";
