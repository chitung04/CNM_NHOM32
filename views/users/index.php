<?php require_once 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people me-2"></i>Quản lý người dùng</h2>
    <a href="index.php?page=users&action=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Thêm người dùng
    </a>
</div>

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

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Danh sách người dùng</h5>
            </div>
            <div class="col-md-6">
                <div class="search-container position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Tìm kiếm người dùng..." autocomplete="off">
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
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Họ tên</th>
                        <th>Số điện thoại</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Chưa có người dùng nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td>
                                    <?php if (isset($user['is_root_admin']) && $user['is_root_admin'] == 1): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-fill-check"></i> Admin gốc
                                        </span>
                                    <?php elseif ($user['role'] === 'manager'): ?>
                                        <span class="badge bg-primary">
                                            <i class="bi bi-person-badge"></i> Quản lý
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info">
                                            <i class="bi bi-person"></i> Nhân viên
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $user['is_active'] ? 'Hoạt động' : 'Khóa'; ?>
                                    </span>
                                </td>
                                <td><?php echo $user['created_at'] ? date('d/m/Y', strtotime($user['created_at'])) : '-'; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php 
                                        // Kiểm tra vai trò
                                        $currentUserRole = $_SESSION['role'];
                                        $isCurrentRootAdmin = isset($_SESSION['is_root_admin']) && $_SESSION['is_root_admin'] == 1;
                                        $isCurrentManager = $currentUserRole === 'manager';
                                        $isCurrentStaff = $currentUserRole === 'staff';
                                        
                                        // Kiểm tra user trong danh sách
                                        $isTargetRootAdmin = isset($user['is_root_admin']) && $user['is_root_admin'] == 1;
                                        $isTargetManager = $user['role'] === 'manager';
                                        $isSelf = $user['user_id'] == $_SESSION['user_id'];
                                        
                                        // Logic phân quyền:
                                        // 1. Staff chỉ được edit/delete chính mình
                                        // 2. Admin được phân quyền (manager nhưng không phải root):
                                        //    - Chỉ được edit/delete chính mình và staff
                                        //    - Không được edit/delete root admin hoặc admin khác
                                        // 3. Root admin:
                                        //    - Có thể edit/delete tất cả (trừ root admin khác)
                                        //    - Có thể edit/delete admin được phân quyền
                                        
                                        $cannotEdit = false;
                                        $disabledReason = '';
                                        
                                        if ($isCurrentStaff && !$isSelf) {
                                            // Staff chỉ được sửa chính mình
                                            $cannotEdit = true;
                                            $disabledReason = 'Nhân viên chỉ được chỉnh sửa chính mình';
                                        } elseif ($isCurrentManager && !$isCurrentRootAdmin) {
                                            // Admin được phân quyền không được sửa bất kỳ admin nào khác
                                            if ($isTargetManager && !$isSelf) {
                                                $cannotEdit = true;
                                                $disabledReason = 'Không thể chỉnh sửa quản lý khác';
                                            }
                                        } elseif ($isCurrentRootAdmin) {
                                            // Root admin không được sửa root admin khác
                                            if ($isTargetRootAdmin && !$isSelf) {
                                                $cannotEdit = true;
                                                $disabledReason = 'Không thể chỉnh sửa admin gốc khác';
                                            }
                                            // Root admin có thể sửa admin được phân quyền và staff
                                        }
                                        
                                        if ($cannotEdit): ?>
                                            <button class="btn btn-outline-secondary" disabled title="<?php echo $disabledReason; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" disabled title="<?php echo $disabledReason; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="index.php?page=users&action=edit&id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if (!$isSelf): ?>
                                                <a href="index.php?page=users&action=delete&id=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-outline-danger"
                                                   onclick="return confirm('Bạn có chắc muốn xóa người dùng <?php echo htmlspecialchars($user['username']); ?>?\n\nThao tác này không thể hoàn tác!')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary" disabled title="Không thể xóa chính mình">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
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
console.log('User search loaded');

let searchTimeout;

// Simple table search function
function searchTable(keyword) {
    console.log('Searching table for:', keyword);
    
    const table = document.getElementById('usersTable');
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
    const tbody = document.querySelector('#usersTable tbody');
    if (!document.getElementById('no-results-row')) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.id = 'no-results-row';
        noResultsRow.innerHTML = `
            <td colspan="8" class="text-center text-muted py-4">
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                Không tìm thấy người dùng nào phù hợp
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

// AJAX search for suggestions (placeholder - users don't need medicine suggestions)
function searchSuggestions(keyword) {
    // For users, we don't need medicine suggestions
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
    
    // Hide suggestions for users (no AJAX needed)
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

console.log('User search script ready');
</script>

<?php require_once 'views/layouts/footer.php'; ?>
