<?php
require_once 'Database.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT * FROM categories WHERE pharmacy_id = ? ORDER BY category_name ASC";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT * FROM categories WHERE category_id = ? AND pharmacy_id = ?";
        $stmt = $this->db->query($sql, [$id, $pharmacyId]);
        return $stmt->fetch();
    }
}
