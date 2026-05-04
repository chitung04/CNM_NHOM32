<?php
/**
 * Script tự động tạo dữ liệu mẫu cho pharmacy
 * Chạy khi đăng nhập để tạo thuốc + lô tự động
 */

session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['pharmacy_id'])) {
    die("❌ Vui lòng đăng nhập trước!");
}

require_once 'config/database.php';
require_once 'models/Database.php';

$pharmacyId = $_SESSION['pharmacy_id'];
$userId = $_SESSION['user_id'];

echo "<h2>🚀 Tự động tạo dữ liệu mẫu</h2>";
echo "<p>Pharmacy ID: <strong>$pharmacyId</strong></p>";
echo "<p>User ID: <strong>$userId</strong></p>";
echo "<hr>";

$db = Database::getInstance();

// Kiểm tra đã có dữ liệu chưa
$stmt = $db->query("SELECT COUNT(*) as count FROM medicines WHERE pharmacy_id = ?", [$pharmacyId]);
$medicineCount = $stmt->fetch()['count'];

if ($medicineCount > 0) {
    echo "<div style='background: #ffffee; border: 1px solid orange; padding: 20px;'>";
    echo "<h3>⚠️ Đã có dữ liệu!</h3>";
    echo "<p>Pharmacy này đã có <strong>$medicineCount</strong> loại thuốc.</p>";
    echo "<p>Bạn có muốn:</p>";
    echo "<ul>";
    echo "<li><a href='index.php?page=medicines'>Xem danh sách thuốc</a></li>";
    echo "<li><a href='index.php?page=batches'>Xem danh sách lô thuốc</a></li>";
    echo "<li><a href='index.php?page=sales'>Vào trang bán hàng</a></li>";
    echo "</ul>";
    echo "</div>";
    exit;
}

echo "<p>✅ Pharmacy chưa có dữ liệu. Bắt đầu tạo...</p>";
echo "<hr>";

