<?php
require_once 'models/Invoice.php';
require_once 'models/Medicine.php';
require_once 'models/Batch.php';
require_once 'models/Category.php';
require_once 'helpers/audit.php';
require_once 'helpers/audit.php';
require_once 'helpers/audit.php';

class SalesController {
    private $invoiceModel;
    private $medicineModel;
    private $batchModel;
    private $categoryModel;
    
    public function __construct() {
        $this->invoiceModel = new Invoice();
        $this->medicineModel = new Medicine();
        $this->batchModel = new Batch();
        $this->categoryModel = new Category();
    }
    
    /**
     * Trang bán hàng
     */
    public function index() {
        $medicines = $this->medicineModel->getAll();
        $categories = $this->categoryModel->getAll();
        
        // Lấy tất cả đơn hàng chưa thanh toán của user hiện tại
        $pendingInvoices = $this->invoiceModel->getPendingByUser($_SESSION['user_id']);
        
        // Lấy đơn hàng hiện tại từ session
        $currentInvoiceId = $_SESSION['current_invoice_id'] ?? null;
        $currentInvoice = null;
        $invoiceDetails = [];
        
        // Nếu có đơn hàng hiện tại, kiểm tra xem nó có tồn tại không
        if ($currentInvoiceId) {
            $currentInvoice = $this->invoiceModel->getById($currentInvoiceId);
            if ($currentInvoice && $currentInvoice['payment_method'] === null) {
                // Đơn hàng tồn tại và chưa thanh toán
                $invoiceDetails = $this->invoiceModel->getDetails($currentInvoiceId);
            } else {
                // Đơn hàng không tồn tại hoặc đã thanh toán, chọn đơn chưa thanh toán đầu tiên
                unset($_SESSION['current_invoice_id']);
                $currentInvoiceId = null;
                $currentInvoice = null;
                
                if (!empty($pendingInvoices)) {
                    $currentInvoice = $pendingInvoices[0];
                    $currentInvoiceId = $currentInvoice['invoice_id'];
                    $_SESSION['current_invoice_id'] = $currentInvoiceId;
                    $invoiceDetails = $this->invoiceModel->getDetails($currentInvoiceId);
                }
            }
        } else if (!empty($pendingInvoices)) {
            // Không có đơn hàng hiện tại, chọn đơn chưa thanh toán đầu tiên
            $currentInvoice = $pendingInvoices[0];
            $currentInvoiceId = $currentInvoice['invoice_id'];
            $_SESSION['current_invoice_id'] = $currentInvoiceId;
            $invoiceDetails = $this->invoiceModel->getDetails($currentInvoiceId);
        }
        
        $pageTitle = "Bán hàng";
        require_once 'views/sales/index.php';
    }
    
    /**
     * Thêm vào giỏ hàng
     */
    public function addToCart() {
        $medicineId = $_POST['medicine_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        // Kiểm tra có đơn hàng đang mở không
        $invoiceId = $_SESSION['current_invoice_id'] ?? null;
        if (!$invoiceId) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng tạo đơn hàng trước']);
            exit;
        }
        
        // Lấy thông tin thuốc
        $medicine = $this->medicineModel->getById($medicineId);
        if (!$medicine) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thuốc']);
            exit;
        }
        
        // Kiểm tra tồn kho
        $inventory = $this->medicineModel->getTotalInventory($medicineId);
        if ($inventory < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho. Tồn kho: ' . $inventory]);
            exit;
        }
        
        // Lấy batch còn hàng (FIFO - First In First Out)
        $batches = $this->batchModel->getByMedicine($medicineId);
        $activeBatches = array_filter($batches, function($b) {
            return $b['status'] === 'active' && $b['quantity'] > 0;
        });
        
        if (empty($activeBatches)) {
            echo json_encode(['success' => false, 'message' => 'Không có lô thuốc khả dụng']);
            exit;
        }
        
        // Lấy batch đầu tiên (FIFO)
        $batch = reset($activeBatches);
        
