<?php
require_once 'models/Batch.php';
require_once 'models/Medicine.php';
require_once 'models/Supplier.php';

class BatchController {
    private $batchModel;
    private $medicineModel;
    private $supplierModel;
    
    public function __construct() {
        $this->batchModel = new Batch();
        $this->medicineModel = new Medicine();
        $this->supplierModel = new Supplier();
    }
    
    public function index() {
        $batches = $this->batchModel->getAll();
        $pageTitle = "Quản lý lô thuốc";
        require_once 'views/batches/index.php';
    }
    
    public function create() {
        $medicines = $this->medicineModel->getAll();
        $suppliers = $this->supplierModel->getAll();
        $pageTitle = "Nhập lô thuốc mới";
        require_once 'views/batches/create.php';
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=batches');
            exit;
        }
        
        try {
            $data = [
                'medicine_id' => $_POST['medicine_id'] ?? null,
                'supplier_id' => $_POST['supplier_id'] ?? null,
                'quantity' => $_POST['quantity'] ?? 0,
                'expiry_date' => $_POST['expiry_date'] ?? '',
                'import_date' => $_POST['import_date'] ?? date('Y-m-d')
            ];
            
            $id = $this->batchModel->create($data);
            
            if ($id) {
                $_SESSION['success'] = "Nhập lô thuốc thành công";
                header('Location: index.php?page=batches');
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi nhập lô thuốc";
                header('Location: index.php?page=batches&action=create');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?page=batches&action=create');
        }
        exit;
    }
    
    public function view() {
        $id = $_GET['id'] ?? 0;
        $batch = $this->batchModel->getById($id);
        
        if (!$batch) {
            $_SESSION['error'] = "Không tìm thấy lô thuốc";
            header('Location: index.php?page=batches');
            exit;
        }
        
        // Lấy lịch sử bán hàng của lô này
        require_once 'models/InvoiceDetail.php';
        $invoiceDetailModel = new InvoiceDetail();
        $salesHistory = $invoiceDetailModel->getByBatch($id);
        
        $pageTitle = "Chi tiết lô thuốc #" . $batch['batch_id'];
        require_once 'views/batches/view.php';
    }
}
