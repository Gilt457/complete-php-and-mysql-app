-- Professional PHP MySQL Application Database Schema
-- This file contains the complete database structure for the application
-- 
-- Features:
-- - User management with roles and permissions
-- - Product catalog with categories
-- - Order management system
-- - Inventory tracking
-- - Audit trails
-- - Optimized indexes for performance

-- Create database
CREATE DATABASE IF NOT EXISTS php_mysql_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_mysql_app;

-- ================================
-- User Management Tables
-- ================================

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
    status ENUM('active', 'inactive', 'pending', 'banned') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    last_login DATETIME,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_role (role),
    INDEX idx_created_at (created_at)
);

-- User profiles table (extended user information)
CREATE TABLE user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    avatar VARCHAR(255),
    bio TEXT,
    website VARCHAR(255),
    location VARCHAR(255),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    timezone VARCHAR(50) DEFAULT 'UTC',
    language VARCHAR(10) DEFAULT 'en',
    notifications_enabled BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- User addresses table
CREATE TABLE user_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('billing', 'shipping', 'both') DEFAULT 'both',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    company VARCHAR(255),
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type)
);

-- ================================
-- Product Management Tables
-- ================================

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT,
    image VARCHAR(255),
    meta_title VARCHAR(255),
    meta_description TEXT,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_parent_id (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_sort_order (sort_order)
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    sku VARCHAR(100) UNIQUE NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2),
    cost_price DECIMAL(10, 2),
    category_id INT,
    brand VARCHAR(255),
    weight DECIMAL(8, 2),
    dimensions VARCHAR(100),
    image VARCHAR(255),
    gallery JSON,
    stock_quantity INT DEFAULT 0,
    stock_status ENUM('in_stock', 'out_of_stock', 'on_backorder') DEFAULT 'in_stock',
    manage_stock BOOLEAN DEFAULT TRUE,
    allow_backorders BOOLEAN DEFAULT FALSE,
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    meta_title VARCHAR(255),
    meta_description TEXT,
    tags JSON,
    attributes JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category_id (category_id),
    INDEX idx_sku (sku),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_price (price),
    INDEX idx_stock_status (stock_status),
    INDEX idx_created_at (created_at),
    FULLTEXT idx_search (name, description, sku)
);

-- Product images table
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_sort_order (sort_order)
);

-- Product reviews table
CREATE TABLE product_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT,
    name VARCHAR(255),
    email VARCHAR(255),
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    review TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating)
);

-- ================================
-- Order Management Tables
-- ================================

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    currency VARCHAR(3) DEFAULT 'USD',
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    shipping_amount DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(255),
    
    -- Billing address
    billing_first_name VARCHAR(100),
    billing_last_name VARCHAR(100),
    billing_company VARCHAR(255),
    billing_address_line_1 VARCHAR(255),
    billing_address_line_2 VARCHAR(255),
    billing_city VARCHAR(100),
    billing_state VARCHAR(100),
    billing_postal_code VARCHAR(20),
    billing_country VARCHAR(100),
    
    -- Shipping address
    shipping_first_name VARCHAR(100),
    shipping_last_name VARCHAR(100),
    shipping_company VARCHAR(255),
    shipping_address_line_1 VARCHAR(255),
    shipping_address_line_2 VARCHAR(255),
    shipping_city VARCHAR(100),
    shipping_state VARCHAR(100),
    shipping_postal_code VARCHAR(20),
    shipping_country VARCHAR(100),
    
    notes TEXT,
    shipped_at DATETIME,
    delivered_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100),
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- ================================
-- Shopping Cart Tables
-- ================================

-- Shopping cart table
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_product_id (product_id),
    UNIQUE KEY unique_cart_item (user_id, product_id, session_id)
);

-- ================================
-- Content Management Tables
-- ================================

-- Pages table
CREATE TABLE pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    meta_title VARCHAR(255),
    meta_description TEXT,
    status ENUM('published', 'draft', 'private') DEFAULT 'draft',
    template VARCHAR(100),
    featured_image VARCHAR(255),
    author_id INT,
    published_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_author_id (author_id),
    INDEX idx_published_at (published_at)
);

-- Blog posts table
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    featured_image VARCHAR(255),
    meta_title VARCHAR(255),
    meta_description TEXT,
    status ENUM('published', 'draft', 'private') DEFAULT 'draft',
    author_id INT,
    category_id INT,
    tags JSON,
    view_count INT DEFAULT 0,
    published_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_author_id (author_id),
    INDEX idx_published_at (published_at),
    FULLTEXT idx_search (title, content)
);

-- ================================
-- System Tables
-- ================================

-- Settings table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    group_name VARCHAR(100),
    is_public BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_group_name (group_name)
);

