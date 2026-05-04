# 🔒 SỬA LỖI: PHÂN CHIA DỮ LIỆU THEO NHÀ THUỐC

## ❌ VẤN ĐỀ

Khi admin nhà thuốc mới đăng nhập, vẫn thấy dữ liệu (users, thuốc, hóa đơn) của nhà thuốc khác.

## ✅ NGUYÊN NHÂN

Models chưa filter dữ liệu theo `pharmacy_id`.

## 🔧 ĐÃ SỬA

### 1. AuthController.php
- ✅ Lưu `pharmacy_id` vào session khi login
- ✅ Mỗi user đăng nhập sẽ có `$_SESSION['pharmacy_id']`

### 2. User.php  
- ✅ `getAll()` - Chỉ lấy users của nhà thuốc hiện tại
- ✅ `create()` - Tự động gán `pharmacy_id` khi tạo user mới

### 3. helpers/pharmacy.php (MỚI)
- ✅ `getCurrentPharmacyId()` - Lấy pharmacy_id từ session
- ✅ `requirePharmacyId()` - Bắt buộc phải có pharmacy_id
- ✅ `getCurrentPharmacy()` - Lấy thông tin nhà thuốc hiện tại

## 📋 CẦN LÀM TIẾP

Cập nhật các models còn lại để filter theo `pharmacy_id`:

### Medicine.php
```php
public function getAll() {
    $pharmacyId = requirePharmacyId();
    $sql = "SELECT * FROM medicines WHERE pharmacy_id = ? ORDER BY medicine_name";
    $stmt = $this->db->query($sql, [$pharmacyId]);
    return $stmt->fetchAll();
}

public function create($data) {
    $pharmacyId = requirePharmacyId();
    $sql = "INSERT INTO medicines (pharmacy_id, name, ...) VALUES (?, ?, ...)";
    // ...
}
```

### Batch.php
```php
public function getAll() {
    $pharmacyId = requirePharmacyId();
    $sql = "SELECT * FROM batches WHERE pharmacy_id = ? ORDER BY created_at DESC";
    // ...
}
```

### Supplier.php
```php
public function getAll() {
    $pharmacyId = requirePharmacyId();
    $sql = "SELECT * FROM suppliers WHERE pharmacy_id = ? ORDER BY supplier_name";
    // ...
}
```

### Invoice.php
```php
public function getAll() {
    $pharmacyId = requirePharmacyId();
    $sql = "SELECT * FROM invoices WHERE pharmacy_id = ? ORDER BY invoice_date DESC";
    // ...
}
```

## 🎯 QUY TẮC

**1. Khi query (SELECT):**
```php
$pharmacyId = requirePharmacyId(); // Bắt buộc có pharmacy_id
$sql = "SELECT * FROM table WHERE pharmacy_id = ?";
$stmt = $this->db->query($sql, [$pharmacyId]);
```

**2. Khi tạo mới (INSERT):**
```php
$pharmacyId = requirePharmacyId();
$sql = "INSERT INTO table (pharmacy_id, col1, col2) VALUES (?, ?, ?)";
$this->db->execute($sql, [$pharmacyId, $val1, $val2]);
```

**3. Khi cập nhật (UPDATE):**
```php
$pharmacyId = requirePharmacyId();
$sql = "UPDATE table SET col1 = ? WHERE id = ? AND pharmacy_id = ?";
$this->db->execute($sql, [$val1, $id, $pharmacyId]);
```

**4. Khi xóa (DELETE):**
```php
$pharmacyId = requirePharmacyId();
$sql = "DELETE FROM table WHERE id = ? AND pharmacy_id = ?";
$this->db->execute($sql, [$id, $pharmacyId]);
```

## ⚡ TEST

### 1. Đăng ký nhà thuốc mới
```
http://localhost/CNM_NHOM32/index.php?page=auth&action=register
```

### 2. Đăng nhập với admin mới
- Username: (username vừa đăng ký)
- Password: (password vừa đăng ký)

### 3. Kiểm tra
- ✅ Không thấy users của nhà thuốc khác
- ✅ Không thấy thuốc của nhà thuốc khác
- ✅ Không thấy hóa đơn của nhà thuốc khác

### 4. Tạo nhân viên mới
- Vào "Quản lý người dùng" → "Thêm người dùng"
- Nhân viên tự động thuộc nhà thuốc hiện tại

### 5. Đăng xuất và đăng nhập lại với admin cũ
- Kiểm tra vẫn thấy dữ liệu cũ của mình
- Không thấy dữ liệu nhà thuốc mới

## 🚀 TRẠNG THÁI

- ✅ AuthController - Lưu pharmacy_id vào session
- ✅ User model - Filter theo pharmacy_id
- ✅ Helper functions - Hỗ trợ pharmacy operations
- ⏳ Medicine model - CẦN CẬP NHẬT
- ⏳ Batch model - CẦN CẬP NHẬT
- ⏳ Supplier model - CẦN CẬP NHẬT
- ⏳ Invoice model - CẦN CẬP NHẬT
- ⏳ Category model - CẦN CẬP NHẬT
- ⏳ Unit model - CẦN CẬP NHẬT

## 📝 GHI CHÚ

Hiện tại:
- ✅ User đã được phân chia theo nhà thuốc
- ⏳ Các dữ liệu khác (thuốc, lô, hóa đơn) chưa được phân chia

Bạn có thể:
1. Test tạo user mới → Sẽ thấy chỉ users của nhà thuốc mình
2. Các chức năng khác tạm thời vẫn thấy dữ liệu chung (sẽ sửa tiếp)
