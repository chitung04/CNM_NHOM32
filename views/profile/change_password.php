<?php 
// Xử lý form submit TRƯỚC KHI load header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Load user model
    require_once 'models/User.php';
    $userModel = new User();
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin";
    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "Mật khẩu mới và xác nhận mật khẩu không khớp";
    } elseif (strlen($newPassword) < 6) {
        $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự";
    } else {
        // Xác thực mật khẩu hiện tại
        $user = $userModel->findByUsername($_SESSION['username']);
        
        if ($user && password_verify($currentPassword, $user['password'])) {
            // Cập nhật mật khẩu mới
            try {
                $data = [
                    'full_name' => $user['full_name'],
                    'phone' => $user['phone'],
                    'email' => $user['email'] ?? null,
                    'role' => $user['role'],
                    'password' => $newPassword
                ];
                
                $result = $userModel->update($_SESSION['user_id'], $data);
                
                if ($result) {
                    $_SESSION['success'] = "Đổi mật khẩu thành công";
                    echo "<script>window.location.href='index.php?page=profile';</script>";
                    exit;
                } else {
                    $_SESSION['error'] = "Không thể đổi mật khẩu";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Mật khẩu hiện tại không đúng";
        }
    }
}

// Load header SAU KHI xử lý form
require_once 'views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'views/layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-key"></i> Đổi mật khẩu
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
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Thay đổi mật khẩu</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="changePasswordForm">
                                <div class="mb-3">
                                    <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="current_password" 
                                               id="currentPassword" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleCurrent">
                                            <i class="bi bi-eye" id="eyeCurrent"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="new_password" 
                                               id="newPassword" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNew">
                                            <i class="bi bi-eye" id="eyeNew"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-2" id="passwordStrength"></div>
                                    <small class="text-muted" id="strengthText"></small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="confirm_password" 
                                               id="confirmPassword" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirm">
                                            <i class="bi bi-eye" id="eyeConfirm"></i>
                                        </button>
                                    </div>
                                    <small id="passwordMatch"></small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Đổi mật khẩu
                                    </button>
                                    <a href="index.php?page=profile" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-2"></i>Hủy
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Yêu cầu mật khẩu</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Tối thiểu 6 ký tự
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Nên có chữ hoa và chữ thường
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Nên có số
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                    Nên có ký tự đặc biệt (@, #, $, ...)
                                </li>
                            </ul>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Lưu ý:</strong> Sau khi đổi mật khẩu, bạn sẽ cần đăng nhập lại với mật khẩu mới.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.password-strength {
    height: 5px;
    border-radius: 3px;
    transition: all 0.3s;
}
.strength-weak { background: #ef4444; width: 33%; }
.strength-medium { background: #f59e0b; width: 66%; }
.strength-strong { background: #10b981; width: 100%; }
</style>

<script>
// Toggle password visibility
document.getElementById('toggleCurrent').addEventListener('click', function() {
    const input = document.getElementById('currentPassword');
    const icon = document.getElementById('eyeCurrent');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});

document.getElementById('toggleNew').addEventListener('click', function() {
    const input = document.getElementById('newPassword');
    const icon = document.getElementById('eyeNew');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});

document.getElementById('toggleConfirm').addEventListener('click', function() {
    const input = document.getElementById('confirmPassword');
    const icon = document.getElementById('eyeConfirm');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});

// Password strength checker
document.getElementById('newPassword').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    strengthBar.className = 'password-strength mt-2';
    if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
        strengthText.textContent = 'Mật khẩu yếu';
        strengthText.className = 'text-danger';
    } else if (strength <= 3) {
        strengthBar.classList.add('strength-medium');
        strengthText.textContent = 'Mật khẩu trung bình';
        strengthText.className = 'text-warning';
    } else {
        strengthBar.classList.add('strength-strong');
        strengthText.textContent = 'Mật khẩu mạnh';
        strengthText.className = 'text-success';
    }
});

// Password match checker
document.getElementById('confirmPassword').addEventListener('input', function() {
    const password = document.getElementById('newPassword').value;
    const confirmPassword = this.value;
    const matchText = document.getElementById('passwordMatch');
    
    if (confirmPassword === '') {
        matchText.textContent = '';
    } else if (password === confirmPassword) {
        matchText.textContent = '✓ Mật khẩu khớp';
        matchText.className = 'text-success';
    } else {
        matchText.textContent = '✗ Mật khẩu không khớp';
        matchText.className = 'text-danger';
    }
});

// Form validation
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Mật khẩu xác nhận không khớp!');
        return false;
    }
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
