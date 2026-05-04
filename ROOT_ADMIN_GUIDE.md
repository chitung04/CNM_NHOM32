# Hướng dẫn hệ thống phân quyền Admin gốc

## 📋 Tổng quan

Hệ thống bây giờ có 3 cấp độ người dùng:

1. **Admin gốc (Root Admin)** - Người tạo nhà thuốc, quyền cao nhất
2. **Admin được phân quyền** - Staff được nâng lên thành Manager
3. **Nhân viên (Staff)** - Quyền thấp nhất

---

## 🔧 Cài đặt

### Bước 1: Chạy SQL để thêm cột `is_root_admin`

```bash
# Chạy file SQL này trong phpMyAdmin hoặc MySQL
mysql -u root -p qlnt_db < add_root_admin_column.sql
```

Hoặc copy nội dung file `add_root_admin_column.sql` và chạy trong phpMyAdmin.

### Bước 2: Kiểm tra kết quả

Sau khi chạy SQL, kiểm tra bảng `users`:
- Admin đầu tiên của mỗi pharmacy sẽ có `is_root_admin = 1`
- Các user khác có `is_root_admin = 0`

---

## 🔒 Quy tắc phân quyền

### **1. Nhân viên (Staff)**
- ✅ Chỉ được xem và sửa thông tin chính mình
- ❌ Không được xem/sửa thông tin người khác
- ❌ Không được truy cập trang quản lý người dùng

### **2. Admin được phân quyền (Manager không phải Root)**
- ✅ Được xem danh sách tất cả user trong nhà thuốc
- ✅ Được sửa/xóa thông tin chính mình
- ✅ Được tạo/sửa/xóa Staff
- ✅ Được nâng Staff lên Manager
- ❌ **KHÔNG** được sửa/xóa Admin gốc
- ❌ **KHÔNG** được sửa/xóa Admin được phân quyền khác
- ❌ **KHÔNG** được tự thay đổi vai trò của mình

### **3. Admin gốc (Root Admin)**
- ✅ Được xem danh sách tất cả user trong nhà thuốc
- ✅ Được sửa/xóa thông tin chính mình
- ✅ Được tạo/sửa/xóa Staff
- ✅ Được nâng Staff lên Manager
- ✅ **Được hạ Admin được phân quyền xuống Staff**
- ✅ **Được sửa/xóa Admin được phân quyền**
- ❌ **KHÔNG** được sửa/xóa Admin gốc khác (nếu có nhiều admin gốc)
- ❌ **KHÔNG** được tự thay đổi vai trò của mình

---

## 📊 Ví dụ thực tế

### Tình huống 1: Nhà thuốc mới đăng ký

```
1. Đăng ký nhà thuốc "ABC Pharmacy"
   → Tạo admin: "admin_abc" (is_root_admin = 1)

2. Admin_abc đăng nhập và tạo:
   - Staff: "nhanvien1"
   - Staff: "nhanvien2"

3. Admin_abc nâng "nhanvien1" lên Manager
   → nhanvien1 bây giờ là Manager (is_root_admin = 0)

4. Phân quyền:
   - admin_abc: Có thể hạ nhanvien1 xuống Staff
   - nhanvien1: KHÔNG thể hạ admin_abc xuống Staff
   - nhanvien1: Có thể nâng nhanvien2 lên Manager
   - nhanvien2: Chỉ sửa được chính mình
```

### Tình huống 2: Admin gốc muốn hạ quyền Admin được phân quyền

```
Bước 1: Admin gốc đăng nhập
Bước 2: Vào "Quản lý người dùng"
Bước 3: Tìm Admin được phân quyền (có badge "Quản lý")
Bước 4: Click nút Edit (màu xanh)
Bước 5: Trong dropdown "Vai trò", chọn "Nhân viên"
Bước 6: Click "Cập nhật"
→ Admin được phân quyền bây giờ trở thành Staff
```

---

## 🎨 Giao diện

### Badge vai trò trong danh sách:

- **Admin gốc**: Badge đỏ với icon shield `🛡️ Admin gốc`
- **Quản lý**: Badge xanh dương `👤 Quản lý`
- **Nhân viên**: Badge xanh lá `👤 Nhân viên`

### Nút Edit/Delete:

- **Màu xanh (active)**: Có quyền edit/delete
- **Màu xám (disabled)**: Không có quyền, hover để xem lý do

### Dropdown vai trò khi edit:

- **Enabled**: Có thể thay đổi vai trò
- **Disabled**: Không thể thay đổi, có thông báo lý do

