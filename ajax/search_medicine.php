<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/secure_session.php';
require_once $basePath . '/models/Medicine.php';

// Set content type to JSON
header('Content-Type: application/json');

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

try {
    $medicineModel = new Medicine();
    
    // Sử dụng method suggestions cho AJAX autocomplete
    $medicines = $medicineModel->searchSuggestions($keyword, 10);
    
    // Thêm thông tin tồn kho cho mỗi thuốc
    foreach ($medicines as &$medicine) {
        if (!isset($medicine['inventory'])) {
            $medicine['inventory'] = $medicineModel->getTotalInventory($medicine['medicine_id']);
        }
    }
    
    echo json_encode([
        'success' => true,
        'medicines' => $medicines
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
