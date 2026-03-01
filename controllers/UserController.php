<?php
require_once 'models/User.php';
require_once 'helpers/csrf.php';
require_once 'helpers/security.php';
require_once 'helpers/logger.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function index() {
        $users = $this->userModel->getAll();
        $pageTitle = "Quản lý người dùng";
        require_once 'views/users/index.php';
    }
    
    public function create() {
        $pageTitle = "Thêm người dùng";
        require_once 'views/users/create.php';
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=users');
            exit;
        }
        
        // Verify CSRF token
        requireCsrfToken();
        
        try {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $full_name = sanitize($_POST['full_name'] ?? '');
            $role = sanitize($_POST['role'] ?? 'staff');
            
            // Validate input
            if (empty($username) || empty($password) || empty($full_name)) {
                throw new Exception('Vui lòng điền đầy đủ thông tin');
            }
            
            // Validate username (chỉ chữ, số, gạch dưới)
            if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                throw new Exception('Tên đăng nhập phải từ 3-20 ký tự, chỉ chứa chữ, số và gạch dưới');
            }
            
            // Validate password strength
            $passwordValidation = validateStrongPassword($password);
            if (!$passwordValidation['valid']) {
                throw new Exception($passwordValidation['message']);
            }
            
            // Validate role
            if (!in_array($role, ['staff', 'manager'])) {
                throw new Exception('Vai trò không hợp lệ');
            }
            
            $data = [
                'username' => $username,
                'password' => $password,
                'full_name' => $full_name,
                'role' => $role
            ];
            
            $userId = $this->userModel->create($data);
            
            // Log action
            logAction('CREATE_USER', "Created user: $username, Role: $role, ID: $userId");
            logDataChange('users', 'INSERT', $userId, "Username: $username");
            
            $_SESSION['success'] = "Thêm người dùng thành công";
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            logError('CREATE_USER_FAILED', $e->getMessage());
        }
        
        header('Location: index.php?page=users');
        exit;
    }
    
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID không hợp lệ";
            header('Location: index.php?page=users');
            exit;
        }
        
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['error'] = "Không tìm thấy người dùng";
            header('Location: index.php?page=users');
            exit;
        }
        
        $pageTitle = "Sửa người dùng";
        require_once 'views/users/edit.php';
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=users');
            exit;
        }
        
        // Verify CSRF token
        requireCsrfToken();
        
        $id = (int)($_POST['user_id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID không hợp lệ";
            header('Location: index.php?page=users');
            exit;
        }
        
        try {
            $full_name = sanitize($_POST['full_name'] ?? '');
            $role = sanitize($_POST['role'] ?? 'staff');
            $password = $_POST['password'] ?? '';
            
            // Validate input
            if (empty($full_name)) {
                throw new Exception('Vui lòng nhập họ tên');
            }
            
            // Validate role
            if (!in_array($role, ['staff', 'manager'])) {
                throw new Exception('Vai trò không hợp lệ');
            }
            
            $data = [
                'full_name' => $full_name,
                'role' => $role
            ];
            
            // Nếu có đổi mật khẩu
            if (!empty($password)) {
                $passwordValidation = validateStrongPassword($password);
                if (!$passwordValidation['valid']) {
                    throw new Exception($passwordValidation['message']);
                }
                $data['password'] = $password;
            }
            
            $this->userModel->update($id, $data);
            
            // Log action
            $changes = "Full name: $full_name, Role: $role";
            if (!empty($password)) {
                $changes .= ", Password changed";
            }
            logAction('UPDATE_USER', "Updated user ID: $id");
            logDataChange('users', 'UPDATE', $id, $changes);
            
            $_SESSION['success'] = "Cập nhật người dùng thành công";
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            logError('UPDATE_USER_FAILED', "ID: $id, Error: " . $e->getMessage());
        }
        
        header('Location: index.php?page=users');
        exit;
    }
    
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error'] = "ID không hợp lệ";
            header('Location: index.php?page=users');
            exit;
        }
        
        // Không cho xóa chính mình
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Không thể xóa tài khoản đang đăng nhập";
            header('Location: index.php?page=users');
            exit;
        }
        
        try {
            // Lấy thông tin user trước khi xóa để log
            $user = $this->userModel->getById($id);
            
            $this->userModel->delete($id);
            
            // Log action
            logAction('DELETE_USER', "Deleted user ID: $id, Username: {$user['username']}");
            logDataChange('users', 'DELETE', $id, "Username: {$user['username']}");
            
            $_SESSION['success'] = "Xóa người dùng thành công";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            logError('DELETE_USER_FAILED', "ID: $id, Error: " . $e->getMessage());
        }
        
        header('Location: index.php?page=users');
        exit;
    }
}
