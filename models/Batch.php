<?php
require_once 'Database.php';

class Batch {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy tất cả lô thuốc
     */
    public function getAll() {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT b.*, m.medicine_name, s.supplier_name,
                DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
                FROM batches b
                LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
                LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
                WHERE b.pharmacy_id = ?
                ORDER BY b.batch_id ASC";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy lô thuốc theo ID
     */
    public function getById($id) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT b.*, m.medicine_name, s.supplier_name,
                DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
                FROM batches b
                LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
                LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
                WHERE b.batch_id = ? AND b.pharmacy_id = ?";
        $stmt = $this->db->query($sql, [$id, $pharmacyId]);
        return $stmt->fetch();
    }
    
    /**
     * Lấy lô thuốc theo medicine_id
     */
    public function getByMedicine($medicineId) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT b.*, s.supplier_name,
                DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
                FROM batches b
                LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
                WHERE b.medicine_id = ? AND b.pharmacy_id = ? AND b.status = 'active'
                ORDER BY b.expiry_date ASC";
        $stmt = $this->db->query($sql, [$medicineId, $pharmacyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo lô thuốc mới
     */
    public function create($data) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        // Validate
        if (empty($data['medicine_id']) || empty($data['quantity']) || 
            empty($data['expiry_date']) || empty($data['import_date'])) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc");
        }
        
        if ($data['quantity'] <= 0) {
            throw new Exception("Số lượng phải lớn hơn 0");
        }
        
        // Kiểm tra định dạng ngày tháng
        if (!$this->isValidDate($data['import_date'])) {
            throw new Exception("Ngày nhập không đúng định dạng");
        }
        
        if (!$this->isValidDate($data['expiry_date'])) {
            throw new Exception("Ngày hết hạn không đúng định dạng");
        }
        
        // Kiểm tra ngày hết hạn
        if (strtotime($data['expiry_date']) < strtotime($data['import_date'])) {
            throw new Exception("Ngày hết hạn phải sau ngày nhập kho");
        }
        
        // Tạo QR code unique
        require_once 'helpers/qrcode.php';
        $qrCode = generateUniqueQRCode('BATCH');
        
        $sql = "INSERT INTO batches (medicine_id, supplier_id, quantity, expiry_date, import_date, qr_code, pharmacy_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $result = $this->db->execute($sql, [
            $data['medicine_id'],
            $data['supplier_id'] ?? null,
            $data['quantity'],
            $data['expiry_date'],
            $data['import_date'],
            $qrCode,
            $pharmacyId
        ]);
        
        if ($result) {
            $id = $this->db->lastInsertId();
            
            // Tạo URL dẫn đến trang thông tin thuốc
            $qrData = generateMedicineQRData($id, $qrCode, $data['medicine_id']);
            
            // Tạo file QR code
            generateQRCode($qrData, $qrCode);
            
            return $id;
        }
        
        return false;
    }
    
    /**
     * Kiểm tra định dạng ngày tháng hợp lệ
     */
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Cập nhật lô thuốc
     */
    public function update($id, $data) {
        $sql = "UPDATE batches 
                SET supplier_id = ?, quantity = ?, expiry_date = ?, import_date = ?
                WHERE batch_id = ?";
        
        return $this->db->execute($sql, [
            $data['supplier_id'] ?? null,
            $data['quantity'],
            $data['expiry_date'],
            $data['import_date'],
            $id
        ]);
    }
    
    /**
     * Cập nhật số lượng lô thuốc
     */
    public function updateQuantity($id, $quantity) {
        $sql = "UPDATE batches SET quantity = ? WHERE batch_id = ?";
        return $this->db->execute($sql, [$quantity, $id]);
    }
    
    /**
     * Lấy lô thuốc sắp hết hạn
     */
    public function getExpiringBatches($days = 30) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT b.*, m.medicine_name, s.supplier_name,
                DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
                FROM batches b
                LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
                LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
                WHERE b.pharmacy_id = ?
                AND b.status = 'active' 
                AND DATEDIFF(b.expiry_date, CURDATE()) <= ?
                AND DATEDIFF(b.expiry_date, CURDATE()) >= 0
                ORDER BY b.expiry_date ASC";
        $stmt = $this->db->query($sql, [$pharmacyId, $days]);
        return $stmt->fetchAll();
    }
    
    /**
     * Cập nhật status lô hết hạn
     */
    public function updateExpiredBatches() {
        $sql = "UPDATE batches 
                SET status = 'expired' 
                WHERE expiry_date < CURDATE() AND status = 'active'";
        return $this->db->execute($sql);
    }
}
