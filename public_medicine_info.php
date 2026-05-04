<?php
/**
 * Trang thông tin thuốc công khai - không cần đăng nhập
 * URL: public_medicine_info.php?qr=BATCH_1735000101_2001&token=abc123
 */

// Bắt đầu session
session_start();

// Include helper
require_once 'helpers/public_access.php';

// Bắt lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lấy QR code từ URL
$qrCode = $_GET['qr'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($qrCode)) {
    http_response_code(404);
    die('❌ QR Code không hợp lệ');
}

// Kiểm tra public access token
$tokenData = validatePublicAccessToken($token);
if (!$tokenData) {
    // Tạo token mới nếu chưa có
    $token = generatePublicAccessToken($qrCode);
    $tokenData = validatePublicAccessToken($token);
}

// Cho phép truy cập công khai
allowPublicAccess();

try {
    // Kết nối database đơn giản
    $pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tìm batch theo QR code
    $sql = "SELECT b.*, m.medicine_name, m.price, m.description, c.category_name, u.unit_name, s.supplier_name
            FROM batches b
            LEFT JOIN medicines m ON b.medicine_id = m.medicine_id
            LEFT JOIN categories c ON m.category_id = c.category_id
            LEFT JOIN units u ON m.unit_id = u.unit_id
            LEFT JOIN suppliers s ON b.supplier_id = s.supplier_id
            WHERE b.qr_code = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$qrCode]);
    $batch = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$batch) {
        // Nếu không tìm thấy trong batches, thử tìm trong medicines
        $sql = "SELECT m.*, c.category_name, u.unit_name, 
                       0 as quantity, 0 as batch_id, '' as batch_number,
                       CURDATE() as import_date, DATE_ADD(CURDATE(), INTERVAL 2 YEAR) as expiry_date,
                       'active' as status, NOW() as created_at, '' as supplier_name
                FROM medicines m
                LEFT JOIN categories c ON m.category_id = c.category_id
                LEFT JOIN units u ON m.unit_id = u.unit_id
                WHERE m.qr_code = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$qrCode]);
        $batch = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$batch) {
            // Tạo thông tin mặc định nếu không tìm thấy
            $batch = [
                'batch_id' => 0,
                'medicine_name' => 'Thông tin thuốc',
                'category_name' => 'Chưa phân loại',
                'unit_name' => 'Viên',
                'price' => 0,
                'quantity' => 0,
                'batch_number' => $qrCode,
                'import_date' => date('Y-m-d'),
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
                'status' => 'active',
                'supplier_name' => 'DUO PHARMA',
                'description' => 'Thông tin chi tiết sẽ được cập nhật sau.'
            ];
        }
    }
    
    // Tạo Google Search URL
    $googleSearchUrl = 'https://www.google.com/search?q=' . urlencode($batch['medicine_name'] . ' thuốc thông tin');
    
} catch (Exception $e) {
    // Nếu lỗi database, tạo thông tin mặc định
    error_log("Medicine info error: " . $e->getMessage());
    
    $batch = [
        'batch_id' => 0,
        'medicine_name' => 'Thông tin thuốc - ' . $qrCode,
        'category_name' => 'Dược phẩm',
        'unit_name' => 'Viên',
        'price' => 0,
        'quantity' => 0,
        'batch_number' => $qrCode,
        'import_date' => date('Y-m-d'),
        'expiry_date' => date('Y-m-d', strtotime('+2 years')),
        'status' => 'active',
        'supplier_name' => 'DUO PHARMA',
        'description' => 'Vui lòng liên hệ nhà thuốc để biết thêm thông tin chi tiết.'
    ];
}

