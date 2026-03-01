# Test Chức Năng Tạo Đơn Hàng (Modal Version)

## Các thay đổi đã thực hiện:

### 1. **views/sales/index.php**
- ✅ Thay nút "Tạo đơn hàng" thành button mở modal
- ✅ Thêm Modal popup với 2 cột:
  - Cột trái: Danh sách thuốc với tìm kiếm và input số lượng
  - Cột phải: Giỏ hàng tạm với tổng tiền
- ✅ JavaScript xử lý thêm/xóa sản phẩm trong modal
- ✅ Nút "Lưu đơn hàng" để tạo đơn với tất cả sản phẩm đã chọn

### 2. **ajax/create_order_with_items.php** (MỚI)
- ✅ Nhận danh sách sản phẩm từ modal
- ✅ Kiểm tra tồn kho cho từng sản phẩm
- ✅ Tạo invoice với tất cả sản phẩm cùng lúc
- ✅ Xử lý transaction để đảm bảo tính toàn vẹn dữ liệu

## Quy trình sử dụng mới:

### Bước 1: Mở modal tạo đơn hàng
1. Vào trang "Bán hàng"
2. Nhấn nút **"Tạo đơn hàng"** (màu xanh lá, góc trên bên phải)
3. Modal popup hiện ra với 2 cột

### Bước 2: Chọn thuốc trong modal
1. **Tìm kiếm thuốc**: Gõ tên thuốc vào ô tìm kiếm
2. **Chọn số lượng**: Nhập số lượng muốn mua
3. **Thêm vào giỏ**: Nhấn nút ➕ màu xanh
4. Sản phẩm xuất hiện ở cột bên phải
5. Lặp lại để thêm nhiều sản phẩm

### Bước 3: Xem giỏ hàng tạm
- Cột bên phải hiển thị:
  - Danh sách sản phẩm đã chọn
  - Giá × Số lượng cho mỗi sản phẩm
  - Nút 🗑️ để xóa sản phẩm
  - Tổng tiền ở dưới cùng

### Bước 4: Lưu đơn hàng
1. Nhấn nút **"Lưu đơn hàng"** (màu xanh, góc dưới bên phải)
2. Hệ thống:
   - Kiểm tra tồn kho cho tất cả sản phẩm
   - Tạo invoice với invoice number
   - Thêm tất cả sản phẩm vào invoice_details
   - Tính tổng tiền
   - Lưu invoice ID vào session
3. Modal đóng và trang reload
4. Đơn hàng hiện ra ở cột bên phải

### Bước 5: Hoàn tất đơn hàng (như cũ)
1. Có thể áp dụng giảm giá
2. Nhấn "Hoàn tất đơn hàng"
3. Trừ inventory và chuyển đến trang hóa đơn

## Ưu điểm của quy trình mới (Modal):

✅ **Trực quan hơn**: Modal popup tập trung, không bị phân tâm
✅ **Nhanh hơn**: Chọn nhiều sản phẩm cùng lúc trong modal
✅ **Rõ ràng hơn**: Thấy rõ giỏ hàng tạm trước khi lưu
✅ **Linh hoạt hơn**: Có thể xóa/sửa sản phẩm trước khi lưu
✅ **An toàn hơn**: Kiểm tra tồn kho cho tất cả sản phẩm trước khi tạo đơn
✅ **UX tốt hơn**: Không cần reload trang nhiều lần

## Test Cases:

### Test 1: Mở modal tạo đơn hàng
- [ ] Vào trang bán hàng
- [ ] Nhấn nút "Tạo đơn hàng"
- [ ] Kiểm tra modal hiện ra
- [ ] Kiểm tra 2 cột hiển thị đúng

### Test 2: Tìm kiếm thuốc trong modal
- [ ] Gõ tên thuốc vào ô tìm kiếm
- [ ] Kiểm tra danh sách lọc đúng
- [ ] Xóa tìm kiếm, kiểm tra hiển thị lại tất cả

### Test 3: Thêm sản phẩm vào giỏ tạm
- [ ] Chọn số lượng
- [ ] Nhấn nút ➕
- [ ] Kiểm tra sản phẩm hiển thị ở cột phải
- [ ] Kiểm tra tổng tiền cập nhật

### Test 4: Thêm nhiều sản phẩm
- [ ] Thêm sản phẩm A
- [ ] Thêm sản phẩm B
- [ ] Thêm lại sản phẩm A (kiểm tra số lượng tăng)
- [ ] Kiểm tra tổng tiền đúng

