# Hướng dẫn hệ thống Admin cố định (Final)

## 🎯 Logic cuối cùng

### Phân loại người dùng:

1. **Admin cố định (Root Admin)** 🛡️
   - `role = 'manager'` VÀ `is_root_admin = 1`
   - Được tạo khi:
     - Đăng ký nhà thuốc mới (admin đầu tiên)
     - Tạo manager mới từ trang quản lý người dùng
   - Có thể có **NHIỀU** admin cố định trong 1 pharmacy

2. **Admin được phân quyền** 👤
   - `role = 'manager'` VÀ `is_root_admin = 0`
   - Được tạo khi: Staff được nâng lên Manager (hiếm khi xảy ra)

3. **Nhân viên (Staff)** 👤
   - `role = 'staff'`
   - `is_root_admin = 0`

---

## 🔒 Quy tắc phân quyền

### **Admin cố định** 🛡️
- ✅ Có thể xem tất cả users trong pharmacy
- ✅ Có thể tạo/sửa/xóa Staff
- ✅ Có thể tạo Admin cố định mới
- ✅ Có thể sửa/xóa Admin được phân quyền
- ✅ Có thể nâng Staff lên Manager (→ trở thành Admin cố định)
- ✅ Có thể hạ Admin được phân quyền xuống Staff
- ❌ **KHÔNG** thể sửa/xóa Admin cố định khác
- ❌ **KHÔNG** thể tự đổi role của mình

### **Admin được phân quyền** 👤
- ✅ Có thể xem tất cả users trong pharmacy
- ✅ Có thể tạo/sửa/xóa Staff
- ✅ Có thể nâng Staff lên Manager (→ trở thành Admin cố định)
- ❌ **KHÔNG** thể sửa/xóa bất kỳ Admin nào khác
- ❌ **KHÔNG** thể tự đổi role của mình

### **Nhân viên (Staff)** 👤
- ✅ Chỉ xem và sửa thông tin chính mình
- ❌ Không truy cập được trang quản lý người dùng

---

## 📋 Cài đặt

### Bước 1: Chạy SQL để cập nhật logic
```bash
# Mở phpMyAdmin và chạy file:
update_root_admin_logic.sql
```

File này sẽ:
- ✅ Xóa các trigger cũ (ràng buộc 1 root admin per pharmacy)
- ✅ Tạo trigger mới (chỉ ngăn xóa manager duy nhất)
- ✅ Cho phép có nhiều root admin trong 1 pharmacy

### Bước 2: Logout và Login lại
Để session cập nhật `is_root_admin`

### Bước 3: Test
- Tạo manager mới → Tự động là Admin cố định
- Nâng staff lên manager → Trở thành Admin cố định
- Admin cố định có thể sửa Admin được phân quyền
- Admin cố định KHÔNG sửa được Admin cố định khác

---

## 🔄 Các trường hợp sử dụng

### Trường hợp 1: Tạo Admin cố định mới
```
1. Đăng nhập bằng Admin cố định
2. Vào "Quản lý người dùng" → "Thêm người dùng"
3. Điền thông tin, chọn vai trò "Quản lý"
4. Lưu → User mới tự động có is_root_admin = 1
```

**Kết quả:** Admin mới là Admin cố định, có quyền như admin hiện tại

### Trường hợp 2: Nâng Staff lên Manager
```
1. Đăng nhập bằng Admin cố định
2. Vào "Quản lý người dùng"
3. Click Edit ở Staff cần nâng
4. Đổi vai trò từ "Nhân viên" → "Quản lý"
5. Lưu → Staff trở thành Admin cố định (is_root_admin = 1)
```

**Kết quả:** Staff được nâng lên thành Admin cố định

### Trường hợp 3: Hạ Admin được phân quyền xuống Staff
```
1. Đăng nhập bằng Admin cố định
2. Vào "Quản lý người dùng"
3. Tìm Admin được phân quyền (badge xanh "👤 Quản lý")
4. Click Edit
5. Đổi vai trò từ "Quản lý" → "Nhân viên"
6. Lưu → Admin được phân quyền trở thành Staff (is_root_admin = 0)
```

**Kết quả:** Admin được phân quyền bị hạ xuống Staff

### Trường hợp 4: Admin cố định cố sửa Admin cố định khác
```
1. Đăng nhập bằng Admin cố định A
2. Vào "Quản lý người dùng"
3. Tìm Admin cố định B (badge đỏ "🛡️ Admin cố định")
4. Click Edit → Nút bị disabled
5. Hover vào nút → "Không thể chỉnh sửa Admin cố định khác"
```

**Kết quả:** Không thể sửa

---

## 🎨 Giao diện

### Badge trong danh sách users:

| Loại | Badge | Màu | Icon |
|------|-------|-----|------|
| Admin cố định | 🛡️ Admin cố định | Đỏ | shield |
| Admin được phân quyền | 👤 Quản lý | Xanh dương | person-badge |
| Nhân viên | 👤 Nhân viên | Xanh lá | person |

