<?php
/**
 * Script để TẠO LẠI tất cả QR code với IP đúng
 * Chạy script này qua IP, KHÔNG qua localhost
 */

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/qrcode.php';
require_once 'config/config.php';
require_once 'helpers/url_helper.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Tạo lại tất cả QR Codes</title>";
echo "<meta charset='UTF-8'>";
echo "<style>
body { 
    font-family: monospace; 
    padding: 20px; 
    background: #f5f5f5;
} 
.success { color: green; } 
.error { color: red; } 
.info { color: blue; } 
.warning { color: orange; }
.container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.progress-bar {
    width: 100%;
    height: 30px;
    background: #e0e0e0;
    border-radius: 15px;
    overflow: hidden;
    margin: 20px 0;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #45a049);
    transition: width 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}
</style>";
echo "</head><body>\n";

echo "<div class='container'>\n";
echo "<h1>🔄 Tạo lại tất cả QR Codes</h1>\n";
echo "<hr>\n";

// Kiểm tra xem có đang dùng localhost không
if (isLocalhost()) {
    echo "<div class='warning' style='background: #fff3cd; padding: 20px; border: 3px solid orange; border-radius: 10px; margin-bottom: 20px;'>\n";
    echo "<h2>⚠️ CẢNH BÁO: Đang sử dụng LOCALHOST</h2>\n";
    echo "<p><strong>Hostname hiện tại:</strong> " . $_SERVER['HTTP_HOST'] . "</p>\n";
    echo "<p style='font-size: 1.2em; color: red;'><strong>QR code sẽ KHÔNG hoạt động trên điện thoại!</strong></p>\n";
    echo "<p><strong>Bạn PHẢI truy cập qua IP:</strong></p>\n";
    
    $serverIP = getServerIP();
    if ($serverIP) {
        $correctURL = "http://{$serverIP}/CNM_NHOM32/regenerate_all_qr_with_ip.php";
        echo "<p style='font-size: 1.3em;'><a href='{$correctURL}' style='color: blue; font-weight: bold;'>{$correctURL}</a></p>\n";
    }
    
    echo "<p style='margin-top: 20px;'><strong>Bạn có muốn tiếp tục với localhost không?</strong> (Không khuyến khích)</p>\n";
    echo "<form method='post'>\n";
    echo "<button type='submit' name='force_generate' value='1' style='background: orange; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em;'>⚠️ Tiếp tục với localhost</button>\n";
    echo "<a href='index.php?page=medicines' style='margin-left: 10px; padding: 15px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>← Quay lại</a>\n";
    echo "</form>\n";
    echo "</div>\n";
    
    if (!isset($_POST['force_generate'])) {
        echo "</div></body></html>\n";
        exit;
    }
}

// Lấy base URL
$baseURL = getBaseUrl();
echo "<div class='info' style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>\n";
echo "<p><strong>✅ Base URL sử dụng:</strong> <code style='font-size: 1.2em; color: #0056b3;'>{$baseURL}</code></p>\n";
echo "<p><strong>Hostname:</strong> {$_SERVER['HTTP_HOST']}</p>\n";
echo "<p><strong>Server IP:</strong> " . getServerIP() . "</p>\n";
echo "</div>\n";

$db = Database::getInstance();

// 1. Lấy tất cả medicines có QR code
echo "<h2>📦 Bước 1: Tạo lại QR code cho THUỐC</h2>\n";
$stmt = $db->query('SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NOT NULL');
$medicines = $stmt->fetchAll();

echo "<p class='info'>Tìm thấy <strong>" . count($medicines) . "</strong> thuốc có QR code</p>\n";

$medicineSuccess = 0;
$medicineFailed = 0;

