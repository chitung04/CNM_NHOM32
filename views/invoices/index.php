<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt me-2"></i>Lịch sử đơn hàng</h2>
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
                <h5 class="mb-0">Danh sách đơn hàng</h5>
            </div>
            <div class="col-md-6">
                <div class="search-container position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Tìm kiếm đơn hàng..." autocomplete="off">
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
            <table class="table table-hover" id="invoicesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mã đơn hàng</th>
                        <th>Nhân viên</th>
                        <th>Ngày tạo</th>
                        <th>Tổng tiền</th>
                        <th>Giảm giá</th>
                        <th>Thành tiền</th>
                        <th>QR Code</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Chưa có đơn hàng nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $stt = 1; foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($invoice['staff_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></td>
                                <td><?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?>đ</td>
                                <td><?php echo number_format($invoice['discount'], 0, ',', '.'); ?>đ</td>
                                <td>
                                    <strong class="text-success">
                                        <?php echo number_format($invoice['final_amount'], 0, ',', '.'); ?>đ
                                    </strong>
                                </td>
                                <td>
                                    <?php if (!empty($invoice['qr_code'])): ?>
                                        <div class="d-flex align-items-center">
                                            <small class="text-muted me-2"><?php echo htmlspecialchars($invoice['qr_code']); ?></small>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="viewInvoiceInfo('<?php echo $invoice['invoice_id']; ?>', '<?php echo htmlspecialchars($invoice['qr_code']); ?>')"
                                                    title="Xem thông tin đơn hàng">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có QR</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?page=invoices&action=view&id=<?php echo $invoice['invoice_id']; ?>" 
                                           class="btn btn-outline-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="index.php?page=invoices&action=print&id=<?php echo $invoice['invoice_id']; ?>" 
                                           class="btn btn-outline-primary" title="In hóa đơn" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <?php if (hasRole('manager')): ?>
                                        <a href="index.php?page=invoices&action=delete&id=<?php echo $invoice['invoice_id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Xóa đơn hàng"
                                           onclick="return confirm('Bạn có chắc muốn xóa đơn hàng <?php echo htmlspecialchars($invoice['invoice_number']); ?>?\n\nLưu ý: Xóa đơn hàng sẽ KHÔNG hoàn lại tồn kho!');">
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
console.log('Invoice search loaded');

let searchTimeout;

// Simple table search function
function searchTable(keyword) {
    console.log('Searching table for:', keyword);
    
    const table = document.getElementById('invoicesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    let visibleCount = 0;
    let firstVisibleRow = null;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        
        // Skip if this is the "no data" row
        if (row.querySelector('td[colspan]')) {
            continue;
        }
        
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
    const tbody = document.querySelector('#invoicesTable tbody');
    if (!document.getElementById('no-results-row')) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.id = 'no-results-row';
        noResultsRow.innerHTML = `
            <td colspan="9" class="text-center text-muted py-4">
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                Không tìm thấy đơn hàng nào phù hợp
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

// AJAX search for suggestions (placeholder - invoices don't need AJAX suggestions)
function searchSuggestions(keyword) {
    // For invoices, we don't need AJAX suggestions since all data is already loaded
    // Just hide suggestions
    document.getElementById('searchSuggestions').style.display = 'none';
}

// Search input event
document.getElementById('searchInput').addEventListener('input', function() {
    const keyword = this.value.trim();
    console.log('Input changed to:', keyword);
    
    clearTimeout(searchTimeout);
    
    // Always search table immediately
    searchTable(keyword);
    
    // Hide suggestions for invoices (no AJAX needed)
    document.getElementById('searchSuggestions').style.display = 'none';
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

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-container')) {
        document.getElementById('searchSuggestions').style.display = 'none';
    }
});

console.log('Invoice search script ready');

// View Invoice Info function
function viewInvoiceInfo(invoiceId, qrCode) {
    console.log('Viewing invoice info for:', invoiceId, qrCode);
    
    // Tạo URL xem thông tin đơn hàng
    const invoiceUrl = 'invoice_info.php?qr=' + encodeURIComponent(qrCode) + '&id=' + invoiceId;
    
    // Mở trong tab mới
    window.open(invoiceUrl, '_blank');
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>
