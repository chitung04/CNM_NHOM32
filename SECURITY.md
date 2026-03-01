# Chính sách bảo mật

## Các tính năng bảo mật đã triển khai

### 1. Authentication & Authorization

#### Session Security
- Session timeout: 30 phút không hoạt động
- Session regeneration sau khi đăng nhập
- HttpOnly cookies để chống XSS
- Secure cookies (khi dùng HTTPS)
- SameSite cookies để chống CSRF

#### Password Security
- Hash mật khẩu với `password_hash()` (BCRYPT)
- Yêu cầu mật khẩu mạnh:
  - Tối thiểu 8 ký tự
  - Có chữ hoa
  - Có chữ thường
  - Có số
- Không lưu mật khẩu dạng plain text

#### Rate Limiting
- Giới hạn 5 lần đăng nhập sai trong 5 phút
- Tự động unlock sau 5 phút
- Thông báo thời gian còn lại khi bị khóa

### 2. Input Validation & Sanitization

#### XSS Prevention
- Escape tất cả output với `htmlspecialchars()`
- Sanitize input với `sanitize()` function
- Content Security Policy headers (khuyến nghị)

#### SQL Injection Prevention
- Sử dụng PDO Prepared Statements cho tất cả queries
- Không concatenate SQL strings
- Validate data types trước khi query

#### CSRF Protection
- CSRF token cho tất cả POST requests
- Token được generate mỗi session
- Validate token trước khi xử lý form

### 3. File Security

#### Upload Security
- Validate file types
- Giới hạn file size
- Rename files khi upload
- Store uploads ngoài web root (khuyến nghị)

#### Directory Protection
- `.htaccess` chặn truy cập trực tiếp vào:
  - `.env` file
  - Config files
  - Log files
  - Backup files

### 4. Database Security

#### Connection Security
- Credentials trong `.env` file (không commit lên Git)
- Sử dụng user với quyền tối thiểu (principle of least privilege)
- Encrypted connection nếu database ở remote server

#### Data Protection
- Backup định kỳ
- Validate data integrity
- Transaction rollback khi có lỗi

### 5. Logging & Monitoring

#### Audit Logging
Ghi log tất cả hành động quan trọng:
- Đăng nhập/đăng xuất
- Thêm/sửa/xóa dữ liệu
- Thay đổi quyền
- Backup/restore
- Lỗi hệ thống

#### Log Files
- `logs/auth.log` - Authentication events
- `logs/actions.log` - User actions
- `logs/data.log` - Data changes
- `logs/error.log` - System errors

### 6. Error Handling

#### Production Mode
```php
APP_ENV=production
APP_DEBUG=false
```
- Ẩn error messages chi tiết
- Log errors vào file
- Hiển thị generic error pages

#### Development Mode
```php
APP_ENV=development
APP_DEBUG=true
```
- Hiển thị error messages
- Stack traces
- Debug information

## Checklist triển khai Production

### Trước khi deploy

- [ ] Đổi `APP_ENV=production` trong `.env`
- [ ] Đổi `APP_DEBUG=false` trong `.env`
- [ ] Đổi tất cả mật khẩu mặc định
- [ ] Tạo database user với quyền tối thiểu
- [ ] Bật HTTPS
- [ ] Cấu hình firewall
- [ ] Giới hạn quyền truy cập thư mục (755 cho folders, 644 cho files)
- [ ] Xóa hoặc bảo vệ file `debug.php`, `test.php`
- [ ] Kiểm tra `.gitignore` không commit `.env`
- [ ] Backup database trước khi deploy
- [ ] Test tất cả chức năng trên staging environment

### Sau khi deploy

- [ ] Kiểm tra logs không có lỗi
- [ ] Test login/logout
- [ ] Test phân quyền
- [ ] Test CSRF protection
- [ ] Test rate limiting
- [ ] Kiểm tra backup tự động
- [ ] Cấu hình monitoring
- [ ] Cấu hình alerts

## Cấu hình Server khuyến nghị

### PHP Configuration (php.ini)

```ini
; Tắt hiển thị lỗi
display_errors = Off
display_startup_errors = Off

; Bật log lỗi
log_errors = On
error_log = /path/to/logs/php_errors.log

; Session security
session.cookie_httponly = 1
session.cookie_secure = 1  ; Nếu dùng HTTPS
session.cookie_samesite = Strict
session.use_only_cookies = 1

; File upload
upload_max_filesize = 10M
post_max_size = 10M

; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
```

### Apache Configuration

```apache
# Bật mod_rewrite
LoadModule rewrite_module modules/mod_rewrite.so

# Bật mod_headers
LoadModule headers_module modules/mod_headers.so

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"

# HTTPS redirect (nếu có SSL)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### MySQL Configuration

```sql
-- Tạo user với quyền tối thiểu
CREATE USER 'pharmacy_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON pharmacy_db.* TO 'pharmacy_user'@'localhost';
FLUSH PRIVILEGES;

-- Không dùng root user trong production!
```

## Báo cáo lỗ hổng bảo mật

Nếu phát hiện lỗ hổng bảo mật, vui lòng:

1. **KHÔNG** tạo public issue trên GitHub
2. Gửi email riêng tư đến: [your-email@example.com]
3. Mô tả chi tiết lỗ hổng
4. Cung cấp steps to reproduce
5. Đợi phản hồi trước khi công khai

## Cập nhật bảo mật

### Kiểm tra định kỳ

- [ ] Cập nhật PHP version
- [ ] Cập nhật MySQL version
- [ ] Cập nhật dependencies
- [ ] Review logs hàng tuần
- [ ] Kiểm tra failed login attempts
- [ ] Kiểm tra unusual activities
- [ ] Test backup/restore process

### Khi có incident

1. Isolate affected systems
2. Analyze logs
3. Identify root cause
4. Apply fix
5. Test thoroughly
6. Document incident
7. Update security measures
8. Notify affected users (nếu cần)

## Best Practices

### Cho Developers

- Luôn validate và sanitize input
- Sử dụng prepared statements
- Escape output
- Không hardcode credentials
- Review code trước khi merge
- Test security features
- Keep dependencies updated
- Follow OWASP guidelines

### Cho Administrators

- Thay đổi mật khẩu định kỳ
- Review user permissions
- Monitor logs
- Backup regularly
- Test restore process
- Keep system updated
- Use strong passwords
- Enable 2FA (nếu có)

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [MySQL Security](https://dev.mysql.com/doc/refman/8.0/en/security.html)
- [Apache Security Tips](https://httpd.apache.org/docs/2.4/misc/security_tips.html)

## License

Tài liệu này là một phần của dự án và tuân theo cùng license.