try {
    // 1. Tạo categories
    echo "<h3>1. Tạo danh mục thuốc...</h3>";
    
    $categories = [
        ['Kháng sinh', 'Thuốc kháng sinh điều trị nhiễm khuẩn'],
        ['Giảm đau', 'Thuốc giảm đau, hạ sốt'],
        ['Tim mạch', 'Thuốc điều trị tim mạch'],
        ['Tiểu đường', 'Thuốc điều trị tiểu đường'],
        ['Vitamin', 'Vitamin và khoáng chất']
    ];
    
    $categoryIds = [];
    foreach ($categories as $cat) {
        $stmt = $db->query(
            "INSERT INTO categories (category_name, description, pharmacy_id, created_at) VALUES (?, ?, ?, NOW())",
            [$cat[0], $cat[1], $pharmacyId]
        );
        $categoryIds[] = $db->lastInsertId();
        echo "✅ Tạo danh mục: {$cat[0]}<br>";
    }
    
    echo "<hr>";
    
    // 2. Tạo units
    echo "<h3>2. Tạo đơn vị tính...</h3>";
    
    $units = ['Viên', 'Vỉ', 'Hộp', 'Chai', 'Ống'];
    $unitIds = [];
    
    foreach ($units as $unit) {
        $stmt = $db->query(
            "INSERT INTO units (unit_name, pharmacy_id, created_at) VALUES (?, ?, NOW())",
            [$unit, $pharmacyId]
        );
        $unitIds[] = $db->lastInsertId();
        echo "✅ Tạo đơn vị: $unit<br>";
    }
    
    echo "<hr>";
    
    // 3. Tạo suppliers
    echo "<h3>3. Tạo nhà cung cấp...</h3>";
    
    $suppliers = [
        ['Công ty Dược phẩm A', '123 Đường ABC', '0901234567', 'contact@pharma-a.com'],
        ['Công ty Dược phẩm B', '456 Đường XYZ', '0907654321', 'info@pharma-b.com']
    ];
    
    $supplierIds = [];
    foreach ($suppliers as $sup) {
        $stmt = $db->query(
            "INSERT INTO suppliers (supplier_name, address, phone, email, pharmacy_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [$sup[0], $sup[1], $sup[2], $sup[3], $pharmacyId]
        );
        $supplierIds[] = $db->lastInsertId();
        echo "✅ Tạo NCC: {$sup[0]}<br>";
    }
    
    echo "<hr>";
    
    // 4. Tạo medicines
    echo "<h3>4. Tạo thuốc...</h3>";
    
    $medicines = [
        ['Amoxicillin 500mg', $categoryIds[0], $unitIds[0], 5000, 'Kháng sinh điều trị nhiễm khuẩn'],
        ['Paracetamol 500mg', $categoryIds[1], $unitIds[0], 3000, 'Thuốc giảm đau, hạ sốt'],
        ['Ibuprofen 400mg', $categoryIds[1], $unitIds[0], 4000, 'Thuốc giảm đau, chống viêm'],
        ['Amlodipine 5mg', $categoryIds[2], $unitIds[0], 8000, 'Thuốc điều trị tăng huyết áp'],
        ['Metformin 500mg', $categoryIds[3], $unitIds[0], 6000, 'Thuốc điều trị tiểu đường type 2'],
        ['Vitamin C 1000mg', $categoryIds[4], $unitIds[0], 2000, 'Bổ sung vitamin C'],
        ['Cefixime 200mg', $categoryIds[0], $unitIds[0], 12000, 'Kháng sinh thế hệ 3'],
        ['Aspirin 100mg', $categoryIds[2], $unitIds[0], 3500, 'Thuốc chống đông máu'],
        ['Omeprazole 20mg', $categoryIds[1], $unitIds[0], 7000, 'Thuốc điều trị loét dạ dày'],
        ['Cetirizine 10mg', $categoryIds[1], $unitIds[0], 2500, 'Thuốc chống dị ứng']
    ];
    
    $medicineIds = [];
    foreach ($medicines as $med) {
        $qrCode = 'MED_' . time() . '_' . rand(1000, 9999);
        $stmt = $db->query(
            "INSERT INTO medicines (medicine_name, category_id, unit_id, price, description, pharmacy_id, qr_code, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$med[0], $med[1], $med[2], $med[3], $med[4], $pharmacyId, $qrCode]
        );
        $medicineIds[] = $db->lastInsertId();
        echo "✅ Tạo thuốc: {$med[0]}<br>";
        usleep(10000); // Đợi 0.01s để QR code không trùng
    }
    
    echo "<hr>";
    
    // 5. Tạo batches
    echo "<h3>5. Tạo lô thuốc...</h3>";
    
    $batchCount = 0;
    foreach ($medicineIds as $index => $medId) {
        // Tạo 2 lô cho mỗi thuốc
        for ($i = 1; $i <= 2; $i++) {
            $batchNumber = "BATCH_P{$pharmacyId}_" . str_pad($batchCount + 1, 4, '0', STR_PAD_LEFT);
            $quantity = rand(100, 500);
            $importDate = date('Y-m-d');
            $expiryDate = date('Y-m-d', strtotime('+' . rand(12, 24) . ' months'));
            $supplierId = $supplierIds[array_rand($supplierIds)];
            $qrCode = 'BATCH_' . time() . '_' . rand(1000, 9999);
            
            $stmt = $db->query(
                "INSERT INTO batches (medicine_id, batch_number, quantity, import_date, expiry_date, supplier_id, status, pharmacy_id, qr_code, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW())",
                [$medId, $batchNumber, $quantity, $importDate, $expiryDate, $supplierId, $pharmacyId, $qrCode]
            );
            
            $batchCount++;
            usleep(10000); // Đợi 0.01s
        }
        echo "✅ Tạo 2 lô cho thuốc ID: $medId<br>";
    }
    
    echo "<hr>";
    
    // Thống kê
    echo "<div style='background: #eeffee; border: 2px solid green; padding: 20px; margin: 20px 0;'>";
    echo "<h2>🎉 HOÀN TẤT!</h2>";
    echo "<h3>Đã tạo:</h3>";
    echo "<ul style='font-size: 18px;'>";
    echo "<li>✅ <strong>" . count($categoryIds) . "</strong> danh mục</li>";
    echo "<li>✅ <strong>" . count($unitIds) . "</strong> đơn vị tính</li>";
    echo "<li>✅ <strong>" . count($supplierIds) . "</strong> nhà cung cấp</li>";
    echo "<li>✅ <strong>" . count($medicineIds) . "</strong> loại thuốc</li>";
    echo "<li>✅ <strong>$batchCount</strong> lô thuốc</li>";
    echo "</ul>";
    echo "<h3>Bây giờ bạn có thể:</h3>";
    echo "<ul style='font-size: 16px;'>";
    echo "<li><a href='index.php?page=medicines' style='color: blue;'>📦 Xem danh sách thuốc</a></li>";
    echo "<li><a href='index.php?page=batches' style='color: blue;'>📋 Xem danh sách lô thuốc</a></li>";
    echo "<li><a href='index.php?page=sales' style='color: blue;'>🛒 Vào trang bán hàng</a></li>";
    echo "<li><a href='index.php?page=dashboard' style='color: blue;'>🏠 Về trang chủ</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffeeee; border: 2px solid red; padding: 20px;'>";
    echo "<h3>❌ Lỗi:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}
a {
    text-decoration: none;
    font-weight: bold;
}
a:hover {
    text-decoration: underline;
}
</style>
