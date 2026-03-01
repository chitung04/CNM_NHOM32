# 📱 Hướng dẫn QR Code - Hệ thống quản lý bán thuốc

## 🎯 Tổng quan

File `database_schema.sql` đã được cập nhật với:
- ✅ Schema đầy đủ
- ✅ 29 medicines với QR codes
- ✅ 32 batches với QR codes  
- ✅ 5 invoices mẫu với QR codes
- ✅ Dữ liệu đầy đủ: users, categories, units, suppliers

## 🚀 Cài đặt nhanh (3 bước)

### Bước 1: Import database

```bash
# Mở phpMyAdmin hoặc MySQL command line
# Import file: database_schema.sql
```

Hoặc dùng command line:
```bash
mysql -u root -p < database_schema.sql
```

### Bước 2: Generate QR code images

```bash
php tools/generate_all_qrcodes.php
```

Script này sẽ tạo 66 QR code images:
- 29 QR codes cho medicines
- 32 QR codes cho batches
- 5 QR codes cho invoices

### Bước 3: Xem kết quả

Mở trình duyệt:
```
http://localhost/CNM_NHOM32/tools/view_qrcodes.php
```

## 📋 Danh sách QR Codes có sẵn

### Medicines (29 thuốc)

| Mã QR | Tên thuốc | Giá |
|-------|-----------|-----|
| `MED_1735000001_1001` | Amoxicillin 500mg | 3,500 VNĐ |
| `MED_1735000002_1002` | Cefixime 200mg | 8,500 VNĐ |
| `MED_1735000003_1003` | Azithromycin 250mg | 12,000 VNĐ |
| `MED_1735000004_1004` | Ciprofloxacin 500mg | 6,500 VNĐ |
| `MED_1735000005_1005` | Paracetamol 500mg | 2,000 VNĐ |
| `MED_1735000006_1006` | Ibuprofen 400mg | 4,500 VNĐ |
| `MED_1735000007_1007` | Aspirin 100mg | 3,000 VNĐ |
| `MED_1735000008_1008` | Diclofenac 50mg | 5,500 VNĐ |
| `MED_1735000009_1009` | Vitamin C 1000mg | 5,000 VNĐ |
| `MED_1735000010_1010` | Vitamin B Complex | 8,000 VNĐ |
| ... và 19 thuốc khác |

### Batches (32 lô thuốc)

| Mã QR | Batch ID | Số lượng |
|-------|----------|----------|
| `BATCH_1735000101_2001` | 1 | 500 |
| `BATCH_1735000102_2002` | 2 | 300 |
| `BATCH_1735000103_2003` | 3 | 200 |
| ... và 29 batches khác |

### Invoices (5 hóa đơn)

| Mã QR | Số hóa đơn | Tổng tiền |
|-------|------------|-----------|
| `INV_1735000201_3001` | INV20240115001 | 45,000 VNĐ |
| `INV_1735000202_3002` | INV20240115002 | 110,000 VNĐ |
| `INV_1735000203_3003` | INV20240116001 | 85,000 VNĐ |
| `INV_1735000204_3004` | INV20240116002 | 180,000 VNĐ |
| `INV_1735000205_3005` | INV20240117001 | 60,000 VNĐ |

## 🧪 Test QR Code Scanning

### Test với giao diện web

1. Mở: `http://localhost/CNM_NHOM32/tools/test_scan_qr.html`
2. Nhập một trong các mã QR trên (ví dụ: `MED_1735000005_1005`)
3. Click "Scan QR Code"
4. Xem kết quả hiển thị thông tin thuốc

### Test với PHP script

```bash
php tools/test_qrcode.php
```

## 📁 Cấu trúc files

```
CNM_NHOM32/
├── database_schema.sql          # ⭐ FILE CHÍNH - Schema + Data + QR codes
├── assets/qrcodes/              # Thư mục chứa QR code images
│   ├── MED_*.png               # QR codes cho medicines
│   ├── BATCH_*.png             # QR codes cho batches
│   └── INV_*.png               # QR codes cho invoices
├── tools/
│   ├── generate_all_qrcodes.php # Script tạo tất cả QR codes
│   ├── view_qrcodes.php        # Xem tất cả QR codes
│   ├── test_scan_qr.html       # Test scan QR code
│   └── test_qrcode.php         # Test tạo QR code
├── helpers/
│   ├── qrcode.php              # Helper functions
│   └── phpqrcode.php           # QR code library
└── ajax/
    ├── get_medicine_by_qr.php  # API scan QR (cần login)
    └── get_medicine_by_qr_public.php # API scan QR (public - test only)
```

## 🔧 Troubleshooting

### QR codes không được tạo

**Nguyên nhân**: Không có kết nối internet (cần để gọi API)

**Giải pháp**:
1. Kiểm tra kết nối internet
2. Chạy lại: `php tools/generate_all_qrcodes.php`

### Scan QR không trả về kết quả

**Nguyên nhân**: Chưa import database hoặc chưa đăng nhập

**Giải pháp**:
1. Import `database_schema.sql` vào MySQL
2. Đăng nhập với: `admin` / `admin123`
3. Hoặc dùng endpoint public: `tools/test_scan_qr.html`

### Database name không đúng

**Hiện tại**: Database name là `qlnt_db` (theo file .env)

Nếu muốn đổi:
1. Sửa trong `database_schema.sql`: `CREATE DATABASE qlnt_db`
2. Sửa trong `.env`: `DB_NAME=qlnt_db`

## 📊 Thống kê dữ liệu

Sau khi import `database_schema.sql`:

- 👥 Users: 4 (2 managers, 2 staff)
- 📦 Categories: 8
- 📏 Units: 8
- 🏢 Suppliers: 5
- 💊 Medicines: 29 (tất cả có QR code)
- 📦 Batches: 32 (tất cả có QR code)
- 🧾 Invoices: 5 (tất cả có QR code)
- 📝 Invoice Details: 13
- 🔔 Notifications: 3

## 🎓 Tài khoản đăng nhập

| Username | Password | Role | Tên |
|----------|----------|------|-----|
| admin | admin123 | Manager | Quản trị viên |
| staff | staff123 | Staff | Nhân viên bán hàng |
| manager1 | admin123 | Manager | Nguyễn Văn A |
| staff1 | staff123 | Staff | Trần Thị B |

## 🌐 Links hữu ích

- Xem QR codes: http://localhost/CNM_NHOM32/tools/view_qrcodes.php
- Test scan QR: http://localhost/CNM_NHOM32/tools/test_scan_qr.html
- Hệ thống chính: http://localhost/CNM_NHOM32/

## ✅ Checklist hoàn thành

- [x] Database schema với tất cả tables
- [x] Dữ liệu mẫu đầy đủ (29 medicines, 32 batches, 5 invoices)
- [x] QR codes cho tất cả medicines, batches, invoices
- [x] Script generate QR code images
- [x] Tools để xem và test QR codes
- [x] API endpoints để scan QR codes
- [x] Hướng dẫn đầy đủ

## 🎉 Kết luận

Hệ thống QR Code đã hoàn chỉnh và sẵn sàng sử dụng!

**Để bắt đầu:**
1. Import `database_schema.sql`
2. Chạy `php tools/generate_all_qrcodes.php`
3. Mở `http://localhost/CNM_NHOM32/tools/view_qrcodes.php`

Tất cả đã được gom vào 1 file SQL duy nhất: `database_schema.sql` ✨
