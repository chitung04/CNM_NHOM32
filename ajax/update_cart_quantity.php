<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/secure_session.php';
require_once $basePath . '/helpers/audit.php';
require_once $basePath . '/models/Database.php';
require_once $basePath . '/models/Medicine.php';
require_once $basePath . '/helpers/audit.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$detailId = intval($_POST['detail_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);

if ($detailId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $medicineModel = new Medicine();
    
    // Lấy thông tin chi tiết đơn hàng
    $sql = "SELECT id.*, m.medicine_name, m.price 
            FROM invoice_details id
            JOIN medicines m ON id.medicine_id = m.medicine_id
            JOIN invoices i ON id.invoice_id = i.invoice_id
            WHERE id.detail_id = ? AND i.user_id = ? AND i.payment_method IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$detailId, $_SESSION['user_id']]);
    $detail = $stmt->fetch();
    
    if (!$detail) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong đơn hàng']);
        exit;
    }
    
    // Kiểm tra tồn kho
    $inventory = $medicineModel->getTotalInventory($detail['medicine_id']);
    if ($quantity > $inventory) {
        echo json_encode([
            'success' => false, 
            'message' => "Không đủ hàng trong kho. Tồn kho: $inventory"
        ]);
        exit;
    }
    
    $conn->beginTransaction();
    
    // Cập nhật số lượng và subtotal trong invoice_details
    $newSubtotal = $detail['price'] * $quantity;
    $sql = "UPDATE invoice_details SET quantity = ?, subtotal = ? WHERE detail_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$quantity, $newSubtotal, $detailId]);
    
    // Cập nhật tổng tiền của invoice
    $invoiceId = $detail['invoice_id'];
    $sql = "UPDATE invoices SET 
            total_amount = (SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?),
            final_amount = (SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?) - discount
            WHERE invoice_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$invoiceId, $invoiceId, $invoiceId]);
    
    $conn->commit();
    
    // GHI LOG CẬP NHẬT SỐ LƯỢNG SẢN PHẨM
    try {
        auditUpdate('invoice_details', $detailId, [
            'quantity' => $detail['quantity'],
            'subtotal' => $detail['subtotal']
        ], [
            'quantity' => $quantity,
            'subtotal' => $newSubtotal
        ]);
    } catch (Exception $auditError) {
        error_log("Audit log error: " . $auditError->getMessage());
        // Không throw exception để không ảnh hưởng đến việc cập nhật
    }
    
    // GHI LOG CẬP NHẬT SỐ LƯỢNG
    try {
        require_once $basePath . '/helpers/audit.php';
        auditLog('UPDATE_CART_QUANTITY', 'invoice_details', $detailId, 
            ['quantity' => $detail['quantity']], 
            ['quantity' => $quantity]
        );
    } catch (Exception $e) {
        error_log("Error logging quantity update: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật số lượng thành công'
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error updating cart quantity: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
