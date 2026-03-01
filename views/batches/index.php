<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam me-2"></i>Quản lý lô thuốc</h2>
    <a href="index.php?page=batches&action=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nhập lô mới
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Danh sách lô thuốc</h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" 
                           placeholder="Tìm kiếm lô thuốc...">
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search me-1"></i>Tìm
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="batchesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên thuốc</th>
                        <th>Nhà cung cấp</th>
                        <th>Số lượng</th>
                        <th>Ngày nhập</th>
                        <th>Hạn sử dụng</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $stt = 1; foreach ($batches as $batch): ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($batch['medicine_name']); ?></td>
                            <td><?php echo htmlspecialchars($batch['supplier_name'] ?? '-'); ?></td>
                            <td><span class="badge bg-info"><?php echo $batch['quantity']; ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($batch['import_date'])); ?></td>
                            <td>
                                <?php 
                                $daysToExpiry = $batch['days_to_expiry'];
                                $expiryClass = $daysToExpiry <= 30 ? 'text-danger' : 'text-success';
                                ?>
                                <span class="<?php echo $expiryClass; ?>">
                                    <?php echo date('d/m/Y', strtotime($batch['expiry_date'])); ?>
                                    <?php if ($daysToExpiry <= 30 && $daysToExpiry >= 0): ?>
                                        <br><small>(Còn <?php echo $daysToExpiry; ?> ngày)</small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusBadge = [
                                    'active' => 'bg-success',
                                    'expired' => 'bg-danger',
                                    'sold_out' => 'bg-secondary'
                                ];
                                $statusText = [
                                    'active' => 'Còn hàng',
                                    'expired' => 'Hết hạn',
                                    'sold_out' => 'Hết hàng'
                                ];
                                ?>
                                <span class="badge <?php echo $statusBadge[$batch['status']]; ?>">
                                    <?php echo $statusText[$batch['status']]; ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?page=batches&action=view&id=<?php echo $batch['batch_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
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

<script>
// Search functionality - Vanilla JavaScript
function performSearch() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('batchesTable');
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
