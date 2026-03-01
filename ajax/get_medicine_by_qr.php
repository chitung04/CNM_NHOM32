<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/models/Medicine.php';
require_once $basePath . '/helpers/auth.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

// Lấy QR code từ request
$qrCode = $_POST['qr_code'] ?? '';

if (empty($qrCode)) {
    echo json_encode(['success' => false, 'message' => 'Mã QR không hợp lệ']);
    exit;
}

try {
    $medicineModel = new Medicine();
    $medicine = $medicineModel->getByQRCode($qrCode);
    
    if (!$medicine) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thuốc với mã QR này']);
        exit;
    }
    
    // Lấy tồn kho
    $inventory = $medicineModel->getTotalInventory($medicine['medicine_id']);
    $medicine['inventory'] = $inventory;
    
    echo json_encode([
        'success' => true,
        'medicine' => $medicine
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
