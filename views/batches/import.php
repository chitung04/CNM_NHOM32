<?php 
require_once 'views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import lô thuốc từ CSV
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
                            <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Upload file CSV</h5>
                        </div>
                        <div class="card-body">
                            <form action="index.php?page=batches&action=process_import" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Chọn file CSV <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                                    <small class="text-muted">
                                        File CSV phải có định dạng UTF-8 và theo mẫu bên dưới
                                    </small>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="skipFirstRow" name="skip_first_row" checked>
                                    <label class="form-check-label" for="skipFirstRow">
                                        Bỏ qua dòng đầu tiên (header)
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="autoCreateQR" name="auto_create_qr" checked>
                                    <label class="form-check-label" for="autoCreateQR">
                                        Tự động tạo QR code cho các lô mới
                                    </label>
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
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h3 class="text-success"><?= $result['success'] ?? 0 ?></h3>
                                        <p class="text-muted">Thành công</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="text-danger"><?= $result['failed'] ?? 0 ?></h3>
                                        <p class="text-muted">Thất bại</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="text-primary"><?= $result['total'] ?? 0 ?></h3>
                                        <p class="text-muted">Tổng cộng</p>
                                    </div>
                                </div>

                                <?php if (!empty($result['errors'])): ?>
                                    <hr>
                                    <h6 class="text-danger">Chi tiết lỗi:</h6>
                                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Dòng</th>
                                                    <th>Lỗi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($result['errors'] as $error): ?>
                                                    <tr>
                                                        <td><?= $error['row'] ?></td>
                                                        <td><?= htmlspecialchars($error['message']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Debug: Show all result data -->
                                <hr>
                                <details>
                                    <summary>Debug Info (Click để xem)</summary>
                                    <pre><?php print_r($result); ?></pre>
                                </details>
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
                            <h6>Định dạng file CSV:</h6>
                            <p class="small">File CSV phải có các cột theo thứ tự:</p>
                            <ol class="small">
                                <li><strong>medicine_id</strong> - ID thuốc (bắt buộc)</li>
                                <li><strong>batch_number</strong> - Số lô (bắt buộc)</li>
                                <li><strong>quantity</strong> - Số lượng (bắt buộc)</li>
                                <li><strong>import_date</strong> - Ngày nhập (YYYY-MM-DD)</li>
                                <li><strong>expiry_date</strong> - Hạn sử dụng (YYYY-MM-DD)</li>
                                <li><strong>supplier_id</strong> - ID nhà cung cấp</li>
                            </ol>

                            <hr>

                            <h6>Ví dụ:</h6>
                            <pre class="bg-light p-2 small" style="font-size: 11px;">medicine_id,batch_number,quantity,import_date,expiry_date,supplier_id
1,LOT001,100,2026-05-01,2028-05-01,1
2,LOT002,200,2026-05-01,2027-12-31,2
3,LOT003,150,2026-05-01,2028-06-30,1</pre>

                            <hr>

                            <div class="d-grid">
                                <a href="index.php?page=batches&action=download_template" class="btn btn-success">
                                    <i class="bi bi-download me-2"></i>Tải file mẫu CSV
                                </a>
                            </div>

                            <hr>

                            <h6>Lưu ý:</h6>
                            <ul class="small">
                                <li>File phải là định dạng CSV (UTF-8)</li>
                                <li>Dòng đầu tiên là header (tên cột)</li>
                                <li>medicine_id phải tồn tại trong hệ thống</li>
                                <li>Số lô không được trùng</li>
                                <li>Ngày tháng theo định dạng YYYY-MM-DD</li>
                                <li>Số lượng phải là số nguyên dương</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Danh sách thuốc -->
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Danh sách thuốc</h5>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <?php
                            require_once 'models/Medicine.php';
                            $medicineModel = new Medicine();
                            $medicines = $medicineModel->getAll();
                            ?>
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
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
