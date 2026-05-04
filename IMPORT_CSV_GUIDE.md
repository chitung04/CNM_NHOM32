# 📥 HƯỚNG DẪN IMPORT LÔ THUỐC TỪ CSV

## ✅ Tính năng mới: Import hàng loạt lô thuốc

Thay vì nhập từng lô một, bây giờ bạn có thể import hàng trăm lô cùng lúc từ file CSV!

---

## 🚀 CÁCH SỬ DỤNG

### Bước 1: Vào trang Import
```
1. Đăng nhập với tài khoản Manager
2. Vào "Quản lý lô thuốc"
3. Click nút "Import CSV" (màu xanh lá)
```

### Bước 2: Tải file mẫu
```
1. Trong trang Import, click "Tải file mẫu CSV"
2. File batch_import_template.csv sẽ được tải về
3. Mở file bằng Excel hoặc Google Sheets
```

### Bước 3: Chuẩn bị dữ liệu

**Định dạng file CSV:**
```csv
medicine_id,batch_number,quantity,import_date,expiry_date,supplier_id
1,LOT001,100,2026-05-01,2028-05-01,1
2,LOT002,200,2026-05-01,2027-12-31,2
3,LOT003,150,2026-05-01,2028-06-30,1
```

**Giải thích các cột:**

| Cột | Bắt buộc | Mô tả | Ví dụ |
|-----|----------|-------|-------|
| **medicine_id** | ✅ Có | ID thuốc (xem trong danh sách bên phải) | 1 |
| **batch_number** | ✅ Có | Số lô (không được trùng) | LOT001 |
| **quantity** | ✅ Có | Số lượng (số nguyên dương) | 100 |
| **import_date** | ❌ Không | Ngày nhập (YYYY-MM-DD) | 2026-05-01 |
| **expiry_date** | ❌ Không | Hạn sử dụng (YYYY-MM-DD) | 2028-05-01 |
| **supplier_id** | ❌ Không | ID nhà cung cấp | 1 |

### Bước 4: Upload file

```
1. Click "Chọn file CSV"
2. Chọn file đã chuẩn bị
3. Tick vào:
   ✅ Bỏ qua dòng đầu tiên (header)
   ✅ Tự động tạo QR code
4. Click "Upload và Import"
```

### Bước 5: Xem kết quả

Sau khi import, hệ thống sẽ hiển thị:
- ✅ **Thành công:** Số lô import thành công
- ❌ **Thất bại:** Số lô bị lỗi
- 📊 **Tổng cộng:** Tổng số lô trong file
- 📝 **Chi tiết lỗi:** Danh sách các dòng bị lỗi và lý do

---

## 📋 VÍ DỤ THỰC TẾ

### Ví dụ 1: Import 3 lô thuốc

**File CSV:**
```csv
medicine_id,batch_number,quantity,import_date,expiry_date,supplier_id
1,LOT20260501A,500,2026-05-01,2028-05-01,1
1,LOT20260501B,300,2026-05-01,2028-05-01,1
2,LOT20260502A,200,2026-05-02,2027-12-31,2
```

**Kết quả:**
```
✅ Thành công: 3
❌ Thất bại: 0
📊 Tổng cộng: 3
```

### Ví dụ 2: Import có lỗi

**File CSV:**
```csv
medicine_id,batch_number,quantity,import_date,expiry_date,supplier_id
1,LOT001,100,2026-05-01,2028-05-01,1
999,LOT002,200,2026-05-01,2027-12-31,2
2,LOT001,150,2026-05-01,2028-06-30,1
```

**Kết quả:**
```
✅ Thành công: 1
❌ Thất bại: 2
📊 Tổng cộng: 3

Chi tiết lỗi:
- Dòng 3: Thuốc ID 999 không tồn tại
- Dòng 4: Số lô LOT001 đã tồn tại
```

---

## ⚠️ LƯU Ý QUAN TRỌNG

### 1. Định dạng file
- ✅ File phải là CSV (UTF-8)
- ✅ Dòng đầu tiên là header (tên cột)
- ✅ Các cột cách nhau bằng dấu phẩy (,)

### 2. Dữ liệu hợp lệ
- ✅ **medicine_id:** Phải tồn tại trong hệ thống
- ✅ **batch_number:** Không được trùng với lô đã có
- ✅ **quantity:** Phải là số nguyên dương (> 0)
- ✅ **Ngày tháng:** Định dạng YYYY-MM-DD (ví dụ: 2026-05-01)

### 3. Kiểm tra trước khi import
- ✅ Xem danh sách thuốc bên phải để lấy medicine_id đúng
- ✅ Kiểm tra số lô chưa tồn tại trong hệ thống
- ✅ Đảm bảo ngày hết hạn > ngày nhập

