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
        $sql = "SELECT user_id, username, full_name, role, created_at FROM users WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Lấy tất cả users
     */
    public function getAll() {
        $sql = "SELECT user_id, username, full_name, role, is_active, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo user mới
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['username']) || empty($data['password']) || 
            empty($data['full_name']) || empty($data['role'])) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc");
        }
        
        // Check username uniqueness
        if ($this->usernameExists($data['username'])) {
            throw new Exception("Tên đăng nhập đã tồn tại");
        }
        
        $sql = "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)";
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->db->execute($sql, [
            $data['username'],
            $hashedPassword,
            $data['full_name'],
            $data['role']
        ]);
    }
    
    /**
     * Cập nhật user
     */
    public function update($id, $data) {
        if (!empty($data['password'])) {
            $sql = "UPDATE users SET full_name = ?, password = ?, role = ? WHERE user_id = ?";
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            return $this->db->execute($sql, [
                $data['full_name'],
                $hashedPassword,
                $data['role'],
                $id
            ]);
        } else {
            $sql = "UPDATE users SET full_name = ?, role = ? WHERE user_id = ?";
            return $this->db->execute($sql, [
                $data['full_name'],
                $data['role'],
                $id
            ]);
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
