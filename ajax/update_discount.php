<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/auth.php';
require_once $basePath . '/models/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$invoiceId = (int)($_POST['invoice_id'] ?? 0);
$discount = (float)($_POST['discount'] ?? 0);

if ($invoiceId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
    exit;
}

if ($discount < 0) {
    echo json_encode(['success' => false, 'message' => 'Giảm giá không hợp lệ']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Cập nhật discount và final_amount
    $sql = "UPDATE invoices SET 
            discount = ?,
            final_amount = total_amount - ?
            WHERE invoice_id = ?";
    $db->execute($sql, [$discount, $discount, $invoiceId]);
    
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật giảm giá']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
