<?php require_once 'views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= $pageTitle ?></h1>
            </div>
            
            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-3">
                        <input type="hidden" name="page" value="reports">
                        <input type="hidden" name="action" value="topSelling">
                        
                        <div class="col-md-3">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="<?= htmlspecialchars($startDate) ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="<?= htmlspecialchars($endDate) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Số lượng</label>
                            <select name="limit" class="form-select">
                                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>Top 10</option>
                                <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>Top 20</option>
                                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>Top 50</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="bi bi-search"></i> Xem báo cáo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Tổng số lượng bán</h5>
                            <h2><?= number_format($totalQuantity) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Tổng doanh thu</h5>
                            <h2><?= number_format($totalRevenue) ?> đ</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Số loại thuốc</h5>
                            <h2><?= count($topMedicines) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Biểu đồ thuốc bán chạy</h5>
                </div>
                <div class="card-body">
                    <canvas id="topSellingChart" height="80"></canvas>
                </div>
            </div>
            
            <!-- Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Chi tiết thuốc bán chạy</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Hạng</th>
                                    <th>Tên thuốc</th>
                                    <th>Danh mục</th>
                                    <th>Đơn vị</th>
                                    <th>Số lượng bán</th>
                                    <th>Số đơn hàng</th>
                                    <th>Doanh thu</th>
                                    <th>% Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topMedicines)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $rank = 1; ?>
                                    <?php foreach ($topMedicines as $medicine): ?>
                                        <?php 
                                        $percentage = $totalRevenue > 0 ? ($medicine['total_revenue'] / $totalRevenue * 100) : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if ($rank <= 3): ?>
                                                    <span class="badge bg-warning text-dark">#<?= $rank ?></span>
                                                <?php else: ?>
                                                    #<?= $rank ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($medicine['medicine_name']) ?></td>
                                            <td><?= htmlspecialchars($medicine['category_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($medicine['unit_name'] ?? 'N/A') ?></td>
                                            <td><?= number_format($medicine['total_quantity']) ?></td>
                                            <td><?= number_format($medicine['order_count']) ?></td>
                                            <td><?= number_format($medicine['total_revenue']) ?> đ</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $percentage ?>%"
                                                         aria-valuenow="<?= $percentage ?>" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        <?= number_format($percentage, 1) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $rank++; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Biểu đồ thuốc bán chạy
const ctx = document.getElementById('topSellingChart');
const chartData = {
    labels: [
        <?php foreach ($topMedicines as $medicine): ?>
            '<?= addslashes($medicine['medicine_name']) ?>',
        <?php endforeach; ?>
    ],
    datasets: [{
        label: 'Số lượng bán',
        data: [
            <?php foreach ($topMedicines as $medicine): ?>
                <?= $medicine['total_quantity'] ?>,
            <?php endforeach; ?>
        ],
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }, {
        label: 'Doanh thu (nghìn đồng)',
        data: [
            <?php foreach ($topMedicines as $medicine): ?>
                <?= round($medicine['total_revenue'] / 1000) ?>,
            <?php endforeach; ?>
        ],
        backgroundColor: 'rgba(75, 192, 192, 0.5)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
    }]
};

new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        }
    }
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
