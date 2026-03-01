<?php
require_once 'Database.php';

class Medicine {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy tất cả thuốc
     */
    public function getAll() {
        $sql = "SELECT m.*, c.category_name, u.unit_name 
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                ORDER BY m.medicine_id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy thuốc theo ID
     */
    public function getById($id) {
        $sql = "SELECT m.*, c.category_name, u.unit_name 
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE m.medicine_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Tìm kiếm thuốc theo tên
     */
    public function search($keyword) {
        $sql = "SELECT m.*, c.category_name, u.unit_name 
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE m.medicine_name LIKE ?
                ORDER BY m.medicine_name ASC";
        $stmt = $this->db->query($sql, ['%' . $keyword . '%']);
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo thuốc mới
     */
    public function create($data) {
        // Validate
        if (empty($data['medicine_name']) || empty($data['price'])) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc");
        }
        
        if ($data['price'] < 0) {
            throw new Exception("Giá thuốc không được âm");
        }
        
        // Tạo QR code unique
        require_once 'helpers/qrcode.php';
        $qrCode = generateUniqueQRCode('MED');
        
        $sql = "INSERT INTO medicines (medicine_name, category_id, unit_id, price, description, qr_code) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $result = $this->db->execute($sql, [
            $data['medicine_name'],
            $data['category_id'] ?? null,
            $data['unit_id'] ?? null,
            $data['price'],
            $data['description'] ?? null,
            $qrCode
        ]);
        
        if ($result) {
            $id = $this->db->lastInsertId();
            
            // Tạo file QR code
            $qrData = "MEDICINE_ID:" . $id . "|CODE:" . $qrCode;
            generateQRCode($qrData, $qrCode);
            
            return $id;
        }
        
        return false;
    }
    
    /**
     * Cập nhật thuốc
     */
    public function update($id, $data) {
        // Validate
        if (empty($data['medicine_name']) || empty($data['price'])) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc");
        }
        
        if ($data['price'] < 0) {
            throw new Exception("Giá thuốc không được âm");
        }
        
        $sql = "UPDATE medicines 
                SET medicine_name = ?, category_id = ?, unit_id = ?, price = ?, description = ?
                WHERE medicine_id = ?";
        
        return $this->db->execute($sql, [
            $data['medicine_name'],
            $data['category_id'] ?? null,
            $data['unit_id'] ?? null,
            $data['price'],
            $data['description'] ?? null,
            $id
        ]);
    }
    
    /**
     * Xóa thuốc
     */
    public function delete($id) {
        // Kiểm tra xem thuốc có trong hóa đơn không
        $sql = "SELECT COUNT(*) as count FROM invoice_details WHERE medicine_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Không thể xóa thuốc đã có trong hóa đơn");
        }
        
        $sql = "DELETE FROM medicines WHERE medicine_id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Lấy thuốc theo QR code
     */
    public function getByQRCode($qrcode) {
        $sql = "SELECT m.*, c.category_name, u.unit_name 
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE m.qr_code = ?";
        $stmt = $this->db->query($sql, [$qrcode]);
        return $stmt->fetch();
    }
    
    /**
     * Lấy tổng tồn kho của thuốc
     */
    public function getTotalInventory($medicineId) {
        $sql = "SELECT COALESCE(SUM(quantity), 0) as total 
                FROM batches 
                WHERE medicine_id = ? AND status = 'active'";
        $stmt = $this->db->query($sql, [$medicineId]);
        $result = $stmt->fetch();
        return $result['total'];
    }
}
