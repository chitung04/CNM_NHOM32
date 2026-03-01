<?php
/**
 * Script tự động import database schema
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Cài đặt Database</h2>";

// Load config
require_once 'config/config.php';

try {
    // Kết nối MySQL (không chọn database)
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Kết nối MySQL thành công</p>";
    
    // Đọc file SQL
    $sqlFile = __DIR__ . '/database_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("File database_schema.sql không tồn tại!");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "<p>✓ Đọc file SQL thành công</p>";
    
    // Tách các câu lệnh SQL
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   strpos($stmt, '--') !== 0 && 
                   $stmt !== '';
        }
    );
    
    echo "<p>📝 Tìm thấy " . count($statements) . " câu lệnh SQL</p>";
    echo "<hr>";
    
    // Thực thi từng câu lệnh
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            $pdo->exec($statement);
            $success++;
            
            // Hiển thị tiến trình
            if (preg_match('/CREATE TABLE (\w+)/i', $statement, $matches)) {
                echo "<p style='color: green;'>✓ Tạo bảng: {$matches[1]}</p>";
            } elseif (preg_match('/INSERT INTO (\w+)/i', $statement, $matches)) {
                echo "<p style='color: blue;'>✓ Insert dữ liệu vào: {$matches[1]}</p>";
            } elseif (preg_match('/ALTER TABLE (\w+)/i', $statement, $matches)) {
                echo "<p style='color: orange;'>✓ Alter bảng: {$matches[1]}</p>";
            } elseif (preg_match('/CREATE DATABASE/i', $statement)) {
                echo "<p style='color: purple;'>✓ Tạo database: pharmacy_db</p>";
            }
            
        } catch (PDOException $e) {
            $errors++;
            // Bỏ qua lỗi "already exists"
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "<p style='color: red;'>✗ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Hoàn thành!</h3>";
    echo "<p>Thành công: $success câu lệnh</p>";
    if ($errors > 0) {
        echo "<p style='color: orange;'>Lỗi/Bỏ qua: $errors câu lệnh</p>";
    }
    
    // Kiểm tra các bảng đã tạo
    echo "<hr>";
    echo "<h3>📊 Kiểm tra các bảng:</h3>";
    
    $pdo->exec("USE " . DB_NAME);
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tables as $table) {
        // Đếm số record
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $countStmt->fetchColumn();
        echo "<li><strong>$table</strong>: $count records</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><a href='index.php' class='btn'>🏠 Về trang chủ</a></p>";
    echo "<p><a href='simple_login.php' class='btn'>🔐 Đăng nhập</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Chi tiết: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2, h3 {
        color: #333;
    }
    p {
        margin: 5px 0;
    }
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 5px;
    }
    .btn:hover {
        background: #0056b3;
    }
    pre {
        background: #fff;
        padding: 10px;
        border: 1px solid #ddd;
        overflow-x: auto;
    }
</style>
