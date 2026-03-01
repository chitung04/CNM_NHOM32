<?php
require_once 'helpers/auth.php';
requireLogin();

$pageTitle = $pageTitle ?? 'Chi tiết thuốc';
require_once 'views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-capsule"></i> Chi tiết thuốc
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php?page=medicines" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                    <?php if (isManager()): ?>
                    <a href="index.php?page=medicines&action=edit&id=<?= $medicine['medicine_id'] ?>" class="btn btn-sm btn-primary ms-2">
                        <i class="bi bi-pencil"></i> Sửa
                    </a>
                    <?php endif; ?>
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
                <!-- Thông tin thuốc -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th width="200">Mã thuốc:</th>
                                        <td><strong>#<?= $medicine['medicine_id'] ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Tên thuốc:</th>
                                        <td><strong class="text-primary"><?= htmlspecialchars($medicine['medicine_name']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Danh mục:</th>
                                        <td>
                                            <?php if ($medicine['category_name']): ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($medicine['category_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Chưa phân loại</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Đơn vị tính:</th>
                                        <td><?= htmlspecialchars($medicine['unit_name'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Giá bán:</th>
                                        <td><strong class="text-success"><?= number_format($medicine['price'], 0, ',', '.') ?> VNĐ</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Tồn kho:</th>
                                        <td>
                                            <?php
                                            $stockClass = 'success';
                                            if ($inventory < 10) {
                                                $stockClass = 'danger';
                                            } elseif ($inventory < 50) {
                                                $stockClass = 'warning';
                                            }
                                            ?>
                                            <span class="badge bg-<?= $stockClass ?> fs-6">
                                                <?= $inventory ?> <?= htmlspecialchars($medicine['unit_name'] ?? '') ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Mô tả:</th>
                                        <td><?= nl2br(htmlspecialchars($medicine['description'] ?? 'Không có mô tả')) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Ngày tạo:</th>
                                        <td><?= date('d/m/Y H:i', strtotime($medicine['created_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Cập nhật lần cuối:</th>
                                        <td><?= date('d/m/Y H:i', strtotime($medicine['updated_at'])) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Danh sách lô thuốc -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Danh sách lô thuốc</h5>
                            <?php if (isManager()): ?>
                            <a href="index.php?page=batches&action=create&medicine_id=<?= $medicine['medicine_id'] ?>" class="btn btn-sm btn-success">
                                <i class="bi bi-plus-circle"></i> Nhập lô mới
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($batches)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Chưa có lô thuốc nào.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Mã lô</th>
                                                <th>Số lượng</th>
                                                <th>Ngày nhập</th>
                                                <th>Hạn sử dụng</th>
                                                <th>Nhà cung cấp</th>
                                                <th>Trạng thái</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($batches as $batch): ?>
                                            <tr>
                                                <td><strong>#<?= $batch['batch_id'] ?></strong></td>
                                                <td><?= $batch['quantity'] ?></td>
                                                <td><?= date('d/m/Y', strtotime($batch['import_date'])) ?></td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($batch['expiry_date'])) ?>
                                                    <?php if ($batch['days_to_expiry'] <= 30 && $batch['days_to_expiry'] > 0): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-exclamation-triangle"></i> <?= $batch['days_to_expiry'] ?> ngày
                                                        </span>
                                                    <?php elseif ($batch['days_to_expiry'] <= 0): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-x-circle"></i> Hết hạn
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($batch['supplier_name'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php
                                                    $statusBadge = [
                                                        'active' => '<span class="badge bg-success">Còn hàng</span>',
                                                        'expired' => '<span class="badge bg-danger">Hết hạn</span>',
                                                        'sold_out' => '<span class="badge bg-secondary">Hết hàng</span>'
                                                    ];
                                                    echo $statusBadge[$batch['status']] ?? '';
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?page=batches&action=view&id=<?= $batch['batch_id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- QR Code và thống kê -->
                <div class="col-md-4">
                    <!-- QR Code -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Mã QR</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if ($medicine['qr_code']): ?>
                                <img src="assets/qrcodes/<?= $medicine['qr_code'] ?>.png" 
                                     alt="QR Code" 
                                     class="img-fluid mb-3"
                                     style="max-width: 200px;">
                                <p class="text-muted small mb-0">
                                    <code><?= $medicine['qr_code'] ?></code>
                                </p>
                            <?php else: ?>
                                <p class="text-muted">Chưa có mã QR</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Thống kê bán hàng -->
                    <?php if (isset($salesStats) && $salesStats): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Thống kê bán hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Tổng số lượng đã bán</small>
                                <h4 class="mb-0"><?= number_format($salesStats['total_quantity'] ?? 0) ?></h4>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Tổng doanh thu</small>
                                <h4 class="mb-0 text-success"><?= number_format($salesStats['total_revenue'] ?? 0, 0, ',', '.') ?> VNĐ</h4>
                            </div>
                            <div>
                                <small class="text-muted">Số đơn hàng</small>
                                <h4 class="mb-0"><?= number_format($salesStats['order_count'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
