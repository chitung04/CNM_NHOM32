<?php
/**
 * Test đơn giản để thêm sản phẩm vào đơn hàng
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/auth.php';
require_once 'models/Medicine.php';
require_once 'models/Batch.php';
require_once 'models/Database.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    die("Vui lòng đăng nhập trước");
}

echo "<h2>Test Thêm Sản Phẩm Vào Đơn Hàng</h2>";

try {
    $db = Database::getInstance();
    $medicineModel = new Medicine();
    
    // Lấy thuốc đầu tiên
    $medicines = $medicineModel->getAll();
    if (empty($medicines)) {
        die("Không có thuốc nào trong hệ thống");
    }
    
    $medicine = $medicines[0];
    echo "<p>Thuốc: <strong>" . htmlspecialchars($medicine['medicine_name']) . "</strong></p>";
    echo "<p>Giá: " . number_format($medicine['price']) . "đ</p>";
    
    // Kiểm tra tồn kho
    $inventory = $medicineModel->getTotalInventory($medicine['medicine_id']);
    echo "<p>Tồn kho: <strong>$inventory</strong></p>";
    
    if ($inventory <= 0) {
        die("<p style='color: red;'>Thuốc này không còn hàng</p>");
    }
    
    // Kiểm tra batch
    $batchModel = new Batch();
    $batches = $batchModel->getByMedicine($medicine['medicine_id']);
    
    echo "<p>Số lô thuốc: " . count($batches) . "</p>";
    
    $validBatches = array_filter($batches, function($batch) {
        return $batch['status'] === 'active' && 
               $batch['quantity'] > 0 && 
               strtotime($batch['expiry_date']) > time();
    });
    
    if (empty($validBatches)) {
        die("<p style='color: red;'>Không có lô thuốc khả dụng</p>");
    }
    
    usort($validBatches, function($a, $b) {
        return strtotime($a['expiry_date']) - strtotime($b['expiry_date']);
    });
    
    $selectedBatch = $validBatches[0];
    echo "<p>Lô được chọn: #" . $selectedBatch['batch_id'] . " (Số lượng: " . $selectedBatch['quantity'] . ")</p>";
    
    // Kiểm tra invoice hiện tại
    $invoiceId = $_SESSION['current_invoice_id'] ?? null;
    echo "<p>Invoice ID hiện tại: " . ($invoiceId ?? 'Chưa có') . "</p>";
    
    if (!$invoiceId) {
        echo "<h3>Tạo invoice mới</h3>";
        
        $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);
        $userId = $_SESSION['user_id'];
        $qrCode = 'INV_' . time() . '_' . rand(1000, 9999);
        
        echo "<p>Invoice Number: $invoiceNumber</p>";
        echo "<p>QR Code: $qrCode</p>";
        
        $sql = "INSERT INTO invoices (invoice_number, user_id, total_amount, discount, final_amount, qr_code) 
                VALUES (?, ?, 0, 0, 0, ?)";
        
        $result = $db->execute($sql, [$invoiceNumber, $userId, $qrCode]);
        
        if ($result) {
            $invoiceId = $db->lastInsertId();
            $_SESSION['current_invoice_id'] = $invoiceId;
            echo "<p style='color: green;'>✓ Tạo invoice thành công! ID: $invoiceId</p>";
        } else {
            die("<p style='color: red;'>❌ Không thể tạo invoice</p>");
        }
    }
    
    // Thêm sản phẩm vào invoice_details
    echo "<h3>Thêm sản phẩm vào đơn hàng</h3>";
    
    $quantity = 1;
    $unitPrice = (float)$medicine['price'];
    $subtotal = $unitPrice * $quantity;
    
    $sql = "INSERT INTO invoice_details (invoice_id, medicine_id, batch_id, quantity, unit_price, subtotal) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $result = $db->execute($sql, [
        $invoiceId, 
        $medicine['medicine_id'], 
        $selectedBatch['batch_id'], 
        $quantity, 
        $unitPrice, 
        $subtotal
    ]);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Thêm sản phẩm thành công!</p>";
        
        // Cập nhật tổng tiền
        $sql = "UPDATE invoices SET 
                total_amount = (SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?),
                final_amount = (SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?) - discount
                WHERE invoice_id = ?";
        $db->execute($sql, [$invoiceId, $invoiceId, $invoiceId]);
        
        // Trừ số lượng batch
        $sql = "UPDATE batches SET quantity = quantity - ? WHERE batch_id = ?";
        $db->execute($sql, [$quantity, $selectedBatch['batch_id']]);
        
        echo "<p style='color: green;'>✓ Cập nhật tồn kho thành công!</p>";
        echo "<p><a href='index.php?page=sales'>→ Xem đơn hàng</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Không thể thêm sản phẩm</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
h2, h3 { color: #333; }
p { margin: 10px 0; }
</style>
