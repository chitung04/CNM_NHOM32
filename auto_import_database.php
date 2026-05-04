<?php
/**
 * Script tự động import database từ FINAL_DATABASE_COMPLETE.sql
 */

echo "<h2>🔧 Tự động Import Database</h2>";
echo "<hr>";

// Kiểm tra file SQL có tồn tại không
$sqlFile = __DIR__ . '/FINAL_DATABASE_COMPLETE.sql';

if (!file_exists($sqlFile)) {
    die("<p style='color: red;'>❌ Không tìm thấy file: FINAL_DATABASE_COMPLETE.sql</p>");
}

echo "<p>✅ Tìm thấy file: <strong>FINAL_DATABASE_COMPLETE.sql</strong></p>";
echo "<p>Kích thước: <strong>" . number_format(filesize($sqlFile) / 1024, 2) . " KB</strong></p>";

try {
    require_once 'config/database.php';
    
    // Kết nối MySQL không chọn database
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p>✅ Kết nối MySQL thành công</p>";
    
    // Đọc file SQL
    echo "<h3>Đang đọc file SQL...</h3>";
    $sql = file_get_contents($sqlFile);
    
    if (empty($sql)) {
        throw new Exception("File SQL rỗng!");
    }
    
    echo "<p>✅ Đọc file thành công (" . strlen($sql) . " ký tự)</p>";
    
    // Thực thi SQL
    echo "<h3>Đang import database...</h3>";
    echo "<p><em>Quá trình này có thể mất 10-30 giây...</em></p>";
    
    // Tách các câu lệnh SQL
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            // Bỏ qua lỗi DROP DATABASE nếu không tồn tại
            if (strpos($e->getMessage(), 'database doesn\'t exist') === false) {
                $errorCount++;
                echo "<p style='color: orange;'>⚠️ Lỗi: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH!</h2>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px;'>";
    echo "<h3>📊 Thống kê:</h3>";
    echo "<ul>";
    echo "<li>Câu lệnh thành công: <strong>{$successCount}</strong></li>";
    echo "<li>Câu lệnh lỗi: <strong>{$errorCount}</strong></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<br>";
    echo "<div style='background: #cfe2ff; padding: 20px; border-radius: 5px;'>";
    echo "<h3>🎉 Bước tiếp theo:</h3>";
    echo "<ol>";
    echo "<li><strong>Đăng nhập:</strong> Username: <code>admin</code> / Password: <code>123456</code></li>";
    echo "<li><a href='index.php' style='font-size: 18px; font-weight: bold;'>→ Đi tới trang chủ</a></li>";
    echo "<li><a href='index.php?page=sales' style='font-size: 18px; font-weight: bold;'>→ Thử bán hàng</a></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "</div>";
    
    echo "<br>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "<h3>💡 Hãy import thủ công:</h3>";
    echo "<ol>";
    echo "<li>Mở <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
    echo "<li>Click tab <strong>Import</strong></li>";
    echo "<li>Chọn file <strong>FINAL_DATABASE_COMPLETE.sql</strong></li>";
    echo "<li>Click <strong>Go</strong></li>";
    echo "</ol>";
    echo "</div>";
}
