<?php
/**
 * Database Helper Class
 * Provides secure database operations using prepared statements
 */
class Database {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Execute a prepared SELECT query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @param string $types Parameter types (i, d, s, b)
     * @return mysqli_result|false Result set or false on failure
     */
    public function select($query, $params = [], $types = '') {
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            error_log("Database prepare error: " . $this->conn->error);
            return false;
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            error_log("Database execute error: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Execute a prepared INSERT/UPDATE/DELETE query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @param string $types Parameter types (i, d, s, b)
     * @return bool True on success
     */
    public function execute($query, $params = [], $types = '') {
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            error_log("Database prepare error: " . $this->conn->error);
            return false;
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Database execute error: " . $stmt->error);
        }
        
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Get last inserted ID
     * @return int Last insert ID
     */
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Get single row from query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @param string $types Parameter types
     * @return array|null Row data or null if not found
     */
    public function selectOne($query, $params = [], $types = '') {
        $result = $this->select($query, $params, $types);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get all rows from query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @param string $types Parameter types
     * @return array Array of rows
     */
    public function selectAll($query, $params = [], $types = '') {
        $result = $this->select($query, $params, $types);
        $rows = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        
        return $rows;
    }
    
    /**
     * Count rows matching query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @param string $types Parameter types
     * @return int Row count
     */
    public function count($query, $params = [], $types = '') {
        $result = $this->select($query, $params, $types);
        
        if ($result) {
            return $result->num_rows;
        }
        
        return 0;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }
    
    /**
     * Escape string (fallback for non-prepared statements)
     * @param string $string String to escape
     * @return string Escaped string
     */
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}
