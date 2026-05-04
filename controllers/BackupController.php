<?php
require_once 'helpers/logger.php';

class BackupController {
    private $backupDir;
    
    public function __construct() {
        $this->backupDir = __DIR__ . '/../uploads/backups/';
        
        // Tạo thư mục backup nếu chưa có
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Trang quản lý backup
     */
    public function index() {
        requireManager();
        
        // Lấy danh sách file backup
        $backups = $this->getBackupFiles();
        
        $pageTitle = "Sao lưu & Khôi phục dữ liệu";
        require_once 'views/backup/index.php';
    }
    
    /**
     * Tạo backup
     */
    public function create() {
        requireManager();
        
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupDir . $filename;
            
            // Tạo backup bằng PHP thuần (không cần mysqldump)
            $backupContent = $this->createBackupContent();
            
            if (file_put_contents($filepath, $backupContent)) {
                logAction('BACKUP_CREATE', "Created backup: $filename");
                $_SESSION['success'] = "Sao lưu dữ liệu thành công: $filename";
            } else {
                throw new Exception("Không thể ghi file backup");
            }
            
        } catch (Exception $e) {
            logError('BACKUP_CREATE_ERROR', $e->getMessage());
            $_SESSION['error'] = "Lỗi khi sao lưu: " . $e->getMessage();
        }
        
        header('Location: index.php?page=backup');
        exit;
    }
    
    /**
     * Khôi phục từ backup
     */
    public function restore() {
        requireManager();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=backup');
            exit;
        }
        
        try {
            $filename = $_POST['backup_file'] ?? '';
            
            if (empty($filename)) {
                throw new Exception("Vui lòng chọn file backup");
            }
            
            $filepath = $this->backupDir . basename($filename);
            
            if (!file_exists($filepath)) {
                throw new Exception("File backup không tồn tại");
            }
            
            // Lấy thông tin database từ config
            $host = DB_HOST;
            $user = DB_USER;
            $pass = DB_PASS;
            $name = DB_NAME;
            
            // Khôi phục bằng mysql
            $command = sprintf(
                'mysql --host=%s --user=%s --password=%s %s < %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($name),
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0) {
                logAction('BACKUP_RESTORE', "Restored from backup: $filename");
                $_SESSION['success'] = "Khôi phục dữ liệu thành công từ: $filename";
            } else {
                throw new Exception("Lỗi khi khôi phục: " . implode("\n", $output));
            }
            
        } catch (Exception $e) {
            logError('BACKUP_RESTORE_ERROR', $e->getMessage());
            $_SESSION['error'] = "Lỗi khi khôi phục: " . $e->getMessage();
        }
        
        header('Location: index.php?page=backup');
        exit;
    }
    
    /**
     * Tải xuống file backup
     */
    public function download() {
        requireManager();
        
        $filename = $_GET['file'] ?? '';
        $filepath = $this->backupDir . basename($filename);
        
        if (!file_exists($filepath)) {
            $_SESSION['error'] = "File backup không tồn tại";
            header('Location: index.php?page=backup');
            exit;
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
    
    /**
     * Xóa file backup
     */
    public function delete() {
        requireManager();
        
        $filename = $_GET['file'] ?? '';
        $filepath = $this->backupDir . basename($filename);
        
        try {
            if (!file_exists($filepath)) {
                throw new Exception("File backup không tồn tại");
            }
            
            if (unlink($filepath)) {
                logAction('BACKUP_DELETE', "Deleted backup: $filename");
                $_SESSION['success'] = "Xóa file backup thành công";
            } else {
                throw new Exception("Không thể xóa file backup");
            }
            
        } catch (Exception $e) {
            logError('BACKUP_DELETE_ERROR', $e->getMessage());
            $_SESSION['error'] = "Lỗi khi xóa: " . $e->getMessage();
        }
        
        header('Location: index.php?page=backup');
        exit;
    }
    
    /**
     * Lấy danh sách file backup
     */
    private function getBackupFiles() {
        $files = [];
        
        if (is_dir($this->backupDir)) {
            $items = scandir($this->backupDir);
            
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && pathinfo($item, PATHINFO_EXTENSION) === 'sql') {
                    $filepath = $this->backupDir . $item;
                    $files[] = [
                        'name' => $item,
                        'size' => filesize($filepath),
                        'date' => filemtime($filepath)
                    ];
                }
            }
            
            // Sắp xếp theo ngày mới nhất
            usort($files, function($a, $b) {
                return $b['date'] - $a['date'];
            });
        }
        
        return $files;
    }
    
    /**
     * Tạo nội dung backup bằng PHP
     */
    private function createBackupContent() {
        try {
            // Kết nối database
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $backup = "-- ============================================\n";
            $backup .= "-- DUO PHARMA - Database Backup\n";
            $backup .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
            $backup .= "-- Database: " . DB_NAME . "\n";
            $backup .= "-- ============================================\n\n";
            
            $backup .= "SET FOREIGN_KEY_CHECKS = 0;\n";
            $backup .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $backup .= "SET AUTOCOMMIT = 0;\n";
            $backup .= "START TRANSACTION;\n\n";
            
            // Lấy danh sách bảng
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                $backup .= "-- ============================================\n";
                $backup .= "-- Table: $table\n";
                $backup .= "-- ============================================\n\n";
                
                // Lấy cấu trúc bảng
                $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
                $backup .= "DROP TABLE IF EXISTS `$table`;\n";
                $backup .= $createTable['Create Table'] . ";\n\n";
                
                // Lấy dữ liệu
                $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    $backup .= "-- Dữ liệu cho bảng `$table`\n";
                    
                    foreach ($rows as $row) {
                        $columns = array_keys($row);
                        $values = array_values($row);
                        
                        // Escape values
                        $escapedValues = array_map(function($value) use ($pdo) {
                            return $value === null ? 'NULL' : $pdo->quote($value);
                        }, $values);
                        
                        $backup .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
                    }
                    $backup .= "\n";
                }
            }
            
            $backup .= "COMMIT;\n";
            $backup .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            $backup .= "\n-- Backup completed: " . date('Y-m-d H:i:s') . "\n";
            
            return $backup;
            
        } catch (Exception $e) {
            throw new Exception("Lỗi khi tạo backup: " . $e->getMessage());
        }
    }
}
