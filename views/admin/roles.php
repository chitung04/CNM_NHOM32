<?php 
require_once 'views/layouts/header.php';
require_once 'helpers/permissions.php';

requireManager();
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quản lý vai trò & quyền</h1>
            </div>
            
            <!-- Tổng quan -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>
                                Nhân viên (Staff)
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Vai trò cơ bản cho nhân viên bán hàng</p>
                            
                            <h6 class="mt-3">Quyền hạn:</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Xem trang chủ và thống kê cơ bản
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Bán hàng và tạo hóa đơn
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Tra cứu thông tin thuốc
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Xem và in hóa đơn
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-x-circle text-danger me-2"></i>
                                    Không thể quản lý thuốc
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-x-circle text-danger me-2"></i>
                                    Không thể xem báo cáo
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-x-circle text-danger me-2"></i>
                                    Không thể quản lý người dùng
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person-badge me-2"></i>
                                Quản lý (Manager)
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Vai trò quản trị với đầy đủ quyền</p>
                            
                            <h6 class="mt-3">Quyền hạn:</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Tất cả quyền của Nhân viên
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Quản lý thuốc (CRUD)
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Quản lý lô thuốc và nhập kho
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Quản lý nhà cung cấp
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Xem tất cả báo cáo và thống kê
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Quản lý người dùng và phân quyền
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Sao lưu và khôi phục dữ liệu
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ma trận quyền chi tiết -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-table me-2"></i>
                        Ma trận quyền chi tiết
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Chức năng</th>
                                    <th class="text-center">Nhân viên</th>
                                    <th class="text-center">Quản lý</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $features = [
                                    'Dashboard' => [
                                        'dashboard.view' => 'Xem trang chủ'
                                    ],
                                    'Bán hàng' => [
                                        'sales.view' => 'Xem giao diện bán hàng',
                                        'sales.create' => 'Tạo đơn hàng',
                                        'sales.invoice' => 'Xem và in hóa đơn'
                                    ],
                                    'Quản lý thuốc' => [
                                        'medicines.view' => 'Xem danh sách thuốc',
                                        'medicines.create' => 'Thêm thuốc mới',
                                        'medicines.edit' => 'Sửa thông tin thuốc',
                                        'medicines.delete' => 'Xóa thuốc'
                                    ],
                                    'Quản lý lô thuốc' => [
                                        'batches.view' => 'Xem lô thuốc',
                                        'batches.create' => 'Nhập lô mới',
                                        'batches.edit' => 'Sửa lô thuốc'
                                    ],
                                    'Nhà cung cấp' => [
                                        'suppliers.view' => 'Xem nhà cung cấp',
                                        'suppliers.create' => 'Thêm nhà cung cấp',
                                        'suppliers.edit' => 'Sửa nhà cung cấp'
                                    ],
                                    'Báo cáo' => [
                                        'reports.sales' => 'Báo cáo doanh thu',
                                        'reports.inventory' => 'Báo cáo tồn kho',
                                        'reports.expiry' => 'Báo cáo hết hạn',
                                        'reports.topSelling' => 'Thống kê bán chạy'
                                    ],
                                    'Quản lý người dùng' => [
                                        'users.view' => 'Xem người dùng',
                                        'users.create' => 'Thêm người dùng',
                                        'users.edit' => 'Sửa người dùng'
                                    ],
                                    'Sao lưu' => [
                                        'backup.view' => 'Xem backup',
                                        'backup.create' => 'Tạo backup',
                                        'backup.restore' => 'Khôi phục'
                                    ]
                                ];
                                
                                foreach ($features as $category => $perms):
                                ?>
                                    <tr class="table-secondary">
                                        <td colspan="3"><strong><?= $category ?></strong></td>
                                    </tr>
                                    <?php foreach ($perms as $perm => $desc): ?>
                                        <tr>
                                            <td><?= $desc ?></td>
                                            <td class="text-center">
                                                <?php if (in_array('staff', PERMISSIONS[$perm])): ?>
                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (in_array('manager', PERMISSIONS[$perm])): ?>
                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Hướng dẫn -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Hướng dẫn phân quyền
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Cách thay đổi vai trò người dùng:</h6>
                    <ol>
                        <li>Vào menu "Quản lý người dùng"</li>
                        <li>Chọn người dùng cần thay đổi</li>
                        <li>Nhấn "Sửa" và chọn vai trò mới</li>
                        <li>Lưu thay đổi</li>
                    </ol>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Lưu ý:</strong> Người dùng cần đăng xuất và đăng nhập lại để quyền mới có hiệu lực.
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
