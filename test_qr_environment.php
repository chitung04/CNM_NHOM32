<?php
/**
 * Test QR Code Environment
 * Kiểm tra môi trường và khả năng hoạt động của QR code
 */

require_once 'helpers/url_helper.php';

$isLocalhost = isLocalhost();
$canQRWork = canQRCodeWork();
$baseUrl = getBaseUrl();
$mobileUrl = getMobileAccessibleUrl();
$serverIP = getServerIP();
$host = $_SERVER['HTTP_HOST'] ?? 'Unknown';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test QR Code Environment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .status-badge {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
        }
        .qr-sample {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><i class="bi bi-qr-code me-2"></i>Test QR Code Environment</h2>
            </div>
            <div class="card-body">
                <!-- Trạng thái tổng quan -->
                <div class="text-center mb-4">
                    <?php if ($canQRWork): ?>
                        <span class="status-badge bg-success text-white">
                            <i class="bi bi-check-circle me-2"></i>QR Code CÓ THỂ HOẠT ĐỘNG
                        </span>
                    <?php else: ?>
                        <span class="status-badge bg-danger text-white">
                            <i class="bi bi-x-circle me-2"></i>QR Code KHÔNG THỂ HOẠT ĐỘNG
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Thông tin chi tiết -->
                <h4 class="mt-4"><i class="bi bi-info-circle me-2"></i>Thông tin môi trường:</h4>
                
                <div class="info-box">
                    <strong>Hostname hiện tại:</strong>
                    <div class="mt-2">
                        <code style="font-size: 1.1rem; background: white; padding: 5px 10px; border-radius: 5px;">
                            <?php echo htmlspecialchars($host); ?>
                        </code>
                    </div>
                </div>

                <div class="info-box">
                    <strong>Đang chạy trên localhost?</strong>
                    <div class="mt-2">
                        <?php if ($isLocalhost): ?>
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="bi bi-exclamation-triangle me-1"></i>CÓ - Đang dùng localhost
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-check-circle me-1"></i>KHÔNG - Đang dùng IP/Domain thực
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-box">
                    <strong>Base URL:</strong>
                    <div class="mt-2">
                        <code style="font-size: 1.1rem; background: white; padding: 5px 10px; border-radius: 5px;">
                            <?php echo htmlspecialchars($baseUrl); ?>
                        </code>
                    </div>
                </div>

                <div class="info-box">
                    <strong>Server IP:</strong>
                    <div class="mt-2">
                        <code style="font-size: 1.1rem; background: white; padding: 5px 10px; border-radius: 5px;">
                            <?php echo htmlspecialchars($serverIP); ?>
                        </code>
                    </div>
                </div>

                <div class="info-box">
                    <strong>URL có thể truy cập từ mobile:</strong>
                    <div class="mt-2">
                        <?php if ($mobileUrl): ?>
                            <code style="font-size: 1.1rem; background: white; padding: 5px 10px; border-radius: 5px;">
                                <?php echo htmlspecialchars($mobileUrl); ?>
                            </code>
                        <?php else: ?>
                            <span class="text-danger">
                                <i class="bi bi-x-circle me-1"></i>Không có URL truy cập được từ mobile
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hướng dẫn -->
                <div class="alert alert-info mt-4">
                    <h5><i class="bi bi-lightbulb me-2"></i>Hướng dẫn:</h5>
                    <?php if ($isLocalhost): ?>
                        <p><strong>Để QR code hoạt động, bạn cần:</strong></p>
                        <ol>
                            <li>Tìm IP thực của máy tính (ví dụ: 192.168.1.100)</li>
                            <li>Truy cập qua IP thay vì localhost:
                                <?php if ($serverIP && $serverIP !== '127.0.0.1'): ?>
                                    <br><code>http://<?php echo $serverIP; ?>/CNM_NHOM32/</code>
                                <?php endif; ?>
                            </li>
                            <li>Đảm bảo điện thoại và máy tính cùng mạng WiFi</li>
                            <li>Quét QR code bằng điện thoại</li>
                        </ol>
                        
                        <p class="mb-0"><strong>Cách tìm IP máy tính:</strong></p>
                        <ul class="mb-0">
                            <li><strong>Windows:</strong> Mở CMD và gõ <code>ipconfig</code></li>
                            <li><strong>Mac/Linux:</strong> Mở Terminal và gõ <code>ifconfig</code></li>
                        </ul>
                    <?php else: ?>
                        <p class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Môi trường của bạn đã sẵn sàng! QR code có thể hoạt động bình thường.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Test QR Code -->
                <?php if ($mobileUrl): ?>
                    <div class="qr-sample mt-4">
                        <h5><i class="bi bi-qr-code me-2"></i>Test QR Code</h5>
                        <p class="text-muted">Quét QR code này bằng điện thoại để test</p>
                        <?php
                        // Tạo QR code test
                        require_once 'helpers/qrcode.php';
                        $testUrl = $mobileUrl . 'test_qr_environment.php';
                        $testQR = 'TEST_' . time();
                        
                        try {
                            generateQRCode($testUrl, $testQR);
                            $qrPath = 'assets/qrcodes/' . $testQR . '.png';
                            if (file_exists($qrPath)) {
                                echo '<img src="' . $qrPath . '" alt="Test QR" style="max-width: 200px; border: 2px solid #ddd; border-radius: 10px;">';
                                echo '<p class="mt-2"><small>URL: ' . htmlspecialchars($testUrl) . '</small></p>';
                            }
                        } catch (Exception $e) {
                            echo '<p class="text-danger">Không thể tạo QR code test: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Nút quay lại -->
                <div class="text-center mt-4">
                    <a href="index.php?page=medicines" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại trang tra cứu thuốc
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