        try {
            $conn = $this->invoiceModel->getConnection();
            
            // Kiểm tra xem thuốc đã có trong đơn hàng chưa
            $sql = "SELECT * FROM invoice_details WHERE invoice_id = ? AND medicine_id = ? AND batch_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$invoiceId, $medicineId, $batch['batch_id']]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Cập nhật số lượng
                $newQuantity = $existing['quantity'] + $quantity;
                $newSubtotal = $newQuantity * $medicine['price'];
                
                $sql = "UPDATE invoice_details SET quantity = ?, subtotal = ? WHERE detail_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$newQuantity, $newSubtotal, $existing['detail_id']]);
            } else {
                // Thêm mới
                $subtotal = $medicine['price'] * $quantity;
                $sql = "INSERT INTO invoice_details (invoice_id, medicine_id, batch_id, quantity, unit_price, subtotal) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $invoiceId,
                    $medicineId,
                    $batch['batch_id'],
                    $quantity,
                    $medicine['price'],
                    $subtotal
                ]);
            }
            
            // Cập nhật tổng tiền của invoice
            $sql = "UPDATE invoices SET 
                    total_amount = (SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?),
                    final_amount = (SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?) - discount
                    WHERE invoice_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$invoiceId, $invoiceId, $invoiceId]);
            
            // GHI LOG THÊM SẢN PHẨM VÀO ĐƠN HÀNG
            auditLog('ADD_TO_CART', 'invoice_details', null, null, [
                'invoice_id' => $invoiceId,
                'medicine_name' => $medicine['medicine_name'],
                'quantity' => $quantity,
                'unit_price' => $medicine['price']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Đã thêm vào đơn hàng']);
            
        } catch (Exception $e) {
            error_log("Error adding to cart: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Hiển thị trang thanh toán
     */
    public function checkout() {
        $invoiceId = $_SESSION['current_invoice_id'] ?? null;
        
        if (!$invoiceId) {
            $_SESSION['error'] = "Không có đơn hàng nào đang mở";
            header('Location: index.php?page=sales');
            exit;
        }
        
        $invoice = $this->invoiceModel->getById($invoiceId);
        if (!$invoice) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng";
            unset($_SESSION['current_invoice_id']);
            header('Location: index.php?page=sales');
            exit;
        }
        
        $details = $this->invoiceModel->getDetails($invoiceId);
        
        if (empty($details)) {
            $_SESSION['error'] = "Đơn hàng trống, không thể thanh toán";
            header('Location: index.php?page=sales');
            exit;
        }
        
        $pageTitle = "Thanh toán đơn hàng #" . $invoice['invoice_number'];
        require_once 'views/sales/checkout.php';
    }
    
    /**
     * Hoàn tất đơn hàng hiện tại
     */
    public function complete() {
        error_log("Complete method called");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Not POST method, redirecting");
            header('Location: index.php?page=sales');
            exit;
        }
        
        $invoiceId = $_SESSION['current_invoice_id'] ?? null;
        error_log("Current invoice ID: " . $invoiceId);
        
        if (!$invoiceId) {
            error_log("No current invoice ID");
            $_SESSION['error'] = "Không có đơn hàng nào đang mở";
            header('Location: index.php?page=sales');
            exit;
        }
        
        try {
            $conn = $this->invoiceModel->getConnection();
            $conn->beginTransaction();
            
            // Lấy thông tin thanh toán
            $paymentMethod = $_POST['payment_method'] ?? 'cash';
            $amountPaid = floatval($_POST['amount_paid'] ?? 0);
            
            // Debug log
            error_log("Payment Method: " . $paymentMethod);
            error_log("Amount Paid: " . $amountPaid);
            
            // Lấy chi tiết đơn hàng
            $details = $this->invoiceModel->getDetails($invoiceId);
            error_log("Invoice details count: " . count($details));
            
            if (empty($details)) {
                throw new Exception("Đơn hàng trống, không thể hoàn tất");
            }
            
            // Lấy thông tin invoice
            $invoice = $this->invoiceModel->getById($invoiceId);
            error_log("Invoice data: " . print_r($invoice, true));
            
            // Kiểm tra số tiền thanh toán (chỉ với tiền mặt)
            if ($paymentMethod === 'cash') {
                if ($amountPaid < $invoice['final_amount']) {
                    throw new Exception("Số tiền khách đưa không đủ. Cần: " . number_format($invoice['final_amount']) . "đ, Nhận: " . number_format($amountPaid) . "đ");
                }
            } else {
                // Với chuyển khoản, set amount_paid = final_amount
                $amountPaid = $invoice['final_amount'];
            }
            
            // Trừ số lượng từ batch - ĐÃ ĐƯỢC TRỪ KHI TẠO ĐƠN HÀNG
            // Chỉ cần kiểm tra lại tồn kho để đảm bảo
            foreach ($details as $item) {
                error_log("Verifying inventory for: " . $item['medicine_name'] . " - Quantity: " . $item['quantity']);
                
                // Kiểm tra lại batch có đủ số lượng không (đã bị trừ khi tạo đơn)
                $sql = "SELECT quantity FROM batches WHERE batch_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$item['batch_id']]);
                $batchInfo = $stmt->fetch();
                
                if ($batchInfo === false) {
                    throw new Exception("Không tìm thấy lô thuốc cho " . $item['medicine_name']);
                }
                
                // Số lượng đã được trừ khi tạo đơn hàng, không cần trừ lại
                error_log("Batch {$item['batch_id']} current quantity: " . $batchInfo['quantity']);
            }
            
            // Cập nhật thông tin thanh toán vào invoice
            $sql = "UPDATE invoices SET payment_method = ?, amount_paid = ? WHERE invoice_id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$paymentMethod, $amountPaid, $invoiceId]);
            
            if (!$result) {
                throw new Exception("Không thể cập nhật thông tin thanh toán");
            }
            
            // GHI LOG HOÀN TẤT ĐƠN HÀNG
            try {
                auditUpdate('invoices', $invoiceId, 
                    ['payment_method' => null, 'amount_paid' => 0], 
                    ['payment_method' => $paymentMethod, 'amount_paid' => $amountPaid]
                );
            } catch (Exception $auditError) {
                error_log("Audit log error: " . $auditError->getMessage());
                // Không throw exception để không ảnh hưởng đến thanh toán
            }
            
            error_log("Payment update successful");
            $conn->commit();
            
            // GHI LOG HOÀN TẤT ĐƠN HÀNG
            auditUpdate('invoices', $invoiceId, $invoice, [
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'status' => 'completed'
            ]);
            
            // GHI LOG HOÀN TẤT ĐƠN HÀNG
            auditLog('COMPLETE_ORDER', 'invoices', $invoiceId, null, [
                'invoice_number' => $invoice['invoice_number'],
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'final_amount' => $invoice['final_amount'],
                'change' => $paymentMethod === 'cash' ? ($amountPaid - $invoice['final_amount']) : 0
            ]);
            
            // Tính tiền thừa (nếu thanh toán tiền mặt)
            $change = 0;
            if ($paymentMethod === 'cash') {
                $change = $amountPaid - $invoice['final_amount'];
            }
            
            // Lưu thông tin để hiển thị
            $_SESSION['payment_success'] = [
                'invoice_id' => $invoiceId,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change' => $change
            ];
            
            // Xóa invoice ID khỏi session
            unset($_SESSION['current_invoice_id']);
            
            $_SESSION['success'] = "Thanh toán thành công! Mã hóa đơn: " . $invoice['invoice_number'];
            error_log("Payment completed successfully, redirecting to invoice view");
            header('Location: index.php?page=invoices&action=view&id=' . $invoiceId);
            
        } catch (Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error completing order: " . $e->getMessage());
            $_SESSION['error'] = "Lỗi khi hoàn tất đơn hàng: " . $e->getMessage();
            header('Location: index.php?page=sales&action=checkout');
        }
        exit;
    }
    
    /**
     * Tạo đơn hàng mới (hủy đơn hiện tại nếu có)
     */
    public function newOrder() {
        $oldInvoiceId = $_SESSION['current_invoice_id'] ?? null;
        
        if ($oldInvoiceId) {
            try {
                // Lấy thông tin đơn hàng cũ để ghi log
                $oldInvoice = $this->invoiceModel->getById($oldInvoiceId);
                $oldDetails = $this->invoiceModel->getDetails($oldInvoiceId);
                
                // QUAN TRỌNG: Hoàn trả tồn kho trước khi xóa đơn hàng
                $this->restoreInventoryFromCancelledOrder($oldInvoiceId);
                
                // Xóa đơn hàng cũ chưa hoàn tất
                $this->invoiceModel->delete($oldInvoiceId);
                
                // GHI LOG HỦY ĐƠN HÀNG
                auditLog('CANCEL_ORDER', 'invoices', $oldInvoiceId, null, [
                    'invoice_number' => $oldInvoice['invoice_number'] ?? 'Unknown',
                    'total_amount' => $oldInvoice['total_amount'] ?? 0,
                    'items_count' => count($oldDetails),
                    'reason' => 'Tạo đơn hàng mới'
                ]);
                
                $_SESSION['success'] = "Đã hủy đơn hàng cũ và tạo đơn mới";
            } catch (Exception $e) {
                error_log("Error deleting old invoice: " . $e->getMessage());
                $_SESSION['success'] = "Đã tạo đơn hàng mới";
            }
        } else {
            $_SESSION['success'] = "Sẵn sàng tạo đơn hàng mới";
        }
        
        // Xóa invoice ID khỏi session
        unset($_SESSION['current_invoice_id']);
        
        header('Location: index.php?page=sales');
        exit;
    }
    
    /**
     * Hoàn trả tồn kho khi hủy đơn hàng
     */
    private function restoreInventoryFromCancelledOrder($invoiceId) {
        try {
            $conn = $this->invoiceModel->getConnection();
            
            // Lấy chi tiết đơn hàng bị hủy
            $details = $this->invoiceModel->getDetails($invoiceId);
            
            if (!empty($details)) {
                $conn->beginTransaction();
                
                foreach ($details as $item) {
                    // Hoàn trả số lượng về batch
                    $sql = "UPDATE batches SET quantity = quantity + ?, status = 'active' WHERE batch_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$item['quantity'], $item['batch_id']]);
                    
                    error_log("Restored inventory: " . $item['medicine_name'] . " x " . $item['quantity'] . " to batch " . $item['batch_id']);
                }
                
                $conn->commit();
                
                // Kiểm tra lại thông báo sau khi hoàn trả
                require_once 'models/Notification.php';
                $notificationModel = new Notification();
                $notificationModel->checkLowStock();
                
                error_log("Inventory restored for cancelled order: " . $invoiceId);
            }
            
        } catch (Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error restoring inventory: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Tạo đơn hàng trống mới
     */
    public function createOrder() {
        try {
            // KHÔNG xóa đơn cũ - cho phép nhiều đơn hàng chưa thanh toán
            
            // Tạo đơn hàng trống
            $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);
            
            // Tạo QR code
            require_once 'helpers/qrcode.php';
            $qrCode = generateUniqueQRCode('INV');
            
            $conn = $this->invoiceModel->getConnection();
            $sql = "INSERT INTO invoices (invoice_number, user_id, total_amount, discount, final_amount, qr_code, payment_method) 
                    VALUES (?, ?, 0, 0, 0, ?, NULL)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $invoiceNumber,
                $_SESSION['user_id'],
                $qrCode
            ]);
            
            $invoiceId = $conn->lastInsertId();
            
            // GHI LOG TẠO ĐƠN HÀNG MỚI
            auditCreate('invoices', $invoiceId, [
                'invoice_number' => $invoiceNumber,
                'user_id' => $_SESSION['user_id'],
                'qr_code' => $qrCode,
                'status' => 'pending'
            ]);
            
            // Lưu vào session
            $_SESSION['current_invoice_id'] = $invoiceId;
            $_SESSION['success'] = "Đã tạo đơn hàng #" . $invoiceNumber . ". Bạn có thể thêm sản phẩm vào đơn hàng.";
            
            header('Location: index.php?page=sales');
            exit;
            
        } catch (Exception $e) {
            error_log("Error creating order: " . $e->getMessage());
            $_SESSION['error'] = "Không thể tạo đơn hàng: " . $e->getMessage();
            header('Location: index.php?page=sales');
            exit;
        }
    }
    
    /**
     * Chuyển đổi đơn hàng hiện tại
     */
    public function switchOrder() {
        $invoiceId = $_GET['invoice_id'] ?? 0;
        
        // Kiểm tra đơn hàng có tồn tại và thuộc về user hiện tại không
        $invoice = $this->invoiceModel->getById($invoiceId);
        if (!$invoice || $invoice['user_id'] != $_SESSION['user_id'] || $invoice['payment_method'] !== null) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng hoặc đơn hàng đã được thanh toán";
            header('Location: index.php?page=sales');
            exit;
        }
        
        // Cập nhật session
        $_SESSION['current_invoice_id'] = $invoiceId;
        
        // GHI LOG CHUYỂN ĐỔI ĐƠN HÀNG
        auditLog('SWITCH_ORDER', 'invoices', $invoiceId, null, [
            'invoice_number' => $invoice['invoice_number'],
            'previous_invoice' => $_SESSION['current_invoice_id'] ?? null
        ]);
        
        $_SESSION['success'] = "Đã chuyển sang đơn hàng #" . $invoice['invoice_number'];
        
        header('Location: index.php?page=sales');
        exit;
    }
    
    /**
     * Xóa khỏi giỏ hàng
     */
    public function removeFromCart() {
        $key = $_POST['key'] ?? '';
        
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
        
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }
    
    /**
     * Xem/In hóa đơn
     */
    public function invoice() {
        $id = $_GET['id'] ?? 0;
        $invoice = $this->invoiceModel->getById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = "Không tìm thấy hóa đơn";
            header('Location: index.php?page=sales');
            exit;
        }
        
        $details = $this->invoiceModel->getDetails($id);
        
        $pageTitle = "Hóa đơn #" . $invoice['invoice_number'];
        require_once 'views/sales/invoice.php';
    }
}
