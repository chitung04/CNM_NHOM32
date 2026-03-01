<?php
/**
 * Public endpoint để test QR code (không cần đăng nhập)
 * CHỈ DÙNG ĐỂ TEST - XÓA TRONG PRODUCTION
 */

require_once '../config/config.php';
require_once '../models/Medicine.php';

header('Content-Type: application/json');

// Lấy QR code từ request
$qrCode = $_POST['qr_code'] ?? $_GET['qr_code'] ?? '';

if (empty($qrCode)) {
    echo json_encode(['success' => false, 'message' => 'Mã QR không hợp lệ']);
    exit;
}

try {
    $medicineModel = new Medicine();
    $medicine = $medicineModel->getByQRCode($qrCode);
    
    if (!$medicine) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thuốc với mã QR này: ' . $qrCode]);
        exit;
    }
    
    // Lấy tồn kho
    $inventory = $medicineModel->getTotalInventory($medicine['medicine_id']);
    $medicine['total_inventory'] = $inventory;
    
    echo json_encode([
        'success' => true,
        'medicine' => $medicine
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
