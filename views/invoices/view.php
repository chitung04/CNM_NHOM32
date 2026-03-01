<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt me-2"></i>Chi tiết đơn hàng #<?php echo htmlspecialchars($invoice['invoice_number']); ?></h2>
    <div>
        <a href="index.php?page=invoices&action=print&id=<?php echo $invoice['invoice_id']; ?>" 
           class="btn btn-primary" target="_blank">
            <i class="bi bi-printer me-2"></i>In hóa đơn
        </a>
        <a href="index.php?page=invoices" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Thông tin đơn hàng</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Số hóa đơn:</strong></td>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nhân viên:</strong></td>
                        <td><?php echo htmlspecialchars($invoice['staff_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Ngày tạo:</strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tổng tiền:</strong></td>
                        <td><?php echo number_format($invoice['total_amount']); ?>đ</td>
                    </tr>
                    <tr>
                        <td><strong>Giảm giá:</strong></td>
                        <td><?php echo number_format($invoice['discount']); ?>đ</td>
                    </tr>
                    <tr>
                        <td><strong>Thành tiền:</strong></td>
                        <td><h5 class="text-success mb-0"><?php echo number_format($invoice['final_amount']); ?>đ</h5></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Mã QR</h5></div>
            <div class="card-body text-center">
                <?php if (!empty($invoice['qr_code'])): ?>
                    <img src="assets/qrcodes/<?php echo htmlspecialchars($invoice['qr_code']); ?>.png" 
                         alt="QR Code" class="img-fluid" style="max-width: 200px;">
                <?php else: ?>
                    <p class="text-muted">Không có mã QR</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">Chi tiết sản phẩm</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
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
                    <?php 
                    $stt = 1;
                    foreach ($details as $item): 
                    ?>
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
                        <td colspan="5" class="text-end"><strong>Tổng tiền:</strong></td>
                        <td><strong><?php echo number_format($invoice['total_amount']); ?>đ</strong></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end"><strong>Giảm giá:</strong></td>
                        <td><strong>-<?php echo number_format($invoice['discount']); ?>đ</strong></td>
                    </tr>
                    <tr class="table-success">
                        <td colspan="5" class="text-end"><strong>Thành tiền:</strong></td>
                        <td><strong><?php echo number_format($invoice['final_amount']); ?>đ</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
