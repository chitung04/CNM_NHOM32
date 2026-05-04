<?php
/**
 * Test xem thuốc có hiển thị trên trang bán hàng không
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

echo "<h2>🧪 Test thuốc trên trang bán hàng</h2>";
echo "<hr>";

$db = Database::getInstance();

try {
    // Query giống như trong SalesController
    $sql = "SELECT m.medicine_id, m.medicine_name, m.price, m.qr_code,
            c.category_name, u.unit_name,
            COALESCE(SUM(b.quantity), 0) as total_stock
            FROM medicines m
            LEFT JOIN categories c ON m.category_id = c.category_id
            LEFT JOIN units u ON m.unit_id = u.unit_id
            LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
            WHERE m.pharmacy_id = ?
            GROUP BY m.medicine_id
            HAVING total_stock > 0
            ORDER BY m.medicine_name ASC";
    
    $medicines = $db->query($sql, [$pharmacyId])->fetchAll();
    
    echo "<h3>✅ Kết quả:</h3>";
    echo "<p>Tìm thấy <strong>" . count($medicines) . "</strong> thuốc CÓ TỒN KHO (sẽ hiển thị trên trang bán hàng)</p>";
    
    if (count($medicines) > 0) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4>📋 Danh sách thuốc sẽ hiển thị:</h4>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>STT</th><th>Tên thuốc</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>QR Code</th>";
        echo "</tr>";
        
        $stt = 1;
        foreach ($medicines as $med) {
            echo "<tr>";
            echo "<td>{$stt}</td>";
            echo "<td><strong>{$med['medicine_name']}</strong></td>";
            echo "<td>{$med['category_name']}</td>";
            echo "<td>" . number_format($med['price']) . " VNĐ</td>";
            echo "<td style='color: green; font-weight: bold;'>{$med['total_stock']}</td>";
            echo "<td>{$med['qr_code']}</td>";
            echo "</tr>";
            $stt++;
        }
        echo "</table>";
        echo "</div>";
        
        echo "<div style='background: #cfe2ff; padding: 20px; border-radius: 5px;'>";
        echo "<h4>🎉 HOÀN HẢO!</h4>";
        echo "<p>Tất cả <strong>" . count($medicines) . "</strong> thuốc này sẽ tự động hiển thị trên trang bán hàng.</p>";
        echo "<p><strong>Bước tiếp theo:</strong></p>";
        echo "<ol>";
        echo "<li><a href='index.php?page=sales' style='font-size: 16px; font-weight: bold;'>→ Vào trang bán hàng</a></li>";
        echo "<li>Refresh trang (Ctrl + F5) nếu chưa thấy thuốc mới</li>";
        echo "<li>Click vào modal \"Tạo đơn hàng\" để chọn thuốc</li>";
        echo "</ol>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px;'>";
        echo "<h4>⚠️ KHÔNG CÓ THUỐC NÀO!</h4>";
        echo "<p><strong>Nguyên nhân:</strong> Tất cả thuốc đều hết hàng hoặc chưa có lô thuốc.</p>";
        echo "<p><strong>Giải pháp:</strong></p>";
        echo "<ol>";
        echo "<li>Chạy các lệnh SQL thêm thuốc và lô thuốc ở trên</li>";
        echo "<li>Hoặc import file <strong>ULTIMATE_DATABASE_FINAL.sql</strong></li>";
        echo "</ol>";
        echo "</div>";
    }
    
    // Kiểm tra tổng số thuốc
    echo "<hr>";
    echo "<h3>📊 Thống kê:</h3>";
    
    $totalMedicines = $db->query(
        "SELECT COUNT(*) as count FROM medicines WHERE pharmacy_id = ?",
        [$pharmacyId]
    )->fetch();
    
    $totalBatches = $db->query(
        "SELECT COUNT(*) as count FROM batches WHERE pharmacy_id = ? AND status = 'active'",
        [$pharmacyId]
    )->fetch();
    
    echo "<ul>";
    echo "<li>Tổng số thuốc: <strong>{$totalMedicines['count']}</strong></li>";
    echo "<li>Tổng số lô thuốc active: <strong>{$totalBatches['count']}</strong></li>";
    echo "<li>Thuốc có tồn kho: <strong>" . count($medicines) . "</strong></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ LỖI:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "</div>";
}
