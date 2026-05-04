<?php
/**
 * Thêm cột pharmacy_id vào bảng invoices
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<h2>🔧 Sửa bảng invoices</h2>";
echo "<hr>";

try {
    // 1. Kiểm tra cột pharmacy_id đã tồn tại chưa
    echo "<h3>1. Kiểm tra cột pharmacy_id...</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM invoices LIKE 'pharmacy_id'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "<p style='color: green;'>✅ Cột pharmacy_id đã tồn tại</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Cột pharmacy_id chưa tồn tại, đang thêm...</p>";
        
        // Thêm cột
        $db->query("ALTER TABLE invoices ADD COLUMN pharmacy_id INT NULL AFTER invoice_id");
        echo "<p style='color: green;'>✅ Đã thêm cột pharmacy_id</p>";
    }
    
    // 2. Cập nhật pharmacy_id cho các invoice cũ
    echo "<h3>2. Cập nhật pharmacy_id cho invoices cũ...</h3>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM invoices WHERE pharmacy_id IS NULL");
    $nullCount = $stmt->fetch()['count'];
    
    if ($nullCount > 0) {
        echo "<p>Tìm thấy $nullCount invoices chưa có pharmacy_id</p>";
        $db->query("UPDATE invoices SET pharmacy_id = 1 WHERE pharmacy_id IS NULL");
        echo "<p style='color: green;'>✅ Đã cập nhật pharmacy_id = 1 cho $nullCount invoices</p>";
    } else {
        echo "<p style='color: green;'>✅ Tất cả invoices đã có pharmacy_id</p>";
    }
    
    // 3. Set NOT NULL
    echo "<h3>3. Set cột pharmacy_id NOT NULL...</h3>";
    try {
        $db->query("ALTER TABLE invoices MODIFY COLUMN pharmacy_id INT NOT NULL");
        echo "<p style='color: green;'>✅ Đã set NOT NULL</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Có thể đã set NOT NULL rồi: " . $e->getMessage() . "</p>";
    }
    
    // 4. Thêm foreign key
    echo "<h3>4. Thêm foreign key...</h3>";
    try {
        $db->query("ALTER TABLE invoices ADD CONSTRAINT fk_invoices_pharmacy FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Đã thêm foreign key</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Foreign key có thể đã tồn tại: " . $e->getMessage() . "</p>";
    }
    
    // 5. Thêm index
    echo "<h3>5. Thêm index...</h3>";
    try {
        $db->query("CREATE INDEX idx_invoices_pharmacy ON invoices(pharmacy_id)");
        echo "<p style='color: green;'>✅ Đã thêm index</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Index có thể đã tồn tại: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px;'>";
    echo "<h3 style='color: #155724;'>✅ HOÀN TẤT!</h3>";
    echo "<p>Bảng invoices đã được sửa thành công.</p>";
    echo "<p><a href='index.php?page=invoices' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>📋 Xem danh sách hóa đơn</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 20px;'>";
    echo "<h3 style='color: #721c24;'>❌ LỖI:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
</style>
