<!DOCTYPE html>
<html>
<head>
    <title>Hóa đơn <?php echo $invoice['invoice_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="text-center mb-4">
            <h2>HÓA ĐƠN BÁN HÀNG</h2>
            <p>Số: <?php echo $invoice['invoice_number']; ?></p>
            <p>Ngày: <?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></p>
        </div>
        
        <p><strong>Nhân viên:</strong> <?php echo htmlspecialchars($invoice['staff_name']); ?></p>
        
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên thuốc</th>
                    <th>Đơn vị</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $stt = 1; foreach ($details as $item): ?>
                    <tr>
                        <td><?php echo $stt++; ?></td>
                        <td><?php echo htmlspecialchars($item['medicine_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['unit_name'] ?? ''); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['unit_price']); ?>đ</td>
                        <td><?php echo number_format($item['subtotal']); ?>đ</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end"><strong>Tổng cộng:</strong></td>
                    <td><strong><?php echo number_format($invoice['total_amount']); ?>đ</strong></td>
                </tr>
                <?php if ($invoice['discount'] > 0): ?>
                <tr>
                    <td colspan="5" class="text-end">Giảm giá:</td>
                    <td><?php echo number_format($invoice['discount']); ?>đ</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="5" class="text-end"><strong>Thanh toán:</strong></td>
                    <td><strong><?php echo number_format($invoice['final_amount']); ?>đ</strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> In hóa đơn
            </button>
            <a href="index.php?page=sales" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</body>
</html>
