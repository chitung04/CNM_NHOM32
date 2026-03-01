<?php
/**
 * Script sửa database - Xóa và tạo lại
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Sửa Database</h2>";

// Load config
require_once 'config/config.php';

try {
    // Kết nối MySQL
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Kết nối MySQL thành công</p>";
    
    // Bước 1: Xóa database cũ
    echo "<h3>Bước 1: Xóa database cũ</h3>";
    try {
        $pdo->exec("DROP DATABASE IF EXISTS " . DB_NAME);
        echo "<p style='color: green;'>✓ Đã xóa database cũ</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠ Không thể xóa database: " . $e->getMessage() . "</p>";
        echo "<p>Thử xóa thủ công các bảng...</p>";
        
        // Thử xóa từng bảng
        try {
            $pdo->exec("USE " . DB_NAME);
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Tắt foreign key check
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            foreach ($tables as $table) {
                try {
                    $pdo->exec("DROP TABLE IF EXISTS `$table`");
                    echo "<p style='color: blue;'>✓ Xóa bảng: $table</p>";
                } catch (PDOException $e) {
                    echo "<p style='color: red;'>✗ Không xóa được bảng $table: " . $e->getMessage() . "</p>";
                }
            }
            
            // Bật lại foreign key check
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</p>";
        }
    }
    
    // Bước 2: Tạo database mới
    echo "<h3>Bước 2: Tạo database mới</h3>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);
    echo "<p style='color: green;'>✓ Đã tạo database mới</p>";
    
    // Bước 3: Import schema
    echo "<h3>Bước 3: Import schema</h3>";
    
    $sqlFile = __DIR__ . '/database_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("File database_schema.sql không tồn tại!");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Loại bỏ các dòng CREATE DATABASE và USE
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE .*?;/i', '', $sql);
    
    // Tách các câu lệnh SQL
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            $stmt = trim($stmt);
            return !empty($stmt) && 
                   strpos($stmt, '--') !== 0;
        }
    );
    
    echo "<p>📝 Thực thi " . count($statements) . " câu lệnh SQL</p>";
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $pdo->exec($statement);
            $success++;
            
            // Hiển thị tiến trình
            if (preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                echo "<p style='color: green;'>✓ Tạo bảng: {$matches[1]}</p>";
            } elseif (preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                echo "<p style='color: blue;'>✓ Insert dữ liệu: {$matches[1]}</p>";
            } elseif (preg_match('/ALTER TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                echo "<p style='color: orange;'>✓ Alter bảng: {$matches[1]}</p>";
            }
            
        } catch (PDOException $e) {
            $errors++;
            echo "<p style='color: red;'>✗ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Hoàn thành!</h3>";
    echo "<p><strong>Thành công:</strong> $success câu lệnh</p>";
    echo "<p><strong>Lỗi:</strong> $errors câu lệnh</p>";
    
    // Kiểm tra các bảng
    echo "<hr>";
    echo "<h3>📊 Danh sách bảng đã tạo:</h3>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: red;'>❌ Không có bảng nào được tạo!</p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>Tên bảng</th><th>Số records</th></tr>";
        
        foreach ($tables as $table) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $countStmt->fetchColumn();
            echo "<tr><td><strong>$table</strong></td><td>$count</td></tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🏠 Về trang chủ</a>";
    echo "<a href='simple_login.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🔐 Đăng nhập</a>";
    echo "<a href='test_login.php' style='display: inline-block; padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🧪 Test hệ thống</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Lỗi nghiêm trọng:</p>";
    echo "<pre style='background: #fff; padding: 15px; border: 2px solid red;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 900px;
        margin: 30px auto;
        padding: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    h2, h3 {
        color: #333;
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    p {
        margin: 8px 0;
        padding: 8px;
        background: white;
        border-radius: 3px;
    }
    hr {
        border: none;
        border-top: 2px solid rgba(255,255,255,0.3);
        margin: 20px 0;
    }
    table {
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    pre {
        font-size: 12px;
        line-height: 1.4;
    }
</style>
