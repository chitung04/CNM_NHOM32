# Hướng Dẫn Sử Dụng Hệ Thống Quản Lý Nhà Thuốc

## Đăng Nhập

### Tài khoản mặc định:
- **Quản lý (Manager):**
  - Username: `admin`
  - Password: `admin123`

- **Nhân viên (Staff):**
  - Username: `staff`
  - Password: `staff123`

## Chức Năng Theo Vai Trò

### Nhân Viên (Staff)
1. **Bán hàng**
   - Tìm kiếm thuốc theo tên
   - Thêm thuốc vào giỏ hàng
   - Áp dụng giảm giá
   - Thanh toán và in hóa đơn

2. **Tra cứu thuốc**
   - Xem danh sách thuốc
   - Kiểm tra tồn kho
   - Xem thông tin chi tiết

### Quản Lý (Manager)
Có tất cả quyền của Nhân viên, thêm:

1. **Quản lý thuốc**
   - Thêm/Sửa/Xóa thuốc
   - Tự động tạo QR code

2. **Quản lý lô thuốc**
   - Nhập kho mới
   - Theo dõi hạn sử dụng
   - Quản lý QR code lô

3. **Quản lý nhà cung cấp**
   - Thêm/Sửa/Xóa nhà cung cấp
   - Liên kết với lô hàng

4. **Báo cáo & Thống kê**
   - Báo cáo doanh thu theo thời gian
   - Thuốc bán chạy (với biểu đồ)
   - Báo cáo tồn kho
   - Cảnh báo thuốc sắp hết hạn

5. **Quản lý người dùng**
   - Thêm/Sửa/Xóa tài khoản
   - Phân quyền Staff/Manager

6. **Sao lưu & Khôi phục**
   - Tạo backup database
   - Khôi phục từ backup
   - Tải xuống file backup

## Tính Năng Nổi Bật

### 1. Hệ Thống Thông Báo Tự Động
- Cảnh báo thuốc sắp hết hàng (< 10 đơn vị)
- Cảnh báo lô sắp hết hạn (< 30 ngày)
- Hiển thị badge trên navbar
- Cập nhật realtime mỗi 30 giây

### 2. QR Code
- Tự động tạo QR code cho mỗi thuốc
- QR code cho mỗi lô hàng
- QR code trên hóa đơn
- Lưu trữ tại `assets/qrcodes/`

### 3. Tìm Kiếm AJAX
- Tìm kiếm thuốc không reload trang
- Debounce để tối ưu hiệu suất
- Hiển thị kết quả realtime

### 4. In Hóa Đơn
- Template in chuyên nghiệp
- Hiển thị QR code
- Có thể in lại hóa đơn cũ

### 5. Báo Cáo Trực Quan
- Biểu đồ Chart.js
- Filter theo khoảng thời gian
- Export dữ liệu

## Cron Job - Kiểm Tra Hết Hạn Tự Động

### Cài đặt trên Linux/Unix:
```bash
# Mở crontab
crontab -e

# Thêm dòng sau (chạy mỗi ngày lúc 00:00)
0 0 * * * /usr/bin/php /path/to/project/cron/check_expiry.php
```

### Cài đặt trên Windows:
1. Mở Task Scheduler
2. Tạo Basic Task mới
3. Trigger: Daily lúc 00:00
4. Action: Start a program
5. Program: `C:\php\php.exe`
6. Arguments: `C:\path\to\project\cron\check_expiry.php`

### Test thủ công:
```bash
php cron/check_expiry.php
```

## Bảo Mật

### Đã Implement:
- ✅ Password hashing (bcrypt)
- ✅ CSRF protection
- ✅ XSS prevention (htmlspecialchars)
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ Session timeout (30 phút)
- ✅ Rate limiting cho login
- ✅ Role-based access control
- ✅ Input validation và sanitization
- ✅ Secure file upload
- ✅ Error logging

### Khuyến Nghị:
1. Đổi mật khẩu admin mặc định
2. Sử dụng HTTPS trong production
3. Backup database thường xuyên
4. Giới hạn quyền truy cập thư mục uploads/
5. Cập nhật PHP và dependencies định kỳ

## Xử Lý Lỗi

### Lỗi thường gặp:

1. **Không kết nối được database**
   - Kiểm tra file `.env`
   - Đảm bảo MySQL đang chạy
   - Kiểm tra username/password

2. **QR code không hiển thị**
   - Kiểm tra thư mục `assets/qrcodes/` có quyền ghi
   - Đảm bảo GD extension được bật trong PHP

3. **Backup/Restore không hoạt động**
   - Kiểm tra mysqldump có trong PATH
   - Kiểm tra quyền ghi thư mục `uploads/backups/`

4. **Session timeout quá nhanh**
   - Điều chỉnh `SESSION_TIMEOUT` trong `config/config.php`

## Logs

Tất cả logs được lưu tại `logs/`:
- `app.log` - Log chung của ứng dụng
- `error.log` - Log lỗi
- `login.log` - Log đăng nhập/đăng xuất

## Hỗ Trợ

Nếu gặp vấn đề, kiểm tra:
1. File logs trong thư mục `logs/`
2. PHP error log
3. MySQL error log
4. Browser console (F12)

## Cập Nhật Hệ Thống

1. Backup database trước khi cập nhật
2. Pull code mới từ repository
3. Chạy migration SQL nếu có
4. Clear cache trình duyệt
5. Test trên môi trường staging trước

## Tối Ưu Hiệu Suất

### Đã Implement:
- Database indexing
- AJAX cho tìm kiếm
- Lazy loading cho images
- Minified CSS/JS (production)
- Query optimization

### Khuyến Nghị:
- Sử dụng Redis/Memcached cho session
- Enable OPcache trong PHP
- Sử dụng CDN cho static assets
- Optimize images
- Enable GZIP compression

## Backup & Restore

### Backup Tự Động:
Tạo script backup tự động bằng cron:
```bash
# Backup mỗi ngày lúc 2:00 AM
0 2 * * * /usr/bin/php /path/to/project/controllers/BackupController.php
```

### Backup Thủ Công:
1. Đăng nhập với tài khoản Manager
2. Vào menu "Sao lưu dữ liệu"
3. Nhấn "Tạo bản sao lưu mới"
4. Tải xuống file backup về máy

### Khôi Phục:
1. Vào menu "Sao lưu dữ liệu"
2. Chọn file backup
3. Nhấn "Khôi phục"
4. Xác nhận (cảnh báo: sẽ ghi đè dữ liệu hiện tại)

## Liên Hệ

Để được hỗ trợ, vui lòng liên hệ:
- Email: support@example.com
- Phone: 0123-456-789
