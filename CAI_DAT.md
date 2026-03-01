# Hướng Dẫn Cài Đặt Hệ Thống Quản Lý Nhà Thuốc

## Yêu Cầu Hệ Thống

- **PHP**: >= 7.4
- **MySQL**: >= 5.7 hoặc MariaDB >= 10.2
- **Web Server**: Apache hoặc Nginx
- **Extensions PHP cần thiết**:
  - PDO
  - PDO_MySQL
  - GD (cho QR Code)
  - mbstring
  - json
  - session

## Cài Đặt Trên Windows (XAMPP)

### Bước 1: Cài Đặt XAMPP

1. Tải XAMPP từ: https://www.apachefriends.org/
2. Cài đặt XAMPP (khuyến nghị cài vào `C:\xampp`)
3. Khởi động XAMPP Control Panel
4. Start Apache và MySQL

### Bước 2: Copy Project

1. Copy toàn bộ thư mục project vào `C:\xampp\htdocs\pharmacy`
2. Hoặc đặt tên thư mục khác tùy ý

### Bước 3: Tạo Database

1. Mở trình duyệt, truy cập: http://localhost/phpmyadmin
2. Click "New" để tạo database mới
3. Tên database: `pharmacy_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Bước 4: Import Database Schema

1. Trong phpMyAdmin, chọn database `pharmacy_db`
2. Click tab "Import"
3. Click "Choose File" và chọn file `database_schema.sql` trong thư mục project
4. Click "Go" để import
5. Đợi import hoàn tất (sẽ tạo tất cả bảng và dữ liệu mẫu)

### Bước 5: Cấu Hình File .env

File `.env` đã có sẵn với cấu hình mặc định cho XAMPP:

```env
DB_HOST=localhost
DB_NAME=pharmacy_db
DB_USER=root
DB_PASS=
DB_CHARSET=utf8
BASE_URL=http://localhost/pharmacy
```

**Lưu ý**: 
- Nếu bạn đặt project trong thư mục khác, sửa `BASE_URL`
- Ví dụ: `http://localhost/ten-thu-muc-cua-ban`
- Nếu MySQL có password, thêm vào `DB_PASS`

### Bước 6: Cấp Quyền Thư Mục

Đảm bảo các thư mục sau có quyền ghi (writable):

- `assets/qrcodes/` - Lưu QR code
- `uploads/backups/` - Lưu file backup
- `logs/` - Lưu log files

Trên Windows với XAMPP, thường không cần làm gì thêm.

### Bước 7: Truy Cập Hệ Thống

1. Mở trình duyệt
2. Truy cập: **http://localhost/pharmacy**
3. Hoặc: **http://localhost/pharmacy/login.php**

### Tài Khoản Đăng Nhập Mặc Định

**Quản lý (Manager):**
- Username: `admin`
- Password: `admin123`

**Nhân viên (Staff):**
- Username: `staff`
- Password: `staff123`

---

## Cài Đặt Trên Linux/Ubuntu

### Bước 1: Cài Đặt LAMP Stack

```bash
# Update package list
sudo apt update

# Cài đặt Apache
sudo apt install apache2

# Cài đặt MySQL
sudo apt install mysql-server

# Cài đặt PHP và extensions
sudo apt install php php-mysql php-gd php-mbstring php-json

# Restart Apache
sudo systemctl restart apache2
```

### Bước 2: Copy Project

```bash
# Copy project vào thư mục web
sudo cp -r /path/to/project /var/www/html/pharmacy

# Cấp quyền
sudo chown -R www-data:www-data /var/www/html/pharmacy
sudo chmod -R 755 /var/www/html/pharmacy
```

### Bước 3: Tạo Database

