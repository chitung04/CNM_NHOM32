# 🖼️ Hướng dẫn bật GD Extension trong XAMPP

## Vấn đề
Hệ thống cần GD extension để xử lý hình ảnh QR code, nhưng hiện tại chưa được bật.

## Cách sửa

### Bước 1: Mở file php.ini
1. Mở **XAMPP Control Panel**
2. Nhấn nút **Config** bên cạnh **Apache**
3. Chọn **PHP (php.ini)**

### Bước 2: Tìm và sửa dòng GD
1. Nhấn **Ctrl + F** để tìm kiếm
2. Tìm dòng: `;extension=gd`
3. Bỏ dấu `;` ở đầu thành: `extension=gd`

### Bước 3: Lưu và restart
1. Nhấn **Ctrl + S** để lưu file
2. Đóng file php.ini
3. Trong XAMPP Control Panel, nhấn **Stop** rồi **Start** Apache

### Bước 4: Kiểm tra
Chạy lại file `check_system_errors.php` để kiểm tra GD extension đã hoạt động.

## Lưu ý
- Nếu không tìm thấy dòng `;extension=gd`, hãy tìm `extension=gd2` hoặc thêm dòng `extension=gd` vào cuối phần extensions
- Đảm bảo không có dấu cách thừa trước `extension=gd`

## Kiểm tra thành công
Khi thành công, bạn sẽ thấy:
```
✅ gd extension loaded
```

Thay vì:
```
❌ Missing PHP extension: gd
```