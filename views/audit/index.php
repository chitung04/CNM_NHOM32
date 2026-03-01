<?php 
require_once 'views/layouts/header.php';
require_once 'helpers/audit.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= $pageTitle ?></h1>
                <div class="btn-toolbar">
                    <a href="index.php?page=audit&action=statistics" class="btn btn-outline-primary me-2">
                        <i class="bi bi-graph-up me-2"></i>Thống kê
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cleanupModal">
                        <i class="bi bi-trash me-2"></i>Dọn dẹp logs cũ
                    </button>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-3">
                        <input type="hidden" name="page" value="audit">
                        
                        <div class="col-md-3">
                            <label class="form-label">Người dùng</label>
                            <select name="user_id" class="form-select">
                                <option value="">Tất cả</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['user_id'] ?>" <?= ($_GET['user_id'] ?? '') == $user['user_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Hành động</label>
                            <select name="action" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="CREATE" <?= ($_GET['action'] ?? '') == 'CREATE' ? 'selected' : '' ?>>Tạo mới</option>
                                <option value="UPDATE" <?= ($_GET['action'] ?? '') == 'UPDATE' ? 'selected' : '' ?>>Cập nhật</option>
                                <option value="DELETE" <?= ($_GET['action'] ?? '') == 'DELETE' ? 'selected' : '' ?>>Xóa</option>
                                <option value="LOGIN_SUCCESS" <?= ($_GET['action'] ?? '') == 'LOGIN_SUCCESS' ? 'selected' : '' ?>>Đăng nhập</option>
                                <option value="LOGOUT" <?= ($_GET['action'] ?? '') == 'LOGOUT' ? 'selected' : '' ?>>Đăng xuất</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Bảng</label>
                            <select name="table_name" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="users" <?= ($_GET['table_name'] ?? '') == 'users' ? 'selected' : '' ?>>Người dùng</option>
                                <option value="medicines" <?= ($_GET['table_name'] ?? '') == 'medicines' ? 'selected' : '' ?>>Thuốc</option>
                                <option value="batches" <?= ($_GET['table_name'] ?? '') == 'batches' ? 'selected' : '' ?>>Lô thuốc</option>
                                <option value="invoices" <?= ($_GET['table_name'] ?? '') == 'invoices' ? 'selected' : '' ?>>Hóa đơn</option>
                                <option value="suppliers" <?= ($_GET['table_name'] ?? '') == 'suppliers' ? 'selected' : '' ?>>Nhà cung cấp</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="bi bi-search me-2"></i>Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Logs Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Người dùng</th>
                                    <th>Hành động</th>
                                    <th>Bảng</th>
                                    <th>Record ID</th>
                                    <th>IP Address</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td>
                                                <?= htmlspecialchars($log['full_name'] ?? 'N/A') ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($log['username'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $badgeClass = 'secondary';
                                                if (strpos($log['action'], 'CREATE') !== false) $badgeClass = 'success';
                                                elseif (strpos($log['action'], 'UPDATE') !== false) $badgeClass = 'info';
                                                elseif (strpos($log['action'], 'DELETE') !== false) $badgeClass = 'danger';
                                                elseif (strpos($log['action'], 'LOGIN') !== false) $badgeClass = 'primary';
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= getActionName($log['action']) ?>
                                                </span>
                                            </td>
                                            <td><?= getTableName($log['table_name']) ?></td>
                                            <td><?= $log['record_id'] ?? '-' ?></td>
                                            <td><small><?= htmlspecialchars($log['ip_address'] ?? '') ?></small></td>
                                            <td>
                                                <a href="index.php?page=audit&action=view&id=<?= $log['log_id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=audit&p=<?= $i ?><?= !empty($_GET['user_id']) ? '&user_id='.$_GET['user_id'] : '' ?><?= !empty($_GET['action']) ? '&action='.$_GET['action'] : '' ?><?= !empty($_GET['table_name']) ? '&table_name='.$_GET['table_name'] : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=audit&action=cleanup">
                <div class="modal-header">
                    <h5 class="modal-title">Dọn dẹp logs cũ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Hành động này sẽ xóa vĩnh viễn các logs cũ và không thể khôi phục!
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xóa logs cũ hơn (ngày)</label>
                        <input type="number" name="days" class="form-control" value="90" min="30" required>
                        <small class="text-muted">Khuyến nghị: 90 ngày</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa logs</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
