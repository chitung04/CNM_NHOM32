-- ============================================
-- RÀNG BUỘC VÀ TRIGGER CHO ROOT ADMIN
-- ============================================

-- 1. Tạo unique index để đảm bảo chỉ có 1 root admin per pharmacy
-- (Sử dụng partial index - chỉ áp dụng cho is_root_admin = 1)
DROP INDEX IF EXISTS idx_unique_root_admin_per_pharmacy ON users;

-- MySQL không hỗ trợ partial index trực tiếp, nên ta dùng trigger

-- ============================================
-- TRIGGER: Kiểm tra trước khi INSERT
-- ============================================
DROP TRIGGER IF EXISTS before_user_insert_check_root_admin;

DELIMITER $$

CREATE TRIGGER before_user_insert_check_root_admin
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    DECLARE root_admin_count INT;
    
    -- Nếu user mới là root admin
    IF NEW.is_root_admin = 1 THEN
        -- Kiểm tra xem pharmacy đã có root admin chưa
        SELECT COUNT(*) INTO root_admin_count
        FROM users
        WHERE pharmacy_id = NEW.pharmacy_id 
        AND is_root_admin = 1;
        
        -- Nếu đã có root admin, báo lỗi
        IF root_admin_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nhà thuốc này đã có Admin gốc. Mỗi nhà thuốc chỉ được có 1 Admin gốc.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- TRIGGER: Kiểm tra trước khi UPDATE
-- ============================================
DROP TRIGGER IF EXISTS before_user_update_check_root_admin;

DELIMITER $$

CREATE TRIGGER before_user_update_check_root_admin
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    DECLARE root_admin_count INT;
    
    -- Nếu đang cố gắng set user thành root admin
    IF NEW.is_root_admin = 1 AND OLD.is_root_admin = 0 THEN
        -- Kiểm tra xem pharmacy đã có root admin chưa
        SELECT COUNT(*) INTO root_admin_count
        FROM users
        WHERE pharmacy_id = NEW.pharmacy_id 
        AND is_root_admin = 1
        AND user_id != NEW.user_id;
        
        -- Nếu đã có root admin khác, báo lỗi
        IF root_admin_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nhà thuốc này đã có Admin gốc. Mỗi nhà thuốc chỉ được có 1 Admin gốc.';
        END IF;
    END IF;
    
    -- Ngăn không cho hạ quyền root admin xuống 0 nếu là root admin duy nhất
    IF OLD.is_root_admin = 1 AND NEW.is_root_admin = 0 THEN
        DECLARE manager_count INT;
        
        -- Đếm số manager còn lại trong pharmacy
        SELECT COUNT(*) INTO manager_count
        FROM users
        WHERE pharmacy_id = NEW.pharmacy_id 
        AND role = 'manager'
        AND user_id != NEW.user_id;
        
        -- Nếu không còn manager nào, báo lỗi
        IF manager_count = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Không thể hạ quyền Admin gốc duy nhất. Phải có ít nhất 1 quản lý trong nhà thuốc.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- TRIGGER: Ngăn xóa root admin nếu là duy nhất
-- ============================================
DROP TRIGGER IF EXISTS before_user_delete_check_root_admin;

DELIMITER $$

CREATE TRIGGER before_user_delete_check_root_admin
BEFORE DELETE ON users
FOR EACH ROW
BEGIN
    DECLARE manager_count INT;
    
    -- Nếu đang xóa root admin
    IF OLD.is_root_admin = 1 THEN
        -- Đếm số manager còn lại
        SELECT COUNT(*) INTO manager_count
        FROM users
        WHERE pharmacy_id = OLD.pharmacy_id 
        AND role = 'manager'
        AND user_id != OLD.user_id;
        
        -- Nếu không còn manager nào, báo lỗi
        IF manager_count = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Không thể xóa Admin gốc duy nhất. Phải có ít nhất 1 quản lý trong nhà thuốc.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- KIỂM TRA VÀ SỬA DỮ LIỆU HIỆN TẠI
-- ============================================

-- Tìm các pharmacy có nhiều hơn 1 root admin
SELECT 
    pharmacy_id,
    COUNT(*) as root_admin_count,
    GROUP_CONCAT(username) as root_admins
FROM users
WHERE is_root_admin = 1
GROUP BY pharmacy_id
HAVING COUNT(*) > 1;

-- Nếu có pharmacy nào có nhiều root admin, chỉ giữ lại admin đầu tiên
UPDATE users u1
SET is_root_admin = 0
WHERE is_root_admin = 1
AND user_id NOT IN (
    SELECT * FROM (
        SELECT MIN(u2.user_id)
        FROM users u2
        WHERE u2.pharmacy_id = u1.pharmacy_id
        AND u2.is_root_admin = 1
        GROUP BY u2.pharmacy_id
    ) as temp
);

-- ============================================
-- KIỂM TRA KẾT QUẢ
-- ============================================

SELECT 
    p.pharmacy_name,
    u.user_id,
    u.username,
    u.full_name,
    u.role,
    u.is_root_admin,
    CASE 
        WHEN u.is_root_admin = 1 THEN '🛡️ Admin gốc'
        WHEN u.role = 'manager' THEN '👤 Admin được phân quyền'
        ELSE '👤 Nhân viên'
    END as user_type
FROM users u
LEFT JOIN pharmacies p ON u.pharmacy_id = p.pharmacy_id
ORDER BY u.pharmacy_id, u.is_root_admin DESC, u.role DESC, u.user_id;

-- Kiểm tra số lượng root admin per pharmacy
SELECT 
    p.pharmacy_name,
    COUNT(CASE WHEN u.is_root_admin = 1 THEN 1 END) as root_admin_count,
    COUNT(CASE WHEN u.role = 'manager' THEN 1 END) as total_managers,
    COUNT(*) as total_users
FROM pharmacies p
LEFT JOIN users u ON p.pharmacy_id = u.pharmacy_id
GROUP BY p.pharmacy_id, p.pharmacy_name;

SELECT '✅ Triggers và ràng buộc đã được tạo thành công!' as message;
