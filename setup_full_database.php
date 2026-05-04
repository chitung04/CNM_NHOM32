<?php
echo "<h2>🔧 Thiết lập Database đầy đủ</h2>";
echo "<hr>";

// Thông tin kết nối
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "qlnt_db";

try {
    // Kết nối MySQL server
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Kết nối MySQL server thành công!";
    echo "</div>";
    
    // Tạo database
    echo "<h3>🗄️ Tạo Database...</h3>";
    $pdo->exec("DROP DATABASE IF EXISTS $dbname");
    $pdo->exec("CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE $dbname");
    
    echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Tạo database '$dbname' thành công!";
    echo "</div>";
    
    // Tạo các bảng
    echo "<h3>📋 Tạo các bảng...</h3>";
    
    // Bảng users
    $pdo->exec("
    CREATE TABLE users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        role ENUM('staff', 'manager') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'users'<br>";
    
    // Bảng categories
    $pdo->exec("
    CREATE TABLE categories (
        category_id INT PRIMARY KEY AUTO_INCREMENT,
        category_name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'categories'<br>";
    
    // Bảng units
    $pdo->exec("
    CREATE TABLE units (
        unit_id INT PRIMARY KEY AUTO_INCREMENT,
        unit_name VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'units'<br>";
    
    // Bảng suppliers
    $pdo->exec("
    CREATE TABLE suppliers (
        supplier_id INT PRIMARY KEY AUTO_INCREMENT,
        supplier_name VARCHAR(150) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        address VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'suppliers'<br>";
    
    // Bảng medicines
    $pdo->exec("
    CREATE TABLE medicines (
        medicine_id INT PRIMARY KEY AUTO_INCREMENT,
        medicine_name VARCHAR(150) NOT NULL,
        category_id INT,
        unit_id INT,
        price DECIMAL(10,2) NOT NULL,
        description TEXT,
        qr_code VARCHAR(50) UNIQUE,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL,
        deleted_at DATETIME DEFAULT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(category_id),
        FOREIGN KEY (unit_id) REFERENCES units(unit_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'medicines'<br>";
    
    // Bảng batches
    $pdo->exec("
    CREATE TABLE batches (
        batch_id INT PRIMARY KEY AUTO_INCREMENT,
        medicine_id INT NOT NULL,
        supplier_id INT,
        batch_number VARCHAR(50) NOT NULL,
        quantity INT NOT NULL,
        expiry_date DATE NOT NULL,
        import_date DATE NOT NULL,
        import_price DECIMAL(10,2) DEFAULT 0,
        qr_code VARCHAR(50) UNIQUE,
        status ENUM('active', 'expired', 'sold_out') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id),
        FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'batches'<br>";
    
    // Bảng invoices
    $pdo->exec("
    CREATE TABLE invoices (
        invoice_id INT PRIMARY KEY AUTO_INCREMENT,
        invoice_number VARCHAR(50) UNIQUE NOT NULL,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        discount DECIMAL(10,2) DEFAULT 0,
        final_amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('cash', 'bank_transfer') DEFAULT NULL,
        amount_paid DECIMAL(10,2) DEFAULT 0,
        bank_qr_code VARCHAR(255) DEFAULT NULL,
        qr_code VARCHAR(50) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'invoices'<br>";
    
    // Bảng invoice_details
    $pdo->exec("
    CREATE TABLE invoice_details (
        detail_id INT PRIMARY KEY AUTO_INCREMENT,
        invoice_id INT NOT NULL,
        medicine_id INT NOT NULL,
        batch_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id),
        FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id),
        FOREIGN KEY (batch_id) REFERENCES batches(batch_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'invoice_details'<br>";
    
    // Bảng notifications
    $pdo->exec("
    CREATE TABLE notifications (
        notification_id INT PRIMARY KEY AUTO_INCREMENT,
        type ENUM('low_stock', 'expiry_warning') NOT NULL,
        message TEXT,
        reference_id INT,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tạo bảng 'notifications'<br>";
    
    echo "<h3>👥 Thêm dữ liệu users...</h3>";
    
    // Thêm users
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?)");
    
    $users = [
        ['admin', $hash, 'Quản lý', '0123456789', 'manager'],
        ['nhanvien1', $hash, 'Nhân viên 1', '0987654321', 'staff'],
        ['nhanvien2', $hash, 'Nhân viên 2', '0912345678', 'staff']
    ];
    
    foreach ($users as $user) {
        $stmt->execute($user);
        echo "✅ Thêm user '{$user[0]}'<br>";
    }
    
    echo "<h3>📦 Thêm dữ liệu cơ bản...</h3>";
    
    // Thêm categories
    $categories = [
        ['Thuốc kê đơn', 'Thuốc chỉ bán theo đơn của bác sĩ'],
        ['Thuốc không kê đơn', 'Thuốc bán tự do, không cần đơn (OTC)'],
        ['Thực phẩm chức năng', 'TPCN bổ sung dinh dưỡng, vitamin'],
        ['Dược mỹ phẩm', 'Mỹ phẩm có tác dụng điều trị'],
        ['Thiết bị y tế', 'Dụng cụ, thiết bị y tế']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "✅ Thêm " . count($categories) . " categories<br>";
    
    // Thêm units
    $units = ['Viên', 'Hộp', 'Chai', 'Tuýp', 'Gói', 'Vỉ', 'Ống', 'Lọ'];
    $stmt = $pdo->prepare("INSERT INTO units (unit_name) VALUES (?)");
    foreach ($units as $unit) {
        $stmt->execute([$unit]);
    }
    echo "✅ Thêm " . count($units) . " units<br>";
    
    // Thêm suppliers
    $suppliers = [
        ['Công ty Dược phẩm Hà Nội', '0241234567', 'hanoi@pharma.vn', '123 Đường Láng, Đống Đa, Hà Nội'],
        ['Công ty Dược Sài Gòn', '0281234567', 'saigon@pharma.vn', '456 Nguyễn Trãi, Quận 1, TP.HCM'],
        ['Công ty Dược phẩm Trung ương', '0243456789', 'central@pharma.vn', '789 Giải Phóng, Hai Bà Trưng, Hà Nội']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO suppliers (supplier_name, phone, email, address) VALUES (?, ?, ?, ?)");
    foreach ($suppliers as $supplier) {
        $stmt->execute($supplier);
    }
    echo "✅ Thêm " . count($suppliers) . " suppliers<br>";
    
    // Thêm một số medicines mẫu
    $medicines = [
        ['Paracetamol 500mg', 2, 1, 2000, 'Thuốc giảm đau, hạ sốt', 'MED_001'],
        ['Amoxicillin 500mg', 1, 1, 3500, 'Kháng sinh điều trị nhiễm khuẩn', 'MED_002'],
        ['Vitamin C 1000mg', 3, 1, 5000, 'TPCN bổ sung vitamin C', 'MED_003'],
        ['Betamethasone Cream', 4, 4, 15000, 'Kem bôi điều trị viêm da', 'MED_004'],
        ['Băng gạc vô trùng', 5, 2, 5000, 'Băng gạc y tế vô trùng', 'MED_005']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO medicines (medicine_name, category_id, unit_id, price, description, qr_code) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($medicines as $med) {
        $stmt->execute($med);
    }
    echo "✅ Thêm " . count($medicines) . " medicines mẫu<br>";
    
    // Thêm một số batches mẫu
    $batches = [
        [1, 1, 'LOT001', 500, '2025-12-31', '2024-01-15', 1500, 'BATCH_001'],
        [2, 1, 'LOT002', 200, '2025-11-30', '2024-02-01', 2500, 'BATCH_002'],
        [3, 2, 'LOT003', 300, '2026-06-30', '2024-03-01', 4000, 'BATCH_003'],
        [4, 2, 'LOT004', 150, '2026-04-30', '2024-04-01', 12000, 'BATCH_004'],
        [5, 3, 'LOT005', 1000, '2027-12-31', '2024-01-01', 3000, 'BATCH_005']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO batches (medicine_id, supplier_id, batch_number, quantity, expiry_date, import_date, import_price, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($batches as $batch) {
        $stmt->execute($batch);
    }
    echo "✅ Thêm " . count($batches) . " batches mẫu<br>";
    
    echo "<div style='background: #e7f3ff; border: 1px solid #007bff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Thiết lập hoàn tất!</h3>";
    echo "<p><strong>Database đã được tạo đầy đủ với:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Tất cả các bảng cần thiết</li>";
    echo "<li>✅ 3 users với mật khẩu 123456</li>";
    echo "<li>✅ Dữ liệu mẫu cơ bản</li>";
    echo "<li>✅ Các ràng buộc và index</li>";
    echo "</ul>";
    echo "<br>";
    echo "<p><strong>Thông tin đăng nhập:</strong></p>";
    echo "<ul>";
    echo "<li><strong>admin</strong> / 123456 (Manager)</li>";
    echo "<li><strong>nhanvien1</strong> / 123456 (Staff)</li>";
    echo "<li><strong>nhanvien2</strong> / 123456 (Staff)</li>";
    echo "</ul>";
    echo "<br>";
    echo "<p><a href='index.php?page=auth&action=login' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Đi đến trang đăng nhập</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>Lỗi:</strong> " . $e->getMessage();
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Giải pháp:</strong><br>";
    echo "1. Kiểm tra XAMPP MySQL đã khởi động<br>";
    echo "2. Kiểm tra thông tin kết nối database<br>";
    echo "3. Đảm bảo có quyền tạo database";
    echo "</div>";
}

// ===== PHẦN CẬP NHẬT DATABASE HIỆN TẠI =====
echo "<hr><h2>🔄 Cập nhật Database hiện tại</h2>";

try {
    // Kết nối database hiện tại
    $pdo_update = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo_update->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>👤 Cập nhật tên admin...</h3>";
    
    // Cập nhật tên admin từ "Quản lý chính" thành "Quản lý"
    $updateSql = "UPDATE users SET full_name = 'Quản lý' WHERE username = 'admin' AND full_name = 'Quản lý chính'";
    $stmt = $pdo_update->prepare($updateSql);
    $result = $stmt->execute();
    $rowsAffected = $stmt->rowCount();
    
    if ($rowsAffected > 0) {
        echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ Đã cập nhật tên admin từ 'Quản lý chính' thành 'Quản lý'";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "ℹ️ Tên admin đã là 'Quản lý' hoặc không tìm thấy user admin";
        echo "</div>";
    }
    
    // Kiểm tra kết quả
    $checkSql = "SELECT username, full_name, role FROM users WHERE username = 'admin'";
    $checkStmt = $pdo_update->query($checkSql);
    $admin = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<h3>📋 Thông tin admin hiện tại:</h3>";
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Username:</strong> " . htmlspecialchars($admin['username']) . "<br>";
        echo "<strong>Tên hiển thị:</strong> " . htmlspecialchars($admin['full_name']) . "<br>";
        echo "<strong>Vai trò:</strong> " . htmlspecialchars($admin['role']);
        echo "</div>";
    }
    
    echo "<div style='background: #e7f3ff; border: 1px solid #007bff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ Cập nhật hoàn tất!</h3>";
    echo "<p>Bây giờ admin sẽ hiển thị là 'Quản lý' thay vì 'Quản lý chính'</p>";
    echo "<p><strong>Lưu ý:</strong> Vui lòng đăng xuất và đăng nhập lại để thấy thay đổi trong giao diện!</p>";
    echo "<br>";
    echo "<p><a href='index.php?page=auth&action=logout' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🚪 Đăng xuất</a>";
    echo "<a href='index.php?page=auth&action=login' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Đăng nhập lại</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>Lỗi cập nhật:</strong> " . $e->getMessage();
    echo "</div>";
}
?>