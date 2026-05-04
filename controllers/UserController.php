<?php
require_once 'models/User.php';
require_once 'helpers/csrf.php';
require_once 'helpers/security.php';
require_once 'helpers/logger.php';
require_once 'helpers/secure_session.php';
require_once 'helpers/audit.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        // Chỉ manager mới có quyền quản lý người dùng
        requireManager();
        
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
            $full_name = sanitize($_POST['full_name'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $role = sanitize($_POST['role'] ?? 'staff');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($username) || empty($full_name) || empty($password)) {
                throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
            }
            
            // Validate username (chỉ chữ, số, gạch dưới)
            if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                throw new Exception('Tên đăng nhập phải từ 3-20 ký tự, chỉ chứa chữ, số và gạch dưới');
            }
            
            // Validate password
            if (strlen($password) < 6) {
                throw new Exception('Mật khẩu phải có ít nhất 6 ký tự');
            }
            
            // Validate confirm password
            if ($password !== $confirm_password) {
                throw new Exception('Mật khẩu xác nhận không khớp');
            }
            
            // Validate role
            if (!in_array($role, ['staff', 'manager'])) {
                throw new Exception('Vai trò không hợp lệ');
            }
            
            $data = [
                'username' => $username,
                'password' => $password,
                'full_name' => $full_name,
                'phone' => $phone,
                'role' => $role,
                'is_root_admin' => ($role === 'manager') ? 1 : 0  // Manager mới = Admin cố định
            ];
            
            $userId = $this->userModel->create($data);
            
            // GHI LOG TẠO NGƯỜI DÙNG MỚI
            auditCreate('users', $userId, [
                'username' => $username,
                'full_name' => $full_name,
                'phone' => $phone,
                'role' => $role
            ]);
            
            // Log action (giữ lại log cũ)
            logAction('CREATE_USER', "Created user: $username, Role: $role, ID: $userId");
            logDataChange('users', 'INSERT', $userId, "Username: $username, Phone: $phone");
            
            $_SESSION['success'] = "Thêm nhân viên thành công";
            
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
            // Lấy thông tin user đang được sửa
            $targetUser = $this->userModel->getById($id);
            
            if (!$targetUser) {
                throw new Exception('Không tìm thấy người dùng');
            }
            
            // KIỂM TRA PHÂN QUYỀN
            $currentUserIsRootAdmin = isset($_SESSION['is_root_admin']) && $_SESSION['is_root_admin'] == 1;
            $targetUserIsRootAdmin = isset($targetUser['is_root_admin']) && $targetUser['is_root_admin'] == 1;
            
            // 1. Không thể tự sửa vai trò của chính mình
            if ($id == $_SESSION['user_id']) {
                // Cho phép sửa thông tin cá nhân, nhưng không cho đổi role
                $newRole = $targetUser['role']; // Giữ nguyên role cũ
            } else {
                $newRole = sanitize($_POST['role'] ?? 'staff');
            }
            
            // 2. Chỉ manager mới có thể sửa người dùng
            if ($_SESSION['role'] !== 'manager') {
                throw new Exception('Bạn không có quyền sửa thông tin người dùng');
            }
            
            // 3. Admin cố định KHÔNG thể sửa Admin cố định khác
            if ($currentUserIsRootAdmin && $targetUserIsRootAdmin && $id != $_SESSION['user_id']) {
                throw new Exception('Không thể chỉnh sửa Admin cố định khác');
            }
            
            // 4. Admin được phân quyền KHÔNG thể sửa bất kỳ admin nào khác
            if (!$currentUserIsRootAdmin && $targetUser['role'] === 'manager' && $id != $_SESSION['user_id']) {
                throw new Exception('Không thể chỉnh sửa quản lý khác');
            }
            
            $full_name = sanitize($_POST['full_name'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $role = $newRole;
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
                'phone' => $phone,
                'role' => $role
            ];
            
            // Nếu nâng staff lên manager → set is_root_admin = 1 (Admin cố định)
            // Nếu hạ manager xuống staff → set is_root_admin = 0
            if ($role === 'manager' && $targetUser['role'] === 'staff') {
                $data['is_root_admin'] = 1; // Nâng lên = Admin cố định
            } elseif ($role === 'staff' && $targetUser['role'] === 'manager') {
                $data['is_root_admin'] = 0; // Hạ xuống = Bỏ admin cố định
            }
            
            // Nếu có đổi mật khẩu
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    throw new Exception('Mật khẩu phải có ít nhất 6 ký tự');
                }
                $data['password'] = $password;
            }
            
            // Lấy dữ liệu cũ để ghi log
            $oldData = $targetUser;
            
            $this->userModel->update($id, $data);
            
            // GHI LOG CẬP NHẬT NGƯỜI DÙNG
            auditUpdate('users', $id, $oldData, $data);
            
            // Log action (giữ lại log cũ)
            $changes = "Full name: $full_name, Phone: $phone, Role: $role";
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
            
            // GHI LOG XÓA NGƯỜI DÙNG
            auditDelete('users', $id, $user);
            
            // Log action (giữ lại log cũ)
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
