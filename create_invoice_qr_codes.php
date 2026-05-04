<?php
/**
 * Tạo QR codes cho tất cả đơn hàng
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🧾 Tạo QR Codes cho đơn hàng</h2>";

// IP của máy tính
$serverIP = '192.168.100.98';

try {
    $db = Database::getInstance();
    
    // Lấy tất cả đơn hàng có QR code
    $sql = "SELECT invoice_id, invoice_number, qr_code FROM invoices WHERE qr_code IS NOT NULL ORDER BY invoice_id DESC";
    $stmt = $db->query($sql);
    $invoices = $stmt->fetchAll();
    
    echo "<p>📋 Tìm thấy " . count($invoices) . " đơn hàng có QR code</p>";
    echo "<p>🌐 IP server: <strong>{$serverIP}</strong></p>";
    
    $action = $_GET['action'] ?? '';
    if ($action === 'create_all') {
        $success = 0;
        $failed = 0;
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
        
        echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;'>";
        
        foreach ($invoices as $index => $invoice) {
            try {
                // URL với IP trỏ đến trang invoice_info.php
                $url = "http://{$serverIP}/CNM_NHOM32/invoice_info.php?qr=" . urlencode($invoice['qr_code']) . "&id=" . $invoice['invoice_id'];
                $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($url);
                
                $image = @file_get_contents($apiUrl, false, $context);
                
                if ($image && strlen($image) > 100) {
                    $path = 'assets/qrcodes/' . $invoice['qr_code'] . '.png';
                    file_put_contents($path, $image);
                    $success++;
                    echo "<p style='color: green;'>✅ " . ($index + 1) . "/" . count($invoices) . ": {$invoice['invoice_number']} ({$invoice['qr_code']})</p>";
                } else {
                    $failed++;
                    echo "<p style='color: red;'>❌ " . ($index + 1) . "/" . count($invoices) . ": {$invoice['invoice_number']} ({$invoice['qr_code']})</p>";
                }
                
                // Flush output để hiển thị real-time
                if (ob_get_level()) ob_flush();
                flush();
                
                usleep(200000); // Delay 0.2s
                
            } catch (Exception $e) {
                $failed++;
                echo "<p style='color: red;'>❌ " . ($index + 1) . "/" . count($invoices) . ": {$invoice['invoice_number']} - " . $e->getMessage() . "</p>";
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
        echo "<h4 style='color: #155724;'>🎯 Tất cả QR codes đơn hàng giờ hoạt động từ điện thoại!</h4>";
        echo "</div>";
        
        // Test một QR code
        if ($success > 0 && !empty($invoices)) {
            $testInvoice = $invoices[0];
            $testUrl = "http://{$serverIP}/CNM_NHOM32/invoice_info.php?qr=" . urlencode($testInvoice['qr_code']) . "&id=" . $testInvoice['invoice_id'];
            
            echo "<div style='background: #cce5ff; border: 1px solid #007bff; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;'>";
            echo "<h4>🧪 Test QR Code Đơn Hàng</h4>";
            echo "<p>Quét QR code này để test:</p>";
            echo "<img src='assets/qrcodes/{$testInvoice['qr_code']}.png' style='width: 200px; height: 200px; border: 2px solid #007bff;'>";
            echo "<p><strong>Đơn hàng:</strong> {$testInvoice['invoice_number']}</p>";
            echo "<p><strong>URL:</strong> {$testUrl}</p>";
            echo "<p><a href='{$testUrl}' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Test trực tiếp</a></p>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>⚠️ Lưu ý quan trọng:</h4>";
        echo "<ul>";
        echo "<li>Điều này sẽ tạo QR codes cho TẤT CẢ " . count($invoices) . " đơn hàng</li>";
        echo "<li>QR codes sẽ trỏ đến trang invoice_info.php</li>";
        echo "<li>Sử dụng IP {$serverIP} để hoạt động từ điện thoại</li>";
        echo "<li>Quá trình mất khoảng " . ceil(count($invoices) * 0.2 / 60) . " phút</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<p><a href='?action=create_all' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🚀 Tạo QR codes cho tất cả đơn hàng</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php?page=invoices'>📋 Về danh sách đơn hàng</a> | <a href='index.php'>🏠 Về trang chủ</a></p>";
?>