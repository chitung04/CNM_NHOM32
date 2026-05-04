<?php
/**
 * Tool để tạo lại QR code cho tất cả thuốc
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../helpers/qrcode.php';
require_once '../models/Medicine.php';

echo "<h2>🔄 Tạo lại QR Code cho tất cả thuốc</h2>";

try {
    $medicineModel = new Medicine();
    $medicines = $medicineModel->getAll();
    
    echo "<p>Tìm thấy " . count($medicines) . " thuốc cần tạo QR code.</p>";
    
    $success = 0;
    $failed = 0;
    $updated = 0;
    
    foreach ($medicines as $medicine) {
        echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<strong>Thuốc:</strong> " . htmlspecialchars($medicine['medicine_name']) . " (ID: {$medicine['medicine_id']})<br>";
        
        try {
            // Kiểm tra xem đã có QR code chưa
            if (empty($medicine['qr_code'])) {
                // Tạo QR code mới
                $qrCode = generateUniqueQRCode('MED');
                
                // Cập nhật QR code vào database
                $db = Database::getInstance();
                $sql = "UPDATE medicines SET qr_code = ? WHERE medicine_id = ?";
                $db->execute($sql, [$qrCode, $medicine['medicine_id']]);
                
                $medicine['qr_code'] = $qrCode;
                echo "<span style='color: blue;'>✨ Tạo mã QR mới: {$qrCode}</span><br>";
            } else {
                echo "<span style='color: green;'>✅ Đã có mã QR: {$medicine['qr_code']}</span><br>";
            }
            
            // Tạo URL thông tin thuốc
            $baseUrl = rtrim(BASE_URL, '/');
            $infoUrl = $baseUrl . '/medicine_info.php?qr=' . urlencode($medicine['qr_code']) . '&id=' . $medicine['medicine_id'];
            
            // Tạo dữ liệu QR code với thông tin chi tiết
            $qrData = json_encode([
                'type' => 'medicine',
                'id' => $medicine['medicine_id'],
                'name' => $medicine['medicine_name'],
                'price' => $medicine['price'],
                'category' => $medicine['category_name'] ?? 'Chưa phân loại',
                'unit' => $medicine['unit_name'] ?? 'Viên',
                'description' => $medicine['description'] ?? '',
                'qr_code' => $medicine['qr_code'],
                'url' => $infoUrl,
                'created_at' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);
            
            // Tạo file QR code
            $result = generateQRCode($qrData, $medicine['qr_code']);
            
            if ($result) {
                echo "<span style='color: green;'>✅ Tạo file QR thành công: {$result}</span><br>";
                echo "<span style='color: #666;'>🔗 URL: <a href='{$infoUrl}' target='_blank'>{$infoUrl}</a></span><br>";
                echo "<span style='color: #666;'>📄 Dữ liệu QR: " . substr($qrData, 0, 100) . "...</span><br>";
                $success++;
            } else {
                echo "<span style='color: red;'>❌ Lỗi tạo file QR</span><br>";
                $failed++;
            }
            
        } catch (Exception $e) {
            echo "<span style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</span><br>";
            $failed++;
        }
        
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h3>📊 Kết quả:</h3>";
    echo "<ul>";
    echo "<li style='color: green;'>✅ Thành công: {$success} thuốc</li>";
    echo "<li style='color: red;'>❌ Thất bại: {$failed} thuốc</li>";
    echo "<li style='color: blue;'>📁 Thư mục QR: " . QRCODE_PATH . "</li>";
    echo "</ul>";
    
    if ($success > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4 style='color: #155724;'>🎉 Hoàn thành!</h4>";
        echo "<p>Đã tạo thành công QR code cho {$success} thuốc. Bạn có thể:</p>";
        echo "<ul>";
        echo "<li>Xem QR code trong trang quản lý thuốc</li>";
        echo "<li>In QR code để dán lên sản phẩm</li>";
        echo "<li>Quét QR code để xem thông tin thuốc</li>";
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<h4>❌ Lỗi hệ thống:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='../index.php?page=medicines'>← Quay lại trang quản lý thuốc</a></p>";
?>