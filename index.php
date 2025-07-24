<?php
/**
 * Application Entry Point
 * 
 * This is the main entry point for the application. It handles routing,
 * initializes the application, and displays the appropriate content.
 * 
 * Features:
 * - Simple routing system
 * - Error handling
 * - Security headers
 * - Performance monitoring
 */

// Start output buffering
ob_start();

// Include configuration and dependencies
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Performance monitoring start
if (APP_DEBUG) {
    $startTime = microtime(true);
    $startMemory = memory_get_usage();
}

try {
    // Get the requested page
    $page = $_GET['page'] ?? 'home';
    $page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page); // Sanitize page parameter
    
    // Define available pages
    $availablePages = [
        'home' => 'views/home.php',
        'login' => 'views/auth/login.php',
        'register' => 'views/auth/register.php',
        'logout' => 'views/auth/logout.php',
        'profile' => 'views/user/profile.php',
        'products' => 'views/product/list.php',
        'product' => 'views/product/detail.php',
        'cart' => 'views/cart/index.php',
        'checkout' => 'views/checkout/index.php',
        'admin' => 'views/admin/dashboard.php',
        'about' => 'views/pages/about.php',
        'contact' => 'views/pages/contact.php',
        '404' => 'views/errors/404.php'
    ];
    
    // Check if page exists
    if (!isset($availablePages[$page]) || !file_exists($availablePages[$page])) {
        $page = '404';
        http_response_code(404);
    }
    
    // Check authentication requirements
    $protectedPages = ['profile', 'admin', 'checkout'];
    if (in_array($page, $protectedPages) && !User::isLoggedIn()) {
        setFlashMessage(MSG_WARNING, 'Please log in to access this page.');
        redirect('index.php?page=login');
    }
    
    // Check admin access
    if ($page === 'admin' && !User::hasRole(ROLE_ADMIN)) {
        setFlashMessage(MSG_ERROR, 'Access denied. Admin privileges required.');
        redirect('index.php');
    }
    
    // Set page title based on the current page
    $pageTitle = getPageTitle($page);
    
    // Include the requested page
    $pageContent = $availablePages[$page];
    
    // Test database connection
    $db = Database::getInstance();
    if (!$db->getConnection()) {
        throw new Exception('Database connection failed');
    }
    
} catch (Exception $e) {
    // Log the error
    logError('Application error: ' . $e->getMessage());
    
    // Show error page in production, detailed error in development
    if (APP_DEBUG) {
        $errorMessage = $e->getMessage();
        $pageContent = 'views/errors/debug.php';
    } else {
        $pageContent = 'views/errors/500.php';
        http_response_code(500);
    }
}

/**
 * Get page title based on page name
 * 
 * @param string $page Page name
 * @return string Page title
 */
function getPageTitle($page)
{
    $titles = [
        'home' => 'Home',
        'login' => 'Login',
        'register' => 'Register',
        'profile' => 'My Profile',
        'products' => 'Products',
        'product' => 'Product Details',
        'cart' => 'Shopping Cart',
        'checkout' => 'Checkout',
        'admin' => 'Admin Dashboard',
        'about' => 'About Us',
        'contact' => 'Contact Us',
        '404' => 'Page Not Found'
    ];
    
    return $titles[$page] ?? 'Page';
}

// Get current user if logged in
$currentUser = null;
if (User::isLoggedIn()) {
    $user = new User();
    $currentUser = $user->getById(User::getCurrentUserId());
}

// Set global variables for templates
$siteName = APP_NAME;
$baseUrl = getBaseUrl();
$currentPage = $page;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitizeOutput($pageTitle . ' - ' . $siteName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="public/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="public/images/favicon.ico">
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="Professional PHP and MySQL web application">
    <meta name="keywords" content="php, mysql, ecommerce, web application">
    <meta name="author" content="Professional PHP App">
    
    <!-- Open Graph meta tags -->
    <meta property="og:title" content="<?php echo sanitizeOutput($pageTitle . ' - ' . $siteName); ?>">
    <meta property="og:description" content="Professional PHP and MySQL web application">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo getCurrentUrl(); ?>">
    
    <!-- Additional head content -->
    <?php if (isset($additionalHead)): ?>
        <?php echo $additionalHead; ?>
    <?php endif; ?>
</head>
<body class="<?php echo 'page-' . $currentPage; ?>">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Flash Messages -->
        <div class="container">
            <?php echo displayFlashMessages(); ?>
        </div>
        
        <!-- Page Content -->
        <?php
        if (file_exists($pageContent)) {
            include $pageContent;
        } else {
            echo '<div class="container"><div class="alert alert-danger">Page not found</div></div>';
        }
        ?>
    </main>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="public/js/app.js"></script>
    
    <!-- Additional scripts -->
    <?php if (isset($additionalScripts)): ?>
        <?php echo $additionalScripts; ?>
    <?php endif; ?>
    
    <?php if (APP_DEBUG): ?>
        <!-- Performance Information -->
        <div class="debug-info" style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; z-index: 9999;">
            <div>Execution Time: <?php echo number_format((microtime(true) - $startTime) * 1000, 2); ?>ms</div>
            <div>Memory Usage: <?php echo formatFileSize(memory_get_usage() - $startMemory); ?></div>
            <div>Peak Memory: <?php echo formatFileSize(memory_get_peak_usage()); ?></div>
        </div>
    <?php endif; ?>
    
    <?php
    // Performance monitoring end
    if (APP_DEBUG) {
        performanceCheckpoint('Page Load Complete');
    }
    ?>
</body>
</html>
<?php
// End output buffering and send to browser
ob_end_flush();
?>
