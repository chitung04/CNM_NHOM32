# Hướng dẫn cài đặt Cron Job

## Mục đích
Script `check_expiry.php` tự động kiểm tra:
- Thuốc sắp hết hạn (< 30 ngày)
- Thuốc tồn kho thấp (< 10 đơn vị)
- Cập nhật status lô thuốc đã hết hạn
- Xóa thông báo cũ đã đọc

## Cài đặt trên Linux/Unix

### 1. Mở crontab editor
```bash
crontab -e
```

### 2. Thêm dòng sau (chạy mỗi ngày lúc 00:00)
```bash
0 0 * * * /usr/bin/php /path/to/pharmacy-management/cron/check_expiry.php >> /path/to/pharmacy-management/logs/cron.log 2>&1
```

### 3. Lưu và thoát
Cron job sẽ tự động chạy mỗi ngày

## Cài đặt trên Windows (Task Scheduler)

### 1. Mở Task Scheduler
- Nhấn `Win + R`
- Gõ `taskschd.msc` và Enter

### 2. Tạo Task mới
- Click "Create Basic Task"
- Name: "Pharmacy Expiry Check"
- Description: "Check expiring medicines daily"

### 3. Trigger
- Daily
- Start time: 00:00
- Recur every: 1 days

### 4. Action
- Start a program
- Program/script: `C:\xampp\php\php.exe`
- Arguments: `C:\xampp\htdocs\pharmacy-management\cron\check_expiry.php`
- Start in: `C:\xampp\htdocs\pharmacy-management`

### 5. Finish
Task sẽ chạy tự động mỗi ngày

## Test thủ công

### Linux/Unix
```bash
cd /path/to/pharmacy-management
php cron/check_expiry.php
```

### Windows
```cmd
cd C:\xampp\htdocs\pharmacy-management
C:\xampp\php\php.exe cron\check_expiry.php
```

## Kiểm tra log
Xem file `logs/cron.log` để kiểm tra kết quả chạy

## Lưu ý
- Đảm bảo PHP CLI đã được cài đặt
- Kiểm tra quyền thực thi file
- Đường dẫn phải chính xác
- Database phải accessible từ cron job
