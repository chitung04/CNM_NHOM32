# 🔧 CẬP NHẬT MODELS - FILTER THEO PHARMACY_ID

## ✅ ĐÃ CẬP NHẬT

1. **AuthController.php** - Lưu `pharmacy_id` vào session khi login
2. **User.php** - Filter users theo `pharmacy_id`

## 📋 CẦN CẬP NHẬT TIẾP

Các models sau cần thêm filter `pharmacy_id`:

### 1. Medicine.php
```php
// Trong getAll()
$pharmacyId = $_SESSION['pharmacy_id'] ?? null;
if (!$pharmacyId) return [];

$sql = "SELECT * FROM medicines WHERE pharmacy_id = ? ORDER BY ...";
$stmt = $this->db->query($sql, [$pharmacyId]);
```

### 2. Batch.php
```php
// Trong getAll()
$pharmacyId = $_SESSION['pharmacy_id'] ?? null;
if (!$pharmacyId) return [];

$sql = "SELECT * FROM batches WHERE pharmacy_id = ? ORDER BY ...";
```

### 3. Supplier.php
```php
// Trong getAll()
$pharmacyId = $_SESSION['pharmacy_id'] ?? null;
if (!$pharmacyId) return [];

$sql = "SELECT * FROM suppliers WHERE pharmacy_id = ? ORDER BY ...";
```

### 4. Invoice.php
```php
// Trong getAll()
$pharmacyId = $_SESSION['pharmacy_id'] ?? null;
if (!$pharmacyId) return [];

$sql = "SELECT * FROM invoices WHERE pharmacy_id = ? ORDER BY ...";
```

### 5. Category & Unit
Cũng cần filter theo `pharmacy_id`

## 🎯 NGUYÊN TẮC

**Khi query dữ liệu:**
```php
$pharmacyId = $_SESSION['pharmacy_id'] ?? null;
if (!$pharmacyId) {
    return []; // hoặc throw exception
}

$sql = "SELECT * FROM table WHERE pharmacy_id = ?";
$stmt = $this->db->query($sql, [$pharmacyId]);
```

**Khi tạo dữ liệu mới:**
```php
$pharmacyId = $_SESSION['pharmacy_id'] ?? null;
if (!$pharmacyId) {
    throw new Exception("Không xác định được nhà thuốc");
}

$sql = "INSERT INTO table (pharmacy_id, ...) VALUES (?, ...)";
$this->db->execute($sql, [$pharmacyId, ...]);
```

## ⚡ QUICK FIX

Tôi sẽ tạo script tự động cập nhật tất cả models!
