<?php
session_start();

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'helpers/pharmacy.php';

$db = Database::getInstance();

echo "<h2>🐛 Debug Notifications</h2>";
echo "<hr>";

// 1. Kiểm tra session
echo "<h3>1. Session Info:</h3>";
echo "<pre>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NULL') . "\n";
echo "pharmacy_id: " . ($_SESSION['pharmacy_id'] ?? 'NULL') . "\n";
echo "username: " . ($_SESSION['username'] ?? 'NULL') . "\n";
echo "</pre>";

// 2. Kiểm tra pharmacy_id
$pharmacyId = getCurrentPharmacyId();
echo "<h3>2. getCurrentPharmacyId():</h3>";
echo "<p><strong>" . ($pharmacyId ?? 'NULL') . "</strong></p>";

if (!$pharmacyId) {
    echo "<p style='color: red;'>❌ KHÔNG CÓ pharmacy_id! Đây là nguyên nhân.</p>";
    exit;
}

// 3. Kiểm tra thuốc sắp hết hàng
echo "<h3>3. Thuốc sắp hết hàng (< 50):</h3>";
$sqlLowStock = "SELECT m.medicine_id, m.medicine_name, u.unit_name,
                COALESCE(SUM(b.quantity), 0) as total_stock
                FROM medicines m
                LEFT JOIN batches b ON m.medicine_id = b.medicine_id AND b.status = 'active'
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE m.pharmacy_id = ?
                GROUP BY m.medicine_id, m.medicine_name, u.unit_name
                HAVING total_stock < 50
                ORDER BY total_stock ASC";

$stmtLowStock = $db->query($sqlLowStock, [$pharmacyId]);
$lowStockMedicines = $stmtLowStock->fetchAll();

echo "<p>Tìm thấy: <strong>" . count($lowStockMedicines) . "</strong> thuốc</p>";

if (count($lowStockMedicines) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Tên thuốc</th><th>Tồn kho</th><th>Đơn vị</th></tr>";
    foreach ($lowStockMedicines as $med) {
        echo "<tr>";
        echo "<td>{$med['medicine_id']}</td>";
        echo "<td>{$med['medicine_name']}</td>";
        echo "<td style='color: red; font-weight: bold;'>{$med['total_stock']}</td>";
        echo "<td>{$med['unit_name']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Kiểm tra lô sắp hết hạn
echo "<h3>4. Lô thuốc sắp hết hạn (< 60 ngày):</h3>";
$sqlExpiring = "SELECT b.batch_id, b.expiry_date, m.medicine_name, b.batch_number,
                DATEDIFF(b.expiry_date, CURDATE()) as days_left
                FROM batches b
                JOIN medicines m ON b.medicine_id = m.medicine_id
                WHERE b.pharmacy_id = ?
                AND b.status = 'active' 
                AND DATEDIFF(b.expiry_date, CURDATE()) <= 60
                AND DATEDIFF(b.expiry_date, CURDATE()) > 0
                ORDER BY days_left ASC";

$stmtExpiring = $db->query($sqlExpiring, [$pharmacyId]);
$expiringBatches = $stmtExpiring->fetchAll();

echo "<p>Tìm thấy: <strong>" . count($expiringBatches) . "</strong> lô</p>";

if (count($expiringBatches) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Batch ID</th><th>Tên thuốc</th><th>Số lô</th><th>HSD</th><th>Còn lại</th></tr>";
    foreach ($expiringBatches as $batch) {
        $expiryDate = date('d/m/Y', strtotime($batch['expiry_date']));
        echo "<tr>";
        echo "<td>{$batch['batch_id']}</td>";
        echo "<td>{$batch['medicine_name']}</td>";
        echo "<td>{$batch['batch_number']}</td>";
        echo "<td>$expiryDate</td>";
        echo "<td style='color: orange; font-weight: bold;'>{$batch['days_left']} ngày</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 5. Tổng kết
echo "<hr>";
echo "<h3>📊 Tổng kết:</h3>";
$total = count($lowStockMedicines) + count($expiringBatches);
echo "<p style='font-size: 20px;'>Tổng số thông báo: <strong style='color: blue;'>$total</strong></p>";

if ($total == 0) {
    echo "<p style='color: green;'>✅ Không có thuốc nào sắp hết hàng hoặc hết hạn!</p>";
    echo "<p>Đây là lý do tại sao ajax/get_notifications.php trả về count: 0</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
table { margin: 20px 0; width: 100%; }
th { background: #f0f0f0; text-align: left; }
</style>
