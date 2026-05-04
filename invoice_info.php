<?php
/**
 * Trang hiển thị thông tin đơn hàng từ QR code - KHÔNG CẦN ĐĂNG NHẬP
 * URL: invoice_info.php?qr=INV_1774285800_6193&id=6
 */

// Bắt đầu session
session_start();

// Bắt lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Lấy thông tin từ URL
$qrCode = $_GET['qr'] ?? '';
$invoiceId = $_GET['id'] ?? '';

if (empty($qrCode) && empty($invoiceId)) {
    http_response_code(404);
    die('❌ Thông tin đơn hàng không hợp lệ');
}

try {
    // Kết nối database
    $pdo = new PDO("mysql:host=localhost;dbname=qlnt_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tìm đơn hàng theo QR code hoặc ID
    $sql = "SELECT i.*, u.full_name as staff_name
            FROM invoices i
            LEFT JOIN users u ON i.user_id = u.user_id
            WHERE i.qr_code = ? OR i.invoice_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$qrCode, $invoiceId]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$invoice) {
        // Tạo thông tin mặc định nếu không tìm thấy
        $invoice = [
            'invoice_id' => $invoiceId ?: 0,
            'invoice_number' => $qrCode ?: 'Không xác định',
            'staff_name' => 'DUO PHARMA',
            'total_amount' => 0,
            'discount' => 0,
            'final_amount' => 0,
            'payment_method' => 'Chưa thanh toán',
            'amount_paid' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'qr_code' => $qrCode
        ];
        $invoiceDetails = [];
    } else {
        // Lấy chi tiết đơn hàng
        $sql = "SELECT id.*, m.medicine_name, m.price as medicine_price
                FROM invoice_details id
                LEFT JOIN medicines m ON id.medicine_id = m.medicine_id
                WHERE id.invoice_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$invoice['invoice_id']]);
        $invoiceDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    // Nếu lỗi database, tạo thông tin mặc định
    error_log("Invoice info error: " . $e->getMessage());
    
    $invoice = [
        'invoice_id' => $invoiceId ?: 0,
        'invoice_number' => $qrCode ?: 'Đơn hàng test',
        'staff_name' => 'DUO PHARMA',
        'total_amount' => 0,
        'discount' => 0,
        'final_amount' => 0,
        'payment_method' => 'Chưa xác định',
        'amount_paid' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'qr_code' => $qrCode
    ];
    $invoiceDetails = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($invoice['invoice_number']); ?> - Thông tin đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
        }
        
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
            border: none;
        }
        
        .invoice-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .modal-body {
            padding: 2rem;
            max-height: 70vh;
            overflow-y: auto;
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
        
        .status-paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .pharmacy-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 1px solid #cbd5e1;
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
        
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(3px);
        }
        
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .close-btn:hover {
            opacity: 1;
            color: white;
        }
        
        .table-custom {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 1rem;
            }
            
            .modal-header {
                padding: 1.5rem;
            }
            
            .invoice-icon {
                font-size: 2.5rem;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Modal hiển thị ngay khi load trang -->
    <div class="modal fade show" id="invoiceModal" tabindex="-1" style="display: block;" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <div class="w-100">
                        <i class="bi bi-receipt invoice-icon"></i>
                        <h1 class="modal-title mb-2"><?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>
                        <p class="mb-0 opacity-90">Thông tin đơn hàng từ nhà thuốc</p>
                    </div>
                    <button type="button" class="close-btn" onclick="window.close()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="modal-body">
                    <!-- Public Access Info -->
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Truy cập công khai:</strong> Bạn đang xem thông tin đơn hàng mà không cần đăng nhập.
                        <br><small class="text-muted">
                            Quét QR code để xem chi tiết đơn hàng
                        </small>
                    </div>
                    
                    <!-- Thông tin cơ bản -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-person me-2"></i>Nhân viên bán hàng
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($invoice['staff_name'] ?? 'DUO PHARMA'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar me-2"></i>Ngày tạo đơn
                                </div>
                                <div class="info-value">
                                    <?php echo $invoice['created_at'] ? date('d/m/Y H:i', strtotime($invoice['created_at'])) : '-'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-currency-dollar me-2"></i>Tổng tiền
                                </div>
                                <div class="info-value">
                                    <?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?>đ
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-percent me-2"></i>Giảm giá
                                </div>
                                <div class="info-value text-danger">
                                    <?php echo number_format($invoice['discount'], 0, ',', '.'); ?>đ
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-cash me-2"></i>Thành tiền
                                </div>
                                <div class="info-value text-success fw-bold fs-5">
                                    <?php echo number_format($invoice['final_amount'], 0, ',', '.'); ?>đ
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-credit-card me-2"></i>Hình thức thanh toán
                                </div>
                                <div class="info-value">
                                    <?php 
                                    $paymentMethods = [
                                        'cash' => 'Tiền mặt',
                                        'bank_transfer' => 'Chuyển khoản',
                                        'card' => 'Thẻ'
                                    ];
                                    echo $paymentMethods[$invoice['payment_method']] ?? 'Chưa thanh toán';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chi tiết đơn hàng -->
                    <?php if (!empty($invoiceDetails)): ?>
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-list-ul me-2"></i>Chi tiết đơn hàng
                        </div>
                        <div class="table-responsive mt-2">
                            <table class="table table-custom table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên thuốc</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1; foreach ($invoiceDetails as $detail): ?>
                                    <tr>
                                        <td><?php echo $stt++; ?></td>
                                        <td><?php echo htmlspecialchars($detail['medicine_name']); ?></td>
                                        <td><?php echo $detail['quantity']; ?></td>
                                        <td><?php echo number_format($detail['unit_price'], 0, ',', '.'); ?>đ</td>
                                        <td><?php echo number_format($detail['subtotal'], 0, ',', '.'); ?>đ</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
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
                                    Quét QR để xem đơn hàng
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- QR Code info -->
                    <div class="qr-info">
                        <small>
                            <i class="bi bi-qr-code me-1"></i>
                            QR Code: <?php echo htmlspecialchars($qrCode ?: $invoice['qr_code']); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Backdrop -->
    <div class="modal-backdrop show"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tự động focus vào modal khi load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('invoiceModal').focus();
        });
        
        // Đóng modal khi nhấn ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.close();
            }
        });
        
        // Đóng modal khi click vào backdrop
        document.querySelector('.modal-backdrop').addEventListener('click', function() {
            window.close();
        });
    </script>
</body>
</html>