### Nút Edit/Delete:

| Người xem | Người được xem | Nút Edit | Nút Delete |
|-----------|----------------|----------|------------|
| Admin cố định | Chính mình | ✅ Active | ❌ Disabled |
| Admin cố định | Admin cố định khác | ❌ Disabled | ❌ Disabled |
| Admin cố định | Admin được phân quyền | ✅ Active | ✅ Active |
| Admin cố định | Staff | ✅ Active | ✅ Active |
| Admin được phân quyền | Chính mình | ✅ Active | ❌ Disabled |
| Admin được phân quyền | Admin bất kỳ | ❌ Disabled | ❌ Disabled |
| Admin được phân quyền | Staff | ✅ Active | ✅ Active |
| Staff | Chính mình | ✅ Active | ❌ Disabled |
| Staff | Bất kỳ ai khác | ❌ Disabled | ❌ Disabled |

---

## 🧪 Test Cases

### Test 1: Tạo Manager mới = Admin cố định
```
1. Login bằng Admin cố định
2. Tạo user mới với role = "Quản lý"
3. Kiểm tra database:
   SELECT username, role, is_root_admin FROM users WHERE username = 'new_admin';
4. Kỳ vọng: role = 'manager', is_root_admin = 1
```

### Test 2: Nâng Staff lên Manager = Admin cố định
```
1. Login bằng Admin cố định
2. Edit Staff, đổi role từ "Nhân viên" → "Quản lý"
3. Kiểm tra database
4. Kỳ vọng: role = 'manager', is_root_admin = 1
```

### Test 3: Admin cố định sửa được Admin được phân quyền
```
1. Tạo Admin được phân quyền (thủ công set is_root_admin = 0)
2. Login bằng Admin cố định
3. Edit Admin được phân quyền
4. Kỳ vọng: Nút Edit active, có thể đổi role
```

### Test 4: Admin cố định KHÔNG sửa được Admin cố định khác
```
1. Có 2 Admin cố định: A và B
2. Login bằng A
3. Cố edit B
4. Kỳ vọng: Nút Edit disabled, hover thấy "Không thể chỉnh sửa Admin cố định khác"
```

### Test 5: Có thể có nhiều Admin cố định
```
1. Tạo Admin 1 với role = "Quản lý"
2. Tạo Admin 2 với role = "Quản lý"
3. Tạo Admin 3 với role = "Quản lý"
4. Kiểm tra database:
   SELECT COUNT(*) FROM users WHERE pharmacy_id = 1 AND is_root_admin = 1;
5. Kỳ vọng: COUNT = 3 (hoặc nhiều hơn)
```

---

## 📊 Database Schema

### Bảng `users`:

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    pharmacy_id INT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    full_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    role ENUM('staff', 'manager') DEFAULT 'staff',
    is_root_admin TINYINT(1) DEFAULT 0,  -- 1 = Admin cố định, 0 = Không
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Trigger hiện tại:

```sql
-- Chỉ có 1 trigger: Ngăn xóa manager duy nhất
before_user_delete_check_manager
```

---

## 🔧 Files đã thay đổi

1. **update_root_admin_logic.sql** - SQL script cập nhật logic
2. **controllers/UserController.php** - Logic tạo/sửa user
3. **models/User.php** - Hỗ trợ is_root_admin
4. **views/users/index.php** - Hiển thị badge và nút (đã cập nhật trước đó)
5. **views/users/edit.php** - Dropdown role (đã cập nhật trước đó)

---

## ⚠️ Lưu ý quan trọng

1. **Mỗi pharmacy nên có ít nhất 1 Admin cố định**
2. **Khi tạo Manager mới → Tự động là Admin cố định**
3. **Khi nâng Staff lên Manager → Trở thành Admin cố định**
4. **Admin cố định không thể tự hạ quyền mình xuống Staff**
5. **Không thể xóa manager duy nhất trong pharmacy**

---

## 🚀 Triển khai

### Checklist:

- [ ] Chạy `update_root_admin_logic.sql` trong phpMyAdmin
- [ ] Kiểm tra trigger `before_user_delete_check_manager` đã được tạo
- [ ] Logout và login lại tất cả users
- [ ] Test tạo manager mới → Kiểm tra is_root_admin = 1
- [ ] Test nâng staff lên manager → Kiểm tra is_root_admin = 1
- [ ] Test admin cố định sửa được admin được phân quyền
- [ ] Test admin cố định KHÔNG sửa được admin cố định khác
- [ ] Kiểm tra badge hiển thị đúng trong danh sách users

---

**Hoàn thành!** 🎉

Hệ thống Admin cố định đã sẵn sàng với logic mới:
- ✅ Có thể có nhiều Admin cố định
- ✅ Manager mới = Admin cố định
- ✅ Admin cố định sửa được Admin được phân quyền
- ✅ Admin cố định KHÔNG sửa được Admin cố định khác
