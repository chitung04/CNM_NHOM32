# Sửa lỗi QR Code không hiển thị trong trang tra cứu thuốc

## Vấn đề
Trong phần tra cứu thuốc (`views/medicines/index.php` và `views/medicines/view.php`), mã QR không hiển thị mặc dù trong database có giá trị `qr_code`.

## Nguyên nhân
1. **Database có giá trị QR code** (ví dụ: `MED_1735000101_2001`)
2. **Nhưng file hình ảnh QR code không tồn tại** trong thư mục `assets/qrcodes/`
3. Trong thư mục `assets/qrcodes/` chỉ có QR code của **lô thuốc** (BATCH_*.png), không có QR code của **thuốc** (MED_*.png)

## Giải pháp

### 1. Tạo script để sinh QR code cho thuốc
**File mới: `generate_missing_medicine_qr.php`**
- Script này sẽ quét database tìm tất cả thuốc có `qr_code` nhưng chưa có file hình ảnh
- Tự động tạo file QR code PNG cho các thuốc đó
- Hiển thị tiến trình và kết quả

### 2. Cập nhật trang chi tiết thuốc
**File: `views/medicines/view.php`**
- Kiểm tra xem file QR code có tồn tại không trước khi hiển thị
- Nếu có giá trị `qr_code` nhưng không có file:
  - Hiển thị cảnh báo "Hình ảnh QR code chưa được tạo"
  - Hiển thị nút "Tạo QR code" (chỉ cho Manager)
- Nếu có file: hiển thị hình ảnh QR code bình thường

### 3. Cập nhật trang danh sách thuốc
**File: `views/medicines/index.php`**
- Thêm nút "Tạo QR code" ở header (chỉ cho Manager)
- Trong bảng danh sách:
  - Kiểm tra file QR code có tồn tại không
  - Nếu không tồn tại: hiển thị icon cảnh báo "Chưa tạo"
  - Nếu tồn tại: hiển thị bình thường với các nút xem và test

### 4. File kiểm tra (debug)
**File mới: `check_medicine_qr.php`**
- Script để kiểm tra trạng thái QR code trong database
- So sánh giữa database và file thực tế
- Hiển thị thống kê

## Cách sử dụng

### Bước 1: Kiểm tra trạng thái hiện tại
Truy cập: `http://[your-ip]/CNM_NHOM32/check_medicine_qr.php`

### Bước 2: Tạo QR code cho thuốc
Truy cập: `http://[your-ip]/CNM_NHOM32/generate_missing_medicine_qr.php`

Hoặc trong trang quản lý thuốc, click nút "Tạo QR code" ở góc trên bên phải.

### Bước 3: Kiểm tra lại
Quay lại trang tra cứu thuốc hoặc xem chi tiết thuốc để kiểm tra QR code đã hiển thị.

## Lưu ý
- QR code chỉ hoạt động khi truy cập qua IP (không phải localhost)
- QR code sẽ dẫn đến trang `public_medicine_info.php` (không cần đăng nhập)
- Chỉ Manager mới thấy nút "Tạo QR code"
- Script tạo QR code sử dụng API QR Server (https://api.qrserver.com)

## Files đã thay đổi
1. ✅ `views/medicines/view.php` - Thêm kiểm tra file QR code tồn tại
2. ✅ `views/medicines/index.php` - Thêm kiểm tra file và nút tạo QR
3. ✅ `generate_missing_medicine_qr.php` - Script tạo QR code (MỚI)
4. ✅ `check_medicine_qr.php` - Script kiểm tra trạng thái (MỚI)
