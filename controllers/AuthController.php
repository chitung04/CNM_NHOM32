<?php
require_once 'helpers/audit.php';
require_once 'helpers/secure_session.php';

class AuthController {
    private $pdo;
    private $secureSession;
    
    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            $this->pdo = null;
        }
        
        $this->secureSession = SecureSession::getInstance();
    }
    
    public function login() {
        $error = '';
        
        // Nếu đã đăng nhập, chuyển về dashboard
        if ($this->secureSession->isLoggedIn()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        // Xử lý form submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $error = 'Vui lòng nhập tên đăng nhập và mật khẩu';
            } else {
                $user = $this->authenticate($username, $password);
                
                if ($user) {
                    // Sử dụng secure session để đăng nhập
                    $this->secureSession->login(
                        $user['user_id'],
                        $user['username'],
                        $user['full_name'],
                        $user['role']
                    );
                    
                    // Lưu pharmacy_id và is_root_admin vào session
                    $_SESSION['pharmacy_id'] = $user['pharmacy_id'];
                    $_SESSION['is_root_admin'] = $user['is_root_admin'] ?? 0;
                    
                    // GHI LOG ĐĂNG NHẬP THÀNH CÔNG
                    auditLogin($username, true);
                    
                    header('Location: index.php?page=dashboard');
                    exit;
                } else {
                    // GHI LOG ĐĂNG NHẬP THẤT BẠI
                    auditLogin($username, false);
                    
                    $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
                }
            }
        }
        
        require_once 'views/auth/login.php';
    }
    
    private function authenticate($username, $password) {
        // Thử database trước
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    return $user;
                }
            } catch (Exception $e) {
                // Fallback nếu database lỗi
            }
        }
        
        // Fallback authentication với mật khẩu 123456
        $fallbackUsers = [
            'admin' => [
                'user_id' => 1,
                'username' => 'admin',
                'full_name' => 'Quản lý',
                'phone' => '0123456789',
                'role' => 'manager',
                'pharmacy_id' => 1,
                'is_root_admin' => 0
            ],
            'nhanvien1' => [
                'user_id' => 2,
                'username' => 'nhanvien1',
                'full_name' => 'Nhân viên 1',
                'phone' => '0987654321',
                'role' => 'staff',
                'pharmacy_id' => 1,
                'is_root_admin' => 0
            ],
            'nhanvien2' => [
                'user_id' => 3,
                'username' => 'nhanvien2',
                'full_name' => 'Nhân viên 2',
                'phone' => '0912345678',
                'role' => 'staff',
                'pharmacy_id' => 1,
                'is_root_admin' => 0
            ]
        ];
        
        if (isset($fallbackUsers[$username]) && $password === '123456') {
            return $fallbackUsers[$username];
        }
        
        return false;
    }
    
    public function logout() {
        // GHI LOG ĐĂNG XUẤT
        auditLogout();
        
        // Sử dụng secure session để đăng xuất
        $this->secureSession->logout();
        
        header('Location: index.php?page=auth&action=login&message=logged_out');
        exit;
    }
    
    public function register() {
        $error = '';
        $success = '';
        
        // Nếu đã đăng nhập, chuyển về dashboard
        if ($this->secureSession->isLoggedIn()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        // Xử lý form submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $pharmacyName = trim($_POST['pharmacy_name'] ?? '');
            $pharmacyAddress = trim($_POST['pharmacy_address'] ?? '');
            $agreeTerms = isset($_POST['agree_terms']);
            
            // Validation
            if (empty($username) || empty($password) || empty($fullName) || 
                empty($email) || empty($phone) || empty($pharmacyName) || empty($pharmacyAddress)) {
                $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
            } elseif (!$agreeTerms) {
                $error = 'Bạn phải đồng ý với điều khoản sử dụng';
            } elseif ($password !== $confirmPassword) {
                $error = 'Mật khẩu xác nhận không khớp';
            } elseif (strlen($password) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự';
            } elseif (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
                $error = 'Tên đăng nhập phải từ 4-20 ký tự, chỉ chứa chữ, số và dấu gạch dưới';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email không hợp lệ';
            } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
                $error = 'Số điện thoại phải có 10-11 số';
            } else {
                // Tạo pharmacy và admin
                $result = $this->createPharmacyWithAdmin([
                    'pharmacy_name' => $pharmacyName,
                    'pharmacy_address' => $pharmacyAddress,
                    'pharmacy_phone' => $phone,
                    'pharmacy_email' => $email,
                    'admin_username' => $username,
                    'admin_password' => $password,
                    'admin_fullname' => $fullName,
                    'admin_phone' => $phone,
                    'admin_email' => $email
                ]);
                
                if ($result['success']) {
                    $success = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...';
                    // Redirect sau 2 giây
                    header('refresh:2;url=index.php?page=auth&action=login&registered=1');
                } else {
                    $error = $result['message'];
                }
            }
        }
        
        require_once 'views/auth/register.php';
    }
    
    private function createPharmacyWithAdmin($data) {
        if (!$this->pdo) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Kiểm tra username đã tồn tại
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$data['admin_username']]);
            if ($stmt->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng'];
            }
            
            // Kiểm tra email đã tồn tại
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$data['admin_email']]);
            if ($stmt->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Email đã được sử dụng'];
            }
            
            // Tạo pharmacy_code unique
            $pharmacyCode = 'PH' . strtoupper(substr(md5($data['pharmacy_name'] . time()), 0, 8));
            
            // Tạo pharmacy
            $stmt = $this->pdo->prepare("
                INSERT INTO pharmacies (pharmacy_name, pharmacy_code, address, phone, email, status, subscription_plan)
                VALUES (?, ?, ?, ?, ?, 'active', 'free')
            ");
            $stmt->execute([
                $data['pharmacy_name'],
                $pharmacyCode,
                $data['pharmacy_address'],
                $data['pharmacy_phone'],
                $data['pharmacy_email']
            ]);
            
            $pharmacyId = $this->pdo->lastInsertId();
            
            // Tạo admin user (đánh dấu là root admin)
            $hashedPassword = password_hash($data['admin_password'], PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                INSERT INTO users (pharmacy_id, username, password, full_name, phone, email, role, is_root_admin, is_active)
                VALUES (?, ?, ?, ?, ?, ?, 'manager', 1, 1)
            ");
            $stmt->execute([
                $pharmacyId,
                $data['admin_username'],
                $hashedPassword,
                $data['admin_fullname'],
                $data['admin_phone'],
                $data['admin_email']
            ]);
            
            // Tạo categories mặc định
            $defaultCategories = [
                'Thuốc kháng sinh', 'Thuốc giảm đau', 'Thuốc hạ sốt', 
                'Vitamin & Khoáng chất', 'Thuốc tiêu hóa', 'Thuốc tim mạch',
                'Thuốc da liễu', 'Thực phẩm chức năng'
            ];
            
            $categoryIds = [];
            $stmt = $this->pdo->prepare("INSERT INTO categories (pharmacy_id, category_name) VALUES (?, ?)");
            foreach ($defaultCategories as $category) {
                $stmt->execute([$pharmacyId, $category]);
                $categoryIds[] = $this->pdo->lastInsertId();
            }
            
            // Tạo units mặc định
            $defaultUnits = ['Viên', 'Vỉ', 'Hộp', 'Chai', 'Tuýp', 'Gói', 'Ống', 'Lọ'];
            
            $unitIds = [];
            $stmt = $this->pdo->prepare("INSERT INTO units (pharmacy_id, unit_name) VALUES (?, ?)");
            foreach ($defaultUnits as $unit) {
                $stmt->execute([$pharmacyId, $unit]);
                $unitIds[] = $this->pdo->lastInsertId();
            }
            
            // Tạo suppliers mặc định
            $defaultSuppliers = [
                ['Công ty Dược phẩm Hà Nội', '123 Đường Láng, Hà Nội', '0901234567', 'contact@pharma-hn.com'],
                ['Công ty Dược phẩm Sài Gòn', '456 Nguyễn Huệ, TP.HCM', '0907654321', 'info@pharma-sg.com'],
                ['Công ty Dược phẩm Trung Ương', '789 Trần Hưng Đạo, Hà Nội', '0912345678', 'sales@pharma-central.com']
            ];
            
            $supplierIds = [];
            $stmt = $this->pdo->prepare("
                INSERT INTO suppliers (pharmacy_id, supplier_name, address, phone, email) 
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($defaultSuppliers as $supplier) {
                $stmt->execute([
                    $pharmacyId,
                    $supplier[0],
                    $supplier[1],
                    $supplier[2],
                    $supplier[3]
                ]);
                $supplierIds[] = $this->pdo->lastInsertId();
            }
            
            // Tạo medicines mẫu (10 loại thuốc)
            $sampleMedicines = [
                ['Amoxicillin 500mg', 0, 0, 5000, 'Kháng sinh điều trị nhiễm khuẩn đường hô hấp'],
                ['Paracetamol 500mg', 1, 0, 3000, 'Thuốc giảm đau, hạ sốt hiệu quả'],
                ['Ibuprofen 400mg', 1, 0, 4000, 'Thuốc giảm đau, chống viêm'],
                ['Amlodipine 5mg', 5, 0, 8000, 'Thuốc điều trị tăng huyết áp'],
                ['Metformin 500mg', 4, 0, 6000, 'Thuốc điều trị tiểu đường type 2'],
                ['Vitamin C 1000mg', 3, 0, 2000, 'Bổ sung vitamin C, tăng sức đề kháng'],
                ['Cefixime 200mg', 0, 0, 12000, 'Kháng sinh thế hệ 3 điều trị nhiễm khuẩn'],
                ['Aspirin 100mg', 5, 0, 3500, 'Thuốc chống đông máu, phòng ngừa tai biến'],
                ['Omeprazole 20mg', 4, 0, 7000, 'Thuốc điều trị loét dạ dày, trào ngược'],
                ['Cetirizine 10mg', 6, 0, 2500, 'Thuốc chống dị ứng, viêm mũi']
            ];
            
            $medicineIds = [];
            $stmt = $this->pdo->prepare("
                INSERT INTO medicines (pharmacy_id, medicine_name, category_id, unit_id, price, description, qr_code) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($sampleMedicines as $index => $medicine) {
                $qrCode = 'MED_' . time() . '_' . rand(1000, 9999);
                $stmt->execute([
                    $pharmacyId,
                    $medicine[0],
                    $categoryIds[$medicine[1]],
                    $unitIds[$medicine[2]],
                    $medicine[3],
                    $medicine[4],
                    $qrCode
                ]);
                $medicineIds[] = $this->pdo->lastInsertId();
                usleep(10000); // Đợi 0.01s để QR code không trùng
            }
            
            // Tạo batches cho mỗi thuốc (2 lô/thuốc = 20 lô)
            $stmt = $this->pdo->prepare("
                INSERT INTO batches (pharmacy_id, medicine_id, batch_number, quantity, import_date, expiry_date, supplier_id, status, qr_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?)
            ");
            
            $batchCount = 0;
            foreach ($medicineIds as $medId) {
                // Tạo 2 lô cho mỗi thuốc
                for ($i = 1; $i <= 2; $i++) {
                    $batchNumber = "BATCH_P{$pharmacyId}_" . str_pad($batchCount + 1, 4, '0', STR_PAD_LEFT);
                    $quantity = rand(100, 500);
                    $importDate = date('Y-m-d');
                    $expiryDate = date('Y-m-d', strtotime('+' . rand(12, 24) . ' months'));
                    $supplierId = $supplierIds[array_rand($supplierIds)];
                    $qrCode = 'BATCH_' . time() . '_' . rand(1000, 9999);
                    
                    $stmt->execute([
                        $pharmacyId,
                        $medId,
                        $batchNumber,
                        $quantity,
                        $importDate,
                        $expiryDate,
                        $supplierId,
                        $qrCode
                    ]);
                    
                    $batchCount++;
                    usleep(10000); // Đợi 0.01s để QR code không trùng
                }
            }
            
            $this->pdo->commit();
            
            return ['success' => true, 'pharmacy_id' => $pharmacyId];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()];
        }
    }
}
