<?php
require_once 'models/Medicine.php';
require_once 'models/Category.php';
require_once 'models/Unit.php';

class MedicineController {
    private $medicineModel;
    private $categoryModel;
    private $unitModel;
    
    public function __construct() {
        $this->medicineModel = new Medicine();
        $this->categoryModel = new Category();
        $this->unitModel = new Unit();
    }
    
    /**
     * Danh sách thuốc
     */
    public function index() {
        $medicines = $this->medicineModel->getAll();
        
        // Lấy tồn kho cho mỗi thuốc
        foreach ($medicines as &$medicine) {
            $medicine['inventory'] = $this->medicineModel->getTotalInventory($medicine['medicine_id']);
        }
        
        $pageTitle = "Quản lý thuốc";
        require_once 'views/medicines/index.php';
    }
    
    /**
     * Form thêm thuốc mới
     */
    public function create() {
        $categories = $this->categoryModel->getAll();
        $units = $this->unitModel->getAll();
        
        $pageTitle = "Thêm thuốc mới";
        require_once 'views/medicines/create.php';
    }
    
    /**
     * Lưu thuốc mới
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=medicines');
            exit;
        }
        
        try {
            $data = [
                'medicine_name' => trim($_POST['medicine_name'] ?? ''),
                'category_id' => $_POST['category_id'] ?? null,
                'unit_id' => $_POST['unit_id'] ?? null,
                'price' => $_POST['price'] ?? 0,
                'description' => trim($_POST['description'] ?? '')
            ];
            
            $id = $this->medicineModel->create($data);
            
            if ($id) {
                $_SESSION['success'] = "Thêm thuốc thành công";
                header('Location: index.php?page=medicines');
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi thêm thuốc";
                header('Location: index.php?page=medicines&action=create');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?page=medicines&action=create');
        }
        exit;
    }
    
    /**
     * Form sửa thuốc
     */
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $medicine = $this->medicineModel->getById($id);
        
        if (!$medicine) {
            $_SESSION['error'] = "Không tìm thấy thuốc";
            header('Location: index.php?page=medicines');
            exit;
        }
        
        $categories = $this->categoryModel->getAll();
        $units = $this->unitModel->getAll();
        
        $pageTitle = "Sửa thông tin thuốc";
        require_once 'views/medicines/edit.php';
    }
    
    /**
     * Cập nhật thuốc
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=medicines');
            exit;
        }
        
        $id = $_POST['medicine_id'] ?? 0;
        
        try {
            $data = [
                'medicine_name' => trim($_POST['medicine_name'] ?? ''),
                'category_id' => $_POST['category_id'] ?? null,
                'unit_id' => $_POST['unit_id'] ?? null,
                'price' => $_POST['price'] ?? 0,
                'description' => trim($_POST['description'] ?? '')
            ];
            
            $result = $this->medicineModel->update($id, $data);
            
            if ($result) {
                $_SESSION['success'] = "Cập nhật thuốc thành công";
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật thuốc";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: index.php?page=medicines');
        exit;
    }
    
    /**
     * Xem chi tiết thuốc
     */
    public function view() {
        $id = $_GET['id'] ?? 0;
        $medicine = $this->medicineModel->getById($id);
        
        if (!$medicine) {
            $_SESSION['error'] = "Không tìm thấy thuốc";
            header('Location: index.php?page=medicines');
            exit;
        }
        
        // Lấy tồn kho
        $inventory = $this->medicineModel->getTotalInventory($id);
        
        // Lấy danh sách lô thuốc
        require_once 'models/Batch.php';
        $batchModel = new Batch();
        $batches = $batchModel->getByMedicine($id);
        
        // Lấy thống kê bán hàng (30 ngày gần nhất)
        require_once 'models/InvoiceDetail.php';
        $invoiceDetailModel = new InvoiceDetail();
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        $salesStats = $invoiceDetailModel->getSalesByMedicine($id, $startDate, $endDate);
        
        $pageTitle = "Chi tiết thuốc: " . $medicine['medicine_name'];
        require_once 'views/medicines/view.php';
    }
    
    /**
     * Xóa thuốc
     */
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        try {
            $result = $this->medicineModel->delete($id);
            
            if ($result) {
                $_SESSION['success'] = "Xóa thuốc thành công";
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi xóa thuốc";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: index.php?page=medicines');
        exit;
    }
}
