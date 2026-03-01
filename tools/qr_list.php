<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách QR Codes - Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { color: #333; margin-bottom: 30px; }
        .section { margin-bottom: 40px; }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .qr-code {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
        }
        .qr-code:hover {
            background: #667eea;
            color: white;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #764ba2;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .stat-box {
            flex: 1;
            min-width: 150px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Danh sách QR Codes để Test</h1>
        
        <?php
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/../config/config.php';
        require_once __DIR__ . '/../models/Database.php';
        
        try {
            $db = Database::getInstance();
            
            // Thống kê
            $medicineCount = $db->query("SELECT COUNT(*) as count FROM medicines")->fetch()['count'];
            $batchCount = $db->query("SELECT COUNT(*) as count FROM batches")->fetch()['count'];
            $categoryCount = $db->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'];
            $supplierCount = $db->query("SELECT COUNT(*) as count FROM suppliers")->fetch()['count'];
            ?>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $medicineCount; ?></div>
                    <div class="stat-label">Medicines</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $batchCount; ?></div>
                    <div class="stat-label">Batches</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $categoryCount; ?></div>
                    <div class="stat-label">Categories</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $supplierCount; ?></div>
                    <div class="stat-label">Suppliers</div>
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <a href="test_scan_qr.html" class="btn">🔍 Test Scan QR</a>
                <a href="view_qrcodes.php" class="btn">📱 Xem QR Images</a>
            </div>
            
            <!-- MEDICINES -->
            <div class="section">
                <h2>💊 Medicines (<?php echo $medicineCount; ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên thuốc</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>QR Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $medicines = $db->query("
                            SELECT m.medicine_id, m.medicine_name, m.price, m.qr_code, c.category_name
                            FROM medicines m
                            LEFT JOIN categories c ON m.category_id = c.category_id
                            ORDER BY m.medicine_id
                        ")->fetchAll();
                        
                        $stt = 1;
                        foreach ($medicines as $med) {
                            echo "<tr>";
                            echo "<td>" . $stt++ . "</td>";
                            echo "<td>" . htmlspecialchars($med['medicine_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($med['category_name'] ?? 'N/A') . "</td>";
                            echo "<td>" . number_format($med['price']) . " VNĐ</td>";
                            echo "<td><span class='qr-code' onclick='testQR(\"" . $med['qr_code'] . "\")'>" . $med['qr_code'] . "</span></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- BATCHES -->
            <div class="section">
                <h2>📦 Batches (<?php echo $batchCount; ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thuốc</th>
                            <th>Số lượng</th>
                            <th>Hạn sử dụng</th>
                            <th>Nhà cung cấp</th>
                            <th>QR Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $batches = $db->query("
                            SELECT b.batch_id, b.quantity, b.expiry_date, b.qr_code,
                                   m.medicine_name, s.supplier_name
                            FROM batches b
                            LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
                            LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
                            ORDER BY b.batch_id
                        ")->fetchAll();
                        
                        $stt = 1;
                        foreach ($batches as $batch) {
                            $expiryDate = new DateTime($batch['expiry_date']);
                            $now = new DateTime();
                            $daysUntilExpiry = $now->diff($expiryDate)->days;
                            $isExpiringSoon = $daysUntilExpiry < 30;
                            
                            echo "<tr" . ($isExpiringSoon ? " style='background: #fff3cd;'" : "") . ">";
                            echo "<td>" . $stt++ . "</td>";
                            echo "<td>" . htmlspecialchars($batch['medicine_name']) . "</td>";
                            echo "<td>" . $batch['quantity'] . "</td>";
                            echo "<td>" . $batch['expiry_date'] . ($isExpiringSoon ? " ⚠️" : "") . "</td>";
                            echo "<td>" . htmlspecialchars($batch['supplier_name'] ?? 'N/A') . "</td>";
                            echo "<td><span class='qr-code' onclick='testQR(\"" . $batch['qr_code'] . "\")'>" . $batch['qr_code'] . "</span></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <?php
        } catch (Exception $e) {
            echo "<div style='color: red; padding: 20px; background: #f8d7da; border-radius: 5px;'>";
            echo "Lỗi: " . $e->getMessage();
            echo "</div>";
        }
        ?>
    </div>
    
    <script>
        function testQR(qrCode) {
            window.open('test_scan_qr.html?qr=' + qrCode, '_blank');
        }
        
        // Copy QR code on click
        document.querySelectorAll('.qr-code').forEach(el => {
            el.title = 'Click để test scan QR này';
        });
    </script>
</body>
</html>
