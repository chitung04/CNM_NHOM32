<?php 
require_once 'views/layouts/header.php'; 
require_once 'helpers/secure_session.php';
require_once 'helpers/url_helper.php';

// Kiểm tra xem QR code có thể hoạt động không
$qrCodeEnabled = canQRCodeWork();
$localhostWarning = isLocalhost() ? getLocalhostWarning() : null;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <?php if (isManager()): ?>
        <h2><i class="bi bi-capsule me-2"></i>Quản lý thuốc</h2>
        <div>
            <a href="generate_missing_medicine_qr.php" 
               class="btn btn-outline-warning me-2"
               target="_blank"
               title="Tạo QR code cho các thuốc chưa có hình ảnh QR">
                <i class="bi bi-qr-code me-1"></i>Tạo QR code
            </a>
            <a href="index.php?page=medicines&action=create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Thêm thuốc mới
            </a>
        </div>
    <?php else: ?>
        <h2><i class="bi bi-search me-2"></i>Tra cứu thuốc</h2>
        <div class="text-muted">
            <i class="bi bi-info-circle me-1"></i>Tìm kiếm thông tin thuốc và kiểm tra tồn kho
        </div>
    <?php endif; ?>
</div>

<?php if ($localhostWarning): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo $localhostWarning; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <?php if (isManager()): ?>
                    <h5 class="mb-0">Danh sách thuốc</h5>
                <?php else: ?>
                    <h5 class="mb-0">Tìm kiếm thuốc</h5>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <div class="search-container position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Nhập tên thuốc để tìm kiếm..." autocomplete="off">
                        <button class="btn btn-primary" type="button" id="searchButton">
                            <i class="bi bi-search me-1"></i>Tìm
                        </button>
                    </div>
                    <!-- Suggestions dropdown -->
                    <div id="searchSuggestions" class="position-absolute w-100 bg-white border rounded shadow-sm" 
                         style="top: 100%; z-index: 1000; display: none; max-height: 300px; overflow-y: auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="medicinesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên thuốc</th>
                        <th>Danh mục</th>
                        <th>Đơn vị</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>QR Code</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($medicines)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Chưa có thuốc nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $stt = 1; foreach ($medicines as $medicine): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($medicine['medicine_name']); ?></strong>
                                </td>
                                <td>
                                    <?php if ($medicine['category_name']): ?>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($medicine['category_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($medicine['unit_name'] ?? '-'); ?></td>
                                <td>
                                    <strong class="text-success">
                                        <?php echo number_format($medicine['price'], 0, ',', '.'); ?>đ
                                    </strong>
                                </td>
                                <td>
                                    <?php 
                                    $inventory = $medicine['inventory'];
                                    $badgeClass = $inventory < LOW_STOCK_THRESHOLD ? 'bg-danger' : 'bg-success';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $inventory; ?> <?php echo htmlspecialchars($medicine['unit_name'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($medicine['qr_code'])): ?>
                                        <?php 
                                        $qrImagePath = 'assets/qrcodes/' . $medicine['qr_code'] . '.png';
                                        $qrImageExists = file_exists($qrImagePath);
                                        ?>
                                        
                                        <?php if (!$qrImageExists): ?>
                                            <!-- QR code value exists but image file is missing -->
                                            <div class="text-center">
                                                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 1.2rem;" 
                                                   title="Hình ảnh QR code chưa được tạo"></i>
                                                <br><small class="text-muted">Chưa tạo</small>
                                            </div>
                                        <?php elseif ($qrCodeEnabled): ?>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <small class="text-muted me-2"><?php echo htmlspecialchars($medicine['qr_code']); ?></small>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="viewMedicineInfo('<?php echo htmlspecialchars($medicine['qr_code']); ?>')"
                                                            title="Xem thông tin thuốc">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="testQRCode('<?php echo htmlspecialchars($medicine['qr_code']); ?>')"
                                                            title="Test QR Code">
                                                        <i class="bi bi-qr-code"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center">
                                                <i class="bi bi-qr-code text-warning" style="font-size: 1.5rem;" 
                                                   title="QR code chỉ hoạt động khi không dùng localhost"></i>
                                                <br><small class="text-muted">Localhost</small>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có QR</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!empty($medicine['qr_code']) && $qrCodeEnabled): ?>
                                            <a href="public_medicine_info.php?qr=<?php echo urlencode($medicine['qr_code']); ?>" 
                                               class="btn btn-outline-success" 
                                               title="Xem thông tin QR"
                                               target="_blank">
                                                <i class="bi bi-qr-code-scan"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn btn-success add-to-cart" 
                                                data-id="<?php echo $medicine['medicine_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($medicine['medicine_name']); ?>"
                                                title="Thêm vào đơn hàng">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                        <a href="index.php?page=medicines&action=view&id=<?php echo $medicine['medicine_id']; ?>" 
                                           class="btn btn-outline-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (isManager()): ?>
                                            <a href="index.php?page=medicines&action=edit&id=<?php echo $medicine['medicine_id']; ?>" 
                                               class="btn btn-outline-primary" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="index.php?page=medicines&action=delete&id=<?php echo $medicine['medicine_id']; ?>" 
                                               class="btn btn-outline-danger" title="Xóa"
                                               onclick="return confirmDelete('Bạn có chắc chắn muốn xóa thuốc này? Thuốc sẽ được ẩn để giữ lịch sử giao dịch.')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
console.log('Medicine search loaded');

let searchTimeout;

// Simple table search function
function searchTable(keyword) {
    console.log('Searching table for:', keyword);
    
    const table = document.getElementById('medicinesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    let visibleCount = 0;
    let firstVisibleRow = null;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;
        
        if (keyword === '' || text.toLowerCase().indexOf(keyword.toLowerCase()) > -1) {
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
    
    console.log('Found', visibleCount, 'matching rows');
    
    // Auto-scroll to first result if searching and found results
    if (keyword && visibleCount > 0 && firstVisibleRow) {
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
    
    // Show message if no results
    if (visibleCount === 0 && keyword !== '') {
        showNoResultsMessage();
    } else if (keyword === '') {
        hideNoResultsMessage();
    }
}

// Show no results message
function showNoResultsMessage() {
    const tbody = document.querySelector('#medicinesTable tbody');
    if (!document.getElementById('no-results-row')) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.id = 'no-results-row';
        noResultsRow.innerHTML = `
            <td colspan="8" class="text-center text-muted py-4">
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                Không tìm thấy thuốc nào phù hợp
            </td>
        `;
        tbody.appendChild(noResultsRow);
    }
}

// Hide no results message
function hideNoResultsMessage() {
    const noResultsRow = document.getElementById('no-results-row');
    if (noResultsRow) {
        noResultsRow.remove();
    }
}

// AJAX search for suggestions using fetch (same as sales page)
function searchSuggestions(keyword) {
    if (keyword.length < 2) {
        document.getElementById('searchSuggestions').style.display = 'none';
        return;
    }
    
    console.log('Getting suggestions for:', keyword);
    
    fetch('ajax/search_medicine.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'keyword=' + encodeURIComponent(keyword)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Suggestions response:', data);
        
        if (data.success && data.medicines && data.medicines.length > 0) {
            let html = '';
            data.medicines.slice(0, 5).forEach(function(medicine) { // Limit to 5 suggestions
                const inventory = medicine.inventory || 0;
                const stockClass = inventory < 10 ? 'text-danger' : 'text-success';
                
                html += `
                    <div class="suggestion-item p-2 border-bottom" style="cursor: pointer;" 
                         data-name="${medicine.medicine_name}">
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
            document.getElementById('searchSuggestions').innerHTML = html;
            document.getElementById('searchSuggestions').style.display = 'block';
            console.log('Suggestions displayed');
        } else {
            document.getElementById('searchSuggestions').innerHTML = '<div class="p-2 text-muted text-center">Không tìm thấy gợi ý</div>';
            document.getElementById('searchSuggestions').style.display = 'block';
            console.log('No suggestions found');
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        document.getElementById('searchSuggestions').style.display = 'none';
    });
}

// Search input event
document.getElementById('searchInput').addEventListener('input', function() {
    const keyword = this.value.trim();
    console.log('Input changed to:', keyword);
    
    clearTimeout(searchTimeout);
    
    // Always search table immediately
    searchTable(keyword);
    
    // Show suggestions if keyword is long enough
    if (keyword.length >= 2) {
        searchTimeout = setTimeout(() => searchSuggestions(keyword), 300);
    } else {
        document.getElementById('searchSuggestions').style.display = 'none';
    }
});

// Search button click
document.getElementById('searchButton').addEventListener('click', function() {
    const keyword = document.getElementById('searchInput').value.trim();
    console.log('Search button clicked with:', keyword);
    searchTable(keyword);
    document.getElementById('searchSuggestions').style.display = 'none';
});

// Enter key search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const keyword = this.value.trim();
        console.log('Enter pressed with:', keyword);
        searchTable(keyword);
        document.getElementById('searchSuggestions').style.display = 'none';
    }
});

// Click on suggestion
document.addEventListener('click', function(e) {
    if (e.target.closest('#searchSuggestions .suggestion-item')) {
        const item = e.target.closest('.suggestion-item');
        const name = item.getAttribute('data-name');
        document.getElementById('searchInput').value = name;
        document.getElementById('searchSuggestions').style.display = 'none';
        searchTable(name);
    }
    
    // Hide suggestions when clicking outside
    if (!e.target.closest('.search-container')) {
        document.getElementById('searchSuggestions').style.display = 'none';
    }
});

// Test QR Code function
function testQRCode(qrCode) {
    console.log('Testing QR Code:', qrCode);
    
    // Tạo URL để test QR code
    const testUrl = 'public_medicine_info.php?qr=' + encodeURIComponent(qrCode);
    
    // Mở trong tab mới
    window.open(testUrl, '_blank');
}

// View Medicine Info function
function viewMedicineInfo(qrCode) {
    console.log('Viewing medicine info for:', qrCode);
    
    // Tạo URL xem thông tin thuốc
    const medicineUrl = 'public_medicine_info.php?qr=' + encodeURIComponent(qrCode);
    
    // Mở trong tab mới
    window.open(medicineUrl, '_blank');
}

// Add to cart functionality using fetch
document.addEventListener('click', function(e) {
    if (e.target.closest('.add-to-cart')) {
        const btn = e.target.closest('.add-to-cart');
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        
        if (!confirm('Thêm "' + name + '" vào đơn hàng?')) {
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        
        fetch('ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'medicine_id=' + encodeURIComponent(id) + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Đã thêm vào đơn hàng!\nVào trang "Bán hàng" để xem chi tiết.');
                btn.innerHTML = '<i class="bi bi-check"></i>';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            } else {
                alert('❌ ' + (data.message || 'Không thể thêm sản phẩm'));
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-plus"></i>';
            }
        })
        .catch(error => {
            console.error('Add to cart error:', error);
            alert('❌ Lỗi kết nối. Vui lòng thử lại.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-plus"></i>';
        });
    }
});

console.log('Medicine search script ready');
</script>

<?php require_once 'views/layouts/footer.php'; ?>
