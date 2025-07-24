# PHP and MySQL Professional Web Application - Complete Documentation

## Table of Contents

1. [Overview](#overview)
2. [Project Structure](#project-structure)
3. [Installation Guide](#installation-guide)
4. [Configuration](#configuration)
5. [Database Architecture](#database-architecture)
6. [Application Components](#application-components)
7. [Security Features](#security-features)
8. [API Endpoints](#api-endpoints)
9. [User Guide](#user-guide)
10. [Development Guide](#development-guide)
11. [Deployment](#deployment)
12. [Troubleshooting](#troubleshooting)

## Overview

This is a professional PHP and MySQL web application built with modern development practices, security considerations, and scalable architecture. The application serves as a complete e-commerce platform with user management, product catalog, shopping cart, and administrative features.

### Key Features

- **Modern PHP Architecture**: Object-oriented design with MVC pattern
- **Secure Authentication**: Password hashing, session management, CSRF protection
- **Responsive Design**: Bootstrap 5 with custom CSS and JavaScript
- **Database Management**: Comprehensive schema with optimized indexes
- **Security First**: XSS protection, SQL injection prevention, input validation
- **User Experience**: Intuitive interface with AJAX functionality
- **Admin Panel**: Complete backend management system
- **Performance Optimized**: Efficient queries, caching, and lazy loading

## Project Structure

```
complete php and mysql app/
├── config/                     # Configuration files
│   ├── config.php             # Main application config
│   ├── constants.php          # Application constants
│   └── database.php           # Database configuration
├── classes/                    # PHP Classes (Models)
│   ├── Database.php           # Database connection & operations
│   ├── User.php               # User model with authentication
│   ├── Product.php            # Product management model
│   └── Validator.php          # Input validation class
├── controllers/                # Controller layer (future expansion)
│   ├── AuthController.php     # Authentication handling
│   ├── UserController.php     # User management
│   └── ProductController.php  # Product operations
├── views/                      # View templates
│   ├── auth/                  # Authentication pages
│   │   ├── login.php         # User login page
│   │   ├── register.php      # User registration
│   │   └── logout.php        # Logout handling
│   ├── user/                  # User management pages
│   ├── product/               # Product-related pages
│   ├── admin/                 # Admin panel pages
│   ├── pages/                 # Static content pages
│   └── home.php              # Homepage
├── includes/                   # Reusable components
│   ├── functions.php          # Utility functions
│   ├── header.php            # Common header
│   ├── footer.php            # Common footer
│   └── navbar.php            # Navigation bar
├── public/                     # Public assets
│   ├── css/                   # Stylesheets
│   │   └── style.css         # Custom CSS
│   ├── js/                    # JavaScript files
│   │   └── app.js            # Custom JavaScript
│   ├── images/                # Image assets
│   └── uploads/               # File uploads
├── database/                   # Database files
│   ├── schema.sql             # Complete database schema
│   ├── migrations/            # Database migrations
│   └── seeds/                 # Sample data
├── logs/                       # Application logs
├── .htaccess                   # Apache configuration
├── .env.example               # Environment variables template
├── index.php                  # Application entry point
└── README.md                  # Project documentation
```

## Installation Guide

### Prerequisites

- **PHP 7.4+** with extensions:
  - PDO MySQL
  - mbstring
  - openssl
  - curl
  - gd (for image processing)
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Apache/Nginx** web server
- **Composer** (optional, for dependencies)

### Step-by-Step Installation

1. **Download/Clone the Project**

   ```bash
   # If using Git
   git clone <repository-url>
   cd "complete php and mysql app"

   # Or extract ZIP file to your web directory
   ```

2. **Configure Environment**

   ```bash
   # Copy environment configuration
   copy .env.example .env

   # Edit .env file with your settings
   notepad .env
   ```

3. **Database Setup**

   ```sql
   -- Create database
   CREATE DATABASE php_mysql_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

   -- Import schema
   mysql -u root -p php_mysql_app < database/schema.sql
   ```

4. **File Permissions**

   ```bash
   # Ensure proper permissions (Linux/Mac)
   chmod 755 public/uploads/
   chmod 755 logs/
   chmod 644 .env
   ```

5. **Web Server Configuration**
   - Point document root to the project directory
   - Ensure .htaccess is enabled (Apache)
   - Configure virtual host (recommended)

## Configuration

### Environment Variables (.env)

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=php_mysql_app
DB_USER=root
DB_PASS=your_password

# Application Settings
APP_NAME="Your App Name"
APP_URL=http://localhost/your-app
APP_ENV=development
APP_DEBUG=true

# Security
SECRET_KEY=your-secret-key-here
CSRF_TOKEN_NAME=csrf_token

# File Upload
MAX_FILE_SIZE=5242880
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf
```

### Main Configuration (config/config.php)

This file handles:

- Environment variable loading
- Constant definitions
- Error reporting settings
- Session configuration
- Security headers

### Constants (config/constants.php)

Defines application-wide constants:

- User roles and statuses
- Message types
- HTTP status codes
- Validation rules
- Business logic constants

## Database Architecture

### Core Tables

#### Users Table

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
    status ENUM('active', 'inactive', 'pending', 'banned') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Purpose**: Stores user account information with authentication data.

**Key Features**:

- Unique email and username constraints
- Role-based access control
- Account status management
- Timestamp tracking
- Password hashing support

#### Products Table

```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    sku VARCHAR(100) UNIQUE NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT,
    stock_quantity INT DEFAULT 0,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Purpose**: Product catalog with inventory management.

**Key Features**:

- SEO-friendly slugs
- Stock tracking
- Category relationships
- Featured products
- Status management

#### Orders Table

```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**Purpose**: Order management and tracking.

### Relationships

- Users → Orders (One-to-Many)
- Categories → Products (One-to-Many)
- Products → Order Items (One-to-Many)
- Orders → Order Items (One-to-Many)

### Indexes and Performance

- Primary keys on all tables
- Unique indexes on email, username, SKU
- Foreign key indexes
- Search indexes on product names and descriptions
- Composite indexes for common query patterns

## Application Components

### 1. Database Class (classes/Database.php)

**Purpose**: Centralized database connection and operations using PDO.

**Key Methods**:

- `getInstance()`: Singleton pattern for connection management
- `query($sql, $params)`: Execute prepared statements
- `insert($table, $data)`: Insert data with automatic parameter binding
- `update($table, $data, $where, $params)`: Update operations
- `delete($table, $where, $params)`: Delete operations
- `beginTransaction()`, `commit()`, `rollback()`: Transaction support

**Features**:

- PDO with prepared statements (SQL injection protection)
- Error logging and handling
- Transaction support
- Connection pooling ready
- Query optimization

### 2. User Class (classes/User.php)

**Purpose**: User management, authentication, and authorization.

**Key Methods**:

- `register($userData)`: User registration with validation
- `login($identifier, $password)`: Authentication
- `getById($userId)`: Retrieve user information
- `updateProfile($userId, $data)`: Profile management
- `changePassword($userId, $current, $new)`: Password updates

**Features**:

- Secure password hashing (password_hash/verify)
- Session management
- Role-based access control
- Input validation
- Account status management

### 3. Product Class (classes/Product.php)

**Purpose**: Product catalog management and e-commerce operations.

**Key Methods**:

- `create($productData)`: Add new products
- `getAll($page, $limit, $filters)`: Paginated product listing
- `getById($productId)`: Product details
- `update($productId, $data)`: Product updates
- `search($query)`: Product search

**Features**:

- Image upload handling
- Inventory management
- Search and filtering
- Pagination support
- Category relationships

### 4. Validator Class (classes/Validator.php)

**Purpose**: Comprehensive input validation and sanitization.

**Key Methods**:

- `validateEmail($email)`: Email validation
- `validatePassword($password)`: Password strength checking
- `validateFileUpload($file)`: File upload validation
- `sanitizeString($input)`: XSS protection
- `validateCSRFToken($token)`: CSRF protection

**Features**:

- Multiple validation types
- CSRF token generation/validation
- File upload security
- XSS protection
- Custom validation rules

### 5. Utility Functions (includes/functions.php)

**Purpose**: Common functionality used throughout the application.

**Categories**:

- **Session Management**: Login status, role checking
- **Flash Messages**: User notifications
- **Formatting**: Currency, dates, file sizes
- **Security**: CSRF tokens, input sanitization
- **Pagination**: Dynamic pagination generation
- **Logging**: Error and info logging

## Security Features

### 1. Authentication & Authorization

- **Password Hashing**: PHP's password_hash() with bcrypt
- **Session Security**: Secure session configuration
- **Role-Based Access**: User, admin, moderator roles
- **Account Lockout**: Protection against brute force attacks

### 2. Input Validation & Sanitization

- **Server-Side Validation**: All inputs validated
- **XSS Protection**: htmlspecialchars() for output
- **SQL Injection Prevention**: PDO prepared statements
- **File Upload Security**: Type and size validation

### 3. CSRF Protection

- **Token Generation**: Unique tokens per session
- **Token Validation**: All state-changing operations protected
- **Automatic Integration**: Helper functions for forms

### 4. Security Headers

- **X-Content-Type-Options**: nosniff
- **X-Frame-Options**: DENY
- **X-XSS-Protection**: 1; mode=block
- **Referrer-Policy**: strict-origin-when-cross-origin

### 5. Error Handling

- **Production Mode**: Generic error messages
- **Development Mode**: Detailed error information
- **Logging**: All errors logged for analysis
- **Graceful Degradation**: User-friendly error pages

## API Endpoints (Future Enhancement)

### Authentication

- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `POST /api/auth/refresh` - Token refresh

### Products

- `GET /api/products` - List products
- `GET /api/products/{id}` - Get product details
- `POST /api/products` - Create product (admin)
- `PUT /api/products/{id}` - Update product (admin)
- `DELETE /api/products/{id}` - Delete product (admin)

### Cart

- `GET /api/cart` - Get cart contents
- `POST /api/cart/add` - Add item to cart
- `PUT /api/cart/update` - Update cart item
- `DELETE /api/cart/remove` - Remove cart item

## User Guide

### For End Users

#### Registration Process

1. Navigate to registration page
2. Fill in required information
3. Create strong password
4. Verify email (if enabled)
5. Complete profile setup

#### Shopping Experience

1. Browse products by category
2. Use search functionality
3. View product details
4. Add items to cart
5. Proceed to checkout
6. Complete payment
7. Track order status

#### Account Management

1. Update profile information
2. Change password
3. View order history
4. Manage addresses
5. Update preferences

### For Administrators

#### User Management

1. View all users
2. Edit user information
3. Change user roles
4. Activate/deactivate accounts
5. Monitor user activity

#### Product Management

1. Add new products
2. Update product information
3. Manage inventory
4. Set pricing
5. Handle product images
6. Organize categories

#### Order Management

1. View all orders
2. Update order status
3. Process payments
4. Handle refunds
5. Generate reports

## Development Guide

### Code Standards

- **PSR-4**: Autoloading standard
- **PSR-12**: Coding style
- **PHPDoc**: Documentation comments
- **Naming Conventions**: CamelCase for classes, snake_case for functions

### Adding New Features

#### 1. Creating a New Model

```php
<?php
class NewModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Model methods here
}
?>
```

#### 2. Creating a New View

```php
<?php
// views/new-feature.php
$pageTitle = 'New Feature';
?>
<div class="container">
    <!-- Your HTML content -->
</div>
```

#### 3. Adding Routes

Update `index.php` to include new pages in the `$availablePages` array.

### Database Migrations

#### Creating Migrations

1. Create new file in `database/migrations/`
2. Name with timestamp: `YYYY_MM_DD_HHMMSS_description.sql`
3. Include both UP and DOWN operations

#### Example Migration

```sql
-- Migration: 2024_01_01_120000_add_wishlist_table.sql

-- UP
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, product_id)
);

-- DOWN
DROP TABLE IF EXISTS wishlist;
```

### Testing

#### Unit Testing Setup (Future)

```bash
# Install PHPUnit
composer require --dev phpunit/phpunit

# Create test directory
mkdir tests

# Run tests
./vendor/bin/phpunit tests
```

#### Test Example

```php
<?php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
    public function testUserRegistration() {
        $user = new User();
        $result = $user->register([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Test123!',
            'firstName' => 'Test',
            'lastName' => 'User'
        ]);

        $this->assertTrue($result['success']);
    }
}
?>
```

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in .env
- [ ] Set `APP_DEBUG=false` in .env
- [ ] Generate secure `SECRET_KEY`
- [ ] Configure database with production credentials
- [ ] Set up SSL certificate
- [ ] Configure file permissions
- [ ] Set up error logging
- [ ] Configure backup strategy
- [ ] Set up monitoring

### Server Configuration

#### Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName yourapp.com
    DocumentRoot /path/to/your/app

    <Directory /path/to/your/app>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/yourapp_error.log
    CustomLog ${APACHE_LOG_DIR}/yourapp_access.log combined
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourapp.com;
    root /path/to/your/app;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Performance Optimization

#### Database Optimization

- Use appropriate indexes
- Optimize queries
- Implement query caching
- Regular database maintenance

#### Application Optimization

- Enable OPcache
- Implement application caching
- Optimize images
- Minify CSS/JavaScript
- Use CDN for static assets

## Troubleshooting

### Common Issues

#### Database Connection Errors

**Symptoms**: "Database connection failed" error
**Solutions**:

1. Check database credentials in .env
2. Verify MySQL service is running
3. Check database exists
4. Verify user permissions

#### Permission Errors

**Symptoms**: Cannot write to logs or uploads directory
**Solutions**:

1. Check file permissions (755 for directories, 644 for files)
2. Verify web server user ownership
3. Check PHP file upload settings

#### Session Issues

**Symptoms**: Users can't stay logged in
**Solutions**:

1. Check session directory permissions
2. Verify session configuration in php.ini
3. Check for session conflicts

#### Performance Issues

**Symptoms**: Slow page loading
**Solutions**:

1. Enable PHP OPcache
2. Optimize database queries
3. Add missing indexes
4. Enable compression

### Debug Mode

Enable debug mode for development:

```env
APP_DEBUG=true
APP_ENV=development
```

This provides:

- Detailed error messages
- Query logging
- Performance metrics
- Stack traces

### Logging

Check application logs:

- `logs/error.log` - PHP errors
- `logs/database.log` - Database operations
- `logs/app.log` - Application events

### Support

For technical support:

1. Check this documentation
2. Review error logs
3. Search existing issues
4. Create detailed bug report

---

## Conclusion

This professional PHP and MySQL application provides a solid foundation for e-commerce or business applications. The modular architecture, security features, and comprehensive documentation make it suitable for both learning and production use.

The application demonstrates modern PHP development practices while maintaining simplicity and readability. Regular updates and security patches are recommended for production deployments.

For questions or contributions, please refer to the project repository and follow the established coding standards.
