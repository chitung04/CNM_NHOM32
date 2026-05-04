# 🔍 HƯỚNG DẪN KIỂM TRA VÀ SỬA LỖI THUỐC KHÔNG HIỂN THỊ

## ❓ VẤN ĐỀ
Thuốc đã có trong database nhưng không hiển thị trên trang bán hàng.

---

## 🎯 NGUYÊN NHÂN CÓ THỂ

### 1. **Chưa import database đầy đủ**
   - File `ULTIMATE_DATABASE_FINAL.sql` chưa được import
   - Hoặc import không thành công

### 2. **Đang đăng nhập với pharmacy khác**
   - Thuốc thuộc pharmacy_id = 1
   - Nhưng đang đăng nhập với pharmacy_id = 3 (hoặc khác)

### 3. **Thuốc không có lô hàng (batches)**
   - Thuốc có trong bảng `medicines`
   - Nhưng không có lô nào trong bảng `batches`
   - Hoặc tất cả lô đều có `quantity = 0`

### 4. **Lô hàng không active**
   - Lô có status = 'expired' hoặc 'out_of_stock'
   - Chỉ lô có status = 'active' mới hiển thị

---

## 🔧 CÁCH KIỂM TRA

### Bước 1: Chạy script kiểm tra
```
http://localhost/CNM_NHOM32/check_sales_medicines.php
```

Script này sẽ hiển thị:
- ✅ Pharmacy ID bạn đang dùng
- 📊 Tổng số thuốc trong database
- 📦 Tổng số lô thuốc
- 📋 Danh sách 10 thuốc đầu tiên với tồn kho
- ⚠️ Thuốc có vấn đề (có lô nhưng không có hàng)

### Bước 2: Kiểm tra kết quả

#### ✅ KẾT QUẢ TỐT:
```
Pharmacy ID: 1
Tổng số thuốc: 30
Tổng số lô: 60
Lô active có hàng: 60
Số thuốc trả về: 30
Thuốc có tồn kho: 30
```

#### ❌ KẾT QUẢ XẤU:
```
Pharmacy ID: 1
Tổng số thuốc: 0
Tổng số lô: 0
Số thuốc trả về: 0
```
→ **Chưa import database!**

#### ⚠️ KẾT QUẢ CẢNH BÁO:
```
Pharmacy ID: 1
Tổng số thuốc: 30
Tổng số lô: 60
Số thuốc trả về: 30
Thuốc có tồn kho: 0
Thuốc hết hàng: 30
```
→ **Tất cả thuốc đều hết hàng!**

---

## 🛠️ CÁCH SỬA

### Trường hợp 1: Chưa có thuốc nào (Tổng số thuốc = 0)

**Giải pháp:** Import lại database

1. Mở phpMyAdmin
2. Chọn database `qlnt_db`
3. Click tab "Import"
4. Chọn file `ULTIMATE_DATABASE_FINAL.sql`
5. Click "Go"
6. Đăng nhập lại với:
   - Username: `admin`
   - Password: `123456`

---

### Trường hợp 2: Có thuốc nhưng tất cả đều hết hàng

**Giải pháp:** Thêm lô hàng mới

Chạy SQL sau trong phpMyAdmin:

```sql
-- Lấy pharmacy_id của bạn
SET @pharmacy_id = 1;

-- Thêm lô hàng cho 5 thuốc đầu tiên
INSERT INTO batches (pharmacy_id, medicine_id, supplier_id, batch_number, quantity, import_date, expiry_date, qr_code, status) VALUES
(@pharmacy_id, 1, 1, 'LOT20260505101', 100, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), CONCAT('BATCH_', UNIX_TIMESTAMP(), '_', FLOOR(RAND() * 9000 + 1000)), 'active'),
(@pharmacy_id, 2, 1, 'LOT20260505102', 100, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), CONCAT('BATCH_', UNIX_TIMESTAMP() + 1, '_', FLOOR(RAND() * 9000 + 1000)), 'active'),
(@pharmacy_id, 3, 1, 'LOT20260505103', 100, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), CONCAT('BATCH_', UNIX_TIMESTAMP() + 2, '_', FLOOR(RAND() * 9000 + 1000)), 'active'),
(@pharmacy_id, 4, 1, 'LOT20260505104', 100, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), CONCAT('BATCH_', UNIX_TIMESTAMP() + 3, '_', FLOOR(RAND() * 9000 + 1000)), 'active'),
(@pharmacy_id, 5, 1, 'LOT20260505105', 100, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), CONCAT('BATCH_', UNIX_TIMESTAMP() + 4, '_', FLOOR(RAND() * 9000 + 1000)), 'active');
```

---

### Trường hợp 3: Đang đăng nhập với pharmacy khác

**Giải pháp:** Đăng nhập lại với tài khoản đúng

1. Đăng xuất
2. Đăng nhập với:
   - Username: `admin`
   - Password: `123456`
   - (Tài khoản này thuộc pharmacy_id = 1)

---

### Trường hợp 4: Muốn thêm 200 thuốc như yêu cầu

**Bạn đã được cung cấp 6 bước SQL để thêm 40 thuốc:**

#### Bước 1: Thêm 20 thuốc đầu tiên
```sql
SET @pharmacy_id = 1;

INSERT INTO medicines (pharmacy_id, medicine_name, category_id, unit_id, price, qr_code) VALUES
(@pharmacy_id, 'Acetylcysteine 200mg', 2, 1, 4500, CONCAT('MED_', UNIX_TIMESTAMP(), '_', FLOOR(RAND() * 9000 + 1000))),
(@pharmacy_id, 'Acyclovir 400mg', 1, 1, 8500, CONCAT('MED_', UNIX_TIMESTAMP() + 1, '_', FLOOR(RAND() * 9000 + 1000))),
-- ... (18 thuốc nữa)
```

#### Bước 2: Thêm 20 thuốc tiếp theo
```sql
-- Tương tự bước 1
```

#### Bước 3-6: Thêm lô hàng cho các thuốc

**Chi tiết đầy đủ 6 bước đã được cung cấp trong chat trước.**

---

## 📊 KIỂM TRA SAU KHI SỬA

1. Chạy lại: `http://localhost/CNM_NHOM32/check_sales_medicines.php`
2. Kiểm tra:
   - ✅ Số thuốc > 0
   - ✅ Số lô > 0
   - ✅ Thuốc có tồn kho > 0
3. Vào trang bán hàng: `http://localhost/CNM_NHOM32/index.php?page=sales`
4. Kiểm tra danh sách thuốc hiển thị

---

## 🎯 KẾT LUẬN

**Nguyên nhân chính:** 
- Thuốc có trong database nhưng không có lô hàng (batches) với tồn kho > 0
- Hoặc chưa import database đầy đủ

**Giải pháp:**
1. Chạy script kiểm tra để xác định vấn đề
2. Import lại database nếu chưa có thuốc
3. Thêm lô hàng nếu thuốc đã có nhưng hết hàng
4. Chạy 6 bước SQL để thêm 40 thuốc mới (nếu muốn)

---

## 📞 HỖ TRỢ

Nếu vẫn gặp vấn đề, hãy:
1. Chạy `check_sales_medicines.php`
2. Chụp màn hình kết quả
3. Báo lại để được hỗ trợ cụ thể

---

**Ngày tạo:** 05/05/2026  
**Phiên bản:** 1.0
