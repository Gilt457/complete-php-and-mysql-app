<?php
/**
 * Database Configuration Class
 * 
 * This file provides database connection configuration and connection testing.
 * It encapsulates database connection logic and provides methods for connection management.
 * 
 * Features:
 * - PDO connection with error handling
 * - Connection testing
 * - Database configuration validation
 * - Connection pooling preparation
 */

require_once 'config.php';

class DatabaseConfig
{
    private static $instance = null;
    private $connection = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {}
    
    /**
     * Get singleton instance of DatabaseConfig
     * 
     * @return DatabaseConfig
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection
     * 
     * @return PDO Database connection
     * @throws Exception If connection fails
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                
                // Log successful connection
                $this->logMessage("Database connection established successfully");
                
            } catch (PDOException $e) {
                $this->logError("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection failed. Please check your configuration.");
            }
        }
        
        return $this->connection;
    }
    
    /**
     * Test database connection
     * 
     * @return bool True if connection is successful
     */
    public function testConnection()
    {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            $this->logError("Connection test failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Close database connection
     */
    public function closeConnection()
    {
        $this->connection = null;
    }
    
    /**
     * Get database information
     * 
     * @return array Database information
     */
    public function getDatabaseInfo()
    {
        try {
            $pdo = $this->getConnection();
            $version = $pdo->query("SELECT VERSION()")->fetchColumn();
            
            return [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'version' => $version,
                'charset' => 'utf8mb4',
                'status' => 'connected'
            ];
        } catch (Exception $e) {
            return [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute a raw SQL query (use with caution)
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return PDOStatement
     */
    public function executeQuery($sql, $params = [])
    {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Query execution failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Query execution failed");
        }
    }
    
    /**
     * Begin database transaction
     */
    public function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit database transaction
     */
    public function commit()
    {
        $this->getConnection()->commit();
    }
    
    /**
     * Rollback database transaction
     */
    public function rollback()
    {
        $this->getConnection()->rollBack();
    }
    
    /**
     * Log error message
     * 
     * @param string $message Error message
     */
    private function logError($message)
    {
        $logFile = LOGS_PATH . '/database_error.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] ERROR: {$message}" . PHP_EOL;
        
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log informational message
     * 
     * @param string $message Info message
     */
    private function logMessage($message)
    {
        if (APP_DEBUG) {
            $logFile = LOGS_PATH . '/database.log';
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] INFO: {$message}" . PHP_EOL;
            
            if (!is_dir(LOGS_PATH)) {
                mkdir(LOGS_PATH, 0755, true);
            }
            
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {}
}
?>
