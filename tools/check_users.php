<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT COUNT(*) AS cnt FROM users");
    $row = $stmt->fetch();
    echo "Users table exists. Count: " . ($row['cnt'] ?? '0') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
