<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt me-2"></i>Lịch sử đơn hàng</h2>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Danh sách đơn hàng</h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" 
                           placeholder="Tìm kiếm đơn hàng...">
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search me-1"></i>Tìm
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="invoicesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mã đơn hàng</th>
                        <th>Nhân viên</th>
                        <th>Ngày tạo</th>
                        <th>Tổng tiền</th>
                        <th>Giảm giá</th>
                        <th>Thành tiền</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Chưa có đơn hàng nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $stt = 1; foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($invoice['staff_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></td>
                                <td><?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?>đ</td>
                                <td><?php echo number_format($invoice['discount'], 0, ',', '.'); ?>đ</td>
                                <td>
                                    <strong class="text-success">
                                        <?php echo number_format($invoice['final_amount'], 0, ',', '.'); ?>đ
                                    </strong>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?page=invoices&action=view&id=<?php echo $invoice['invoice_id']; ?>" 
                                           class="btn btn-outline-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="index.php?page=invoices&action=print&id=<?php echo $invoice['invoice_id']; ?>" 
                                           class="btn btn-outline-primary" title="In hóa đơn" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search functionality - Vanilla JavaScript
function performSearch() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('invoicesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;
        
        if (text.toLowerCase().indexOf(searchValue) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// Search on button click
document.getElementById('searchButton').addEventListener('click', performSearch);

// Search on Enter key
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performSearch();
    }
});

// Real-time search on typing
document.getElementById('searchInput').addEventListener('keyup', performSearch);
</script>

<?php require_once 'views/layouts/footer.php'; ?>
