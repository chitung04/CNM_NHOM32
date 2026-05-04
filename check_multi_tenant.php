<?php
/**
 * Script kiểm tra cài đặt Multi-Tenant
 */

$pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Kiểm tra Multi-Tenant</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>";
echo "<style>
body { 
    font-family: 'Segoe UI', sans-serif; 
    padding: 20px; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
} 
.container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.check-item {
    padding: 15px;
    margin: 10px 0;
    border-radius: 10px;
    border-left: 4px solid #3b82f6;
    background: #f8f9fa;
}
.check-item.success {
    border-color: #10b981;
    background: #d1fae5;
}
.check-item.error {
    border-color: #ef4444;
    background: #fee2e2;
}
.success-icon { color: #10b981; font-size: 1.5rem; }
.error-icon { color: #ef4444; font-size: 1.5rem; }
</style>";
echo "</head><body>\n";

echo "<div class='container'>\n";
echo "<h1 class='text-center mb-4'><i class='bi bi-check2-circle'></i> Kiểm tra cài đặt Multi-Tenant</h1>\n";
echo "<hr>\n";

$totalChecks = 0;
$passedChecks = 0;

// Check 1: Bảng pharmacies
echo "<h3><i class='bi bi-database'></i> 1. Kiểm tra bảng pharmacies</h3>\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'pharmacies'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='check-item success'><i class='bi bi-check-circle success-icon'></i> <strong>Bảng pharmacies đã tồn tại</strong></div>\n";
        $passedChecks++;
        
        // Đếm số nhà thuốc
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM pharmacies");
        $count = $stmt->fetch()['count'];
        echo "<div class='check-item'><i class='bi bi-info-circle'></i> Số nhà thuốc: <strong>$count</strong></div>\n";
        
        // Hiển thị danh sách
        $stmt = $pdo->query("SELECT * FROM pharmacies");
        $pharmacies = $stmt->fetchAll();
        if (count($pharmacies) > 0) {
            echo "<table class='table table-bordered mt-2'>\n";
            echo "<thead><tr><th>ID</th><th>Tên</th><th>Mã</th><th>Trạng thái</th><th>Gói</th></tr></thead>\n";
            echo "<tbody>\n";
            foreach ($pharmacies as $p) {
                echo "<tr>";
                echo "<td>{$p['pharmacy_id']}</td>";
                echo "<td>{$p['pharmacy_name']}</td>";
                echo "<td><code>{$p['pharmacy_code']}</code></td>";
                echo "<td><span class='badge bg-success'>{$p['status']}</span></td>";
                echo "<td><span class='badge bg-info'>{$p['subscription_plan']}</span></td>";
                echo "</tr>";
            }
            echo "</tbody></table>\n";
        }
    } else {
        echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Bảng pharmacies CHƯA tồn tại</strong><br><small>Cần chạy file database_multi_tenant_schema.sql</small></div>\n";
    }
} catch (Exception $e) {
    echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "</div>\n";
}
$totalChecks++;

// Check 2: Cột pharmacy_id trong users
echo "<h3 class='mt-4'><i class='bi bi-people'></i> 2. Kiểm tra cột pharmacy_id trong users</h3>\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'pharmacy_id'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='check-item success'><i class='bi bi-check-circle success-icon'></i> <strong>Cột pharmacy_id đã tồn tại trong bảng users</strong></div>\n";
        $passedChecks++;
        
        // Kiểm tra users có pharmacy_id
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE pharmacy_id IS NOT NULL");
        $count = $stmt->fetch()['count'];
        echo "<div class='check-item'><i class='bi bi-info-circle'></i> Số users có pharmacy_id: <strong>$count</strong></div>\n";
        
        // Kiểm tra users KHÔNG có pharmacy_id
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE pharmacy_id IS NULL");
        $nullCount = $stmt->fetch()['count'];
        if ($nullCount > 0) {
            echo "<div class='check-item error'><i class='bi bi-exclamation-triangle error-icon'></i> <strong>Cảnh báo:</strong> Có $nullCount users chưa được gán pharmacy_id</div>\n";
        }
    } else {
        echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Cột pharmacy_id CHƯA tồn tại trong bảng users</strong></div>\n";
    }
} catch (Exception $e) {
    echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "</div>\n";
}
$totalChecks++;

