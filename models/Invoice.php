<?php
require_once 'Database.php';

class Invoice {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy database connection
     */
    public function getConnection() {
        return $this->db->getConnection();
    }
    
    /**
     * Tạo hóa đơn mới
     */
    public function create($data) {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Tạo số hóa đơn
            $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);
            
            // Tạo QR code
            require_once 'helpers/qrcode.php';
            $qrCode = generateUniqueQRCode('INV');
            
            // Insert invoice
            $sql = "INSERT INTO invoices (invoice_number, user_id, total_amount, discount, final_amount, qr_code) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->execute($sql, [
                $invoiceNumber,
                $data['user_id'],
                $data['total_amount'],
                $data['discount'] ?? 0,
                $data['final_amount'],
                $qrCode
            ]);
            
            $invoiceId = $this->db->lastInsertId();
            
            // Insert invoice details và cập nhật inventory
            foreach ($data['items'] as $item) {
                // Insert detail
                $sql = "INSERT INTO invoice_details (invoice_id, medicine_id, batch_id, quantity, unit_price, subtotal) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $this->db->execute($sql, [
                    $invoiceId,
                    $item['medicine_id'],
                    $item['batch_id'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['subtotal']
                ]);
                
                // Cập nhật số lượng batch
                $sql = "UPDATE batches SET quantity = quantity - ? WHERE batch_id = ?";
                $this->db->execute($sql, [$item['quantity'], $item['batch_id']]);
                
                // Cập nhật status nếu hết hàng
                $sql = "UPDATE batches SET status = 'sold_out' WHERE batch_id = ? AND quantity = 0";
                $this->db->execute($sql, [$item['batch_id']]);
            }
            
            // Tạo QR code file
            $qrData = "INVOICE:" . $invoiceNumber . "|ID:" . $invoiceId;
            generateQRCode($qrData, $qrCode);
            
            $conn->commit();
            return $invoiceId;
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Lấy hóa đơn theo ID
     */
    public function getById($id) {
        $sql = "SELECT i.*, u.full_name as staff_name
                FROM invoices i
                LEFT JOIN users u ON i.user_id = u.user_id
                WHERE i.invoice_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Lấy chi tiết hóa đơn
     */
    public function getDetails($invoiceId) {
        $sql = "SELECT id.*, m.medicine_name, u.unit_name
                FROM invoice_details id
                LEFT JOIN medicines m ON id.medicine_id = m.medicine_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE id.invoice_id = ?";
        $stmt = $this->db->query($sql, [$invoiceId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy tất cả hóa đơn
     */
    public function getAll($filters = []) {
        $sql = "SELECT i.*, u.full_name as staff_name
                FROM invoices i
                LEFT JOIN users u ON i.user_id = u.user_id
                ORDER BY i.created_at DESC
                LIMIT 100";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy hóa đơn theo khoảng thời gian
     */
    public function getByDateRange($startDate, $endDate) {
        $sql = "SELECT i.*, u.full_name as staff_name
                FROM invoices i
                LEFT JOIN users u ON i.user_id = u.user_id
                WHERE DATE(i.created_at) BETWEEN ? AND ?
                ORDER BY i.created_at DESC";
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Tính tổng doanh thu
     */
    public function getTotalRevenue($startDate, $endDate) {
        $sql = "SELECT COALESCE(SUM(final_amount), 0) as total
                FROM invoices
                WHERE DATE(created_at) BETWEEN ? AND ?";
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Xóa hóa đơn (chỉ dùng cho đơn chưa hoàn tất)
     */
    public function delete($invoiceId) {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Lấy thông tin invoice details để hoàn trả số lượng
            $details = $this->getDetails($invoiceId);
            
            // Hoàn trả số lượng về batch
            foreach ($details as $item) {
                $sql = "UPDATE batches SET quantity = quantity + ? WHERE batch_id = ?";
                $this->db->execute($sql, [$item['quantity'], $item['batch_id']]);
                
                // Cập nhật lại status nếu batch đã sold_out
                $sql = "UPDATE batches SET status = 'active' WHERE batch_id = ? AND status = 'sold_out'";
                $this->db->execute($sql, [$item['batch_id']]);
            }
            
            // Xóa invoice details
            $sql = "DELETE FROM invoice_details WHERE invoice_id = ?";
            $this->db->execute($sql, [$invoiceId]);
            
            // Xóa invoice
            $sql = "DELETE FROM invoices WHERE invoice_id = ?";
            $this->db->execute($sql, [$invoiceId]);
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Lấy danh sách thuốc bán chạy
     */
    public function getTopSellingMedicines($startDate, $endDate, $limit = 10) {
        $sql = "SELECT 
                    m.medicine_id,
                    m.medicine_name,
                    c.category_name,
                    u.unit_name,
                    SUM(id.quantity) as total_quantity,
                    SUM(id.subtotal) as total_revenue,
                    COUNT(DISTINCT i.invoice_id) as order_count
                FROM invoice_details id
                INNER JOIN invoices i ON id.invoice_id = i.invoice_id
                INNER JOIN medicines m ON id.medicine_id = m.medicine_id
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE DATE(i.created_at) BETWEEN ? AND ?
                GROUP BY m.medicine_id
                ORDER BY total_quantity DESC
                LIMIT ?";
        $stmt = $this->db->query($sql, [$startDate, $endDate, $limit]);
        return $stmt->fetchAll();
    }
}
