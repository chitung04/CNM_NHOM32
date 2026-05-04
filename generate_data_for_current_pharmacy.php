<?php
/**
 * Script tạo dữ liệu mẫu cho pharmacy hiện tại
 * Chạy script này để tạo thuốc và lô thuốc sắp hết hạn/hết hàng
 */

session_start();

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/secure_session.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    die("❌ Vui lòng đăng nhập trước!");
}

// Lấy pharmacy_id từ session
require_once 'helpers/pharmacy.php';
$pharmacyId = getCurrentPharmacyId();

if (!$pharmacyId) {
    die("❌ Không tìm thấy pharmacy_id!");
}

echo "<h2>🔧 Tạo dữ liệu mẫu cho Pharmacy ID: {$pharmacyId}</h2>";
echo "<hr>";

$db = Database::getInstance();

try {
    // 1. Kiểm tra và tạo categories
    echo "<h3>1. Tạo danh mục thuốc...</h3>";
    $categories = [
        'Thuốc giảm đau',
        'Thuốc kháng sinh',
        'Thuốc tiêu hóa',
        'Vitamin & Khoáng chất'
    ];
    
    $categoryIds = [];
    foreach ($categories as $catName) {
        // Kiểm tra đã tồn tại chưa
        $check = $db->query("SELECT category_id FROM categories WHERE pharmacy_id = ? AND category_name = ?", 
            [$pharmacyId, $catName])->fetch();
        
        if ($check) {
            $categoryIds[] = $check['category_id'];
            echo "✓ Danh mục '{$catName}' đã tồn tại (ID: {$check['category_id']})<br>";
        } else {
            $db->query("INSERT INTO categories (pharmacy_id, category_name) VALUES (?, ?)", 
                [$pharmacyId, $catName]);
            $categoryIds[] = $db->lastInsertId();
            echo "✓ Tạo danh mục '{$catName}' (ID: {$db->lastInsertId()})<br>";
        }
    }
    
    // 2. Kiểm tra và tạo units
    echo "<h3>2. Tạo đơn vị tính...</h3>";
    $units = ['Viên', 'Hộp', 'Chai', 'Tuýp'];
    
    $unitIds = [];
    foreach ($units as $unitName) {
        $check = $db->query("SELECT unit_id FROM units WHERE pharmacy_id = ? AND unit_name = ?", 
            [$pharmacyId, $unitName])->fetch();
        
        if ($check) {
            $unitIds[] = $check['unit_id'];
            echo "✓ Đơn vị '{$unitName}' đã tồn tại (ID: {$check['unit_id']})<br>";
        } else {
            $db->query("INSERT INTO units (pharmacy_id, unit_name) VALUES (?, ?)", 
                [$pharmacyId, $unitName]);
            $unitIds[] = $db->lastInsertId();
            echo "✓ Tạo đơn vị '{$unitName}' (ID: {$db->lastInsertId()})<br>";
        }
    }
    
    // 3. Kiểm tra và tạo suppliers
    echo "<h3>3. Tạo nhà cung cấp...</h3>";
    $suppliers = [
        ['name' => 'Công ty Dược phẩm A', 'phone' => '0901234567'],
        ['name' => 'Công ty Dược phẩm B', 'phone' => '0907654321']
    ];
    
    $supplierIds = [];
    foreach ($suppliers as $supplier) {
        $check = $db->query("SELECT supplier_id FROM suppliers WHERE pharmacy_id = ? AND supplier_name = ?", 
            [$pharmacyId, $supplier['name']])->fetch();
        
        if ($check) {
            $supplierIds[] = $check['supplier_id'];
            echo "✓ NCC '{$supplier['name']}' đã tồn tại (ID: {$check['supplier_id']})<br>";
        } else {
            $db->query("INSERT INTO suppliers (pharmacy_id, supplier_name, phone) VALUES (?, ?, ?)", 
                [$pharmacyId, $supplier['name'], $supplier['phone']]);
            $supplierIds[] = $db->lastInsertId();
            echo "✓ Tạo NCC '{$supplier['name']}' (ID: {$db->lastInsertId()})<br>";
        }
    }
    
    // 4. Tạo thuốc
    echo "<h3>4. Tạo thuốc...</h3>";
    $medicines = [
        ['name' => 'Paracetamol 500mg', 'price' => 50000, 'cat' => 0, 'unit' => 0],
        ['name' => 'Amoxicillin 500mg', 'price' => 120000, 'cat' => 1, 'unit' => 0],
        ['name' => 'Buscopan 10mg', 'price' => 85000, 'cat' => 2, 'unit' => 0],
        ['name' => 'Vitamin C 1000mg', 'price' => 95000, 'cat' => 3, 'unit' => 0],
        ['name' => 'Strepsils Honey Lemon', 'price' => 45000, 'cat' => 0, 'unit' => 0],
        ['name' => 'Omeprazole 20mg', 'price' => 150000, 'cat' => 2, 'unit' => 0],
        ['name' => 'Cetirizine 10mg', 'price' => 65000, 'cat' => 0, 'unit' => 0],
        ['name' => 'Ibuprofen 400mg', 'price' => 75000, 'cat' => 0, 'unit' => 0],
    ];
    
    $medicineIds = [];
    foreach ($medicines as $med) {
        // Kiểm tra đã tồn tại chưa
        $check = $db->query("SELECT medicine_id FROM medicines WHERE pharmacy_id = ? AND medicine_name = ?", 
            [$pharmacyId, $med['name']])->fetch();
        
        if ($check) {
            $medicineIds[] = $check['medicine_id'];
            echo "✓ Thuốc '{$med['name']}' đã tồn tại (ID: {$check['medicine_id']})<br>";
        } else {
            $qrCode = 'MED_' . time() . '_' . rand(1000, 9999);
            $db->query(
                "INSERT INTO medicines (pharmacy_id, medicine_name, category_id, unit_id, price, qr_code) 
                VALUES (?, ?, ?, ?, ?, ?)",
                [$pharmacyId, $med['name'], $categoryIds[$med['cat']], $unitIds[$med['unit']], $med['price'], $qrCode]
            );
            $medicineIds[] = $db->lastInsertId();
            echo "✓ Tạo thuốc '{$med['name']}' (ID: {$db->lastInsertId()})<br>";
            usleep(100000); // 0.1 giây để QR code unique
        }
    }
    
    // 5. Tạo lô thuốc với một số lô SẮP HẾT HẠN và SẮP HẾT HÀNG
    echo "<h3>5. Tạo lô thuốc...</h3>";
    
    $batchCount = 0;
    foreach ($medicineIds as $index => $medId) {
        // Lô 1: Sắp hết hạn (30 ngày nữa)
        $expiryDate1 = date('Y-m-d', strtotime('+30 days'));
        $qrCode1 = 'BATCH_' . time() . '_' . rand(1000, 9999);
        $db->query(
            "INSERT INTO batches (pharmacy_id, medicine_id, supplier_id, batch_number, quantity, expiry_date, qr_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$pharmacyId, $medId, $supplierIds[0], 'LOT' . date('Ymd') . sprintf('%03d', $index * 2 + 1), 80, $expiryDate1, $qrCode1]
        );
        echo "✓ Tạo lô sắp hết hạn (30 ngày) cho thuốc ID {$medId}<br>";
        $batchCount++;
        usleep(100000);
        
        // Lô 2: Sắp hết hàng (số lượng thấp)
        $expiryDate2 = date('Y-m-d', strtotime('+180 days'));
        $qrCode2 = 'BATCH_' . time() . '_' . rand(1000, 9999);
        $db->query(
            "INSERT INTO batches (pharmacy_id, medicine_id, supplier_id, batch_number, quantity, expiry_date, qr_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$pharmacyId, $medId, $supplierIds[1], 'LOT' . date('Ymd') . sprintf('%03d', $index * 2 + 2), 25, $expiryDate2, $qrCode2]
        );
        echo "✓ Tạo lô sắp hết hàng (25 viên) cho thuốc ID {$medId}<br>";
        $batchCount++;
        usleep(100000);
    }
    
    // 6. Tạo thông báo
    echo "<h3>6. Tạo thông báo...</h3>";
    
    // Xóa thông báo cũ
    $db->query("DELETE FROM notifications WHERE pharmacy_id = ?", [$pharmacyId]);
    echo "✓ Xóa thông báo cũ<br>";
    
    // Tạo thông báo mới
    require_once 'models/Notification.php';
    $notificationModel = new Notification();
    $notificationModel->checkLowStock();
    $notificationModel->checkExpiring();
    
    $notifCount = $db->query("SELECT COUNT(*) as count FROM notifications WHERE pharmacy_id = ?", [$pharmacyId])->fetch();
    echo "✓ Tạo {$notifCount['count']} thông báo mới<br>";
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH!</h2>";
    echo "<p><strong>Tổng kết:</strong></p>";
    echo "<ul>";
    echo "<li>Pharmacy ID: {$pharmacyId}</li>";
    echo "<li>Danh mục: " . count($categoryIds) . "</li>";
    echo "<li>Đơn vị: " . count($unitIds) . "</li>";
    echo "<li>Nhà cung cấp: " . count($supplierIds) . "</li>";
    echo "<li>Thuốc: " . count($medicineIds) . "</li>";
    echo "<li>Lô thuốc: {$batchCount}</li>";
    echo "<li>Thông báo: {$notifCount['count']}</li>";
    echo "</ul>";
    
    echo "<p><a href='index.php' class='btn btn-primary'>← Quay lại trang chủ</a></p>";
    echo "<p><a href='index.php?page=notifications' class='btn btn-success'>Xem thông báo →</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
