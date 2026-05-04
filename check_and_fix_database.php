<?php
/**
 * Script kiểm tra và sửa database thiếu bảng
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔧 Kiểm tra và sửa Database</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    
    // Danh sách bảng cần thiết
    $requiredTables = [
        'pharmacies',
        'users',
        'categories',
        'units',
        'suppliers',
        'medicines',
        'batches',
        'invoices',
        'invoice_details',
        'notifications',
        'audit_logs'
    ];
    
    echo "<h3>1. Kiểm tra các bảng trong database...</h3>";
    
    $existingTables = [];
    $result = $db->query("SHOW TABLES")->fetchAll();
    foreach ($result as $row) {
        $existingTables[] = $row[0];
    }
    
    echo "<p>Tìm thấy <strong>" . count($existingTables) . "</strong> bảng:</p>";
    echo "<ul>";
    foreach ($existingTables as $table) {
        echo "<li>✅ {$table}</li>";
    }
    echo "</ul>";
    
    // Kiểm tra bảng thiếu
    $missingTables = array_diff($requiredTables, $existingTables);
    
    if (count($missingTables) > 0) {
        echo "<h3>❌ Thiếu " . count($missingTables) . " bảng:</h3>";
        echo "<ul>";
        foreach ($missingTables as $table) {
            echo "<li style='color: red;'><strong>{$table}</strong></li>";
        }
        echo "</ul>";
        
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; border: 2px solid #ffc107; margin: 20px 0;'>";
        echo "<h3>⚠️ CẢNH BÁO: Database không đầy đủ!</h3>";
        echo "<p><strong>Giải pháp:</strong> Bạn cần import lại database hoàn chỉnh.</p>";
        echo "<ol>";
        echo "<li>Mở <strong>phpMyAdmin</strong>: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Chọn database <strong>qlnt_db</strong></li>";
        echo "<li>Click tab <strong>Import</strong></li>";
        echo "<li>Chọn file <strong>FINAL_DATABASE_COMPLETE.sql</strong></li>";
        echo "<li>Click <strong>Go</strong></li>";
        echo "</ol>";
        echo "<p><strong>Lưu ý:</strong> File này sẽ XÓA và TẠO LẠI toàn bộ database với dữ liệu mẫu.</p>";
        echo "</div>";
        
    } else {
        echo "<h3>✅ Tất cả bảng đều tồn tại!</h3>";
        
        // Kiểm tra cột trong bảng invoices
        echo "<h3>2. Kiểm tra cấu trúc bảng invoices...</h3>";
        
        $columns = $db->query("SHOW COLUMNS FROM invoices")->fetchAll();
        $columnNames = array_column($columns, 'Field');
        
        echo "<p>Các cột trong bảng invoices:</p>";
        echo "<ul>";
        foreach ($columnNames as $col) {
            echo "<li>{$col}</li>";
        }
        echo "</ul>";
        
        // Kiểm tra các cột cần thiết
        $requiredColumns = ['invoice_id', 'invoice_number', 'user_id', 'total_amount', 'pharmacy_id', 'payment_method', 'qr_code'];
        $missingColumns = array_diff($requiredColumns, $columnNames);
        
        if (count($missingColumns) > 0) {
            echo "<p style='color: red;'><strong>❌ Thiếu cột:</strong> " . implode(', ', $missingColumns) . "</p>";
        } else {
            echo "<p style='color: green;'><strong>✅ Tất cả cột cần thiết đều có!</strong></p>";
        }
        
        // Kiểm tra cột trong bảng invoice_details
        echo "<h3>3. Kiểm tra cấu trúc bảng invoice_details...</h3>";
        
        $columns = $db->query("SHOW COLUMNS FROM invoice_details")->fetchAll();
        $columnNames = array_column($columns, 'Field');
        
        echo "<p>Các cột trong bảng invoice_details:</p>";
        echo "<ul>";
        foreach ($columnNames as $col) {
            echo "<li>{$col}</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH KIỂM TRA!</h2>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "</div>";
}
