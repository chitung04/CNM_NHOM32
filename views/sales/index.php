<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-cart-plus me-2"></i>Bán hàng</h2>
    <div>
        <?php if ($currentInvoiceId && !empty($invoiceDetails)): ?>
            <a href="index.php?page=sales&action=checkout" class="btn btn-primary btn-lg me-2">
                <i class="bi bi-credit-card me-2"></i>Thanh toán
            </a>
        <?php endif; ?>
        <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#createOrderModal">
            <i class="bi bi-file-earmark-plus me-2"></i>Tạo đơn hàng
        </button>
    </div>
</div>

<?php if (!empty($pendingInvoices) && count($pendingInvoices) > 1): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Các đơn hàng chưa thanh toán (<?php echo count($pendingInvoices); ?> đơn)
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($pendingInvoices as $invoice): ?>
                        <div class="col-md-4 mb-2">
                            <?php 
                            $isActive = ($invoice['invoice_id'] == $currentInvoiceId);
                            $btnClass = $isActive ? 'btn-primary' : 'btn-outline-primary';
                            ?>
                            <a href="index.php?page=sales&action=switchOrder&invoice_id=<?php echo $invoice['invoice_id']; ?>" 
                               class="btn <?php echo $btnClass; ?> w-100 text-start">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success ms-1">Hiện tại</span>
                                        <?php endif; ?>
                                        <br><small><?php echo $invoice['created_at'] ? date('d/m/Y H:i', strtotime($invoice['created_at'])) : '-'; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <strong><?php echo number_format($invoice['final_amount'], 0, ',', '.'); ?>đ</strong>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($currentInvoiceId && $currentInvoice): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>Đơn hàng hiện tại: <?php echo htmlspecialchars($currentInvoice['invoice_number']); ?>
                    </h5>
                    <div>
                        <a href="index.php?page=sales&action=checkout" class="btn btn-light btn-sm me-2">
                            <i class="bi bi-credit-card me-1"></i>Thanh toán
                        </a>
                        <a href="index.php?page=sales&action=newOrder" class="btn btn-warning btn-sm" 
                           onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                            <i class="bi bi-x-circle me-1"></i>Hủy đơn
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($invoiceDetails)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Chi tiết đơn hàng</h6>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createOrderModal" id="addMoreMedicinesBtn">
                            <i class="bi bi-plus-circle me-1"></i>Thêm thuốc
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên thuốc</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                    <th width="80">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stt = 1;
                                foreach ($invoiceDetails as $detail): 
                                ?>
                                <tr>
                                    <td><?php echo $stt++; ?></td>
                                    <td><?php echo htmlspecialchars($detail['medicine_name']); ?></td>
                                    <td><?php echo number_format($detail['unit_price'], 0, ',', '.'); ?>đ</td>
                                    <td><?php echo $detail['quantity']; ?></td>
                                    <td><?php echo number_format($detail['subtotal'], 0, ',', '.'); ?>đ</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-edit-item" 
                                                    data-detail-id="<?php echo $detail['detail_id'] ?? ''; ?>"
                                                    data-medicine-name="<?php echo htmlspecialchars($detail['medicine_name']); ?>"
                                                    data-quantity="<?php echo $detail['quantity']; ?>"
                                                    title="Sửa số lượng">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-remove-item" 
                                                    data-detail-id="<?php echo $detail['detail_id'] ?? ''; ?>"
                                                    data-medicine-name="<?php echo htmlspecialchars($detail['medicine_name']); ?>"
                                                    title="Xóa khỏi đơn hàng">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="5" class="text-end"><strong>Tổng tiền:</strong></td>
                                    <td><strong><?php echo number_format($currentInvoice['total_amount'], 0, ',', '.'); ?>đ</strong></td>
                                </tr>
                                <?php if ($currentInvoice['discount'] > 0): ?>
                                <tr>
                                    <td colspan="5" class="text-end">Giảm giá:</td>
                                    <td class="text-danger">-<?php echo number_format($currentInvoice['discount'], 0, ',', '.'); ?>đ</td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-success">
                                    <td colspan="5" class="text-end"><strong>Thành tiền:</strong></td>
                                    <td><strong class="text-success"><?php echo number_format($currentInvoice['final_amount'], 0, ',', '.'); ?>đ</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>Đơn hàng trống. Vui lòng thêm sản phẩm vào đơn hàng.
                        <button type="button" class="btn btn-success btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#createOrderModal" id="addFirstMedicineBtn">
                            <i class="bi bi-plus-circle me-1"></i>Thêm thuốc đầu tiên
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Danh sách thuốc</h5></div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <div class="position-relative flex-grow-1">
                        <input type="text" class="form-control border-start-0 border-end-0" id="searchMedicine" 
                               placeholder="Tìm thuốc theo tên, hoạt chất..." autocomplete="off">
                        <!-- Suggestions dropdown -->
                        <div id="searchSuggestions" class="position-absolute w-100 bg-white border rounded shadow-sm" 
                             style="top: 100%; z-index: 1000; display: none; max-height: 300px; overflow-y: auto;">
                        </div>
                    </div>
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search me-1"></i>Tìm
                    </button>
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#categoryFilterModal">
                        <i class="bi bi-funnel me-1"></i>Lọc
                    </button>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Đơn vị</th>
                                <th>Giá</th>
                                <th>Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody id="medicineList">
                            <?php foreach ($medicines as $med): ?>
                                <?php $inventory = $this->medicineModel->getTotalInventory($med['medicine_id']); ?>
                                <tr data-category-id="<?php echo $med['category_id'] ?? ''; ?>">
                                    <td>
                                        <a href="#" class="text-decoration-none medicine-detail-link" 
                                           data-medicine-id="<?php echo $med['medicine_id']; ?>">
                                            <strong class="text-primary"><?php echo htmlspecialchars($med['medicine_name']); ?></strong>
                                        </a>
                                        <?php if (!empty($med['category_name'])): ?>
                                            <br><small class="text-muted"><i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($med['category_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($med['unit_name'] ?? 'Viên'); ?></td>
                                    <td><?php echo number_format($med['price']); ?>đ</td>
                                    <td><span class="badge bg-info"><?php echo $inventory; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo đơn hàng -->
<div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark" id="createOrderModalLabel">
                    <i class="bi bi-file-earmark-plus me-2"></i>Tạo đơn hàng mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Danh sách thuốc -->
                    <div class="col-md-7">
                        <h6 class="mb-3">Chọn thuốc</h6>
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <div class="position-relative flex-grow-1">
                                <input type="text" class="form-control border-start-0 border-end-0" id="modalSearchMedicine" 
                                       placeholder="Tìm thuốc theo tên, hoạt chất..." autocomplete="off">
                                <!-- Modal suggestions dropdown -->
                                <div id="modalSearchSuggestions" class="position-absolute w-100 bg-white border rounded shadow-sm" 
                                     style="top: 100%; z-index: 1050; display: none; max-height: 300px; overflow-y: auto;">
                                </div>
                            </div>
                            <button class="btn btn-primary" type="button" id="modalSearchButton">
                                <i class="bi bi-search me-1"></i>Tìm
                            </button>
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#categoryFilterModal">
                                <i class="bi bi-funnel me-1"></i>Lọc
                            </button>
                        </div>
                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Tên thuốc</th>
                                        <th>Giá</th>
                                        <th>Tồn kho</th>
                                        <th>QR Code</th>
                                        <th width="100">Số lượng</th>
                                        <th width="80">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="modalMedicineList">
                                    <?php foreach ($medicines as $med): ?>
                                        <?php $inventory = $this->medicineModel->getTotalInventory($med['medicine_id']); ?>
                                        <tr data-medicine-id="<?php echo $med['medicine_id']; ?>" data-category-id="<?php echo $med['category_id'] ?? ''; ?>">
                                            <td>
                                                <?php echo htmlspecialchars($med['medicine_name']); ?>
                                                <br><small class="text-muted">Đơn vị: <?php echo htmlspecialchars($med['unit_name'] ?? 'Viên'); ?></small>
                                                <?php if (!empty($med['category_name'])): ?>
                                                    <br><small class="text-info"><i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($med['category_name']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($med['price']); ?>đ</td>
                                            <td><span class="badge bg-info"><?php echo $inventory; ?></span></td>
                                            <td class="text-center">
                                                <?php if (!empty($med['qr_code'])): ?>
                                                    <?php 
                                                    $qrImagePath = 'assets/qrcodes/' . $med['qr_code'] . '.png';
                                                    if (file_exists($qrImagePath)): 
                                                    ?>
                                                        <img src="<?php echo $qrImagePath; ?>" 
                                                             alt="QR" 
                                                             style="width: 30px; height: 30px; cursor: pointer;"
                                                             class="qr-preview-modal"
                                                             data-qr="<?php echo htmlspecialchars($med['qr_code']); ?>"
                                                             title="Click để xem QR">
                                                    <?php else: ?>
                                                        <i class="bi bi-qr-code text-muted" title="QR chưa tạo"></i>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm medicine-quantity" 
                                                       min="0" max="<?php echo $inventory; ?>" value="0"
                                                       data-id="<?php echo $med['medicine_id']; ?>"
                                                       style="width: 70px;">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success add-to-modal-cart" 
                                                        data-id="<?php echo $med['medicine_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($med['medicine_name']); ?>"
                                                        data-price="<?php echo $med['price']; ?>"
                                                        data-unit="<?php echo htmlspecialchars($med['unit_name'] ?? 'Viên'); ?>"
                                                        data-inventory="<?php echo $inventory; ?>">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Giỏ hàng tạm -->
                    <div class="col-md-5">
                        <h6 class="mb-3">Đơn hàng</h6>
                        <div id="modalCart" style="max-height: 300px; overflow-y: auto; min-height: 200px; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px;">
                            <div class="text-center py-5 text-muted" id="emptyCartMessage">
                                <i class="bi bi-cart3" style="font-size: 3rem;"></i>
                                <p class="mt-3">Chưa có sản phẩm nào</p>
                                <small>Chọn thuốc bên trái để thêm vào đơn</small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Tạm tính:</span>
                                <strong id="modalSubtotal">0đ</strong>
                            </div>
                            <div class="mb-2">
                                <label class="form-label mb-1">Giảm giá (VNĐ)</label>
                                <input type="number" class="form-control form-control-sm" id="modalDiscount" 
                                       value="0" min="0" max="0">
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Thành tiền:</strong>
                                <h5 class="mb-0 text-success" id="modalTotalAmount">0đ</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Hủy
                </button>
                <button type="button" class="btn btn-success" id="saveOrderBtn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Lưu đơn hàng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lọc theo danh mục -->
<div class="modal fade" id="categoryFilterModal" tabindex="-1" aria-labelledby="categoryFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark" id="categoryFilterModalLabel">
                    <i class="bi bi-funnel me-2"></i>Lọc theo danh mục
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="filterByCategory('all')">
                        <i class="bi bi-grid me-2"></i>Tất cả danh mục
                    </button>
                </div>
                <div class="row">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-md-6 mb-2">
                            <button type="button" class="btn btn-outline-secondary w-100" 
                                    onclick="filterByCategory(<?php echo $category['category_id']; ?>)">
                                <i class="bi bi-tag me-2"></i><?php echo htmlspecialchars($category['category_name']); ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="resetFilter()">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Bỏ lọc
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chi tiết thuốc -->
<div class="modal fade" id="medicineDetailModal" tabindex="-1" aria-labelledby="medicineDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark" id="medicineDetailModalLabel">
                    <i class="bi bi-capsule me-2"></i>Chi tiết thuốc
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="medicineDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-3">Đang tải thông tin thuốc...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
console.log('Script started');

// Debug: Check if categories are loaded
console.log('Categories available:', <?php echo json_encode($categories); ?>);

// Modal cart data
let modalCartItems = [];
let searchTimeout;
let currentCategoryFilter = 'all';

// Format number
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Load medicine detail
function loadMedicineDetail(medicineId) {
    console.log('Loading medicine detail for ID:', medicineId);
    
    if (!medicineId) {
        console.error('Medicine ID is empty or null');
        return;
    }
    
    const content = document.getElementById('medicineDetailContent');
    if (!content) {
        console.error('Medicine detail content element not found');
        return;
    }
    
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-3">Đang tải thông tin thuốc...</p>
        </div>
    `;
    
    console.log('Sending AJAX request for medicine ID:', medicineId);
    
    fetch('ajax/get_medicine_detail.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'medicine_id=' + encodeURIComponent(medicineId)
    })
    .then(response => {
        console.log('AJAX response received, status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('AJAX response data:', data);
        if (data.success) {
            displayMedicineDetail(data);
        } else {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Lỗi: ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading medicine detail:', error);
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Không thể tải thông tin thuốc. Vui lòng thử lại.
            </div>
        `;
    });
}

// Display medicine detail
function displayMedicineDetail(data) {
    const medicine = data.medicine;
    const batches = data.batches;
    const salesHistory = data.salesHistory;
    const stats = data.statistics;
    
    let html = `
        <div class="row">
            <!-- Thông tin thuốc -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin thuốc</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="200">Tên thuốc:</th>
                                    <td><strong class="text-primary fs-5">${medicine.medicine_name}</strong></td>
                                </tr>
                                <tr>
                                    <th>Danh mục:</th>
                                    <td>
                                        ${medicine.category_name ? 
                                            `<span class="badge bg-info fs-6">${medicine.category_name}</span>` : 
                                            '<span class="text-muted">Chưa phân loại</span>'
                                        }
                                    </td>
                                </tr>
                                <tr>
                                    <th>Đơn vị tính:</th>
                                    <td>${medicine.unit_name || 'Viên'}</td>
                                </tr>
                                <tr>
                                    <th>Giá bán:</th>
                                    <td><strong class="text-success fs-5">${formatNumber(medicine.price)}đ</strong></td>
                                </tr>
                                <tr>
                                    <th>Tổng tồn kho:</th>
                                    <td>
                                        <strong class="fs-5">${data.totalInventory}</strong>
                                        ${data.totalInventory == 0 ? 
                                            '<span class="badge bg-danger ms-2">Hết hàng</span>' :
                                            data.totalInventory < 10 ? 
                                                '<span class="badge bg-warning text-dark ms-2">Sắp hết</span>' :
                                                '<span class="badge bg-success ms-2">Còn hàng</span>'
                                        }
                                    </td>
                                </tr>
                                ${medicine.description ? `
                                <tr>
                                    <th>Mô tả:</th>
                                    <td>${medicine.description}</td>
                                </tr>
                                ` : ''}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Danh sách lô thuốc -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Danh sách lô thuốc</h5>
                    </div>
                    <div class="card-body">
                        ${batches.length > 0 ? `
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Số lô</th>
                                            <th>Nhà cung cấp</th>
                                            <th>Số lượng</th>
                                            <th>Hạn sử dụng</th>
                                            <th>Trạng thái</th>
                                            <th width="180">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${batches.map(batch => {
                                            const canAdd = batch.status === 'active' && batch.quantity > 0 && batch.days_to_expiry > 0;
                                            return `
                                            <tr>
                                                <td><strong>#${batch.batch_id}</strong></td>
                                                <td>${batch.supplier_name || '<span class="text-muted">Không có</span>'}</td>
                                                <td><strong>${batch.quantity}</strong></td>
                                                <td>
                                                    ${new Date(batch.expiry_date).toLocaleDateString('vi-VN')}
                                                    ${batch.days_to_expiry <= 30 && batch.days_to_expiry > 0 ? 
                                                        `<br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Còn ${batch.days_to_expiry} ngày</small>` :
                                                        batch.days_to_expiry <= 0 ?
                                                            '<br><small class="text-danger"><i class="bi bi-x-circle"></i> Đã hết hạn</small>' :
                                                            `<br><small class="text-success"><i class="bi bi-check-circle"></i> Còn ${batch.days_to_expiry} ngày</small>`
                                                    }
                                                </td>
                                                <td>
                                                    ${batch.status === 'active' ? 
                                                        '<span class="badge bg-success">Còn hàng</span>' :
                                                        batch.status === 'expired' ?
                                                            '<span class="badge bg-danger">Hết hạn</span>' :
                                                            '<span class="badge bg-secondary">Hết hàng</span>'
                                                    }
                                                </td>
                                                <td>
                                                    ${canAdd ? `
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" 
                                                                   class="form-control batch-quantity-input" 
                                                                   min="1" 
                                                                   max="${batch.quantity}" 
                                                                   value="1"
                                                                   style="width: 70px;"
                                                                   data-batch-id="${batch.batch_id}"
                                                                   data-medicine-id="${data.medicine.medicine_id}"
                                                                   data-medicine-name="${data.medicine.medicine_name}"
                                                                   data-price="${data.medicine.price}"
                                                                   data-max="${batch.quantity}">
                                                            <button class="btn btn-success btn-add-from-batch" 
                                                                    data-batch-id="${batch.batch_id}"
                                                                    title="Thêm vào giỏ hàng">
                                                                <i class="bi bi-plus-circle"></i>
                                                            </button>
                                                        </div>
                                                    ` : `
                                                        <span class="text-muted small">Không khả dụng</span>
                                                    `}
                                                </td>
                                            </tr>
                                        `}).join('')}
                                    </tbody>
                                </table>
                            </div>
                        ` : `
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>Không có lô thuốc nào trong kho.
                            </div>
                        `}
                    </div>
                </div>

                <!-- Lịch sử bán hàng -->
                ${salesHistory.length > 0 ? `
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Lịch sử bán hàng gần đây</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Số hóa đơn</th>
                                        <th>Ngày bán</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${salesHistory.map(sale => `
                                        <tr>
                                            <td><strong>${sale.invoice_number}</strong></td>
                                            <td>${new Date(sale.created_at).toLocaleDateString('vi-VN')}</td>
                                            <td>${sale.quantity}</td>
                                            <td>${formatNumber(sale.unit_price)}đ</td>
                                            <td><strong>${formatNumber(sale.subtotal)}đ</strong></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>

            <!-- Thống kê và QR Code -->
            <div class="col-md-4">
                <!-- Thống kê -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Thống kê</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Tổng tồn kho</small>
                            <h4 class="mb-0 text-primary">${data.totalInventory}</h4>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Đã bán (tổng)</small>
                            <h4 class="mb-0 text-info">${stats.totalSold}</h4>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Doanh thu (tổng)</small>
                            <h4 class="mb-0 text-success">${formatNumber(stats.totalRevenue)}đ</h4>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Số lô thuốc</small>
                            <h4 class="mb-0">${batches.length}</h4>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                ${medicine.qr_code ? `
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>Mã QR - Google Search</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle"></i>
                            <strong>QR Code Google Search:</strong> Quét bằng điện thoại để tìm thông tin chính thống về thuốc này.
                        </div>
                        
                        <img src="assets/qrcodes/${medicine.qr_code}.png" 
                             alt="QR Code" 
                             class="img-fluid mb-3"
                             style="max-width: 200px; border: 2px solid #dee2e6; border-radius: 10px;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        
                        <div class="alert alert-warning" style="display: none;">
                            <i class="bi bi-exclamation-triangle"></i>
                            QR Code chưa được tạo
                        </div>
                        
                        <p class="text-muted small mb-2">
                            <code>${medicine.qr_code}</code>
                        </p>
                        
                        <small class="text-muted d-block">
                            <i class="bi bi-phone"></i> Quét bằng điện thoại để tự động mở Google Search
                        </small>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('medicineDetailContent').innerHTML = html;
}

// Apply combined filters (search + category)
function applyFilters(tableId, searchValue = '') {
    console.log(`Applying filters to ${tableId}: search="${searchValue}", category="${currentCategoryFilter}"`);
    
    let table, rows;
    
    if (tableId === 'medicineList') {
        // Main table - get tbody
        table = document.getElementById(tableId);
        if (!table) {
            console.error('Table not found:', tableId);
            return;
        }
        rows = table.getElementsByTagName('tr');
    } else if (tableId === 'modalMedicineList') {
        // Modal table - get tbody
        table = document.getElementById(tableId);
        if (!table) {
            console.error('Table not found:', tableId);
            return;
        }
        rows = table.getElementsByTagName('tr');
    } else {
        console.error('Unknown table ID:', tableId);
        return;
    }
    
    let visibleCount = 0;
    let firstVisibleRow = null;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;
        const categoryId = row.getAttribute('data-category-id');
        
        // Check search filter
        const matchesSearch = !searchValue || text.toLowerCase().indexOf(searchValue.toLowerCase()) > -1;
        
        // Check category filter
        const matchesCategory = currentCategoryFilter === 'all' || categoryId == currentCategoryFilter;
        
        // Show row only if it matches both filters
        if (matchesSearch && matchesCategory) {
            row.style.display = '';
            visibleCount++;
            
            // Remember first visible row for auto-scroll
            if (!firstVisibleRow) {
                firstVisibleRow = row;
            }
        } else {
            row.style.display = 'none';
        }
    }
    
    console.log(`Filter applied: ${visibleCount} rows visible out of ${rows.length}`);
    
    // Auto-scroll to first result if searching and found results
    if (searchValue && visibleCount > 0 && firstVisibleRow) {
        setTimeout(() => {
            firstVisibleRow.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            // Highlight the first result briefly
            firstVisibleRow.style.backgroundColor = '#e3f2fd';
            setTimeout(() => {
                firstVisibleRow.style.backgroundColor = '';
            }, 2000);
        }, 100);
    }
    
    // Show "no results" message if needed
    showNoResultsMessage(tableId, visibleCount === 0 && (searchValue !== '' || currentCategoryFilter !== 'all'));
}

// Show/hide no results message
function showNoResultsMessage(tableId, show) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let noResultsRow = table.querySelector('.no-results-row');
    
    if (show) {
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            noResultsRow.innerHTML = `
                <td colspan="4" class="text-center py-4 text-muted">
                    <i class="bi bi-search me-2"></i>
                    Không tìm thấy thuốc nào phù hợp
                </td>
            `;
            table.appendChild(noResultsRow);
        }
        noResultsRow.style.display = '';
    } else {
        if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }
}

