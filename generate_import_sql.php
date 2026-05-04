<?php
/**
 * Script tạo file SQL import lô thuốc tự động
 * Dựa trên thuốc có sẵn trong pharmacy
 */

session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['pharmacy_id'])) {
    die("Vui lòng đăng nhập trước!");
}

require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'models/Medicine.php';

$pharmacyId = $_SESSION['pharmacy_id'];
$pharmacyName = $_SESSION['pharmacy_name'] ?? 'Pharmacy';

echo "<h2>🔧 Tạo file SQL import lô thuốc</h2>";
echo "<p>Pharmacy ID: <strong>$pharmacyId</strong></p>";
echo "<hr>";

// Lấy danh sách thuốc của pharmacy
$medicineModel = new Medicine();
$medicines = $medicineModel->getAll();

if (count($medicines) == 0) {
    echo "<div style='color: red; padding: 20px; background: #ffeeee;'>";
    echo "<h3>❌ Không có thuốc nào!</h3>";
    echo "<p>Vui lòng thêm thuốc trước khi tạo file import.</p>";
    echo "<a href='index.php?page=medicines' class='btn'>Thêm thuốc</a>";
    echo "</div>";
    exit;
}

echo "<p>Tìm thấy <strong>" . count($medicines) . "</strong> loại thuốc</p>";
echo "<hr>";

// Tạo file SQL
$filename = "import_batches_pharmacy_{$pharmacyId}.sql";
$sqlContent = "-- Import lô thuốc cho Pharmacy ID: $pharmacyId\n";
$sqlContent .= "-- Tạo ngày: " . date('Y-m-d H:i:s') . "\n";
$sqlContent .= "-- Tổng số thuốc: " . count($medicines) . "\n\n";

$sqlContent .= "INSERT INTO batches (medicine_id, batch_number, quantity, import_date, expiry_date, supplier_id, status, pharmacy_id, qr_code, created_at) VALUES\n";

$values = [];
$batchCounter = 1;

foreach ($medicines as $medicine) {
    $medicineId = $medicine['medicine_id'];
    $timestamp = time() + $batchCounter;
    
    // Tạo 2 lô cho mỗi thuốc
    for ($i = 1; $i <= 2; $i++) {
        $batchNumber = "BATCH_" . $pharmacyId . "_" . str_pad($batchCounter, 4, '0', STR_PAD_LEFT);
        $quantity = rand(100, 500);
        $importDate = date('Y-m-d');
        $expiryDate = date('Y-m-d', strtotime('+' . rand(12, 24) . ' months'));
        $supplierId = rand(1, 2);
        $qrCode = "BATCH_{$timestamp}_{$batchCounter}";
        
        $values[] = "($medicineId, '$batchNumber', $quantity, '$importDate', '$expiryDate', $supplierId, 'active', $pharmacyId, '$qrCode', NOW())";
        
        $batchCounter++;
    }
}

$sqlContent .= implode(",\n", $values) . ";";

// Lưu file
file_put_contents($filename, $sqlContent);

echo "<div style='color: green; padding: 20px; background: #eeffee; border: 1px solid green;'>";
echo "<h3>✅ Tạo file thành công!</h3>";
echo "<p><strong>File:</strong> $filename</p>";
echo "<p><strong>Số lô:</strong> " . count($values) . " lô (" . count($medicines) . " thuốc x 2 lô)</p>";
echo "<p><a href='$filename' download class='btn' style='background: green; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📥 Tải file SQL</a></p>";
echo "</div>";

echo "<hr>";
echo "<h3>📋 Hướng dẫn sử dụng:</h3>";
echo "<ol>";
echo "<li>Tải file SQL ở trên</li>";
echo "<li>Vào: <a href='index.php?page=batches&action=import_sql'>Trang Import SQL</a></li>";
echo "<li>Upload file vừa tải</li>";
echo "<li>Click 'Upload và Import'</li>";
echo "<li>✅ Hoàn tất!</li>";
echo "</ol>";

echo "<hr>";
echo "<h3>📊 Xem trước 5 dòng đầu:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
$lines = explode("\n", $sqlContent);
for ($i = 0; $i < min(10, count($lines)); $i++) {
    echo htmlspecialchars($lines[$i]) . "\n";
}
echo "...</pre>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin: 5px;
}
.btn:hover {
    background: #0056b3;
}
</style>
