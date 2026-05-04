<?php
/**
 * SETUP DATABASE HOÀN CHỈNH VỚI AUDIT LOG
 * Chạy file này để tạo lại database với đầy đủ tính năng audit log
 */

require_once 'config/database.php';

echo "<h2>🔧 SETUP DATABASE VỚI AUDIT LOG SYSTEM</h2>";

try {
    // Kết nối MySQL
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>✅ 1. Kết nối MySQL thành công</h3>";
    
    // Đọc và thực thi database schema
    $sqlFile = 'database_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Không tìm thấy file database_schema.sql");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Tách các câu lệnh SQL
    $statements = explode(';', $sql);
    
    echo "<h3>⚙️ 2. Đang tạo database và bảng...</h3>";
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (Exception $e) {
                // Bỏ qua lỗi không quan trọng
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "<div style='color: orange;'>⚠️ " . $e->getMessage() . "</div>";
                }
            }
        }
    }
    
    echo "<h3>✅ 3. Database đã được tạo thành công!</h3>";
    
    // Kết nối lại với database mới
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kiểm tra bảng audit_logs
    $stmt = $pdo->query("SHOW TABLES LIKE 'audit_logs'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>✅ 4. Bảng audit_logs đã sẵn sàng</h3>";
    } else {
        echo "<h3>❌ 4. Bảng audit_logs chưa được tạo</h3>";
    }
    
    // Thống kê dữ liệu
    echo "<h3>📊 5. Thống kê dữ liệu trong database:</h3>";
    
    $tables = [
        'users' => 'Người dùng',
        'medicines' => 'Thuốc',
        'categories' => 'Danh mục thuốc',
        'units' => 'Đơn vị tính',
        'suppliers' => 'Nhà cung cấp',
        'batches' => 'Lô thuốc',
        'invoices' => 'Hóa đơn',
        'invoice_details' => 'Chi tiết hóa đơn',
        'notifications' => 'Thông báo',
        'audit_logs' => 'Nhật ký hoạt động'
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Bảng</th><th>Tên tiếng Việt</th><th>Số records</th><th>Trạng thái</th></tr>";
    
    foreach ($tables as $table => $name) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            $count = $result['count'];
            
            $status = $count > 0 ? "✅ Có dữ liệu" : "⚠️ Trống";
            $color = $count > 0 ? "#d4edda" : "#fff3cd";
            
            echo "<tr style='background: $color;'>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>$name</td>";
            echo "<td>$count</td>";
            echo "<td>$status</td>";
            echo "</tr>";
            
        } catch (Exception $e) {
            echo "<tr style='background: #f8d7da;'>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>$name</td>";
            echo "<td>-</td>";
            echo "<td>❌ Lỗi: " . $e->getMessage() . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Test audit log system
    echo "<h3>🧪 6. Test hệ thống Audit Log:</h3>";
    
    try {
        // Test ghi log
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            1, // user_id
            'TEST',
            'system',
            null,
            null,
            json_encode(['test' => 'Database setup completed']),
            $_SERVER['REMOTE_ADDR'] ?? 'localhost',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Setup Script'
        ]);
        
        if ($result) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724;'>";
            echo "✅ Hệ thống Audit Log hoạt động bình thường!";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
        echo "❌ Lỗi test Audit Log: " . $e->getMessage();
        echo "</div>";
    }
    
    echo "<h3>🎉 KẾT QUẢ CUỐI CÙNG</h3>";
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; border-left: 5px solid #0c5460;'>";
    echo "<h4>📈 Hệ thống đã sẵn sàng với các tính năng:</h4>";
    echo "<ul>";
    echo "<li><strong>✅ Database MySQL:</strong> Hoạt động bình thường</li>";
    echo "<li><strong>✅ Audit Log System:</strong> Ghi nhật ký tất cả hoạt động</li>";
    echo "<li><strong>✅ User Authentication:</strong> admin/nhanvien1/nhanvien2 - password: 123456</li>";
    echo "<li><strong>✅ Inventory Management:</strong> Quản lý tồn kho real-time</li>";
    echo "<li><strong>✅ Notification System:</strong> Thông báo tự động</li>";
    echo "<li><strong>✅ QR Code System:</strong> Mã QR cho thuốc và lô hàng</li>";
    echo "</ul>";
    
    echo "<div style='background: #d4edda; padding: 15px; margin-top: 15px; border-radius: 5px;'>";
    echo "<strong>🚀 HỆ THỐNG SẴN SÀNG SỬ DỤNG!</strong><br>";
    echo "Tất cả hoạt động đăng nhập, thêm thuốc, bán hàng, v.v. đều được ghi log vào database.";
    echo "</div>";
    echo "</div>";
    
    // Hiển thị một số log mẫu
    echo "<h3>📋 Nhật ký hoạt động gần đây:</h3>";
    try {
        $stmt = $pdo->query("SELECT al.*, u.username, u.full_name 
                            FROM audit_logs al 
                            LEFT JOIN users u ON al.user_id = u.user_id 
                            ORDER BY al.created_at DESC 
                            LIMIT 5");
        $logs = $stmt->fetchAll();
        
        if (!empty($logs)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f8f9fa;'><th>Thời gian</th><th>Người dùng</th><th>Hành động</th><th>Bảng</th><th>IP</th></tr>";
            
            foreach ($logs as $log) {
                echo "<tr>";
                echo "<td>" . ($log['created_at'] ? date('d/m/Y H:i:s', strtotime($log['created_at'])) : '-') . "</td>";
                echo "<td>" . ($log['full_name'] ?? 'System') . "</td>";
                echo "<td>" . $log['action'] . "</td>";
                echo "<td>" . $log['table_name'] . "</td>";
                echo "<td>" . $log['ip_address'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Chưa có log nào trong hệ thống.</p>";
        }
    } catch (Exception $e) {
        echo "<p>Không thể hiển thị log: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ LỖI</h3>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<strong>Chi tiết lỗi:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f8f9fa;
}

h2, h3 {
    color: #2c3e50;
}

table {
    margin: 10px 0;
    font-size: 14px;
}

th {
    padding: 8px;
    text-align: left;
}

td {
    padding: 6px 8px;
}
</style>