<?php
/**
 * AJAX endpoint để tạo lại QR codes cho medicines
 */

header('Content-Type: application/json');

// Kiểm tra authentication
require_once '../helpers/secure_session.php';
$secureSession = SecureSession::getInstance();

if (!$secureSession->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Chỉ manager mới được tạo QR
if (!isManager()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền thực hiện']);
    exit;
}

try {
    require_once '../config/database.php';
    require_once '../helpers/qrcode.php';
    
    $medicineId = $_POST['medicine_id'] ?? '';
    $qrCode = $_POST['qr_code'] ?? '';
    $createNew = $_POST['create_new'] ?? false;
    
    if (empty($medicineId)) {
        echo json_encode(['success' => false, 'message' => 'Medicine ID không hợp lệ']);
        exit;
    }
    
    $db = Database::getInstance();
    
    // Lấy thông tin medicine
    $sql = "SELECT * FROM medicines WHERE medicine_id = ?";
    $stmt = $db->query($sql, [$medicineId]);
    $medicine = $stmt->fetch();
    
    if (!$medicine) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thuốc']);
        exit;
    }
    
    // Tạo QR code mới nếu chưa có hoặc yêu cầu tạo mới
    if (empty($qrCode) || $createNew) {
        $qrCode = 'MED_' . time() . '_' . $medicineId;
    }
    
    // Tạo URL cho QR code với IP address
    $qrUrl = "http://192.168.100.98/CNM_NHOM32/medicine_info.php?medicine_id={$medicineId}&qr={$qrCode}";
    
    // Tạo QR code image
    $result = generateQRCode($qrUrl, $qrCode);
    
    if ($result) {
        // Cập nhật QR code vào database
        $updateSql = "UPDATE medicines SET qr_code = ? WHERE medicine_id = ?";
        $updateStmt = $db->prepare($updateSql);
        
        if ($updateStmt->execute([$qrCode, $medicineId])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Đã tạo QR Code thành công',
                'qr_code' => $qrCode,
                'qr_url' => $qrUrl
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật database']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi tạo QR Code image']);
    }
    
} catch (Exception $e) {
    error_log("Regenerate QR error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>