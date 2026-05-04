<?php
/**
 * Helper functions cho multi-tenant pharmacy
 */

/**
 * Lấy pharmacy_id từ session
 */
function getCurrentPharmacyId() {
    return $_SESSION['pharmacy_id'] ?? null;
}

/**
 * Kiểm tra pharmacy_id có tồn tại không
 * Nếu không có, trả về null thay vì throw exception
 */
function requirePharmacyId() {
    $pharmacyId = getCurrentPharmacyId();
    
    // Nếu không có pharmacy_id, có thể là:
    // 1. User cũ chưa có pharmacy_id trong database
    // 2. Session chưa được set đúng
    // Trong trường hợp này, trả về null để các model xử lý
    if (!$pharmacyId) {
        // Thử lấy từ database nếu user đã đăng nhập
        if (isset($_SESSION['user_id'])) {
            try {
                require_once 'models/Database.php';
                $db = Database::getInstance();
                $stmt = $db->query("SELECT pharmacy_id FROM users WHERE user_id = ?", [$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user && $user['pharmacy_id']) {
                    $_SESSION['pharmacy_id'] = $user['pharmacy_id'];
                    return $user['pharmacy_id'];
                }
            } catch (Exception $e) {
                // Ignore error
            }
        }
        
        // Nếu vẫn không có, trả về null
        return null;
    }
    
    return $pharmacyId;
}

/**
 * Thêm điều kiện pharmacy_id vào WHERE clause
 */
function addPharmacyFilter($sql, &$params = []) {
    $pharmacyId = getCurrentPharmacyId();
    if (!$pharmacyId) {
        return $sql;
    }
    
    // Kiểm tra xem SQL đã có WHERE chưa
    if (stripos($sql, 'WHERE') !== false) {
        $sql .= " AND pharmacy_id = ?";
    } else {
        $sql .= " WHERE pharmacy_id = ?";
    }
    
    $params[] = $pharmacyId;
    return $sql;
}

/**
 * Lấy thông tin nhà thuốc hiện tại
 */
function getCurrentPharmacy() {
    $pharmacyId = getCurrentPharmacyId();
    if (!$pharmacyId) {
        return null;
    }
    
    try {
        require_once 'models/Database.php';
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM pharmacies WHERE pharmacy_id = ?", [$pharmacyId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Kiểm tra user có thuộc pharmacy hiện tại không
 */
function checkUserBelongsToPharmacy($userId) {
    $pharmacyId = getCurrentPharmacyId();
    if (!$pharmacyId) {
        return false;
    }
    
    try {
        require_once 'models/Database.php';
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE user_id = ? AND pharmacy_id = ?", [$userId, $pharmacyId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}
