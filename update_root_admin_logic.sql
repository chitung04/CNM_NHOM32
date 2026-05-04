-- ============================================
-- CẬP NHẬT LOGIC ROOT ADMIN MỚI
-- ============================================
-- Logic mới: 
-- - Có thể có nhiều Root Admin (admin cố định) trong 1 pharmacy
-- - Root Admin chỉ sửa được manager được phân quyền (is_root_admin = 0)
-- - Root Admin KHÔNG sửa được Root Admin khác

-- 1. XÓA CÁC TRIGGER CŨ (nếu có)
DROP TRIGGER IF EXISTS before_user_insert_check_root_admin;
DROP TRIGGER IF EXISTS before_user_update_check_root_admin;
DROP TRIGGER IF EXISTS before_user_delete_check_root_admin;

-- 2. TẠO TRIGGER MỚI: Ngăn xóa tất cả managers
DELIMITER $$

CREATE TRIGGER before_user_delete_check_manager
BEFORE DELETE ON users
FOR EACH ROW
BEGIN
    DECLARE manager_count INT;
    
    -- Nếu đang xóa manager
    IF OLD.role = 'manager' THEN
        -- Đếm số manager còn lại
        SELECT COUNT(*) INTO manager_count
        FROM users
        WHERE pharmacy_id = OLD.pharmacy_id 
        AND role = 'manager'
        AND user_id != OLD.user_id;
        
        -- Nếu không còn manager nào, báo lỗi
        IF manager_count = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Không thể xóa quản lý duy nhất. Phải có ít nhất 1 quản lý trong nhà thuốc.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- 3. KIỂM TRA DỮ LIỆU HIỆN TẠI
SELECT 
    p.pharmacy_name,
    u.user_id,
    u.username,
    u.full_name,
    u.role,
    u.is_root_admin,
    CASE 
        WHEN u.is_root_admin = 1 THEN '🛡️ Admin cố định'
        WHEN u.role = 'manager' THEN '👤 Admin được phân quyền'
        ELSE '👤 Nhân viên'
    END as user_type
FROM users u
LEFT JOIN pharmacies p ON u.pharmacy_id = p.pharmacy_id
WHERE u.role = 'manager'
ORDER BY u.pharmacy_id, u.is_root_admin DESC, u.user_id;

-- 4. THỐNG KÊ
SELECT 
    p.pharmacy_name,
    COUNT(CASE WHEN u.is_root_admin = 1 THEN 1 END) as root_admin_count,
    COUNT(CASE WHEN u.role = 'manager' AND u.is_root_admin = 0 THEN 1 END) as promoted_admin_count,
    COUNT(CASE WHEN u.role = 'manager' THEN 1 END) as total_managers
FROM pharmacies p
LEFT JOIN users u ON p.pharmacy_id = u.pharmacy_id
GROUP BY p.pharmacy_id, p.pharmacy_name;

SELECT '✅ Logic Root Admin mới đã được cập nhật!' as message;
SELECT 'ℹ️ Bây giờ có thể có nhiều Root Admin (admin cố định) trong 1 pharmacy' as note;
