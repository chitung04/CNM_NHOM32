<?php
/**
 * Script sửa tên bảng invoice_items thành invoice_details
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔧 Sửa tên bảng invoice_items → invoice_details</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    
    // 1. Kiểm tra bảng invoice_items
    echo "<h3>1. Kiểm tra bảng invoice_items...</h3>";
    
    $result = $db->query("SHOW TABLES LIKE 'invoice_items'")->fetch();
    
    if ($result) {
        echo "✅ Bảng 'invoice_items' tồn tại<br>";
        
        // Kiểm tra bảng invoice_details
        $result2 = $db->query("SHOW TABLES LIKE 'invoice_details'")->fetch();
        
        if ($result2) {
            echo "⚠️ Bảng 'invoice_details' cũng tồn tại<br>";
            echo "<p>Có 2 lựa chọn:</p>";
            echo "<ol>";
            echo "<li>Xóa bảng invoice_details và đổi tên invoice_items</li>";
            echo "<li>Giữ nguyên và sửa code để dùng invoice_items</li>";
            echo "</ol>";
            
            // Đếm số dòng trong mỗi bảng
            $count1 = $db->query("SELECT COUNT(*) as count FROM invoice_items")->fetch();
            $count2 = $db->query("SELECT COUNT(*) as count FROM invoice_details")->fetch();
            
            echo "<p>invoice_items: <strong>{$count1['count']}</strong> dòng</p>";
            echo "<p>invoice_details: <strong>{$count2['count']}</strong> dòng</p>";
            
        } else {
            echo "✅ Bảng 'invoice_details' chưa tồn tại<br>";
            echo "<strong>Đang đổi tên bảng...</strong><br><br>";
            
            // Đổi tên bảng
            $db->query("RENAME TABLE invoice_items TO invoice_details");
            
            echo "✅ Đã đổi tên thành công: invoice_items → invoice_details<br>";
        }
        
    } else {
        echo "❌ Bảng 'invoice_items' KHÔNG tồn tại<br>";
        
        // Kiểm tra invoice_details
        $result2 = $db->query("SHOW TABLES LIKE 'invoice_details'")->fetch();
        
        if ($result2) {
            echo "✅ Bảng 'invoice_details' đã tồn tại - Không cần làm gì<br>";
        } else {
            echo "❌ Cả 2 bảng đều không tồn tại - Cần tạo mới!<br>";
            
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
    }
    
    // 2. Hiển thị cấu trúc bảng invoice_details
    echo "<h3>2. Cấu trúc bảng invoice_details:</h3>";
    
    $columns = $db->query("SHOW COLUMNS FROM invoice_details")->fetchAll();
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Đếm số dòng
    $count = $db->query("SELECT COUNT(*) as count FROM invoice_details")->fetch();
    echo "<p>Tổng số dòng: <strong>{$count['count']}</strong></p>";
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH!</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<h3>🎉 Bây giờ bạn có thể bán hàng!</h3>";
    echo "<p><a href='index.php?page=sales' style='font-size: 18px; font-weight: bold;'>→ Đi tới trang bán hàng</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "</div>";
}
