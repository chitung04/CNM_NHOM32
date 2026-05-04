# ⚡ KIỂM TRA NGAY - 3 BƯỚC

## 🎯 Mục tiêu
Kiểm tra xem thuốc có hiển thị trên trang bán hàng không.

---

## 📋 BƯỚC 1: Chạy script kiểm tra

Mở trình duyệt và truy cập:
```
http://localhost/CNM_NHOM32/check_sales_medicines.php
```

### Kết quả mong đợi:
```
✅ Pharmacy ID: 1
Tổng số thuốc: 70
Tổng số lô: 120
Lô active có hàng: 110
Số thuốc trả về: 70
Thuốc có tồn kho: 70
```

### Nếu kết quả khác:

#### ❌ Trường hợp 1: Tổng số thuốc = 0
**Nguyên nhân:** Chưa import database

**Giải pháp:**
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Chọn database `qlnt_db`
3. Click tab "Import"
4. Chọn file `qlnt_db.sql`
5. Click "Go"
6. Đăng nhập lại với `admin` / `123456`

#### ⚠️ Trường hợp 2: Có thuốc nhưng tồn kho = 0
**Nguyên nhân:** Thuốc không có lô hàng hoặc lô đã hết

**Giải pháp:**
```
http://localhost/CNM_NHOM32/fix_medicines_no_stock.php
```
Script này sẽ tự động thêm lô hàng cho các thuốc chưa có.

#### 🔄 Trường hợp 3: Pharmacy ID khác 1
**Nguyên nhân:** Đang đăng nhập với pharmacy khác

**Giải pháp:**
1. Đăng xuất
2. Đăng nhập lại với `admin` / `123456`

---

## 📋 BƯỚC 2: Vào trang bán hàng

Truy cập:
```
http://localhost/CNM_NHOM32/index.php?page=sales
```

### Kiểm tra:
- ✅ Có danh sách thuốc hiển thị
- ✅ Mỗi thuốc có giá, tồn kho, QR code
- ✅ Có thể tìm kiếm thuốc
- ✅ Có thể thêm vào giỏ hàng

---

## 📋 BƯỚC 3: Kiểm tra thông báo

Click vào icon chuông 🔔 ở góc phải trên cùng.

### Kết quả mong đợi:
- ⚠️ Có thông báo thuốc sắp hết hạn (10 thuốc)
- ⚠️ Có thông báo thuốc sắp hết hàng (20 thuốc)
- ❌ Có thông báo thuốc đã hết hạn (10 thuốc)

---

## 🎯 TÓM TẮT

| Bước | URL | Kết quả mong đợi |
|------|-----|------------------|
| 1 | `check_sales_medicines.php` | 70 thuốc, 120 lô |
| 2 | `index.php?page=sales` | Danh sách thuốc hiển thị |
| 3 | Click 🔔 | Có thông báo |

---

## 🆘 NẾU VẪN LỖI

1. Chụp màn hình kết quả từ `check_sales_medicines.php`
2. Báo lại kèm ảnh
3. Tôi sẽ hỗ trợ cụ thể

---

**Thời gian:** < 2 phút  
**Độ khó:** ⭐ Dễ  
**Ngày:** 05/05/2026
