<?php
/**
 * Script thêm cột discount vào bảng invoices
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔧 Kiểm tra và thêm cột discount vào bảng invoices</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    
    // 1. Kiểm tra cột discount có tồn tại không
    echo "<h3>1. Kiểm tra cột discount...</h3>";
    
    $result = $db->query("SHOW COLUMNS FROM invoices LIKE 'discount'")->fetch();
    
    if ($result) {
        echo "✅ Cột 'discount' đã tồn tại<br>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } else {
        echo "❌ Cột 'discount' KHÔNG tồn tại<br>";
        echo "<strong>Đang thêm cột...</strong><br><br>";
        
        // Thêm cột discount
        $db->query("ALTER TABLE invoices ADD COLUMN discount DECIMAL(10,2) DEFAULT 0 AFTER total_amount");
        
        echo "✅ Đã thêm cột 'discount' thành công!<br>";
    }
    
    // 2. Kiểm tra cột final_amount
    echo "<h3>2. Kiểm tra cột final_amount...</h3>";
    
    $result = $db->query("SHOW COLUMNS FROM invoices LIKE 'final_amount'")->fetch();
    
    if ($result) {
        echo "✅ Cột 'final_amount' đã tồn tại<br>";
    } else {
        echo "❌ Cột 'final_amount' KHÔNG tồn tại<br>";
        echo "<strong>Đang thêm cột...</strong><br><br>";
        
        // Thêm cột final_amount
        $db->query("ALTER TABLE invoices ADD COLUMN final_amount DECIMAL(10,2) DEFAULT 0 AFTER discount");
        
        echo "✅ Đã thêm cột 'final_amount' thành công!<br>";
    }
    
    // 3. Hiển thị cấu trúc bảng invoices
    echo "<h3>3. Cấu trúc bảng invoices hiện tại:</h3>";
    
    $columns = $db->query("SHOW COLUMNS FROM invoices")->fetchAll();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH!</h2>";
    echo "<p><a href='index.php?page=sales' style='font-size: 16px; font-weight: bold;'>→ Thử bán hàng lại</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "</div>";
}
