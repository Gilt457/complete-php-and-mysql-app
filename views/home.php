<?php
/**
 * Home Page View
 * 
 * This is the main landing page for the application.
 * It displays featured products, categories, and promotional content.
 * 
 * Features:
 * - Hero section with call-to-action
 * - Featured products showcase
 * - Product categories
 * - Latest blog posts
 * - Customer testimonials
 * - Newsletter subscription
 */

require_once 'classes/Product.php';

// Initialize product model
$productModel = new Product();

// Get featured products
$featuredProducts = $productModel->getFeatured(6);

// Sample categories (in a real app, fetch from database)
$categories = [
    [
        'id' => 1,
        'name' => 'Electronics',
        'slug' => 'electronics',
        'image' => 'public/images/categories/electronics.jpg',
        'description' => 'Latest gadgets and electronic devices'
    ],
    [
        'id' => 2,
        'name' => 'Clothing',
        'slug' => 'clothing',
        'image' => 'public/images/categories/clothing.jpg',
        'description' => 'Fashion and apparel for all occasions'
    ],
    [
        'id' => 3,
        'name' => 'Books',
        'slug' => 'books',
        'image' => 'public/images/categories/books.jpg',
        'description' => 'Educational and entertainment books'
    ],
    [
        'id' => 4,
        'name' => 'Home & Garden',
        'slug' => 'home-garden',
        'image' => 'public/images/categories/home.jpg',
        'description' => 'Everything for your home and garden'
    ]
];

// Set page-specific variables
$pageTitle = 'Home - Welcome to ' . APP_NAME;
$additionalHead = '
    <meta name="description" content="Professional PHP and MySQL e-commerce application with modern features and secure design">
    <meta name="keywords" content="ecommerce, php, mysql, shopping, products">
