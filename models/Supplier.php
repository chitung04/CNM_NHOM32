<?php
require_once 'Database.php';

class Supplier {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM suppliers ORDER BY supplier_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM suppliers WHERE supplier_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        if (empty($data['supplier_name'])) {
            throw new Exception("Vui lòng nhập tên nhà cung cấp");
        }
        
        $sql = "INSERT INTO suppliers (supplier_name, phone, email, address) VALUES (?, ?, ?, ?)";
        return $this->db->execute($sql, [
            $data['supplier_name'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null
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