```bash
# Đăng nhập MySQL
sudo mysql -u root -p

# Trong MySQL console:
CREATE DATABASE pharmacy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pharmacy_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON pharmacy_db.* TO 'pharmacy_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Bước 4: Import Database

```bash
mysql -u pharmacy_user -p pharmacy_db < /var/www/html/pharmacy/database_schema.sql
```

### Bước 5: Cấu Hình .env

```bash
cd /var/www/html/pharmacy
nano .env
```

Sửa thông tin database:
```env
DB_HOST=localhost
DB_NAME=pharmacy_db
DB_USER=pharmacy_user
DB_PASS=your_password
BASE_URL=http://localhost/pharmacy
```

### Bước 6: Cấp Quyền Thư Mục

```bash
# Tạo và cấp quyền cho thư mục cần thiết
sudo mkdir -p assets/qrcodes uploads/backups logs
sudo chown -R www-data:www-data assets/qrcodes uploads/backups logs
sudo chmod -R 775 assets/qrcodes uploads/backups logs
```

### Bước 7: Cấu Hình Apache (Optional)

Tạo Virtual Host:

```bash
sudo nano /etc/apache2/sites-available/pharmacy.conf
```

Nội dung:
```apache
<VirtualHost *:80>
    ServerName pharmacy.local
    DocumentRoot /var/www/html/pharmacy
    
    <Directory /var/www/html/pharmacy>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/pharmacy_error.log
    CustomLog ${APACHE_LOG_DIR}/pharmacy_access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite pharmacy.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Thêm vào `/etc/hosts`:
```bash
sudo nano /etc/hosts
# Thêm dòng:
127.0.0.1   pharmacy.local
```

Truy cập: http://pharmacy.local

---

## Cài Đặt Cron Job (Tự Động Kiểm Tra Hết Hạn)

### Windows (Task Scheduler)

1. Mở Task Scheduler
2. Create Basic Task
3. Name: "Pharmacy Expiry Check"
4. Trigger: Daily at 00:00
5. Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\pharmacy\cron\check_expiry.php`
6. Finish

### Linux (Crontab)

```bash
# Mở crontab
crontab -e

# Thêm dòng (chạy mỗi ngày lúc 00:00)
0 0 * * * /usr/bin/php /var/www/html/pharmacy/cron/check_expiry.php
```

### Test Cron Job

```bash
# Windows
C:\xampp\php\php.exe C:\xampp\htdocs\pharmacy\cron\check_expiry.php

# Linux
php /var/www/html/pharmacy/cron/check_expiry.php
```

---

## Kiểm Tra Cài Đặt

### 1. Kiểm Tra PHP Extensions

Tạo file `info.php` trong thư mục project:

```php
<?php
phpinfo();
?>
```

Truy cập: http://localhost/pharmacy/info.php

Kiểm tra các extensions:
- ✅ PDO
- ✅ pdo_mysql
- ✅ GD
- ✅ mbstring
- ✅ json

**Xóa file này sau khi kiểm tra xong!**

### 2. Kiểm Tra Database Connection

Truy cập: http://localhost/pharmacy/debug.php

Nếu thấy "Database connection successful!" là OK.

### 3. Kiểm Tra Quyền Thư Mục

```bash
# Linux
ls -la assets/qrcodes/
ls -la uploads/backups/
ls -la logs/

# Phải có quyền ghi (w)
```

### 4. Test Các Chức Năng

1. ✅ Đăng nhập
2. ✅ Xem dashboard
3. ✅ Tìm kiếm thuốc
4. ✅ Thêm thuốc mới (Manager)
5. ✅ Tạo QR code
6. ✅ Bán hàng
7. ✅ In hóa đơn
8. ✅ Xem báo cáo (Manager)
9. ✅ Backup database (Manager)

---

## Xử Lý Lỗi Thường Gặp

### Lỗi 1: "Access denied for user"

**Nguyên nhân**: Sai thông tin database

**Giải pháp**:
1. Kiểm tra file `.env`
2. Đảm bảo database đã được tạo
3. Kiểm tra username/password MySQL

### Lỗi 2: "Table doesn't exist"

**Nguyên nhân**: Chưa import database schema

**Giải pháp**:
1. Import lại file `database_schema.sql`
2. Kiểm tra database có đúng tên không

### Lỗi 3: QR Code không hiển thị

**Nguyên nhân**: 
- Thiếu GD extension
- Không có quyền ghi thư mục

**Giải pháp**:
```bash
# Kiểm tra GD extension
php -m | grep gd

