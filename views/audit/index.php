<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-journal-text me-2"></i><?= $pageTitle ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="index.php?page=audit&action=export&format=csv" class="btn btn-outline-success">
                <i class="bi bi-download me-2"></i>Xuất CSV
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cleanupModal">
                <i class="bi bi-trash me-2"></i>Dọn dẹp
            </button>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Thống kê nhanh -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Tổng logs</h5>
                        <h3><?= number_format($totalLogs) ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-journal-text fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Hôm nay</h5>
                        <h3><?= $todayCount ?? 0 ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-calendar-day fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Người dùng hoạt động</h5>
                        <h3><?= $activeUsers ?? 0 ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Tuần này</h5>
                        <h3><?= $weekCount ?? 0 ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-calendar-week fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-funnel me-2"></i>Bộ lọc
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="page" value="audit">
            
            <div class="col-md-2">
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
            
            <div class="col-md-2">
                <label class="form-label">Hành động</label>
                <select name="action" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?= $action ?>" <?= ($_GET['action'] ?? '') == $action ? 'selected' : '' ?>>
                            <?= getActionName($action) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Bảng</label>
                <select name="table_name" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($tables as $table): ?>
                        <option value="<?= $table ?>" <?= ($_GET['table_name'] ?? '') == $table ? 'selected' : '' ?>>
                            <?= getTableName($table) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? '' ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? '' ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Lọc
                    </button>
                    <a href="index.php?page=audit" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list-ul me-2"></i>Nhật ký hoạt động
            <span class="badge bg-secondary ms-2"><?= number_format($totalLogs) ?> logs</span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">Thời gian</th>
                        <th width="15%">Người dùng</th>
                        <th width="15%">Hành động</th>
                        <th width="12%">Bảng</th>
                        <th width="8%">ID</th>
                        <th width="15%">IP Address</th>
                        <th width="20%">User Agent</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Không có dữ liệu nhật ký</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <small class="text-muted"><?= $log['created_at'] ? date('d/m/Y', strtotime($log['created_at'])) : '-' ?></small><br>
                                    <strong><?= $log['created_at'] ? date('H:i:s', strtotime($log['created_at'])) : '-' ?></strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($log['full_name'] ?? 'System') ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($log['username'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'secondary';
                                    $icon = 'bi-gear';
                                    if (strpos($log['action'], 'CREATE') !== false || strpos($log['action'], 'INSERT') !== false) {
                                        $badgeClass = 'success';
                                        $icon = 'bi-plus-circle';
                                    } elseif (strpos($log['action'], 'UPDATE') !== false) {
                                        $badgeClass = 'info';
                                        $icon = 'bi-pencil-square';
                                    } elseif (strpos($log['action'], 'DELETE') !== false) {
                                        $badgeClass = 'danger';
                                        $icon = 'bi-trash';
                                    } elseif (strpos($log['action'], 'LOGIN') !== false) {
                                        $badgeClass = 'primary';
                                        $icon = 'bi-box-arrow-in-right';
                                    } elseif (strpos($log['action'], 'LOGOUT') !== false) {
                                        $badgeClass = 'warning';
                                        $icon = 'bi-box-arrow-right';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?> d-flex align-items-center">
                                        <i class="<?= $icon ?> me-1"></i>
                                        <?= getActionName($log['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= getTableName($log['table_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['record_id']): ?>
                                        <code>#<?= $log['record_id'] ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted font-monospace">
                                        <?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                        <?= htmlspecialchars(substr($log['user_agent'] ?? '', 0, 50)) ?><?= strlen($log['user_agent'] ?? '') > 50 ? '...' : '' ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="index.php?page=audit&action=view&id=<?= $log['log_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
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
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php 
                        $currentPage = $_GET['p'] ?? 1;
                        $queryParams = http_build_query(array_filter([
                            'page' => 'audit',
                            'user_id' => $_GET['user_id'] ?? '',
                            'action' => $_GET['action'] ?? '',
                            'table_name' => $_GET['table_name'] ?? '',
                            'start_date' => $_GET['start_date'] ?? '',
                            'end_date' => $_GET['end_date'] ?? ''
                        ]));
                        ?>
                        
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= $queryParams ?>&p=<?= $currentPage - 1 ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= $queryParams ?>&p=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= $queryParams ?>&p=<?= $currentPage + 1 ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}
</style>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=audit&action=cleanup">
                <?= csrfField() ?>
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
