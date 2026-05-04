<?php
/**
 * Script kiểm tra đăng ký pharmacy mới có tự động tạo dữ liệu không
 */

require_once 'config/database.php';
require_once 'models/Database.php';

echo "<h2>🧪 Test: Đăng ký Pharmacy mới</h2>";
echo "<hr>";

$db = Database::getInstance();

// Lấy danh sách pharmacies hiện tại
echo "<h3>📋 Danh sách Pharmacies hiện tại:</h3>";
$stmt = $db->query("SELECT pharmacy_id, pharmacy_name, pharmacy_code, created_at FROM pharmacies ORDER BY pharmacy_id");
$pharmacies = $stmt->fetchAll();

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>ID</th><th>Tên nhà thuốc</th><th>Mã</th><th>Ngày tạo</th><th>Thuốc</th><th>Lô</th><th>NCC</th></tr>";

foreach ($pharmacies as $pharmacy) {
    $pid = $pharmacy['pharmacy_id'];
    
    // Đếm số lượng dữ liệu
    $medicineCount = $db->query("SELECT COUNT(*) as count FROM medicines WHERE pharmacy_id = ?", [$pid])->fetch()['count'];
    $batchCount = $db->query("SELECT COUNT(*) as count FROM batches WHERE pharmacy_id = ?", [$pid])->fetch()['count'];
    $supplierCount = $db->query("SELECT COUNT(*) as count FROM suppliers WHERE pharmacy_id = ?", [$pid])->fetch()['count'];
    
    echo "<tr>";
    echo "<td>{$pid}</td>";
    echo "<td>{$pharmacy['pharmacy_name']}</td>";
    echo "<td>{$pharmacy['pharmacy_code']}</td>";
    echo "<td>{$pharmacy['created_at']}</td>";
    echo "<td style='text-align: center;'><strong>{$medicineCount}</strong></td>";
    echo "<td style='text-align: center;'><strong>{$batchCount}</strong></td>";
    echo "<td style='text-align: center;'><strong>{$supplierCount}</strong></td>";
    echo "</tr>";
}

echo "</table>";
echo "<hr>";

// Hướng dẫn test
echo "<div style='background: #e8f4f8; border: 2px solid #0066cc; padding: 20px; margin: 20px 0;'>";
echo "<h3>📝 Hướng dẫn test:</h3>";
echo "<ol style='font-size: 16px;'>";
echo "<li>Mở trang đăng ký: <a href='index.php?page=auth&action=register' target='_blank' style='color: blue; font-weight: bold;'>index.php?page=auth&action=register</a></li>";
echo "<li>Điền thông tin đăng ký pharmacy mới (ví dụ: <strong>Nhà thuốc Test</strong>)</li>";
echo "<li>Sau khi đăng ký thành công, quay lại trang này và <strong>refresh</strong></li>";
echo "<li>Kiểm tra pharmacy mới có <strong>10 thuốc</strong>, <strong>20 lô</strong>, và <strong>3 nhà cung cấp</strong> không</li>";
echo "<li>Đăng nhập bằng tài khoản mới tạo và vào trang <strong>Bán hàng</strong> để xem thuốc</li>";
echo "</ol>";
echo "</div>";

// Thống kê tổng quan
echo "<div style='background: #f0f8f0; border: 2px solid #00aa00; padding: 20px; margin: 20px 0;'>";
echo "<h3>📊 Thống kê hệ thống:</h3>";

$totalPharmacies = count($pharmacies);
$totalMedicines = $db->query("SELECT COUNT(*) as count FROM medicines")->fetch()['count'];
$totalBatches = $db->query("SELECT COUNT(*) as count FROM batches")->fetch()['count'];
$totalSuppliers = $db->query("SELECT COUNT(*) as count FROM suppliers")->fetch()['count'];
$totalUsers = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];

echo "<ul style='font-size: 18px;'>";
echo "<li>🏥 Tổng số nhà thuốc: <strong>$totalPharmacies</strong></li>";
echo "<li>💊 Tổng số thuốc: <strong>$totalMedicines</strong></li>";
echo "<li>📦 Tổng số lô: <strong>$totalBatches</strong></li>";
echo "<li>🏢 Tổng số nhà cung cấp: <strong>$totalSuppliers</strong></li>";
echo "<li>👥 Tổng số người dùng: <strong>$totalUsers</strong></li>";
echo "</ul>";
echo "</div>";

// Kiểm tra pharmacy mới nhất
echo "<h3>🆕 Pharmacy mới nhất:</h3>";
$latestPharmacy = $db->query("SELECT * FROM pharmacies ORDER BY pharmacy_id DESC LIMIT 1")->fetch();

if ($latestPharmacy) {
    $pid = $latestPharmacy['pharmacy_id'];
    
    echo "<div style='background: #fffacd; border: 2px solid #ffa500; padding: 20px; margin: 20px 0;'>";
    echo "<h4>📍 {$latestPharmacy['pharmacy_name']} (ID: {$pid})</h4>";
    echo "<p><strong>Mã:</strong> {$latestPharmacy['pharmacy_code']}</p>";
    echo "<p><strong>Địa chỉ:</strong> {$latestPharmacy['address']}</p>";
    echo "<p><strong>Ngày tạo:</strong> {$latestPharmacy['created_at']}</p>";
    
    // Kiểm tra dữ liệu
    $medicines = $db->query("SELECT * FROM medicines WHERE pharmacy_id = ?", [$pid])->fetchAll();
    $batches = $db->query("SELECT * FROM batches WHERE pharmacy_id = ?", [$pid])->fetchAll();
    $suppliers = $db->query("SELECT * FROM suppliers WHERE pharmacy_id = ?", [$pid])->fetchAll();
    $users = $db->query("SELECT * FROM users WHERE pharmacy_id = ?", [$pid])->fetchAll();
    
    echo "<h4>📊 Dữ liệu tự động:</h4>";
    echo "<ul>";
    echo "<li>✅ Thuốc: <strong>" . count($medicines) . "</strong> loại</li>";
    echo "<li>✅ Lô thuốc: <strong>" . count($batches) . "</strong> lô</li>";
    echo "<li>✅ Nhà cung cấp: <strong>" . count($suppliers) . "</strong> NCC</li>";
    echo "<li>✅ Người dùng: <strong>" . count($users) . "</strong> user</li>";
    echo "</ul>";
    
    if (count($medicines) >= 10 && count($batches) >= 20 && count($suppliers) >= 3) {
        echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 15px; margin: 10px 0;'>";
        echo "<h3 style='color: #155724; margin: 0;'>✅ PASS: Dữ liệu tự động đã được tạo thành công!</h3>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 15px; margin: 10px 0;'>";
        echo "<h3 style='color: #721c24; margin: 0;'>❌ FAIL: Dữ liệu tự động chưa đầy đủ!</h3>";
        echo "<p>Cần: 10 thuốc, 20 lô, 3 NCC</p>";
        echo "</div>";
    }
    
    // Hiển thị danh sách thuốc
    if (count($medicines) > 0) {
        echo "<h4>💊 Danh sách thuốc:</h4>";
        echo "<ol>";
        foreach ($medicines as $med) {
            echo "<li>{$med['medicine_name']} - {$med['price']}đ</li>";
        }
        echo "</ol>";
    }
    
    echo "</div>";
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}
table {
    width: 100%;
    margin: 20px 0;
}
th {
    font-weight: bold;
}
a {
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
