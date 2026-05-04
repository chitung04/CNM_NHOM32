<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-qr-code me-2"></i>Quản lý mã QR thuốc</h2>
    <div>
        <a href="tools/regenerate_qr_codes.php" class="btn btn-success me-2" target="_blank">
            <i class="bi bi-arrow-clockwise me-2"></i>Tạo lại tất cả QR
        </a>
        <a href="index.php?page=medicines" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Danh sách mã QR</h5>
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
        <div class="row" id="qrCodesGrid">
            <?php if (empty($medicines)): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Chưa có thuốc nào
                </div>
            <?php else: ?>
                <?php foreach ($medicines as $medicine): ?>
                    <div class="col-md-6 col-lg-4 mb-4 medicine-item">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-truncate" title="<?php echo htmlspecialchars($medicine['medicine_name']); ?>">
                                    <?php echo htmlspecialchars($medicine['medicine_name']); ?>
                                </h6>
                                <small class="text-muted">ID: <?php echo $medicine['medicine_id']; ?></small>
                            </div>
                            <div class="card-body text-center">
                                <?php if (!empty($medicine['qr_code'])): ?>
                                    <?php 
                                    $qrPath = 'assets/qrcodes/' . $medicine['qr_code'] . '.png';
                                    if (file_exists($qrPath)): 
                                    ?>
                                        <img src="<?php echo $qrPath; ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 150px;">
                                        <div class="mb-2">
                                            <small class="text-muted">Mã QR:</small><br>
                                            <code class="small"><?php echo $medicine['qr_code']; ?></code>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted mb-3">
                                            <i class="bi bi-exclamation-triangle fs-1"></i>
                                            <p>File QR không tồn tại</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-qr-code fs-1"></i>
                                        <p>Chưa có mã QR</p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-2">
                                    <small class="text-muted">Giá:</small>
                                    <strong class="text-success"><?php echo number_format($medicine['price'], 0, ',', '.'); ?>đ</strong>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Tồn kho:</small>
                                    <span class="badge bg-<?php echo $medicine['inventory'] < 10 ? 'danger' : 'success'; ?>">
                                        <?php echo $medicine['inventory']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="btn-group w-100">
                                    <?php if (!empty($medicine['qr_code'])): ?>
                                        <a href="medicine_info.php?qr=<?php echo urlencode($medicine['qr_code']); ?>" 
                                           class="btn btn-outline-info btn-sm" target="_blank" title="Xem thông tin thuốc (không cần quét QR)">
                                            <i class="bi bi-link-45deg"></i>
                                        </a>
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="downloadQR('<?php echo $medicine['qr_code']; ?>', '<?php echo htmlspecialchars($medicine['medicine_name']); ?>')"
                                                title="Tải QR">
                                            <i class="bi bi-download"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="printQR('<?php echo $medicine['qr_code']; ?>', '<?php echo htmlspecialchars($medicine['medicine_name']); ?>')"
                                                title="In QR">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-warning btn-sm" 
                                            onclick="regenerateQR(<?php echo $medicine['medicine_id']; ?>)"
                                            title="Tạo lại QR">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Search functionality
function performSearch() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('.medicine-item');
    
    let visibleCount = 0;
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (searchValue === '' || text.indexOf(searchValue) > -1) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    console.log('Found', visibleCount, 'matching items');
}

// Search events
document.getElementById('searchButton').addEventListener('click', performSearch);
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') performSearch();
});
document.getElementById('searchInput').addEventListener('input', performSearch);

// Download QR code
function downloadQR(qrCode, medicineName) {
    const link = document.createElement('a');
    link.href = 'assets/qrcodes/' + qrCode + '.png';
    link.download = 'QR_' + medicineName.replace(/[^a-zA-Z0-9]/g, '_') + '_' + qrCode + '.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Print QR code
function printQR(qrCode, medicineName) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>In mã QR - ${medicineName}</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                .qr-container { margin: 20px auto; max-width: 300px; }
                img { max-width: 100%; }
                h3 { margin: 10px 0; }
                .qr-code { font-family: monospace; font-size: 12px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="qr-container">
                <h3>${medicineName}</h3>
                <img src="assets/qrcodes/${qrCode}.png" alt="QR Code">
                <div class="qr-code">Mã QR: ${qrCode}</div>
                <p><small>Quét mã để xem thông tin thuốc</small></p>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() {
                        window.close();
                    }
                }
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Regenerate QR code
function regenerateQR(medicineId) {
    if (!confirm('Bạn có chắc muốn tạo lại mã QR cho thuốc này?')) {
        return;
    }
    
    fetch('ajax/regenerate_qr.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'medicine_id=' + medicineId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Tạo lại mã QR thành công!');
            location.reload();
        } else {
            alert('❌ Lỗi: ' + (data.message || 'Không thể tạo lại mã QR'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Lỗi kết nối');
    });
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>