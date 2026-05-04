<?php
/**
 * KIỂM TRA VÀ SỬA TOÀN BỘ HỆ THỐNG
 */

echo "<h1>🔧 KIỂM TRA VÀ SỬA HỆ THỐNG</h1>";
echo "<hr>";

$errors = [];
$warnings = [];
$success = [];

// BƯỚC 1: Kiểm tra XAMPP MySQL
echo "<h2>1️⃣ KIỂM TRA XAMPP MYSQL</h2>";
try {
    $conn = new mysqli('localhost', 'root', '');
    if ($conn->connect_error) {
        $errors[] = "❌ Không thể kết nối MySQL: " . $conn->connect_error;
        echo "<p style='color: red;'>❌ MySQL không chạy! Hãy mở XAMPP Control Panel và Start MySQL</p>";
    } else {
        $success[] = "✅ MySQL đang chạy";
        echo "<p style='color: green;'>✅ MySQL đang chạy</p>";
        
        // BƯỚC 2: Kiểm tra database qlnt_db
        echo "<h2>2️⃣ KIỂM TRA DATABASE qlnt_db</h2>";
        $result = $conn->query("SHOW DATABASES LIKE 'qlnt_db'");
        
        if ($result->num_rows == 0) {
            $warnings[] = "⚠️ Database qlnt_db không tồn tại";
            echo "<p style='color: orange;'>⚠️ Database qlnt_db không tồn tại</p>";
            
            // Tạo database
            echo "<p>🔧 Đang tạo database qlnt_db...</p>";
            if ($conn->query("CREATE DATABASE qlnt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                $success[] = "✅ Đã tạo database qlnt_db";
                echo "<p style='color: green;'>✅ Đã tạo database qlnt_db</p>";
            } else {
                $errors[] = "❌ Không thể tạo database: " . $conn->error;
                echo "<p style='color: red;'>❌ Không thể tạo database: " . $conn->error . "</p>";
            }
        } else {
            $success[] = "✅ Database qlnt_db tồn tại";
            echo "<p style='color: green;'>✅ Database qlnt_db tồn tại</p>";
        }
        
        // BƯỚC 3: Kiểm tra các bảng
        echo "<h2>3️⃣ KIỂM TRA CÁC BẢNG</h2>";
        $conn->select_db('qlnt_db');
        
        $requiredTables = ['pharmacies', 'users', 'categories', 'units', 'suppliers', 'medicines', 'batches'];
        $existingTables = [];
        
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $existingTables[] = $row[0];
        }
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Bảng</th><th>Trạng thái</th><th>Số dòng</th></tr>";
        
        $missingTables = [];
        foreach ($requiredTables as $table) {
            if (in_array($table, $existingTables)) {
                $countResult = $conn->query("SELECT COUNT(*) as total FROM {$table}");
                $count = $countResult->fetch_assoc()['total'];
                
                if ($count > 0) {
                    echo "<tr><td>{$table}</td><td style='color: green;'>✅ Tồn tại</td><td style='color: green;'>{$count}</td></tr>";
                    $success[] = "✅ Bảng {$table}: {$count} dòng";
                } else {
                    echo "<tr><td>{$table}</td><td style='color: orange;'>⚠️ Trống</td><td style='color: orange;'>0</td></tr>";
                    $warnings[] = "⚠️ Bảng {$table} trống";
                }
            } else {
                echo "<tr><td>{$table}</td><td style='color: red;' colspan='2'>❌ Không tồn tại</td></tr>";
                $errors[] = "❌ Bảng {$table} không tồn tại";
                $missingTables[] = $table;
            }
        }
        echo "</table>";
        
        // BƯỚC 4: Đánh giá và đưa ra giải pháp
        echo "<hr>";
        echo "<h2>4️⃣ ĐÁNH GIÁ VÀ GIẢI PHÁP</h2>";
        
        if (!empty($errors) || !empty($missingTables)) {
            echo "<div style='background: #ffebee; padding: 15px; border-left: 4px solid red;'>";
            echo "<h3 style='color: red;'>❌ HỆ THỐNG CÓ LỖI NGHIÊM TRỌNG</h3>";
            echo "<p><strong>Vấn đề:</strong></p>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>{$error}</li>";
            }
            echo "</ul>";
            
            echo "<p><strong>Giải pháp:</strong></p>";
            echo "<ol>";
            echo "<li><strong>Import database từ file qlnt_db.sql:</strong>";
            echo "<ul>";
            echo "<li>Mở phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
            echo "<li>Chọn database <code>qlnt_db</code></li>";
            echo "<li>Click tab <strong>Import</strong></li>";
            echo "<li>Click <strong>Choose File</strong> → Chọn file <code>qlnt_db.sql</code></li>";
            echo "<li>Click <strong>Go</strong></li>";
            echo "</ul>";
            echo "</li>";
            echo "<li><strong>Hoặc chạy script tự động:</strong> <a href='import_database_auto.php' target='_blank'><strong>import_database_auto.php</strong></a></li>";
            echo "</ol>";
            echo "</div>";
            
        } elseif (!empty($warnings)) {
            echo "<div style='background: #fff3e0; padding: 15px; border-left: 4px solid orange;'>";
            echo "<h3 style='color: orange;'>⚠️ HỆ THỐNG THIẾU DỮ LIỆU</h3>";
            echo "<p><strong>Vấn đề:</strong></p>";
            echo "<ul>";
            foreach ($warnings as $warning) {
                echo "<li>{$warning}</li>";
            }
            echo "</ul>";
            
            echo "<p><strong>Giải pháp:</strong></p>";
            echo "<ol>";
            echo "<li><strong>Import dữ liệu mẫu:</strong> Chạy file <code>qlnt_db.sql</code> trong phpMyAdmin</li>";
            echo "<li><strong>Hoặc:</strong> <a href='import_database_auto.php' target='_blank'><strong>Import tự động</strong></a></li>";
            echo "</ol>";
            echo "</div>";
            
        } else {
            echo "<div style='background: #e8f5e9; padding: 15px; border-left: 4px solid green;'>";
            echo "<h3 style='color: green;'>✅ HỆ THỐNG HOẠT ĐỘNG BÌNH THƯỜNG</h3>";
            echo "<p><strong>Tất cả kiểm tra đều OK!</strong></p>";
            echo "<ul>";
            foreach ($success as $s) {
                echo "<li>{$s}</li>";
            }
            echo "</ul>";
            
            echo "<p><strong>Bước tiếp theo:</strong></p>";
            echo "<ol>";
            echo "<li><a href='logout_simple.php'><strong>Đăng xuất</strong></a></li>";
            echo "<li><a href='index.php?page=login'><strong>Đăng nhập</strong></a> với <code>admin</code> / <code>123456</code></li>";
            echo "<li><a href='check_session.php'><strong>Kiểm tra session</strong></a> (phải có pharmacy_id)</li>";
            echo "<li><a href='index.php?page=sales'><strong>Vào trang bán hàng</strong></a></li>";
            echo "</ol>";
            echo "</div>";
        }
        
        $conn->close();
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ LỖI:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📊 TÓM TẮT</h2>";
echo "<p><strong>Thành công:</strong> " . count($success) . "</p>";
echo "<p><strong>Cảnh báo:</strong> " . count($warnings) . "</p>";
echo "<p><strong>Lỗi:</strong> " . count($errors) . "</p>";
