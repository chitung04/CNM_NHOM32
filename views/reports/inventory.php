<?php require_once 'views/layouts/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-boxes me-2"></i>Báo cáo tồn kho</h2>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tên thuốc</th>
                        <th>Danh mục</th>
                        <th>Đơn vị</th>
                        <th>Tồn kho</th>
                        <th>Giá bán</th>
                        <th>Giá trị tồn</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalValue = 0;
                    foreach ($medicines as $med): 
                        $value = $med['inventory'] * $med['price'];
                        $totalValue += $value;
                        
                        $statusClass = $med['inventory'] < LOW_STOCK_THRESHOLD ? 'danger' : 'success';
                        $statusText = $med['inventory'] < LOW_STOCK_THRESHOLD ? 'Sắp hết' : 'Còn hàng';
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($med['medicine_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($med['category_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($med['unit_name'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo $med['inventory']; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($med['price']); ?>đ</td>
                            <td><strong><?php echo number_format($value); ?>đ</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <td colspan="5" class="text-end"><strong>Tổng giá trị tồn kho:</strong></td>
                        <td colspan="2"><strong class="text-success"><?php echo number_format($totalValue); ?>đ</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
