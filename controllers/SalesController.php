<?php
require_once 'models/Invoice.php';
require_once 'models/Medicine.php';
require_once 'models/Batch.php';

class SalesController {
    private $invoiceModel;
    private $medicineModel;
    private $batchModel;
    
    public function __construct() {
        $this->invoiceModel = new Invoice();
        $this->medicineModel = new Medicine();
        $this->batchModel = new Batch();
    }
    
    /**
     * Trang bán hàng
     */
    public function index() {
        $medicines = $this->medicineModel->getAll();
        
        // Lấy đơn hàng hiện tại từ session
        $currentInvoiceId = $_SESSION['current_invoice_id'] ?? null;
        $currentInvoice = null;
        $invoiceDetails = [];
        
        if ($currentInvoiceId) {
            $currentInvoice = $this->invoiceModel->getById($currentInvoiceId);
            if ($currentInvoice) {
                $invoiceDetails = $this->invoiceModel->getDetails($currentInvoiceId);
            } else {
                // Invoice không tồn tại, xóa khỏi session
                unset($_SESSION['current_invoice_id']);
                $currentInvoiceId = null;
            }
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
            
            echo json_encode(['success' => true, 'message' => 'Đã thêm vào đơn hàng']);
            
        } catch (Exception $e) {
            error_log("Error adding to cart: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Hoàn tất đơn hàng hiện tại
     */
    public function complete() {
        $invoiceId = $_SESSION['current_invoice_id'] ?? null;
        
        if (!$invoiceId) {
            $_SESSION['error'] = "Không có đơn hàng nào đang mở";
            header('Location: index.php?page=sales');
            exit;
        }
        
        try {
            $conn = $this->invoiceModel->getConnection();
            
            // Lấy chi tiết đơn hàng
            $details = $this->invoiceModel->getDetails($invoiceId);
            
            if (empty($details)) {
                $_SESSION['error'] = "Đơn hàng trống, không thể hoàn tất";
                header('Location: index.php?page=sales');
                exit;
            }
            
            // Trừ số lượng từ batch
            foreach ($details as $item) {
                $sql = "UPDATE batches SET quantity = quantity - ? WHERE batch_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$item['quantity'], $item['batch_id']]);
                
                // Cập nhật status nếu hết hàng
                $sql = "UPDATE batches SET status = 'sold_out' WHERE batch_id = ? AND quantity = 0";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$item['batch_id']]);
            }
            
            // Xóa invoice ID khỏi session
            unset($_SESSION['current_invoice_id']);
            
            $_SESSION['success'] = "Đơn hàng đã hoàn tất";
            header('Location: index.php?page=invoices&action=view&id=' . $invoiceId);
            
        } catch (Exception $e) {
            error_log("Error completing order: " . $e->getMessage());
            $_SESSION['error'] = "Lỗi khi hoàn tất đơn hàng: " . $e->getMessage();
            header('Location: index.php?page=sales');
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
                // Xóa đơn hàng cũ chưa hoàn tất
                $this->invoiceModel->delete($oldInvoiceId);
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
     * Tạo đơn hàng trống mới
     */
    public function createOrder() {
        try {
            // Kiểm tra nếu đã có đơn hàng đang mở
            $oldInvoiceId = $_SESSION['current_invoice_id'] ?? null;
            if ($oldInvoiceId) {
                // Xóa đơn cũ
                $this->invoiceModel->delete($oldInvoiceId);
            }
            
            // Tạo đơn hàng trống
            $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);
            
            // Tạo QR code
            require_once 'helpers/qrcode.php';
            $qrCode = generateUniqueQRCode('INV');
            
            $conn = $this->invoiceModel->getConnection();
            $sql = "INSERT INTO invoices (invoice_number, user_id, total_amount, discount, final_amount, qr_code) 
                    VALUES (?, ?, 0, 0, 0, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $invoiceNumber,
                $_SESSION['user_id'],
                $qrCode
            ]);
            
            $invoiceId = $conn->lastInsertId();
            
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
     * Thanh toán
     */
    public function checkout() {
        if (empty($_SESSION['cart'])) {
            $_SESSION['error'] = "Giỏ hàng trống";
            header('Location: index.php?page=sales');
            exit;
        }
        
        try {
            $discount = $_POST['discount'] ?? 0;
            $totalAmount = 0;
            $items = [];
            
            foreach ($_SESSION['cart'] as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $totalAmount += $subtotal;
                
                $items[] = [
                    'medicine_id' => $item['medicine_id'],
                    'batch_id' => $item['batch_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $subtotal
                ];
            }
            
            $finalAmount = $totalAmount - $discount;
            
            $data = [
                'user_id' => $_SESSION['user_id'],
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'items' => $items
            ];
            
            $invoiceId = $this->invoiceModel->create($data);
            
            // Xóa giỏ hàng
            $_SESSION['cart'] = [];
            
            $_SESSION['success'] = "Thanh toán thành công";
            header('Location: index.php?page=sales&action=invoice&id=' . $invoiceId);
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?page=sales');
        }
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
