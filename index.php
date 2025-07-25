<?php
/**
 * Application Entry Point
 * 
 * This is the main entry point for the Alibaba Clone MVC application.
 * It initializes the application, handles routing, and coordinates
 * the Model-View-Controller architecture.
 * 
 * Features:
 * - MVC Architecture implementation
 * - Advanced routing system
 * - Error handling and logging
 * - Security headers and protection
 * - Session management
 * - Performance monitoring
 */

// Start session
session_start();

// Start output buffering
ob_start();

// Include configuration and dependencies
require_once 'config/config.php';
require_once 'includes/functions.php';

// Include MVC components
require_once 'classes/Database.php';
require_once 'classes/User.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Performance monitoring start
if (defined('APP_DEBUG') && APP_DEBUG) {
    $startTime = microtime(true);
    $startMemory = memory_get_usage();
}

try {
    // Initialize and dispatch router
    $router = require_once 'routes.php';
    
    // Handle the request using MVC architecture
    $router->dispatch();
    
} catch (Exception $e) {
    // Log error
    error_log("Application Error: " . $e->getMessage());
    
    // Handle error gracefully
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "<h1>Application Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        // Show user-friendly error page
        require_once 'controllers/ErrorController.php';
        $errorController = new ErrorController();
        $errorController->serverError();
    }
}

// Performance monitoring end
if (defined('APP_DEBUG') && APP_DEBUG && isset($startTime)) {
    $executionTime = (microtime(true) - $startTime) * 1000;
    $memoryUsage = memory_get_usage() - $startMemory;
    $peakMemory = memory_get_peak_usage();
    
    echo "<!-- Performance Info: Execution Time: " . number_format($executionTime, 2) . "ms, ";
    echo "Memory Usage: " . number_format($memoryUsage / 1024, 2) . "KB, ";
    echo "Peak Memory: " . number_format($peakMemory / 1024, 2) . "KB -->";
}

// Flush output buffer
ob_end_flush();
?>
