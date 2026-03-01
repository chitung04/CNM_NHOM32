<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/auth.php';
require_once $basePath . '/models/Medicine.php';

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$keyword = $_POST['keyword'] ?? '';

if (empty($keyword)) {
    echo json_encode(['success' => false, 'medicines' => []]);
    exit;
}

// Giới hạn độ dài keyword
if (strlen($keyword) > 100) {
    echo json_encode(['success' => false, 'message' => 'Keyword too long']);
    exit;
}

$medicineModel = new Medicine();
$medicines = $medicineModel->search($keyword);

// Thêm thông tin tồn kho
foreach ($medicines as &$medicine) {
    $medicine['inventory'] = $medicineModel->getTotalInventory($medicine['medicine_id']);
}

echo json_encode([
    'success' => true,
    'medicines' => $medicines
]);
