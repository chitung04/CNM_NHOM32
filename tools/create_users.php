<?php
/**
 * Create or update two users:
 * - quanly / quanly1234 (manager)
 * - nhanvien / nhanvien1234 (staff)
 * Usage: php tools/create_users.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    $users = [
        [
            'username' => 'quanly',
            'password' => 'quanly12345',
            'full_name' => 'Quản lý',
            'role' => 'manager'
        ],
        [
            'username' => 'nhanvien',
            'password' => 'nhanvien2233',
            'full_name' => 'Nhân viên',
            'role' => 'staff'
        ]
    ];

    foreach ($users as $u) {
        // Check existence
        $stmt = $db->prepare('SELECT user_id FROM users WHERE username = ?');
        $stmt->execute([$u['username']]);
        $row = $stmt->fetch();

        $hashed = password_hash($u['password'], PASSWORD_DEFAULT);

        if ($row) {
            $id = $row['user_id'];
            $stmt = $db->prepare('UPDATE users SET password = ?, full_name = ?, role = ?, is_active = 1, updated_at = NOW() WHERE user_id = ?');
            $stmt->execute([$hashed, $u['full_name'], $u['role'], $id]);
            echo "Updated user: {$u['username']}\n";
        } else {
            $stmt = $db->prepare('INSERT INTO users (username, password, full_name, role, created_at, is_active) VALUES (?, ?, ?, ?, NOW(), 1)');
            $stmt->execute([$u['username'], $hashed, $u['full_name'], $u['role']]);
            echo "Created user: {$u['username']}\n";
        }
    }

    echo "Done.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
