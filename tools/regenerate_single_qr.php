<?php
// Tool để tạo lại QR code cho một lô thuốc cụ thể
session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../helpers/qrcode.php';
require_once '../models/Database.php';

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
    
    if (empty($batch['qr_code'])) {
        $_SESSION['error'] = "Lô thuốc này chưa có mã QR";
        header('Location: ../index.php?page=batches&action=view&id=' . $batch_id);
        exit;
    }
    
    // Xóa file QR code cũ nếu có
    deleteQRCode($batch['qr_code'] . '.png');
    
    // Tạo URL dẫn đến trang thông tin thuốc
    $qrData = generateMedicineQRData($batch_id, $batch['qr_code'], $batch['medicine_id']);
    
    // Tạo lại file QR code
    $result = generateQRCode($qrData, $batch['qr_code']);
    
    if ($result) {
        $_SESSION['success'] = "Tạo lại QR code thành công cho lô thuốc #{$batch_id}";
    } else {
        $_SESSION['error'] = "Có lỗi khi tạo lại QR code";
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi: " . $e->getMessage();
}

header('Location: ../index.php?page=batches&action=view&id=' . $batch_id);
exit;