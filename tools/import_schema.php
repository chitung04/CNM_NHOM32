<?php
/**
 * Script import schema bằng PDO
 * Usage (CLI): php tools/import_schema.php
 * This will read database_schema.sql in project root and execute statements.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    // Load DB constants
    if (!defined('DB_HOST') || !defined('DB_NAME')) {
        throw new Exception('Database configuration missing');
    }

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $sqlFile = __DIR__ . '/../database_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('database_schema.sql not found');
    }

    $sql = file_get_contents($sqlFile);

    // Simple split by ";\n" - should work for this schema file
    $statements = preg_split('/;\s*\n/', $sql);

    $pdo->beginTransaction();
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '' || strpos($stmt, '--') === 0) continue;
        $pdo->exec($stmt);
    }
    $pdo->commit();

    echo "Schema imported successfully.\n";
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Import failed: " . $e->getMessage() . "\n";
    exit(1);
}
