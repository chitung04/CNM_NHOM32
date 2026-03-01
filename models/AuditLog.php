<?php
require_once 'Database.php';

class AuditLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Ghi log hoạt động
     */
    public function log($action, $tableName, $recordId = null, $oldValues = null, $newValues = null) {
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress,
            $userAgent
        ]);
    }
    
    /**
     * Lấy tất cả logs
     */
    public function getAll($limit = 100, $offset = 0) {
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->query($sql, [$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy logs theo user
     */
    public function getByUser($userId, $limit = 50) {
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE al.user_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        $stmt = $this->db->query($sql, [$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy logs theo bảng và record
     */
    public function getByRecord($tableName, $recordId, $limit = 20) {
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE al.table_name = ? AND al.record_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        $stmt = $this->db->query($sql, [$tableName, $recordId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy logs theo khoảng thời gian
     */
    public function getByDateRange($startDate, $endDate, $limit = 100) {
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE DATE(al.created_at) BETWEEN ? AND ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        $stmt = $this->db->query($sql, [$startDate, $endDate, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy logs theo action
     */
    public function getByAction($action, $limit = 50) {
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE al.action = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        $stmt = $this->db->query($sql, [$action, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Đếm tổng số logs
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM audit_logs WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $sql .= " AND table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Xóa logs cũ (cleanup)
     */
    public function cleanup($days = 90) {
        $sql = "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->execute($sql, [$days]);
    }
    
    /**
     * Lấy thống kê hoạt động
     */
    public function getStatistics($startDate = null, $endDate = null) {
        $sql = "SELECT 
                    action,
                    COUNT(*) as count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM audit_logs
                WHERE 1=1";
        
        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $sql .= " GROUP BY action ORDER BY count DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
}
