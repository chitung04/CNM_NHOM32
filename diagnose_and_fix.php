<?php
/**
 * Script chẩn đoán và sửa toàn diện
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🔬 CHẨN ĐOÁN VÀ SỬA LỖI TOÀN DIỆN</h2>";
echo "<hr>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // BƯỚC 1: Kiểm tra database name
    echo "<h3>1️⃣ KIỂM TRA DATABASE</h3>";
    $stmt = $conn->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "<p><strong>Database hiện tại:</strong> {$result['db_name']}</p>";
    
    // BƯỚC 2: Kiểm tra các bảng
    echo "<h3>2️⃣ KIỂM TRA CÁC BẢNG</h3>";
    $tables = ['pharmacies', 'users', 'categories', 'units', 'suppliers', 'medicines', 'batches'];
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Bảng</th><th>Số dòng</th><th>Trạng thái</th></tr>";
    
    $tableStatus = [];
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM {$table}");
            $result = $stmt->fetch();
            $total = $result['total'];
            $tableStatus[$table] = $total;
            
            if ($total > 0) {
                echo "<tr><td>{$table}</td><td style='color: green; font-weight: bold;'>{$total}</td><td style='color: green;'>✅ OK</td></tr>";
            } else {
                echo "<tr><td>{$table}</td><td style='color: red; font-weight: bold;'>0</td><td style='color: red;'>❌ TRỐNG</td></tr>";
            }
        } catch (Exception $e) {
            echo "<tr><td>{$table}</td><td colspan='2' style='color: red;'>❌ Không tồn tại</td></tr>";
            $tableStatus[$table] = -1;
        }
    }
    echo "</table>";
    
    // BƯỚC 3: Chẩn đoán vấn đề
    echo "<h3>3️⃣ CHẨN ĐOÁN VẤN ĐỀ</h3>";
    
    $problems = [];
    
    if ($tableStatus['pharmacies'] == 0) {
        $problems[] = "❌ Không có pharmacy nào trong database";
    }
    
    if ($tableStatus['users'] == 0) {
        $problems[] = "❌ Không có user nào trong database";
    }
    
    if ($tableStatus['medicines'] == 0) {
        $problems[] = "❌ Không có thuốc nào trong database";
    }
    
    if ($tableStatus['batches'] == 0) {
        $problems[] = "❌ Không có lô thuốc nào trong database";
    }
    
    if ($tableStatus['categories'] == 0) {
        $problems[] = "⚠️ Không có danh mục thuốc";
    }
    
    if (empty($problems)) {
        echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ KHÔNG CÓ VẤN ĐỀ VỀ DỮ LIỆU!</p>";
        echo "<p>Database có đủ dữ liệu. Vấn đề có thể là:</p>";
        echo "<ul>";
        echo "<li>Đang đăng nhập với pharmacy_id khác</li>";
        echo "<li>Lỗi trong code hiển thị</li>";
        echo "<li>Session không đúng</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-weight: bold; font-size: 18px;'>❌ PHÁT HIỆN VẤN ĐỀ:</p>";
        echo "<ul>";
        foreach ($problems as $problem) {
            echo "<li>{$problem}</li>";
        }
        echo "</ul>";
    }
    
    // BƯỚC 4: Kiểm tra chi tiết nếu có dữ liệu
    if ($tableStatus['medicines'] > 0) {
        echo "<h3>4️⃣ CHI TIẾT THUỐC</h3>";
        
        // Phân bố theo pharmacy
        $stmt = $conn->query("SELECT pharmacy_id, COUNT(*) as total FROM medicines GROUP BY pharmacy_id");
        $byPharmacy = $stmt->fetchAll();
        
        echo "<p><strong>Phân bố thuốc theo pharmacy:</strong></p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr style='background: #f0f0f0;'><th>Pharmacy ID</th><th>Số thuốc</th></tr>";
        foreach ($byPharmacy as $p) {
            echo "<tr><td>{$p['pharmacy_id']}</td><td>{$p['total']}</td></tr>";
        }
        echo "</table>";
        
        // 5 thuốc đầu tiên
        $stmt = $conn->query("SELECT m.medicine_id, m.pharmacy_id, m.medicine_name, m.price, 
                                     COALESCE(SUM(b.quantity), 0) as total_stock
                              FROM medicines m
                              LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
                              GROUP BY m.medicine_id
                              LIMIT 5");
        $medicines = $stmt->fetchAll();
        
        echo "<p><strong>5 thuốc đầu tiên:</strong></p>";
        echo "<table border='1' cellpadding='5' style='width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Pharmacy ID</th><th>Tên thuốc</th><th>Giá</th><th>Tồn kho</th></tr>";
        foreach ($medicines as $m) {
            $stockColor = $m['total_stock'] > 0 ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$m['medicine_id']}</td>";
            echo "<td>{$m['pharmacy_id']}</td>";
            echo "<td>{$m['medicine_name']}</td>";
            echo "<td>" . number_format($m['price']) . "đ</td>";
            echo "<td style='color: {$stockColor}; font-weight: bold;'>{$m['total_stock']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    if ($tableStatus['batches'] > 0) {
        echo "<h3>5️⃣ CHI TIẾT LÔ THUỐC</h3>";
        
        // Phân bố theo status
        $stmt = $conn->query("SELECT status, COUNT(*) as total, SUM(quantity) as total_qty FROM batches GROUP BY status");
        $byStatus = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr style='background: #f0f0f0;'><th>Status</th><th>Số lô</th><th>Tổng số lượng</th></tr>";
        foreach ($byStatus as $s) {
            echo "<tr><td>{$s['status']}</td><td>{$s['total']}</td><td>{$s['total_qty']}</td></tr>";
        }
        echo "</table>";
    }
    
    // BƯỚC 5: Giải pháp
    echo "<hr>";
    echo "<h3>🎯 GIẢI PHÁP</h3>";
    
    if (!empty($problems)) {
        echo "<p style='color: red; font-weight: bold;'>⚠️ DATABASE TRỐNG HOẶC THIẾU DỮ LIỆU!</p>";
        echo "<p><strong>Hãy làm theo các bước sau:</strong></p>";
        echo "<ol>";
        echo "<li><strong>Import database:</strong> Mở phpMyAdmin → Chọn database <code>qlnt_db</code> → Tab Import → Chọn file <code>qlnt_db.sql</code> → Click Go</li>";
        echo "<li><strong>Hoặc chạy:</strong> <a href='import_database_auto.php' target='_blank'><strong>Script import tự động</strong></a></li>";
        echo "<li><strong>Sau đó:</strong> Đăng nhập lại với <code>admin</code> / <code>123456</code></li>";
        echo "</ol>";
    } else {
        echo "<p style='color: green; font-weight: bold;'>✅ DATABASE CÓ ĐỦ DỮ LIỆU!</p>";
        echo "<p><strong>Vấn đề có thể là:</strong></p>";
        echo "<ol>";
        echo "<li><strong>Kiểm tra session:</strong> <a href='check_session.php' target='_blank'>Xem session hiện tại</a></li>";
        echo "<li><strong>Kiểm tra code:</strong> Có thể lỗi trong Medicine::getAll() hoặc SalesController</li>";
        echo "<li><strong>Thử đăng xuất và đăng nhập lại</strong></li>";
        echo "</ol>";
    }
    
    echo "<hr>";
    echo "<p><a href='index.php'><strong>← Về trang chủ</strong></a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ LỖI:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