---

## 🔍 Kiểm tra phân quyền

### Test 1: Staff không được sửa người khác
```
1. Đăng nhập bằng Staff
2. Vào "Quản lý người dùng"
3. Xem các user khác → Nút Edit/Delete bị disabled
4. Hover vào nút → Thấy "Nhân viên chỉ được chỉnh sửa chính mình"
```

### Test 2: Admin được phân quyền không sửa được Admin khác
```
1. Đăng nhập bằng Admin được phân quyền
2. Vào "Quản lý người dùng"
3. Xem Admin gốc → Nút Edit/Delete bị disabled
4. Xem Admin được phân quyền khác → Nút Edit/Delete bị disabled
5. Xem Staff → Nút Edit/Delete active (có thể sửa)
```

### Test 3: Admin gốc có thể hạ quyền Admin được phân quyền
```
1. Đăng nhập bằng Admin gốc
2. Vào "Quản lý người dùng"
3. Xem Admin được phân quyền → Nút Edit active
4. Click Edit → Dropdown vai trò enabled
5. Đổi từ "Quản lý" sang "Nhân viên" → Lưu thành công
```

### Test 4: Admin gốc không sửa được Admin gốc khác
```
1. Đăng nhập bằng Admin gốc
2. Vào "Quản lý người dùng"
3. Xem Admin gốc khác (nếu có) → Nút Edit/Delete bị disabled
4. Hover vào nút → Thấy "Không thể chỉnh sửa admin gốc khác"
```

---

## 📝 Database Schema

### Bảng `users` - Cột mới:

```sql
is_root_admin TINYINT(1) DEFAULT 0
```

**Giá trị:**
- `1` = Admin gốc (người đăng ký nhà thuốc)
- `0` = Admin được phân quyền hoặc Staff

### Session variables:

```php
$_SESSION['is_root_admin']  // 1 hoặc 0
$_SESSION['role']           // 'manager' hoặc 'staff'
$_SESSION['pharmacy_id']    // ID nhà thuốc
```

---

## 🚀 Files đã thay đổi

1. **add_root_admin_column.sql** - SQL script thêm cột
2. **controllers/AuthController.php** - Lưu is_root_admin vào session
3. **views/users/index.php** - Logic hiển thị nút Edit/Delete
4. **views/users/edit.php** - Logic dropdown vai trò

---

## ⚠️ Lưu ý quan trọng

1. **Không thể có 2 Root Admin trong cùng 1 pharmacy** (trừ khi thêm thủ công)
2. **Root Admin không thể tự hạ quyền mình xuống Staff**
3. **Nếu Root Admin bị xóa**, cần chỉ định Root Admin mới thủ công trong database
4. **Khi đăng ký nhà thuốc mới**, admin đầu tiên tự động là Root Admin

---

## 🔧 Troubleshooting

### Vấn đề: Không thấy badge "Admin gốc"
**Giải pháp:** Chạy lại SQL script `add_root_admin_column.sql`

### Vấn đề: Admin gốc không sửa được Admin được phân quyền
**Giải pháp:** 
1. Kiểm tra `$_SESSION['is_root_admin']` có giá trị 1 không
2. Logout và login lại
3. Kiểm tra database: `SELECT * FROM users WHERE is_root_admin = 1`

### Vấn đề: Tất cả admin đều là Root Admin
**Giải pháp:**
```sql
-- Reset tất cả về 0
UPDATE users SET is_root_admin = 0;

-- Chỉ đánh dấu admin đầu tiên của mỗi pharmacy
UPDATE users u1
SET is_root_admin = 1
WHERE role = 'manager'
AND user_id = (
    SELECT MIN(u2.user_id) 
    FROM (SELECT * FROM users) u2 
    WHERE u2.pharmacy_id = u1.pharmacy_id 
    AND u2.role = 'manager'
);
```

---

## ✅ Checklist triển khai

- [ ] Chạy SQL script `add_root_admin_column.sql`
- [ ] Kiểm tra cột `is_root_admin` đã được thêm
- [ ] Kiểm tra admin đầu tiên có `is_root_admin = 1`
- [ ] Test đăng nhập với Root Admin
- [ ] Test tạo Staff và nâng lên Manager
- [ ] Test Root Admin hạ quyền Admin được phân quyền
- [ ] Test Admin được phân quyền không sửa được Admin khác
- [ ] Test Staff chỉ sửa được chính mình

---

**Hoàn thành!** 🎉

Hệ thống phân quyền 3 cấp đã sẵn sàng sử dụng.
