<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-truck me-2"></i>Quản lý nhà cung cấp</h2>
    <a href="index.php?page=suppliers&action=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm nhà cung cấp
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên nhà cung cấp</th>
                        <th>Điện thoại</th>
                        <th>Email</th>
                        <th>Địa chỉ</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?php echo $supplier['supplier_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($supplier['supplier_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($supplier['phone'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($supplier['email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($supplier['address'] ?? '-'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="index.php?page=suppliers&action=edit&id=<?php echo $supplier['supplier_id']; ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="index.php?page=suppliers&action=delete&id=<?php echo $supplier['supplier_id']; ?>" 
                                       class="btn btn-outline-danger"
                                       onclick="return confirmDelete('Xóa nhà cung cấp này?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
