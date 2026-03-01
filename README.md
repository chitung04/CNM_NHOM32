# Hệ thống quản lý bán thuốc cho nhà thuốc tư nhân

## Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx với mod_rewrite
- WAMP/XAMPP/LAMP

## Cài đặt

### Bước 1: Cấu hình môi trường

1. Copy file `.env.example` thành `.env`:
```bash
copy .env.example .env
```

2. Mở file `.env` và điều chỉnh thông tin:
```env
DB_HOST=localhost
DB_NAME=pharmacy_db
DB_USER=root
DB_PASS=your_password_here
APP_ENV=development
BASE_URL=http://localhost/your_folder_name
```

**LƯU Ý BẢO MẬT:**
- Không commit file `.env` lên Git
- Trong production, đặt `APP_ENV=production` và `APP_DEBUG=false`
- Đổi mật khẩu database mạnh hơn

### Bước 2: Tạo database

1. Mở phpMyAdmin hoặc MySQL command line
2. Tạo database mới tên `pharmacy_db`:
```sql
CREATE DATABASE pharmacy_db CHARACTER SET utf8 COLLATE utf8_unicode_ci;
```

3. Import file `database_schema.sql` (đã bao gồm cả indexes):
   - Trong phpMyAdmin: Chọn database `pharmacy_db` → Import → Chọn file `database_schema.sql`
   - Hoặc dùng command line:
```bash
mysql -u root -p pharmacy_db < database_schema.sql
```

### Bước 3: Phân quyền thư mục

Đảm bảo các thư mục sau có quyền ghi (chmod 755 hoặc 777):
- `assets/qrcodes/`
- `uploads/backups/`
- `logs/`

### Bước 4: Truy cập hệ thống

Mở trình duyệt và truy cập:
```
http://localhost/your_folder_name/
```

## Tính năng bảo mật

### Đã triển khai:
- ✅ **CSRF Protection**: Tất cả form POST đều có CSRF token
- ✅ **XSS Prevention**: Escape tất cả output với htmlspecialchars()
- ✅ **SQL Injection Prevention**: Sử dụng Prepared Statements
- ✅ **Session Security**: HttpOnly cookies, session regeneration
- ✅ **Rate Limiting**: Giới hạn số lần đăng nhập sai (5 lần/5 phút)
- ✅ **Strong Password**: Yêu cầu mật khẩu mạnh (8+ ký tự, chữ hoa, chữ thường, số)
- ✅ **Input Validation**: Validate và sanitize tất cả input
- ✅ **Audit Logging**: Log tất cả hành động quan trọng
- ✅ **Environment Variables**: Credentials trong .env file
- ✅ **Error Handling**: Ẩn lỗi trong production mode

### Logs:
Hệ thống ghi log vào thư mục `logs/`:
- `auth.log` - Đăng nhập/đăng xuất
- `actions.log` - Các hành động quan trọng
- `data.log` - Thay đổi dữ liệu (CRUD)
- `error.log` - Lỗi hệ thống

## Tài khoản mặc định

### Quản lý
- Username: `admin`
- Password: `admin123`

### Nhân viên
- Username: `staff`
- Password: `staff123`

## Chức năng chính

### Dành cho Nhân viên (Staff)
- ✅ Bán hàng
- ✅ Tra cứu thuốc
- ✅ Kiểm tra tồn kho
- ✅ In hóa đơn

### Dành cho Quản lý (Manager)
- ✅ Tất cả chức năng của Nhân viên
- ✅ Quản lý thuốc (thêm/sửa/xóa)
- ✅ Quản lý lô thuốc (nhập kho)
- ✅ Quản lý nhà cung cấp
- ✅ Quản lý người dùng
- ✅ Báo cáo doanh thu
- ✅ Báo cáo tồn kho
- ✅ Báo cáo thuốc sắp hết hạn
- ✅ Sao lưu/Khôi phục dữ liệu

## Công nghệ sử dụng

- **Backend**: PHP (MVC Pattern)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **AJAX**: jQuery
- **QR Code**: Google Charts API
- **Icons**: Bootstrap Icons

## Cấu trúc thư mục

```
pharmacy-management/
├── config/              # Cấu hình
├── models/              # Models (Database logic)
├── views/               # Views (Giao diện)
├── controllers/         # Controllers (Business logic)
├── helpers/             # Helper functions
├── ajax/                # AJAX handlers
├── assets/              # CSS, JS, Images
│   ├── css/
│   ├── js/
│   ├── images/
│   └── qrcodes/        # QR codes được tạo
├── uploads/             # File uploads
│   └── backups/        # Database backups
└── index.php           # Entry point
```

## Xử lý lỗi thường gặp

### Lỗi: Internal Server Error

1. Kiểm tra file `.htaccess` có tồn tại không
2. Bật error reporting trong `index.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```
3. Kiểm tra Apache error log

### Lỗi: Database connection failed

1. Kiểm tra MySQL service đang chạy
2. Kiểm tra thông tin trong `config/database.php`
3. Đảm bảo database `pharmacy_db` đã được tạo

### Lỗi: Page not found

1. Kiểm tra mod_rewrite đã được bật trong Apache
2. Kiểm tra file `.htaccess`
3. Thử truy cập trực tiếp: `index.php?page=auth&action=login`

## Hỗ trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
1. PHP version >= 7.4
2. MySQL service đang chạy
3. Database đã được import
4. Thư mục có quyền ghi

## License

Đồ án môn học - Chỉ dùng cho mục đích học tập
