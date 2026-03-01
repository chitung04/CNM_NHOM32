<?php
session_start();

// Đường dẫn tuyệt đối
$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/helpers/auth.php';
require_once $basePath . '/models/Medicine.php';
require_once $basePath . '/models/Batch.php';
require_once $basePath . '/models/Invoice.php';
require_once $basePath . '/models/Database.php';

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

// Validate và sanitize input
$medicineId = (int)($_POST['medicine_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if ($medicineId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID thuốc không hợp lệ']);
    exit;
}

if ($quantity <= 0 || $quantity > 1000) {
    echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
    exit;
}

try {
    $db = Database::getInstance();
    $medicineModel = new Medicine();
    $batchModel = new Batch();
    
    // Log để debug
    error_log("Add to cart - Medicine ID: $medicineId, Quantity: $quantity");
    
    $medicine = $medicineModel->getById($medicineId);
    if (!$medicine) {
        error_log("Medicine not found: $medicineId");
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thuốc']);
        exit;
    }
    
    error_log("Medicine found: " . $medicine['medicine_name']);
    
    $inventory = $medicineModel->getTotalInventory($medicineId);
    error_log("Inventory: $inventory");
    
    if ($inventory < $quantity) {
        echo json_encode(['success' => false, 'message' => "Không đủ hàng. Tồn kho: $inventory"]);
        exit;
    }
    
    // Lấy batch còn hàng và chưa hết hạn (FIFO)
    $batches = $batchModel->getByMedicine($medicineId);
    $validBatches = array_filter($batches, function($batch) {
        return $batch['status'] === 'active' && 
               $batch['quantity'] > 0 && 
               strtotime($batch['expiry_date']) > time();
    });
    
    if (empty($validBatches)) {
        echo json_encode(['success' => false, 'message' => 'Không có lô thuốc khả dụng']);
        exit;
    }
    
    // Sắp xếp theo ngày hết hạn (FIFO)
    usort($validBatches, function($a, $b) {
        return strtotime($a['expiry_date']) - strtotime($b['expiry_date']);
    });
    
    $selectedBatch = $validBatches[0];
    
    // Kiểm tra số lượng batch có đủ không
    if ($selectedBatch['quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => "Lô này chỉ còn {$selectedBatch['quantity']} sản phẩm"]);
        exit;
    }
    
    // Kiểm tra xem user có invoice đang mở không (trong session)
    $invoiceId = $_SESSION['current_invoice_id'] ?? null;
    
    if (!$invoiceId) {
        // Tạo invoice mới
        $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);
        $userId = $_SESSION['user_id'];
        
        // Tạo QR code cho invoice
        require_once $basePath . '/helpers/qrcode.php';
        $qrCode = generateUniqueQRCode('INV');
        
        $sql = "INSERT INTO invoices (invoice_number, user_id, total_amount, discount, final_amount, qr_code) 
                VALUES (?, ?, 0, 0, 0, ?)";
        $db->execute($sql, [$invoiceNumber, $userId, $qrCode]);
        $invoiceId = $db->lastInsertId();
        
        // Lưu invoice ID vào session
        $_SESSION['current_invoice_id'] = $invoiceId;
        
        // Tạo file QR code (không bắt buộc phải thành công)
        try {
            $qrData = "INVOICE:$invoiceNumber|ID:$invoiceId";
            generateQRCode($qrData, $qrCode);
        } catch (Exception $e) {
            // Log lỗi nhưng vẫn tiếp tục
            error_log("QR Code generation failed: " . $e->getMessage());
        }
    }
    
    // Thêm sản phẩm vào invoice_details
    $unitPrice = (float)$medicine['price'];
    $subtotal = $unitPrice * $quantity;
    
    $sql = "INSERT INTO invoice_details (invoice_id, medicine_id, batch_id, quantity, unit_price, subtotal) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $db->execute($sql, [$invoiceId, $medicineId, $selectedBatch['batch_id'], $quantity, $unitPrice, $subtotal]);
    
    // Cập nhật tổng tiền invoice
    $sql = "UPDATE invoices SET 
            total_amount = (SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?),
            final_amount = (SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?) - discount
            WHERE invoice_id = ?";
    $db->execute($sql, [$invoiceId, $invoiceId, $invoiceId]);
    
    // Trừ số lượng trong batch
    $sql = "UPDATE batches SET quantity = quantity - ? WHERE batch_id = ?";
    $db->execute($sql, [$quantity, $selectedBatch['batch_id']]);
    
    // Cập nhật trạng thái batch nếu hết hàng
    $sql = "UPDATE batches SET status = 'sold_out' WHERE batch_id = ? AND quantity = 0";
    $db->execute($sql, [$selectedBatch['batch_id']]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm vào đơn hàng',
        'invoice_id' => $invoiceId
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

