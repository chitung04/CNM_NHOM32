# Test Profile Edit Flow

## Cách test thông báo thành công khi sửa profile:

### Bước 1: Đăng nhập
```
URL: http://localhost/CNM_NHOM32/index.php?page=login
Username: admin
Password: 123456
```

### Bước 2: Vào trang Profile
```
URL: http://localhost/CNM_NHOM32/index.php?page=profile
```

### Bước 3: Click "Chỉnh sửa thông tin"
```
URL sẽ chuyển sang: http://localhost/CNM_NHOM32/index.php?page=profile&action=edit
```

### Bước 4: Thay đổi thông tin
- Đổi họ tên: "Admin Test"
- Đổi email: "admin@test.com"
- Đổi số điện thoại: "0123456789"

### Bước 5: Click "Lưu thay đổi"

### Kết quả mong đợi:
✅ Trang redirect về: `http://localhost/CNM_NHOM32/index.php?page=profile`
✅ Hiển thị thông báo màu xanh: "Cập nhật thông tin thành công"
✅ Thông tin đã được cập nhật trong bảng

---

## Nếu không thấy thông báo:

### Kiểm tra 1: Session có được lưu không?
Thêm dòng này vào đầu `views/profile/index.php`:
```php
<?php
echo "DEBUG: ";
var_dump($_SESSION);
?>
```

### Kiểm tra 2: Headers có bị gửi sớm không?
Kiểm tra file `views/profile/edit.php` dòng 1-5:
- KHÔNG được có khoảng trắng trước `<?php`
- KHÔNG được có `echo` hoặc HTML trước khi xử lý form

### Kiểm tra 3: Database có được update không?
Chạy query này trong phpMyAdmin:
```sql
SELECT * FROM users WHERE user_id = 1;
```
Xem thông tin có thay đổi không.

---

## Code đã sửa trong views/profile/edit.php:

```php
<?php 
// Xử lý form submit TRƯỚC KHI load header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... xử lý form ...
    
    if ($result) {
        $_SESSION['success'] = "Cập nhật thông tin thành công";
        $_SESSION['full_name'] = $fullName;
        
        // Lưu session trước khi redirect
        session_write_close();
        
        // Redirect
        if (!headers_sent()) {
            header('Location: index.php?page=profile');
            exit;
        }
    }
}

// Load header SAU KHI xử lý form
require_once 'views/layouts/header.php';
?>
```

**Key points:**
1. ✅ Xử lý POST trước khi load header
2. ✅ `session_write_close()` để đảm bảo session được lưu
3. ✅ Kiểm tra `headers_sent()` trước khi redirect
4. ✅ `exit` sau redirect

---

## Nếu vẫn không hoạt động:

### Option 1: Dùng JavaScript redirect
```php
echo "<script>
    sessionStorage.setItem('success', 'Cập nhật thông tin thành công');
    window.location.href='index.php?page=profile';
</script>";
exit;
```

Và trong `views/profile/index.php`:
```php
<script>
if (sessionStorage.getItem('success')) {
    alert(sessionStorage.getItem('success'));
    sessionStorage.removeItem('success');
}
</script>
```

### Option 2: Dùng URL parameter
```php
header('Location: index.php?page=profile&success=1');
exit;
```

Và trong `views/profile/index.php`:
```php
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Cập nhật thông tin thành công
    </div>
<?php endif; ?>
```

---

## Kết luận:

Code hiện tại PHẢI hoạt động vì:
1. ✅ Session được lưu trước redirect
2. ✅ Header được gửi đúng cách
3. ✅ Success message được set trong session
4. ✅ Profile index page hiển thị success message

Nếu vẫn không thấy, có thể do:
- Browser cache (Ctrl+F5 để hard refresh)
- Session không được enable trong PHP
- Output buffering đang bật

**Test lại và báo kết quả!**
