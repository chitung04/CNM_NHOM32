<?php
/**
 * Script tạo lại QR code THÔNG MINH
 * QR code sẽ chứa URL ngắn: qr.php?c=CODE
 * Hoạt động với BẤT KỲ IP/domain nào
 */

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/qrcode.php';
require_once 'config/config.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Tạo QR Code Thông Minh</title>";
echo "<meta charset='UTF-8'>";
echo "<style>
body { 
    font-family: 'Segoe UI', sans-serif; 
    padding: 20px; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
} 
.container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.success { color: #10b981; } 
.error { color: #ef4444; } 
.info { color: #3b82f6; } 
.warning { color: #f59e0b; }
.progress-bar {
    width: 100%;
    height: 30px;
    background: #e5e7eb;
    border-radius: 15px;
    overflow: hidden;
    margin: 20px 0;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    transition: width 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}
.highlight {
    background: #fef3c7;
    padding: 20px;
    border-radius: 10px;
    border: 2px solid #f59e0b;
    margin: 20px 0;
}
</style>";
echo "</head><body>\n";

echo "<div class='container'>\n";
echo "<h1>🚀 Tạo QR Code Thông Minh</h1>\n";
echo "<p style='font-size: 1.1em; color: #6b7280;'>QR code sẽ hoạt động với BẤT KỲ IP/domain nào!</p>\n";
echo "<hr>\n";

// Lấy current host
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host . '/CNM_NHOM32';

echo "<div class='highlight'>\n";
echo "<h3>💡 Cách hoạt động:</h3>\n";
echo "<ul style='font-size: 1.05em;'>\n";
echo "<li><strong>QR code cũ:</strong> <code>http://26.112.182.250/CNM_NHOM32/medicine_info.php?qr=BATCH_123</code></li>\n";
echo "<li><strong>QR code mới:</strong> <code>{$baseUrl}/qr.php?c=BATCH_123</code></li>\n";
echo "<li>✅ Hoạt động với localhost, IP, domain bất kỳ</li>\n";
echo "<li>✅ Tự động phát hiện loại (medicine/batch/invoice)</li>\n";
echo "<li>✅ Không cần đăng nhập</li>\n";
echo "</ul>\n";
echo "</div>\n";

$db = Database::getInstance();

// 1. Medicines
echo "<h2>📦 Bước 1: Tạo QR code cho THUỐC</h2>\n";
$stmt = $db->query('SELECT medicine_id, medicine_name, qr_code FROM medicines WHERE qr_code IS NOT NULL');
$medicines = $stmt->fetchAll();

echo "<p class='info'>Tìm thấy <strong>" . count($medicines) . "</strong> thuốc</p>\n";

$medicineSuccess = 0;
$medicineFailed = 0;

if (count($medicines) > 0) {
    echo "<div class='progress-bar'><div class='progress-fill' id='medicine-progress' style='width: 0%'>0%</div></div>\n";
    
    foreach ($medicines as $index => $medicine) {
        $qrCode = $medicine['qr_code'];
        $qrFile = "assets/qrcodes/{$qrCode}.png";
        
        // Xóa file cũ
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }
        
        // URL mới - ngắn gọn và linh hoạt
        $qrData = $baseUrl . '/qr.php?c=' . urlencode($qrCode);
        
        try {
            $result = generateQRCode($qrData, $qrCode);
            
            if ($result && file_exists($qrFile)) {
                $medicineSuccess++;
            } else {
                $medicineFailed++;
            }
        } catch (Exception $e) {
            $medicineFailed++;
        }
        
        $progress = round((($index + 1) / count($medicines)) * 100);
        echo "<script>document.getElementById('medicine-progress').style.width = '{$progress}%'; document.getElementById('medicine-progress').textContent = '{$progress}%';</script>\n";
        flush();
    }
    
    echo "<p class='success'>✓ Hoàn thành: {$medicineSuccess} thành công, {$medicineFailed} thất bại</p>\n";
}

echo "<hr>\n";

// 2. Batches
echo "<h2>📦 Bước 2: Tạo QR code cho LÔ THUỐC</h2>\n";
$stmt = $db->query('SELECT batch_id, medicine_id, qr_code FROM batches WHERE qr_code IS NOT NULL');
$batches = $stmt->fetchAll();

echo "<p class='info'>Tìm thấy <strong>" . count($batches) . "</strong> lô thuốc</p>\n";

$batchSuccess = 0;
$batchFailed = 0;

if (count($batches) > 0) {
    echo "<div class='progress-bar'><div class='progress-fill' id='batch-progress' style='width: 0%'>0%</div></div>\n";
    
    foreach ($batches as $index => $batch) {
        $qrCode = $batch['qr_code'];
        $qrFile = "assets/qrcodes/{$qrCode}.png";
        
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }
        
        $qrData = $baseUrl . '/qr.php?c=' . urlencode($qrCode);
        
        try {
            $result = generateQRCode($qrData, $qrCode);
            
            if ($result && file_exists($qrFile)) {
                $batchSuccess++;
            } else {
                $batchFailed++;
            }
        } catch (Exception $e) {
            $batchFailed++;
        }
        
        $progress = round((($index + 1) / count($batches)) * 100);
        echo "<script>document.getElementById('batch-progress').style.width = '{$progress}%'; document.getElementById('batch-progress').textContent = '{$progress}%';</script>\n";
        flush();
    }
    
    echo "<p class='success'>✓ Hoàn thành: {$batchSuccess} thành công, {$batchFailed} thất bại</p>\n";
}

echo "<hr>\n";

// 3. Invoices
echo "<h2>📦 Bước 3: Tạo QR code cho HÓA ĐƠN</h2>\n";
$stmt = $db->query('SELECT invoice_id, invoice_number, qr_code FROM invoices WHERE qr_code IS NOT NULL');
$invoices = $stmt->fetchAll();

echo "<p class='info'>Tìm thấy <strong>" . count($invoices) . "</strong> hóa đơn</p>\n";

$invoiceSuccess = 0;
$invoiceFailed = 0;

if (count($invoices) > 0) {
    echo "<div class='progress-bar'><div class='progress-fill' id='invoice-progress' style='width: 0%'>0%</div></div>\n";
    
    foreach ($invoices as $index => $invoice) {
        $qrCode = $invoice['qr_code'];
        $qrFile = "assets/qrcodes/{$qrCode}.png";
        
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }
        
        $qrData = $baseUrl . '/qr.php?c=' . urlencode($qrCode);
        
        try {
            $result = generateQRCode($qrData, $qrCode);
            
            if ($result && file_exists($qrFile)) {
                $invoiceSuccess++;
            } else {
                $invoiceFailed++;
            }
        } catch (Exception $e) {
            $invoiceFailed++;
        }
        
        $progress = round((($index + 1) / count($invoices)) * 100);
        echo "<script>document.getElementById('invoice-progress').style.width = '{$progress}%'; document.getElementById('invoice-progress').textContent = '{$progress}%';</script>\n";
        flush();
    }
    
    echo "<p class='success'>✓ Hoàn thành: {$invoiceSuccess} thành công, {$invoiceFailed} thất bại</p>\n";
}

echo "<hr>\n";

// Tổng kết
$totalSuccess = $medicineSuccess + $batchSuccess + $invoiceSuccess;
$totalFailed = $medicineFailed + $batchFailed + $invoiceFailed;

echo "<div style='background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 30px; border-radius: 15px; border: 3px solid #10b981;'>\n";
echo "<h2 style='color: #065f46;'>🎉 Hoàn thành!</h2>\n";
echo "<div style='font-size: 1.3em; margin: 20px 0;'>\n";
echo "<p><strong>Tổng số QR code:</strong> " . ($totalSuccess + $totalFailed) . "</p>\n";
echo "<p class='success'><strong>✓ Thành công:</strong> {$totalSuccess}</p>\n";
echo "<p class='error'><strong>✗ Thất bại:</strong> {$totalFailed}</p>\n";
echo "</div>\n";

echo "<div style='background: white; padding: 20px; border-radius: 10px; margin-top: 20px;'>\n";
echo "<h3>✅ Bây giờ QR code sẽ:</h3>\n";
echo "<ul style='font-size: 1.1em;'>\n";
echo "<li>✅ Hoạt động với <strong>localhost</strong></li>\n";
echo "<li>✅ Hoạt động với <strong>IP bất kỳ</strong> (192.168.x.x, 26.112.x.x, ...)</li>\n";
echo "<li>✅ Hoạt động với <strong>domain</strong> (nếu có)</li>\n";
echo "<li>✅ <strong>KHÔNG CẦN</strong> đăng nhập</li>\n";
echo "<li>✅ Tự động phát hiện loại và chuyển hướng</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<div style='background: #fef3c7; padding: 15px; border-radius: 10px; margin-top: 20px; border: 2px solid #f59e0b;'>\n";
echo "<h4>📱 Cách test:</h4>\n";
echo "<ol style='font-size: 1.05em;'>\n";
echo "<li>Quét QR code bằng điện thoại</li>\n";
echo "<li>Sẽ mở URL: <code>{$baseUrl}/qr.php?c=...</code></li>\n";
echo "<li>Tự động chuyển đến trang thông tin</li>\n";
echo "<li>Không cần đăng nhập!</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "</div>\n";

echo "<p style='text-align: center; margin-top: 30px;'>\n";
echo "<a href='index.php?page=medicines' style='padding: 15px 40px; background: #3b82f6; color: white; text-decoration: none; border-radius: 10px; font-size: 1.2em; display: inline-block;'>← Quay lại Quản lý thuốc</a>\n";
echo "</p>\n";

echo "</div>\n";
echo "</body></html>\n";
