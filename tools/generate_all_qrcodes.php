<?php
/**
 * Generate QR code images cho TẤT CẢ dữ liệu trong database
 * Chạy sau khi import database_schema.sql
 */

define('BASE_PATH', dirname(__DIR__));
define('QRCODE_PATH', BASE_PATH . '/assets/qrcodes');

require_once BASE_PATH . '/helpers/qrcode.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/Database.php';

echo "=== Generate ALL QR Codes ===\n\n";

try {
    $db = Database::getInstance();
    
    // Lấy tất cả medicines từ database
    $medicines = $db->query("
        SELECT medicine_id, medicine_name, qr_code 
        FROM medicines 
        ORDER BY medicine_id
    ")->fetchAll();
    
    // Lấy tất cả batches từ database
    $batches = $db->query("
        SELECT batch_id, qr_code 
        FROM batches 
        ORDER BY batch_id
    ")->fetchAll();
    
    // Lấy tất cả invoices từ database
    $invoices = $db->query("
        SELECT invoice_id, invoice_number, qr_code 
        FROM invoices 
        ORDER BY invoice_id
    ")->fetchAll();
    
    $qrCodes = [];
    
    // Add medicines
    foreach ($medicines as $med) {
        $qrCodes[] = [
            'type' => 'MED',
            'code' => $med['qr_code'],
            'data' => "MEDICINE_ID:{$med['medicine_id']}|CODE:{$med['qr_code']}",
            'name' => $med['medicine_name']
        ];
    }
    
    // Add batches
    foreach ($batches as $batch) {
        $qrCodes[] = [
            'type' => 'BATCH',
            'code' => $batch['qr_code'],
            'data' => "BATCH_ID:{$batch['batch_id']}|CODE:{$batch['qr_code']}",
            'name' => "Batch #{$batch['batch_id']}"
        ];
    }
    
    // Add invoices
    foreach ($invoices as $inv) {
        $qrCodes[] = [
            'type' => 'INV',
            'code' => $inv['qr_code'],
            'data' => "INVOICE:{$inv['invoice_number']}|ID:{$inv['invoice_id']}",
            'name' => $inv['invoice_number']
        ];
    }
    
    // Generate all QR codes
    $success = 0;
    $failed = 0;
    $total = count($qrCodes);
    
    echo "Found:\n";
    echo "- " . count($medicines) . " medicines\n";
    echo "- " . count($batches) . " batches\n";
    echo "- " . count($invoices) . " invoices\n";
    echo "Total: $total QR codes to generate\n\n";
    
    foreach ($qrCodes as $index => $qr) {
        $num = $index + 1;
        echo "[$num/$total] {$qr['type']}: {$qr['name']}...";
        
        $result = generateQRCode($qr['data'], $qr['code']);
        
        if ($result) {
            echo " ✓\n";
            $success++;
        } else {
            echo " ✗\n";
            $failed++;
        }
        
        // Sleep để tránh rate limit API
        if ($num % 10 == 0) {
            echo "   (Pausing...)\n";
            sleep(1);
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Total: $total\n";
    echo "Success: $success\n";
    echo "Failed: $failed\n";
    echo "\nQR codes saved to: " . QRCODE_PATH . "\n";
    echo "\nBạn có thể xem QR codes tại: http://localhost/CNM_NHOM32/tools/view_qrcodes.php\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Vui lòng đảm bảo đã import database_schema.sql trước!\n";
}

