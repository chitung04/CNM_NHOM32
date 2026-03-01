<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-capsule me-2"></i>Quản lý thuốc</h2>
    <a href="index.php?page=medicines&action=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm thuốc mới
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Danh sách thuốc</h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" 
                           placeholder="Tìm kiếm thuốc...">
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search me-1"></i>Tìm
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="medicinesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên thuốc</th>
                        <th>Danh mục</th>
                        <th>Đơn vị</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($medicines)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Chưa có thuốc nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $stt = 1; foreach ($medicines as $medicine): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($medicine['medicine_name']); ?></strong>
                                </td>
                                <td>
                                    <?php if ($medicine['category_name']): ?>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($medicine['category_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($medicine['unit_name'] ?? '-'); ?></td>
                                <td>
                                    <strong class="text-success">
                                        <?php echo number_format($medicine['price'], 0, ',', '.'); ?>đ
                                    </strong>
                                </td>
                                <td>
                                    <?php 
                                    $inventory = $medicine['inventory'];
                                    $badgeClass = $inventory < LOW_STOCK_THRESHOLD ? 'bg-danger' : 'bg-success';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $inventory; ?> <?php echo htmlspecialchars($medicine['unit_name'] ?? ''); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success add-to-cart" 
                                                data-id="<?php echo $medicine['medicine_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($medicine['medicine_name']); ?>"
                                                title="Thêm vào đơn hàng">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                        <a href="index.php?page=medicines&action=view&id=<?php echo $medicine['medicine_id']; ?>" 
                                           class="btn btn-outline-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="index.php?page=medicines&action=edit&id=<?php echo $medicine['medicine_id']; ?>" 
                                           class="btn btn-outline-primary" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?page=medicines&action=delete&id=<?php echo $medicine['medicine_id']; ?>" 
                                           class="btn btn-outline-danger" title="Xóa"
                                           onclick="return confirmDelete('Bạn có chắc chắn muốn xóa thuốc này?')">
                                            <i class="bi bi-trash"></i>
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
// Wait for jQuery to load
$(document).ready(function() {
    // Search functionality - Vanilla JavaScript
    function performSearch() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const table = document.getElementById('medicinesTable');
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

    // Add to cart functionality
    $('.add-to-cart').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const btn = $(this);
        
        if (!confirm('Thêm "' + name + '" vào đơn hàng?')) {
            return;
        }
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: 'ajax/add_to_cart.php',
            method: 'POST',
            data: {medicine_id: id, quantity: 1},
            dataType: 'json',
            success: function(res) {
                console.log('Success:', res);
                if (res.success) {
                    alert('Đã thêm vào đơn hàng! Vào trang "Bán hàng" để xem.');
                    btn.prop('disabled', false);
                } else {
                    alert(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                alert('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể thêm sản phẩm'));
                btn.prop('disabled', false);
            }
        });
    });
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
