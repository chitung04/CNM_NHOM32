<?php
/**
 * Debug: Kiểm tra QR code trong medicines
 */

require_once 'config/database.php';
require_once 'models/Medicine.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<meta charset='UTF-8'>";
echo "<title>Debug Medicine QR Codes</title>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .card { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #007bff; color: white; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    img { border: 1px solid #ddd; }
</style>";
echo "</head><body>";

echo "<h1>🔍 Debug Medicine QR Codes</h1>";

try {
    $medicineModel = new Medicine();
    $medicines = $medicineModel->getAll();
    
    echo "<div class='card'>";
    echo "<h2>Tổng số thuốc: " . count($medicines) . "</h2>";
    echo "</div>";
    
    echo "<div class='card'>";
    echo "<h3>Chi tiết từng thuốc:</h3>";
    echo "<table>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Tên thuốc</th>";
    echo "<th>QR Code (DB)</th>";
    echo "<th>File QR</th>";
    echo "<th>Preview</th>";
    echo "<th>Trạng thái</th>";
    echo "</tr>";
    
    $hasQR = 0;
    $noQR = 0;
    $hasFile = 0;
    $noFile = 0;
    
    foreach ($medicines as $medicine) {
        echo "<tr>";
        echo "<td>" . $medicine['medicine_id'] . "</td>";
        echo "<td><strong>" . htmlspecialchars($medicine['medicine_name']) . "</strong></td>";
        
        // QR Code trong DB
        if (!empty($medicine['qr_code'])) {
            echo "<td class='success'>" . htmlspecialchars($medicine['qr_code']) . "</td>";
            $hasQR++;
            
            // Kiểm tra file
            $qrPath = 'assets/qrcodes/' . $medicine['qr_code'] . '.png';
            if (file_exists($qrPath)) {
                echo "<td class='success'>✅ Có file</td>";
                echo "<td><img src='" . $qrPath . "' width='50' height='50'></td>";
                echo "<td class='success'>✅ OK</td>";
                $hasFile++;
            } else {
                echo "<td class='error'>❌ Không có file</td>";
                echo "<td>-</td>";
                echo "<td class='error'>❌ Thiếu file QR</td>";
                $noFile++;
            }
        } else {
            echo "<td class='error'>NULL</td>";
            echo "<td>-</td>";
            echo "<td>-</td>";
            echo "<td class='error'>❌ Chưa có QR code</td>";
            $noQR++;
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
    // Thống kê
    echo "<div class='card'>";
    echo "<h3>📊 Thống kê:</h3>";
    echo "<ul>";
    echo "<li><strong>Có QR code trong DB:</strong> <span class='success'>" . $hasQR . "</span></li>";
    echo "<li><strong>Không có QR code:</strong> <span class='error'>" . $noQR . "</span></li>";
    echo "<li><strong>Có file QR:</strong> <span class='success'>" . $hasFile . "</span></li>";
    echo "<li><strong>Thiếu file QR:</strong> <span class='error'>" . $noFile . "</span></li>";
    echo "</ul>";
    echo "</div>";
    
    // Hướng dẫn fix
    if ($noQR > 0 || $noFile > 0) {
        echo "<div class='card' style='background: #fff3cd; border: 2px solid #ffc107;'>";
        echo "<h3>⚠️ Cần sửa:</h3>";
        
        if ($noQR > 0) {
            echo "<p><strong>Có " . $noQR . " thuốc chưa có QR code trong database.</strong></p>";
            echo "<p>Chạy script sau để tạo QR code:</p>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
            echo "php create_medicine_qr_codes.php";
            echo "</pre>";
        }
        
        if ($noFile > 0) {
            echo "<p><strong>Có " . $noFile . " QR code thiếu file hình ảnh.</strong></p>";
            echo "<p>Chạy script sau để tạo lại file QR:</p>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
            echo "php fix_medicine_qr_codes.php";
            echo "</pre>";
        }
        
        echo "</div>";
    } else {
        echo "<div class='card' style='background: #d4edda; border: 2px solid #28a745;'>";
        echo "<h3>✅ Tất cả QR code đều OK!</h3>";
        echo "<p>Tất cả thuốc đều có QR code và file hình ảnh.</p>";
        echo "</div>";
    }
    
    // Link quay lại
    echo "<div class='card'>";
    echo "<a href='index.php?page=medicines' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>";
    echo "← Quay lại trang tra cứu thuốc";
    echo "</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='card' style='background: #f8d7da; border: 2px solid #dc3545;'>";
    echo "<h3>❌ Lỗi:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>
