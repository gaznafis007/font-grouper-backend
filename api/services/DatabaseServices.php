<?php
// api/services/DatabaseService.php
namespace Services;

use Config\Config;

class DatabaseService {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new \PDO(
                "mysql:host=" . Config::$DB_HOST . ";dbname=" . Config::$DB_NAME,
                Config::$DB_USER,
                Config::$DB_PASS
            );
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->initTables();
        } catch (\PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseService();
        }
        return self::$instance;
    }
    
    private function initTables() {
        // Create fonts table
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS fonts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create font groups table
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS font_groups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create font group items table (to store fonts in a group)
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS font_group_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                group_id INT NOT NULL,
                font_id INT NOT NULL,
                FOREIGN KEY (group_id) REFERENCES font_groups(id) ON DELETE CASCADE,
                FOREIGN KEY (font_id) REFERENCES fonts(id) ON DELETE CASCADE
            )
        ");
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams) {
        $setClauses = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setClauses[] = "$column = ?";
            $params[] = $value;
        }
        
        $setClause = implode(', ', $setClauses);
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(array_merge($params, $whereParams));
        
        return $stmt->rowCount();
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
}
?>