if (count($medicines) > 0) {
    echo "<div class='progress-bar'><div class='progress-fill' id='medicine-progress' style='width: 0%'>0%</div></div>\n";
    
    foreach ($medicines as $index => $medicine) {
        $qrCode = $medicine['qr_code'];
        $qrFile = "assets/qrcodes/{$qrCode}.png";
        
        // Xóa file cũ nếu tồn tại
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }
        
        // Tạo URL mới với IP
        $qrData = $baseURL . '/medicine_info.php?qr=' . urlencode($qrCode);
        
        try {
            $result = generateQRCode($qrData, $qrCode);
            
            if ($result && file_exists($qrFile)) {
                $medicineSuccess++;
                echo "<p class='success'>✓ #{$medicine['medicine_id']}: {$medicine['medicine_name']} - <code>{$qrCode}</code></p>\n";
            } else {
                $medicineFailed++;
                echo "<p class='error'>✗ #{$medicine['medicine_id']}: {$medicine['medicine_name']} - FAILED</p>\n";
            }
        } catch (Exception $e) {
            $medicineFailed++;
            echo "<p class='error'>✗ #{$medicine['medicine_id']}: Error - " . $e->getMessage() . "</p>\n";
        }
        
        // Update progress
        $progress = round((($index + 1) / count($medicines)) * 100);
        echo "<script>document.getElementById('medicine-progress').style.width = '{$progress}%'; document.getElementById('medicine-progress').textContent = '{$progress}%';</script>\n";
        flush();
    }
}

echo "<hr>\n";

// 2. Lấy tất cả batches có QR code
echo "<h2>📦 Bước 2: Tạo lại QR code cho LÔ THUỐC</h2>\n";
$stmt = $db->query('SELECT batch_id, medicine_id, qr_code FROM batches WHERE qr_code IS NOT NULL');
$batches = $stmt->fetchAll();

echo "<p class='info'>Tìm thấy <strong>" . count($batches) . "</strong> lô thuốc có QR code</p>\n";

$batchSuccess = 0;
$batchFailed = 0;

if (count($batches) > 0) {
    echo "<div class='progress-bar'><div class='progress-fill' id='batch-progress' style='width: 0%'>0%</div></div>\n";
    
    foreach ($batches as $index => $batch) {
        $qrCode = $batch['qr_code'];
        $qrFile = "assets/qrcodes/{$qrCode}.png";
        
        // Xóa file cũ nếu tồn tại
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }
        
        // Tạo URL mới với IP
        $qrData = $baseURL . '/medicine_info.php?qr=' . urlencode($qrCode);
        
        try {
            $result = generateQRCode($qrData, $qrCode);
            
            if ($result && file_exists($qrFile)) {
                $batchSuccess++;
                echo "<p class='success'>✓ Batch #{$batch['batch_id']}: <code>{$qrCode}</code></p>\n";
            } else {
                $batchFailed++;
                echo "<p class='error'>✗ Batch #{$batch['batch_id']}: FAILED</p>\n";
            }
        } catch (Exception $e) {
            $batchFailed++;
            echo "<p class='error'>✗ Batch #{$batch['batch_id']}: Error - " . $e->getMessage() . "</p>\n";
        }
        
        // Update progress
        $progress = round((($index + 1) / count($batches)) * 100);
        echo "<script>document.getElementById('batch-progress').style.width = '{$progress}%'; document.getElementById('batch-progress').textContent = '{$progress}%';</script>\n";
        flush();
    }
}

echo "<hr>\n";

// 3. Lấy tất cả invoices có QR code
echo "<h2>📦 Bước 3: Tạo lại QR code cho HÓA ĐƠN</h2>\n";
$stmt = $db->query('SELECT invoice_id, invoice_number, qr_code FROM invoices WHERE qr_code IS NOT NULL');
$invoices = $stmt->fetchAll();

echo "<p class='info'>Tìm thấy <strong>" . count($invoices) . "</strong> hóa đơn có QR code</p>\n";

$invoiceSuccess = 0;
$invoiceFailed = 0;

if (count($invoices) > 0) {
    echo "<div class='progress-bar'><div class='progress-fill' id='invoice-progress' style='width: 0%'>0%</div></div>\n";
    
    foreach ($invoices as $index => $invoice) {
        $qrCode = $invoice['qr_code'];
        $qrFile = "assets/qrcodes/{$qrCode}.png";
        
        // Xóa file cũ nếu tồn tại
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }
        
        // Tạo URL mới với IP
        $qrData = $baseURL . '/invoice_info.php?qr=' . urlencode($qrCode) . '&id=' . $invoice['invoice_id'];
        
        try {
            $result = generateQRCode($qrData, $qrCode);
            
            if ($result && file_exists($qrFile)) {
                $invoiceSuccess++;
                echo "<p class='success'>✓ Invoice #{$invoice['invoice_id']}: {$invoice['invoice_number']} - <code>{$qrCode}</code></p>\n";
            } else {
                $invoiceFailed++;
                echo "<p class='error'>✗ Invoice #{$invoice['invoice_id']}: FAILED</p>\n";
            }
        } catch (Exception $e) {
            $invoiceFailed++;
            echo "<p class='error'>✗ Invoice #{$invoice['invoice_id']}: Error - " . $e->getMessage() . "</p>\n";
        }
        
        // Update progress
        $progress = round((($index + 1) / count($invoices)) * 100);
        echo "<script>document.getElementById('invoice-progress').style.width = '{$progress}%'; document.getElementById('invoice-progress').textContent = '{$progress}%';</script>\n";
        flush();
    }
}

