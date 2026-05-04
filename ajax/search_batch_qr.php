<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/secure_session.php';
require_once $basePath . '/models/Database.php';

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
    echo json_encode(['success' => false, 'batches' => []]);
    exit;
}

// Giới hạn độ dài keyword
if (strlen($keyword) > 100) {
    echo json_encode(['success' => false, 'message' => 'Keyword too long']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Tìm kiếm trong batches theo tên thuốc, QR code, batch number
    $sql = "SELECT b.batch_id, b.batch_number, b.qr_code, b.quantity, b.status,
                   m.medicine_name, m.price, c.category_name, s.supplier_name,
                   DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
            FROM batches b
            LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
            LEFT JOIN categories c ON m.category_id = c.category_id
            LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
            WHERE (m.medicine_name LIKE ? 
                   OR b.qr_code LIKE ? 
                   OR b.batch_number LIKE ?
                   OR s.supplier_name LIKE ?)
            ORDER BY b.batch_id DESC
            LIMIT 10";
    
    $searchTerm = '%' . $keyword . '%';
    $stmt = $db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $batches = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'batches' => $batches
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}