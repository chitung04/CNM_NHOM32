<?php 
require_once 'views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import lô thuốc từ SQL
                </h1>
                <a href="index.php?page=batches" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Form upload -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Upload file SQL</h5>
                        </div>
                        <div class="card-body">
                            <form action="index.php?page=batches&action=process_import_sql" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Chọn file SQL <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="sql_file" accept=".sql" required>
                                    <small class="text-muted">
                                        File SQL phải chứa câu lệnh INSERT INTO batches
                                    </small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-upload me-2"></i>Upload và Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Kết quả import (nếu có) -->
                    <?php if (isset($_SESSION['import_result'])): ?>
                        <?php $result = $_SESSION['import_result']; unset($_SESSION['import_result']); ?>
                        <div class="card mt-3">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Kết quả import</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success">
                                    <h4><i class="bi bi-check-circle me-2"></i>Import thành công!</h4>
                                    <p class="mb-0"><?= htmlspecialchars($result['message'] ?? 'Đã import dữ liệu') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Hướng dẫn -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Hướng dẫn</h5>
                        </div>
                        <div class="card-body">
                            <h6>Định dạng file SQL:</h6>
                            <p class="small">File SQL phải chứa câu lệnh INSERT:</p>

                            <pre class="bg-light p-2 small" style="font-size: 10px;">INSERT INTO batches 
(medicine_id, batch_number, 
quantity, import_date, 
expiry_date, supplier_id, 
status, pharmacy_id, 
qr_code, created_at) 
VALUES
(1, 'LOT001', 100, 
'2026-05-05', '2028-05-05', 
1, 'active', 1, 
'BATCH_001', NOW());</pre>

                            <hr>

                            <div class="d-grid">
                                <a href="import_batches.sql" class="btn btn-success" download>
                                    <i class="bi bi-download me-2"></i>Tải file SQL mẫu
                                </a>
                            </div>

                            <hr>

                            <h6>Lưu ý:</h6>
                            <ul class="small">
                                <li>File phải là định dạng .sql</li>
                                <li>Chứa câu lệnh INSERT INTO batches</li>
                                <li>medicine_id phải tồn tại</li>
                                <li>batch_number không được trùng</li>
                                <li><strong class="text-danger">pharmacy_id phải đúng với pharmacy của bạn</strong></li>
                            </ul>

                            <hr>

                            <h6>Ưu điểm:</h6>
                            <ul class="small text-success">
                                <li>✅ Nhanh hơn CSV</li>
                                <li>✅ Import hàng trăm lô cùng lúc</li>
                                <li>✅ Không cần validate từng dòng</li>
                                <li>✅ Có thể tạo QR code sau</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Danh sách thuốc của pharmacy hiện tại -->
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Thuốc của bạn</h5>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <?php
                            require_once 'models/Medicine.php';
                            $medicineModel = new Medicine();
                            $medicines = $medicineModel->getAll();
                            
                            if (count($medicines) > 0):
                            ?>
                                <p class="small text-muted">Pharmacy ID: <?= $_SESSION['pharmacy_id'] ?? 'N/A' ?></p>
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên thuốc</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($medicines as $med): ?>
                                            <tr>
                                                <td><strong><?= $med['medicine_id'] ?></strong></td>
                                                <td><?= htmlspecialchars($med['medicine_name']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Chưa có thuốc nào!</strong>
                                    <p class="mb-0 small">Vui lòng thêm thuốc trước khi import lô.</p>
                                    <a href="index.php?page=medicines" class="btn btn-sm btn-warning mt-2">
                                        <i class="bi bi-plus-circle me-1"></i>Thêm thuốc
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