### 4. QR Code
- ✅ Nếu tick "Tự động tạo QR code", mỗi lô sẽ có QR riêng
- ✅ QR code có format: BATCH_timestamp_random
- ✅ File QR được lưu trong `assets/qrcodes/`

---

## 🔧 XỬ LÝ LỖI THƯỜNG GẶP

### Lỗi 1: "Thuốc ID X không tồn tại"
**Nguyên nhân:** medicine_id không có trong hệ thống  
**Giải pháp:** 
1. Xem danh sách thuốc bên phải trang Import
2. Sử dụng medicine_id đúng
3. Hoặc thêm thuốc mới trước khi import

### Lỗi 2: "Số lô X đã tồn tại"
**Nguyên nhân:** batch_number bị trùng  
**Giải pháp:** 
1. Đổi số lô thành số khác
2. Hoặc kiểm tra lô cũ có thể xóa không

### Lỗi 3: "quantity phải là số nguyên dương"
**Nguyên nhân:** Số lượng <= 0 hoặc không phải số  
**Giải pháp:** 
1. Đảm bảo quantity là số nguyên
2. Số lượng phải > 0

### Lỗi 4: "Thiếu dữ liệu (cần ít nhất 3 cột)"
**Nguyên nhân:** Dòng CSV thiếu cột  
**Giải pháp:** 
1. Đảm bảo mỗi dòng có đủ 3 cột đầu tiên
2. Các cột cách nhau bằng dấu phẩy

### Lỗi 5: "File phải có định dạng CSV"
**Nguyên nhân:** Upload file không phải CSV  
**Giải pháp:** 
1. Lưu file dạng CSV (UTF-8)
2. Không upload file Excel (.xlsx)

---

## 💡 TIPS & TRICKS

### Tip 1: Import số lượng lớn
```
- Chia nhỏ file thành nhiều batch (mỗi file 100-200 dòng)
- Import từng batch để dễ kiểm soát lỗi
- Backup database trước khi import số lượng lớn
```

### Tip 2: Tạo file CSV từ Excel
```
1. Mở Excel
2. Nhập dữ liệu theo format
3. File → Save As → CSV UTF-8 (Comma delimited)
4. Chọn vị trí lưu và Save
```

### Tip 3: Kiểm tra dữ liệu trước
```
1. Import file nhỏ (3-5 dòng) để test
2. Kiểm tra kết quả
3. Nếu OK, import file lớn
```

### Tip 4: Xử lý ngày tháng
```
- Định dạng chuẩn: YYYY-MM-DD
- Ví dụ: 2026-05-01 (1 tháng 5 năm 2026)
- Không dùng: 01/05/2026 hoặc 05-01-2026
```

### Tip 5: Tự động điền ngày
```
- Nếu không điền import_date → Tự động = hôm nay
- Nếu không điền expiry_date → Tự động = +2 năm
- Nếu không điền supplier_id → Để trống (NULL)
```

---

## 📊 DEMO NHANH

### Tạo file CSV test:

**Bước 1:** Tạo file `test_import.csv`:
```csv
medicine_id,batch_number,quantity,import_date,expiry_date,supplier_id
1,TEST001,100,2026-05-05,2028-05-05,1
2,TEST002,200,2026-05-05,2027-12-31,1
3,TEST003,150,2026-05-05,2028-06-30,2
```

**Bước 2:** Upload và import

**Bước 3:** Kiểm tra kết quả:
```
1. Vào "Quản lý lô thuốc"
2. Tìm TEST001, TEST002, TEST003
3. ✅ Thấy 3 lô mới với QR code
```

---

## 🎯 CHECKLIST TRƯỚC KHI IMPORT

- [ ] File đúng định dạng CSV (UTF-8)
- [ ] Dòng đầu tiên là header
- [ ] medicine_id đã kiểm tra trong hệ thống
- [ ] batch_number không trùng
- [ ] quantity là số nguyên dương
- [ ] Ngày tháng đúng format YYYY-MM-DD
- [ ] Đã test với file nhỏ trước
- [ ] Đã backup database (nếu import số lượng lớn)

---

## 🎉 KẾT LUẬN

**Tính năng Import CSV giúp:**
- ⚡ Tiết kiệm thời gian (import hàng trăm lô trong vài giây)
- ✅ Giảm lỗi nhập liệu
- 📊 Dễ quản lý dữ liệu lớn
- 🔄 Tự động tạo QR code
- 📝 Báo cáo chi tiết lỗi

**Sử dụng khi:**
- Nhập kho số lượng lớn
- Chuyển dữ liệu từ hệ thống cũ
- Cập nhật hàng loạt lô thuốc

---

**Ngày tạo:** 05/05/2026  
**Tính năng:** Import CSV cho lô thuốc  
**Trạng thái:** ✅ READY TO USE
