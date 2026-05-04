<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/secure_session.php';
require_once $basePath . '/helpers/audit.php';
require_once $basePath . '/models/Database.php';
require_once $basePath . '/helpers/audit.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$detailId = intval($_POST['detail_id'] ?? 0);

if ($detailId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Kiểm tra xem detail có thuộc về user hiện tại và đơn hàng chưa thanh toán không
    $sql = "SELECT id.invoice_id 
            FROM invoice_details id
            JOIN invoices i ON id.invoice_id = i.invoice_id
            WHERE id.detail_id = ? AND i.user_id = ? AND i.payment_method IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$detailId, $_SESSION['user_id']]);
    $detail = $stmt->fetch();
    
    if (!$detail) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong đơn hàng']);
        exit;
    }
    
    $conn->beginTransaction();
    
    // Lấy thông tin chi tiết trước khi xóa để ghi log
    $sql = "SELECT id.*, m.medicine_name 
            FROM invoice_details id
            JOIN medicines m ON id.medicine_id = m.medicine_id
            WHERE id.detail_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$detailId]);
    $detailInfo = $stmt->fetch();
    
    // Xóa chi tiết đơn hàng
    $sql = "DELETE FROM invoice_details WHERE detail_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$detailId]);
    
    // Cập nhật lại tổng tiền của invoice
    $invoiceId = $detail['invoice_id'];
    $sql = "UPDATE invoices SET 
            total_amount = COALESCE((SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?), 0),
            final_amount = COALESCE((SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?), 0) - discount
            WHERE invoice_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$invoiceId, $invoiceId, $invoiceId]);
    
    $conn->commit();
    
    // GHI LOG XÓA SẢN PHẨM KHỎI ĐƠN HÀNG
    if ($detailInfo) {
        try {
            auditDelete('invoice_details', $detailId, [
                'medicine_name' => $detailInfo['medicine_name'],
                'quantity' => $detailInfo['quantity'],
                'unit_price' => $detailInfo['unit_price'],
                'subtotal' => $detailInfo['subtotal'],
                'invoice_id' => $detailInfo['invoice_id']
            ]);
        } catch (Exception $auditError) {
            error_log("Audit log error: " . $auditError->getMessage());
            // Không throw exception để không ảnh hưởng đến việc xóa
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa sản phẩm khỏi đơn hàng'
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error removing from cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
