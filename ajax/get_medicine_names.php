<?php
/**
 * Lấy danh sách tên thuốc để hiển thị trong popup
 */

session_start();

$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/pharmacy.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'medicines' => []
    ]);
    exit;
}

$db = Database::getInstance();

try {
    // Lấy pharmacy_id
    $pharmacyId = getCurrentPharmacyId();
    
    if (!$pharmacyId) {
        echo json_encode([
            'success' => false,
            'medicines' => []
        ]);
        exit;
    }
    
    // Lấy danh sách thuốc (giới hạn 20 thuốc)
    $sql = "SELECT medicine_name, price 
            FROM medicines 
            WHERE pharmacy_id = ?
            ORDER BY medicine_name ASC
            LIMIT 20";
    
    $stmt = $db->query($sql, [$pharmacyId]);
    $medicines = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'medicines' => $medicines
    ]);
    
} catch (Exception $e) {
    error_log("Get medicine names error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'medicines' => []
    ]);
}
