# Hướng dẫn Ràng buộc Root Admin

## 🔒 Các ràng buộc đã thêm

### 1. **Chỉ có 1 Root Admin per Pharmacy**
- Mỗi nhà thuốc chỉ được có **TỐI ĐA 1 Admin gốc**
- Không thể có 2 Root Admin trong cùng 1 pharmacy
- Được thực thi bằng Database Triggers

### 2. **Không thể xóa Root Admin duy nhất**
- Nếu Root Admin là manager duy nhất → Không thể xóa
- Phải có ít nhất 1 manager trong pharmacy

### 3. **Không thể hạ quyền Root Admin nếu là duy nhất**
- Nếu không còn manager nào khác → Không thể hạ xuống staff
- Phải nâng người khác lên manager trước

---

## 📋 Cài đặt

### Bước 1: Chạy SQL Constraints
```bash
# Mở phpMyAdmin và chạy file:
add_root_admin_constraints.sql
```

File này sẽ:
- ✅ Tạo 3 triggers (INSERT, UPDATE, DELETE)
- ✅ Kiểm tra và sửa dữ liệu hiện tại (nếu có nhiều root admin)
- ✅ Chỉ giữ lại root admin đầu tiên của mỗi pharmacy

### Bước 2: Sử dụng công cụ quản lý
```
http://localhost/CNM_NHOM32/manage_root_admin.php
```

Công cụ này cho phép:
- 📊 Xem danh sách Root Admin của mỗi pharmacy
- 🔄 Chuyển quyền Root Admin cho user khác
- ⚙️ Đặt user làm Root Admin mới

---

## 🛡️ Triggers đã tạo

### 1. `before_user_insert_check_root_admin`
**Khi:** Thêm user mới
**Kiểm tra:** Nếu user mới có `is_root_admin = 1`
**Hành động:** Báo lỗi nếu pharmacy đã có root admin

**Ví dụ:**
```sql
-- Pharmacy 1 đã có root admin
INSERT INTO users (pharmacy_id, username, role, is_root_admin) 
VALUES (1, 'admin_new', 'manager', 1);
-- ❌ Lỗi: "Nhà thuốc này đã có Admin gốc"
```

### 2. `before_user_update_check_root_admin`
**Khi:** Cập nhật user
**Kiểm tra:** 
- Nếu đang set `is_root_admin = 1` → Kiểm tra pharmacy đã có root admin chưa
- Nếu đang hạ `is_root_admin = 0` → Kiểm tra còn manager nào không

**Ví dụ:**
```sql
-- Cố gắng set user khác làm root admin
UPDATE users SET is_root_admin = 1 WHERE user_id = 5;
-- ❌ Lỗi nếu pharmacy đã có root admin

-- Cố gắng hạ root admin duy nhất
UPDATE users SET is_root_admin = 0 WHERE user_id = 1;
-- ❌ Lỗi nếu không còn manager nào
```

### 3. `before_user_delete_check_root_admin`
**Khi:** Xóa user
**Kiểm tra:** Nếu đang xóa root admin → Kiểm tra còn manager nào không
**Hành động:** Báo lỗi nếu không còn manager

**Ví dụ:**
```sql
-- Cố gắng xóa root admin duy nhất
DELETE FROM users WHERE user_id = 1 AND is_root_admin = 1;
-- ❌ Lỗi: "Không thể xóa Admin gốc duy nhất"
```

---

## 🔄 Cách chuyển quyền Root Admin

### Phương pháp 1: Dùng công cụ web (Khuyến nghị)
1. Truy cập: `http://localhost/CNM_NHOM32/manage_root_admin.php`
2. Tìm pharmacy cần chuyển quyền
3. Click nút "Đặt làm Root Admin" ở user mới
4. Xác nhận → Root Admin cũ tự động bị hạ quyền

### Phương pháp 2: Dùng SQL
```sql
-- Bước 1: Bỏ root admin của user cũ
UPDATE users SET is_root_admin = 0 WHERE user_id = 1;

-- Bước 2: Set root admin cho user mới
UPDATE users SET is_root_admin = 1, role = 'manager' WHERE user_id = 5;
```

---

## 📊 Kiểm tra hệ thống

### Kiểm tra số lượng Root Admin per Pharmacy
```sql
SELECT 
    p.pharmacy_name,
    COUNT(CASE WHEN u.is_root_admin = 1 THEN 1 END) as root_admin_count,
    GROUP_CONCAT(CASE WHEN u.is_root_admin = 1 THEN u.username END) as root_admins
FROM pharmacies p
LEFT JOIN users u ON p.pharmacy_id = u.pharmacy_id
GROUP BY p.pharmacy_id, p.pharmacy_name;
```

**Kết quả mong đợi:**
- `root_admin_count` = 1 cho mỗi pharmacy
- Nếu = 0: Pharmacy chưa có root admin (cần đặt)
- Nếu > 1: Lỗi! (Chạy lại SQL constraints)

### Kiểm tra Triggers đã tạo
```sql
SHOW TRIGGERS WHERE `Table` = 'users';
```

Phải thấy 3 triggers:
- `before_user_insert_check_root_admin`
- `before_user_update_check_root_admin`
- `before_user_delete_check_root_admin`

