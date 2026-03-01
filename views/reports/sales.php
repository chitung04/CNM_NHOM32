<?php require_once 'views/layouts/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-bar-chart me-2"></i>Báo cáo doanh thu</h2>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="type" value="sales">
            
            <div class="col-md-4">
                <label class="form-label">Từ ngày</label>
                <input type="date" class="form-control" name="start_date" 
                       value="<?php echo $startDate; ?>">
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Đến ngày</label>
                <input type="date" class="form-control" name="end_date" 
                       value="<?php echo $endDate; ?>">
            </div>
            
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-2"></i>Xem báo cáo
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted">Tổng doanh thu</h6>
                <h2 class="text-success"><?php echo number_format($totalRevenue); ?>đ</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted">Số hóa đơn</h6>
                <h2 class="text-primary"><?php echo count($invoices); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="text-muted">Trung bình/hóa đơn</h6>
                <h2 class="text-info">
                    <?php echo count($invoices) > 0 ? number_format($totalRevenue / count($invoices)) : 0; ?>đ
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Doanh thu theo ngày</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Số hóa đơn</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenueByDay as $date => $revenue): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($date)); ?></td>
                            <td>
                                <?php 
                                $count = count(array_filter($invoices, function($inv) use ($date) {
                                    return date('Y-m-d', strtotime($inv['created_at'])) === $date;
                                }));
                                echo $count;
                                ?>
                            </td>
                            <td><strong><?php echo number_format($revenue); ?>đ</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Danh sách hóa đơn</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Số HĐ</th>
                        <th>Ngày</th>
                        <th>Nhân viên</th>
                        <th>Tổng tiền</th>
                        <th>Giảm giá</th>
                        <th>Thanh toán</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><?php echo $inv['invoice_number']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($inv['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($inv['staff_name']); ?></td>
                            <td><?php echo number_format($inv['total_amount']); ?>đ</td>
                            <td><?php echo number_format($inv['discount']); ?>đ</td>
                            <td><strong><?php echo number_format($inv['final_amount']); ?>đ</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
