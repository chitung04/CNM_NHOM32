<?php
require_once 'Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Xác thực người dùng
     */
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? AND is_active = 1";
        $stmt = $this->db->query($sql, [$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Lấy thông tin user theo ID
     */
    public function getById($id) {
        $sql = "SELECT user_id, username, full_name, phone, email, role, is_root_admin, is_active, created_at FROM users WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Lấy tất cả users (chỉ hiển thị user đang hoạt động của nhà thuốc hiện tại)
     */
    public function getAll() {
        $pharmacyId = $_SESSION['pharmacy_id'] ?? null;
        
        if (!$pharmacyId) {
            return [];
        }
        
        $sql = "SELECT user_id, username, full_name, phone, email, role, is_root_admin, is_active, created_at 
                FROM users 
                WHERE is_active = 1 AND pharmacy_id = ?
                ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy tất cả users bao gồm cả đã khóa (của nhà thuốc hiện tại)
     */
    public function getAllIncludeInactive() {
        $pharmacyId = $_SESSION['pharmacy_id'] ?? null;
        
        if (!$pharmacyId) {
            return [];
        }
        
        $sql = "SELECT user_id, username, full_name, phone, email, role, is_root_admin, is_active, created_at 
                FROM users 
                WHERE pharmacy_id = ?
                ORDER BY is_active DESC, created_at DESC";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo user mới (tự động gán pharmacy_id từ session)
     */
    public function create($data) {
        $pharmacyId = $_SESSION['pharmacy_id'] ?? null;
        
        if (!$pharmacyId) {
            throw new Exception("Không xác định được nhà thuốc");
        }
        
        // Validate required fields
        if (empty($data['username']) || empty($data['password']) || 
            empty($data['full_name']) || empty($data['role'])) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc");
        }
        
        // Check username uniqueness
        if ($this->usernameExists($data['username'])) {
            throw new Exception("Tên đăng nhập đã được sử dụng");
        }
        
        $isRootAdmin = $data['is_root_admin'] ?? 0;
        
        $sql = "INSERT INTO users (pharmacy_id, username, password, full_name, role, phone, email, is_root_admin, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $result = $this->db->execute($sql, [
            $pharmacyId,
            $data['username'],
            $hashedPassword,
            $data['full_name'],
            $data['role'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $isRootAdmin
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Cập nhật user
     */
    public function update($id, $data) {
        $isRootAdmin = $data['is_root_admin'] ?? null;
        
        if (!empty($data['password'])) {
            if ($isRootAdmin !== null) {
                $sql = "UPDATE users SET full_name = ?, phone = ?, email = ?, password = ?, role = ?, is_root_admin = ? WHERE user_id = ?";
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                return $this->db->execute($sql, [
                    $data['full_name'],
                    $data['phone'] ?? null,
                    $data['email'] ?? null,
                    $hashedPassword,
                    $data['role'],
                    $isRootAdmin,
                    $id
                ]);
            } else {
                $sql = "UPDATE users SET full_name = ?, phone = ?, email = ?, password = ?, role = ? WHERE user_id = ?";
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                return $this->db->execute($sql, [
                    $data['full_name'],
                    $data['phone'] ?? null,
                    $data['email'] ?? null,
                    $hashedPassword,
                    $data['role'],
                    $id
                ]);
            }
        } else {
            if ($isRootAdmin !== null) {
                $sql = "UPDATE users SET full_name = ?, phone = ?, email = ?, role = ?, is_root_admin = ? WHERE user_id = ?";
                return $this->db->execute($sql, [
                    $data['full_name'],
                    $data['phone'] ?? null,
                    $data['email'] ?? null,
                    $data['role'],
                    $isRootAdmin,
                    $id
                ]);
            } else {
                $sql = "UPDATE users SET full_name = ?, phone = ?, email = ?, role = ? WHERE user_id = ?";
                return $this->db->execute($sql, [
                    $data['full_name'],
                    $data['phone'] ?? null,
                    $data['email'] ?? null,
                    $data['role'],
                    $id
                ]);
            }
        }
    }
    
    /**
     * Xóa user (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE users SET is_active = 0 WHERE user_id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Tìm user theo username
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->query($sql, [$username]);
        return $stmt->fetch();
    }
    
    /**
     * Kiểm tra username đã tồn tại
     */
    private function usernameExists($username) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $stmt = $this->db->query($sql, [$username]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Kiểm tra quyền
     */
    public function checkPermission($userId, $permission) {
        $user = $this->getById($userId);
        if (!$user) return false;
        
        // Manager có tất cả quyền
        if ($user['role'] === 'manager') {
            return true;
        }
        
        // Staff chỉ có quyền hạn chế
        $staffPermissions = ['sales', 'medicine_search', 'inventory_check'];
        return in_array($permission, $staffPermissions);
    }
}
