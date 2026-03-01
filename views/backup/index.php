<?php require_once 'views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= $pageTitle ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" onclick="createBackup()">
                        <i class="bi bi-download"></i> Tạo bản sao lưu mới
                    </button>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- Hướng dẫn -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Hướng dẫn</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>Sao lưu:</strong> Nhấn nút "Tạo bản sao lưu mới" để tạo file backup của toàn bộ cơ sở dữ liệu</li>
                        <li><strong>Khôi phục:</strong> Chọn file backup và nhấn "Khôi phục" để phục hồi dữ liệu (sẽ ghi đè dữ liệu hiện tại)</li>
                        <li><strong>Tải xuống:</strong> Tải file backup về máy để lưu trữ an toàn</li>
                        <li><strong>Lưu ý:</strong> Nên tạo backup thường xuyên và lưu trữ ở nhiều nơi khác nhau</li>
                    </ul>
                </div>
            </div>
            
            <!-- Danh sách backup -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-archive"></i> Danh sách bản sao lưu</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($backups)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Chưa có bản sao lưu nào. Hãy tạo bản sao lưu đầu tiên!
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tên file</th>
                                        <th>Kích thước</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-file-earmark-zip"></i>
                                                <?= htmlspecialchars($backup['name']) ?>
                                            </td>
                                            <td><?= formatFileSize($backup['size']) ?></td>
                                            <td><?= date('d/m/Y H:i:s', $backup['date']) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-success" 
                                                            onclick="restoreBackup('<?= htmlspecialchars($backup['name']) ?>')"
                                                            title="Khôi phục">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                    <a href="index.php?page=backup&action=download&file=<?= urlencode($backup['name']) ?>" 
                                                       class="btn btn-info" title="Tải xuống">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger" 
                                                            onclick="deleteBackup('<?= htmlspecialchars($backup['name']) ?>')"
                                                            title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Form ẩn để restore -->
<form id="restoreForm" method="POST" action="index.php?page=backup&action=restore" style="display: none;">
    <input type="hidden" name="backup_file" id="restoreFile">
</form>

<script>
function createBackup() {
    if (confirm('Bạn có chắc muốn tạo bản sao lưu mới?')) {
        window.location.href = 'index.php?page=backup&action=create';
    }
}

function restoreBackup(filename) {
    if (confirm('CẢNH BÁO: Khôi phục sẽ ghi đè toàn bộ dữ liệu hiện tại!\n\nBạn có chắc muốn khôi phục từ file: ' + filename + '?')) {
        document.getElementById('restoreFile').value = filename;
        document.getElementById('restoreForm').submit();
    }
}

function deleteBackup(filename) {
    if (confirm('Bạn có chắc muốn xóa file backup: ' + filename + '?')) {
        window.location.href = 'index.php?page=backup&action=delete&file=' + encodeURIComponent(filename);
    }
}
</script>

<?php 
// Helper function để format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<?php require_once 'views/layouts/footer.php'; ?>
