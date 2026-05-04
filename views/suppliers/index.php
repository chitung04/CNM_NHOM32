<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-truck me-2"></i>Quản lý nhà cung cấp</h2>
    <a href="index.php?page=suppliers&action=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm nhà cung cấp
    </a>
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
                <h5 class="mb-0">Danh sách nhà cung cấp</h5>
            </div>
            <div class="col-md-6">
                <div class="search-container position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Tìm kiếm nhà cung cấp..." autocomplete="off">
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
            <table class="table table-hover" id="suppliersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên nhà cung cấp</th>
                        <th>Điện thoại</th>
                        <th>Email</th>
                        <th>Địa chỉ</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suppliers)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Chưa có nhà cung cấp nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo $supplier['supplier_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($supplier['supplier_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($supplier['phone'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['address'] ?? '-'); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?page=suppliers&action=edit&id=<?php echo $supplier['supplier_id']; ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?page=suppliers&action=delete&id=<?php echo $supplier['supplier_id']; ?>" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirmDelete('Xóa nhà cung cấp này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
console.log('Supplier search loaded');

let searchTimeout;

// Simple table search function
function searchTable(keyword) {
    console.log('Searching table for:', keyword);
    
    const table = document.getElementById('suppliersTable');
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
    const tbody = document.querySelector('#suppliersTable tbody');
    if (!document.getElementById('no-results-row')) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.id = 'no-results-row';
        noResultsRow.innerHTML = `
            <td colspan="6" class="text-center text-muted py-4">
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                Không tìm thấy nhà cung cấp nào phù hợp
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

// AJAX search for suggestions (placeholder - suppliers don't need medicine suggestions)
function searchSuggestions(keyword) {
    // For suppliers, we don't need medicine suggestions
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
    
    // Hide suggestions for suppliers (no AJAX needed)
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

console.log('Supplier search script ready');
</script>

<?php require_once 'views/layouts/footer.php'; ?>