# Cấp quyền thư mục
chmod 775 assets/qrcodes/
```

### Lỗi 4: "404 Not Found" khi truy cập

**Nguyên nhân**: 
- Sai đường dẫn
- mod_rewrite chưa enable

**Giải pháp**:
```bash
# Enable mod_rewrite (Linux)
sudo a2enmod rewrite
sudo systemctl restart apache2

# Kiểm tra file .htaccess có tồn tại
```

### Lỗi 5: Session không hoạt động

**Nguyên nhân**: Session path không có quyền ghi

**Giải pháp**:
```bash
# Kiểm tra session path
php -i | grep session.save_path

# Cấp quyền
sudo chmod 777 /var/lib/php/sessions
```

### Lỗi 6: "Warning: mkdir(): Permission denied"

**Nguyên nhân**: Không có quyền tạo thư mục

**Giải pháp**:
```bash
# Cấp quyền cho thư mục project
sudo chown -R www-data:www-data /var/www/html/pharmacy
```

---

## Bảo Mật Sau Khi Cài Đặt

### 1. Đổi Mật Khẩu Admin

1. Đăng nhập với tài khoản admin
2. Vào "Quản lý người dùng"
3. Sửa user admin
4. Đổi password mạnh hơn

### 2. Xóa File Debug

```bash
rm debug.php
rm info.php
```

### 3. Tắt Debug Mode (Production)

Sửa file `.env`:
```env
APP_ENV=production
APP_DEBUG=false
```

### 4. Bảo Vệ Thư Mục Uploads

Tạo file `.htaccess` trong `uploads/`:
```apache
Options -Indexes
<FilesMatch "\.(php|php3|php4|php5|phtml)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

### 5. Giới Hạn Quyền File

```bash
# Linux
find /var/www/html/pharmacy -type f -exec chmod 644 {} \;
find /var/www/html/pharmacy -type d -exec chmod 755 {} \;
chmod 775 assets/qrcodes uploads/backups logs
```

---

## Nâng Cấp Hệ Thống

### Backup Trước Khi Nâng Cấp

1. Backup database qua giao diện web
2. Hoặc dùng mysqldump:
```bash
mysqldump -u root -p pharmacy_db > backup_$(date +%Y%m%d).sql
```

### Pull Code Mới

```bash
cd /var/www/html/pharmacy
git pull origin main
```

### Chạy Migration (nếu có)

```bash
mysql -u root -p pharmacy_db < migrations/update_xxx.sql
```

### Clear Cache

```bash
# Clear PHP opcache
sudo systemctl restart apache2

# Clear browser cache
Ctrl + Shift + Delete
```

---

## Liên Hệ Hỗ Trợ

Nếu gặp vấn đề trong quá trình cài đặt:

1. Kiểm tra file logs: `logs/error.log`
2. Kiểm tra Apache error log
3. Kiểm tra MySQL error log
4. Liên hệ: support@example.com

---

## Checklist Cài Đặt

- [ ] Cài đặt XAMPP/LAMP
- [ ] Copy project vào thư mục web
- [ ] Tạo database `pharmacy_db`
- [ ] Import file `database_schema.sql`
- [ ] Cấu hình file `.env`
- [ ] Cấp quyền thư mục (qrcodes, backups, logs)
- [ ] Test truy cập: http://localhost/pharmacy
- [ ] Đăng nhập thành công
- [ ] Test các chức năng chính
- [ ] Cài đặt cron job (optional)
- [ ] Đổi password admin
- [ ] Xóa file debug
- [ ] Tắt debug mode (production)

---

**Chúc bạn cài đặt thành công! 🎉**
