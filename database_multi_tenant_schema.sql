-- ============================================
-- SCHEMA CHO HỆ THỐNG ĐA NHÀ THUỐC (MULTI-TENANT)
-- ============================================

-- 1. Bảng pharmacies - Quản lý các nhà thuốc
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

-- 2. Cập nhật bảng users - Thêm pharmacy_id
ALTER TABLE users ADD COLUMN pharmacy_id INT AFTER user_id;
ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER phone;
ALTER TABLE users ADD CONSTRAINT fk_users_pharmacy FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE;
ALTER TABLE users ADD INDEX idx_pharmacy_id (pharmacy_id);

-- 3. Cập nhật bảng medicines - Thêm pharmacy_id
ALTER TABLE medicines ADD COLUMN pharmacy_id INT AFTER medicine_id;
ALTER TABLE medicines ADD CONSTRAINT fk_medicines_pharmacy FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE;
ALTER TABLE medicines ADD INDEX idx_pharmacy_id (pharmacy_id);

-- 4. Cập nhật bảng batches - Thêm pharmacy_id
ALTER TABLE batches ADD COLUMN pharmacy_id INT AFTER batch_id;
ALTER TABLE batches ADD CONSTRAINT fk_batches_pharmacy FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE;
ALTER TABLE batches ADD INDEX idx_pharmacy_id (pharmacy_id);

-- 5. Cập nhật bảng suppliers - Thêm pharmacy_id
ALTER TABLE suppliers ADD COLUMN pharmacy_id INT AFTER supplier_id;
ALTER TABLE suppliers ADD CONSTRAINT fk_suppliers_pharmacy FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE;
ALTER TABLE suppliers ADD INDEX idx_pharmacy_id (pharmacy_id);

-- 6. Cập nhật bảng invoices - Thêm pharmacy_id
ALTER TABLE invoices ADD COLUMN pharmacy_id INT AFTER invoice_id;
ALTER TABLE invoices ADD CONSTRAINT fk_invoices_pharmacy FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE;
ALTER TABLE invoices ADD INDEX idx_pharmacy_id (pharmacy_id);

-- 7. Cập nhật bảng notifications - Thêm pharmacy_id
ALTER TABLE notifications ADD COLUMN pharmacy_id INT AFTER notification_id;
ALTER TABLE notifications ADD CONSTRAINT fk_notifications_pharmacy FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE;
ALTER TABLE notifications ADD INDEX idx_pharmacy_id (pharmacy_id);

