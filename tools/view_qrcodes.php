<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes - Pharmacy Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
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
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2em;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .stat-value {
            color: #333;
            font-size: 1.8em;
            font-weight: bold;
        }
        
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .qr-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 2px solid #e9ecef;
        }
        
        .qr-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .qr-card img {
            width: 100%;
            max-width: 200px;
            height: auto;
            border-radius: 8px;
            background: white;
            padding: 10px;
        }
        
        .qr-filename {
            margin-top: 15px;
            font-weight: bold;
            color: #333;
            word-break: break-all;
        }
        
        .qr-size {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .qr-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8em;
            margin-top: 10px;
            font-weight: bold;
        }
        
        .type-medicine {
            background: #d4edda;
            color: #155724;
        }
        
        .type-batch {
            background: #fff3cd;
            color: #856404;
        }
        
        .type-invoice {
            background: #cce5ff;
            color: #004085;
        }
        
        .type-test {
            background: #f8d7da;
            color: #721c24;
        }
        
        .no-qr {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-qr-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📱 QR Codes</h1>
        <p class="subtitle">Hệ thống quản lý bán thuốc</p>
        
        <?php
        define('BASE_PATH', dirname(__DIR__));
        define('QRCODE_PATH', BASE_PATH . '/assets/qrcodes');
        
        $qrcodes = [];
        if (file_exists(QRCODE_PATH)) {
            $files = glob(QRCODE_PATH . '/*.png');
            foreach ($files as $file) {
                $filename = basename($file);
                $filesize = filesize($file);
                
                // Determine type
                $type = 'test';
                if (strpos($filename, 'MED_') === 0) {
                    $type = 'medicine';
                } elseif (strpos($filename, 'BATCH_') === 0) {
                    $type = 'batch';
                } elseif (strpos($filename, 'INV_') === 0) {
                    $type = 'invoice';
                }
                
                $qrcodes[] = [
                    'filename' => $filename,
                    'path' => str_replace('\\', '/', $file),
                    'size' => $filesize,
                    'type' => $type
                ];
            }
        }
        
        $totalSize = array_sum(array_column($qrcodes, 'size'));
        ?>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-label">Tổng số QR Codes</div>
                <div class="stat-value"><?php echo count($qrcodes); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Tổng dung lượng</div>
                <div class="stat-value"><?php echo number_format($totalSize / 1024, 2); ?> KB</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Thư mục</div>
                <div class="stat-value" style="font-size: 0.9em;">assets/qrcodes</div>
            </div>
        </div>
        
        <?php if (empty($qrcodes)): ?>
            <div class="no-qr">
                <div class="no-qr-icon">📭</div>
                <h2>Chưa có QR code nào</h2>
                <p>Chạy script test_qrcode.php để tạo QR code mẫu</p>
                <a href="#" class="btn" onclick="alert('Chạy: php tools/test_qrcode.php'); return false;">Hướng dẫn</a>
            </div>
        <?php else: ?>
            <div class="qr-grid">
                <?php foreach ($qrcodes as $qr): ?>
                    <div class="qr-card">
                        <img src="../assets/qrcodes/<?php echo htmlspecialchars($qr['filename']); ?>" 
                             alt="<?php echo htmlspecialchars($qr['filename']); ?>">
                        <div class="qr-filename"><?php echo htmlspecialchars($qr['filename']); ?></div>
                        <div class="qr-size"><?php echo number_format($qr['size']); ?> bytes</div>
                        <span class="qr-type type-<?php echo $qr['type']; ?>">
                            <?php 
                            $typeLabels = [
                                'medicine' => 'Thuốc',
                                'batch' => 'Lô thuốc',
                                'invoice' => 'Hóa đơn',
                                'test' => 'Test'
                            ];
                            echo $typeLabels[$qr['type']];
                            ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
