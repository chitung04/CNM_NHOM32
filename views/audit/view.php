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
                <a href="index.php?page=audit" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Thông tin chung</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="150">Log ID:</th>
                                    <td><?= $log['log_id'] ?></td>
                                </tr>
                                <tr>
                                    <th>Thời gian:</th>
                                    <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Người dùng:</th>
                                    <td><?= htmlspecialchars($log['full_name']) ?> (<?= htmlspecialchars($log['username']) ?>)</td>
                                </tr>
                                <tr>
                                    <th>Hành động:</th>
                                    <td><span class="badge bg-primary"><?= getActionName($log['action']) ?></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Chi tiết kỹ thuật</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="150">Bảng:</th>
                                    <td><?= getTableName($log['table_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Record ID:</th>
                                    <td><?= $log['record_id'] ?? 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <th>IP Address:</th>
                                    <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                </tr>
                                <tr>
                                    <th>User Agent:</th>
                                    <td><small><?= htmlspecialchars($log['user_agent']) ?></small></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($log['old_values']): ?>
                        <h6>Giá trị cũ</h6>
                        <pre class="bg-light p-3"><?= htmlspecialchars(json_encode(json_decode($log['old_values']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    <?php endif; ?>
                    
                    <?php if ($log['new_values']): ?>
                        <h6>Giá trị mới</h6>
                        <pre class="bg-light p-3"><?= htmlspecialchars(json_encode(json_decode($log['new_values']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