';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="hero-title fade-in">
                    Welcome to <?php echo sanitizeOutput(APP_NAME); ?>
                </h1>
                <p class="hero-subtitle fade-in">
                    Discover amazing products with our professional PHP and MySQL e-commerce platform. 
                    Built with security, performance, and user experience in mind.
                </p>
                <div class="hero-buttons fade-in">
                    <a href="index.php?page=products" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-shopping-bag me-2"></i>
                        Shop Now
                    </a>
                    <a href="index.php?page=about" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>
                        Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="public/images/hero-image.svg" alt="E-commerce Platform" class="img-fluid" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="feature-box">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Secure & Safe</h5>
                    <p class="text-muted">Built with security best practices and encryption</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-box">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                    </div>
                    <h5>Fast Delivery</h5>
                    <p class="text-muted">Quick and reliable shipping to your doorstep</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-box">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-primary"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">Round-the-clock customer support</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="feature-box">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-undo fa-3x text-primary"></i>
                    </div>
                    <h5>Easy Returns</h5>
                    <p class="text-muted">Hassle-free return and refund policy</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Shop by Category</h2>
                <p class="text-muted">Explore our wide range of product categories</p>
            </div>
        </div>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card category-card h-100 hover-lift">
                        <div class="card-img-top-wrapper" style="height: 200px; overflow: hidden;">
                            <img src="<?php echo sanitizeOutput($category['image']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo sanitizeOutput($category['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo sanitizeOutput($category['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo sanitizeOutput($category['description']); ?></p>
                            <a href="index.php?page=products&category=<?php echo urlencode($category['slug']); ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>
                                Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Featured Products</h2>
                <p class="text-muted">Check out our handpicked featured products</p>
            </div>
        </div>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card product-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo $product['image'] ? 'public/images/products/' . sanitizeOutput($product['image']) : 'public/images/placeholder.jpg'; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo sanitizeOutput($product['name']); ?>">
                            <?php if ($product['featured']): ?>
                                <span class="badge bg-primary position-absolute top-0 start-0 m-2">Featured</span>
                            <?php endif; ?>
                            <div class="product-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0">
                                <a href="index.php?page=product&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-light btn-sm me-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-primary btn-sm add-to-cart" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="index.php?page=product&id=<?php echo $product['id']; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo sanitizeOutput($product['name']); ?>
                                </a>
                            </h6>
                            <p class="card-text text-muted text-truncate-2">
                                <?php echo sanitizeOutput($product['description']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="product-price text-primary fw-bold">
                                        <?php echo formatCurrency($product['price']); ?>
                                    </span>
                                    <?php if (isset($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                                        <small class="product-original-price ms-2">
                                            <?php echo formatCurrency($product['compare_price']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="rating">
                                    <?php
                                    $rating = $product['average_rating'] ?? 0;
                                    for ($i = 1; $i <= 5; $i++):
                                        $starClass = $i <= $rating ? 'fas' : 'far';
                                    ?>
                                        <i class="<?php echo $starClass; ?> fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <a href="index.php?page=products" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-th-large me-2"></i>
                    View All Products
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Statistics Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold">500+</h3>
                    <p class="mb-0">Products</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold">1000+</h3>
                    <p class="mb-0">Happy Customers</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold">24/7</h3>
                    <p class="mb-0">Customer Support</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold">99%</h3>
                    <p class="mb-0">Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="text-muted">Don't just take our word for it</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body text-center">
                        <div class="testimonial-rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">
                            "Amazing platform with great products and excellent customer service. 
                            The checkout process is smooth and secure."
                        </p>
                        <div class="testimonial-author">
                            <img src="public/images/avatars/user1.jpg" alt="John Doe" class="rounded-circle mb-2" width="60" height="60">
                            <h6 class="mb-0">John Doe</h6>
                            <small class="text-muted">Verified Customer</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body text-center">
                        <div class="testimonial-rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">
                            "Fast delivery and high-quality products. I've been shopping here for months 
                            and never had any issues."
                        </p>
                        <div class="testimonial-author">
                            <img src="public/images/avatars/user2.jpg" alt="Jane Smith" class="rounded-circle mb-2" width="60" height="60">
                            <h6 class="mb-0">Jane Smith</h6>
                            <small class="text-muted">Verified Customer</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body text-center">
                        <div class="testimonial-rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">
                            "Professional website with modern design. Easy to navigate and find what 
                            I'm looking for. Highly recommended!"
                        </p>
                        <div class="testimonial-author">
                            <img src="public/images/avatars/user3.jpg" alt="Mike Johnson" class="rounded-circle mb-2" width="60" height="60">
                            <h6 class="mb-0">Mike Johnson</h6>
                            <small class="text-muted">Verified Customer</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-3">Stay Updated</h2>
                <p class="text-muted mb-4">
                    Subscribe to our newsletter and be the first to know about new products, 
                    special offers, and exclusive deals.
                </p>
                <form class="newsletter-form row g-3 justify-content-center" id="homeNewsletterForm">
                    <div class="col-auto">
                        <input type="email" class="form-control form-control-lg" name="email" 
                               placeholder="Enter your email address" required style="min-width: 300px;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>
                            Subscribe
                        </button>
                    </div>
                </form>
                <small class="text-muted">
                    We respect your privacy. Unsubscribe at any time.
                </small>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5rem 0;
}

.feature-box {
    padding: 2rem 1rem;
    transition: transform 0.3s ease;
}

.feature-box:hover {
    transform: translateY(-5px);
}

.category-card:hover .card-img-top {
    transform: scale(1.05);
}

.product-card:hover .product-overlay {
    opacity: 1 !important;
    background: rgba(0, 0, 0, 0.7);
    transition: opacity 0.3s ease;
}

.testimonial-card {
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stat-item {
    padding: 1rem;
}

.section-title {
    position: relative;
    display: inline-block;
    margin-bottom: 1rem;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: var(--primary-color);
    border-radius: 2px;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-buttons .btn {
        display: block;
        margin-bottom: 1rem;
        width: 100%;
    }
    
    .newsletter-form .col-auto {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .newsletter-form input {
        min-width: 100% !important;
        margin-bottom: 1rem;
    }
}
</style>

<script>
// Home page specific JavaScript
$(document).ready(function() {
    // Newsletter form submission
    $('#homeNewsletterForm').on('submit', function(e) {
        e.preventDefault();
        
        const email = $(this).find('input[name="email"]').val();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();
        
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Subscribing...').prop('disabled', true);
        
        // Simulate newsletter subscription
        setTimeout(function() {
            $btn.html('<i class="fas fa-check me-2"></i>Subscribed!').removeClass('btn-primary').addClass('btn-success');
            
            setTimeout(function() {
                $btn.html(originalText).removeClass('btn-success').addClass('btn-primary').prop('disabled', false);
                $('#homeNewsletterForm')[0].reset();
            }, 2000);
        }, 1000);
    });
    
    // Animate statistics on scroll
    const animateStats = function() {
        $('.stat-item h3').each(function() {
            const $this = $(this);
            const target = parseInt($this.text().replace(/\D/g, ''));
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(function() {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                $this.text(Math.floor(current) + ($this.text().includes('+') ? '+' : '') + ($this.text().includes('%') ? '%' : ''));
            }, 20);
        });
    };
    
    // Trigger animation when stats section comes into view
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                animateStats();
                observer.unobserve(entry.target);
            }
        });
    });
    
    const statsSection = document.querySelector('.py-5.bg-primary');
    if (statsSection) {
        observer.observe(statsSection);
    }
});
</script>
