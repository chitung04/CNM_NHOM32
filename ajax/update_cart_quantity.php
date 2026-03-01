<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/helpers/auth.php';
require_once $basePath . '/models/Medicine.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$key = $_POST['key'] ?? '';
$quantity = intval($_POST['quantity'] ?? 0);

if (empty($key) || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

if (!isset($_SESSION['cart'][$key])) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng']);
    exit;
}

try {
    // Kiểm tra tồn kho
    $medicineModel = new Medicine();
    $medicineId = $_SESSION['cart'][$key]['medicine_id'];
    $inventory = $medicineModel->getTotalInventory($medicineId);
    
    if ($quantity > $inventory) {
        echo json_encode([
            'success' => false, 
            'message' => "Không đủ hàng trong kho. Tồn kho: $inventory"
        ]);
        exit;
    }
    
    // Cập nhật số lượng
    $_SESSION['cart'][$key]['quantity'] = $quantity;
    
    // Tính lại tổng tiền
    $totalAmount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalAmount += $item['price'] * $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'cart' => $_SESSION['cart'],
        'totalAmount' => $totalAmount
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