echo "<hr>\n";

// Tổng kết
$totalSuccess = $medicineSuccess + $batchSuccess + $invoiceSuccess;
$totalFailed = $medicineFailed + $batchFailed + $invoiceFailed;
$totalAll = $totalSuccess + $totalFailed;

echo "<h2>📊 Tổng kết</h2>\n";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>\n";
echo "<table style='width: 100%; border-collapse: collapse;'>\n";
echo "<tr style='background: #e9ecef;'><th style='padding: 10px; text-align: left;'>Loại</th><th style='padding: 10px;'>Thành công</th><th style='padding: 10px;'>Thất bại</th><th style='padding: 10px;'>Tổng</th></tr>\n";
echo "<tr><td style='padding: 10px;'>Thuốc</td><td class='success' style='padding: 10px; text-align: center;'><strong>{$medicineSuccess}</strong></td><td class='error' style='padding: 10px; text-align: center;'><strong>{$medicineFailed}</strong></td><td style='padding: 10px; text-align: center;'><strong>" . count($medicines) . "</strong></td></tr>\n";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px;'>Lô thuốc</td><td class='success' style='padding: 10px; text-align: center;'><strong>{$batchSuccess}</strong></td><td class='error' style='padding: 10px; text-align: center;'><strong>{$batchFailed}</strong></td><td style='padding: 10px; text-align: center;'><strong>" . count($batches) . "</strong></td></tr>\n";
echo "<tr><td style='padding: 10px;'>Hóa đơn</td><td class='success' style='padding: 10px; text-align: center;'><strong>{$invoiceSuccess}</strong></td><td class='error' style='padding: 10px; text-align: center;'><strong>{$invoiceFailed}</strong></td><td style='padding: 10px; text-align: center;'><strong>" . count($invoices) . "</strong></td></tr>\n";
echo "<tr style='background: #d4edda; font-size: 1.2em;'><td style='padding: 15px;'><strong>TỔNG CỘNG</strong></td><td class='success' style='padding: 15px; text-align: center;'><strong>{$totalSuccess}</strong></td><td class='error' style='padding: 15px; text-align: center;'><strong>{$totalFailed}</strong></td><td style='padding: 15px; text-align: center;'><strong>{$totalAll}</strong></td></tr>\n";
echo "</table>\n";
echo "</div>\n";

if ($totalSuccess > 0) {
    echo "<div class='success' style='background: #d4edda; padding: 20px; border-radius: 10px; margin-top: 20px; border: 2px solid #28a745;'>\n";
    echo "<h3>✅ Hoàn thành!</h3>\n";
    echo "<p style='font-size: 1.1em;'>Đã tạo lại <strong>{$totalSuccess}</strong> QR codes với URL: <code>{$baseURL}</code></p>\n";
    echo "<p><strong>Bây giờ bạn có thể:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Quét QR code bằng điện thoại để kiểm tra</li>\n";
    echo "<li>QR code sẽ mở trang thông tin mà KHÔNG CẦN đăng nhập</li>\n";
    echo "<li>Đảm bảo điện thoại và máy tính cùng mạng WiFi</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
}

if ($totalFailed > 0) {
    echo "<div class='error' style='background: #f8d7da; padding: 20px; border-radius: 10px; margin-top: 20px; border: 2px solid #dc3545;'>\n";
    echo "<h3>⚠️ Có lỗi xảy ra</h3>\n";
    echo "<p><strong>{$totalFailed}</strong> QR codes không thể tạo. Vui lòng kiểm tra log phía trên.</p>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<p style='text-align: center; margin-top: 30px;'>\n";
echo "<a href='index.php?page=medicines' style='padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 1.1em;'>← Quay lại trang Quản lý thuốc</a>\n";
echo "</p>\n";

echo "</div>\n";
echo "</body></html>\n";
