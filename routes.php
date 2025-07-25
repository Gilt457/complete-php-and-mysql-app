<?php
/**
 * Routes Configuration
 * 
 * This file defines all the routes for the Alibaba Clone application.
 * Routes are organized by functionality and follow RESTful conventions.
 */

// Include required files
require_once 'Router.php';
require_once 'controllers/BaseController.php';

// Create router instance
$router = new Router();

// ======================
// PUBLIC ROUTES
// ======================

// Home and static pages
$router->get('/', 'HomeController@index');
$router->get('/about', 'PageController@about');
$router->get('/contact', 'PageController@contact');
$router->post('/contact', 'PageController@contactSubmit');

// ======================
// AUTHENTICATION ROUTES
// ======================

// Login
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');

// Registration
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@register');

// Email verification
$router->get('/verify-email', 'AuthController@verifyEmail');
$router->post('/resend-verification', 'AuthController@resendVerification');

// Password reset
$router->get('/forgot-password', 'AuthController@forgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password', 'AuthController@resetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// Logout
$router->get('/logout', 'AuthController@logout');
$router->post('/logout', 'AuthController@logout');

// ======================
// PRODUCT ROUTES
// ======================

// Product listing and search
$router->get('/products', 'ProductController@index');
$router->get('/products/search', 'ProductController@search');
$router->get('/category/{id}', 'ProductController@category');

// Product details
$router->get('/product/{id}', 'ProductController@show');

// ======================
// USER DASHBOARD ROUTES
// ======================

// Dashboard and profile (requires authentication)
$router->get('/dashboard', 'UserController@dashboard', ['auth']);
$router->get('/profile', 'UserController@profile', ['auth']);
$router->post('/profile', 'UserController@profile', ['auth']);

// Orders
$router->get('/orders', 'UserController@orders', ['auth']);
$router->get('/orders/{id}', 'UserController@orderDetails', ['auth']);

// Wishlist
$router->get('/wishlist', 'UserController@wishlist', ['auth']);
$router->post('/wishlist/add', 'UserController@addToWishlist', ['auth']);
$router->post('/wishlist/remove', 'UserController@removeFromWishlist', ['auth']);

// Addresses
$router->get('/addresses', 'UserController@addresses', ['auth']);
$router->get('/addresses/add', 'UserController@addAddress', ['auth']);
$router->post('/addresses/add', 'UserController@addAddress', ['auth']);
$router->get('/addresses/{id}/edit', 'UserController@editAddress', ['auth']);
$router->post('/addresses/{id}/edit', 'UserController@editAddress', ['auth']);
$router->delete('/addresses/{id}', 'UserController@deleteAddress', ['auth']);

// Password change
$router->get('/change-password', 'UserController@changePassword', ['auth']);
$router->post('/change-password', 'UserController@changePassword', ['auth']);

// ======================
// SHOPPING CART ROUTES
// ======================

$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');
$router->post('/cart/clear', 'CartController@clear');

// ======================
// CHECKOUT ROUTES
// ======================

$router->get('/checkout', 'CheckoutController@index', ['auth']);
$router->post('/checkout', 'CheckoutController@process', ['auth']);
$router->get('/checkout/success', 'CheckoutController@success', ['auth']);
$router->get('/checkout/cancel', 'CheckoutController@cancel', ['auth']);

// ======================
// ADMIN ROUTES
// ======================

// Admin dashboard (requires admin role)
$router->get('/admin', 'AdminController@dashboard', ['auth', 'admin']);
$router->get('/admin/dashboard', 'AdminController@dashboard', ['auth', 'admin']);

// Admin product management
$router->get('/admin/products', 'ProductController@adminIndex', ['auth', 'admin']);
$router->get('/admin/products/create', 'ProductController@adminCreate', ['auth', 'admin']);
$router->post('/admin/products/create', 'ProductController@adminCreate', ['auth', 'admin']);
$router->get('/admin/products/{id}/edit', 'ProductController@adminEdit', ['auth', 'admin']);
$router->post('/admin/products/{id}/edit', 'ProductController@adminEdit', ['auth', 'admin']);
$router->delete('/admin/products/{id}', 'ProductController@adminDelete', ['auth', 'admin']);

// Admin user management
$router->get('/admin/users', 'AdminController@users', ['auth', 'admin']);
$router->get('/admin/users/{id}', 'AdminController@userDetails', ['auth', 'admin']);
$router->post('/admin/users/{id}/status', 'AdminController@updateUserStatus', ['auth', 'admin']);

// Admin order management
$router->get('/admin/orders', 'AdminController@orders', ['auth', 'admin']);
$router->get('/admin/orders/{id}', 'AdminController@orderDetails', ['auth', 'admin']);
$router->post('/admin/orders/{id}/status', 'AdminController@updateOrderStatus', ['auth', 'admin']);

// Admin categories
$router->get('/admin/categories', 'AdminController@categories', ['auth', 'admin']);
$router->post('/admin/categories', 'AdminController@createCategory', ['auth', 'admin']);
$router->get('/admin/categories/{id}/edit', 'AdminController@editCategory', ['auth', 'admin']);
$router->post('/admin/categories/{id}/edit', 'AdminController@editCategory', ['auth', 'admin']);
$router->delete('/admin/categories/{id}', 'AdminController@deleteCategory', ['auth', 'admin']);

// Admin reports
$router->get('/admin/reports', 'AdminController@reports', ['auth', 'admin']);
$router->get('/admin/reports/sales', 'AdminController@salesReport', ['auth', 'admin']);
$router->get('/admin/reports/users', 'AdminController@usersReport', ['auth', 'admin']);

// ======================
// API ROUTES
// ======================

// API routes for AJAX requests
$router->get('/api/products/autocomplete', 'ApiController@productAutocomplete');
$router->get('/api/categories', 'ApiController@categories');
$router->post('/api/reviews', 'ApiController@submitReview', ['auth']);

// ======================
// MIDDLEWARE DEFINITIONS
// ======================

// Authentication middleware
$router->middleware('auth', 'AuthMiddleware');

// Admin middleware
$router->middleware('admin', 'AdminMiddleware');

// CSRF middleware
$router->middleware('csrf', 'CsrfMiddleware');

// Rate limiting middleware
$router->middleware('throttle', 'ThrottleMiddleware');

// Return router instance
return $router;
