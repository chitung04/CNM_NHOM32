<?php
require_once 'Database.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM categories ORDER BY category_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE category_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
}
