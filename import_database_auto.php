<?php
/**
 * Script tự động import database từ file qlnt_db.sql
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>📥 TỰ ĐỘNG IMPORT DATABASE</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Đọc file SQL
    $sqlFile = 'qlnt_db.sql';
    
    if (!file_exists($sqlFile)) {
        die("<p style='color: red;'>❌ Không tìm thấy file {$sqlFile}</p>");
    }
    
    echo "<p>✅ Tìm thấy file: {$sqlFile}</p>";
    
    // Đọc nội dung file
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        die("<p style='color: red;'>❌ Không thể đọc file {$sqlFile}</p>");
    }
    
    echo "<p>✅ Đã đọc file SQL (" . number_format(strlen($sql)) . " bytes)</p>";
    
    // Tách các câu lệnh SQL
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    echo "<p>✅ Tìm thấy " . count($statements) . " câu lệnh SQL</p>";
    echo "<hr>";
    
    // Thực thi từng câu lệnh
    $success = 0;
    $errors = 0;
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    foreach ($statements as $index => $statement) {
        try {
            // Bỏ qua các comment
            if (preg_match('/^(--|\/\*|#)/', trim($statement))) {
                continue;
            }
            
            $conn->exec($statement);
            $success++;
            
            // Hiển thị tiến trình mỗi 50 câu lệnh
            if ($success % 50 == 0) {
                echo "<p>⏳ Đã thực thi {$success} câu lệnh...</p>";
                flush();
            }
            
        } catch (PDOException $e) {
            $errors++;
            // Chỉ hiển thị lỗi quan trọng
            if (!preg_match('/(already exists|duplicate)/i', $e->getMessage())) {
                echo "<p style='color: orange;'>⚠️ Lỗi câu lệnh " . ($index + 1) . ": " . substr($e->getMessage(), 0, 100) . "...</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>✅ HOÀN TẤT IMPORT</h3>";
    echo "<p><strong>Thành công:</strong> {$success} câu lệnh</p>";
    echo "<p><strong>Lỗi:</strong> {$errors} câu lệnh</p>";
    
    // Kiểm tra kết quả
    echo "<hr>";
    echo "<h3>📊 KIỂM TRA KẾT QUẢ</h3>";
    
    $tables = ['pharmacies', 'users', 'categories', 'medicines', 'batches', 'suppliers', 'units'];
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Bảng</th><th>Số dòng</th></tr>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM {$table}");
            $result = $stmt->fetch();
            $total = $result['total'];
            $color = $total > 0 ? 'green' : 'red';
            echo "<tr><td>{$table}</td><td style='color: {$color}; font-weight: bold;'>{$total}</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td>{$table}</td><td style='color: red;'>❌ Lỗi</td></tr>";
        }
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>🎯 TIẾP THEO</h3>";
    echo "<p>1. <a href='index.php?page=login'><strong>Đăng nhập</strong></a> với username: <code>admin</code>, password: <code>123456</code></p>";
    echo "<p>2. <a href='check_sales_medicines.php'><strong>Kiểm tra lại thuốc</strong></a></p>";
    echo "<p>3. <a href='index.php?page=sales'><strong>Vào trang bán hàng</strong></a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ LỖI:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
