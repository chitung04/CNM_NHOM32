<?php
/**
 * Debug QR Codes - Kiểm tra tình trạng QR codes
 */

require_once 'config/database.php';

echo "<h2>🔍 Debug QR Codes</h2>";

try {
    $db = Database::getInstance();
    
    // Kiểm tra medicines có QR code
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📊 Thống kê QR Codes:</h4>";
    
    $sql1 = "SELECT COUNT(*) as total FROM medicines";
    $stmt1 = $db->query($sql1);
    $totalMedicines = $stmt1->fetch()['total'];
    
    $sql2 = "SELECT COUNT(*) as total FROM medicines WHERE qr_code IS NOT NULL AND qr_code != ''";
    $stmt2 = $db->query($sql2);
    $medicinesWithQR = $stmt2->fetch()['total'];
    
    $sql3 = "SELECT COUNT(*) as total FROM batches WHERE qr_code IS NOT NULL AND qr_code != ''";
    $stmt3 = $db->query($sql3);
    $batchesWithQR = $stmt3->fetch()['total'];
    
    echo "<ul>";
    echo "<li><strong>Tổng medicines:</strong> {$totalMedicines}</li>";
    echo "<li><strong>Medicines có QR:</strong> {$medicinesWithQR}</li>";
    echo "<li><strong>Batches có QR:</strong> {$batchesWithQR}</li>";
    echo "</ul>";
    echo "</div>";
    
    // Hiển thị medicines và QR codes
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📋 Danh sách Medicines và QR Codes:</h4>";
    
    $sql4 = "SELECT m.medicine_id, m.medicine_name, m.qr_code as medicine_qr,
                    (SELECT b.qr_code FROM batches b WHERE b.medicine_id = m.medicine_id AND b.qr_code IS NOT NULL LIMIT 1) as batch_qr
             FROM medicines m 
             ORDER BY m.medicine_id ASC 
             LIMIT 10";
    $stmt4 = $db->query($sql4);
    $medicines = $stmt4->fetchAll();
    
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>ID</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Tên thuốc</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>QR từ medicines</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>QR từ batches</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>File QR</th>";
    echo "</tr>";
    
    foreach ($medicines as $med) {
        $qrCode = $med['medicine_qr'] ?: $med['batch_qr'];
        $qrFile = $qrCode ? "assets/qrcodes/{$qrCode}.png" : '';
        $fileExists = $qrFile && file_exists($qrFile);
        
        echo "<tr>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$med['medicine_id']}</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($med['medicine_name']) . "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . ($med['medicine_qr'] ?: '<span style="color: red;">Không có</span>') . "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . ($med['batch_qr'] ?: '<span style="color: red;">Không có</span>') . "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>";
        
        if ($qrCode) {
            if ($fileExists) {
                echo "<span style='color: green;'>✅ {$qrFile}</span>";
            } else {
                echo "<span style='color: red;'>❌ File không tồn tại</span>";
            }
        } else {
            echo "<span style='color: gray;'>Không có QR</span>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
    // Kiểm tra files QR trong thư mục
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>📁 Files QR trong thư mục assets/qrcodes:</h4>";
    
    $qrDir = 'assets/qrcodes/';
    if (is_dir($qrDir)) {
        $files = scandir($qrDir);
        $qrFiles = array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'png';
        });
        
        echo "<p><strong>Tổng files QR:</strong> " . count($qrFiles) . "</p>";
        
        if (count($qrFiles) > 0) {
            echo "<div style='max-height: 200px; overflow-y: auto;'>";
            foreach (array_slice($qrFiles, 0, 20) as $file) {
                $fileSize = filesize($qrDir . $file);
                echo "<div style='margin: 5px 0;'>";
                echo "<strong>{$file}</strong> ({$fileSize} bytes)";
                echo "</div>";
            }
            if (count($qrFiles) > 20) {
                echo "<p><em>... và " . (count($qrFiles) - 20) . " files khác</em></p>";
            }
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>❌ Thư mục assets/qrcodes không tồn tại!</p>";
    }
    echo "</div>";
    
    // Test QR codes
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🧪 Test QR Codes:</h4>";
    
    $testSql = "SELECT m.medicine_id, m.medicine_name, 
                       COALESCE(m.qr_code, 
                                (SELECT b.qr_code FROM batches b WHERE b.medicine_id = m.medicine_id AND b.qr_code IS NOT NULL LIMIT 1)
                       ) as qr_code
                FROM medicines m 
                WHERE COALESCE(m.qr_code, 
                               (SELECT b.qr_code FROM batches b WHERE b.medicine_id = m.medicine_id AND b.qr_code IS NOT NULL LIMIT 1)
                      ) IS NOT NULL
                LIMIT 5";
    $testStmt = $db->query($testSql);
    $testMedicines = $testStmt->fetchAll();
    
    if (count($testMedicines) > 0) {
        foreach ($testMedicines as $med) {
            $testUrl = 'medicine_info.php?medicine_id=' . $med['medicine_id'] . '&qr=' . $med['qr_code'];
            
            echo "<div style='margin: 10px 0; padding: 10px; background: white; border-radius: 5px;'>";
            echo "<strong>{$med['medicine_name']}</strong> (QR: {$med['qr_code']})<br>";
            echo "<a href='{$testUrl}' target='_blank' style='color: #007bff;'>{$testUrl}</a>";
            
            // Hiển thị QR image nếu có
            $qrImagePath = "assets/qrcodes/{$med['qr_code']}.png";
            if (file_exists($qrImagePath)) {
                echo "<br><img src='{$qrImagePath}' alt='QR Code' style='width: 100px; height: 100px; margin-top: 10px;'>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>❌ Không có medicine nào có QR code để test!</p>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='fix_medicine_qr_codes.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>🔧 Sửa QR Codes</a>";
echo "<a href='create_medicine_qr_codes.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>➕ Tạo QR Codes</a>";
echo "<a href='index.php?page=medicines' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;'>📋 Tra cứu thuốc</a>";
echo "</div>";
?>