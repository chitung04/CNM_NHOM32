<?php
require_once 'Database.php';

class Unit {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM units ORDER BY unit_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM units WHERE unit_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
}