// Format number
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Filter by category
function filterByCategory(categoryId) {
    console.log('Filtering by category:', categoryId);
    console.log('Available rows with category data:');
    
    // Debug: Check all rows and their category IDs
    const mainRows = document.querySelectorAll('#medicineList tr[data-category-id]');
    const modalRows = document.querySelectorAll('#modalMedicineList tr[data-category-id]');
    
    console.log('Main table rows with category-id:', mainRows.length);
    mainRows.forEach((row, index) => {
        if (index < 5) { // Show first 5 for debugging
            console.log(`Row ${index}: category-id = "${row.getAttribute('data-category-id')}", text = "${row.textContent.substring(0, 50)}..."`);
        }
    });
    
    console.log('Modal table rows with category-id:', modalRows.length);
    modalRows.forEach((row, index) => {
        if (index < 5) { // Show first 5 for debugging
            console.log(`Modal Row ${index}: category-id = "${row.getAttribute('data-category-id')}", text = "${row.textContent.substring(0, 50)}..."`);
        }
    });
    
    // Update current filter
    currentCategoryFilter = categoryId;
    
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('categoryFilterModal'));
    if (modal) {
        modal.hide();
    }
    
    // Update filter button text to show active filter
    const filterButtons = document.querySelectorAll('[data-bs-target="#categoryFilterModal"]');
    filterButtons.forEach(btn => {
        if (categoryId === 'all') {
            btn.innerHTML = '<i class="bi bi-funnel me-1"></i>Lọc';
            btn.className = 'btn btn-outline-secondary';
        } else {
            // Find category name
            const categoryButton = document.querySelector(`button[onclick="filterByCategory(${categoryId})"]`);
            const categoryName = categoryButton ? categoryButton.textContent.trim().replace(/🏷️/g, '').replace(/\s+/g, ' ').trim() : 'Danh mục';
            btn.innerHTML = '<i class="bi bi-funnel-fill me-1"></i>' + categoryName;
            btn.className = 'btn btn-info text-white';
        }
    });
    
    // Apply filters to both tables
    const mainSearchValue = document.getElementById('searchMedicine').value;
    const modalSearchValue = document.getElementById('modalSearchMedicine').value;
    
    console.log('Applying filters after category change...');
    applyFilters('medicineList', mainSearchValue);
    applyFilters('modalMedicineList', modalSearchValue);
    
    console.log('Category filter applied successfully');
}