### Test 5: Xóa sản phẩm khỏi giỏ tạm
- [ ] Thêm vài sản phẩm
- [ ] Nhấn nút 🗑️ để xóa
- [ ] Kiểm tra sản phẩm biến mất
- [ ] Kiểm tra tổng tiền giảm

### Test 6: Lưu đơn hàng
- [ ] Thêm sản phẩm vào giỏ
- [ ] Nhấn "Lưu đơn hàng"
- [ ] Kiểm tra thông báo thành công
- [ ] Kiểm tra trang reload
- [ ] Kiểm tra đơn hàng hiển thị ở cột phải

### Test 7: Kiểm tra tồn kho
- [ ] Nhập số lượng > tồn kho
- [ ] Nhấn thêm vào giỏ
- [ ] Kiểm tra thông báo lỗi

### Test 8: Đóng modal
- [ ] Thêm sản phẩm vào giỏ tạm
- [ ] Nhấn "Hủy" hoặc X
- [ ] Mở lại modal
- [ ] Kiểm tra giỏ đã được reset

### Test 9: Tạo đơn hàng mới khi đã có đơn
- [ ] Tạo đơn hàng qua modal
- [ ] Nhấn "Tạo đơn hàng mới"
- [ ] Xác nhận hủy đơn cũ
- [ ] Kiểm tra modal mở ra
- [ ] Tạo đơn mới

## Phân quyền:

✅ **Staff**: Có đầy đủ quyền tạo đơn hàng, thêm sản phẩm, áp dụng giảm giá, hoàn tất đơn
✅ **Manager**: Có đầy đủ quyền tạo đơn hàng, thêm sản phẩm, áp dụng giảm giá, hoàn tất đơn

**Cả 2 vai trò đều có quyền bán hàng như nhau!**

## Lưu ý khi test:

1. **Database**: Đảm bảo có dữ liệu mẫu (thuốc, batch, user)
2. **Session**: Đảm bảo đã đăng nhập
3. **Permissions**: Test với cả Staff và Manager role - cả 2 đều hoạt động giống nhau
4. **Browser Console**: Kiểm tra không có lỗi JavaScript
5. **Network Tab**: Kiểm tra AJAX requests thành công

## Các file đã thay đổi:

1. `views/sales/index.php` - Thêm modal và JavaScript xử lý
2. `ajax/create_order_with_items.php` - **FILE MỚI** - Tạo đơn hàng với nhiều sản phẩm

## Không thay đổi:

- `controllers/SalesController.php` - Các method cũ vẫn hoạt động
- `ajax/update_discount.php` - Vẫn hoạt động bình thường
- `ajax/add_to_cart.php` - Vẫn dùng cho thêm sản phẩm sau khi đã có đơn
- Database schema - Không cần thay đổi

## Screenshots mô tả:

### 1. Trang bán hàng ban đầu
```
┌─────────────────────────────────────────┐
│ Bán hàng          [Tạo đơn hàng] ← Nút  │
├─────────────────────────────────────────┤
│ Danh sách thuốc  │  Chưa có đơn hàng    │
│                  │  Nhấn "Tạo đơn hàng" │
└─────────────────────────────────────────┘
```

### 2. Modal tạo đơn hàng
```
┌──────────────────────────────────────────────────┐
│ ✓ Tạo đơn hàng mới                          [X]  │
├──────────────────────────────────────────────────┤
│ Chọn thuốc              │  Đơn hàng              │
│ [Tìm kiếm...]           │                        │
│ ┌─────────────────────┐ │  • Paracetamol        │
│ │ Paracetamol  5000đ  │ │    5000đ × 2 = 10000đ│
│ │ Tồn: 100  [2] [➕]  │ │  • Vitamin C          │
│ │ Vitamin C    3000đ  │ │    3000đ × 1 = 3000đ │
│ │ Tồn: 50   [1] [➕]  │ │                        │
│ └─────────────────────┘ │  Tổng: 13,000đ        │
├──────────────────────────────────────────────────┤
│              [Hủy]  [Lưu đơn hàng]              │
└──────────────────────────────────────────────────┘
```

### 3. Sau khi lưu đơn hàng
```
┌─────────────────────────────────────────┐
│ Bán hàng    [Tạo đơn hàng mới]          │
├─────────────────────────────────────────┤
│ Danh sách thuốc  │  Đơn hàng hiện tại   │
│                  │  ID: 123              │
│                  │  • Paracetamol 10000đ │
│                  │  • Vitamin C   3000đ  │
│                  │  Tổng: 13,000đ        │
│                  │  [Hoàn tất đơn hàng]  │
└─────────────────────────────────────────┘
```
