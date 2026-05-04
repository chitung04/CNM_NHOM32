# 🚀 Hướng dẫn Test nhanh - Tự động tạo dữ liệu

## ⚡ Test trong 5 phút

### Bước 1: Kiểm tra trạng thái hiện tại
```
http://localhost/CNM_NHOM32/test_new_pharmacy_registration.php
```
→ Xem có bao nhiêu pharmacy đang tồn tại

---

### Bước 2: Đăng ký pharmacy mới
```
http://localhost/CNM_NHOM32/index.php?page=auth&action=register
```

**Điền thông tin:**
- Tên nhà thuốc: `Nhà thuốc Demo`
- Địa chỉ: `123 Đường Test, Hà Nội`
- Tên đăng nhập: `demo`
- Mật khẩu: `123456`
- Xác nhận mật khẩu: `123456`
- Họ tên: `Admin Demo`
- Email: `demo@test.com`
- Số điện thoại: `0901234567`
- ✅ Đồng ý điều khoản

→ Nhấn **Đăng ký**

---

### Bước 3: Kiểm tra dữ liệu đã tạo
Quay lại:
```
http://localhost/CNM_NHOM32/test_new_pharmacy_registration.php
```

**Kết quả mong đợi:**
- ✅ Pharmacy mới xuất hiện trong bảng
- ✅ Có **10 thuốc**
- ✅ Có **20 lô**
- ✅ Có **3 nhà cung cấp**

---

### Bước 4: Đăng nhập và test
```
http://localhost/CNM_NHOM32/index.php?page=auth&action=login
```

**Đăng nhập:**
- Username: `demo`
- Password: `123456`

---

### Bước 5: Kiểm tra trang bán hàng
```
http://localhost/CNM_NHOM32/index.php?page=sales
```

**Kết quả mong đợi:**
- ✅ Hiển thị **10 thuốc** trong danh sách
- ✅ Có thể thêm vào giỏ hàng
- ✅ Có thể tạo hóa đơn
- ✅ Có thể tìm kiếm thuốc
- ✅ Có thể lọc theo danh mục

---

## 🎯 Checklist đầy đủ

### Dữ liệu tự động:
- [ ] 8 danh mục thuốc
- [ ] 8 đơn vị tính
- [ ] 3 nhà cung cấp
- [ ] 10 loại thuốc
- [ ] 20 lô thuốc (2 lô/thuốc)
- [ ] Mỗi thuốc có QR code unique
- [ ] Mỗi lô có QR code unique

### Chức năng:
- [ ] Đăng nhập thành công
- [ ] Trang bán hàng hiển thị thuốc
- [ ] Thêm vào giỏ hàng hoạt động
- [ ] Tạo hóa đơn thành công
- [ ] Tìm kiếm thuốc hoạt động
- [ ] Lọc theo danh mục hoạt động

### Multi-tenant:
- [ ] Pharmacy A không thấy dữ liệu của Pharmacy B
- [ ] Mỗi pharmacy có dữ liệu riêng
- [ ] Admin chỉ quản lý pharmacy của mình

---

## 🐛 Troubleshooting

### Lỗi: "Tên đăng nhập đã được sử dụng"
→ Đổi username khác (ví dụ: `demo2`, `demo3`)

### Lỗi: "Email đã được sử dụng"
→ Đổi email khác (ví dụ: `demo2@test.com`)

### Không thấy thuốc trên trang bán hàng
→ Kiểm tra:
1. Đã đăng nhập đúng tài khoản chưa?
2. Chạy `test_new_pharmacy_registration.php` xem có dữ liệu không?
3. Kiểm tra `pharmacy_id` trong session

### QR code bị trùng
→ Không thể xảy ra vì có `usleep(10000)` và `rand(1000, 9999)`

---

## 📊 So sánh trước và sau

### ❌ Trước đây:
1. Đăng ký pharmacy
2. Đăng nhập
3. Trang bán hàng **TRỐNG**
4. Phải import SQL thủ công
5. Phải chạy script `setup_data_for_pharmacy.php`
6. Mất 10-15 phút setup

### ✅ Bây giờ:
1. Đăng ký pharmacy
2. Đăng nhập
3. Trang bán hàng **ĐẦY ĐỦ 10 THUỐC**
4. Không cần làm gì thêm
5. Sẵn sàng demo ngay
6. Chỉ mất 2 phút!

---

## 🎓 Demo cho thầy/cô

### Kịch bản demo:
1. **Mở trang đăng ký** → Giải thích multi-tenant
2. **Đăng ký pharmacy mới** → Điền thông tin trực tiếp
3. **Đăng nhập** → Vào hệ thống
4. **Vào trang bán hàng** → Thấy ngay 10 thuốc
5. **Thêm vào giỏ** → Chọn thuốc, nhập số lượng
6. **Tạo hóa đơn** → Hoàn tất đơn hàng
7. **Giải thích:** "Tất cả dữ liệu này được tạo tự động khi đăng ký!"

### Điểm nhấn:
- ✅ Multi-tenant: Mỗi nhà thuốc có dữ liệu riêng
- ✅ Tự động: Không cần import thủ công
- ✅ Đầy đủ: Categories, Units, Suppliers, Medicines, Batches
- ✅ QR code: Mỗi thuốc và lô có QR riêng
- ✅ Sẵn sàng: Đăng ký xong là dùng được ngay

---

**Chúc may mắn với presentation ngày mai! 🎉**
