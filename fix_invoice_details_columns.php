<?php
/**
 * Script kiểm tra và thêm các cột thiếu vào invoice_details
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔧 Sửa bảng invoice_details</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    
    // 1. Kiểm tra bảng invoice_details có tồn tại không
    echo "<h3>1. Kiểm tra bảng invoice_details...</h3>";
    
    $result = $db->query("SHOW TABLES LIKE 'invoice_details'")->fetch();
    
    if (!$result) {
        echo "❌ Bảng 'invoice_details' KHÔNG tồn tại!<br>";
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
    } else {
        echo "✅ Bảng 'invoice_details' đã tồn tại<br>";
    }
    
    // 2. Kiểm tra các cột
    echo "<h3>2. Kiểm tra các cột trong bảng...</h3>";
    
    $columns = $db->query("SHOW COLUMNS FROM invoice_details")->fetchAll();
    $columnNames = array_column($columns, 'Field');
    
    echo "<p>Các cột hiện có: <strong>" . implode(', ', $columnNames) . "</strong></p>";
    
    // Danh sách cột cần thiết
    $requiredColumns = [
        'detail_id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'invoice_id' => 'INT NOT NULL',
        'medicine_id' => 'INT NOT NULL',
        'batch_id' => 'INT',
        'quantity' => 'INT NOT NULL',
        'unit_price' => 'DECIMAL(10,2) NOT NULL',
        'subtotal' => 'DECIMAL(10,2) NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    
    $missingColumns = [];
    foreach ($requiredColumns as $colName => $colDef) {
        if (!in_array($colName, $columnNames)) {
            $missingColumns[$colName] = $colDef;
        }
    }
    
    if (count($missingColumns) > 0) {
        echo "<h3>3. Thêm các cột thiếu:</h3>";
        
        foreach ($missingColumns as $colName => $colDef) {
            echo "<p>Đang thêm cột '<strong>{$colName}</strong>'...</p>";
            
            try {
                // Xác định vị trí thêm cột
                $afterColumn = '';
                if ($colName === 'subtotal') {
                    $afterColumn = 'AFTER unit_price';
                } elseif ($colName === 'created_at') {
                    $afterColumn = 'AFTER subtotal';
                }
                
                $sql = "ALTER TABLE invoice_details ADD COLUMN {$colName} {$colDef} {$afterColumn}";
                $db->query($sql);
                
                echo "✅ Đã thêm cột '{$colName}'<br>";
            } catch (Exception $e) {
                echo "❌ Lỗi khi thêm cột '{$colName}': " . $e->getMessage() . "<br>";
            }
        }
    } else {
        echo "<p style='color: green;'>✅ Tất cả cột cần thiết đều có!</p>";
    }
    
    // 4. Hiển thị cấu trúc bảng cuối cùng
    echo "<h3>4. Cấu trúc bảng invoice_details:</h3>";
    
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
    
    // 5. Kiểm tra bảng invoices
    echo "<h3>5. Kiểm tra bảng invoices:</h3>";
    
    $invoiceColumns = $db->query("SHOW COLUMNS FROM invoices")->fetchAll();
    $invoiceColumnNames = array_column($invoiceColumns, 'Field');
    
    echo "<p>Các cột: <strong>" . implode(', ', $invoiceColumnNames) . "</strong></p>";
    
    // Kiểm tra cột pharmacy_id
    if (!in_array('pharmacy_id', $invoiceColumnNames)) {
        echo "<p style='color: red;'>❌ Thiếu cột 'pharmacy_id' trong bảng invoices</p>";
        echo "<p>Đang thêm...</p>";
        
        $db->query("ALTER TABLE invoices ADD COLUMN pharmacy_id INT NOT NULL AFTER user_id");
        $db->query("ALTER TABLE invoices ADD INDEX idx_invoices_pharmacy (pharmacy_id)");
        
        echo "<p style='color: green;'>✅ Đã thêm cột 'pharmacy_id'</p>";
    } else {
        echo "<p style='color: green;'>✅ Cột 'pharmacy_id' đã có</p>";
    }
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH!</h2>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px;'>";
    echo "<h3>🎉 Bây giờ bạn có thể bán hàng!</h3>";
    echo "<p><a href='index.php?page=sales' style='font-size: 18px; font-weight: bold;'>→ Đi tới trang bán hàng</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    
    echo "<br>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "<h3>💡 Giải pháp cuối cùng:</h3>";
    echo "<p><strong>Import lại database hoàn chỉnh:</strong></p>";
    echo "<ol>";
    echo "<li>Mở <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
    echo "<li>Chọn database <strong>qlnt_db</strong></li>";
    echo "<li>Click tab <strong>Operations</strong> → <strong>Drop the database</strong></li>";
    echo "<li>Click tab <strong>Import</strong></li>";
    echo "<li>Chọn file <strong>FINAL_DATABASE_COMPLETE.sql</strong></li>";
    echo "<li>Click <strong>Go</strong></li>";
    echo "</ol>";
    echo "<p>Sau đó đăng nhập lại: <code>admin</code> / <code>123456</code></p>";
    echo "</div>";
}
