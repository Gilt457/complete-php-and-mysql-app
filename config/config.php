<?php
/**
 * Application Configuration File
 * 
 * This file loads environment variables and sets up application-wide constants.
 * It serves as the central configuration hub for the entire application.
 * 
 * Functions:
 * - Loads environment variables from .env file
 * - Defines application constants
 * - Sets up error reporting based on environment
 * - Configures session settings
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Application Constants
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Professional PHP MySQL App');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'php_mysql_app');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Security Configuration
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'change-this-secret-key');
define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');

// File Upload Configuration
define('MAX_FILE_SIZE', (int)($_ENV['MAX_FILE_SIZE'] ?? 5242880)); // 5MB
define('ALLOWED_EXTENSIONS', explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,gif'));
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');

// Session Configuration
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200)); // 2 hours
define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'PHP_MYSQL_APP_SESSION');

// Path Constants
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/error.log');
}

// Timezone
date_default_timezone_set('UTC');

// Session Configuration
ini_set('session.name', SESSION_NAME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
