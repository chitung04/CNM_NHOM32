# 🚀 Hướng dẫn Upload hệ thống lên Host

## 📋 Chuẩn bị trước khi upload

### 1. Kiểm tra hệ thống
- ✅ QR Code system hoạt động
- ✅ Database có 62 lô thuốc
- ✅ 186 QR code files
- ✅ File medicine_info.php tồn tại

### 2. Cần chuẩn bị
- Hosting hỗ trợ PHP 7.4+ và MySQL
- FTP/SFTP client (FileZilla, WinSCP)
- Thông tin database từ hosting

## 🔧 Các bước thực hiện

### Bước 1: Chuẩn bị files
1. **Tạo file .env cho production**
2. **Backup database**
3. **Kiểm tra permissions**
4. **Tối ưu hóa code**

### Bước 2: Upload files
1. **Upload tất cả files PHP**
2. **Upload thư mục assets (bao gồm QR codes)**
3. **Tạo database trên hosting**
4. **Import database**

### Bước 3: Cấu hình
1. **Cập nhật .env với thông tin hosting**
2. **Kiểm tra permissions thư mục**
3. **Test hệ thống**

## 📝 Chi tiết từng bước

### Bước 1.1: Tạo .env cho production
```env
# Database Configuration (từ hosting)
DB_HOST=localhost
DB_NAME=ten_database_hosting
DB_USER=username_hosting
DB_PASS=password_hosting

# Application Configuration
APP_ENV=production
APP_DEBUG=false
BASE_URL=https://domain-cua-ban.com

# Session Configuration
SESSION_TIMEOUT=1800

# Security
LOW_STOCK_THRESHOLD=10
EXPIRY_WARNING_DAYS=30
```

### Bước 1.2: Backup database
Xuất database hiện tại để import lên hosting

### Bước 1.3: Tối ưu files
- Xóa files không cần thiết
- Kiểm tra security
- Tối ưu hình ảnh QR codes

## 🌐 Các hosting phổ biến

### Hosting Việt Nam
- **AZDIGI**: Tốt, hỗ trợ PHP/MySQL
- **INET**: Ổn định, giá rẻ  
- **MATBAO**: Dễ sử dụng
- **PAVIETNAM**: Chất lượng tốt

### Hosting quốc tế
- **Hostinger**: Rẻ, tốt cho starter
- **SiteGround**: Chất lượng cao
- **Bluehost**: Phổ biến

## ⚠️ Lưu ý quan trọng

1. **Database**: Phải import đúng charset (utf8mb4)
2. **QR Codes**: 186 files QR phải upload đầy đủ
3. **Permissions**: Thư mục uploads, logs cần quyền ghi
4. **BASE_URL**: Phải đúng domain hosting
5. **SSL**: Nên dùng HTTPS cho bảo mật

## 🔍 Kiểm tra sau khi upload

1. Truy cập trang chủ
2. Test đăng nhập
3. Test QR codes
4. Kiểm tra database
5. Test các chức năng chính

---

Bạn muốn tôi hỗ trợ bước nào cụ thể?