---

## ⚠️ Các trường hợp đặc biệt

### Trường hợp 1: Pharmacy chưa có Root Admin
**Vấn đề:** Pharmacy mới tạo, chưa có ai là root admin

**Giải pháp:**
```sql
-- Đặt manager đầu tiên làm root admin
UPDATE users 
SET is_root_admin = 1 
WHERE pharmacy_id = 1 
AND role = 'manager' 
ORDER BY user_id ASC 
LIMIT 1;
```

### Trường hợp 2: Pharmacy có nhiều Root Admin (Lỗi)
**Vấn đề:** Dữ liệu cũ có nhiều root admin

**Giải pháp:** Chạy lại SQL constraints, nó sẽ tự động sửa:
```sql
-- Chỉ giữ lại root admin đầu tiên
UPDATE users u1
SET is_root_admin = 0
WHERE is_root_admin = 1
AND user_id NOT IN (
    SELECT * FROM (
        SELECT MIN(u2.user_id)
        FROM users u2
        WHERE u2.pharmacy_id = u1.pharmacy_id
        AND u2.is_root_admin = 1
        GROUP BY u2.pharmacy_id
    ) as temp
);
```

### Trường hợp 3: Muốn chuyển Root Admin cho người khác
**Giải pháp:** Dùng `manage_root_admin.php` hoặc:
```sql
START TRANSACTION;

-- Bỏ root admin cũ
UPDATE users SET is_root_admin = 0 WHERE user_id = 1;

-- Set root admin mới
UPDATE users SET is_root_admin = 1, role = 'manager' WHERE user_id = 5;

COMMIT;
```

### Trường hợp 4: Root Admin muốn nghỉ việc
**Bước 1:** Chuyển quyền cho người khác trước
**Bước 2:** Sau đó mới xóa/vô hiệu hóa tài khoản cũ

```sql
-- Bước 1: Chuyển quyền
UPDATE users SET is_root_admin = 0 WHERE user_id = 1;
UPDATE users SET is_root_admin = 1 WHERE user_id = 5;

-- Bước 2: Vô hiệu hóa tài khoản cũ
UPDATE users SET is_active = 0 WHERE user_id = 1;
-- Hoặc xóa (nếu không còn manager nào khác thì sẽ lỗi)
DELETE FROM users WHERE user_id = 1;
```

---

## 🧪 Test Cases

### Test 1: Không thể tạo 2 Root Admin
```sql
-- Giả sử pharmacy 1 đã có root admin (user_id = 1)
INSERT INTO users (pharmacy_id, username, password, full_name, role, is_root_admin) 
VALUES (1, 'admin2', 'hash', 'Admin 2', 'manager', 1);
-- ❌ Kỳ vọng: Lỗi "Nhà thuốc này đã có Admin gốc"
```

### Test 2: Không thể hạ Root Admin duy nhất
```sql
-- Giả sử user_id = 1 là root admin duy nhất
UPDATE users SET is_root_admin = 0 WHERE user_id = 1;
-- ❌ Kỳ vọng: Lỗi "Không thể hạ quyền Admin gốc duy nhất"
```

### Test 3: Có thể chuyển Root Admin
```sql
-- Có 2 managers: user_id = 1 (root), user_id = 2 (không root)
START TRANSACTION;
UPDATE users SET is_root_admin = 0 WHERE user_id = 1;
UPDATE users SET is_root_admin = 1 WHERE user_id = 2;
COMMIT;
-- ✅ Kỳ vọng: Thành công
```

### Test 4: Không thể xóa Root Admin duy nhất
```sql
-- Giả sử user_id = 1 là manager duy nhất
DELETE FROM users WHERE user_id = 1;
-- ❌ Kỳ vọng: Lỗi "Không thể xóa Admin gốc duy nhất"
```

---

## 📝 Checklist triển khai

- [ ] Chạy `add_root_admin_constraints.sql` trong phpMyAdmin
- [ ] Kiểm tra 3 triggers đã được tạo
- [ ] Kiểm tra mỗi pharmacy chỉ có 1 root admin
- [ ] Test không thể tạo root admin thứ 2
- [ ] Test không thể xóa root admin duy nhất
- [ ] Test chuyển quyền root admin thành công
- [ ] Truy cập `manage_root_admin.php` để xem giao diện quản lý

---

## 🔧 Troubleshooting

### Lỗi: "Trigger already exists"
**Giải pháp:** Triggers đã tồn tại, bỏ qua hoặc DROP trước:
```sql
DROP TRIGGER IF EXISTS before_user_insert_check_root_admin;
DROP TRIGGER IF EXISTS before_user_update_check_root_admin;
DROP TRIGGER IF EXISTS before_user_delete_check_root_admin;
```

### Lỗi: Pharmacy có nhiều Root Admin
**Giải pháp:** Chạy lại phần cleanup trong SQL script

### Lỗi: Không thể cập nhật user
**Nguyên nhân:** Trigger đang chặn
**Giải pháp:** Kiểm tra logic, đảm bảo tuân thủ ràng buộc

---

**Hoàn thành!** 🎉

Hệ thống ràng buộc Root Admin đã sẵn sàng, đảm bảo tính toàn vẹn dữ liệu.
