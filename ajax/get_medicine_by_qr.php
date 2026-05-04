<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/models/Medicine.php';
require_once $basePath . '/helpers/secure_session.php';

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
    // Tìm theo QR code trong bảng batches trước
    $sql = "SELECT b.*, m.medicine_name, m.price, m.description, c.category_name, u.unit_name, s.supplier_name
            FROM batches b
            LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
            LEFT JOIN categories c ON m.category_id = c.category_id
            LEFT JOIN units u ON m.unit_id = u.unit_id
            LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
            WHERE b.qr_code = ?";
    
    $db = Database::getInstance();
    $stmt = $db->query($sql, [$qrCode]);
    $batch = $stmt->fetch();
    
    if (!$batch) {
        // Fallback: tìm trong bảng medicines
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
    } else {
        // Trả về thông tin batch
        echo json_encode([
            'success' => true,
            'batch' => $batch,
            'medicine' => [
                'medicine_id' => $batch['medicine_id'],
                'medicine_name' => $batch['medicine_name'],
                'price' => $batch['price'] ?? 0,
                'inventory' => $batch['quantity']
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
