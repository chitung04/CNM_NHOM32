<?php
/**
 * Database Connection Class - Singleton Pattern
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $maxRetries = 3;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
                // Thử kết nối với database cụ thể trước
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 10,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                
                // Kiểm tra kết nối
                $this->connection->query("SELECT 1");
                return; // Kết nối thành công
                
            } catch (PDOException $e) {
                // Nếu database không tồn tại, thử tạo database
                try {
                    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo->exec("USE " . DB_NAME);
                    
                    // Tạo bảng users nếu chưa có
                    $this->createUsersTable($pdo);
                    
                    $this->connection = $pdo;
                    return; // Tạo database thành công
                    
                } catch (PDOException $e2) {
                    $retryCount++;
                    if ($retryCount >= $maxRetries) {
                        // Ghi log chi tiết
                        error_log("Database connection error after {$maxRetries} retries: " . $e2->getMessage());
                        error_log("Connection details - Host: " . DB_HOST . ", Database: " . DB_NAME . ", User: " . DB_USER);
                        
                        // Thông báo lỗi chi tiết hơn
                        if (strpos($e2->getMessage(), 'Connection refused') !== false) {
                            throw new Exception('Không thể kết nối MySQL. Vui lòng kiểm tra XAMPP MySQL đã được khởi động chưa.');
                        } elseif (strpos($e2->getMessage(), 'Access denied') !== false) {
                            throw new Exception('Lỗi xác thực database. Kiểm tra username/password trong file .env');
                        } else {
                            throw new Exception('Lỗi kết nối database: ' . $e2->getMessage());
                        }
                    }
                    sleep(1); // Đợi 1 giây trước khi thử lại
                }
            }
        }
    }
    
    private function createUsersTable($pdo) {
        $createTable = "
        CREATE TABLE IF NOT EXISTS users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            role ENUM('staff', 'manager') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTable);
        
        // Kiểm tra xem có users không
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            // Tạo users mặc định với password 123456
            $hash = password_hash('123456', PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO users (username, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?)");
            
            $users = [
                ['admin', $hash, 'Quản lý', '0123456789', 'manager'],
                ['nhanvien1', $hash, 'Nhân viên 1', '0987654321', 'staff'],
                ['nhanvien2', $hash, 'Nhân viên 2', '0912345678', 'staff']
            ];
            
            foreach ($users as $user) {
                $insertStmt->execute($user);
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}