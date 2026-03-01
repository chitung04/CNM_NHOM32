<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/auth.php';
require_once $basePath . '/models/Database.php';
require_once $basePath . '/models/Medicine.php';
require_once $basePath . '/models/Batch.php';

header('Content-Type: application/json');

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
$data = json_decode($json, true);

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
    
    // Không xóa đơn cũ nữa - mỗi lần tạo là đơn mới
    // Xóa session để không conflict
    unset($_SESSION['current_invoice_id']);
    
    $conn->beginTransaction();
    
    // Tạo invoice mới
    $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);
    
    // Tạo QR code
    require_once $basePath . '/helpers/qrcode.php';
    $qrCode = generateUniqueQRCode('INV');
    
    $sql = "INSERT INTO invoices (invoice_number, user_id, total_amount, discount, final_amount, qr_code) 
            VALUES (?, ?, 0, 0, 0, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $invoiceNumber,
        $_SESSION['user_id'],
        $qrCode
    ]);
    
    $invoiceId = $conn->lastInsertId();
    
    $totalAmount = 0;
    
    // Thêm từng sản phẩm vào invoice
    foreach ($data['items'] as $item) {
        $medicineId = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        
        // Kiểm tra tồn kho
        $inventory = $medicineModel->getTotalInventory($medicineId);
        if ($inventory < $quantity) {
            throw new Exception("Thuốc '{$item['name']}' không đủ hàng. Tồn kho: {$inventory}");
        }
        
        // Lấy batch còn hàng (FIFO)
        $batches = $batchModel->getByMedicine($medicineId);
        $activeBatches = array_filter($batches, function($b) {
            return $b['status'] === 'active' && $b['quantity'] > 0;
        });
        
        if (empty($activeBatches)) {
            throw new Exception("Không có lô thuốc khả dụng cho '{$item['name']}'");
        }
        
        $batch = reset($activeBatches);
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
    }
    
    // Cập nhật tổng tiền của invoice
    $finalAmount = $totalAmount - $discount;
    $sql = "UPDATE invoices SET total_amount = ?, discount = ?, final_amount = ? WHERE invoice_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$totalAmount, $discount, $finalAmount, $invoiceId]);
    
    $conn->commit();
    
    // Lưu invoice ID vào session
    $_SESSION['current_invoice_id'] = $invoiceId;
    
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