// Dọn dẹp tokens hết hạn
cleanupExpiredTokens();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($batch['medicine_name']); ?> - DUO PHARMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        
        .medicine-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .medicine-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .medicine-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .medicine-body {
            padding: 2rem;
        }
        
        .info-row {
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #1f2937;
            font-size: 1rem;
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .status-expired {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .status-low {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .google-btn {
            background: linear-gradient(135deg, #4285f4 0%, #34a853 50%, #fbbc05 75%, #ea4335 100%);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .google-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(66, 133, 244, 0.3);
            color: white;
        }
        
        .pharmacy-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid #cbd5e1;
        }
        
        .public-access-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 1px solid #3b82f6;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .qr-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 1rem;
        }
        
        .expiry-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .expiry-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #ef4444;
        }
        
        @media (max-width: 768px) {
            .medicine-card {
                margin: 0 1rem;
            }
            
            .medicine-header {
                padding: 1.5rem;
            }
            
            .medicine-icon {
                font-size: 2.5rem;
            }
            
            .medicine-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="medicine-card">
            <!-- Header -->
            <div class="medicine-header">
                <i class="bi bi-capsule medicine-icon"></i>
                <h1 class="mb-2"><?php echo htmlspecialchars($batch['medicine_name']); ?></h1>
                <p class="mb-0 opacity-90">Thông tin chi tiết từ nhà thuốc</p>
            </div>
            
            <!-- Content -->
            <div class="medicine-body">
                <!-- Public Access Info -->
                <div class="public-access-info">
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>Truy cập công khai:</strong> Bạn đang xem thông tin thuốc mà không cần đăng nhập. 
                    Link này có hiệu lực trong 1 giờ.
                    <br><small class="text-muted mt-1 d-block">
                        Token: <?php echo substr($token, 0, 8); ?>... | 
                        Hết hạn: <?php echo date('H:i d/m/Y', $tokenData['expires_at']); ?>
                    </small>
                </div>
                
                <!-- Thông tin cơ bản -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-tag me-2"></i>Danh mục
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($batch['category_name'] ?? 'Chưa phân loại'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-rulers me-2"></i>Đơn vị
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($batch['unit_name'] ?? 'Viên'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-currency-dollar me-2"></i>Giá bán
                            </div>
                            <div class="info-value text-success fw-bold">
                                <?php echo number_format($batch['price'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-boxes me-2"></i>Tồn kho
                            </div>
                            <div class="info-value">
                                <span class="fw-bold"><?php echo $batch['quantity']; ?></span>
                                <?php echo htmlspecialchars($batch['unit_name'] ?? 'viên'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Thông tin lô -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-hash me-2"></i>Số lô
                            </div>
                            <div class="info-value fw-bold">
                                <?php echo htmlspecialchars($batch['batch_number']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-calendar-check me-2"></i>Hạn sử dụng
                            </div>
                            <div class="info-value">
                                <?php 
                                $expiryDate = new DateTime($batch['expiry_date']);
                                $today = new DateTime();
                                $daysLeft = $today->diff($expiryDate)->days;
                                $isExpired = $expiryDate < $today;
                                
                                echo $expiryDate->format('d/m/Y');
                                
                                if ($isExpired) {
                                    echo ' <span class="text-danger fw-bold">(Đã hết hạn)</span>';
                                } elseif ($daysLeft <= 30) {
                                    echo ' <span class="text-warning fw-bold">(' . $daysLeft . ' ngày nữa)</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-calendar-plus me-2"></i>Ngày nhập
                            </div>
                            <div class="info-value">
                                <?php echo $batch['import_date'] ? date('d/m/Y', strtotime($batch['import_date'])) : '-'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-check-circle me-2"></i>Trạng thái
                            </div>
                            <div class="info-value">
                                <?php
                                $statusClass = 'status-active';
                                $statusText = 'Còn hàng';
                                
                                if ($batch['status'] === 'expired' || $isExpired) {
                                    $statusClass = 'status-expired';
                                    $statusText = 'Hết hạn';
                                } elseif ($batch['status'] === 'sold_out' || $batch['quantity'] == 0) {
                                    $statusClass = 'status-expired';
                                    $statusText = 'Hết hàng';
                                } elseif ($batch['quantity'] <= 10) {
                                    $statusClass = 'status-low';
                                    $statusText = 'Sắp hết';
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($batch['supplier_name'])): ?>
                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-building me-2"></i>Nhà cung cấp
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($batch['supplier_name']); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($batch['description'])): ?>
                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-info-circle me-2"></i>Mô tả
                    </div>
                    <div class="info-value">
                        <?php echo nl2br(htmlspecialchars($batch['description'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Cảnh báo hết hạn -->
                <?php if ($isExpired): ?>
                <div class="expiry-danger">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                    <strong>Cảnh báo:</strong> Thuốc này đã hết hạn sử dụng. Không nên sử dụng.
                </div>
                <?php elseif ($daysLeft <= 30): ?>
                <div class="expiry-warning">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    <strong>Lưu ý:</strong> Thuốc này sắp hết hạn trong <?php echo $daysLeft; ?> ngày. Vui lòng sử dụng sớm.
                </div>
                <?php endif; ?>
                
                <!-- Nút tìm kiếm Google -->
                <div class="text-center mt-4">
                    <a href="<?php echo htmlspecialchars($googleSearchUrl); ?>" 
                       target="_blank" 
                       class="google-btn">
                        <i class="bi bi-google me-2"></i>
                        Tìm hiểu thêm trên Google
                    </a>
                </div>
                
                <!-- Thông tin nhà thuốc -->
                <div class="pharmacy-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2">
                                <i class="bi bi-shop me-2"></i>
                                DUO PHARMA
                            </h6>
                            <p class="mb-1 text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                123 Đường ABC, Quận XYZ, TP.HCM
                            </p>
                            <p class="mb-0 text-muted">
                                <i class="bi bi-telephone me-1"></i>
                                (028) 1234 5678
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <small class="text-muted">
                                Quét QR để xem thông tin
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- QR Code info -->
                <div class="qr-info">
                    <small>
                        <i class="bi bi-qr-code me-1"></i>
                        QR Code: <?php echo htmlspecialchars($qrCode); ?> | 
                        <i class="bi bi-clock me-1"></i>
                        Truy cập lúc: <?php echo date('H:i:s d/m/Y'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>