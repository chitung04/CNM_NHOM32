<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Database.php';
require_once 'models/AuditLog.php';
require_once 'helpers/audit.php';
require_once 'helpers/secure_session.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: index.php?page=auth&action=login');
    exit;
}

// Chỉ manager mới được xem audit logs
if ($_SESSION['role'] !== 'manager') {
    $_SESSION['error'] = "Bạn không có quyền truy cập trang này";
    header('Location: index.php?page=dashboard');
    exit;
}

$auditModel = new AuditLog();

// Xử lý filters
$filters = [];
$page = intval($_GET['page'] ?? 1);
$limit = 50;
$offset = ($page - 1) * $limit;

$action = $_GET['action_filter'] ?? '';
$table = $_GET['table_filter'] ?? '';
$user = $_GET['user_filter'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Build query với filters
$sql = "SELECT al.*, u.username, u.full_name
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.user_id
        WHERE 1=1";
$params = [];

if (!empty($action)) {
    $sql .= " AND al.action = ?";
    $params[] = $action;
    $filters['action'] = $action;
}

if (!empty($table)) {
    $sql .= " AND al.table_name = ?";
    $params[] = $table;
    $filters['table_name'] = $table;
}

if (!empty($user)) {
    $sql .= " AND al.user_id = ?";
    $params[] = $user;
    $filters['user_id'] = $user;
}

if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND DATE(al.created_at) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

$sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$db = Database::getInstance();
$stmt = $db->query($sql, $params);
$logs = $stmt->fetchAll();

// Đếm tổng số records
$totalLogs = $auditModel->count($filters);
$totalPages = ceil($totalLogs / $limit);

// Lấy danh sách users cho filter
$usersSql = "SELECT user_id, username, full_name FROM users ORDER BY full_name";
$usersStmt = $db->query($usersSql);
$users = $usersStmt->fetchAll();

// Lấy danh sách actions
$actionsSql = "SELECT DISTINCT action FROM audit_logs ORDER BY action";
$actionsStmt = $db->query($actionsSql);
$actions = $actionsStmt->fetchAll();

// Lấy danh sách tables
$tablesSql = "SELECT DISTINCT table_name FROM audit_logs ORDER BY table_name";
$tablesStmt = $db->query($tablesSql);
$tables = $tablesStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật ký hoạt động - DUO PHARMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .audit-log-card {
            border-left: 4px solid #3b82f6;
            margin-bottom: 15px;
        }
        .audit-log-card.CREATE { border-left-color: #10b981; }
        .audit-log-card.UPDATE { border-left-color: #f59e0b; }
        .audit-log-card.DELETE { border-left-color: #ef4444; }
        .audit-log-card.LOGIN_SUCCESS { border-left-color: #10b981; }
        .audit-log-card.LOGIN_FAILED { border-left-color: #ef4444; }
        .audit-log-card.LOGOUT { border-left-color: #6b7280; }
        
        .json-data {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .action-badge {
            font-size: 11px;
            padding: 4px 8px;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-history"></i> Nhật ký hoạt động hệ thống</h2>
                    <div>
                        <a href="index.php?page=dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button class="btn btn-danger" onclick="confirmCleanup()">
                            <i class="fas fa-trash"></i> Dọn dẹp logs cũ
                        </button>
                    </div>
                </div>

                <!-- Thống kê -->
                <div class="stats-card">
                    <div class="row">
                        <div class="col-md-3">
                            <h5><i class="fas fa-database"></i> Tổng số logs</h5>
                            <h3><?= number_format($totalLogs) ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h5><i class="fas fa-users"></i> Người dùng hoạt động</h5>
                            <h3><?= count($users) ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h5><i class="fas fa-table"></i> Bảng được theo dõi</h5>
                            <h3><?= count($tables) ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h5><i class="fas fa-cogs"></i> Loại hoạt động</h5>
                            <h3><?= count($actions) ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Bộ lọc -->
                <div class="filter-section">
                    <h5><i class="fas fa-filter"></i> Bộ lọc</h5>
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="page" value="1">
                        
                        <div class="col-md-2">
                            <label class="form-label">Hoạt động</label>
                            <select name="action_filter" class="form-select">
                                <option value="">Tất cả</option>
                                <?php foreach ($actions as $act): ?>
                                    <option value="<?= htmlspecialchars($act['action']) ?>" 
                                            <?= $action === $act['action'] ? 'selected' : '' ?>>
                                        <?= getActionName($act['action']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Bảng dữ liệu</label>
                            <select name="table_filter" class="form-select">
                                <option value="">Tất cả</option>
                                <?php foreach ($tables as $tbl): ?>
                                    <option value="<?= htmlspecialchars($tbl['table_name']) ?>" 
                                            <?= $table === $tbl['table_name'] ? 'selected' : '' ?>>
                                        <?= getTableName($tbl['table_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Người dùng</label>
                            <select name="user_filter" class="form-select">
                                <option value="">Tất cả</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['user_id'] ?>" 
                                            <?= $user == $u['user_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Lọc
                                </button>
                                <a href="view_audit_logs.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Xóa
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Danh sách logs -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Danh sách hoạt động (Trang <?= $page ?>/<?= $totalPages ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($logs)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Không có dữ liệu nhật ký nào</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <div class="card audit-log-card <?= $log['action'] ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge bg-primary action-badge me-2">
                                                        <?= getActionName($log['action']) ?>
                                                    </span>
                                                    <strong><?= getTableName($log['table_name']) ?></strong>
                                                    <?php if ($log['record_id']): ?>
                                                        <span class="text-muted ms-2">(ID: <?= $log['record_id'] ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="text-muted small mb-2">
                                                    <i class="fas fa-user"></i> 
                                                    <?= htmlspecialchars($log['full_name'] ?? 'Hệ thống') ?>
                                                    (<?= htmlspecialchars($log['username'] ?? 'system') ?>)
                                                    
                                                    <i class="fas fa-clock ms-3"></i>
                                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                                    
                                                    <?php if ($log['ip_address']): ?>
                                                        <i class="fas fa-globe ms-3"></i>
                                                        <?= htmlspecialchars($log['ip_address']) ?>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($log['old_values'] || $log['new_values']): ?>
                                                    <div class="mt-3">
                                                        <?php if ($log['old_values']): ?>
                                                            <div class="mb-2">
                                                                <strong class="text-danger">Dữ liệu cũ:</strong>
                                                                <div class="json-data">
                                                                    <?= htmlspecialchars(json_encode(json_decode($log['old_values']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($log['new_values']): ?>
                                                            <div class="mb-2">
                                                                <strong class="text-success">Dữ liệu mới:</strong>
                                                                <div class="json-data">
                                                                    <?= htmlspecialchars(json_encode(json_decode($log['new_values']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="text-end">
                                                    <small class="text-muted">
                                                        Log ID: <?= $log['log_id'] ?>
                                                    </small>
                                                </div>
                                                
                                                <?php if ($log['user_agent']): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-desktop"></i>
                                                            <?= htmlspecialchars(substr($log['user_agent'], 0, 50)) ?>...
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Phân trang -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Phân trang audit logs" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&action_filter=<?= urlencode($action) ?>&table_filter=<?= urlencode($table) ?>&user_filter=<?= urlencode($user) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">
                                        <i class="fas fa-chevron-left"></i> Trước
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&action_filter=<?= urlencode($action) ?>&table_filter=<?= urlencode($table) ?>&user_filter=<?= urlencode($user) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&action_filter=<?= urlencode($action) ?>&table_filter=<?= urlencode($table) ?>&user_filter=<?= urlencode($user) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">
                                        Sau <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmCleanup() {
            if (confirm('Bạn có chắc chắn muốn xóa các logs cũ hơn 90 ngày?\nHành động này không thể hoàn tác!')) {
                window.location.href = 'cleanup_audit_logs.php';
            }
        }
    </script>
</body>
</html>