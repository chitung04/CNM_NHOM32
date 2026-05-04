<?php
/**
 * Kiểm tra cột email trong bảng users
 */

try {
    $pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html>\n";
    echo "<html><head><title>Kiểm tra cột email</title>";
    echo "<meta charset='UTF-8'>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "</head><body class='p-5'>\n";
    
    echo "<div class='container'>\n";
    echo "<h2>Kiểm tra cột email trong bảng users</h2>\n";
    
    // Kiểm tra cột email
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    
    if ($stmt->rowCount() > 0) {
        echo "<div class='alert alert-success'>";
        echo "<i class='bi bi-check-circle'></i> <strong>Cột email ĐÃ TỒN TẠI</strong>";
        echo "</div>";
        
        $column = $stmt->fetch();
        echo "<pre>";
        print_r($column);
        echo "</pre>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<i class='bi bi-x-circle'></i> <strong>Cột email CHƯA TỒN TẠI</strong>";
        echo "</div>";
        
        echo "<h4>Cần chạy lệnh SQL sau:</h4>";
        echo "<div class='card'>";
        echo "<div class='card-body'>";
        echo "<code>ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER phone;</code>";
        echo "</div>";
        echo "</div>";
        
        echo "<h4 class='mt-4'>Hoặc click nút này để tự động thêm:</h4>";
        
        if (isset($_GET['add'])) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER phone");
                echo "<div class='alert alert-success mt-3'>";
                echo "✅ Đã thêm cột email thành công! <a href='check_email_column.php'>Kiểm tra lại</a>";
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-danger mt-3'>";
                echo "❌ Lỗi: " . $e->getMessage();
                echo "</div>";
            }
        } else {
            echo "<a href='check_email_column.php?add=1' class='btn btn-primary'>Thêm cột email</a>";
        }
    }
    
    // Hiển thị tất cả cột trong bảng users
    echo "<h4 class='mt-4'>Tất cả các cột trong bảng users:</h4>";
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll();
    
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr></thead>";
    echo "<tbody>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    
    // Kiểm tra bảng pharmacies
    echo "<h4 class='mt-4'>Kiểm tra bảng pharmacies:</h4>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'pharmacies'");
    
    if ($stmt->rowCount() > 0) {
        echo "<div class='alert alert-success'>";
        echo "✅ Bảng pharmacies ĐÃ TỒN TẠI";
        echo "</div>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM pharmacies");
        $count = $stmt->fetch()['count'];
        echo "<p>Số nhà thuốc: <strong>$count</strong></p>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "❌ Bảng pharmacies CHƯA TỒN TẠI";
        echo "</div>";
        
        if (isset($_GET['create_table'])) {
            try {
                $sql = "CREATE TABLE IF NOT EXISTS pharmacies (
                    pharmacy_id INT PRIMARY KEY AUTO_INCREMENT,
                    pharmacy_name VARCHAR(255) NOT NULL,
                    pharmacy_code VARCHAR(50) UNIQUE NOT NULL,
                    address TEXT,
                    phone VARCHAR(20),
                    email VARCHAR(100),
                    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
                    subscription_plan ENUM('free', 'basic', 'premium') DEFAULT 'free',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                $pdo->exec($sql);
                
                // Tạo nhà thuốc mặc định
                $pdo->exec("INSERT INTO pharmacies (pharmacy_name, pharmacy_code, address, phone, email, status, subscription_plan)
                           VALUES ('Nhà thuốc DUO PHARMA', 'DUO001', '123 Đường ABC, Quận 1, TP.HCM', '0123456789', 'duopharma@example.com', 'active', 'premium')");
                
                echo "<div class='alert alert-success mt-3'>";
                echo "✅ Đã tạo bảng pharmacies thành công! <a href='check_email_column.php'>Kiểm tra lại</a>";
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-danger mt-3'>";
                echo "❌ Lỗi: " . $e->getMessage();
                echo "</div>";
            }
        } else {
            echo "<a href='check_email_column.php?create_table=1' class='btn btn-primary'>Tạo bảng pharmacies</a>";
        }
    }
    
    echo "<div class='mt-4'>";
    echo "<a href='index.php?page=auth&action=register' class='btn btn-success'>Thử đăng ký</a> ";
    echo "<a href='index.php?page=auth&action=login' class='btn btn-primary'>Đăng nhập</a>";
    echo "</div>";
    
    echo "</div>\n";
    echo "</body></html>\n";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "Lỗi kết nối database: " . $e->getMessage();
    echo "</div>";
}
