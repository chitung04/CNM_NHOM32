<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin thuốc - Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            background: #ffffff;
            color: #333;
            min-height: 100vh;
        }
        .container {
            background: rgba(255,255,255,0.95);
            color: #333;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            margin: 0 auto;
        }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .qr-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🏥 DUO PHARMA - Thông tin thuốc</h2>
        
        <?php
        // Lấy QR code từ URL
        $qrCode = $_GET['qr'] ?? '';
        
        if (empty($qrCode)) {
            echo '<p class="error">❌ Không có mã QR trong URL</p>';
            echo '<p>URL phải có dạng: medicine_info_simple.php?qr=BATCH_1735000101_2001</p>';
        } else {
            echo '<p class="success">✅ QR Code nhận được thành công!</p>';
            
            // Thông tin thuốc giả lập
            $medicines = [
                'BATCH_1735000101_2001' => [
                    'name' => 'Amoxicillin 500mg',
                    'category' => 'Kháng sinh',
                    'price' => '3,500 VNĐ',
                    'quantity' => 479,
                    'expiry' => '31/12/2025'
                ],
                'BATCH_1735000102_2002' => [
                    'name' => 'Paracetamol 500mg', 
                    'category' => 'Giảm đau, hạ sốt',
                    'price' => '2,000 VNĐ',
                    'quantity' => 175,
                    'expiry' => '30/11/2025'
                ],
                'TEST' => [
                    'name' => 'Thuốc Test',
                    'category' => 'Test Category', 
                    'price' => '1,000 VNĐ',
                    'quantity' => 100,
                    'expiry' => '01/01/2026'
                ]
            ];
            
            if (isset($medicines[$qrCode])) {
                $medicine = $medicines[$qrCode];
                echo '<div class="qr-info">';
                echo '<h3>💊 ' . $medicine['name'] . '</h3>';
                echo '<p><strong>Danh mục:</strong> ' . $medicine['category'] . '</p>';
                echo '<p><strong>Giá bán:</strong> ' . $medicine['price'] . '</p>';
                echo '<p><strong>Tồn kho:</strong> ' . $medicine['quantity'] . ' viên</p>';
                echo '<p><strong>Hạn sử dụng:</strong> ' . $medicine['expiry'] . '</p>';
                echo '</div>';
                
                echo '<p class="success">🎉 QR Code hoạt động hoàn hảo!</p>';
            } else {
                echo '<div class="qr-info">';
                echo '<h3>💊 Thông tin thuốc không xác định</h3>';
                echo '<p><strong>Mã QR:</strong> ' . htmlspecialchars($qrCode) . '</p>';
                echo '<p>Đây là QR code hợp lệ nhưng không có trong danh sách test.</p>';
                echo '</div>';
                
                echo '<p class="success">✅ QR Code vẫn hoạt động!</p>';
            }
        }
        ?>
        
        <hr>
        <h3>🔍 Thông tin debug:</h3>
        <p><strong>QR Code:</strong> <?php echo htmlspecialchars($qrCode); ?></p>
        <p><strong>URL đầy đủ:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></p>
        <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        
        <hr>
        <p><a href="index.php" style="color: #007bff;">🏠 Về trang chủ</a></p>
        <p><a href="simple_qr_test.html" style="color: #007bff;">🧪 Về trang test QR</a></p>
    </div>
</body>
</html>