<?php
/**
 * Script xác minh tính năng tự động tạo dữ liệu
 * Kiểm tra pharmacy mới nhất có đầy đủ dữ liệu không
 */

require_once 'config/database.php';
require_once 'models/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'>";
echo "<title>Xác minh tự động tạo dữ liệu</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; background: #f5f5f5; }
.card { background: white; border-radius: 8px; padding: 20px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
.error { background: #f8d7da; border-left: 4px solid #dc3545; color: #721c24; }
.warning { background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; }
.info { background: #d1ecf1; border-left: 4px solid #17a2b8; color: #0c5460; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #f8f9fa; font-weight: bold; }
tr:hover { background: #f8f9fa; }
.badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
.badge-success { background: #28a745; color: white; }
.badge-danger { background: #dc3545; color: white; }
.badge-warning { background: #ffc107; color: #000; }
h1 { color: #333; }
h2 { color: #555; margin-top: 30px; }
.stat { font-size: 24px; font-weight: bold; color: #007bff; }
</style></head><body>";

echo "<h1>🔍 Xác minh tự động tạo dữ liệu</h1>";
echo "<p style='color: #666;'>Kiểm tra pharmacy mới nhất có được tạo dữ liệu tự động không</p>";

// Lấy pharmacy mới nhất
$latestPharmacy = $db->query("
    SELECT * FROM pharmacies 
    ORDER BY pharmacy_id DESC 
    LIMIT 1
")->fetch();

if (!$latestPharmacy) {
    echo "<div class='card error'>";
    echo "<h3>❌ Không tìm thấy pharmacy nào</h3>";
    echo "<p>Hệ thống chưa có pharmacy nào. Vui lòng đăng ký pharmacy mới.</p>";
    echo "</div>";
    exit;
}

$pharmacyId = $latestPharmacy['pharmacy_id'];
$pharmacyName = $latestPharmacy['pharmacy_name'];

echo "<div class='card info'>";
echo "<h2>🏥 Pharmacy mới nhất</h2>";
echo "<table>";
echo "<tr><th>ID</th><td class='stat'>$pharmacyId</td></tr>";
echo "<tr><th>Tên</th><td><strong>$pharmacyName</strong></td></tr>";
echo "<tr><th>Mã</th><td>{$latestPharmacy['pharmacy_code']}</td></tr>";
echo "<tr><th>Địa chỉ</th><td>{$latestPharmacy['address']}</td></tr>";
echo "<tr><th>Ngày tạo</th><td>{$latestPharmacy['created_at']}</td></tr>";
echo "</table>";
echo "</div>";

// Kiểm tra dữ liệu
$checks = [
    'categories' => ['name' => 'Danh mục', 'expected' => 8],
    'units' => ['name' => 'Đơn vị tính', 'expected' => 8],
    'suppliers' => ['name' => 'Nhà cung cấp', 'expected' => 3],
    'medicines' => ['name' => 'Thuốc', 'expected' => 10],
    'batches' => ['name' => 'Lô thuốc', 'expected' => 20],
    'users' => ['name' => 'Người dùng', 'expected' => 1]
];

$allPassed = true;

echo "<div class='card'>";
echo "<h2>📊 Kiểm tra dữ liệu</h2>";
echo "<table>";
echo "<tr><th>Loại dữ liệu</th><th>Số lượng</th><th>Mong đợi</th><th>Trạng thái</th></tr>";

foreach ($checks as $table => $info) {
    $count = $db->query("SELECT COUNT(*) as count FROM $table WHERE pharmacy_id = ?", [$pharmacyId])->fetch()['count'];
    $expected = $info['expected'];
    $passed = $count >= $expected;
    
    if (!$passed) $allPassed = false;
    
    $badge = $passed ? "<span class='badge badge-success'>✓ PASS</span>" : "<span class='badge badge-danger'>✗ FAIL</span>";
    $countStyle = $passed ? "color: green; font-weight: bold;" : "color: red; font-weight: bold;";
    
    echo "<tr>";
    echo "<td>{$info['name']}</td>";
    echo "<td style='$countStyle'>$count</td>";
    echo "<td>≥ $expected</td>";
    echo "<td>$badge</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// Kết quả tổng quan
if ($allPassed) {
    echo "<div class='card success'>";
    echo "<h2>✅ THÀNH CÔNG!</h2>";
    echo "<p style='font-size: 18px;'>Tính năng tự động tạo dữ liệu hoạt động <strong>HOÀN HẢO</strong>!</p>";
    echo "<p>Pharmacy <strong>$pharmacyName</strong> đã được tạo với đầy đủ dữ liệu mẫu.</p>";
    echo "</div>";
} else {
    echo "<div class='card error'>";
    echo "<h2>❌ THẤT BẠI!</h2>";
    echo "<p style='font-size: 18px;'>Một số dữ liệu chưa được tạo đầy đủ.</p>";
    echo "<p>Vui lòng kiểm tra lại code trong <code>controllers/AuthController.php</code></p>";
    echo "</div>";
}

// Chi tiết thuốc
echo "<div class='card'>";
echo "<h2>💊 Danh sách thuốc</h2>";
$medicines = $db->query("
    SELECT m.*, c.category_name, u.unit_name 
    FROM medicines m
    LEFT JOIN categories c ON m.category_id = c.category_id
    LEFT JOIN units u ON m.unit_id = u.unit_id
    WHERE m.pharmacy_id = ?
    ORDER BY m.medicine_id
", [$pharmacyId])->fetchAll();

if (count($medicines) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Tên thuốc</th><th>Danh mục</th><th>Đơn vị</th><th>Giá</th><th>QR Code</th></tr>";
    foreach ($medicines as $med) {
        echo "<tr>";
        echo "<td>{$med['medicine_id']}</td>";
        echo "<td><strong>{$med['medicine_name']}</strong></td>";
        echo "<td>{$med['category_name']}</td>";
        echo "<td>{$med['unit_name']}</td>";
        echo "<td>" . number_format($med['price']) . "đ</td>";
        echo "<td><code>{$med['qr_code']}</code></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ Không có thuốc nào</p>";
}
echo "</div>";

// Chi tiết lô thuốc
echo "<div class='card'>";
echo "<h2>📦 Danh sách lô thuốc</h2>";
$batches = $db->query("
    SELECT b.*, m.medicine_name, s.supplier_name
    FROM batches b
    LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
    LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
    WHERE b.pharmacy_id = ?
    ORDER BY b.batch_id
    LIMIT 10
", [$pharmacyId])->fetchAll();

if (count($batches) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Thuốc</th><th>Số lô</th><th>Số lượng</th><th>HSD</th><th>NCC</th><th>QR Code</th></tr>";
    foreach ($batches as $batch) {
        echo "<tr>";
        echo "<td>{$batch['batch_id']}</td>";
        echo "<td>{$batch['medicine_name']}</td>";
        echo "<td><code>{$batch['batch_number']}</code></td>";
        echo "<td><strong>{$batch['quantity']}</strong></td>";
        echo "<td>{$batch['expiry_date']}</td>";
        echo "<td>{$batch['supplier_name']}</td>";
        echo "<td><code>{$batch['qr_code']}</code></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $totalBatches = $db->query("SELECT COUNT(*) as count FROM batches WHERE pharmacy_id = ?", [$pharmacyId])->fetch()['count'];
    if ($totalBatches > 10) {
        echo "<p style='color: #666;'><em>Hiển thị 10/{$totalBatches} lô đầu tiên</em></p>";
    }
} else {
    echo "<p class='warning'>⚠️ Không có lô thuốc nào</p>";
}
echo "</div>";

// Hướng dẫn tiếp theo
echo "<div class='card info'>";
echo "<h2>🚀 Bước tiếp theo</h2>";
echo "<ol style='font-size: 16px;'>";
echo "<li>Đăng nhập vào hệ thống với tài khoản admin của pharmacy này</li>";
echo "<li>Vào trang <strong>Bán hàng</strong> để xem danh sách thuốc</li>";
echo "<li>Thử thêm thuốc vào giỏ hàng và tạo hóa đơn</li>";
echo "<li>Kiểm tra các chức năng khác: Quản lý thuốc, Quản lý lô, Báo cáo</li>";
echo "</ol>";
echo "<p><a href='index.php?page=auth&action=login' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;'>🔐 Đăng nhập ngay</a></p>";
echo "</div>";

// Thống kê toàn hệ thống
echo "<div class='card'>";
echo "<h2>📈 Thống kê toàn hệ thống</h2>";
$totalPharmacies = $db->query("SELECT COUNT(*) as count FROM pharmacies")->fetch()['count'];
$totalMedicines = $db->query("SELECT COUNT(*) as count FROM medicines")->fetch()['count'];
$totalBatches = $db->query("SELECT COUNT(*) as count FROM batches")->fetch()['count'];
$totalUsers = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];

echo "<table>";
echo "<tr><th>Tổng số nhà thuốc</th><td class='stat'>$totalPharmacies</td></tr>";
echo "<tr><th>Tổng số thuốc</th><td class='stat'>$totalMedicines</td></tr>";
echo "<tr><th>Tổng số lô</th><td class='stat'>$totalBatches</td></tr>";
echo "<tr><th>Tổng số người dùng</th><td class='stat'>$totalUsers</td></tr>";
echo "</table>";
echo "</div>";

echo "<div style='text-align: center; padding: 20px; color: #999;'>";
echo "<p>Ngày kiểm tra: " . date('d/m/Y H:i:s') . "</p>";
echo "<p>Sẵn sàng cho presentation: <strong style='color: #28a745;'>05/05/2026 ✅</strong></p>";
echo "</div>";

echo "</body></html>";
?>