// Reset all filters
function resetFilter() {
    console.log('Resetting all filters');
    
    // Reset category filter
    currentCategoryFilter = 'all';
    
    // Clear search inputs
    document.getElementById('searchMedicine').value = '';
    document.getElementById('modalSearchMedicine').value = '';
    
    // Hide suggestions
    document.getElementById('searchSuggestions').style.display = 'none';
    document.getElementById('modalSearchSuggestions').style.display = 'none';
    
    // Update filter button text
    const filterButtons = document.querySelectorAll('[data-bs-target="#categoryFilterModal"]');
    filterButtons.forEach(btn => {
        btn.innerHTML = '<i class="bi bi-funnel me-1"></i>Lọc';
        btn.className = 'btn btn-outline-secondary';
    });
    
    // Apply filters (will show all items)
    applyFilters('medicineList', '');
    applyFilters('modalMedicineList', '');
    
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('categoryFilterModal'));
    if (modal) {
        modal.hide();
    }
    
    console.log('All filters reset successfully');
}

// AJAX search suggestions for main search
function showSuggestions(keyword, targetSuggestions) {
    if (keyword.length < 2) {
        document.getElementById(targetSuggestions).style.display = 'none';
        return;
    }
    
    console.log('Searching suggestions for:', keyword, 'target:', targetSuggestions);
    
    fetch('ajax/search_medicine.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'keyword=' + encodeURIComponent(keyword)
    })
    .then(response => {
        console.log('Suggestions response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Suggestions response data:', data);
        
        const suggestionsDiv = document.getElementById(targetSuggestions);
        if (!suggestionsDiv) {
            console.error('Suggestions div not found:', targetSuggestions);
            return;
        }
        
        if (data.success && data.medicines && data.medicines.length > 0) {
            let html = '';
            data.medicines.slice(0, 5).forEach(function(medicine) { // Limit to 5 suggestions
                const inventory = medicine.inventory || 0;
                const stockClass = inventory < 10 ? 'text-danger' : 'text-success';
                
                html += `
                    <div class="suggestion-item p-2 border-bottom" style="cursor: pointer;" 
                         data-id="${medicine.medicine_id}" data-name="${medicine.medicine_name}">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${medicine.medicine_name}</strong>
                                <br><small class="text-muted">${medicine.category_name || 'Chưa phân loại'}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary">${new Intl.NumberFormat('vi-VN').format(medicine.price)}đ</div>
                                <small class="${stockClass}">Tồn: ${inventory}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            suggestionsDiv.innerHTML = html;
            suggestionsDiv.style.display = 'block';
            console.log('Suggestions displayed successfully');
        } else {
            suggestionsDiv.innerHTML = '<div class="p-2 text-muted text-center">Không tìm thấy gợi ý</div>';
            suggestionsDiv.style.display = 'block';
            console.log('No suggestions found');
        }
    })
    .catch(error => {
        console.error('Suggestions search error:', error);
        document.getElementById(targetSuggestions).style.display = 'none';
    });
}

// Update modal cart display
function updateModalCart() {
    console.log('updateModalCart called, items:', modalCartItems);
    
    const cartDiv = document.getElementById('modalCart');
    const saveBtn = document.getElementById('saveOrderBtn');
    const discountInput = document.getElementById('modalDiscount');
    const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;
    
    if (modalCartItems.length === 0) {
        cartDiv.innerHTML = `
            <div class="text-center py-5 text-muted" id="emptyCartMessage">
                <i class="bi bi-cart3" style="font-size: 3rem;"></i>
                <p class="mt-3">Chưa có sản phẩm nào</p>
                <small>Chọn thuốc bên trái để thêm vào đơn</small>
            </div>
        `;
        saveBtn.disabled = true;
        document.getElementById('modalSubtotal').textContent = '0đ';
        document.getElementById('modalTotalAmount').textContent = '0đ';
        return;
    }
    
    saveBtn.disabled = false;
    
    let html = '';
    let subtotal = 0;
    
    modalCartItems.forEach((item, index) => {
        const itemSubtotal = item.price * item.quantity;
        subtotal += itemSubtotal;
        
        html += '<div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">';
        html += '<div class="flex-grow-1">';
        html += '<strong>' + item.name + '</strong><br>';
        html += '<small class="text-muted">' + formatNumber(item.price) + 'đ × ' + item.quantity + ' ' + item.unit + '</small>';
        html += '</div>';
        html += '<div class="text-end">';
        html += '<strong class="text-success">' + formatNumber(itemSubtotal) + 'đ</strong><br>';
        html += '<div class="btn-group btn-group-sm mt-1">';
        html += '<button class="btn btn-outline-primary btn-edit-cart" data-index="' + index + '" title="Sửa số lượng">';
        html += '<i class="bi bi-pencil"></i>';
        html += '</button>';
        html += '<button class="btn btn-outline-danger btn-remove-cart" data-index="' + index + '" title="Xóa">';
        html += '<i class="bi bi-trash"></i>';
        html += '</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    });
    
    cartDiv.innerHTML = html;
    
    // Cập nhật tính tiền
    const total = subtotal - discount;
    document.getElementById('modalSubtotal').textContent = formatNumber(subtotal) + 'đ';
    document.getElementById('modalTotalAmount').textContent = formatNumber(total) + 'đ';
    
    // Giới hạn giảm giá không vượt quá tổng tiền
    if (discountInput) {
        discountInput.max = subtotal;
    }
}

// Event delegation for all clicks
document.addEventListener('click', function(e) {
    // Medicine detail link
    if (e.target.closest('.medicine-detail-link')) {
        e.preventDefault();
        const link = e.target.closest('.medicine-detail-link');
        
        // Check if the parent row is visible (not filtered out)
        const parentRow = link.closest('tr');
        if (parentRow && parentRow.style.display === 'none') {
            console.log('Medicine detail link clicked but row is hidden by filter');
            return; // Don't process if row is hidden
        }
        
        const medicineId = link.getAttribute('data-medicine-id');
        console.log('Medicine detail clicked for ID:', medicineId);
        
        if (medicineId) {
            // Manually show the modal
            const modal = new bootstrap.Modal(document.getElementById('medicineDetailModal'));
            modal.show();
            
            // Load the medicine detail
            loadMedicineDetail(medicineId);
        } else {
            console.error('No medicine ID found');
        }
    }
    
    // Click on QR preview in modal
    if (e.target.closest('.qr-preview-modal')) {
        const img = e.target.closest('.qr-preview-modal');
        const qrCode = img.getAttribute('data-qr');
        
        if (qrCode) {
            window.open('public_medicine_info.php?qr=' + encodeURIComponent(qrCode), '_blank');
        }
    }
    
    // Add to cart button
    if (e.target.closest('.add-to-modal-cart')) {
        console.log('Add button clicked!');
        
        const btn = e.target.closest('.add-to-modal-cart');
        
        // Check if the parent row is visible (not filtered out)
        const parentRow = btn.closest('tr');
        if (parentRow && parentRow.style.display === 'none') {
            console.log('Add to cart button clicked but row is hidden by filter');
            return; // Don't process if row is hidden
        }
        
        const id = parseInt(btn.getAttribute('data-id'));
        const name = btn.getAttribute('data-name');
        const price = parseFloat(btn.getAttribute('data-price'));
        const unit = btn.getAttribute('data-unit');
        const inventory = parseInt(btn.getAttribute('data-inventory'));
        
        console.log('Medicine data:', {id, name, price, unit, inventory});
        
        const quantityInput = document.querySelector('input.medicine-quantity[data-id="' + id + '"]');
        let quantity = parseInt(quantityInput.value);
        
        console.log('Quantity:', quantity);
        
        if (isNaN(quantity) || quantity < 1) {
            alert('Vui lòng nhập số lượng lớn hơn 0');
            quantityInput.focus();
            return;
        }
        
        if (quantity > inventory) {
            alert('Số lượng vượt quá tồn kho (' + inventory + ' ' + unit + ')');
            quantityInput.value = 0;
            return;
        }
        
        // Check if already in cart
        const existingIndex = modalCartItems.findIndex(item => item.id === id);
        
        if (existingIndex >= 0) {
            const newQuantity = modalCartItems[existingIndex].quantity + quantity;
            if (newQuantity > inventory) {
                alert('Tổng số lượng vượt quá tồn kho (' + inventory + ' ' + unit + ')');
                return;
            }
            modalCartItems[existingIndex].quantity = newQuantity;
            console.log('Updated existing item');
        } else {
            modalCartItems.push({
                id: id,
                name: name,
                price: price,
                unit: unit,
                quantity: quantity,
                inventory: inventory
            });
            console.log('Added new item');
        }
        
        updateModalCart();
        quantityInput.value = 0;
    }
    
    // Edit cart item
    if (e.target.closest('.btn-edit-cart')) {
        const index = parseInt(e.target.closest('.btn-edit-cart').getAttribute('data-index'));
        const item = modalCartItems[index];
        const newQuantity = prompt('Nhập số lượng mới cho ' + item.name + '\n(Tồn kho: ' + item.inventory + ' ' + item.unit + ')', item.quantity);
        
        if (newQuantity !== null) {
            const qty = parseInt(newQuantity);
            if (qty > 0 && qty <= item.inventory) {
                modalCartItems[index].quantity = qty;
                updateModalCart();
            } else if (qty > item.inventory) {
                alert('Số lượng vượt quá tồn kho (' + item.inventory + ' ' + item.unit + ')');
            } else {
                alert('Số lượng phải lớn hơn 0');
            }
        }
    }
    
    // Remove cart item
    if (e.target.closest('.btn-remove-cart')) {
        const index = parseInt(e.target.closest('.btn-remove-cart').getAttribute('data-index'));
        modalCartItems.splice(index, 1);
        updateModalCart();
    }
    
    // Add from batch in medicine detail modal
    if (e.target.closest('.btn-add-from-batch')) {
        const btn = e.target.closest('.btn-add-from-batch');
        const batchId = btn.getAttribute('data-batch-id');
        
        // Find the input for this batch
        const input = document.querySelector(`.batch-quantity-input[data-batch-id="${batchId}"]`);
        if (!input) {
            console.error('Input not found for batch:', batchId);
            return;
        }
        
        const quantity = parseInt(input.value);
        const medicineId = parseInt(input.getAttribute('data-medicine-id'));
        const medicineName = input.getAttribute('data-medicine-name');
        const price = parseFloat(input.getAttribute('data-price'));
        const maxQuantity = parseInt(input.getAttribute('data-max'));
        
        console.log('Adding from batch:', {batchId, quantity, medicineId, medicineName, price, maxQuantity});
        
        // Validate quantity
        if (isNaN(quantity) || quantity < 1) {
            alert('Vui lòng nhập số lượng lớn hơn 0');
            input.focus();
            return;
        }
        
        if (quantity > maxQuantity) {
            alert(`Số lượng vượt quá tồn kho lô này (${maxQuantity})`);
            input.value = maxQuantity;
            return;
        }
        
        // Disable button and show loading
        btn.disabled = true;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        // Call AJAX to add to cart
        fetch('ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `medicine_id=${encodeURIComponent(medicineId)}&quantity=${encodeURIComponent(quantity)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert(`✅ Đã thêm ${quantity} ${medicineName} vào đơn hàng!`);
                
                // Reset input
                input.value = 1;
                
                // Close modal and reload page to show updated cart
                const modal = bootstrap.Modal.getInstance(document.getElementById('medicineDetailModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Reload page to show updated order
                location.reload();
            } else {
                alert('❌ ' + (data.message || 'Không thể thêm sản phẩm'));
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Add to cart error:', error);
            alert('❌ Lỗi kết nối. Vui lòng thử lại.');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    }
});

// Search functionality outside modal
function performOutsideSearch() {
    const searchValue = document.getElementById('searchMedicine').value.trim();
    console.log('Searching outside for:', searchValue);
    
    // Apply combined filters
    applyFilters('medicineList', searchValue);
}

// Search functionality in modal
function performModalSearch() {
    const searchValue = document.getElementById('modalSearchMedicine').value.trim();
    console.log('Searching in modal for:', searchValue);
    
    // Apply combined filters
    applyFilters('modalMedicineList', searchValue);
}

// Main search input events
document.getElementById('searchMedicine').addEventListener('input', function() {
    const keyword = this.value.trim();
    console.log('Main search input changed to:', keyword);
    clearTimeout(searchTimeout);
    
    // Show suggestions if keyword is long enough
    if (keyword.length >= 2) {
        searchTimeout = setTimeout(() => showSuggestions(keyword, 'searchSuggestions'), 300);
    } else {
        document.getElementById('searchSuggestions').style.display = 'none';
    }
});

// Modal search input events
document.getElementById('modalSearchMedicine').addEventListener('input', function() {
    const keyword = this.value.trim();
    console.log('Modal search input changed to:', keyword);
    clearTimeout(searchTimeout);
    
    // Show suggestions if keyword is long enough
    if (keyword.length >= 2) {
        searchTimeout = setTimeout(() => showSuggestions(keyword, 'modalSearchSuggestions'), 300);
    } else {
        document.getElementById('modalSearchSuggestions').style.display = 'none';
    }
});

// Click on suggestion - main search
document.addEventListener('click', function(e) {
    if (e.target.closest('#searchSuggestions .suggestion-item')) {
        const item = e.target.closest('.suggestion-item');
        const name = item.getAttribute('data-name');
        document.getElementById('searchMedicine').value = name;
        document.getElementById('searchSuggestions').style.display = 'none';
        performOutsideSearch();
    }
    
    // Click on suggestion - modal search
    if (e.target.closest('#modalSearchSuggestions .suggestion-item')) {
        const item = e.target.closest('.suggestion-item');
        const name = item.getAttribute('data-name');
        document.getElementById('modalSearchMedicine').value = name;
        document.getElementById('modalSearchSuggestions').style.display = 'none';
        performModalSearch();
    }
    
    // Hide suggestions when clicking outside
    if (!e.target.closest('.position-relative') && !e.target.closest('.suggestion-item')) {
        document.getElementById('searchSuggestions').style.display = 'none';
        document.getElementById('modalSearchSuggestions').style.display = 'none';
    }
});

// QR Code scanning - main search
// Removed QR scanning functionality for web interface

// QR Code scanning - modal search  
// Removed QR scanning functionality for web interface

// Outside search - button click
document.getElementById('searchButton').addEventListener('click', function() {
    const keyword = document.getElementById('searchMedicine').value.trim();
    console.log('Main search button clicked with:', keyword);
    performOutsideSearch();
    document.getElementById('searchSuggestions').style.display = 'none';
});

// Outside search - Enter key
document.getElementById('searchMedicine').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const keyword = this.value.trim();
        console.log('Main search Enter pressed with:', keyword);
        performOutsideSearch();
        document.getElementById('searchSuggestions').style.display = 'none';
    }
});

// Modal search - button click
document.getElementById('modalSearchButton').addEventListener('click', function() {
    const keyword = document.getElementById('modalSearchMedicine').value.trim();
    console.log('Modal search button clicked with:', keyword);
    performModalSearch();
    document.getElementById('modalSearchSuggestions').style.display = 'none';
});

// Modal search - Enter key
document.getElementById('modalSearchMedicine').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const keyword = this.value.trim();
        console.log('Modal search Enter pressed with:', keyword);
        performModalSearch();
        document.getElementById('modalSearchSuggestions').style.display = 'none';
    }
});

