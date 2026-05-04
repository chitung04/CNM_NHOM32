<?php
/**
 * Script kiểm tra hệ thống QR code
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Kiểm tra hệ thống QR Code</h2>";
    echo "<hr>";
    
    // 1. Kiểm tra QR code trong medicines
    echo "<h3>1. Kiểm tra QR code trong bảng medicines</h3>";
    $stmt = $pdo->query("SELECT medicine_id, medicine_name, qr_code FROM medicines LIMIT 5");
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($medicines)) {
        echo "❌ Không có thuốc nào trong database<br>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Tên thuốc</th><th>QR Code</th><th>Test Link</th></tr>";
        foreach ($medicines as $med) {
            $qrCode = $med['qr_code'] ?? 'NULL';
            $testLink = $qrCode !== 'NULL' ? "<a href='qr.php?c=" . urlencode($qrCode) . "' target='_blank'>Test QR</a>" : '-';
            echo "<tr>";
            echo "<td>" . $med['medicine_id'] . "</td>";
            echo "<td>" . htmlspecialchars($med['medicine_name']) . "</td>";
            echo "<td><code>" . htmlspecialchars($qrCode) . "</code></td>";
            echo "<td>" . $testLink . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    
    // 2. Kiểm tra QR code trong batches
    echo "<h3>2. Kiểm tra QR code trong bảng batches</h3>";
    $stmt = $pdo->query("SELECT batch_id, medicine_id, qr_code, batch_number FROM batches LIMIT 5");
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($batches)) {
        echo "❌ Không có lô hàng nào trong database<br>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Batch ID</th><th>Medicine ID</th><th>Batch Number</th><th>QR Code</th><th>Test Link</th></tr>";
        foreach ($batches as $batch) {
            $qrCode = $batch['qr_code'] ?? 'NULL';
            $testLink = $qrCode !== 'NULL' ? "<a href='qr.php?c=" . urlencode($qrCode) . "' target='_blank'>Test QR</a>" : '-';
            echo "<tr>";
            echo "<td>" . $batch['batch_id'] . "</td>";
            echo "<td>" . $batch['medicine_id'] . "</td>";
            echo "<td>" . htmlspecialchars($batch['batch_number']) . "</td>";
            echo "<td><code>" . htmlspecialchars($qrCode) . "</code></td>";
            echo "<td>" . $testLink . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    
    // 3. Kiểm tra QR code trong invoices
    echo "<h3>3. Kiểm tra QR code trong bảng invoices</h3>";
    $stmt = $pdo->query("SELECT invoice_id, invoice_number, qr_code FROM invoices LIMIT 5");
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($invoices)) {
        echo "❌ Không có hóa đơn nào trong database<br>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Invoice ID</th><th>Invoice Number</th><th>QR Code</th><th>Test Link</th></tr>";
        foreach ($invoices as $inv) {
            $qrCode = $inv['qr_code'] ?? 'NULL';
            $testLink = $qrCode !== 'NULL' ? "<a href='qr.php?c=" . urlencode($qrCode) . "' target='_blank'>Test QR</a>" : '-';
            echo "<tr>";
            echo "<td>" . $inv['invoice_id'] . "</td>";
            echo "<td>" . htmlspecialchars($inv['invoice_number']) . "</td>";
            echo "<td><code>" . htmlspecialchars($qrCode) . "</code></td>";
            echo "<td>" . $testLink . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    
    // 4. Kiểm tra file QR code có tồn tại không
    echo "<h3>4. Kiểm tra file QR code trong thư mục assets/qrcodes</h3>";
    $qrDir = 'assets/qrcodes/';
    
    if (!is_dir($qrDir)) {
        echo "❌ Thư mục $qrDir không tồn tại<br>";
    } else {
        $files = scandir($qrDir);
        $qrFiles = array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'png';
        });
        
        echo "✅ Tìm thấy " . count($qrFiles) . " file QR code<br>";
        
        if (count($qrFiles) > 0) {
            echo "<p>Ví dụ 5 file đầu tiên:</p>";
            echo "<ul>";
            $count = 0;
            foreach ($qrFiles as $file) {
                if ($count >= 5) break;
                echo "<li><code>$file</code></li>";
                $count++;
            }
            echo "</ul>";
        }
    }
    
    echo "<hr>";
    
    // 5. Test qr.php với một mã cụ thể
    echo "<h3>5. Test redirect qr.php</h3>";
    
    // Lấy 1 QR code từ database để test
    $stmt = $pdo->query("SELECT qr_code FROM batches WHERE qr_code IS NOT NULL LIMIT 1");
    $testQR = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testQR) {
        $testCode = $testQR['qr_code'];
        echo "<p>Test với QR code: <code>$testCode</code></p>";
        echo "<p><a href='qr.php?c=" . urlencode($testCode) . "' target='_blank' class='btn'>Click để test redirect</a></p>";
    } else {
        echo "❌ Không tìm thấy QR code nào để test<br>";
    }
    
    echo "<hr>";
    
    // 6. Kiểm tra các file cần thiết
    echo "<h3>6. Kiểm tra các file cần thiết</h3>";
    $requiredFiles = [
        'qr.php' => 'File redirect QR code',
        'medicine_info.php' => 'Trang thông tin thuốc',
        'invoice_info.php' => 'Trang thông tin hóa đơn',
        'helpers/qrcode.php' => 'Helper tạo QR code'
    ];
    
    echo "<ul>";
    foreach ($requiredFiles as $file => $desc) {
        if (file_exists($file)) {
            echo "<li>✅ <strong>$file</strong> - $desc</li>";
        } else {
            echo "<li>❌ <strong>$file</strong> - $desc (KHÔNG TỒN TẠI)</li>";
        }
    }
    echo "</ul>";
    
    echo "<hr>";
    
    // 7. Thống kê
    echo "<h3>7. Thống kê QR code</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN qr_code IS NOT NULL THEN 1 ELSE 0 END) as has_qr FROM medicines");
    $medStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN qr_code IS NOT NULL THEN 1 ELSE 0 END) as has_qr FROM batches");
    $batchStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN qr_code IS NOT NULL THEN 1 ELSE 0 END) as has_qr FROM invoices");
    $invStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Bảng</th><th>Tổng records</th><th>Có QR code</th><th>Không có QR</th><th>%</th></tr>";
    
    echo "<tr>";
    echo "<td>Medicines</td>";
    echo "<td>" . $medStats['total'] . "</td>";
    echo "<td>" . $medStats['has_qr'] . "</td>";
    echo "<td>" . ($medStats['total'] - $medStats['has_qr']) . "</td>";
    echo "<td>" . ($medStats['total'] > 0 ? round($medStats['has_qr'] / $medStats['total'] * 100, 1) : 0) . "%</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td>Batches</td>";
    echo "<td>" . $batchStats['total'] . "</td>";
    echo "<td>" . $batchStats['has_qr'] . "</td>";
    echo "<td>" . ($batchStats['total'] - $batchStats['has_qr']) . "</td>";
    echo "<td>" . ($batchStats['total'] > 0 ? round($batchStats['has_qr'] / $batchStats['total'] * 100, 1) : 0) . "%</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td>Invoices</td>";
    echo "<td>" . $invStats['total'] . "</td>";
    echo "<td>" . $invStats['has_qr'] . "</td>";
    echo "<td>" . ($invStats['total'] - $invStats['has_qr']) . "</td>";
    echo "<td>" . ($invStats['total'] > 0 ? round($invStats['has_qr'] / $invStats['total'] * 100, 1) : 0) . "%</td>";
    echo "</tr>";
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>✅ Kiểm tra hoàn tất!</h3>";
    
    // Đề xuất
    echo "<h4>📋 Đề xuất:</h4>";
    echo "<ul>";
    
    if ($medStats['has_qr'] < $medStats['total']) {
        echo "<li>⚠️ Có " . ($medStats['total'] - $medStats['has_qr']) . " thuốc chưa có QR code. Chạy script tạo QR code.</li>";
    }
    
    if ($batchStats['has_qr'] < $batchStats['total']) {
        echo "<li>⚠️ Có " . ($batchStats['total'] - $batchStats['has_qr']) . " lô hàng chưa có QR code. Chạy script tạo QR code.</li>";
    }
    
    if ($invStats['has_qr'] < $invStats['total']) {
        echo "<li>⚠️ Có " . ($invStats['total'] - $invStats['has_qr']) . " hóa đơn chưa có QR code.</li>";
    }
    
    echo "<li>✅ Test các link QR code ở trên để kiểm tra redirect có hoạt động không.</li>";
    echo "<li>✅ Quét QR code bằng điện thoại để kiểm tra thực tế.</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #ffeeee; border: 1px solid red;'>";
    echo "<h3>❌ Lỗi:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}
h2, h3, h4 {
    color: #333;
}
hr {
    margin: 20px 0;
    border: none;
    border-top: 2px solid #ddd;
}
table {
    width: 100%;
    margin: 20px 0;
}
th {
    background: #f0f0f0;
    text-align: left;
}
code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.btn:hover {
    background: #0056b3;
}
</style>