// Check 3: Cột pharmacy_id trong medicines
echo "<h3 class='mt-4'><i class='bi bi-capsule'></i> 3. Kiểm tra cột pharmacy_id trong medicines</h3>\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM medicines LIKE 'pharmacy_id'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='check-item success'><i class='bi bi-check-circle success-icon'></i> <strong>Cột pharmacy_id đã tồn tại trong bảng medicines</strong></div>\n";
        $passedChecks++;
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM medicines WHERE pharmacy_id IS NOT NULL");
        $count = $stmt->fetch()['count'];
        echo "<div class='check-item'><i class='bi bi-info-circle'></i> Số medicines có pharmacy_id: <strong>$count</strong></div>\n";
    } else {
        echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Cột pharmacy_id CHƯA tồn tại trong bảng medicines</strong></div>\n";
    }
} catch (Exception $e) {
    echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "</div>\n";
}
$totalChecks++;

// Check 4: Cột pharmacy_id trong batches
echo "<h3 class='mt-4'><i class='bi bi-box-seam'></i> 4. Kiểm tra cột pharmacy_id trong batches</h3>\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM batches LIKE 'pharmacy_id'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='check-item success'><i class='bi bi-check-circle success-icon'></i> <strong>Cột pharmacy_id đã tồn tại trong bảng batches</strong></div>\n";
        $passedChecks++;
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM batches WHERE pharmacy_id IS NOT NULL");
        $count = $stmt->fetch()['count'];
        echo "<div class='check-item'><i class='bi bi-info-circle'></i> Số batches có pharmacy_id: <strong>$count</strong></div>\n";
    } else {
        echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Cột pharmacy_id CHƯA tồn tại trong bảng batches</strong></div>\n";
    }
} catch (Exception $e) {
    echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "</div>\n";
}
$totalChecks++;

// Check 5: Cột pharmacy_id trong invoices
echo "<h3 class='mt-4'><i class='bi bi-receipt'></i> 5. Kiểm tra cột pharmacy_id trong invoices</h3>\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM invoices LIKE 'pharmacy_id'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='check-item success'><i class='bi bi-check-circle success-icon'></i> <strong>Cột pharmacy_id đã tồn tại trong bảng invoices</strong></div>\n";
        $passedChecks++;
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM invoices WHERE pharmacy_id IS NOT NULL");
        $count = $stmt->fetch()['count'];
        echo "<div class='check-item'><i class='bi bi-info-circle'></i> Số invoices có pharmacy_id: <strong>$count</strong></div>\n";
    } else {
        echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Cột pharmacy_id CHƯA tồn tại trong bảng invoices</strong></div>\n";
    }
} catch (Exception $e) {
    echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "</div>\n";
}
$totalChecks++;

// Check 6: Stored Procedure
echo "<h3 class='mt-4'><i class='bi bi-gear'></i> 6. Kiểm tra Stored Procedure</h3>\n";
try {
    $stmt = $pdo->query("SHOW PROCEDURE STATUS WHERE Name = 'create_pharmacy_with_admin'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='check-item success'><i class='bi bi-check-circle success-icon'></i> <strong>Stored Procedure create_pharmacy_with_admin đã tồn tại</strong></div>\n";
        $passedChecks++;
    } else {
        echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Stored Procedure create_pharmacy_with_admin CHƯA tồn tại</strong></div>\n";
    }
} catch (Exception $e) {
    echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "</div>\n";
}
$totalChecks++;