// Update total when discount changes
document.getElementById('modalDiscount').addEventListener('input', function() {
    updateModalCart();
});

// Save order button
document.getElementById('saveOrderBtn').addEventListener('click', function() {
    console.log('Save button clicked');
    console.log('Cart items:', modalCartItems);
    
    if (modalCartItems.length === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm');
        return;
    }
    
    const discount = parseFloat(document.getElementById('modalDiscount').value) || 0;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';
    
    const requestData = {
        items: modalCartItems,
        discount: discount
    };
    
    console.log('Sending request:', requestData);
    console.log('URL:', window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/ajax/create_order_with_items.php');
    
    fetch('ajax/create_order_with_items.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Đọc response as text trước để debug
        return response.text().then(text => {
            console.log('Response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                throw new Error('Server trả về không phải JSON: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Đã tạo đơn hàng thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể tạo đơn hàng'));
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Lưu đơn hàng';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Lỗi: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Lưu đơn hàng';
    });
});

// Reset modal when closed
document.getElementById('createOrderModal').addEventListener('hidden.bs.modal', function() {
    modalCartItems = [];
    document.getElementById('modalDiscount').value = 0;
    updateModalCart();
    document.getElementById('modalSearchMedicine').value = '';
    const quantityInputs = document.querySelectorAll('.medicine-quantity');
    quantityInputs.forEach(input => input.value = 0);
});

// Load current order items when "Add More Medicines" button is clicked
document.getElementById('addMoreMedicinesBtn')?.addEventListener('click', function() {
    console.log('Add More Medicines button clicked');
    
    // Load current order items into modal cart
    loadCurrentOrderToModal();
});

// Load current order items when "Add First Medicine" button is clicked
document.getElementById('addFirstMedicineBtn')?.addEventListener('click', function() {
    console.log('Add First Medicine button clicked');
    
    // Clear modal cart for new order
    modalCartItems = [];
    document.getElementById('modalDiscount').value = 0;
    updateModalCart();
});

// Handle edit and remove buttons in current order
document.addEventListener('click', function(e) {
    // Edit item quantity
    if (e.target.closest('.btn-edit-item')) {
        const btn = e.target.closest('.btn-edit-item');
        const detailId = btn.getAttribute('data-detail-id');
        const medicineName = btn.getAttribute('data-medicine-name');
        const currentQuantity = btn.getAttribute('data-quantity');
        
        const newQuantity = prompt(`Nhập số lượng mới cho "${medicineName}":`, currentQuantity);
        
        if (newQuantity !== null && newQuantity !== currentQuantity) {
            const qty = parseInt(newQuantity);
            if (qty > 0) {
                updateOrderItemQuantity(detailId, qty);
            } else if (qty === 0) {
                if (confirm(`Xóa "${medicineName}" khỏi đơn hàng?`)) {
                    removeOrderItem(detailId);
                }
            } else {
                alert('Số lượng phải lớn hơn hoặc bằng 0');
            }
        }
    }
    
    // Remove item
    if (e.target.closest('.btn-remove-item')) {
        const btn = e.target.closest('.btn-remove-item');
        const detailId = btn.getAttribute('data-detail-id');
        const medicineName = btn.getAttribute('data-medicine-name');
        
        if (confirm(`Bạn có chắc muốn xóa "${medicineName}" khỏi đơn hàng?`)) {
            removeOrderItem(detailId);
        }
    }
});

// Function to update order item quantity
function updateOrderItemQuantity(detailId, newQuantity) {
    fetch('ajax/update_cart_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `detail_id=${encodeURIComponent(detailId)}&quantity=${encodeURIComponent(newQuantity)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show updated order
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật số lượng'));
        }
    })
    .catch(error => {
        console.error('Update quantity error:', error);
        alert('Lỗi kết nối. Vui lòng thử lại.');
    });
}

// Function to remove order item
function removeOrderItem(detailId) {
    fetch('ajax/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `detail_id=${encodeURIComponent(detailId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show updated order
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa sản phẩm'));
        }
    })
    .catch(error => {
        console.error('Remove item error:', error);
        alert('Lỗi kết nối. Vui lòng thử lại.');
    });
}

