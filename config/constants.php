<?php
/**
 * Application Constants
 * 
 * This file defines all the constants used throughout the application.
 * Constants provide a way to define values that won't change during execution.
 * 
 * Categories:
 * - User roles and permissions
 * - Status codes
 * - Message types
 * - File types
 * - Pagination settings
 */

// User Roles
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');
define('ROLE_MODERATOR', 'moderator');

// User Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_PENDING', 'pending');
define('STATUS_BANNED', 'banned');

// Message Types
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// HTTP Status Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_INTERNAL_ERROR', 500);

// Pagination
define('DEFAULT_ITEMS_PER_PAGE', 10);
define('MAX_ITEMS_PER_PAGE', 100);

// File Types
define('IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

// Validation Rules
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_PASSWORD_LENGTH', 255);
define('MIN_USERNAME_LENGTH', 3);
define('MAX_USERNAME_LENGTH', 50);
define('MAX_EMAIL_LENGTH', 255);
define('MAX_NAME_LENGTH', 100);

// Rate Limiting
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// API Settings
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour

// Email Settings
define('EMAIL_VERIFICATION_REQUIRED', true);
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hour

// Product Categories (Example for e-commerce)
define('CATEGORY_ELECTRONICS', 'electronics');
define('CATEGORY_CLOTHING', 'clothing');
define('CATEGORY_BOOKS', 'books');
define('CATEGORY_HOME', 'home');
define('CATEGORY_SPORTS', 'sports');

// Order Status (Example for e-commerce)
define('ORDER_PENDING', 'pending');
define('ORDER_PROCESSING', 'processing');
define('ORDER_SHIPPED', 'shipped');
define('ORDER_DELIVERED', 'delivered');
define('ORDER_CANCELLED', 'cancelled');
define('ORDER_REFUNDED', 'refunded');

// Currency
define('DEFAULT_CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');

// Date Formats
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M j, Y');
define('DISPLAY_DATETIME_FORMAT', 'M j, Y g:i A');
?>
