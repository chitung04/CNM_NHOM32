<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/secure_session.php';
require_once $basePath . '/helpers/audit_simple.php';
require_once $basePath . '/models/Database.php';
require_once $basePath . '/models/Medicine.php';
require_once $basePath . '/models/Batch.php';
require_once $basePath . '/helpers/audit.php';

header('Content-Type: application/json');

// Log để debug
error_log("Create order request received");

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

// Đọc JSON data
$json = file_get_contents('php://input');
error_log("Received JSON: " . $json);

$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

if (!isset($data['items']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Không có sản phẩm nào']);
    exit;
}

$discount = isset($data['discount']) ? (float)$data['discount'] : 0;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $medicineModel = new Medicine();
    $batchModel = new Batch();
    
    // Lấy pharmacy_id từ session
    require_once $basePath . '/helpers/pharmacy.php';
    $pharmacyId = getCurrentPharmacyId();
    
    if (!$pharmacyId) {
        throw new Exception("Không tìm thấy pharmacy_id");
    }
    
    // Không xóa đơn cũ nữa - mỗi lần tạo là đơn mới
    // Xóa session để không conflict
    unset($_SESSION['current_invoice_id']);
    
    $conn->beginTransaction();
    
    // Tạo invoice mới
    $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);
    
    // Tạo QR code đơn giản
    $qrCode = 'INV_' . time() . '_' . rand(1000, 9999);
    
    // Tạo invoice - đặt payment_method = NULL để đánh dấu chưa thanh toán
    // Không dùng discount và final_amount nếu cột không tồn tại
    $sql = "INSERT INTO invoices (invoice_number, user_id, total_amount, qr_code, payment_method, pharmacy_id) 
            VALUES (?, ?, 0, ?, NULL, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $invoiceNumber,
        $_SESSION['user_id'],
        $qrCode,
        $pharmacyId
    ]);
    
    $invoiceId = $conn->lastInsertId();
    error_log("Created invoice ID: " . $invoiceId);
    
    $totalAmount = 0;
    $finalAmount = 0; // Khởi tạo biến
    
    // Thêm từng sản phẩm vào invoice
    foreach ($data['items'] as $item) {
        $medicineId = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        
        // Kiểm tra tồn kho
        $inventory = $medicineModel->getTotalInventory($medicineId);
        if ($inventory < $quantity) {
            throw new Exception("Thuốc '{$item['name']}' không đủ hàng. Tồn kho: {$inventory}");
        }
        
        // Lấy batch còn hàng (FIFO) - sử dụng query đơn giản
        $sql = "SELECT * FROM batches WHERE medicine_id = ? AND status = 'active' AND quantity > 0 ORDER BY expiry_date ASC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$medicineId]);
        $batch = $stmt->fetch();
        
        if (!$batch) {
            throw new Exception("Không có lô thuốc khả dụng cho '{$item['name']}'");
        }
        
        // Kiểm tra lô có đủ số lượng không
        if ($batch['quantity'] < $quantity) {
            throw new Exception("Lô thuốc '{$item['name']}' không đủ số lượng. Còn lại: {$batch['quantity']}");
        }
        
        $price = (float)$item['price'];
        $subtotal = $price * $quantity;
        $totalAmount += $subtotal;
        
        // Insert invoice detail
        $sql = "INSERT INTO invoice_details (invoice_id, medicine_id, batch_id, quantity, unit_price, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $invoiceId,
            $medicineId,
            $batch['batch_id'],
            $quantity,
            $price,
            $subtotal
        ]);
        
        // QUAN TRỌNG: Trừ số lượng từ batch ngay khi thêm vào đơn hàng
        $sql = "UPDATE batches SET quantity = quantity - ? WHERE batch_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$quantity, $batch['batch_id']]);
        
        // Cập nhật status nếu hết hàng
        $sql = "UPDATE batches SET status = 'sold_out' WHERE batch_id = ? AND quantity = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$batch['batch_id']]);
        
        error_log("Added item and updated inventory: " . $item['name'] . " x " . $quantity);
    }
    
    // Cập nhật tổng tiền của invoice
    $finalAmount = $totalAmount - $discount;
    $sql = "UPDATE invoices SET total_amount = ? WHERE invoice_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$totalAmount, $invoiceId]);
    
    $conn->commit();
    
    // Lưu invoice ID vào session
    $_SESSION['current_invoice_id'] = $invoiceId;
    
    // GHI LOG TẠO ĐƠN HÀNG VỚI SẢN PHẨM
    auditCreate('invoices', $invoiceId, [
        'invoice_number' => $invoiceNumber,
        'user_id' => $_SESSION['user_id'],
        'total_amount' => $totalAmount,
        'items_count' => count($data['items']),
        'qr_code' => $qrCode
    ]);
    
    // GHI LOG TẠO ĐƠN HÀNG VỚI SẢN PHẨM
    try {
        require_once $basePath . '/helpers/audit.php';
        auditLog('CREATE_ORDER_WITH_ITEMS', 'invoices', $invoiceId, null, [
            'invoice_number' => $invoiceNumber,
            'total_amount' => $totalAmount,
            'final_amount' => $finalAmount,
            'discount' => $discount,
            'items_count' => count($data['items']),
            'items' => array_map(function($item) {
                return [
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ];
            }, $data['items'])
        ]);
    } catch (Exception $e) {
        error_log("Error logging order creation: " . $e->getMessage());
    }
    
    // QUAN TRỌNG: Kiểm tra và tạo thông báo hết hàng sau khi cập nhật tồn kho
    try {
        require_once $basePath . '/models/Notification.php';
        $notificationModel = new Notification();
        
        // Kiểm tra thuốc sắp hết hàng
        $notificationModel->checkLowStock();
        
        // Kiểm tra thuốc sắp hết hạn
        $notificationModel->checkExpiring();
        
        error_log("Notifications checked after inventory update");
    } catch (Exception $e) {
        error_log("Error checking notifications: " . $e->getMessage());
        // Không throw exception vì đây không phải lỗi nghiêm trọng
    }
    
    error_log("Order created successfully: " . $invoiceNumber);
    
    // GHI LOG TẠO ĐƠN HÀNG VỚI SẢN PHẨM - Sử dụng phiên bản safe
    auditCreateSafe('invoices', $invoiceId, [
        'invoice_number' => $invoiceNumber,
        'user_id' => $_SESSION['user_id'],
        'total_amount' => $totalAmount,
        'final_amount' => $finalAmount,
        'items_count' => count($data['items']),
        'discount' => $discount
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã tạo đơn hàng thành công',
        'invoice_id' => $invoiceId,
        'invoice_number' => $invoiceNumber
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error creating order: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
