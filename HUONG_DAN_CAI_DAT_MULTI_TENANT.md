# 🏥 HƯỚNG DẪN CÀI ĐẶT HỆ THỐNG ĐA NHÀ THUỐC (MULTI-TENANT)

## 📋 TỔNG QUAN

Hệ thống cho phép nhiều nhà thuốc đăng ký và sử dụng độc lập:
- Mỗi nhà thuốc có dữ liệu riêng biệt
- Khi đăng ký → tự động tạo 1 tài khoản admin
- Admin nhà thuốc tạo tài khoản nhân viên cho nhà thuốc mình
- Dữ liệu các nhà thuốc hoàn toàn tách biệt

---

## 🔧 BƯỚC 1: CẬP NHẬT DATABASE

### Cách 1: Chạy file SQL (Khuyến nghị)

1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Chọn database `qlnt_db`
3. Click tab "SQL"
4. Copy toàn bộ nội dung file `database_multi_tenant_schema.sql`
5. Paste vào và click "Go"

### Cách 2: Chạy từ command line

```bash
mysql -u root -p qlnt_db < database_multi_tenant_schema.sql
```

### Những gì sẽ được tạo:

✅ **Bảng mới:**
- `pharmacies` - Quản lý các nhà thuốc

✅ **Cột mới được thêm vào các bảng:**
- `users.pharmacy_id` - Liên kết user với nhà thuốc
- `users.email` - Email của user
- `medicines.pharmacy_id` - Thuốc thuộc nhà thuốc nào
- `batches.pharmacy_id` - Lô thuốc thuộc nhà thuốc nào
- `suppliers.pharmacy_id` - Nhà cung cấp thuộc nhà thuốc nào
- `invoices.pharmacy_id` - Hóa đơn thuộc nhà thuốc nào
- `notifications.pharmacy_id` - Thông báo thuộc nhà thuốc nào

✅ **Stored Procedure:**
- `create_pharmacy_with_admin()` - Tạo nhà thuốc + admin tự động

✅ **Views:**
- `pharmacy_statistics` - Thống kê theo nhà thuốc

✅ **Dữ liệu mặc định:**
- Tạo nhà thuốc "DUO PHARMA" với mã PH001
- Gán tất cả dữ liệu hiện tại vào nhà thuốc này

---

## 🚀 BƯỚC 2: KIỂM TRA CÀI ĐẶT

Chạy script kiểm tra:

```
http://localhost/CNM_NHOM32/check_multi_tenant.php
```

Script này sẽ kiểm tra:
- ✅ Bảng pharmacies đã tồn tại
- ✅ Các cột pharmacy_id đã được thêm
- ✅ Dữ liệu hiện tại đã được gán pharmacy_id
- ✅ Stored procedure đã được tạo

---

## 📝 BƯỚC 3: SỬ DỤNG HỆ THỐNG

### 1. Đăng ký nhà thuốc mới

Truy cập: `http://localhost/CNM_NHOM32/index.php?page=auth&action=register`

**Thông tin cần điền:**
- Tên đăng nhập (4-20 ký tự, chỉ chữ, số, _)
- Họ và tên
- Email
- Số điện thoại (10-11 số)
- Tên nhà thuốc
- Địa chỉ nhà thuốc
- Mật khẩu (tối thiểu 6 ký tự)
- Xác nhận mật khẩu

**Khi đăng ký thành công:**
- ✅ Tạo nhà thuốc mới với mã unique (VD: PH12AB34CD)
- ✅ Tạo tài khoản admin cho nhà thuốc
- ✅ Tự động tạo 8 danh mục thuốc mặc định
- ✅ Tự động tạo 8 đơn vị tính mặc định
- ✅ Chuyển đến trang đăng nhập

### 2. Đăng nhập với tài khoản admin

Truy cập: `http://localhost/CNM_NHOM32/index.php?page=auth&action=login`

Đăng nhập bằng:
- Username: (username bạn đã đăng ký)
- Password: (password bạn đã đăng ký)

### 3. Tạo tài khoản nhân viên

Sau khi đăng nhập với admin:
1. Vào menu "Quản lý người dùng"
2. Click "Thêm người dùng"
3. Điền thông tin nhân viên
4. Chọn vai trò: "Nhân viên" (staff)
5. Lưu

**Lưu ý:** Nhân viên được tạo sẽ tự động thuộc nhà thuốc của admin đang đăng nhập.

---

## 🔐 CƠ CHẾ PHÂN QUYỀN

### Admin (Manager)
- ✅ Xem tất cả dữ liệu của nhà thuốc mình
- ✅ Quản lý thuốc, lô thuốc, nhà cung cấp
- ✅ Tạo/sửa/xóa nhân viên
- ✅ Xem báo cáo, thống kê
- ✅ Quản lý cài đặt nhà thuốc
- ❌ KHÔNG thể xem dữ liệu nhà thuốc khác

### Nhân viên (Staff)
- ✅ Bán hàng
- ✅ Xem danh sách thuốc
- ✅ Tìm kiếm thuốc
- ✅ Tạo hóa đơn
- ❌ Không thể quản lý người dùng
- ❌ Không thể xóa dữ liệu quan trọng
- ❌ KHÔNG thể xem dữ liệu nhà thuốc khác

