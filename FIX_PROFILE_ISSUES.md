# 🔧 Sửa lỗi hiển thị Thông tin cá nhân

## ❌ Vấn đề

Sau khi cập nhật email trong phần **Thông tin cá nhân**, trang vẫn hiển thị:
- Email: **Chưa cập nhật**
- Trạng thái: **Không hoạt động**

---

## 🔍 Nguyên nhân

### 1. Email hiển thị "Chưa cập nhật"
- Giá trị `email` trong database là `NULL` hoặc empty string
- Code trong `views/profile/index.php` dòng 56:
  ```php
  <td><?= htmlspecialchars($user['email'] ?? 'Chưa cập nhật') ?></td>
  ```
- Nếu `email` là NULL → Hiển thị "Chưa cập nhật"

### 2. Trạng thái "Không hoạt động"
- Giá trị `is_active` trong database là `0` hoặc `NULL`
- Code trong `views/profile/index.php` dòng 68-72:
  ```php
  <?php if (isset($user['is_active']) && $user['is_active']): ?>
      <span class="badge bg-success">Đang hoạt động</span>
  <?php else: ?>
      <span class="badge bg-secondary">Không hoạt động</span>
  <?php endif; ?>
  ```
- Nếu `is_active` = 0 hoặc NULL → Hiển thị "Không hoạt động"

---

## 🛠️ Cách sửa

### Bước 1: Kiểm tra dữ liệu
Mở trình duyệt và truy cập:
```
http://localhost/CNM_NHOM32/check_admin_data.php
```

Script này sẽ:
- ✅ Hiển thị tất cả dữ liệu của user admin
- ✅ Phát hiện field nào bị NULL hoặc empty
- ✅ Đề xuất SQL để sửa

---

### Bước 2: Sửa user admin
Có 2 cách:

#### Cách 1: Tự động (Khuyến nghị)
Truy cập:
```
http://localhost/CNM_NHOM32/fix_admin_data.php
```

Script sẽ tự động:
- Set `is_active = 1`
- Set `email = 'admin@duopharma.com'` (nếu email đang NULL)

#### Cách 2: Thủ công (SQL)
Chạy SQL trong phpMyAdmin:
```sql
UPDATE users 
SET is_active = 1,
    email = 'admin@duopharma.com'
WHERE username = 'admin';
```

---

### Bước 3: Sửa tất cả users
Nếu có nhiều users bị vấn đề tương tự, truy cập:
```
http://localhost/CNM_NHOM32/fix_all_users_status.php
```

Script sẽ:
- ✅ Kiểm tra tất cả users
- ✅ Tìm users có `is_active = 0` hoặc NULL
- ✅ Tự động set `is_active = 1` cho tất cả

---

### Bước 4: Kiểm tra lại
1. **Đăng xuất** khỏi hệ thống
2. **Đăng nhập lại** bằng tài khoản admin
3. Vào **Thông tin cá nhân**
4. Kiểm tra:
   - ✅ Email hiển thị đúng (không còn "Chưa cập nhật")
   - ✅ Trạng thái: **Đang hoạt động** (màu xanh)

---

## 📋 Checklist

- [ ] Chạy `check_admin_data.php` để kiểm tra
- [ ] Chạy `fix_admin_data.php` để sửa user admin
- [ ] Chạy `fix_all_users_status.php` để sửa tất cả users
- [ ] Đăng xuất và đăng nhập lại
- [ ] Vào Thông tin cá nhân → Kiểm tra email và trạng thái
- [ ] Thử cập nhật email mới → Kiểm tra có hiển thị không

---

## 🎯 Kết quả mong đợi

### Trước khi sửa:
```
Email: Chưa cập nhật
Trạng thái: Không hoạt động (màu xám)
```

### Sau khi sửa:
```
Email: admin@duopharma.com (hoặc email bạn vừa cập nhật)
Trạng thái: Đang hoạt động (màu xanh)
```

---

## 🔍 Debug thêm

Nếu sau khi sửa vẫn không hiển thị đúng:

### 1. Kiểm tra database trực tiếp
Vào phpMyAdmin, chạy:
```sql
SELECT user_id, username, full_name, email, is_active 
FROM users 
WHERE username = 'admin';
```

Kết quả phải là:
- `email`: Có giá trị (không NULL)
- `is_active`: 1

### 2. Kiểm tra session
Thêm code debug vào `views/profile/index.php` sau dòng 8:
```php
echo "<pre>DEBUG USER DATA:\n";
print_r($user);
echo "</pre>";
```

Xem output có đúng không.

### 3. Clear cache trình duyệt
- Nhấn `Ctrl + Shift + Delete`
- Xóa cache và cookies
- Refresh lại trang

---

## 📂 Files liên quan

- `check_admin_data.php` - Kiểm tra dữ liệu user admin
- `fix_admin_data.php` - Sửa user admin
- `fix_all_users_status.php` - Sửa tất cả users
- `views/profile/index.php` - Trang hiển thị thông tin cá nhân
- `views/profile/edit.php` - Trang chỉnh sửa thông tin

---

## 🚀 Tóm tắt nhanh

```bash
# Bước 1: Kiểm tra
http://localhost/CNM_NHOM32/check_admin_data.php

# Bước 2: Sửa
http://localhost/CNM_NHOM32/fix_admin_data.php

# Bước 3: Sửa tất cả (nếu cần)
http://localhost/CNM_NHOM32/fix_all_users_status.php

# Bước 4: Đăng xuất và đăng nhập lại
# Bước 5: Kiểm tra Thông tin cá nhân
```

---

**Ngày tạo:** 04/05/2026  
**Trạng thái:** ✅ Sẵn sàng sử dụng
