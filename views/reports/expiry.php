<?php require_once 'views/layouts/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-exclamation-triangle me-2"></i>Thuốc sắp hết hạn</h2>

<div class="alert alert-warning">
    <i class="bi bi-info-circle me-2"></i>
    Danh sách các lô thuốc sẽ hết hạn trong vòng <?php echo $days; ?> ngày tới
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Lô thuốc</th>
                        <th>Tên thuốc</th>
                        <th>Nhà cung cấp</th>
                        <th>Số lượng</th>
                        <th>Ngày nhập</th>
                        <th>Hạn sử dụng</th>
                        <th>Còn lại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($batches)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                                Không có lô thuốc nào sắp hết hạn
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($batches as $batch): ?>
                            <?php
                            $daysLeft = $batch['days_to_expiry'];
                            $urgencyClass = $daysLeft <= 7 ? 'danger' : ($daysLeft <= 15 ? 'warning' : 'info');
                            ?>
                            <tr class="table-<?php echo $urgencyClass; ?>">
                                <td><strong>#<?php echo $batch['batch_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($batch['medicine_name']); ?></td>
                                <td><?php echo htmlspecialchars($batch['supplier_name'] ?? '-'); ?></td>
                                <td><span class="badge bg-secondary"><?php echo $batch['quantity']; ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($batch['import_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($batch['expiry_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $urgencyClass; ?>">
                                        <?php echo $daysLeft; ?> ngày
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
