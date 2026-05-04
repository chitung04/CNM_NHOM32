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
                        <td><?php echo $invoice['created_at'] ? date('d/m/Y H:i', strtotime($invoice['created_at'])) : '-'; ?></td>
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
            <div class="card-header"><h5 class="mb-0">Thông tin thanh toán</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Hình thức:</strong></td>
                        <td>
                            <?php 
                            if (!empty($invoice['payment_method'])) {
                                if ($invoice['payment_method'] === 'cash') {
                                    echo '<span class="badge bg-success"><i class="bi bi-cash"></i> Tiền mặt</span>';
                                } else {
                                    echo '<span class="badge bg-primary"><i class="bi bi-credit-card"></i> Chuyển khoản</span>';
                                }
                            } else {
                                echo '<span class="badge bg-secondary">Chưa thanh toán</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php if (!empty($invoice['payment_method'])): ?>
                        <tr>
                            <td><strong>Số tiền thanh toán:</strong></td>
                            <td><?php echo number_format($invoice['amount_paid'] ?? 0); ?>đ</td>
                        </tr>
                        <?php if ($invoice['payment_method'] === 'cash'): ?>
                            <?php 
                            $change = ($invoice['amount_paid'] ?? 0) - $invoice['final_amount'];
                            ?>
                            <tr>
                                <td><strong>Tiền thừa:</strong></td>
                                <td class="<?php echo $change > 0 ? 'text-success' : ''; ?>">
                                    <?php echo number_format($change); ?>đ
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>Trạng thái:</strong></td>
                            <td><span class="badge bg-success">Đã thanh toán</span></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center text-muted">
                                <i class="bi bi-exclamation-circle"></i> Chưa có thông tin thanh toán
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
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
