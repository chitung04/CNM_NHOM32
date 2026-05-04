<?php
require_once 'Database.php';

class Unit {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT * FROM units WHERE pharmacy_id = ? ORDER BY unit_name ASC";
        $stmt = $this->db->query($sql, [$pharmacyId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        require_once 'helpers/pharmacy.php';
        $pharmacyId = requirePharmacyId();
        
        $sql = "SELECT * FROM units WHERE unit_id = ? AND pharmacy_id = ?";
        $stmt = $this->db->query($sql, [$id, $pharmacyId]);
        return $stmt->fetch();
    }
}
