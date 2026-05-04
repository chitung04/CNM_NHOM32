# 🎉 Tự động tạo dữ liệu mẫu khi đăng ký Pharmacy

## ✅ Đã hoàn thành

Khi một pharmacy mới đăng ký, hệ thống sẽ **TỰ ĐỘNG** tạo đầy đủ dữ liệu mẫu để nhà thuốc có thể sử dụng ngay lập tức.

---

## 📋 Dữ liệu được tạo tự động

### 1. **8 Danh mục thuốc** (Categories)
- Thuốc kháng sinh
- Thuốc giảm đau
- Thuốc hạ sốt
- Vitamin & Khoáng chất
- Thuốc tiêu hóa
- Thuốc tim mạch
- Thuốc da liễu
- Thực phẩm chức năng

### 2. **8 Đơn vị tính** (Units)
- Viên
- Vỉ
- Hộp
- Chai
- Tuýp
- Gói
- Ống
- Lọ

### 3. **3 Nhà cung cấp** (Suppliers)
- Công ty Dược phẩm Hà Nội
- Công ty Dược phẩm Sài Gòn
- Công ty Dược phẩm Trung Ương

### 4. **10 Loại thuốc** (Medicines)
1. Amoxicillin 500mg - 5,000đ
2. Paracetamol 500mg - 3,000đ
3. Ibuprofen 400mg - 4,000đ
4. Amlodipine 5mg - 8,000đ
5. Metformin 500mg - 6,000đ
6. Vitamin C 1000mg - 2,000đ
7. Cefixime 200mg - 12,000đ
8. Aspirin 100mg - 3,500đ
9. Omeprazole 20mg - 7,000đ
10. Cetirizine 10mg - 2,500đ

### 5. **20 Lô thuốc** (Batches)
- Mỗi thuốc có **2 lô**
- Số lượng: 100-500 viên/lô (random)
- Hạn sử dụng: 12-24 tháng (random)
- Mã QR code unique cho mỗi lô

---

## 🔧 Cách hoạt động

### File đã chỉnh sửa:
- **`controllers/AuthController.php`** - Method `createPharmacyWithAdmin()`

### Quy trình:
1. User điền form đăng ký tại `index.php?page=auth&action=register`
2. Hệ thống tạo pharmacy mới
3. Hệ thống tạo admin user (root admin)
4. **TỰ ĐỘNG tạo:**
   - Categories
   - Units
   - Suppliers
   - Medicines (10 loại)
   - Batches (20 lô, 2 lô/thuốc)
5. Tất cả dữ liệu có `pharmacy_id` đúng (multi-tenant)
6. Mỗi medicine và batch có QR code unique
7. Admin đăng nhập → Vào trang bán hàng → Thấy ngay 10 thuốc

---

## 🧪 Cách test

### Bước 1: Chạy script kiểm tra
```
http://localhost/CNM_NHOM32/test_new_pharmacy_registration.php
```

### Bước 2: Đăng ký pharmacy mới
1. Mở: `index.php?page=auth&action=register`
2. Điền thông tin:
   - Tên nhà thuốc: **Nhà thuốc Test ABC**
   - Địa chỉ: **123 Đường Test**
   - Tên đăng nhập: **testadmin**
   - Mật khẩu: **123456**
   - Email: **test@example.com**
   - SĐT: **0901234567**
3. Nhấn **Đăng ký**

### Bước 3: Kiểm tra kết quả
1. Refresh trang `test_new_pharmacy_registration.php`
2. Xem pharmacy mới có:
   - ✅ 10 thuốc
   - ✅ 20 lô
   - ✅ 3 nhà cung cấp

### Bước 4: Đăng nhập và test
1. Đăng nhập bằng tài khoản mới: **testadmin / 123456**
2. Vào trang **Bán hàng** (`index.php?page=sales`)
3. Kiểm tra có hiển thị 10 thuốc không
4. Thử thêm vào giỏ hàng
5. Thử tạo hóa đơn

---

## 🎯 Lợi ích

### Trước đây:
- ❌ Đăng ký xong → Không có dữ liệu
- ❌ Phải import SQL thủ công
- ❌ Trang bán hàng trống rỗng
- ❌ Không thể demo ngay

### Bây giờ:
- ✅ Đăng ký xong → Có ngay 10 thuốc + 20 lô
- ✅ Không cần import gì cả
- ✅ Trang bán hàng đầy đủ sản phẩm
- ✅ Demo được ngay lập tức
- ✅ Mỗi pharmacy có dữ liệu riêng (multi-tenant)

---

## 🔒 Bảo mật Multi-tenant

- Mỗi pharmacy có `pharmacy_id` riêng
- Tất cả dữ liệu (categories, units, suppliers, medicines, batches) đều có `pharmacy_id`
- Admin của pharmacy A **KHÔNG THỂ** xem dữ liệu của pharmacy B
- QR code unique (không trùng lặp)

---

## 📝 Ghi chú

### QR Code Format:
- Medicine: `MED_<timestamp>_<random>`
- Batch: `BATCH_<timestamp>_<random>`

### Batch Number Format:
- `BATCH_P{pharmacy_id}_{số thứ tự}`
- Ví dụ: `BATCH_P3_0001`, `BATCH_P3_0002`

### Timing:
- Sử dụng `usleep(10000)` (0.01s) giữa mỗi lần tạo QR code
- Đảm bảo timestamp khác nhau → QR code unique

---

## 🚀 Sẵn sàng cho presentation

Bây giờ khi demo cho thầy/cô:
1. Tạo pharmacy mới trực tiếp trên web
2. Đăng nhập ngay
3. Vào trang bán hàng → Có ngay 10 thuốc
4. Tạo hóa đơn → Hoàn toàn hoạt động
5. Không cần chuẩn bị dữ liệu trước!

---

## 📂 Files liên quan

- `controllers/AuthController.php` - Logic tạo dữ liệu tự động
- `test_new_pharmacy_registration.php` - Script test
- `setup_data_for_pharmacy.php` - Script cũ (giờ không cần dùng nữa)
- `views/auth/register.php` - Form đăng ký

---

**Ngày hoàn thành:** 04/05/2026  
**Sẵn sàng cho presentation:** 05/05/2026 ✅
