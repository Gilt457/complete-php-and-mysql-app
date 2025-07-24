<?php
/**
 * Database Connection Class
 * 
 * This class provides a centralized database connection using PDO.
 * It implements the Singleton pattern to ensure only one connection instance.
 * 
 * Features:
 * - Singleton pattern for connection management
 * - PDO with prepared statements for security
 * - Error logging and handling
 * - Transaction support
 * - Query execution methods
 */

require_once __DIR__ . '/../config/config.php';

class Database
{
    private static $instance = null;
    private $pdo = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->connect();
    }
    
    /**
     * Get database instance (Singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     * 
     * @throws Exception If connection fails
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            $this->logError("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Get PDO connection object
     * 
     * @return PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }
    
    /**
     * Execute a prepared statement
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for the query
     * @return PDOStatement
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Query execution failed: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Query execution failed");
        }
    }
    
    /**
     * Fetch all results from a query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for the query
     * @return array
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single result from a query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for the query
     * @return array|false
     */
    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch single column value
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for the query
     * @return mixed
     */
    public function fetchColumn($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert data into database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     */
    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update data in database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Delete data from database
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->pdo->rollBack();
    }
    
    /**
     * Check if table exists
     * 
     * @param string $tableName Table name
     * @return bool
     */
    public function tableExists($tableName)
    {
        $sql = "SHOW TABLES LIKE :table";
        $result = $this->fetch($sql, ['table' => $tableName]);
        return $result !== false;
    }
    
    /**
     * Get table schema
     * 
     * @param string $tableName Table name
     * @return array
     */
    public function getTableSchema($tableName)
    {
        $sql = "DESCRIBE {$tableName}";
        return $this->fetchAll($sql);
    }
    
    /**
     * Execute multiple queries (useful for migrations)
     * 
     * @param string $sqlFile Path to SQL file
     * @return bool
     */
    public function executeSqlFile($sqlFile)
    {
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: {$sqlFile}");
        }
        
        $sql = file_get_contents($sqlFile);
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        try {
            $this->beginTransaction();
            
            foreach ($queries as $query) {
                if (!empty($query)) {
                    $this->query($query);
                }
            }
            
            $this->commit();
            return true;
            
        } catch (Exception $e) {
            $this->rollback();
            $this->logError("SQL file execution failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Log error messages
     * 
     * @param string $message Error message
     */
    private function logError($message)
    {
        $logFile = LOGS_PATH . '/database.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] ERROR: {$message}" . PHP_EOL;
        
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {}
}
?>
