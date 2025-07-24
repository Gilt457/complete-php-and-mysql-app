<?php
/**
 * Utility Functions Library
 * 
 * This file contains utility functions used throughout the application.
 * Functions are organized by category and provide common functionality.
 * 
 * Categories:
 * - Session management
 * - Flash messages
 * - Formatting functions
 * - Security functions
 * - File handling
 * - URL and routing
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Session Management Functions
 */

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Require user to be logged in
 * 
 * @param string $redirectUrl URL to redirect if not logged in
 */
function requireLogin($redirectUrl = '/login.php')
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Check if user has specific role
 * 
 * @param string $role Required role
 * @return bool
 */
function hasRole($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require user to have specific role
 * 
 * @param string $role Required role
 * @param string $redirectUrl URL to redirect if unauthorized
 */
function requireRole($role, $redirectUrl = '/unauthorized.php')
{
    if (!hasRole($role)) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Flash Message Functions
 */

/**
 * Set flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 */
function setFlashMessage($type, $message)
{
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash messages
 * 
 * @return array Flash messages
 */
function getFlashMessages()
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Display flash messages HTML
 * 
 * @return string HTML for flash messages
 */
function displayFlashMessages()
{
    $messages = getFlashMessages();
    $html = '';
    
    foreach ($messages as $message) {
        $alertClass = '';
        switch ($message['type']) {
            case MSG_SUCCESS:
                $alertClass = 'alert-success';
                break;
            case MSG_ERROR:
                $alertClass = 'alert-danger';
                break;
            case MSG_WARNING:
                $alertClass = 'alert-warning';
                break;
            case MSG_INFO:
                $alertClass = 'alert-info';
                break;
            default:
                $alertClass = 'alert-info';
        }
        
        $html .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        $html .= htmlspecialchars($message['message']);
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * Formatting Functions
 */

/**
 * Format currency
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency symbol
 * @return string Formatted currency
 */
function formatCurrency($amount, $currency = CURRENCY_SYMBOL)
{
    return $currency . number_format($amount, 2);
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT)
{
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 * 
 * @param string $datetime Datetime string
 * @param string $format Output format
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = DISPLAY_DATETIME_FORMAT)
{
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Format file size
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Truncate text
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $suffix = '...')
{
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Security Functions
 */

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken()
{
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool
 */
function validateCSRFToken($token)
{
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Generate CSRF token input field
 * 
 * @return string HTML input field
 */
function csrfTokenField()
{
    $token = generateCSRFToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
}

/**
 * Sanitize output for HTML
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitizeOutput($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Clean input data
 * 
 * @param string $data Input data
 * @return string Cleaned data
 */
function cleanInput($data)
{
    return trim(stripslashes($data));
}

/**
 * File Handling Functions
 */

/**
 * Get file extension
 * 
 * @param string $filename Filename
 * @return string File extension
 */
function getFileExtension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file is image
 * 
 * @param string $filename Filename
 * @return bool
 */
function isImageFile($filename)
{
    $extension = getFileExtension($filename);
    return in_array($extension, IMAGE_TYPES);
}

/**
 * Generate unique filename
 * 
 * @param string $originalName Original filename
 * @param string $prefix Filename prefix
 * @return string Unique filename
 */
function generateUniqueFilename($originalName, $prefix = '')
{
    $extension = getFileExtension($originalName);
    return $prefix . uniqid() . '.' . $extension;
}

/**
 * URL and Routing Functions
 */

/**
 * Get current URL
 * 
 * @return string Current URL
 */
function getCurrentUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Get base URL
 * 
 * @return string Base URL
 */
function getBaseUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
}

/**
 * Build URL with parameters
 * 
 * @param string $url Base URL
 * @param array $params URL parameters
 * @return string Complete URL
 */
function buildUrl($url, $params = [])
{
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

/**
 * Redirect to URL
 * 
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect($url, $statusCode = 302)
{
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Validation Helper Functions
 */

/**
 * Check if email is valid
 * 
 * @param string $email Email to validate
 * @return bool
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if URL is valid
 * 
 * @param string $url URL to validate
 * @return bool
 */
function isValidUrl($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Array and Data Manipulation Functions
 */

/**
 * Get array value with default
 * 
 * @param array $array Array to search
 * @param string $key Key to find
 * @param mixed $default Default value
 * @return mixed
 */
function arrayGet($array, $key, $default = null)
{
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Check if array is associative
 * 
 * @param array $array Array to check
 * @return bool
 */
function isAssociativeArray($array)
{
    return array_keys($array) !== range(0, count($array) - 1);
}

/**
 * Pagination Functions
 */

/**
 * Generate pagination links
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links
 * @param int $maxLinks Maximum number of page links to show
 * @return string Pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl, $maxLinks = 5)
{
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination">';
    
    // Previous page link
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . buildUrl($baseUrl, ['page' => $currentPage - 1]) . '">Previous</a></li>';
    }
    
    // Calculate start and end page numbers
    $startPage = max(1, $currentPage - floor($maxLinks / 2));
    $endPage = min($totalPages, $startPage + $maxLinks - 1);
    
    // Adjust start page if we're near the end
    if ($endPage - $startPage < $maxLinks - 1) {
        $startPage = max(1, $endPage - $maxLinks + 1);
    }
    
    // First page link
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . buildUrl($baseUrl, ['page' => 1]) . '">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Page number links
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i == $currentPage) ? ' active' : '';
        $html .= '<li class="page-item' . $activeClass . '"><a class="page-link" href="' . buildUrl($baseUrl, ['page' => $i]) . '">' . $i . '</a></li>';
    }
    
    // Last page link
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . buildUrl($baseUrl, ['page' => $totalPages]) . '">' . $totalPages . '</a></li>';
    }
    
    // Next page link
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . buildUrl($baseUrl, ['page' => $currentPage + 1]) . '">Next</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Logging Functions
 */

/**
 * Log error message
 * 
 * @param string $message Error message
 * @param string $file Log file name
 */
function logError($message, $file = 'error.log')
{
    $logFile = LOGS_PATH . '/' . $file;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] ERROR: {$message}" . PHP_EOL;
    
    if (!is_dir(LOGS_PATH)) {
        mkdir(LOGS_PATH, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Log info message
 * 
 * @param string $message Info message
 * @param string $file Log file name
 */
function logInfo($message, $file = 'app.log')
{
    if (APP_DEBUG) {
        $logFile = LOGS_PATH . '/' . $file;
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] INFO: {$message}" . PHP_EOL;
        
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Performance monitoring
 * 
 * @param string $label Performance checkpoint label
 */
function performanceCheckpoint($label)
{
    if (APP_DEBUG) {
        $memory = memory_get_usage(true);
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        logInfo("Performance [{$label}]: " . formatFileSize($memory) . " memory, {$time}s elapsed", 'performance.log');
    }
}
?>
