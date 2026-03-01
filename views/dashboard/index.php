<?php 
require_once 'views/layouts/header.php';

// Load models để lấy thống kê
require_once 'models/Medicine.php';
require_once 'models/Batch.php';
require_once 'models/Invoice.php';
require_once 'models/Notification.php';

$medicineModel = new Medicine();
$batchModel = new Batch();
$invoiceModel = new Invoice();
$notificationModel = new Notification();

// Lấy thống kê
$totalMedicines = count($medicineModel->getAll());
$todayInvoices = count($invoiceModel->getByDateRange(date('Y-m-d'), date('Y-m-d')));
$expiringBatches = count($batchModel->getExpiringBatches(30));

// Đếm thuốc sắp hết hàng
$lowStockCount = 0;
$medicines = $medicineModel->getAll();
foreach ($medicines as $medicine) {
    $inventory = $medicineModel->getTotalInventory($medicine['medicine_id']);
    if ($inventory < 10) {
        $lowStockCount++;
    }
}

// Lấy doanh thu hôm nay
$todayRevenue = $invoiceModel->getTotalRevenue(date('Y-m-d'), date('Y-m-d'));
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Welcome Card -->
            <div class="welcome-card mt-4">
                <h2>
                    <i class="bi bi-emoji-smile me-2"></i>
                    Xin chào, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!
                </h2>
                <p class="mb-0">Chào mừng bạn đến với hệ thống quản lý nhà thuốc</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-capsule stat-icon text-primary"></i>
                            <h3 class="mt-3"><?= number_format($totalMedicines) ?></h3>
                            <p class="text-muted mb-0">Tổng số thuốc</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-receipt stat-icon text-success"></i>
                            <h3 class="mt-3"><?= number_format($todayInvoices) ?></h3>
                            <p class="text-muted mb-0">Hóa đơn hôm nay</p>
                            <small class="text-success"><?= number_format($todayRevenue) ?> đ</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle stat-icon text-warning"></i>
                            <h3 class="mt-3"><?= number_format($expiringBatches) ?></h3>
                            <p class="text-muted mb-0">Lô sắp hết hạn</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-box-seam stat-icon text-danger"></i>
                            <h3 class="mt-3"><?= number_format($lowStockCount) ?></h3>
                            <p class="text-muted mb-0">Thuốc sắp hết hàng</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-lightning-charge me-2"></i>
                                Thao tác nhanh
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="index.php?page=sales" class="btn btn-outline-primary w-100 py-3">
                                        <i class="bi bi-cart-plus d-block fs-3 mb-2"></i>
                                        Bán hàng
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="index.php?page=medicines" class="btn btn-outline-success w-100 py-3">
                                        <i class="bi bi-search d-block fs-3 mb-2"></i>
                                        Tra cứu thuốc
                                    </a>
                                </div>
                                <?php if (isManager()): ?>
                                <div class="col-md-3">
                                    <a href="index.php?page=batches&action=create" class="btn btn-outline-info w-100 py-3">
                                        <i class="bi bi-box-seam d-block fs-3 mb-2"></i>
                                        Nhập kho
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="index.php?page=reports&action=sales" class="btn btn-outline-warning w-100 py-3">
                                        <i class="bi bi-bar-chart d-block fs-3 mb-2"></i>
                                        Báo cáo
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (isManager()): ?>
            <!-- Recent Activities -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>
                                Hóa đơn gần đây
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $recentInvoices = $invoiceModel->getAll();
                            $recentInvoices = array_slice($recentInvoices, 0, 5);
                            ?>
                            <?php if (empty($recentInvoices)): ?>
                                <p class="text-muted text-center">Chưa có hóa đơn nào</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentInvoices as $invoice): ?>
                                        <a href="index.php?page=sales&action=invoice&id=<?= $invoice['invoice_id'] ?>" 
                                           class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($invoice['invoice_number']) ?></h6>
                                                <small><?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></small>
                                            </div>
                                            <p class="mb-1">
                                                <small>Nhân viên: <?= htmlspecialchars($invoice['staff_name']) ?></small>
                                            </p>
                                            <small class="text-success"><?= number_format($invoice['final_amount']) ?> đ</small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-bell me-2"></i>
                                Cảnh báo
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $notifications = $notificationModel->getUnread(10);
                            ?>
                            <?php if (empty($notifications)): ?>
                                <p class="text-muted text-center">Không có cảnh báo nào</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($notifications as $notif): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php if ($notif['type'] === 'low_stock'): ?>
                                                        <i class="bi bi-box-seam text-danger"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-exclamation-triangle text-warning"></i>
                                                    <?php endif; ?>
                                                    <?= $notif['type'] === 'low_stock' ? 'Sắp hết hàng' : 'Sắp hết hạn' ?>
                                                </h6>
                                                <small><?= date('d/m/Y', strtotime($notif['created_at'])) ?></small>
                                            </div>
                                            <p class="mb-0"><small><?= htmlspecialchars($notif['message']) ?></small></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
}
.stat-card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-icon {
    font-size: 3rem;
    opacity: 0.8;
}
</style>

<?php require_once 'views/layouts/footer.php'; ?>
