-- ============================================
-- THÊM CỘT is_root_admin VÀO BẢNG USERS
-- ============================================

-- Thêm cột is_root_admin (1 = admin gốc, 0 = admin được phân quyền hoặc staff)
ALTER TABLE users 
ADD COLUMN is_root_admin TINYINT(1) DEFAULT 0 AFTER role;

-- Đánh dấu admin đầu tiên của mỗi pharmacy là root admin
-- (User có user_id nhỏ nhất trong mỗi pharmacy và có role = 'manager')
UPDATE users u1
SET is_root_admin = 1
WHERE role = 'manager'
AND user_id = (
    SELECT MIN(u2.user_id) 
    FROM (SELECT * FROM users) u2 
    WHERE u2.pharmacy_id = u1.pharmacy_id 
    AND u2.role = 'manager'
);

-- Tạo index cho is_root_admin
CREATE INDEX idx_users_root_admin ON users(is_root_admin);

-- Kiểm tra kết quả
SELECT 
    user_id,
    username,
    full_name,
    role,
    is_root_admin,
    pharmacy_id,
    CASE 
        WHEN is_root_admin = 1 THEN 'Admin gốc (Root Admin)'
        WHEN role = 'manager' THEN 'Admin được phân quyền'
        ELSE 'Nhân viên (Staff)'
    END as user_type
FROM users
ORDER BY pharmacy_id, is_root_admin DESC, role DESC, user_id;

SELECT 'Root admin column added successfully!' as message;
