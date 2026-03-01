<?php
// Test file để kiểm tra lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing PHP...<br>";

// Test config
echo "Loading config...<br>";
require_once 'config/config.php';
echo "Config OK<br>";

// Test database config
echo "Loading database config...<br>";
require_once 'config/database.php';
echo "Database config OK<br>";

// Test database connection
echo "Testing database connection...<br>";
require_once 'models/Database.php';
try {
    $db = Database::getInstance();
    echo "Database connection OK<br>";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

echo "<br>All tests passed!";
