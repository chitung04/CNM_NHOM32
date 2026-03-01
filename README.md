# Pharmacy Management System (Hệ thống Quản lý Nhà thuốc)

Hệ thống quản lý nhà thuốc với đầy đủ chức năng quản lý thuốc, bán hàng, tồn kho, và báo cáo.

## Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx web server
- XAMPP/WAMP (khuyến nghị cho Windows)

## Cài đặt

### 1. Clone dự án

```bash
git clone https://github.com/chitung04/CNM_NHOM32.git
cd CNM_NHOM32
```

### 2. Cấu hình database

Tạo file `.env` từ file mẫu:

```bash
copy .env.example .env
```

Chỉnh sửa file `.env` với thông tin database của bạn:

```
DB_HOST=localhost
DB_NAME=qlnt_db
DB_USER=root
DB_PASS=
```

### 3. Import database

Có 2 cách để tạo database:

**Cách 1: Sử dụng phpMyAdmin**
- Mở phpMyAdmin
- Import file `database_schema.sql`

**Cách 2: Sử dụng script tự động**
- Truy cập: `http://localhost/CNM_NHOM32/install_database.php`
- Script sẽ tự động tạo database và import dữ liệu mẫu

### 4. Cấu hình web server

**Với XAMPP:**
- Copy thư mục dự án vào `C:\xampp\htdocs\`
- Truy cập: `http://localhost/CNM_NHOM32`

**Với Apache:**
- Cấu hình virtual host hoặc copy vào document root
- Đảm bảo mod_rewrite được bật

### 5. Phân quyền thư mục

Đảm bảo các thư mục sau có quyền ghi:
- `uploads/`
- `logs/`
- `assets/qrcodes/`

## Đăng nhập

Sau khi cài đặt, sử dụng tài khoản mặc định:

**Quản lý:**
- Username: `admin`
- Password: `admin123`

**Nhân viên:**
- Username: `staff`
- Password: `staff123`

## Cấu trúc dự án

```
CNM_NHOM32/
├── ajax/              # AJAX endpoints
├── assets/            # CSS, JS, QR codes
├── config/            # Database & app config
├── controllers/       # Business logic
├── helpers/           # Helper functions
├── models/            # Database models
├── views/             # UI templates
├── uploads/           # File uploads
├── logs/              # Application logs
└── index.php          # Entry point
```

## Tính năng chính

- ✅ Quản lý thuốc (CRUD)
- ✅ Quản lý lô hàng và hạn sử dụng
- ✅ Bán hàng và tạo hóa đơn
- ✅ Quét mã QR để tra cứu thuốc
- ✅ Báo cáo tồn kho, doanh thu
- ✅ Quản lý nhà cung cấp
- ✅ Phân quyền người dùng
- ✅ Audit log và bảo mật

## Hỗ trợ

Nếu gặp vấn đề, vui lòng tạo issue trên GitHub.

## License

MIT License
