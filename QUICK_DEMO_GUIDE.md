# 🎯 HƯỚNG DẪN DEMO NHANH - 15 PHÚT

**Dành cho:** Báo cáo ngày mai  
**Thời gian:** 15 phút  
**Mục tiêu:** Thể hiện tất cả tính năng chính

---

## 🚀 CHUẨN BỊ TRƯỚC KHI DEMO (5 phút trước)

### 1. Khởi động XAMPP
```
✅ Apache: Running
✅ MySQL: Running
```

### 2. Mở trình duyệt
```
URL: http://localhost/CNM_NHOM32
```

### 3. Chuẩn bị điện thoại
```
✅ Mở camera hoặc app quét QR
✅ Kết nối cùng WiFi với laptop (nếu cần)
```

### 4. Mở sẵn các tab
```
Tab 1: Login page
Tab 2: phpMyAdmin (để show database)
Tab 3: VS Code (để show code nếu cần)
```

---

## 📱 DEMO FLOW - 15 PHÚT

### PHẦN 1: GIỚI THIỆU (2 phút)

**Nói:**
> "Chào thầy/cô và các bạn. Em xin giới thiệu đồ án: **DUO PHARMA - Hệ thống quản lý nhà thuốc**"

**Slide/Nói:**
- Mục đích: Quản lý nhà thuốc hiện đại, đa chi nhánh
- Công nghệ: PHP MVC, MySQL, Bootstrap 5, QR Code
- Tính năng nổi bật:
  - ✅ Multi-tenant (nhiều nhà thuốc)
  - ✅ QR Code không cần đăng nhập
  - ✅ Phân quyền 3 cấp
  - ✅ Real-time search & notifications

---

### PHẦN 2: DEMO CHỨC NĂNG (10 phút)

#### 🔐 Demo 1: Đăng ký nhà thuốc mới (1.5 phút)

**Action:**
1. Click "Đăng ký nhà thuốc"
2. Nhập:
   ```
   Tên nhà thuốc: Nhà thuốc Demo
   Địa chỉ: 123 Đường ABC
   Số điện thoại: 0123456789
   Username: demo_admin
   Password: 123456
   Họ tên: Admin Demo
   ```
3. Click "Đăng ký"

**Nói:**
> "Hệ thống hỗ trợ multi-tenant, mỗi nhà thuốc có dữ liệu riêng biệt. Khi đăng ký, tự động tạo tài khoản admin cố định với quyền cao nhất."

**Show:**
- ✅ Đăng ký thành công
- ✅ Tự động đăng nhập
- ✅ Dashboard trống (không có dữ liệu của pharmacy khác)

---

#### 💊 Demo 2: Quản lý thuốc + QR Code (2 phút)

**Action:**
1. Vào "Quản lý thuốc"
2. Click "Thêm thuốc mới"
3. Nhập:
   ```
   Tên thuốc: Paracetamol 500mg
   Danh mục: Thuốc giảm đau
   Đơn vị: Viên
   Giá: 5000
   Mô tả: Thuốc giảm đau, hạ sốt
   ```
4. Click "Lưu"
5. Trong danh sách, click "Xem QR"
6. **Quét QR bằng điện thoại**

**Nói:**
> "Mỗi thuốc tự động được tạo QR code. Khách hàng có thể quét QR để xem thông tin chi tiết mà không cần đăng nhập. Điều này giúp tăng tính minh bạch và tin cậy."

**Show:**
- ✅ QR code hiển thị
- ✅ Quét bằng điện thoại → Mở trang thông tin công khai
- ✅ Trang hiển thị đầy đủ: tên, giá, hạn sử dụng, tồn kho

---

#### 🛒 Demo 3: Bán hàng nhanh (2 phút)

**Action:**
1. Vào "Bán hàng"
2. Tìm thuốc: Gõ "para" → Gợi ý real-time
3. Click "Thêm" → Paracetamol vào giỏ
4. Thay đổi số lượng: 10
5. Áp dụng giảm giá: 5000đ
6. Chọn thanh toán: Tiền mặt
7. Nhập tiền khách đưa: 100000
8. Click "Thanh toán"

**Nói:**
> "Giao diện bán hàng được tối ưu cho tốc độ. Tìm kiếm real-time bằng AJAX, tính toán tự động, hỗ trợ nhiều hình thức thanh toán."

**Show:**
- ✅ Tìm kiếm nhanh
- ✅ Giỏ hàng động
- ✅ Tính toán tự động
- ✅ Hóa đơn có QR code
- ✅ In hóa đơn

---

#### 👥 Demo 4: Phân quyền người dùng (2 phút)

**Action:**
1. Vào "Quản lý người dùng"
2. Click "Thêm người dùng"
3. Tạo Staff:
   ```
   Username: nhanvien_demo
   Password: 123456
   Họ tên: Nhân viên Demo
   Vai trò: Nhân viên
   ```
4. Click "Lưu"
5. Thử nâng lên Manager → Show `is_root_admin = 1`
6. Thử sửa Admin cố định khác → Show nút disabled

**Nói:**
> "Hệ thống có 3 cấp phân quyền: Admin cố định, Admin được phân quyền, và Nhân viên. Admin cố định không thể sửa Admin cố định khác để đảm bảo an toàn."

