<?php
require_once 'Database.php';

class InvoiceDetail {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy chi tiết hóa đơn theo invoice_id
     */
    public function getByInvoiceId($invoiceId) {
        $sql = "SELECT id.*, m.medicine_name, u.unit_name, b.batch_id
                FROM invoice_details id
                LEFT JOIN medicines m ON id.medicine_id = m.medicine_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                LEFT JOIN batches b ON id.batch_id = b.batch_id
                WHERE id.invoice_id = ?
                ORDER BY id.detail_id ASC";
        $stmt = $this->db->query($sql, [$invoiceId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy chi tiết theo ID
     */
    public function getById($id) {
        $sql = "SELECT id.*, m.medicine_name, u.unit_name
                FROM invoice_details id
                LEFT JOIN medicines m ON id.medicine_id = m.medicine_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE id.detail_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Tạo chi tiết hóa đơn mới
     */
    public function create($data) {
        $sql = "INSERT INTO invoice_details (invoice_id, medicine_id, batch_id, quantity, unit_price, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $data['invoice_id'],
            $data['medicine_id'],
            $data['batch_id'],
            $data['quantity'],
            $data['unit_price'],
            $data['subtotal']
        ]);
    }
    
    /**
     * Lấy thống kê bán hàng theo thuốc
     */
    public function getSalesByMedicine($medicineId, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                    m.medicine_name,
                    SUM(id.quantity) as total_quantity,
                    SUM(id.subtotal) as total_revenue,
                    COUNT(DISTINCT id.invoice_id) as order_count
                FROM invoice_details id
                INNER JOIN invoices i ON id.invoice_id = i.invoice_id
                INNER JOIN medicines m ON id.medicine_id = m.medicine_id
                WHERE id.medicine_id = ?";
        
        $params = [$medicineId];
        
        if ($startDate && $endDate) {
            $sql .= " AND DATE(i.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $sql .= " GROUP BY m.medicine_id, m.medicine_name";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Lấy lịch sử bán của một batch
     */
    public function getByBatch($batchId) {
        $sql = "SELECT id.*, i.invoice_number, i.created_at, m.medicine_name
                FROM invoice_details id
                INNER JOIN invoices i ON id.invoice_id = i.invoice_id
                INNER JOIN medicines m ON id.medicine_id = m.medicine_id
                WHERE id.batch_id = ?
                ORDER BY i.created_at DESC";
        $stmt = $this->db->query($sql, [$batchId]);
        return $stmt->fetchAll();
    }
}
