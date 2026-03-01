<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #<?php echo htmlspecialchars($invoice['invoice_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .invoice-info {
            margin-bottom: 20px;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> In hóa đơn
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Đóng
            </button>
        </div>
        
        <div class="invoice-header">
            <h2>HỆ THỐNG QUẢN LÝ NHÀ THUỐC</h2>
            <h3>HÓA ĐƠN BÁN HÀNG</h3>
            <p>Số: <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
        </div>
        
        <div class="row invoice-info">
            <div class="col-6">
                <p><strong>Nhân viên:</strong> <?php echo htmlspecialchars($invoice['staff_name']); ?></p>
                <p><strong>Ngày:</strong> <?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></p>
            </div>
            <div class="col-6 text-end">
                <?php if (!empty($invoice['qr_code'])): ?>
                    <img src="assets/qrcodes/<?php echo htmlspecialchars($invoice['qr_code']); ?>.png" 
                         alt="QR Code" style="width: 100px; height: 100px;">
                <?php endif; ?>
            </div>
        </div>
        
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th width="5%">STT</th>
                    <th width="40%">Tên thuốc</th>
                    <th width="10%">ĐVT</th>
                    <th width="10%">SL</th>
                    <th width="15%">Đơn giá</th>
                    <th width="20%">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                foreach ($details as $item): 
                ?>
                    <tr>
                        <td class="text-center"><?php echo $stt++; ?></td>
                        <td><?php echo htmlspecialchars($item['medicine_name']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($item['unit_name'] ?? ''); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-end"><?php echo number_format($item['unit_price']); ?>đ</td>
                        <td class="text-end"><?php echo number_format($item['subtotal']); ?>đ</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end"><strong>Tổng tiền:</strong></td>
                    <td class="text-end"><strong><?php echo number_format($invoice['total_amount']); ?>đ</strong></td>
                </tr>
                <?php if ($invoice['discount'] > 0): ?>
                <tr>
                    <td colspan="5" class="text-end"><strong>Giảm giá:</strong></td>
                    <td class="text-end"><strong>-<?php echo number_format($invoice['discount']); ?>đ</strong></td>
                </tr>
                <?php endif; ?>
                <tr class="table-success">
                    <td colspan="5" class="text-end"><strong>THÀNH TIỀN:</strong></td>
                    <td class="text-end"><strong><?php echo number_format($invoice['final_amount']); ?>đ</strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="mt-4">
            <p><em>Cảm ơn quý khách đã mua hàng!</em></p>
        </div>
        
        <div class="row mt-5">
            <div class="col-6 text-center">
                <p><strong>Người mua hàng</strong></p>
                <p style="margin-top: 80px;">(Ký, ghi rõ họ tên)</p>
            </div>
            <div class="col-6 text-center">
                <p><strong>Nhân viên bán hàng</strong></p>
                <p style="margin-top: 80px;"><?php echo htmlspecialchars($invoice['staff_name']); ?></p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            // Uncomment to auto-print
            // window.print();
        }
    </script>
</body>
</html>
