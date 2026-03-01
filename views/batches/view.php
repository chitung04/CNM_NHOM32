<?php
require_once 'helpers/auth.php';
requireManager();

$pageTitle = $pageTitle ?? 'Chi tiết lô thuốc';
require_once 'views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-box-seam"></i> Chi tiết lô thuốc
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php?page=batches" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- Thông tin lô thuốc -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin lô thuốc</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th width="200">Mã lô:</th>
                                        <td><strong>#<?= $batch['batch_id'] ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Tên thuốc:</th>
                                        <td>
                                            <a href="index.php?page=medicines&action=view&id=<?= $batch['medicine_id'] ?>" 
                                               class="text-decoration-none">
                                                <strong class="text-primary"><?= htmlspecialchars($batch['medicine_name']) ?></strong>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Nhà cung cấp:</th>
                                        <td>
                                            <?php if ($batch['supplier_name']): ?>
                                                <?= htmlspecialchars($batch['supplier_name']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Không có thông tin</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Số lượng:</th>
                                        <td>
                                            <strong class="fs-5"><?= $batch['quantity'] ?></strong>
                                            <?php if ($batch['quantity'] == 0): ?>
                                                <span class="badge bg-secondary ms-2">Hết hàng</span>
                                            <?php elseif ($batch['quantity'] < 10): ?>
                                                <span class="badge bg-warning text-dark ms-2">Sắp hết</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ngày nhập kho:</th>
                                        <td><?= date('d/m/Y', strtotime($batch['import_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Hạn sử dụng:</th>
                                        <td>
                                            <?= date('d/m/Y', strtotime($batch['expiry_date'])) ?>
                                            <?php if ($batch['days_to_expiry'] <= 30 && $batch['days_to_expiry'] > 0): ?>
                                                <span class="badge bg-warning text-dark ms-2">
                                                    <i class="bi bi-exclamation-triangle"></i> 
                                                    Còn <?= $batch['days_to_expiry'] ?> ngày
                                                </span>
                                            <?php elseif ($batch['days_to_expiry'] <= 0): ?>
                                                <span class="badge bg-danger ms-2">
                                                    <i class="bi bi-x-circle"></i> Đã hết hạn
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success ms-2">
                                                    <i class="bi bi-check-circle"></i> 
                                                    Còn <?= $batch['days_to_expiry'] ?> ngày
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Trạng thái:</th>
                                        <td>
                                            <?php
                                            $statusBadge = [
                                                'active' => '<span class="badge bg-success fs-6">Còn hàng</span>',
                                                'expired' => '<span class="badge bg-danger fs-6">Hết hạn</span>',
                                                'sold_out' => '<span class="badge bg-secondary fs-6">Hết hàng</span>'
                                            ];
                                            echo $statusBadge[$batch['status']] ?? '';
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ngày tạo:</th>
                                        <td><?= date('d/m/Y H:i', strtotime($batch['created_at'])) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Lịch sử bán hàng -->
                    <?php if (!empty($salesHistory)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Lịch sử bán hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Số hóa đơn</th>
                                            <th>Ngày bán</th>
                                            <th>Số lượng</th>
                                            <th>Đơn giá</th>
                                            <th>Thành tiền</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($salesHistory as $sale): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($sale['invoice_number']) ?></strong></td>
                                            <td><?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></td>
                                            <td><?= $sale['quantity'] ?></td>
                                            <td><?= number_format($sale['unit_price'], 0, ',', '.') ?> VNĐ</td>
                                            <td><strong><?= number_format($sale['subtotal'], 0, ',', '.') ?> VNĐ</strong></td>
                                            <td>
                                                <a href="index.php?page=sales&action=invoice&id=<?= $sale['invoice_id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Xem
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <th colspan="2">Tổng cộng</th>
                                            <th><?= array_sum(array_column($salesHistory, 'quantity')) ?></th>
                                            <th></th>
                                            <th><strong><?= number_format(array_sum(array_column($salesHistory, 'subtotal')), 0, ',', '.') ?> VNĐ</strong></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Lô thuốc này chưa được bán.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- QR Code và thống kê -->
                <div class="col-md-4">
                    <!-- QR Code -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Mã QR</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if ($batch['qr_code']): ?>
                                <img src="assets/qrcodes/<?= $batch['qr_code'] ?>.png" 
                                     alt="QR Code" 
                                     class="img-fluid mb-3"
                                     style="max-width: 200px;">
                                <p class="text-muted small mb-0">
                                    <code><?= $batch['qr_code'] ?></code>
                                </p>
                            <?php else: ?>
                                <p class="text-muted">Chưa có mã QR</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Thống kê -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Thống kê</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $initialQuantity = $batch['quantity'];
                            if (!empty($salesHistory)) {
                                $soldQuantity = array_sum(array_column($salesHistory, 'quantity'));
                                $initialQuantity += $soldQuantity;
                            } else {
                                $soldQuantity = 0;
                            }
                            $remainingPercentage = $initialQuantity > 0 ? ($batch['quantity'] / $initialQuantity) * 100 : 0;
                            ?>
                            
                            <div class="mb-3">
                                <small class="text-muted">Số lượng ban đầu</small>
                                <h4 class="mb-0"><?= number_format($initialQuantity) ?></h4>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Đã bán</small>
                                <h4 class="mb-0 text-primary"><?= number_format($soldQuantity) ?></h4>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Còn lại</small>
                                <h4 class="mb-0 text-success"><?= number_format($batch['quantity']) ?></h4>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Tỷ lệ còn lại</small>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar <?= $remainingPercentage < 20 ? 'bg-danger' : ($remainingPercentage < 50 ? 'bg-warning' : 'bg-success') ?>" 
                                         role="progressbar" 
                                         style="width: <?= $remainingPercentage ?>%"
                                         aria-valuenow="<?= $remainingPercentage ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?= number_format($remainingPercentage, 1) ?>%
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($salesHistory)): ?>
                            <div>
                                <small class="text-muted">Tổng doanh thu</small>
                                <h4 class="mb-0 text-success">
                                    <?= number_format(array_sum(array_column($salesHistory, 'subtotal')), 0, ',', '.') ?> VNĐ
                                </h4>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
