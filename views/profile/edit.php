<?php 
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Xử lý form submit TRƯỚC KHI load header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Load user model
    require_once 'models/User.php';
    $userModel = new User();
    
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($fullName)) {
        $_SESSION['error'] = "Họ và tên không được để trống";
    } else {
        try {
            $user = $userModel->getById($_SESSION['user_id']);
            
            $data = [
                'full_name' => $fullName,
                'phone' => $phone,
                'email' => $email,
                'role' => $user['role'] // Giữ nguyên role
            ];
            
            $result = $userModel->update($_SESSION['user_id'], $data);
            
            if ($result) {
                $_SESSION['success'] = "Cập nhật thông tin thành công";
                $_SESSION['full_name'] = $fullName; // Cập nhật session
                
                // Lưu session trước khi redirect
                session_write_close();
                
                // Thử header redirect trước
                if (!headers_sent()) {
                    header('Location: index.php?page=profile');
                    exit;
                } else {
                    // Nếu header đã gửi, dùng JavaScript
                    echo "<script>window.location.href='index.php?page=profile';</script>";
                    exit;
                }
            } else {
                $_SESSION['error'] = "Không thể cập nhật thông tin";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
}

// Load header SAU KHI xử lý form
require_once 'views/layouts/header.php';

// Load user model để hiển thị
require_once 'models/User.php';
$userModel = new User();
$user = $userModel->getById($_SESSION['user_id']);

if (!$user) {
    $_SESSION['error'] = "Không tìm thấy thông tin người dùng";
    header('Location: index.php?page=profile');
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-pencil-square"></i> Chỉnh sửa thông tin cá nhân
                </h1>
                <a href="index.php?page=profile" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Thông tin tài khoản</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                    <small class="text-muted">Không thể thay đổi tên đăng nhập</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" 
                                           value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                           placeholder="email@example.com">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                           placeholder="0123456789">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Vai trò</label>
                                    <input type="text" class="form-control" 
                                           value="<?= $user['role'] === 'manager' ? 'Quản lý' : 'Nhân viên' ?>" disabled>
                                    <small class="text-muted">Không thể thay đổi vai trò</small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Lưu thay đổi
                                    </button>
                                    <a href="index.php?page=profile" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-2"></i>Hủy
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Lưu ý</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Họ và tên là bắt buộc
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Email và số điện thoại là tùy chọn
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-lock text-warning"></i>
                                    Không thể thay đổi tên đăng nhập
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-lock text-warning"></i>
                                    Không thể tự thay đổi vai trò
                                </li>
                            </ul>
                            
                            <hr>
                            
                            <a href="index.php?page=profile&action=change_password" class="btn btn-warning w-100">
                                <i class="bi bi-key me-2"></i>Đổi mật khẩu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>
