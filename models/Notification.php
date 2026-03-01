<?php
require_once 'Database.php';

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Tạo thông báo mới
     */
    public function create($type, $message, $referenceId = null) {
        $sql = "INSERT INTO notifications (type, message, reference_id) VALUES (?, ?, ?)";
        return $this->db->execute($sql, [$type, $message, $referenceId]);
    }
    
    /**
     * Lấy tất cả thông báo chưa đọc
     */
    public function getUnread() {
        $sql = "SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 50";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead($id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Đánh dấu tất cả đã đọc
     */
    public function markAllAsRead() {
        $sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";
        return $this->db->execute($sql);
    }
    
    /**
     * Kiểm tra và tạo thông báo thuốc sắp hết hàng
     */
    public function checkLowStock() {
        $sql = "SELECT m.medicine_id, m.medicine_name, 
                COALESCE(SUM(b.quantity), 0) as total_stock
                FROM medicines m
                LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
                GROUP BY m.medicine_id, m.medicine_name
                HAVING total_stock < ?";
        
        $stmt = $this->db->query($sql, [LOW_STOCK_THRESHOLD]);
        $lowStockMedicines = $stmt->fetchAll();
        
        foreach ($lowStockMedicines as $medicine) {
            // Kiểm tra xem đã có thông báo chưa
            $checkSql = "SELECT COUNT(*) as count FROM notifications 
                        WHERE type = 'low_stock' 
                        AND reference_id = ? 
                        AND is_read = 0";
            $checkStmt = $this->db->query($checkSql, [$medicine['medicine_id']]);
            $exists = $checkStmt->fetch();
            
            if ($exists['count'] == 0) {
                $message = "Thuốc '{$medicine['medicine_name']}' sắp hết hàng. Tồn kho: {$medicine['total_stock']}";
                $this->create('low_stock', $message, $medicine['medicine_id']);
            }
        }
    }
    
    /**
     * Kiểm tra và tạo thông báo thuốc sắp hết hạn
     */
    public function checkExpiring() {
        $sql = "SELECT b.batch_id, b.expiry_date, m.medicine_name,
                DATEDIFF(b.expiry_date, CURDATE()) as days_left
                FROM batches b
                JOIN medicines m ON b.medicine_id = m.medicine_id
                WHERE b.status = 'active' 
                AND DATEDIFF(b.expiry_date, CURDATE()) <= ?
                AND DATEDIFF(b.expiry_date, CURDATE()) > 0";
        
        $stmt = $this->db->query($sql, [EXPIRY_WARNING_DAYS]);
        $expiringBatches = $stmt->fetchAll();
        
        foreach ($expiringBatches as $batch) {
            // Kiểm tra xem đã có thông báo chưa
            $checkSql = "SELECT COUNT(*) as count FROM notifications 
                        WHERE type = 'expiry_warning' 
                        AND reference_id = ? 
                        AND is_read = 0";
            $checkStmt = $this->db->query($checkSql, [$batch['batch_id']]);
            $exists = $checkStmt->fetch();
            
            if ($exists['count'] == 0) {
                $message = "Lô thuốc '{$batch['medicine_name']}' sắp hết hạn trong {$batch['days_left']} ngày";
                $this->create('expiry_warning', $message, $batch['batch_id']);
            }
        }
    }
    
    /**
     * Đếm số thông báo chưa đọc
     */
    public function countUnread() {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE is_read = 0";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }
}
