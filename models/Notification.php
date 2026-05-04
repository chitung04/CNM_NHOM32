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
        $basePath = dirname(__DIR__);
        require_once $basePath . '/helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "INSERT INTO notifications (pharmacy_id, type, message, reference_id) VALUES (?, ?, ?, ?)";
        return $this->db->execute($sql, [$pharmacyId, $type, $message, $referenceId]);
    }
    
    /**
     * Lấy tất cả thông báo chưa đọc
     */
    public function getUnread() {
        $basePath = dirname(__DIR__);
        require_once $basePath . '/helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        // Nếu không có pharmacy_id, trả về mảng rỗng
        if (!$pharmacyId) {
            return [];
        }
        
        $sql = "SELECT * FROM notifications WHERE pharmacy_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 50";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead($id) {
        $basePath = dirname(__DIR__);
        require_once $basePath . '/helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND pharmacy_id = ?";
        return $this->db->execute($sql, [$id, $pharmacyId]);
    }
    
    /**
     * Đánh dấu tất cả đã đọc
     */
    public function markAllAsRead() {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND pharmacy_id = ?";
        return $this->db->execute($sql, [$pharmacyId]);
    }
    
    /**
     * Kiểm tra và tạo thông báo thuốc sắp hết hàng
     */
    public function checkLowStock() {
        $basePath = dirname(__DIR__);
        require_once $basePath . '/helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT m.medicine_id, m.medicine_name, u.unit_name,
                COALESCE(SUM(b.quantity), 0) as total_stock
                FROM medicines m
                LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE m.pharmacy_id = ?
                GROUP BY m.medicine_id, m.medicine_name, u.unit_name
                HAVING total_stock < ?";
        
        $stmt = $this->db->query($sql, [$pharmacyId, LOW_STOCK_THRESHOLD]);
        $lowStockMedicines = $stmt->fetchAll();
        
        foreach ($lowStockMedicines as $medicine) {
            // Kiểm tra xem đã có thông báo chưa
            $checkSql = "SELECT COUNT(*) as count FROM notifications 
                        WHERE pharmacy_id = ?
                        AND type = 'low_stock' 
                        AND reference_id = ? 
                        AND is_read = 0";
            $checkStmt = $this->db->query($checkSql, [$pharmacyId, $medicine['medicine_id']]);
            $exists = $checkStmt->fetch();
            
            if ($exists['count'] == 0) {
                $message = "Thuốc {$medicine['medicine_name']} sắp hết hàng (còn {$medicine['total_stock']} {$medicine['unit_name']})";
                $this->create('low_stock', $message, $medicine['medicine_id']);
            }
        }
    }
    
    /**
     * Kiểm tra và tạo thông báo thuốc sắp hết hạn
     */
    public function checkExpiring() {
        $basePath = dirname(__DIR__);
        require_once $basePath . '/helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT b.batch_id, b.expiry_date, m.medicine_name, b.batch_number,
                DATEDIFF(b.expiry_date, CURDATE()) as days_left
                FROM batches b
                JOIN medicines m ON b.medicine_id = m.medicine_id
                WHERE b.pharmacy_id = ?
                AND b.status = 'active' 
                AND DATEDIFF(b.expiry_date, CURDATE()) <= ?
                AND DATEDIFF(b.expiry_date, CURDATE()) > 0";
        
        $stmt = $this->db->query($sql, [$pharmacyId, EXPIRY_WARNING_DAYS]);
        $expiringBatches = $stmt->fetchAll();
        
        foreach ($expiringBatches as $batch) {
            // Kiểm tra xem đã có thông báo chưa
            $checkSql = "SELECT COUNT(*) as count FROM notifications 
                        WHERE pharmacy_id = ?
                        AND type = 'expiry_warning' 
                        AND reference_id = ? 
                        AND is_read = 0";
            $checkStmt = $this->db->query($checkSql, [$pharmacyId, $batch['batch_id']]);
            $exists = $checkStmt->fetch();
            
            if ($exists['count'] == 0) {
                $expiryDate = date('d/m/Y', strtotime($batch['expiry_date']));
                $message = "Lô thuốc {$batch['medicine_name']} (Lô: {$batch['batch_number']}) sắp hết hạn (hết hạn: {$expiryDate})";
                $this->create('expiry_warning', $message, $batch['batch_id']);
            }
        }
    }
    
    /**
     * Đếm số thông báo chưa đọc
     */
    public function countUnread() {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE pharmacy_id = ? AND is_read = 0";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
}
