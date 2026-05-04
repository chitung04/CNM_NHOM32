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
                            <td><?php echo $date ? date('d/m/Y', strtotime($date)) : '-'; ?></td>
                            <td>
                                <?php 
                                $count = count(array_filter($invoices, function($inv) use ($date) {
                                    return $inv['created_at'] && date('Y-m-d', strtotime($inv['created_at'])) === $date;
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
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td>
                                <a href="#" class="text-decoration-none view-invoice-detail" 
                                   data-invoice-id="<?php echo $inv['invoice_id']; ?>"
                                   data-invoice-number="<?php echo htmlspecialchars($inv['invoice_number']); ?>">
                                    <strong><?php echo $inv['invoice_number']; ?></strong>
                                </a>
                            </td>
                            <td><?php echo $inv['created_at'] ? date('d/m/Y H:i', strtotime($inv['created_at'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($inv['staff_name']); ?></td>
                            <td><?php echo number_format($inv['total_amount']); ?>đ</td>
                            <td><?php echo number_format($inv['discount']); ?>đ</td>
                            <td><strong><?php echo number_format($inv['final_amount']); ?>đ</strong></td>
                            <td>
                                <a href="index.php?page=invoices&action=view&id=<?php echo $inv['invoice_id']; ?>" 
                                   class="btn btn-sm btn-outline-info" 
                                   title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Chi tiết hóa đơn -->
<div class="modal fade" id="invoiceDetailModal" tabindex="-1" aria-labelledby="invoiceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark" id="invoiceDetailModalLabel">
                    <i class="bi bi-receipt me-2"></i>Chi tiết hóa đơn
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="invoiceDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-3">Đang tải thông tin hóa đơn...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load invoice detail
function loadInvoiceDetail(invoiceId) {
    console.log('Loading invoice detail for ID:', invoiceId);
    
    const content = document.getElementById('invoiceDetailContent');
    if (!content) {
        console.error('Invoice detail content element not found');
        return;
    }
    
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-3">Đang tải thông tin hóa đơn...</p>
        </div>
    `;
    
    // Redirect to invoice view page
    window.location.href = 'index.php?page=invoices&action=view&id=' + invoiceId;
}

// Click on invoice number to view detail
document.addEventListener('click', function(e) {
    if (e.target.closest('.view-invoice-detail')) {
        e.preventDefault();
        const link = e.target.closest('.view-invoice-detail');
        const invoiceId = link.getAttribute('data-invoice-id');
        
        if (invoiceId) {
            loadInvoiceDetail(invoiceId);
        }
    }
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
