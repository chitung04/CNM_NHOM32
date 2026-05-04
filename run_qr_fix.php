<?php
/**
 * Script để chạy fix QR codes
 */

// Bắt đầu output buffering để có thể hiển thị progress
ob_start();

echo "🔧 Bắt đầu sửa QR codes...\n";
flush();

try {
    require_once 'config/database.php';
    require_once 'helpers/qrcode.php';
    
    $db = Database::getInstance();
    
    // Tạo thư mục QR nếu chưa có
    if (!file_exists('assets/qrcodes')) {
        mkdir('assets/qrcodes', 0777, true);
        echo "✅ Đã tạo thư mục assets/qrcodes\n";
    }
    
    // Lấy tất cả medicines
    $sql = "SELECT medicine_id, medicine_name, qr_code FROM medicines ORDER BY medicine_id ASC";
    $stmt = $db->query($sql);
    $medicines = $stmt->fetchAll();
    
    $fixed = 0;
    $created = 0;
    $errors = 0;
    
    echo "📊 Tìm thấy " . count($medicines) . " medicines\n";
    
    foreach ($medicines as $medicine) {
        $medicineId = $medicine['medicine_id'];
        $medicineName = $medicine['medicine_name'];
        $currentQR = $medicine['qr_code'];
        
        echo "Xử lý: ID {$medicineId} - {$medicineName}\n";
        
        // Nếu chưa có QR code, tạo mới
        if (empty($currentQR)) {
            $newQR = 'MED_' . time() . '_' . $medicineId;
            $qrUrl = "http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id={$medicineId}&qr={$newQR}";
            
            if (generateQRCode($qrUrl, $newQR)) {
                // Cập nhật vào database
                $updateSql = "UPDATE medicines SET qr_code = ? WHERE medicine_id = ?";
                $updateStmt = $db->prepare($updateSql);
                
                if ($updateStmt->execute([$newQR, $medicineId])) {
                    echo "  ✅ Tạo QR mới: {$newQR}\n";
                    $created++;
                } else {
                    echo "  ❌ Lỗi cập nhật DB\n";
                    $errors++;
                }
            } else {
                echo "  ❌ Lỗi tạo QR image\n";
                $errors++;
            }
        } else {
            // Kiểm tra file QR có tồn tại không
            $qrFile = "assets/qrcodes/{$currentQR}.png";
            if (!file_exists($qrFile)) {
                // Tạo lại file QR
                $qrUrl = "http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id={$medicineId}&qr={$currentQR}";
                
                if (generateQRCode($qrUrl, $currentQR)) {
                    echo "  ✅ Tạo lại file QR: {$currentQR}\n";
                    $fixed++;
                } else {
                    echo "  ❌ Lỗi tạo lại QR image\n";
                    $errors++;
                }
            } else {
                echo "  ✓ QR đã có: {$currentQR}\n";
            }
        }
        
        // Delay nhỏ để tránh spam API
        usleep(100000); // 0.1 giây
        flush();
    }
    
    echo "\n📈 Kết quả:\n";
    echo "- QR codes đã tạo mới: {$created}\n";
    echo "- QR codes đã sửa: {$fixed}\n";
    echo "- Lỗi: {$errors}\n";
    echo "\n✅ Hoàn thành!\n";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

ob_end_flush();
?>