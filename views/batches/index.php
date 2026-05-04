<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam me-2"></i>Quản lý lô thuốc</h2>
    <div>
        <a href="index.php?page=batches&action=import_sql" class="btn btn-success me-2">
            <i class="bi bi-file-earmark-code me-2"></i>Import SQL
        </a>
        <a href="index.php?page=batches&action=create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nhập lô mới
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Danh sách lô thuốc</h5>
            </div>
            <div class="col-md-6">
                <div class="search-container position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Tìm kiếm lô thuốc, QR code..." autocomplete="off">
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
            <table class="table table-hover" id="batchesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên thuốc</th>
                        <th>Nhà cung cấp</th>
                        <th>Số lượng</th>
                        <th>Ngày nhập</th>
                        <th>Hạn sử dụng</th>
                        <th>Trạng thái</th>
                        <th>QR Code</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $stt = 1; foreach ($batches as $batch): ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($batch['medicine_name']); ?></td>
                            <td><?php echo htmlspecialchars($batch['supplier_name'] ?? '-'); ?></td>
                            <td><span class="badge bg-info"><?php echo $batch['quantity']; ?></span></td>
                            <td><?php echo $batch['import_date'] ? date('d/m/Y', strtotime($batch['import_date'])) : '-'; ?></td>
                            <td>
                                <?php 
                                $daysToExpiry = $batch['days_to_expiry'];
                                $expiryClass = $daysToExpiry <= 30 ? 'text-danger' : 'text-success';
                                ?>
                                <span class="<?php echo $expiryClass; ?>">
                                    <?php echo $batch['expiry_date'] ? date('d/m/Y', strtotime($batch['expiry_date'])) : '-'; ?>
                                    <?php if ($daysToExpiry <= 30 && $daysToExpiry >= 0): ?>
                                        <br><small>(Còn <?php echo $daysToExpiry; ?> ngày)</small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusBadge = [
                                    'active' => 'bg-success',
                                    'expired' => 'bg-danger',
                                    'sold_out' => 'bg-secondary'
                                ];
                                $statusText = [
                                    'active' => 'Còn hàng',
                                    'expired' => 'Hết hạn',
                                    'sold_out' => 'Hết hàng'
                                ];
                                ?>
                                <span class="badge <?php echo $statusBadge[$batch['status']]; ?>">
                                    <?php echo $statusText[$batch['status']]; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($batch['qr_code'])): ?>
                                    <div class="d-flex align-items-center">
                                        <small class="text-muted me-2"><?php echo htmlspecialchars($batch['qr_code']); ?></small>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="viewMedicineInfo('<?php echo htmlspecialchars($batch['qr_code']); ?>')"
                                                    title="Xem thông tin thuốc">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="testQRCode('<?php echo htmlspecialchars($batch['qr_code']); ?>')"
                                                    title="Test QR Code">
                                                <i class="bi bi-qr-code"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có QR</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?page=batches&action=view&id=<?php echo $batch['batch_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
console.log('Batch search loaded');

let searchTimeout;

// Test QR Code function
function testQRCode(qrCode) {
    console.log('Testing QR Code:', qrCode);
    
    // Tạo URL để test QR code
    const testUrl = 'medicine_info.php?qr=' + encodeURIComponent(qrCode);
    
    // Mở trong tab mới
    window.open(testUrl, '_blank');
}

// View Medicine Info function
function viewMedicineInfo(qrCode) {
    console.log('Viewing medicine info for:', qrCode);
    
    // Tạo URL xem thông tin thuốc
    const medicineUrl = 'medicine_info.php?qr=' + encodeURIComponent(qrCode);
    
    // Mở trong tab mới
    window.open(medicineUrl, '_blank');
}

// Simple table search function - improved to search QR codes too
function searchTable(keyword) {
    console.log('Searching table for:', keyword);
    
    const table = document.getElementById('batchesTable');
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
    const tbody = document.querySelector('#batchesTable tbody');
    if (!document.getElementById('no-results-row')) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.id = 'no-results-row';
        noResultsRow.innerHTML = `
            <td colspan="8" class="text-center text-muted py-4">
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                Không tìm thấy lô thuốc nào phù hợp
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

// AJAX search for suggestions using fetch - improved for batches
function searchSuggestions(keyword) {
    if (keyword.length < 2) {
        document.getElementById('searchSuggestions').style.display = 'none';
        return;
    }
    
    console.log('Getting batch suggestions for:', keyword);
    
    fetch('ajax/search_batch_qr.php', {
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
        console.log('Batch suggestions response:', data);
        
        if (data.success && data.batches && data.batches.length > 0) {
            let html = '';
            data.batches.slice(0, 5).forEach(function(batch) { // Limit to 5 suggestions
                const quantity = batch.quantity || 0;
                const stockClass = quantity < 10 ? 'text-danger' : 'text-success';
                const statusClass = batch.status === 'active' ? 'bg-success' : 'bg-secondary';
                const statusText = batch.status === 'active' ? 'Còn hàng' : 'Hết hàng';
                
                html += `
                    <div class="suggestion-item p-2 border-bottom" style="cursor: pointer;" 
                         data-name="${batch.medicine_name}" data-qr="${batch.qr_code || ''}">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${batch.medicine_name}</strong>
                                <br><small class="text-muted">
                                    ${batch.qr_code ? 'QR: ' + batch.qr_code : 'Lô: ' + batch.batch_number}
                                    ${batch.supplier_name ? ' - ' + batch.supplier_name : ''}
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary">${new Intl.NumberFormat('vi-VN').format(batch.price || 0)}đ</div>
                                <small class="${stockClass}">Tồn: ${quantity}</small>
                                <span class="badge ${statusClass} ms-1" style="font-size: 0.6em;">${statusText}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            document.getElementById('searchSuggestions').innerHTML = html;
            document.getElementById('searchSuggestions').style.display = 'block';
            console.log('Batch suggestions displayed');
        } else {
            document.getElementById('searchSuggestions').innerHTML = '<div class="p-2 text-muted text-center">Không tìm thấy lô thuốc nào</div>';
            document.getElementById('searchSuggestions').style.display = 'block';
            console.log('No batch suggestions found');
        }
    })
    .catch(error => {
        console.error('Batch search error:', error);
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

console.log('Batch search script ready');
</script>

<?php require_once 'views/layouts/footer.php'; ?>
