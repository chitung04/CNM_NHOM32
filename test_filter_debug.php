<?php
session_start();
require_once 'config/database.php';
require_once 'models/Category.php';
require_once 'models/Medicine.php';

// Test categories
$categoryModel = new Category();
$categories = $categoryModel->getAll();

echo "<h3>Categories trong database:</h3>";
echo "<pre>";
print_r($categories);
echo "</pre>";

// Test medicines with categories
$medicineModel = new Medicine();
$medicines = $medicineModel->getAll();

echo "<h3>Medicines với category_id:</h3>";
echo "<pre>";
foreach ($medicines as $med) {
    echo "ID: " . $med['medicine_id'] . " - Name: " . $med['medicine_name'] . " - Category ID: " . ($med['category_id'] ?? 'NULL') . "\n";
}
echo "</pre>";

// Test specific medicine with category info
$db = Database::getInstance();
$sql = "SELECT m.*, c.category_name 
        FROM medicines m 
        LEFT JOIN categories c ON m.category_id = c.category_id 
        LIMIT 10";
$stmt = $db->query($sql);
$medicinesWithCategory = $stmt->fetchAll();

echo "<h3>Medicines với category_name:</h3>";
echo "<pre>";
print_r($medicinesWithCategory);
echo "</pre>";
?>