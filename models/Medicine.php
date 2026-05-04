<?php
require_once 'Database.php';

class Medicine {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy tất cả thuốc (chỉ thuốc active) với QR code từ medicines và batches
     */
    public function getAll() {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT m.*, c.category_name, u.unit_name,
                       COALESCE(m.qr_code, 
                                (SELECT b.qr_code FROM batches b WHERE b.medicine_id = m.medicine_id AND b.qr_code IS NOT NULL LIMIT 1)
                       ) as qr_code,
                       COALESCE(SUM(b.quantity), 0) as inventory
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
                WHERE m.pharmacy_id = ?
                GROUP BY m.medicine_id, m.medicine_name, m.category_id, m.unit_id, m.price, m.description, m.qr_code, c.category_name, u.unit_name
                ORDER BY m.medicine_id ASC";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy thuốc theo ID
     */
    public function getById($id) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT m.*, c.category_name, u.unit_name,
                       COALESCE(m.qr_code, 
                                (SELECT b.qr_code FROM batches b WHERE b.medicine_id = m.medicine_id AND b.qr_code IS NOT NULL LIMIT 1)
                       ) as qr_code
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE m.medicine_id = ? AND m.pharmacy_id = ?";
        $stmt = $this->db->query($sql, [$id, $pharmacyId]);
        return $stmt->fetch();
    }
    
    /**
     * Tìm kiếm thuốc theo tên hoặc mô tả
     */
    public function search($keyword) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT m.*, c.category_name, u.unit_name 
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE m.pharmacy_id = ?
                  AND (m.medicine_name LIKE ? OR m.description LIKE ?)
                ORDER BY m.medicine_name ASC";
        $stmt = $this->db->query($sql, [$pharmacyId, '%' . $keyword . '%', '%' . $keyword . '%']);
        return $stmt->fetchAll();
    }
    
    /**
     * Tìm kiếm gợi ý (cho AJAX autocomplete) với QR code
     */
    public function searchSuggestions($keyword, $limit = 10) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT m.medicine_id, m.medicine_name, m.price, c.category_name,
                       COALESCE(SUM(b.quantity), 0) as inventory,
                       (SELECT b2.qr_code FROM batches b2 WHERE b2.medicine_id = m.medicine_id AND b2.qr_code IS NOT NULL LIMIT 1) as qr_code
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
                WHERE m.pharmacy_id = ?
                  AND (m.medicine_name LIKE ? OR m.description LIKE ?)
                GROUP BY m.medicine_id, m.medicine_name, m.price, c.category_name
                ORDER BY m.medicine_name ASC
                LIMIT ?";
        $stmt = $this->db->query($sql, [$pharmacyId, '%' . $keyword . '%', '%' . $keyword . '%', $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo thuốc mới
     */
    public function create($data) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        // Validate dữ liệu đầu vào
        $errors = $this->validateMedicineData($data);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        // Tạo QR code unique
        require_once 'helpers/qrcode.php';
        $qrCode = generateUniqueQRCode('MED');
        
        $sql = "INSERT INTO medicines (medicine_name, category_id, unit_id, price, description, qr_code, pharmacy_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $result = $this->db->execute($sql, [
            $data['medicine_name'],
            $data['category_id'] ?? null,
            $data['unit_id'] ?? null,
            $data['price'],
            $data['description'] ?? null,
            $qrCode,
            $pharmacyId
        ]);
        
        if ($result) {
            $id = $this->db->lastInsertId();
            
            // Tạo dữ liệu QR code với thông tin chi tiết thuốc
            $qrData = $this->generateMedicineQRData($id, $qrCode, $data);
            
            // Tạo file QR code
            generateQRCode($qrData, $qrCode);
            
            return $id;
        }
        
        return false;
    }
    
    /**
     * Tạo dữ liệu QR code cho thuốc
     */
    private function generateMedicineQRData($medicineId, $qrCode, $medicineData) {
        // Tạo URL Google Search với thông tin thuốc
        require_once 'helpers/qrcode.php';
        
        return generateGoogleSearchURL(
            $medicineData['medicine_name'],
            '', // manufacturer không có trong DB
            ''  // active_ingredient không có trong DB
        );
    }
    
    /**
     * Cập nhật QR code khi sửa thuốc
     */
    public function updateQRCode($medicineId) {
        $medicine = $this->getById($medicineId);
        if (!$medicine || empty($medicine['qr_code'])) {
            return false;
        }
        
        require_once 'helpers/qrcode.php';
        
        // Tạo lại dữ liệu QR code với Google Search URL
        $qrData = generateGoogleSearchURL(
            $medicine['medicine_name'],
            '', // manufacturer không có trong DB
            ''  // active_ingredient không có trong DB
        );
        
        // Tạo lại file QR code
        return generateQRCode($qrData, $medicine['qr_code']);
    }
    
    /**
     * Cập nhật thuốc
     */
    public function update($id, $data) {
        // Validate dữ liệu đầu vào
        $errors = $this->validateMedicineData($data);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
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
     * Validate dữ liệu thuốc
     */
    private function validateMedicineData($data) {
        $errors = [];
        
        // Kiểm tra tên thuốc
        if (empty($data['medicine_name'])) {
            $errors[] = "Tên thuốc không được để trống";
        } elseif (strlen($data['medicine_name']) < 2) {
            $errors[] = "Tên thuốc phải có ít nhất 2 ký tự";
        } elseif (strlen($data['medicine_name']) > 255) {
            $errors[] = "Tên thuốc không được vượt quá 255 ký tự";
        }
        
        // Kiểm tra giá
        if (empty($data['price']) && $data['price'] !== '0') {
            $errors[] = "Giá bán không được để trống";
        } elseif (!is_numeric($data['price'])) {
            $errors[] = "Giá bán phải là số";
        } elseif ($data['price'] < 0) {
            $errors[] = "Giá bán không được âm";
        } elseif ($data['price'] > 999999999) {
            $errors[] = "Giá bán quá lớn";
        }
        
        // Kiểm tra category_id nếu có
        if (!empty($data['category_id']) && !is_numeric($data['category_id'])) {
            $errors[] = "Danh mục không hợp lệ";
        }
        
        // Kiểm tra unit_id nếu có
        if (!empty($data['unit_id']) && !is_numeric($data['unit_id'])) {
            $errors[] = "Đơn vị tính không hợp lệ";
        }
        
        // Kiểm tra mô tả
        if (!empty($data['description']) && strlen($data['description']) > 1000) {
            $errors[] = "Mô tả không được vượt quá 1000 ký tự";
        }
        
        return $errors;
    }
    
    /**
     * Xóa thuốc (xóa mềm)
     */
    public function delete($id) {
        // Kiểm tra xem thuốc có trong hóa đơn không
        $sql = "SELECT COUNT(*) as count FROM invoice_details WHERE medicine_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            // Nếu có trong hóa đơn, không cho xóa để giữ tính toàn vẹn dữ liệu
            throw new Exception("Không thể xóa thuốc đã có trong hóa đơn để giữ lịch sử giao dịch");
        } else {
            // Nếu chưa có trong hóa đơn nào thì có thể xóa cứng
            $sql = "DELETE FROM medicines WHERE medicine_id = ?";
            return $this->db->execute($sql, [$id]);
        }
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
