<?php require_once 'views/layouts/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-box-seam me-2 text-danger"></i>Thuốc sắp hết hàng</h2>

<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Danh sách các thuốc có tồn kho ≤ <?php echo $threshold; ?> đơn vị
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Danh sách thuốc</h5>
            </div>
            <div class="col-md-6 text-end">
                <form method="GET" action="index.php" class="d-inline-flex gap-2">
                    <input type="hidden" name="page" value="reports">
                    <input type="hidden" name="action" value="lowStock">
                    <label class="form-label mb-0 me-2 align-self-center">Ngưỡng cảnh báo:</label>
                    <input type="number" name="threshold" class="form-control form-control-sm" 
                           style="width: 100px;" value="<?php echo $threshold; ?>" min="1">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên thuốc</th>
                        <th>Danh mục</th>
                        <th>Đơn vị</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lowStockMedicines)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                <h5>Tất cả thuốc đều đủ hàng</h5>
                                <p>Không có thuốc nào có tồn kho thấp hơn <?php echo $threshold; ?> đơn vị</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $stt = 1;
                        foreach ($lowStockMedicines as $medicine): 
                            $inventory = $medicine['inventory'];
                            $urgencyClass = $inventory == 0 ? 'danger' : ($inventory <= 5 ? 'warning' : 'info');
                        ?>
                            <tr class="table-<?php echo $urgencyClass; ?>">
                                <td><?php echo $stt++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($medicine['medicine_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($medicine['category_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($medicine['unit_name'] ?? 'Viên'); ?></td>
                                <td><?php echo number_format($medicine['price'], 0, ',', '.'); ?>đ</td>
                                <td>
                                    <span class="badge bg-<?php echo $urgencyClass; ?> fs-6">
                                        <?php echo $inventory; ?> <?php echo htmlspecialchars($medicine['unit_name'] ?? 'Viên'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($inventory == 0): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Hết hàng
                                        </span>
                                    <?php elseif ($inventory <= 5): ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle"></i> Rất thấp
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info">
                                            <i class="bi bi-info-circle"></i> Thấp
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?page=medicines&action=view&id=<?php echo $medicine['medicine_id']; ?>" 
                                           class="btn btn-outline-info" 
                                           title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (isManager()): ?>
                                        <a href="index.php?page=batches&action=create&medicine_id=<?php echo $medicine['medicine_id']; ?>" 
                                           class="btn btn-outline-success" 
                                           title="Nhập thêm hàng">
                                            <i class="bi bi-plus-circle"></i> Nhập
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($lowStockMedicines)): ?>
    <div class="card-footer bg-light">
        <div class="row">
            <div class="col-md-12">
                <strong>Tổng số thuốc sắp hết hàng: </strong>
                <span class="badge bg-danger fs-6"><?php echo count($lowStockMedicines); ?> loại thuốc</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.table-danger {
    background-color: #f8d7da !important;
}
.table-warning {
    background-color: #fff3cd !important;
}
.table-info {
    background-color: #d1ecf1 !important;
}
</style>

<?php require_once 'views/layouts/footer.php'; ?>
