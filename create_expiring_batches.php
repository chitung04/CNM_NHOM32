<?php
/**
 * Script tạo lô thuốc sắp hết hạn và sắp hết hàng
 * Để test hệ thống thông báo
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

echo "<h2>🔧 Tạo lô thuốc sắp hết hạn và sắp hết hàng</h2>";
echo "<p>Pharmacy ID: <strong>{$pharmacyId}</strong></p>";
echo "<hr>";

$db = Database::getInstance();

try {
    // 1. Kiểm tra pharmacy có tồn tại không
    $pharmacy = $db->query("SELECT * FROM pharmacies WHERE pharmacy_id = ?", [$pharmacyId])->fetch();
    
    if (!$pharmacy) {
        echo "❌ Pharmacy ID {$pharmacyId} không tồn tại!<br>";
        echo "<p><a href='fix_missing_pharmacy.php'>→ Chạy script sửa lỗi pharmacy</a></p>";
        exit;
    }
    
    echo "✅ Pharmacy: {$pharmacy['pharmacy_name']}<br><br>";
    
    // 2. Tạo category nếu chưa có
    echo "<h3>Bước 1: Tạo danh mục</h3>";
    $category = $db->query("SELECT category_id FROM categories WHERE pharmacy_id = ? LIMIT 1", [$pharmacyId])->fetch();
    
    if (!$category) {
        $db->query("INSERT INTO categories (pharmacy_id, category_name, description) VALUES (?, ?, ?)", 
            [$pharmacyId, 'Thuốc thông dụng', 'Các loại thuốc thông dụng']);
        $categoryId = $db->lastInsertId();
        echo "✅ Tạo danh mục mới (ID: {$categoryId})<br>";
    } else {
        $categoryId = $category['category_id'];
        echo "✅ Sử dụng danh mục có sẵn (ID: {$categoryId})<br>";
    }
    
    // 3. Tạo unit nếu chưa có
    echo "<h3>Bước 2: Tạo đơn vị tính</h3>";
    $unit = $db->query("SELECT unit_id FROM units WHERE pharmacy_id = ? LIMIT 1", [$pharmacyId])->fetch();
    
    if (!$unit) {
        $db->query("INSERT INTO units (pharmacy_id, unit_name) VALUES (?, ?)", 
            [$pharmacyId, 'Viên']);
        $unitId = $db->lastInsertId();
        echo "✅ Tạo đơn vị tính mới (ID: {$unitId})<br>";
    } else {
        $unitId = $unit['unit_id'];
        echo "✅ Sử dụng đơn vị tính có sẵn (ID: {$unitId})<br>";
    }
    
    // 4. Tạo supplier nếu chưa có
    echo "<h3>Bước 3: Tạo nhà cung cấp</h3>";
    $supplier = $db->query("SELECT supplier_id FROM suppliers WHERE pharmacy_id = ? LIMIT 1", [$pharmacyId])->fetch();
    
    if (!$supplier) {
        $db->query("INSERT INTO suppliers (pharmacy_id, supplier_name, phone) VALUES (?, ?, ?)", 
            [$pharmacyId, 'Công ty Dược phẩm ABC', '0901234567']);
        $supplierId = $db->lastInsertId();
        echo "✅ Tạo nhà cung cấp mới (ID: {$supplierId})<br>";
    } else {
        $supplierId = $supplier['supplier_id'];
        echo "✅ Sử dụng nhà cung cấp có sẵn (ID: {$supplierId})<br>";
    }
    
    // 5. Tạo thuốc
    echo "<h3>Bước 4: Tạo thuốc</h3>";
    
    $medicines = [
        ['name' => 'Paracetamol 500mg', 'price' => 50000],
        ['name' => 'Amoxicillin 500mg', 'price' => 120000],
        ['name' => 'Buscopan 10mg', 'price' => 85000],
        ['name' => 'Vitamin C 1000mg', 'price' => 95000],
        ['name' => 'Strepsils Honey Lemon', 'price' => 45000],
    ];
    
    $medicineIds = [];
    foreach ($medicines as $med) {
        // Kiểm tra đã tồn tại chưa
        $existing = $db->query(
            "SELECT medicine_id FROM medicines WHERE pharmacy_id = ? AND medicine_name = ?", 
            [$pharmacyId, $med['name']]
        )->fetch();
        
        if ($existing) {
            $medicineIds[] = $existing['medicine_id'];
            echo "✓ Thuốc '{$med['name']}' đã tồn tại (ID: {$existing['medicine_id']})<br>";
        } else {
            $qrCode = 'MED_' . time() . '_' . rand(1000, 9999);
            $db->query(
                "INSERT INTO medicines (pharmacy_id, medicine_name, category_id, unit_id, price, qr_code) 
                VALUES (?, ?, ?, ?, ?, ?)",
                [$pharmacyId, $med['name'], $categoryId, $unitId, $med['price'], $qrCode]
            );
            $medicineIds[] = $db->lastInsertId();
            echo "✓ Tạo thuốc '{$med['name']}' (ID: {$db->lastInsertId()})<br>";
            usleep(100000); // 0.1 giây
        }
    }
    
    // 6. Tạo lô thuốc SẮP HẾT HẠN và SẮP HẾT HÀNG
    echo "<h3>Bước 5: Tạo lô thuốc</h3>";
    
    $batchesCreated = 0;
    
    foreach ($medicineIds as $index => $medId) {
        $medName = $medicines[$index]['name'];
        
        // Lô 1: SẮP HẾT HẠN (20 ngày nữa)
        $expiryDate1 = date('Y-m-d', strtotime('+20 days'));
        $qrCode1 = 'BATCH_' . time() . '_' . rand(1000, 9999);
        $batchNumber1 = 'LOT' . date('Ymd') . sprintf('%03d', $index * 2 + 1);
        
        $db->query(
            "INSERT INTO batches (pharmacy_id, medicine_id, supplier_id, batch_number, quantity, expiry_date, qr_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$pharmacyId, $medId, $supplierId, $batchNumber1, 100, $expiryDate1, $qrCode1]
        );
        echo "✅ Tạo lô <strong>SẮP HẾT HẠN</strong> cho '{$medName}'<br>";
        echo "&nbsp;&nbsp;&nbsp;→ Số lô: {$batchNumber1}, Hết hạn: {$expiryDate1} (còn 20 ngày)<br>";
        $batchesCreated++;
        usleep(100000);
        
        // Lô 2: SẮP HẾT HÀNG (số lượng thấp = 15 viên)
        $expiryDate2 = date('Y-m-d', strtotime('+180 days'));
        $qrCode2 = 'BATCH_' . time() . '_' . rand(1000, 9999);
        $batchNumber2 = 'LOT' . date('Ymd') . sprintf('%03d', $index * 2 + 2);
        
        $db->query(
            "INSERT INTO batches (pharmacy_id, medicine_id, supplier_id, batch_number, quantity, expiry_date, qr_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$pharmacyId, $medId, $supplierId, $batchNumber2, 15, $expiryDate2, $qrCode2]
        );
        echo "✅ Tạo lô <strong>SẮP HẾT HÀNG</strong> cho '{$medName}'<br>";
        echo "&nbsp;&nbsp;&nbsp;→ Số lô: {$batchNumber2}, Số lượng: 15 viên (dưới ngưỡng 50)<br>";
        $batchesCreated++;
        usleep(100000);
        
        echo "<br>";
    }
    
    // 7. Tạo thông báo
    echo "<h3>Bước 6: Tạo thông báo</h3>";
    
    // Xóa thông báo cũ
    $deleted = $db->query("DELETE FROM notifications WHERE pharmacy_id = ?", [$pharmacyId]);
    echo "✓ Xóa thông báo cũ<br>";
    
    // Tạo thông báo mới
    require_once 'models/Notification.php';
    $notificationModel = new Notification();
    
    echo "✓ Đang kiểm tra thuốc sắp hết hàng...<br>";
    $notificationModel->checkLowStock();
    
    echo "✓ Đang kiểm tra lô thuốc sắp hết hạn...<br>";
    $notificationModel->checkExpiring();
    
    // Đếm thông báo
    $notifCount = $db->query(
        "SELECT COUNT(*) as count FROM notifications WHERE pharmacy_id = ?", 
        [$pharmacyId]
    )->fetch();
    
    echo "✅ Tạo <strong>{$notifCount['count']}</strong> thông báo mới<br>";
    
    // Hiển thị chi tiết thông báo
    $notifications = $db->query(
        "SELECT n.*, m.medicine_name 
        FROM notifications n
        LEFT JOIN medicines m ON n.reference_id = m.medicine_id AND n.type = 'low_stock'
        LEFT JOIN batches b ON n.reference_id = b.batch_id AND n.type = 'expiry_warning'
        LEFT JOIN medicines m2 ON b.medicine_id = m2.medicine_id
        WHERE n.pharmacy_id = ?
        ORDER BY n.created_at DESC",
        [$pharmacyId]
    )->fetchAll();
    
    if (count($notifications) > 0) {
        echo "<br><h4>📋 Danh sách thông báo:</h4>";
        echo "<ul>";
        foreach ($notifications as $notif) {
            $icon = $notif['type'] === 'low_stock' ? '⚠️' : '⏰';
            echo "<li>{$icon} {$notif['message']}</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<h2>✅ HOÀN THÀNH!</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>📊 Tổng kết:</h3>";
    echo "<ul>";
    echo "<li>Pharmacy ID: <strong>{$pharmacyId}</strong></li>";
    echo "<li>Thuốc: <strong>" . count($medicineIds) . "</strong></li>";
    echo "<li>Lô thuốc: <strong>{$batchesCreated}</strong></li>";
    echo "<li>Thông báo: <strong>{$notifCount['count']}</strong></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<br>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
    echo "<h3>🎯 Bước tiếp theo:</h3>";
    echo "<ol>";
    echo "<li><a href='index.php' style='font-size: 16px; font-weight: bold;'>← Quay lại trang chủ</a> - Xem thông báo trên thanh cuộn</li>";
    echo "<li><a href='index.php?page=notifications' style='font-size: 16px; font-weight: bold;'>Xem chi tiết thông báo →</a></li>";
    echo "<li><a href='index.php?page=batches' style='font-size: 16px; font-weight: bold;'>Quản lý lô thuốc →</a></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<pre style='background: white; padding: 10px; overflow: auto;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    
    echo "<br>";
    echo "<p><a href='fix_missing_pharmacy.php' style='font-size: 16px;'>→ Thử chạy script sửa lỗi pharmacy</a></p>";
}
