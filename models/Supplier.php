<?php
require_once 'Database.php';

class Supplier {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT * FROM suppliers WHERE pharmacy_id = ? ORDER BY supplier_name ASC";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT * FROM suppliers WHERE supplier_id = ? AND pharmacy_id = ?";
        $stmt = $this->db->query($sql, [$id, $pharmacyId]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        if (empty($data['supplier_name'])) {
            throw new Exception("Vui lòng nhập tên nhà cung cấp");
        }
        
        $sql = "INSERT INTO suppliers (supplier_name, phone, email, address, pharmacy_id) VALUES (?, ?, ?, ?, ?)";
        return $this->db->execute($sql, [
            $data['supplier_name'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $pharmacyId
        ]);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE suppliers SET supplier_name = ?, phone = ?, email = ?, address = ? WHERE supplier_id = ?";
        return $this->db->execute($sql, [
            $data['supplier_name'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM suppliers WHERE supplier_id = ?";
        return $this->db->execute($sql, [$id]);
    }
}
