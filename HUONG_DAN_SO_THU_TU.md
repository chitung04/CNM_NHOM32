# Hướng dẫn Số thứ tự thuốc (STT)

## Đã thực hiện

Đã thêm cột **STT** (Số thứ tự) vào các danh sách hiển thị thuốc và lô hàng:

### 1. Trang quản lý thuốc (`views/medicines/index.php`)
- Thêm cột "STT" hiển thị số thứ tự 1, 2, 3, 4...
- Cột "ID" vẫn giữ nguyên để hiển thị `medicine_id` từ database
- Danh sách hiển thị theo thứ tự tăng dần

**Cấu trúc bảng:**
```
| STT | ID | Tên thuốc | Danh mục | Đơn vị | Giá bán | Tồn kho | Thao tác |
|-----|----|-----------| ---------|--------|---------|---------|----------|
|  1  | 1  | Amoxicillin...                                           |
|  2  | 2  | Cefixime...                                              |
|  3  | 3  | Azithromycin...                                          |
```

### 2. Trang test QR codes (`tools/qr_list.php`)
- Thêm cột "STT" cho bảng Medicines
- Thêm cột "STT" cho bảng Batches
- Sắp xếp theo thứ tự tăng dần (ORDER BY medicine_id, batch_id)

**Cấu trúc bảng Medicines:**
```
| STT | ID | Tên thuốc | Danh mục | Giá | QR Code |
|-----|----|-----------| ---------|-----|---------|
|  1  | 1  | Amoxicillin 500mg | Thuốc kê đơn | 3,500 VNĐ | MED_... |
```

**Cấu trúc bảng Batches:**
```
| STT | ID | Thuốc | Số lượng | Hạn sử dụng | Nhà cung cấp | QR Code |
|-----|----| ------|----------|-------------|--------------|---------|
|  1  | 1  | Amoxicillin... | 500 | 2025-12-31 | Pharbaco | BATCH_... |
```

### 3. Script tạo QR codes (`tools/generate_all_qrcodes.php`)
- Cập nhật để tự động đọc TẤT CẢ dữ liệu từ database
- Không còn hardcode 29 medicines nữa
- Tự động phát hiện và tạo QR cho:
  - Tất cả 61 medicines
  - Tất cả 62 batches
  - Tất cả 5 invoices

**Cách chạy:**
```bash
php tools/generate_all_qrcodes.php
```

## Lợi ích

1. **Dễ đọc hơn**: Người dùng có thể đếm và tham chiếu thuốc theo số thứ tự đơn giản (1, 2, 3...)
2. **Vẫn giữ ID gốc**: Cột ID vẫn hiển thị `medicine_id` từ database để tra cứu
3. **Tự động**: Số thứ tự tự động tăng khi có thêm thuốc mới
4. **Linh hoạt**: Script generate QR codes tự động đọc từ database, không cần update code

## Cách sử dụng

### Xem danh sách thuốc có STT:
1. Đăng nhập vào hệ thống
2. Vào menu "Quản lý thuốc"
3. Xem cột "STT" ở đầu bảng

### Xem danh sách QR codes có STT:
1. Mở trình duyệt: `http://localhost/CNM_NHOM32/tools/qr_list.php`
2. Xem cột "STT" trong bảng Medicines và Batches

### Tạo QR codes cho tất cả dữ liệu:
```bash
# Sau khi import database
mysql -u root -p < database_schema.sql

# Tạo QR codes
php tools/generate_all_qrcodes.php
```

## Kết quả

- ✅ Cột STT hiển thị số thứ tự 1, 2, 3... trong tất cả danh sách
- ✅ Cột ID vẫn giữ nguyên để tra cứu database
- ✅ Script generate QR tự động cho 61 medicines + 62 batches + 5 invoices
- ✅ Không cần hardcode, tự động đọc từ database