-- Activity log table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    subject_type VARCHAR(100),
    subject_id INT,
    properties JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_created_at (created_at)
);

-- Sessions table (for database session storage)
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
);

-- ================================
-- Initial Data
-- ================================

-- Insert default admin user
INSERT INTO users (username, email, password, first_name, last_name, role, status, email_verified) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'active', TRUE);

-- Insert default categories
INSERT INTO categories (name, slug, description, status) VALUES
('Electronics', 'electronics', 'Electronic devices and gadgets', 'active'),
('Clothing', 'clothing', 'Clothing and fashion items', 'active'),
('Books', 'books', 'Books and educational materials', 'active'),
('Home & Garden', 'home-garden', 'Home and garden products', 'active'),
('Sports', 'sports', 'Sports and outdoor equipment', 'active');

-- Insert sample products
INSERT INTO products (name, slug, description, sku, price, category_id, stock_quantity, status, featured) VALUES
('Laptop Computer', 'laptop-computer', 'High-performance laptop computer for work and gaming', 'LAPTOP-001', 999.99, 1, 10, 'active', TRUE),
('Smartphone', 'smartphone', 'Latest smartphone with advanced features', 'PHONE-001', 599.99, 1, 25, 'active', TRUE),
('Cotton T-Shirt', 'cotton-t-shirt', 'Comfortable cotton t-shirt available in multiple colors', 'SHIRT-001', 29.99, 2, 100, 'active', FALSE),
('Programming Book', 'programming-book', 'Comprehensive guide to modern programming', 'BOOK-001', 49.99, 3, 50, 'active', FALSE);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description, group_name, is_public) VALUES
('site_name', 'Professional PHP MySQL App', 'string', 'Site name', 'general', TRUE),
('site_description', 'A professional PHP and MySQL web application', 'string', 'Site description', 'general', TRUE),
('admin_email', 'admin@example.com', 'string', 'Administrator email', 'general', FALSE),
('currency', 'USD', 'string', 'Default currency', 'ecommerce', TRUE),
('items_per_page', '10', 'number', 'Default items per page', 'general', TRUE),
('allow_registration', 'true', 'boolean', 'Allow user registration', 'users', TRUE),
('email_verification', 'false', 'boolean', 'Require email verification', 'users', FALSE);

-- ================================
-- Triggers and Procedures
-- ================================

-- Trigger to update product stock status
DELIMITER ;;
CREATE TRIGGER update_stock_status BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
    IF NEW.manage_stock = TRUE THEN
        IF NEW.stock_quantity <= 0 THEN
            IF NEW.allow_backorders = TRUE THEN
                SET NEW.stock_status = 'on_backorder';
            ELSE
                SET NEW.stock_status = 'out_of_stock';
            END IF;
        ELSE
            SET NEW.stock_status = 'in_stock';
        END IF;
    END IF;
END;;
DELIMITER ;

-- Trigger to generate order number
DELIMITER ;;
CREATE TRIGGER generate_order_number BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
    IF NEW.order_number IS NULL OR NEW.order_number = '' THEN
        SET NEW.order_number = CONCAT('ORD-', YEAR(NOW()), MONTH(NOW()), '-', LPAD(LAST_INSERT_ID() + 1, 6, '0'));
    END IF;
END;;
DELIMITER ;

-- View for order summary
CREATE VIEW order_summary AS
SELECT 
    o.id,
    o.order_number,
    o.user_id,
    u.username,
    u.email,
    o.status,
    o.total_amount,
    o.payment_status,
    o.created_at,
    COUNT(oi.id) as item_count
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- View for product statistics
CREATE VIEW product_stats AS
SELECT 
    p.id,
    p.name,
    p.sku,
    p.price,
    p.stock_quantity,
    c.name as category_name,
    COALESCE(AVG(pr.rating), 0) as average_rating,
    COUNT(pr.id) as review_count,
    COALESCE(SUM(oi.quantity), 0) as total_sold
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id;

-- ================================
-- Indexes for Performance
-- ================================

-- Additional indexes for better performance
CREATE INDEX idx_users_email_status ON users(email, status);
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_activity_logs_user_created ON activity_logs(user_id, created_at);

-- ================================
-- Comments and Documentation
-- ================================

-- Add table comments for documentation
ALTER TABLE users COMMENT = 'User accounts with authentication and role management';
ALTER TABLE products COMMENT = 'Product catalog with inventory management';
ALTER TABLE orders COMMENT = 'Customer orders with payment and shipping tracking';
ALTER TABLE categories COMMENT = 'Product categories with hierarchical structure';
ALTER TABLE cart_items COMMENT = 'Shopping cart items for both guests and registered users';
ALTER TABLE activity_logs COMMENT = 'System activity and audit trail';
ALTER TABLE settings COMMENT = 'Application configuration settings';
