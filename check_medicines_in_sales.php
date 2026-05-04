<?php
/**
 * Script kiểm tra thuốc hiển thị trên trang bán hàng
 */

session_start();

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/secure_session.php';

if (!isLoggedIn()) {
    die("❌ Vui lòng đăng nhập trước!");
}

require_once 'helpers/pharmacy.php';
$pharmacyId = getCurrentPharmacyId();

echo "<h2>🔍 Kiểm tra thuốc trên trang bán hàng</h2>";
echo "<hr>";

$db = Database::getInstance();

try {
    // 1. Thông tin session
    echo "<h3>1. Session Info:</h3>";
    echo "<ul>";
    echo "<li>user_id: <strong>{$_SESSION['user_id']}</strong></li>";
    echo "<li>pharmacy_id: <strong>{$pharmacyId}</strong></li>";
    echo "<li>username: <strong>{$_SESSION['username']}</strong></li>";
    echo "</ul>";
    
    // 2. Kiểm tra tổng số thuốc trong database
    echo "<h3>2. Tổng số thuốc trong database:</h3>";
    $totalMedicines = $db->query("SELECT COUNT(*) as count FROM medicines")->fetch();
    echo "<p>Tổng số thuốc (tất cả pharmacy): <strong>{$totalMedicines['count']}</strong></p>";
    
    // 3. Kiểm tra thuốc của pharmacy hiện tại
    echo "<h3>3. Thuốc của pharmacy {$pharmacyId}:</h3>";
    $medicines = $db->query(
        "SELECT m.*, c.category_name, u.unit_name,
         COALESCE(SUM(b.quantity), 0) as total_stock
         FROM medicines m
         LEFT JOIN categories c ON m.category_id = c.category_id
         LEFT JOIN units u ON m.unit_id = u.unit_id
         LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
         WHERE m.pharmacy_id = ?
         GROUP BY m.medicine_id
         ORDER BY m.medicine_name ASC",
        [$pharmacyId]
    )->fetchAll();
    
    echo "<p>Tìm thấy: <strong>" . count($medicines) . "</strong> thuốc</p>";
    
    if (count($medicines) > 0) {
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Tên thuốc</th><th>Danh mục</th><th>Đơn vị</th><th>Giá</th><th>Tồn kho</th><th>QR Code</th>";
        echo "</tr>";
        
        foreach ($medicines as $med) {
            $stockColor = $med['total_stock'] > 0 ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$med['medicine_id']}</td>";
            echo "<td><strong>{$med['medicine_name']}</strong></td>";
            echo "<td>{$med['category_name']}</td>";
            echo "<td>{$med['unit_name']}</td>";
            echo "<td>" . number_format($med['price']) . " VNĐ</td>";
            echo "<td style='color: {$stockColor}; font-weight: bold;'>{$med['total_stock']}</td>";
            echo "<td>{$med['qr_code']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; border: 2px solid #ffc107;'>";
        echo "<h3>⚠️ KHÔNG CÓ THUỐC NÀO!</h3>";
        echo "<p><strong>Nguyên nhân:</strong></p>";
        echo "<ul>";
        echo "<li>Bạn chưa import file <strong>ULTIMATE_DATABASE_FINAL.sql</strong></li>";
        echo "<li>Hoặc đang login vào pharmacy khác không có dữ liệu</li>";
        echo "</ul>";
        echo "<p><strong>Giải pháp:</strong></p>";
        echo "<ol>";
        echo "<li>Mở <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
        echo "<li>Chọn database <strong>qlnt_db</strong></li>";
        echo "<li>Click tab <strong>Import</strong></li>";
        echo "<li>Chọn file <strong>ULTIMATE_DATABASE_FINAL.sql</strong></li>";
        echo "<li>Click <strong>Go</strong></li>";
        echo "<li>Đăng nhập lại: <code>admin</code> / <code>123456</code></li>";
        echo "</ol>";
        echo "</div>";
    }
    
    // 4. Kiểm tra query mà trang bán hàng sử dụng
    echo "<h3>4. Test query trang bán hàng:</h3>";
    
    $salesQuery = "SELECT m.medicine_id, m.medicine_name, m.price, u.unit_name,
                   COALESCE(SUM(b.quantity), 0) as total_stock
                   FROM medicines m
                   LEFT JOIN units u ON m.unit_id = u.unit_id
                   LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
                   WHERE m.pharmacy_id = ?
                   GROUP BY m.medicine_id
                   HAVING total_stock > 0
                   ORDER BY m.medicine_name ASC";
    
    $salesMedicines = $db->query($salesQuery, [$pharmacyId])->fetchAll();
    
    echo "<p>Thuốc CÓ TỒN KHO (hiển thị trên trang bán hàng): <strong>" . count($salesMedicines) . "</strong></p>";
    
    if (count($salesMedicines) > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ Các thuốc này sẽ hiển thị trên trang bán hàng:</h4>";
        echo "<ul>";
        foreach ($salesMedicines as $med) {
            echo "<li><strong>{$med['medicine_name']}</strong> - Giá: " . number_format($med['price']) . " VNĐ - Tồn: {$med['total_stock']} {$med['unit_name']}</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ KHÔNG CÓ THUỐC NÀO CÓ TỒN KHO!</h4>";
        echo "<p>Tất cả thuốc đều hết hàng hoặc chưa có lô thuốc nào.</p>";
        echo "</div>";
    }
    
    // 5. Kiểm tra batches
    echo "<h3>5. Kiểm tra lô thuốc:</h3>";
    $batches = $db->query(
        "SELECT COUNT(*) as count FROM batches WHERE pharmacy_id = ?",
        [$pharmacyId]
    )->fetch();
    
    echo "<p>Tổng số lô thuốc: <strong>{$batches['count']}</strong></p>";
    
    if ($batches['count'] == 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
        echo "<h4>⚠️ KHÔNG CÓ LÔ THUỐC NÀO!</h4>";
        echo "<p>Đây là lý do tại sao không có thuốc nào hiển thị trên trang bán hàng.</p>";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h2>📋 Tổng kết:</h2>";
    echo "<ul>";
    echo "<li>Pharmacy ID: <strong>{$pharmacyId}</strong></li>";
    echo "<li>Tổng thuốc: <strong>" . count($medicines) . "</strong></li>";
    echo "<li>Thuốc có tồn kho: <strong>" . count($salesMedicines) . "</strong></li>";
    echo "<li>Tổng lô thuốc: <strong>{$batches['count']}</strong></li>";
    echo "</ul>";
    
    echo "<br>";
    echo "<p><a href='index.php?page=sales' style='font-size: 18px; font-weight: bold;'>→ Đi tới trang bán hàng</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "</div>";
}
