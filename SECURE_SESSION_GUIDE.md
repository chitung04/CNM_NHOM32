# 🔒 Hệ thống Session Bảo mật - Hướng dẫn sử dụng

## Tính năng chính

### ✅ Session Isolation (Cách ly Session)
- **Mỗi tab/cửa sổ trình duyệt có session riêng biệt**
- Copy link và paste vào tab mới → **Phải đăng nhập lại**
- Session không được chia sẻ giữa các cửa sổ trình duyệt

### ✅ Browser Fingerprinting
- **Xác định duy nhất mỗi trình duyệt**
- Dựa trên: User Agent, Accept Language, Accept Encoding, IP Address
- Nếu thay đổi trình duyệt → Session tự động hủy

### ✅ Session Security
- **Session Token**: Unique cho mỗi lần đăng nhập
- **CSRF Protection**: Chống tấn công Cross-Site Request Forgery
- **Secure Cookies**: HttpOnly, SameSite=Strict
- **Session Timeout**: Tự động hết hạn sau 30 phút không hoạt động

## Cách hoạt động

### 1. Đăng nhập
```php
// Tạo session token unique
$sessionToken = hash('sha256', uniqid() + time() + browserFingerprint + random_bytes(32));

// Lưu vào session và cookie
$_SESSION['session_token'] = $sessionToken;
setcookie('auth_token', $sessionToken, 0, '/', '', false, true);
```

### 2. Xác thực mỗi request
```php
// Kiểm tra session token
$cookieToken = $_COOKIE['auth_token'] ?? '';
if ($cookieToken !== $_SESSION['session_token']) {
    // Hủy session
    destroySession();
}

// Kiểm tra browser fingerprint
if ($_SESSION['browser_fingerprint'] !== currentBrowserFingerprint()) {
    // Hủy session
    destroySession();
}
```

### 3. Session timeout
```php
// Kiểm tra thời gian không hoạt động
if (time() - $_SESSION['last_activity'] > 1800) { // 30 phút
    destroySession();
}
```

## Test hệ thống

### 🧪 Test 1: Session Isolation
1. Đăng nhập vào hệ thống
2. Copy URL từ thanh địa chỉ
3. Mở tab/cửa sổ trình duyệt mới
4. Paste URL vào tab mới
5. **Kết quả**: Sẽ redirect về trang login

### 🧪 Test 2: Browser Fingerprint
1. Đăng nhập trên Chrome
2. Copy session cookie
3. Paste cookie vào Firefox
4. **Kết quả**: Session bị hủy vì browser fingerprint khác

### 🧪 Test 3: Session Timeout
1. Đăng nhập vào hệ thống
2. Để idle 30 phút
3. Thực hiện bất kỳ action nào
4. **Kết quả**: Redirect về login với thông báo timeout

## Files đã tạo/cập nhật

### 📁 Files mới
- `helpers/secure_session.php` - Hệ thống session bảo mật
- `helpers/public_access.php` - Hệ thống truy cập công khai cho QR codes
- `public_medicine_info.php` - Trang thông tin thuốc công khai
- `update_qr_to_public_access.php` - Script cập nhật QR codes
- `test_secure_session.php` - Trang test hệ thống

### 📝 Files đã cập nhật
- `index.php` - Sử dụng SecureSession thay vì session thường
- `controllers/AuthController.php` - Tích hợp secure session
- `views/auth/login.php` - Thêm thông báo session security
- `medicine_info.php` - Hỗ trợ public access token

## Cách sử dụng

### 1. Đăng nhập bình thường
```
http://localhost/CNM_NHOM32/index.php?page=auth&action=login
```

### 2. Test secure session
```
http://localhost/CNM_NHOM32/test_secure_session.php
```

### 3. Cập nhật QR codes sang public access
```
http://localhost/CNM_NHOM32/update_qr_to_public_access.php
```

### 4. Xem thông tin thuốc công khai
```
http://localhost/CNM_NHOM32/public_medicine_info.php?qr=BATCH_1735000101_2001&token=abc123
```

## Lợi ích

### 🔐 Bảo mật cao
- Ngăn chặn session hijacking
- Chống tấn công CSRF
- Xác thực browser fingerprint

### 🚫 Ngăn chia sẻ session
- Mỗi tab/cửa sổ phải đăng nhập riêng
- Copy link không tự động đăng nhập
- Session cách ly hoàn toàn

### ⏰ Quản lý timeout
- Tự động đăng xuất khi không hoạt động
- Thông báo rõ ràng về lý do logout
- Session stats chi tiết

### 📱 QR Code công khai
- QR codes có thể truy cập mà không cần đăng nhập
- Token tạm thời với thời hạn
- Bảo mật vẫn được đảm bảo

## Cấu hình

### Session Settings
```php
ini_set('session.cookie_httponly', 1);     // Chỉ HTTP, không JavaScript
ini_set('session.use_only_cookies', 1);    // Chỉ dùng cookies
ini_set('session.cookie_secure', 0);       // Set 1 nếu dùng HTTPS
ini_set('session.cookie_samesite', 'Strict'); // Chống CSRF
ini_set('session.use_strict_mode', 1);     // Strict mode
ini_set('session.cookie_lifetime', 0);     // Session cookie
```

### Timeout Settings
```php
define('SESSION_TIMEOUT', 1800); // 30 phút
```

## Troubleshooting

### ❓ Tại sao phải đăng nhập lại khi copy link?
- Đây là tính năng bảo mật, ngăn chia sẻ session giữa các tab
- Mỗi tab/cửa sổ cần xác thực riêng

### ❓ Session bị hủy khi chuyển trình duyệt?
- Browser fingerprint khác nhau giữa các trình duyệt
- Đây là tính năng bảo mật để ngăn session hijacking

### ❓ QR codes không hoạt động?
- Chạy script `update_qr_to_public_access.php` để cập nhật
- QR codes sẽ có public access token

### ❓ Làm sao để disable secure session?
- Thay `require_once 'helpers/secure_session.php'` bằng `require_once 'helpers/auth.php'` trong `index.php`
- Hoặc comment out các dòng secure session

## Kết luận

Hệ thống session bảo mật đã được triển khai thành công với các tính năng:

✅ **Session isolation** - Không chia sẻ session giữa tabs  
✅ **Browser fingerprinting** - Xác thực trình duyệt  
✅ **CSRF protection** - Chống tấn công cross-site  
✅ **Session timeout** - Tự động hết hạn  
✅ **Public QR access** - QR codes truy cập công khai  
✅ **Secure cookies** - Cookies bảo mật  

Bây giờ khi copy link và paste vào tab mới, hệ thống sẽ yêu cầu đăng nhập lại như yêu cầu của bạn! 🎉