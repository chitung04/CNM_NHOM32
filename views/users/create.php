<?php require_once 'views/layouts/header.php'; ?>

<style>
    .create-user-container {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.3) 0%, rgba(59, 130, 246, 0.3) 100%), 
                    url('assets/images/bìa.png') center/cover no-repeat fixed;
        background-size: cover;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1050;
    }
    
    .user-form-container {
        background: rgba(255, 255, 255, 0.02);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        max-width: 420px;
        width: 100%;
    }
    
    .user-form-header {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.3) 0%, rgba(59, 130, 246, 0.3) 100%);
        padding: 20px 25px;
        text-align: center;
        color: white;
    }
    
    .user-form-icon {
        font-size: 36px;
        margin-bottom: 10px;
        opacity: 0.95;
    }
    
    .user-form-header h1 {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 3px;
        letter-spacing: 0.5px;
    }
    
    .user-form-header p {
        font-size: 13px;
        opacity: 0.9;
        font-weight: 300;
    }
    
    .user-form-body {
        padding: 20px 25px;
        background: rgba(255, 255, 255, 0.02);
        backdrop-filter: blur(10px);
    }
    
    .form-group {
        margin-bottom: 14px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 4px;
        color: white;
        font-weight: 500;
        font-size: 12px;
    }
    
    .input-group-custom {
        position: relative;
    }
    
    .input-group-custom i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
        font-size: 14px;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 10px 36px 10px 36px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        font-size: 13px;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        color: white;
    }
    
    .form-control-custom::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .form-control-custom:focus {
        outline: none;
        border-color: rgba(59, 130, 246, 0.5);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .btn-create {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border: none;
        border-radius: 10px;
        color: white;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }
    
    .btn-create:active {
        transform: translateY(0);
    }
    
    .btn-cancel-link {
        width: 100%;
        padding: 12px;
        background: transparent;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        color: white;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        margin-top: 12px;
    }
    
    .btn-cancel-link:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-1px);
        text-decoration: none;
        color: white;
    }
    
    .alert-custom {
        padding: 10px 14px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 13px;
    }
    
    .alert-danger {
        background: #fed7d7;
        border: 1px solid #fc8181;
        color: #c53030;
    }
    
    .alert-info {
        background: rgba(59, 130, 246, 0.2);
        border: 1px solid rgba(59, 130, 246, 0.3);
        color: white;
        backdrop-filter: blur(5px);
    }
    
    select.form-control-custom {
        padding-right: 14px;
    }
</style>

<div class="create-user-container">
    <div class="user-form-container">
        <div class="user-form-header">
            <div class="user-form-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Thêm nhân viên mới</h1>
            <p>Tạo tài khoản cho nhân viên</p>
        </div>
        
        <div class="user-form-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-custom alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?page=users&action=store">
                <?php echo csrfField(); ?>
                
                <div class="form-group">
                    <label class="form-label">Họ và tên</label>
                    <div class="input-group-custom">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               class="form-control-custom" 
                               name="full_name" 
                               placeholder="Nhập họ và tên"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tên đăng nhập</label>
                    <div class="input-group-custom">
                        <i class="fas fa-at"></i>
                        <input type="text" 
                               class="form-control-custom" 
                               name="username" 
                               placeholder="Nhập tên đăng nhập"
                               required
                               pattern="[a-zA-Z0-9_]{3,20}" 
                               title="3-20 ký tự, chỉ chữ, số và gạch dưới">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Vai trò</label>
                    <div class="input-group-custom">
                        <i class="fas fa-user-tag"></i>
                        <select class="form-control-custom" name="role" required>
                            <option value="staff">Nhân viên</option>
                            <option value="manager">Quản lý</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mật khẩu</label>
                    <div class="input-group-custom">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               class="form-control-custom" 
                               name="password" 
                               placeholder="Nhập mật khẩu"
                               required
                               minlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nhập lại mật khẩu</label>
                    <div class="input-group-custom">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               class="form-control-custom" 
                               name="confirm_password" 
                               placeholder="Nhập lại mật khẩu"
                               required
                               minlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Số điện thoại</label>
                    <div class="input-group-custom">
                        <i class="fas fa-phone"></i>
                        <input type="tel" 
                               class="form-control-custom" 
                               name="phone" 
                               placeholder="Nhập số điện thoại"
                               pattern="[0-9]{10,11}" 
                               title="Số điện thoại 10-11 chữ số">
                    </div>
                </div>
                
                <button type="submit" class="btn-create">
                    <i class="fas fa-user-plus"></i>
                    Tạo tài khoản
                </button>
                
                <a href="index.php?page=users" class="btn-cancel-link">
                    <i class="fas fa-times"></i>
                    Hủy bỏ
                </a>
            </form>
        </div>
    </div>
</div>

<script>
// Kiểm tra mật khẩu khớp nhau
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.querySelector('input[name="password"]');
    const confirmPasswordField = document.querySelector('input[name="confirm_password"]');
    const form = document.querySelector('form');
    
    function checkPasswordMatch() {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPasswordField.setCustomValidity('Mật khẩu xác nhận không khớp');
            confirmPasswordField.style.borderColor = '#dc3545';
        } else {
            confirmPasswordField.setCustomValidity('');
            confirmPasswordField.style.borderColor = 'rgba(255, 255, 255, 0.1)';
        }
    }
    
    // Kiểm tra khi nhập
    passwordField.addEventListener('input', checkPasswordMatch);
    confirmPasswordField.addEventListener('input', checkPasswordMatch);
    
    // Kiểm tra khi submit form
    form.addEventListener('submit', function(e) {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
            confirmPasswordField.focus();
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Mật khẩu phải có ít nhất 6 ký tự!');
            passwordField.focus();
            return false;
        }
    });
});
</script>