**Show:**
- ✅ Tạo user thành công
- ✅ Nâng cấp tự động set `is_root_admin = 1`
- ✅ Nút edit bị disabled cho Root Admin khác
- ✅ Badge màu khác nhau: Root Admin (đỏ), Promoted Admin (xanh), Staff (xanh lá)

---

#### 📊 Demo 5: Báo cáo & Thống kê (1.5 phút)

**Action:**
1. Vào "Báo cáo"
2. Chọn khoảng thời gian: Tháng này
3. Xem:
   - Doanh thu
   - Thuốc bán chạy
   - Biểu đồ

**Nói:**
> "Hệ thống cung cấp báo cáo chi tiết theo ngày, tháng, năm. Có thể xuất Excel hoặc PDF để lưu trữ."

**Show:**
- ✅ Biểu đồ doanh thu
- ✅ Top thuốc bán chạy
- ✅ Thống kê tổng quan

---

#### 🔔 Demo 6: Thông báo tự động (1 phút)

**Action:**
1. Click icon chuông ở header
2. Xem danh sách thông báo
3. Click vào 1 thông báo → Đánh dấu đã đọc

**Nói:**
> "Hệ thống tự động kiểm tra và thông báo khi thuốc sắp hết hàng hoặc sắp hết hạn. Sử dụng Cron Job chạy định kỳ."

**Show:**
- ✅ Badge số lượng chưa đọc
- ✅ Danh sách thông báo
- ✅ Đánh dấu đã đọc

---

### PHẦN 3: SHOW DATABASE & CODE (2 phút)

#### Show Database (1 phút)

**Action:**
1. Mở phpMyAdmin
2. Show bảng `pharmacies` → Nhiều pharmacy
3. Show bảng `medicines` → Có cột `pharmacy_id`
4. Show bảng `users` → Có cột `is_root_admin`

**Nói:**
> "Database được thiết kế với kiến trúc multi-tenant. Mỗi bảng đều có pharmacy_id để phân tách dữ liệu."

#### Show Code (1 phút)

**Action:**
1. Mở VS Code
2. Show `models/Medicine.php`:
   ```php
   public function getAll() {
       $pharmacyId = getCurrentPharmacyId();
       $sql = "SELECT * FROM medicines WHERE pharmacy_id = ?";
       // ...
   }
   ```
3. Show `qr.php`:
   ```php
   // Smart redirect based on QR code type
   if (strpos($code, 'BATCH_') === 0) {
       header("Location: medicine_info.php?qr=$code");
   }
   ```

**Nói:**
> "Code được tổ chức theo mô hình MVC. Tất cả models đều filter theo pharmacy_id để đảm bảo data isolation."

---

### PHẦN 4: KẾT LUẬN (1 phút)

**Nói:**
> "Tóm lại, hệ thống DUO PHARMA đã hoàn thành 100% các chức năng:
> - ✅ 11 modules chính
> - ✅ Multi-tenant với data isolation hoàn toàn
> - ✅ QR Code system không cần đăng nhập
> - ✅ Phân quyền 3 cấp an toàn
> - ✅ Real-time search và notifications
> 
> Hệ thống đã sẵn sàng để triển khai thực tế. Em xin cảm ơn thầy/cô và các bạn đã lắng nghe!"

---

## 🎯 TIPS QUAN TRỌNG

### Nếu có câu hỏi:

**Q: "Làm sao đảm bảo dữ liệu giữa các pharmacy không bị lẫn?"**
> A: "Em đã thêm pharmacy_id vào tất cả 15 bảng và cập nhật 8 models để filter theo pharmacy_id. Mỗi query đều có WHERE pharmacy_id = ?"

**Q: "QR code có hoạt động offline không?"**
> A: "QR code cần internet để truy cập trang thông tin. Nhưng có thể cải tiến bằng cách lưu thông tin cơ bản trong QR code dạng text."

**Q: "Hệ thống có bảo mật không?"**
> A: "Có ạ. Em dùng password_hash() cho mật khẩu, prepared statements để chống SQL injection, và session để quản lý đăng nhập."

**Q: "Có thể mở rộng thêm tính năng gì?"**
> A: "Có thể thêm: Quản lý kho, Tích điểm khách hàng, Báo cáo nâng cao, Mobile app, API cho third-party."

---

## 📋 CHECKLIST CUỐI CÙNG

### Trước khi demo:
- [ ] XAMPP đang chạy
- [ ] Database có dữ liệu
- [ ] Browser đã clear cache
- [ ] Điện thoại sẵn sàng quét QR
- [ ] Các tab đã mở sẵn
- [ ] Tài khoản test đã chuẩn bị

### Trong khi demo:
- [ ] Nói rõ ràng, tự tin
- [ ] Show từng bước chậm rãi
- [ ] Giải thích logic khi làm
- [ ] Tương tác với giảng viên
- [ ] Trả lời câu hỏi ngắn gọn

### Sau khi demo:
- [ ] Cảm ơn giảng viên
- [ ] Hỏi feedback
- [ ] Ghi nhận góp ý

---

## 🎊 GOOD LUCK!

**Remember:**
- ✅ Hệ thống hoạt động 100%
- ✅ Bạn đã làm tốt
- ✅ Tự tin trình bày
- ✅ Mọi thứ sẽ ổn!

**You got this! 🚀**

---

**Created:** 04/05/2026  
**For:** Presentation on 05/05/2026  
**Status:** ✅ READY TO PRESENT