// Check 7: View pharmacy_statistics
echo "<h3 class='mt-4'><i class='bi bi-graph-up'></i> 7. Kiểm tra View pharmacy_statistics</h3>\n";
try {
    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_qlnt_db = 'pharmacy_statistics'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='check-item success'><i class='bi bi-check-circle success-icon'></i> <strong>View pharmacy_statistics đã tồn tại</strong></div>\n";
        $passedChecks++;
        
        // Hiển thị thống kê
        $stmt = $pdo->query("SELECT * FROM pharmacy_statistics");
        $stats = $stmt->fetchAll();
        if (count($stats) > 0) {
            echo "<table class='table table-bordered mt-2'>\n";
            echo "<thead><tr><th>Nhà thuốc</th><th>Users</th><th>Thuốc</th><th>Lô</th><th>Đơn hàng</th><th>Doanh thu</th></tr></thead>\n";
            echo "<tbody>\n";
            foreach ($stats as $s) {
                echo "<tr>";
                echo "<td>{$s['pharmacy_name']}</td>";
                echo "<td>{$s['total_users']}</td>";
                echo "<td>{$s['total_medicines']}</td>";
                echo "<td>{$s['total_batches']}</td>";
                echo "<td>{$s['total_invoices']}</td>";
                echo "<td>" . number_format($s['total_revenue']) . "đ</td>";
                echo "</tr>";
            }
            echo "</tbody></table>\n";
        }
    } else {
        echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>View pharmacy_statistics CHƯA tồn tại</strong></div>\n";
    }
} catch (Exception $e) {
    echo "<div class='check-item error'><i class='bi bi-x-circle error-icon'></i> <strong>Lỗi:</strong> " . $e->getMessage() . "</div>\n";
}
$totalChecks++;

// Summary
echo "<hr>\n";
$percentage = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;

echo "<div class='check-item' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;'>\n";
echo "<h2 class='text-center'><i class='bi bi-clipboard-data'></i> KẾT QUẢ</h2>\n";
echo "<div class='row text-center mt-3'>\n";
echo "<div class='col-md-4'>\n";
echo "<h1 class='display-4'>$totalChecks</h1>\n";
echo "<p>Tổng kiểm tra</p>\n";
echo "</div>\n";
echo "<div class='col-md-4'>\n";
echo "<h1 class='display-4'>$passedChecks</h1>\n";
echo "<p>Thành công</p>\n";
echo "</div>\n";
echo "<div class='col-md-4'>\n";
echo "<h1 class='display-4'>$percentage%</h1>\n";
echo "<p>Hoàn thành</p>\n";
echo "</div>\n";
echo "</div>\n";

if ($passedChecks == $totalChecks) {
    echo "<h3 class='text-center mt-3'>🎉 HỆ THỐNG MULTI-TENANT ĐÃ SẴN SÀNG!</h3>\n";
    echo "<p class='text-center'>Bạn có thể bắt đầu đăng ký nhà thuốc mới</p>\n";
} else {
    echo "<h3 class='text-center mt-3'>⚠️ Cần hoàn thành cài đặt</h3>\n";
    echo "<p class='text-center'>Vui lòng chạy file database_multi_tenant_schema.sql</p>\n";
}

echo "</div>\n";

echo "<div class='text-center mt-4'>\n";
echo "<a href='index.php?page=auth&action=register' class='btn btn-success btn-lg me-2'><i class='bi bi-person-plus'></i> Đăng ký nhà thuốc</a>\n";
echo "<a href='index.php?page=auth&action=login' class='btn btn-primary btn-lg me-2'><i class='bi bi-box-arrow-in-right'></i> Đăng nhập</a>\n";
echo "<a href='index.php?page=dashboard' class='btn btn-info btn-lg'><i class='bi bi-house'></i> Dashboard</a>\n";
echo "</div>\n";

echo "</div>\n";
echo "</body></html>\n";