// Function to load current order items into modal cart
function loadCurrentOrderToModal() {
    // Get current order items from the displayed table
    const currentOrderTable = document.querySelector('.card-body table tbody');
    if (!currentOrderTable) {
        console.log('No current order table found');
        return;
    }
    
    // Clear existing modal cart
    modalCartItems = [];
    
    // Extract items from current order table
    const rows = currentOrderTable.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        
        if (cells.length >= 6) { // Updated to 6 columns (added action column)
            const medicineName = cells[1].textContent.trim();
            const unitPriceText = cells[2].textContent.replace(/[^\d]/g, '');
            const unitPrice = parseInt(unitPriceText) || 0;
            const quantity = parseInt(cells[3].textContent.trim()) || 0;
            
            if (medicineName && unitPrice > 0 && quantity > 0) {
                // Try to find medicine ID from the main medicine list
                const medicineRows = document.querySelectorAll('#medicineList tr');
                let medicineId = null;
                let unit = 'Viên';
                let inventory = 999; // Default high inventory
                
                for (let j = 0; j < medicineRows.length; j++) {
                    const medicineRow = medicineRows[j];
                    const nameLink = medicineRow.querySelector('.medicine-detail-link strong');
                    if (nameLink && nameLink.textContent.trim() === medicineName) {
                        medicineId = medicineRow.querySelector('.medicine-detail-link').getAttribute('data-medicine-id');
                        const unitCell = medicineRow.cells[1];
                        if (unitCell) unit = unitCell.textContent.trim();
                        const inventoryBadge = medicineRow.querySelector('.badge.bg-info');
                        if (inventoryBadge) inventory = parseInt(inventoryBadge.textContent.trim()) || 999;
                        break;
                    }
                }
                
                // Add to modal cart
                modalCartItems.push({
                    id: medicineId || Date.now() + i, // Use timestamp as fallback ID
                    name: medicineName,
                    price: unitPrice,
                    unit: unit,
                    quantity: quantity,
                    inventory: inventory
                });
                
                console.log('Added to modal cart:', medicineName, unitPrice, quantity);
            }
        }
    }
    
    // Get current discount
    const discountRows = document.querySelectorAll('.card-body table tfoot tr');
    let currentDiscount = 0;
    for (let i = 0; i < discountRows.length; i++) {
        const row = discountRows[i];
        if (row.textContent.includes('Giảm giá')) {
            const discountText = row.cells[1].textContent.replace(/[^\d]/g, '');
            currentDiscount = parseInt(discountText) || 0;
            break;
        }
    }
    
    // Set discount in modal
    document.getElementById('modalDiscount').value = currentDiscount;
    
    // Update modal cart display
    updateModalCart();
    
    console.log('Loaded current order to modal:', modalCartItems.length, 'items, discount:', currentDiscount);
}

// Reset category filter when modal is opened
document.getElementById('categoryFilterModal').addEventListener('show.bs.modal', function() {
    // Highlight current active filter
    const buttons = this.querySelectorAll('button[onclick^="filterByCategory"]');
    buttons.forEach(btn => {
        btn.classList.remove('btn-primary', 'btn-outline-secondary');
        const categoryId = btn.getAttribute('onclick').match(/filterByCategory\(([^)]+)\)/)[1];
        if ((categoryId === "'all'" && currentCategoryFilter === 'all') || 
            (categoryId != "'all'" && categoryId == currentCategoryFilter)) {
            btn.classList.add('btn-primary');
        } else {
            btn.classList.add('btn-outline-secondary');
        }
    });
});

console.log('Script loaded successfully');
</script>

<?php require_once 'views/layouts/footer.php'; ?>
