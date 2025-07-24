<?php
/**
 * Navigation Bar Component
 * 
 * This file contains the main navigation bar for the application.
 * It adapts based on user authentication status and role.
 * 
 * Features:
 * - Responsive Bootstrap navbar
 * - User authentication status display
 * - Role-based menu items
 * - Search functionality
 * - Cart icon with item count
 */

// Get cart item count (placeholder - implement cart functionality)
$cartItemCount = 0;
if (User::isLoggedIn()) {
    // TODO: Implement cart item count
    // $cartItemCount = Cart::getItemCount(User::getCurrentUserId());
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-store me-2"></i>
            <?php echo sanitizeOutput($siteName); ?>
        </a>
        
        <!-- Mobile toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left menu -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home me-1"></i>
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'products' ? 'active' : ''; ?>" href="index.php?page=products">
                        <i class="fas fa-boxes me-1"></i>
                        Products
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-list me-1"></i>
                        Categories
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php?page=products&category=electronics">Electronics</a></li>
                        <li><a class="dropdown-item" href="index.php?page=products&category=clothing">Clothing</a></li>
                        <li><a class="dropdown-item" href="index.php?page=products&category=books">Books</a></li>
                        <li><a class="dropdown-item" href="index.php?page=products&category=home-garden">Home & Garden</a></li>
                        <li><a class="dropdown-item" href="index.php?page=products&category=sports">Sports</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="index.php?page=about">
                        <i class="fas fa-info-circle me-1"></i>
                        About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="index.php?page=contact">
                        <i class="fas fa-envelope me-1"></i>
                        Contact
                    </a>
                </li>
            </ul>
            
            <!-- Search form -->
            <form class="d-flex me-3" method="GET" action="index.php">
                <input type="hidden" name="page" value="products">
                <div class="input-group">
                    <input class="form-control form-control-sm" type="search" name="search" placeholder="Search products..." value="<?php echo sanitizeOutput($_GET['search'] ?? ''); ?>">
                    <button class="btn btn-outline-light btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Right menu -->
            <ul class="navbar-nav">
                <!-- Shopping cart -->
                <li class="nav-item">
                    <a class="nav-link position-relative <?php echo $currentPage === 'cart' ? 'active' : ''; ?>" href="index.php?page=cart">
                        <i class="fas fa-shopping-cart"></i>
                        Cart
                        <?php if ($cartItemCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cartItemCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <?php if (User::isLoggedIn()): ?>
                    <!-- User menu for logged in users -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo sanitizeOutput($currentUser['first_name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="index.php?page=profile">
                                    <i class="fas fa-user-circle me-2"></i>
                                    My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="index.php?page=orders">
                                    <i class="fas fa-shopping-bag me-2"></i>
                                    My Orders
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="index.php?page=wishlist">
                                    <i class="fas fa-heart me-2"></i>
                                    Wishlist
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (User::hasRole(ROLE_ADMIN)): ?>
                                <li>
                                    <a class="dropdown-item" href="index.php?page=admin">
                                        <i class="fas fa-cogs me-2"></i>
                                        Admin Panel
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item text-danger" href="index.php?page=logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Login/Register for guests -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'login' ? 'active' : ''; ?>" href="index.php?page=login">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'register' ? 'active' : ''; ?>" href="index.php?page=register">
                            <i class="fas fa-user-plus me-1"></i>
                            Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Breadcrumb (optional) -->
<?php if ($currentPage !== 'home'): ?>
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <?php
                // Generate breadcrumb based on current page
                $breadcrumbs = getBreadcrumbs($currentPage);
                foreach ($breadcrumbs as $index => $breadcrumb):
                    if ($index === count($breadcrumbs) - 1):
                ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo sanitizeOutput($breadcrumb['title']); ?>
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo sanitizeOutput($breadcrumb['url']); ?>" class="text-decoration-none">
                            <?php echo sanitizeOutput($breadcrumb['title']); ?>
                        </a>
                    </li>
                <?php
                    endif;
                endforeach;
                ?>
            </ol>
        </nav>
    </div>
</div>
<?php endif; ?>

<?php
/**
 * Generate breadcrumbs for the current page
 * 
 * @param string $page Current page
 * @return array Breadcrumb items
 */
function getBreadcrumbs($page)
{
    $breadcrumbs = [];
    
    switch ($page) {
        case 'products':
            $breadcrumbs[] = ['title' => 'Products', 'url' => 'index.php?page=products'];
            break;
        case 'product':
            $breadcrumbs[] = ['title' => 'Products', 'url' => 'index.php?page=products'];
            $breadcrumbs[] = ['title' => 'Product Details', 'url' => ''];
            break;
        case 'cart':
            $breadcrumbs[] = ['title' => 'Shopping Cart', 'url' => ''];
            break;
        case 'checkout':
            $breadcrumbs[] = ['title' => 'Shopping Cart', 'url' => 'index.php?page=cart'];
            $breadcrumbs[] = ['title' => 'Checkout', 'url' => ''];
            break;
        case 'profile':
            $breadcrumbs[] = ['title' => 'My Account', 'url' => ''];
            break;
        case 'admin':
            $breadcrumbs[] = ['title' => 'Admin Panel', 'url' => ''];
            break;
        case 'login':
            $breadcrumbs[] = ['title' => 'Login', 'url' => ''];
            break;
        case 'register':
            $breadcrumbs[] = ['title' => 'Register', 'url' => ''];
            break;
        case 'about':
            $breadcrumbs[] = ['title' => 'About Us', 'url' => ''];
            break;
        case 'contact':
            $breadcrumbs[] = ['title' => 'Contact Us', 'url' => ''];
            break;
        default:
            $breadcrumbs[] = ['title' => ucfirst($page), 'url' => ''];
    }
    
    return $breadcrumbs;
}
?>
