<?php
/**
 * AJAX endpoint để lấy chi tiết thuốc
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Medicine.php';
require_once __DIR__ . '/../models/Database.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['medicine_id']) || !is_numeric($_POST['medicine_id'])) {
        throw new Exception('ID thuốc không hợp lệ');
    }
    
    $medicineId = (int)$_POST['medicine_id'];
    $medicineModel = new Medicine();
    
    // Lấy thông tin thuốc
    $medicine = $medicineModel->getById($medicineId);
    if (!$medicine) {
        throw new Exception('Không tìm thấy thuốc');
    }
    
    // Lấy tổng tồn kho
    $totalInventory = $medicineModel->getTotalInventory($medicineId);
    
    // Lấy thông tin các lô thuốc
    $db = Database::getInstance();
    $sql = "SELECT b.*, s.supplier_name,
                   DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
            FROM batches b
            LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
            WHERE b.medicine_id = ? AND b.status = 'active' AND b.quantity > 0
            ORDER BY b.expiry_date ASC";
    $stmt = $db->query($sql, [$medicineId]);
    $batches = $stmt->fetchAll();
    
    // Lấy lịch sử bán hàng gần đây
    $sql = "SELECT id.*, i.invoice_number, i.created_at
            FROM invoice_details id
            JOIN invoices i ON id.invoice_id = i.invoice_id
            WHERE id.medicine_id = ?
            ORDER BY i.created_at DESC
            LIMIT 10";
    $stmt = $db->query($sql, [$medicineId]);
    $salesHistory = $stmt->fetchAll();
    
    // Tính toán thống kê
    $totalSold = 0;
    $totalRevenue = 0;
    if (!empty($salesHistory)) {
        $totalSold = array_sum(array_column($salesHistory, 'quantity'));
        $totalRevenue = array_sum(array_column($salesHistory, 'subtotal'));
    }
    
    echo json_encode([
        'success' => true,
        'medicine' => $medicine,
        'totalInventory' => $totalInventory,
        'batches' => $batches,
        'salesHistory' => $salesHistory,
        'statistics' => [
            'totalSold' => $totalSold,
            'totalRevenue' => $totalRevenue
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>