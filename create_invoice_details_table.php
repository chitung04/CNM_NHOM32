<?php
/**
 * Script tạo bảng invoice_details
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔧 Tạo bảng invoice_details</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    
    // 1. Kiểm tra bảng có tồn tại không
    echo "<h3>1. Kiểm tra bảng invoice_details...</h3>";
    
    $result = $db->query("SHOW TABLES LIKE 'invoice_details'")->fetch();
    
    if ($result) {
        echo "✅ Bảng 'invoice_details' đã tồn tại<br>";
    } else {
        echo "❌ Bảng 'invoice_details' KHÔNG tồn tại<br>";
        echo "<strong>Đang tạo bảng...</strong><br><br>";
        
        // Tạo bảng invoice_details
        $sql = "CREATE TABLE invoice_details (
            detail_id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_id INT NOT NULL,
            medicine_id INT NOT NULL,
            batch_id INT,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE,
            FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id),
            FOREIGN KEY (batch_id) REFERENCES batches(batch_id),
            INDEX idx_invoice_details_invoice (invoice_id),
            INDEX idx_invoice_details_medicine (medicine_id),
            INDEX idx_invoice_details_batch (batch_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->query($sql);
        
        echo "✅ Đã tạo bảng 'invoice_details' thành công!<br>";
    }
    
    // 2. Hiển thị cấu trúc bảng
    echo "<h3>2. Cấu trúc bảng invoice_details:</h3>";
    
    $columns = $db->query("SHOW COLUMNS FROM invoice_details")->fetchAll();
    
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
    
    // 3. Kiểm tra bảng invoices
    echo "<h3>3. Kiểm tra bảng invoices...</h3>";
    
    $result = $db->query("SHOW TABLES LIKE 'invoices'")->fetch();
    
    if ($result) {
        echo "✅ Bảng 'invoices' đã tồn tại<br>";
        
        // Kiểm tra các cột
        $columns = $db->query("SHOW COLUMNS FROM invoices")->fetchAll();
        $columnNames = array_column($columns, 'Field');
        
        echo "<p>Các cột: " . implode(', ', $columnNames) . "</p>";
        
        // Kiểm tra cột pharmacy_id
        if (in_array('pharmacy_id', $columnNames)) {
            echo "✅ Cột 'pharmacy_id' đã có<br>";
        } else {
            echo "❌ Cột 'pharmacy_id' CHƯA có - Cần thêm!<br>";
            
            $db->query("ALTER TABLE invoices ADD COLUMN pharmacy_id INT NOT NULL AFTER user_id");
            $db->query("ALTER TABLE invoices ADD FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE");
            
            echo "✅ Đã thêm cột 'pharmacy_id'<br>";
        }
        
    } else {
        echo "❌ Bảng 'invoices' KHÔNG tồn tại - Cần import database đầy đủ!<br>";
    }
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH!</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Bây giờ bạn có thể:</h3>";
    echo "<ol>";
    echo "<li><a href='index.php?page=sales' style='font-size: 16px; font-weight: bold;'>→ Thử bán hàng</a></li>";
    echo "<li><a href='index.php?page=invoices' style='font-size: 16px; font-weight: bold;'>→ Xem lịch sử đơn hàng</a></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    
    echo "<br>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "<h3>💡 Giải pháp:</h3>";
    echo "<p>Nếu lỗi vẫn tiếp tục, hãy import lại database hoàn chỉnh:</p>";
    echo "<ol>";
    echo "<li>Mở phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Chọn database <strong>qlnt_db</strong></li>";
    echo "<li>Click tab <strong>Import</strong></li>";
    echo "<li>Chọn file <strong>FINAL_DATABASE_COMPLETE.sql</strong></li>";
    echo "<li>Click <strong>Go</strong></li>";
    echo "</ol>";
    echo "</div>";
}
