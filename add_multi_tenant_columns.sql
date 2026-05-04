-- ============================================
-- THÊM CÁC CỘT CẦN THIẾT CHO MULTI-TENANT
-- ============================================

-- 1. Tạo bảng pharmacies
CREATE TABLE IF NOT EXISTS pharmacies (
    pharmacy_id INT PRIMARY KEY AUTO_INCREMENT,
    pharmacy_name VARCHAR(255) NOT NULL,
    pharmacy_code VARCHAR(50) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    license_number VARCHAR(100),
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    subscription_plan ENUM('free', 'basic', 'premium') DEFAULT 'free',
    subscription_expires DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pharmacy_code (pharmacy_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Thêm cột email vào users
-- Bỏ qua lỗi nếu cột đã tồn tại
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email') = 0,
    'ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER phone',
    'SELECT "Column email already exists" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Thêm cột pharmacy_id vào users
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'pharmacy_id') = 0,
    'ALTER TABLE users ADD COLUMN pharmacy_id INT AFTER user_id',
    'SELECT "Column pharmacy_id already exists in users" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Thêm cột pharmacy_id vào medicines
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'medicines' AND COLUMN_NAME = 'pharmacy_id') = 0,
    'ALTER TABLE medicines ADD COLUMN pharmacy_id INT AFTER medicine_id',
    'SELECT "Column pharmacy_id already exists in medicines" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Thêm cột pharmacy_id vào batches
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'batches' AND COLUMN_NAME = 'pharmacy_id') = 0,
    'ALTER TABLE batches ADD COLUMN pharmacy_id INT AFTER batch_id',
    'SELECT "Column pharmacy_id already exists in batches" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Thêm cột pharmacy_id vào suppliers
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'suppliers' AND COLUMN_NAME = 'pharmacy_id') = 0,
    'ALTER TABLE suppliers ADD COLUMN pharmacy_id INT AFTER supplier_id',
    'SELECT "Column pharmacy_id already exists in suppliers" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. Thêm cột pharmacy_id vào invoices
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'pharmacy_id') = 0,
    'ALTER TABLE invoices ADD COLUMN pharmacy_id INT AFTER invoice_id',
    'SELECT "Column pharmacy_id already exists in invoices" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. Thêm cột pharmacy_id vào notifications
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'pharmacy_id') = 0,
    'ALTER TABLE notifications ADD COLUMN pharmacy_id INT AFTER notification_id',
    'SELECT "Column pharmacy_id already exists in notifications" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 9. Thêm cột pharmacy_id vào categories
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'pharmacy_id') = 0,
    'ALTER TABLE categories ADD COLUMN pharmacy_id INT AFTER category_id',
    'SELECT "Column pharmacy_id already exists in categories" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 10. Thêm cột pharmacy_id vào units
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'qlnt_db' AND TABLE_NAME = 'units' AND COLUMN_NAME = 'pharmacy_id') = 0,
    'ALTER TABLE units ADD COLUMN pharmacy_id INT AFTER unit_id',
    'SELECT "Column pharmacy_id already exists in units" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- TẠO NHÀ THUỐC MẶC ĐỊNH
-- ============================================

-- Tạo nhà thuốc mẫu (nếu chưa có)
INSERT INTO pharmacies (pharmacy_name, pharmacy_code, address, phone, email, status, subscription_plan)
SELECT 'Nhà thuốc DUO PHARMA', 'DUO001', '123 Đường ABC, Quận 1, TP.HCM', '0123456789', 'duopharma@example.com', 'active', 'premium'
WHERE NOT EXISTS (SELECT 1 FROM pharmacies WHERE pharmacy_code = 'DUO001');

-- Lấy pharmacy_id của nhà thuốc mặc định
SET @pharmacy_id = (SELECT pharmacy_id FROM pharmacies WHERE pharmacy_code = 'DUO001' LIMIT 1);

-- Cập nhật dữ liệu hiện tại với pharmacy_id (chỉ những record chưa có pharmacy_id)
UPDATE users SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;
UPDATE medicines SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;
UPDATE batches SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;
UPDATE suppliers SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;
UPDATE invoices SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;
UPDATE notifications SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;
UPDATE categories SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;
UPDATE units SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;

-- ============================================
-- THÊM INDEXES
-- ============================================

-- Thêm indexes cho pharmacy_id
CREATE INDEX idx_users_pharmacy ON users(pharmacy_id);
CREATE INDEX idx_medicines_pharmacy ON medicines(pharmacy_id);
CREATE INDEX idx_batches_pharmacy ON batches(pharmacy_id);
CREATE INDEX idx_suppliers_pharmacy ON suppliers(pharmacy_id);
CREATE INDEX idx_invoices_pharmacy ON invoices(pharmacy_id);
CREATE INDEX idx_notifications_pharmacy ON notifications(pharmacy_id);
CREATE INDEX idx_categories_pharmacy ON categories(pharmacy_id);
CREATE INDEX idx_units_pharmacy ON units(pharmacy_id);

-- ============================================
-- HOÀN THÀNH
-- ============================================
SELECT 'Multi-tenant setup completed successfully!' as message;
