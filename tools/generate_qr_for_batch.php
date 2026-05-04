<?php
// Tool để tạo QR code cho một lô thuốc cụ thể
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../helpers/qrcode.php';
require_once '../models/Database.php';
require_once '../models/Batch.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=batches');
    exit;
}

$batch_id = $_POST['batch_id'] ?? 0;

if (!$batch_id) {
    $_SESSION['error'] = "ID lô thuốc không hợp lệ";
    header('Location: ../index.php?page=batches');
    exit;
}

try {
    $db = Database::getInstance();
    
    // Lấy thông tin lô thuốc
    $sql = "SELECT * FROM batches WHERE batch_id = ?";
    $stmt = $db->query($sql, [$batch_id]);
    $batch = $stmt->fetch();
    
    if (!$batch) {
        $_SESSION['error'] = "Không tìm thấy lô thuốc";
        header('Location: ../index.php?page=batches');
        exit;
    }
    
    // Tạo QR code nếu chưa có
    if (empty($batch['qr_code'])) {
        $qrCode = generateUniqueQRCode('BATCH');
        
        // Cập nhật QR code vào database
        $updateSql = "UPDATE batches SET qr_code = ? WHERE batch_id = ?";
        $db->execute($updateSql, [$qrCode, $batch_id]);
        
        $batch['qr_code'] = $qrCode;
    }
    
    // Tạo URL dẫn đến trang thông tin thuốc
    $qrData = generateMedicineQRData($batch_id, $batch['qr_code'], $batch['medicine_id']);
    
    // Tạo file QR code
    $result = generateQRCode($qrData, $batch['qr_code']);
    
    if ($result) {
        $_SESSION['success'] = "Tạo QR code thành công cho lô thuốc #{$batch_id}";
    } else {
        $_SESSION['error'] = "Có lỗi khi tạo QR code";
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi: " . $e->getMessage();
}

header('Location: ../index.php?page=batches&action=view&id=' . $batch_id);
exit;