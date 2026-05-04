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
     * Lấy log theo ID
     */
    public function getById($id) {
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE al.log_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
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
        // First count how many will be deleted
        $countSql = "SELECT COUNT(*) as total FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $countStmt = $this->db->query($countSql, [$days]);
        $count = $countStmt->fetch()['total'];
        
        // Then delete them
        $sql = "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $this->db->execute($sql, [$days]);
        
        return $count;
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
    
    /**
     * Lấy logs với filters
     */
    public function getFilteredLogs($filters, $limit, $offset) {
        $sql = "SELECT al.*, u.username, u.full_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $sql .= " AND al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(al.created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(al.created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy danh sách actions duy nhất
     */
    public function getUniqueActions() {
        $sql = "SELECT DISTINCT action FROM audit_logs ORDER BY action";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Lấy danh sách tables duy nhất
     */
    public function getUniqueTables() {
        $sql = "SELECT DISTINCT table_name FROM audit_logs ORDER BY table_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Thống kê theo user
     */
    public function getUserStatistics($startDate, $endDate) {
        $sql = "SELECT u.full_name, u.username, COUNT(*) as activity_count
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.user_id
                WHERE DATE(al.created_at) BETWEEN ? AND ?
                GROUP BY al.user_id, u.full_name, u.username
                ORDER BY activity_count DESC
                LIMIT 10";
        
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Thống kê theo table
     */
    public function getTableStatistics($startDate, $endDate) {
        $sql = "SELECT table_name, COUNT(*) as activity_count
                FROM audit_logs
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY table_name
                ORDER BY activity_count DESC";
        
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Thống kê theo ngày
     */
    public function getDailyStatistics($startDate, $endDate) {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as activity_count
                FROM audit_logs
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
        
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Đếm logs hôm nay
     */
    public function getTodayCount() {
        $sql = "SELECT COUNT(*) as count FROM audit_logs WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Đếm logs tuần này
     */
    public function getWeekCount() {
        $sql = "SELECT COUNT(*) as count FROM audit_logs WHERE YEARWEEK(created_at) = YEARWEEK(NOW())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Đếm số người dùng hoạt động hôm nay
     */
    public function getActiveUsersCount() {
        $sql = "SELECT COUNT(DISTINCT user_id) as count FROM audit_logs WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
