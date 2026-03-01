<?php
require_once 'models/Supplier.php';

class SupplierController {
    private $supplierModel;
    
    public function __construct() {
        $this->supplierModel = new Supplier();
    }
    
    public function index() {
        $suppliers = $this->supplierModel->getAll();
        $pageTitle = "Quản lý nhà cung cấp";
        require_once 'views/suppliers/index.php';
    }
    
    public function create() {
        $pageTitle = "Thêm nhà cung cấp";
        require_once 'views/suppliers/create.php';
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=suppliers');
            exit;
        }
        
        try {
            $data = [
                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];
            
            $this->supplierModel->create($data);
            $_SESSION['success'] = "Thêm nhà cung cấp thành công";
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: index.php?page=suppliers');
        exit;
    }
    
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $supplier = $this->supplierModel->getById($id);
        
        if (!$supplier) {
            $_SESSION['error'] = "Không tìm thấy nhà cung cấp";
            header('Location: index.php?page=suppliers');
            exit;
        }
        
        $pageTitle = "Sửa nhà cung cấp";
        require_once 'views/suppliers/edit.php';
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=suppliers');
            exit;
        }
        
        $id = $_POST['supplier_id'] ?? 0;
        
        try {
            $data = [
                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];
            
            $this->supplierModel->update($id, $data);
            $_SESSION['success'] = "Cập nhật nhà cung cấp thành công";
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: index.php?page=suppliers');
        exit;
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        try {
            $this->supplierModel->delete($id);
            $_SESSION['success'] = "Xóa nhà cung cấp thành công";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: index.php?page=suppliers');
        exit;
    }
}