---

## 📊 CẤU TRÚC DỮ LIỆU

```
pharmacies (Nhà thuốc)
├── pharmacy_id: 1
├── pharmacy_name: "Nhà thuốc ABC"
├── pharmacy_code: "PH12AB34CD"
└── users (Người dùng)
    ├── user_id: 1 (Admin)
    │   └── role: "manager"
    ├── user_id: 2 (Nhân viên 1)
    │   └── role: "staff"
    └── user_id: 3 (Nhân viên 2)
        └── role: "staff"
└── medicines (Thuốc)
    ├── medicine_id: 1
    └── medicine_id: 2
└── batches (Lô thuốc)
    ├── batch_id: 1
    └── batch_id: 2
└── invoices (Hóa đơn)
    ├── invoice_id: 1
    └── invoice_id: 2
```

---

## 🔍 KIỂM TRA DỮ LIỆU

### Xem danh sách nhà thuốc:
```sql
SELECT * FROM pharmacies;
```

### Xem users theo nhà thuốc:
```sql
SELECT u.*, p.pharmacy_name 
FROM users u 
JOIN pharmacies p ON u.pharmacy_id = p.pharmacy_id;
```

### Xem thống kê nhà thuốc:
```sql
SELECT * FROM pharmacy_statistics;
```

### Kiểm tra dữ liệu đã được phân chia đúng:
```sql
-- Kiểm tra medicines
SELECT pharmacy_id, COUNT(*) as total 
FROM medicines 
GROUP BY pharmacy_id;

-- Kiểm tra users
SELECT pharmacy_id, COUNT(*) as total 
FROM users 
GROUP BY pharmacy_id;
```

---

## ⚠️ LƯU Ý QUAN TRỌNG

### 1. Data Isolation (Cách ly dữ liệu)
- Mỗi nhà thuốc CHỈ thấy dữ liệu của mình
- Không thể truy cập dữ liệu nhà thuốc khác
- Tất cả queries phải filter theo `pharmacy_id`

### 2. Khi tạo dữ liệu mới
Luôn phải gán `pharmacy_id` từ session:
```php
$pharmacyId = $_SESSION['pharmacy_id'];
$sql = "INSERT INTO medicines (pharmacy_id, name, ...) VALUES (?, ?, ...)";
```

### 3. Khi query dữ liệu
Luôn phải filter theo `pharmacy_id`:
```php
$pharmacyId = $_SESSION['pharmacy_id'];
$sql = "SELECT * FROM medicines WHERE pharmacy_id = ?";
```

### 4. Backup dữ liệu
- Backup theo từng nhà thuốc
- Hoặc backup toàn bộ và restore có chọn lọc

---

## 🎯 TÍNH NĂNG MỞ RỘNG

### 1. Subscription Plans (Gói đăng ký)
- **Free**: Giới hạn 100 thuốc, 2 nhân viên
- **Basic**: Giới hạn 500 thuốc, 5 nhân viên
- **Premium**: Không giới hạn

### 2. Multi-branch (Đa chi nhánh)
Một nhà thuốc có thể có nhiều chi nhánh:
```sql
CREATE TABLE branches (
    branch_id INT PRIMARY KEY AUTO_INCREMENT,
    pharmacy_id INT,
    branch_name VARCHAR(255),
    address TEXT,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id)
);
```

### 3. Báo cáo tổng hợp
- Báo cáo theo nhà thuốc
- So sánh giữa các chi nhánh
- Thống kê toàn hệ thống (cho super admin)

---

## 🐛 TROUBLESHOOTING

### Lỗi: "pharmacy_id không được để trống"
**Nguyên nhân:** Chưa cập nhật code để gán pharmacy_id

**Giải pháp:** Thêm pharmacy_id vào session khi login:
```php
$_SESSION['pharmacy_id'] = $user['pharmacy_id'];
```

### Lỗi: "Không thể tạo nhà thuốc"
**Nguyên nhân:** Database chưa được cập nhật

**Giải pháp:** Chạy lại file `database_multi_tenant_schema.sql`

### Lỗi: "Tên đăng nhập đã tồn tại"
**Nguyên nhân:** Username đã được dùng bởi nhà thuốc khác

**Giải pháp:** Chọn username khác hoặc thêm prefix (VD: nhathuocabc_admin)

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề:
1. Kiểm tra file log: `error_log`
2. Kiểm tra database đã cập nhật chưa
3. Kiểm tra session có pharmacy_id chưa
4. Xem file `check_multi_tenant.php` để debug

---

## ✅ CHECKLIST CÀI ĐẶT

- [ ] Chạy file `database_multi_tenant_schema.sql`
- [ ] Kiểm tra bảng `pharmacies` đã tồn tại
- [ ] Kiểm tra các cột `pharmacy_id` đã được thêm
- [ ] Dữ liệu cũ đã được gán `pharmacy_id`
- [ ] Test đăng ký nhà thuốc mới
- [ ] Test đăng nhập với admin mới
- [ ] Test tạo nhân viên
- [ ] Test phân quyền dữ liệu
- [ ] Backup database

---

**Hệ thống đã sẵn sàng cho multi-tenant!** 🎉
