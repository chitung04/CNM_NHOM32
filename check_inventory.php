<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['pharmacy_id'] = 1;

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'models/Medicine.php';

$medicineModel = new Medicine();
$medicines = $medicineModel->getAll();

echo "<h2>🔍 Kiểm tra tồn kho thuốc</h2>";
echo "<p>Pharmacy ID: " . $_SESSION['pharmacy_id'] . "</p>";
echo "<hr>";

echo "<p>Tổng số thuốc: <strong>" . count($medicines) . "</strong></p>";

if (count($medicines) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Tên thuốc</th>";
    echo "<th>Tồn kho</th>";
    echo "<th>Số lô</th>";
    echo "<th>Trạng thái</th>";
    echo "</tr>";
    
    foreach ($medicines as $med) {
        $inventory = $medicineModel->getTotalInventory($med['medicine_id']);
        
        // Đếm số lô
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as count FROM batches WHERE medicine_id = ? AND pharmacy_id = ?", 
                          [$med['medicine_id'], $_SESSION['pharmacy_id']]);
        $batchCount = $stmt->fetch()['count'];
        
        $statusColor = $inventory > 0 ? 'green' : 'red';
        $statusText = $inventory > 0 ? '✅ Có hàng' : '❌ Hết hàng';
        
        echo "<tr>";
        echo "<td>" . $med['medicine_id'] . "</td>";
        echo "<td>" . htmlspecialchars($med['medicine_name']) . "</td>";
        echo "<td><strong>" . $inventory . "</strong></td>";
        echo "<td>" . $batchCount . " lô</td>";
        echo "<td style='color: $statusColor;'><strong>$statusText</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    
    // Thống kê
    $withStock = 0;
    $withoutStock = 0;
    
    foreach ($medicines as $med) {
        $inventory = $medicineModel->getTotalInventory($med['medicine_id']);
        if ($inventory > 0) {
            $withStock++;
        } else {
            $withoutStock++;
        }
    }
    
    echo "<h3>📊 Thống kê:</h3>";
    echo "<ul>";
    echo "<li>✅ Có tồn kho: <strong>$withStock</strong> thuốc</li>";
    echo "<li>❌ Hết hàng: <strong>$withoutStock</strong> thuốc</li>";
    echo "</ul>";
    
    if ($withStock == 0) {
        echo "<div style='background: #ffeeee; border: 1px solid red; padding: 20px; margin: 20px 0;'>";
        echo "<h3 style='color: red;'>⚠️ CẢNH BÁO</h3>";
        echo "<p><strong>Không có thuốc nào có tồn kho!</strong></p>";
        echo "<p>Nguyên nhân có thể:</p>";
        echo "<ul>";
        echo "<li>Chưa có lô thuốc nào</li>";
        echo "<li>Lô thuốc có pharmacy_id khác</li>";
        echo "<li>Số lượng trong lô = 0</li>";
        echo "</ul>";
        echo "<p><strong>Giải pháp:</strong></p>";
        echo "<ol>";
        echo "<li>Vào <a href='index.php?page=batches'>Quản lý lô thuốc</a></li>";
        echo "<li>Kiểm tra có lô nào không</li>";
        echo "<li>Nếu không có, import lô mới</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Không có thuốc nào!</p>";
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
    margin: 20px 0;
}
th {
    background: #f0f0f0;
    text-align: left;
}
</style>
