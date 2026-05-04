<?php
echo "<h2>🔧 Sửa lỗi toàn diện hệ thống</h2>";
echo "<hr>";

// Thông tin kết nối
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "qlnt_db";

try {
    // Kết nối database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #efe; border: 1px solid #0a0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Kết nối database thành công!";
    echo "</div>";
    
    // Kiểm tra và tạo các bảng thiếu
    echo "<h3>📋 Kiểm tra và tạo bảng thiếu...</h3>";
    
    // Danh sách các bảng cần thiết
    $requiredTables = [
        'users' => "
        CREATE TABLE IF NOT EXISTS users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            role ENUM('staff', 'manager') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'categories' => "
        CREATE TABLE IF NOT EXISTS categories (
            category_id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'units' => "
        CREATE TABLE IF NOT EXISTS units (
            unit_id INT PRIMARY KEY AUTO_INCREMENT,
            unit_name VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'suppliers' => "
        CREATE TABLE IF NOT EXISTS suppliers (
            supplier_id INT PRIMARY KEY AUTO_INCREMENT,
            supplier_name VARCHAR(150) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            address VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'medicines' => "
        CREATE TABLE IF NOT EXISTS medicines (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'batches' => "
        CREATE TABLE IF NOT EXISTS batches (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'invoices' => "
        CREATE TABLE IF NOT EXISTS invoices (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'invoice_details' => "
        CREATE TABLE IF NOT EXISTS invoice_details (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'notifications' => "
        CREATE TABLE IF NOT EXISTS notifications (
            notification_id INT PRIMARY KEY AUTO_INCREMENT,
            type ENUM('low_stock', 'expiry_warning') NOT NULL,
            message TEXT,
            reference_id INT,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    foreach ($requiredTables as $tableName => $createSQL) {
        try {
            $pdo->exec($createSQL);
            echo "✅ Bảng '$tableName' OK<br>";
        } catch (Exception $e) {
            echo "⚠️ Lỗi tạo bảng '$tableName': " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h3>👥 Kiểm tra và thêm users...</h3>";
    
    // Kiểm tra users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    if ($userCount == 0) {
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
    } else {
        echo "✅ Đã có $userCount users trong database<br>";
    }
    
    echo "<h3>📦 Kiểm tra và thêm dữ liệu cơ bản...</h3>";
    
    // Kiểm tra và thêm categories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $catCount = $stmt->fetch()['count'];
    
    if ($catCount == 0) {
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
    } else {
        echo "✅ Đã có $catCount categories<br>";
    }
    
    // Kiểm tra và thêm units
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM units");
    $unitCount = $stmt->fetch()['count'];
    
    if ($unitCount == 0) {
        $units = ['Viên', 'Hộp', 'Chai', 'Tuýp', 'Gói', 'Vỉ', 'Ống', 'Lọ'];
        $stmt = $pdo->prepare("INSERT INTO units (unit_name) VALUES (?)");
        foreach ($units as $unit) {
            $stmt->execute([$unit]);
        }
        echo "✅ Thêm " . count($units) . " units<br>";
    } else {
        echo "✅ Đã có $unitCount units<br>";
    }
    
    // Kiểm tra và thêm suppliers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM suppliers");
    $supplierCount = $stmt->fetch()['count'];
    
    if ($supplierCount == 0) {
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
    } else {
        echo "✅ Đã có $supplierCount suppliers<br>";
    }
    
    // Kiểm tra và thêm medicines mẫu
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM medicines");
    $medCount = $stmt->fetch()['count'];
    
    if ($medCount == 0) {
        $medicines = [
            ['Paracetamol 500mg', 2, 1, 2000, 'Thuốc giảm đau, hạ sốt', 'MED_001'],
            ['Amoxicillin 500mg', 1, 1, 3500, 'Kháng sinh điều trị nhiễm khuẩn', 'MED_002'],
            ['Vitamin C 1000mg', 3, 1, 5000, 'TPCN bổ sung vitamin C', 'MED_003'],
            ['Betamethasone Cream', 4, 4, 15000, 'Kem bôi điều trị viêm da', 'MED_004'],
            ['Băng gạc vô trùng', 5, 2, 5000, 'Băng gạc y tế vô trùng', 'MED_005'],
            ['Ibuprofen 400mg', 2, 1, 4500, 'Thuốc giảm đau, chống viêm', 'MED_006'],
            ['Cetirizine 10mg', 2, 1, 3000, 'Thuốc chống dị ứng', 'MED_007'],
            ['Omeprazole 20mg', 1, 1, 4500, 'Thuốc điều trị loét dạ dày', 'MED_008'],
            ['Calcium + D3', 3, 1, 12000, 'TPCN bổ sung canxi và vitamin D3', 'MED_009'],
            ['Nhiệt kế điện tử', 5, 1, 85000, 'Nhiệt kế đo nhiệt độ cơ thể', 'MED_010']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO medicines (medicine_name, category_id, unit_id, price, description, qr_code) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($medicines as $med) {
            $stmt->execute($med);
        }
        echo "✅ Thêm " . count($medicines) . " medicines mẫu<br>";
    } else {
        echo "✅ Đã có $medCount medicines<br>";
    }
    
    // Kiểm tra và thêm batches mẫu
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM batches");
    $batchCount = $stmt->fetch()['count'];
    
    if ($batchCount == 0) {
        $batches = [
            [1, 1, 'LOT001', 500, '2025-12-31', '2024-01-15', 1500, 'BATCH_001'],
            [2, 1, 'LOT002', 200, '2025-11-30', '2024-02-01', 2500, 'BATCH_002'],
            [3, 2, 'LOT003', 300, '2026-06-30', '2024-03-01', 4000, 'BATCH_003'],
            [4, 2, 'LOT004', 150, '2026-04-30', '2024-04-01', 12000, 'BATCH_004'],
            [5, 3, 'LOT005', 1000, '2027-12-31', '2024-01-01', 3000, 'BATCH_005'],
            [6, 1, 'LOT006', 400, '2025-10-31', '2024-02-15', 3500, 'BATCH_006'],
            [7, 2, 'LOT007', 350, '2025-09-30', '2024-03-10', 2500, 'BATCH_007'],
            [8, 1, 'LOT008', 300, '2026-08-31', '2024-04-05', 3800, 'BATCH_008'],
            [9, 3, 'LOT009', 250, '2026-07-31', '2024-05-01', 10000, 'BATCH_009'],
            [10, 3, 'LOT010', 100, '2027-06-30', '2024-01-10', 75000, 'BATCH_010']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO batches (medicine_id, supplier_id, batch_number, quantity, expiry_date, import_date, import_price, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($batches as $batch) {
            $stmt->execute($batch);
        }
        echo "✅ Thêm " . count($batches) . " batches mẫu<br>";
    } else {
        echo "✅ Đã có $batchCount batches<br>";
    }
    
    echo "<h3>🔧 Sửa lỗi navbar...</h3>";
    
    // Tạo navbar đơn giản hơn để tránh lỗi
    $simpleNavbar = '<?php
$currentPage = $_GET[\'page\'] ?? \'dashboard\';
$currentAction = $_GET[\'action\'] ?? \'index\';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php?page=dashboard">
            <img src="assets/images/logo.png" alt="Logo" height="32" class="me-2" style="border-radius: 8px;">
            Quản lý nhà thuốc
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown position-relative">
                    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5" id="notification-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count" style="display: none;">
                            0
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <li><h6 class="dropdown-header">Thông báo</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li id="notification-list">
                            <div class="dropdown-item text-muted text-center">
                                Không có thông báo mới
                            </div>
                        </li>
                    </ul>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION[\'full_name\'] ?? \'User\'); ?>
                        <span class="badge bg-light text-dark ms-2">
                            <?php echo ($_SESSION[\'role\'] ?? \'staff\') === \'manager\' ? \'Quản lý\' : \'Nhân viên\'; ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="index.php?page=profile&action=permissions">
                                <i class="bi bi-shield-check me-2"></i>Quyền của tôi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-person me-2"></i>Thông tin cá nhân
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?page=auth&action=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>';
    
    file_put_contents('views/layouts/navbar_simple.php', $simpleNavbar);
    echo "✅ Tạo navbar đơn giản<br>";
    
    echo "<div style='background: #e7f3ff; border: 1px solid #007bff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Sửa lỗi hoàn tất!</h3>";
    echo "<p><strong>Hệ thống đã được sửa lỗi:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Tất cả các bảng cần thiết đã được tạo</li>";
    echo "<li>✅ Dữ liệu mẫu đã được thêm</li>";
    echo "<li>✅ Navbar đã được đơn giản hóa</li>";
    echo "<li>✅ Users với mật khẩu 123456 đã sẵn sàng</li>";
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
    echo "❌ <strong>Lỗi database:</strong> " . $e->getMessage();
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>Lỗi hệ thống:</strong> " . $e->getMessage();
    echo "</div>";
}
?>