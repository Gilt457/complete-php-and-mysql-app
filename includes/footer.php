<?php
/**
 * Footer Component
 * 
 * This file contains the main footer for the application.
 * It includes links, company information, and contact details.
 * 
 * Features:
 * - Responsive Bootstrap footer
 * - Social media links
 * - Newsletter subscription
 * - Company information
 * - Legal links
 */
?>

<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Company Information -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-store me-2"></i>
                    <?php echo sanitizeOutput($siteName); ?>
                </h5>
                <p class="text-muted">
                    A professional PHP and MySQL web application built with modern practices, 
                    security features, and responsive design. Perfect for e-commerce and business applications.
                </p>
                <div class="social-links">
                    <a href="#" class="text-light me-3" title="Facebook">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="Twitter">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="Instagram">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="LinkedIn">
                        <i class="fab fa-linkedin-in fa-lg"></i>
                    </a>
                    <a href="#" class="text-light" title="YouTube">
                        <i class="fab fa-youtube fa-lg"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="index.php" class="text-muted text-decoration-none">
                            <i class="fas fa-home me-2"></i>Home
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=products" class="text-muted text-decoration-none">
                            <i class="fas fa-boxes me-2"></i>Products
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=about" class="text-muted text-decoration-none">
                            <i class="fas fa-info-circle me-2"></i>About Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=contact" class="text-muted text-decoration-none">
                            <i class="fas fa-envelope me-2"></i>Contact
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=faq" class="text-muted text-decoration-none">
                            <i class="fas fa-question-circle me-2"></i>FAQ
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Customer Service -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="mb-3">Customer Service</h6>
                <ul class="list-unstyled">
                    <?php if (User::isLoggedIn()): ?>
                        <li class="mb-2">
                            <a href="index.php?page=profile" class="text-muted text-decoration-none">
                                <i class="fas fa-user me-2"></i>My Account
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?page=orders" class="text-muted text-decoration-none">
                                <i class="fas fa-shopping-bag me-2"></i>My Orders
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="mb-2">
                            <a href="index.php?page=login" class="text-muted text-decoration-none">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?page=register" class="text-muted text-decoration-none">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="mb-2">
                        <a href="index.php?page=shipping" class="text-muted text-decoration-none">
                            <i class="fas fa-truck me-2"></i>Shipping Info
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=returns" class="text-muted text-decoration-none">
                            <i class="fas fa-undo me-2"></i>Returns
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=support" class="text-muted text-decoration-none">
                            <i class="fas fa-headset me-2"></i>Support
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Newsletter Subscription -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h6 class="mb-3">Stay Updated</h6>
                <p class="text-muted">Subscribe to our newsletter to receive the latest updates and offers.</p>
                <form class="newsletter-form" id="newsletterForm">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Enter your email" name="email" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Contact Information -->
                <div class="contact-info mt-4">
                    <h6 class="mb-3">Contact Information</h6>
                    <p class="text-muted mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        123 Business Street, City, State 12345
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-phone me-2"></i>
                        <a href="tel:+1234567890" class="text-muted text-decoration-none">+1 (234) 567-8900</a>
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:info@example.com" class="text-muted text-decoration-none">info@example.com</a>
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-clock me-2"></i>
                        Mon - Fri: 9:00 AM - 6:00 PM
                    </p>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <!-- Bottom Footer -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">
                    &copy; <?php echo date('Y'); ?> <?php echo sanitizeOutput($siteName); ?>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="index.php?page=privacy" class="text-muted text-decoration-none">Privacy Policy</a>
                    </li>
                    <li class="list-inline-item">
                        <span class="text-muted">|</span>
                    </li>
                    <li class="list-inline-item">
                        <a href="index.php?page=terms" class="text-muted text-decoration-none">Terms of Service</a>
                    </li>
                    <li class="list-inline-item">
                        <span class="text-muted">|</span>
                    </li>
                    <li class="list-inline-item">
                        <a href="index.php?page=sitemap" class="text-muted text-decoration-none">Sitemap</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Technology Stack Info (Development mode only) -->
        <?php if (APP_DEBUG): ?>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <small>
                            <strong>Technology Stack:</strong> PHP <?php echo PHP_VERSION; ?>, 
                            MySQL, Bootstrap 5, Font Awesome, jQuery
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</footer>

<!-- Back to Top Button -->
<button type="button" class="btn btn-primary btn-floating btn-lg" id="btn-back-to-top" style="position: fixed; bottom: 20px; right: 20px; display: none; z-index: 999;">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Newsletter subscription handling
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = this.email.value;
    const button = this.querySelector('button[type="submit"]');
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    // Simulate API call (replace with actual implementation)
    setTimeout(function() {
        // Reset button
        button.innerHTML = originalContent;
        button.disabled = false;
        
        // Show success message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show mt-2';
        alertDiv.innerHTML = `
            Thank you for subscribing! We'll keep you updated.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.getElementById('newsletterForm').appendChild(alertDiv);
        
        // Clear form
        document.getElementById('newsletterForm').reset();
        
        // Auto-hide alert after 5 seconds
        setTimeout(function() {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }, 1000);
});

// Back to top button functionality
let backToTopButton = document.getElementById("btn-back-to-top");

window.onscroll = function () {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        backToTopButton.style.display = "block";
    } else {
        backToTopButton.style.display = "none";
    }
};

backToTopButton.addEventListener("click", function() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
});

// Social media link tracking (placeholder)
document.querySelectorAll('.social-links a').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Social media link clicked:', this.title);
        // Implement actual social media functionality or analytics tracking
    });
});
</script>
