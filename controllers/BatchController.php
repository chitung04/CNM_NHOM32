<?php
require_once 'config/config.php';
require_once 'models/Batch.php';
require_once 'models/Medicine.php';
require_once 'models/Supplier.php';
require_once 'helpers/secure_session.php';

class BatchController {
    private $batchModel;
    private $medicineModel;
    private $supplierModel;
    
    public function __construct() {
        // Chỉ manager mới có quyền truy cập quản lý lô thuốc
        requireManager();
        
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
    
    /**
     * Hiển thị trang import CSV
     */
    public function import() {
        $pageTitle = "Import lô thuốc từ CSV";
        require_once 'views/batches/import.php';
    }
    
    /**
     * Xử lý import CSV
     */
    public function processImport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=batches&action=import');
            exit;
        }
        
        // Kiểm tra file upload
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Vui lòng chọn file CSV để upload";
            header('Location: index.php?page=batches&action=import');
            exit;
        }
        
        $file = $_FILES['csv_file'];
        $skipFirstRow = isset($_POST['skip_first_row']);
        $autoCreateQR = isset($_POST['auto_create_qr']);
        
        // Kiểm tra extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $_SESSION['error'] = "File phải có định dạng CSV";
            header('Location: index.php?page=batches&action=import');
            exit;
        }
        
        try {
            $result = $this->importFromCSV($file['tmp_name'], $skipFirstRow, $autoCreateQR);
            
            $_SESSION['import_result'] = $result;
            
            if ($result['success'] > 0) {
                $_SESSION['success'] = "Import thành công {$result['success']}/{$result['total']} lô thuốc";
            } else {
                $_SESSION['error'] = "Không có lô nào được import thành công";
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Lỗi: " . $e->getMessage();
        }
        
        header('Location: index.php?page=batches&action=import');
        exit;
    }
    
    /**
     * Import dữ liệu từ file CSV
     */
    private function importFromCSV($filePath, $skipFirstRow = true, $autoCreateQR = true) {
        $result = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        // Mở file CSV
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Không thể đọc file CSV");
        }
        
        $rowNumber = 0;
        
        // Bỏ qua dòng đầu nếu cần
        if ($skipFirstRow) {
            fgetcsv($handle);
            $rowNumber++;
        }
        
        // Đọc từng dòng
        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // Bỏ qua dòng trống
            if (empty($data) || (count($data) == 1 && empty($data[0]))) {
                continue;
            }
            
            $result['total']++;
            
            try {
                // Validate dữ liệu
                if (count($data) < 3) {
                    throw new Exception("Thiếu dữ liệu (cần ít nhất 3 cột). Có " . count($data) . " cột");
                }
                
                // Trim tất cả dữ liệu
                $medicineId = trim($data[0] ?? '');
                $batchNumber = trim($data[1] ?? '');
                $quantity = trim($data[2] ?? '');
                $importDate = isset($data[3]) && !empty(trim($data[3])) ? trim($data[3]) : date('Y-m-d');
                $expiryDate = isset($data[4]) && !empty(trim($data[4])) ? trim($data[4]) : date('Y-m-d', strtotime('+2 years'));
                $supplierId = isset($data[5]) && !empty(trim($data[5])) ? trim($data[5]) : null;
                
                // Validate
                if (empty($medicineId) || !is_numeric($medicineId)) {
                    throw new Exception("medicine_id không hợp lệ: '$medicineId'");
                }
                
                if (empty($batchNumber)) {
                    throw new Exception("batch_number không được để trống");
                }
                
                if (empty($quantity) || !is_numeric($quantity) || $quantity <= 0) {
                    throw new Exception("quantity phải là số nguyên dương: '$quantity'");
                }
                
                // Kiểm tra medicine_id có tồn tại không
                $medicine = $this->medicineModel->getById($medicineId);
                if (!$medicine) {
                    throw new Exception("Thuốc ID $medicineId không tồn tại");
                }
                
                // Kiểm tra batch_number đã tồn tại chưa
                require_once 'models/Database.php';
                $db = Database::getInstance();
                $stmt = $db->query("SELECT COUNT(*) as count FROM batches WHERE batch_number = ?", [$batchNumber]);
                if ($stmt->fetch()['count'] > 0) {
                    throw new Exception("Số lô $batchNumber đã tồn tại");
                }
                
                // Tạo lô mới
                $batchData = [
                    'medicine_id' => $medicineId,
                    'batch_number' => $batchNumber,
                    'quantity' => $quantity,
                    'import_date' => $importDate,
                    'expiry_date' => $expiryDate,
                    'supplier_id' => $supplierId
                ];
                
                $batchId = $this->batchModel->create($batchData);
                
                if ($batchId) {
                    // Tạo QR code nếu cần
                    if ($autoCreateQR) {
                        try {
                            require_once 'helpers/qrcode.php';
                            $qrCode = 'BATCH_' . time() . '_' . rand(1000, 9999);
                            $qrPath = generateQRCode($qrCode, 'batch');
                            
                            if ($qrPath) {
                                $db->execute("UPDATE batches SET qr_code = ? WHERE batch_id = ?", [$qrCode, $batchId]);
                            }
                        } catch (Exception $qrError) {
                            // QR code lỗi không ảnh hưởng import
                            // Chỉ log lỗi
                        }
                    }
                    
                    $result['success']++;
                } else {
                    throw new Exception("Không thể tạo lô thuốc");
                }
                
            } catch (Exception $e) {
                $result['failed']++;
                $result['errors'][] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                    'data' => $data // Thêm data để debug
                ];
            }
        }
        
        fclose($handle);
        
        return $result;
    }
    
    /**
     * Tải file CSV mẫu
     */
    public function downloadTemplate() {
        // Tạo file CSV mẫu
        $filename = 'batch_import_template.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['medicine_id', 'batch_number', 'quantity', 'import_date', 'expiry_date', 'supplier_id']);
        
        // Dữ liệu mẫu
        fputcsv($output, [1, 'LOT001', 100, date('Y-m-d'), date('Y-m-d', strtotime('+2 years')), 1]);
        fputcsv($output, [2, 'LOT002', 200, date('Y-m-d'), date('Y-m-d', strtotime('+2 years')), 2]);
        fputcsv($output, [3, 'LOT003', 150, date('Y-m-d'), date('Y-m-d', strtotime('+2 years')), 1]);
        
        fclose($output);
        exit;
    }
    
    /**
     * Hiển thị trang import SQL
     */
    public function importSql() {
        $pageTitle = "Import lô thuốc từ SQL";
        require_once 'views/batches/import_sql.php';
    }
    
    /**
     * Xử lý import SQL
     */
    public function processImportSql() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=batches&action=import_sql');
            exit;
        }
        
        // Kiểm tra file upload
        if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Vui lòng chọn file SQL để upload";
            header('Location: index.php?page=batches&action=import_sql');
            exit;
        }
        
        $file = $_FILES['sql_file'];
        
        // Kiểm tra extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            $_SESSION['error'] = "File phải có định dạng SQL";
            header('Location: index.php?page=batches&action=import_sql');
            exit;
        }
        
        try {
            // Đọc nội dung file SQL
            $sqlContent = file_get_contents($file['tmp_name']);
            
            if (empty($sqlContent)) {
                throw new Exception("File SQL rỗng");
            }
            
            // Thực thi SQL
            require_once 'models/Database.php';
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Thực thi từng câu lệnh
            $conn->exec($sqlContent);
            
            $_SESSION['success'] = "Import SQL thành công!";
            $_SESSION['import_result'] = [
                'message' => "Đã import dữ liệu từ file SQL"
            ];
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Lỗi: " . $e->getMessage();
        }
        
        header('Location: index.php?page=batches&action=import_sql');
        exit;
    }
}