-- 8. Bảng pharmacy_settings - Cài đặt cho từng nhà thuốc
CREATE TABLE IF NOT EXISTS pharmacy_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    pharmacy_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacies(pharmacy_id) ON DELETE CASCADE,
    UNIQUE KEY unique_pharmacy_setting (pharmacy_id, setting_key),
    INDEX idx_pharmacy_id (pharmacy_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng audit_logs - Thêm pharmacy_id
ALTER TABLE audit_logs ADD COLUMN pharmacy_id INT AFTER log_id;
ALTER TABLE audit_logs ADD INDEX idx_pharmacy_id (pharmacy_id);

-- ============================================
-- DỮ LIỆU MẪU
-- ============================================

-- Tạo nhà thuốc mẫu
INSERT INTO pharmacies (pharmacy_name, pharmacy_code, address, phone, email, status, subscription_plan) VALUES
('Nhà thuốc DUO PHARMA', 'DUO001', '123 Đường ABC, Quận 1, TP.HCM', '0123456789', 'duopharma@example.com', 'active', 'premium');

-- Lấy pharmacy_id vừa tạo
SET @pharmacy_id = LAST_INSERT_ID();

-- Cập nhật users hiện tại với pharmacy_id
UPDATE users SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;

-- Cập nhật medicines hiện tại với pharmacy_id
UPDATE medicines SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;

-- Cập nhật batches hiện tại với pharmacy_id
UPDATE batches SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;

-- Cập nhật suppliers hiện tại với pharmacy_id
UPDATE suppliers SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;

-- Cập nhật invoices hiện tại với pharmacy_id
UPDATE invoices SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;

-- Cập nhật notifications hiện tại với pharmacy_id
UPDATE notifications SET pharmacy_id = @pharmacy_id WHERE pharmacy_id IS NULL;

-- ============================================
-- VIEWS - Tạo views để dễ query
-- ============================================

-- View: Thống kê theo nhà thuốc
CREATE OR REPLACE VIEW pharmacy_statistics AS
SELECT 
    p.pharmacy_id,
    p.pharmacy_name,
    p.pharmacy_code,
    COUNT(DISTINCT u.user_id) as total_users,
    COUNT(DISTINCT m.medicine_id) as total_medicines,
    COUNT(DISTINCT b.batch_id) as total_batches,
    COUNT(DISTINCT i.invoice_id) as total_invoices,
    COALESCE(SUM(i.final_amount), 0) as total_revenue,
    p.status,
    p.subscription_plan
FROM pharmacies p
LEFT JOIN users u ON p.pharmacy_id = u.pharmacy_id AND u.is_active = 1
LEFT JOIN medicines m ON p.pharmacy_id = m.pharmacy_id
LEFT JOIN batches b ON p.pharmacy_id = b.pharmacy_id
LEFT JOIN invoices i ON p.pharmacy_id = i.pharmacy_id
GROUP BY p.pharmacy_id;

-- ============================================
-- STORED PROCEDURES
-- ============================================

-- Procedure: Tạo nhà thuốc mới với admin
DELIMITER //

CREATE PROCEDURE create_pharmacy_with_admin(
    IN p_pharmacy_name VARCHAR(255),
    IN p_pharmacy_code VARCHAR(50),
    IN p_address TEXT,
    IN p_phone VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_admin_username VARCHAR(50),
    IN p_admin_password VARCHAR(255),
    IN p_admin_fullname VARCHAR(100),
    IN p_admin_phone VARCHAR(20),
    IN p_admin_email VARCHAR(100),
    OUT p_pharmacy_id INT,
    OUT p_user_id INT,
    OUT p_result VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_result = 'ERROR: Có lỗi xảy ra khi tạo nhà thuốc';
        SET p_pharmacy_id = NULL;
        SET p_user_id = NULL;
    END;
    
    START TRANSACTION;
    
    -- Kiểm tra pharmacy_code đã tồn tại
    IF EXISTS (SELECT 1 FROM pharmacies WHERE pharmacy_code = p_pharmacy_code) THEN
        SET p_result = 'ERROR: Mã nhà thuốc đã tồn tại';
        SET p_pharmacy_id = NULL;
        SET p_user_id = NULL;
        ROLLBACK;
    ELSE
        -- Kiểm tra username đã tồn tại
        IF EXISTS (SELECT 1 FROM users WHERE username = p_admin_username) THEN
            SET p_result = 'ERROR: Tên đăng nhập đã tồn tại';
            SET p_pharmacy_id = NULL;
            SET p_user_id = NULL;
            ROLLBACK;
        ELSE
            -- Tạo pharmacy
            INSERT INTO pharmacies (pharmacy_name, pharmacy_code, address, phone, email, status, subscription_plan)
            VALUES (p_pharmacy_name, p_pharmacy_code, p_address, p_phone, p_email, 'active', 'free');
            
            SET p_pharmacy_id = LAST_INSERT_ID();
            
            -- Tạo admin user
            INSERT INTO users (pharmacy_id, username, password, full_name, phone, email, role, is_active)
            VALUES (p_pharmacy_id, p_admin_username, p_admin_password, p_admin_fullname, p_admin_phone, p_admin_email, 'manager', 1);
            
            SET p_user_id = LAST_INSERT_ID();
            
            -- Tạo categories mặc định cho nhà thuốc
            INSERT INTO categories (pharmacy_id, category_name)
            SELECT p_pharmacy_id, category_name
            FROM (
                SELECT 'Thuốc kháng sinh' as category_name UNION ALL
                SELECT 'Thuốc giảm đau' UNION ALL
                SELECT 'Thuốc hạ sốt' UNION ALL
                SELECT 'Vitamin & Khoáng chất' UNION ALL
                SELECT 'Thuốc tiêu hóa' UNION ALL
                SELECT 'Thuốc tim mạch' UNION ALL
                SELECT 'Thuốc da liễu' UNION ALL
                SELECT 'Thực phẩm chức năng'
            ) as default_categories;
            
            -- Tạo units mặc định cho nhà thuốc
            INSERT INTO units (pharmacy_id, unit_name)
            SELECT p_pharmacy_id, unit_name
            FROM (
                SELECT 'Viên' as unit_name UNION ALL
                SELECT 'Vỉ' UNION ALL
                SELECT 'Hộp' UNION ALL
                SELECT 'Chai' UNION ALL
                SELECT 'Tuýp' UNION ALL
                SELECT 'Gói' UNION ALL
                SELECT 'Ống' UNION ALL
                SELECT 'Lọ'
            ) as default_units;
            
            SET p_result = 'SUCCESS';
            COMMIT;
        END IF;
    END IF;
END //

DELIMITER ;

-- ============================================
-- TRIGGERS - Đảm bảo data isolation
-- ============================================

-- Trigger: Kiểm tra pharmacy_id khi insert user
DELIMITER //

CREATE TRIGGER before_user_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.pharmacy_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'pharmacy_id không được để trống';
    END IF;
END //

DELIMITER ;

-- ============================================
-- INDEXES - Tối ưu performance
-- ============================================

-- Thêm composite indexes cho các truy vấn thường dùng
CREATE INDEX idx_users_pharmacy_role ON users(pharmacy_id, role, is_active);
CREATE INDEX idx_medicines_pharmacy_category ON medicines(pharmacy_id, category_id);
CREATE INDEX idx_batches_pharmacy_medicine ON batches(pharmacy_id, medicine_id);
CREATE INDEX idx_invoices_pharmacy_date ON invoices(pharmacy_id, invoice_date);

-- ============================================
-- NOTES
-- ============================================
-- 1. Mỗi nhà thuốc có pharmacy_id riêng
-- 2. Tất cả dữ liệu (users, medicines, batches, etc.) đều gắn với pharmacy_id
-- 3. Admin chỉ thấy và quản lý dữ liệu của nhà thuốc mình
-- 4. Khi đăng ký mới, tự động tạo categories và units mặc định
-- 5. Có thể mở rộng thêm subscription plans (free, basic, premium)
