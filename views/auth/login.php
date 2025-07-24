<?php
/**
 * Login Page View
 * 
 * This page handles user authentication and login functionality.
 * It includes form validation, security features, and user-friendly design.
 * 
 * Features:
 * - Secure login form with CSRF protection
 * - Remember me functionality
 * - Password visibility toggle
 * - Forgot password link
 * - Registration link for new users
 * - Rate limiting protection
 */

require_once 'classes/User.php';
require_once 'classes/Validator.php';

// Initialize classes
$user = new User();
$validator = new Validator();

// Redirect if already logged in
if (User::isLoggedIn()) {
    setFlashMessage(MSG_INFO, 'You are already logged in.');
    redirect('index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$validator->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage(MSG_ERROR, 'Invalid security token. Please try again.');
    } else {
        $identifier = cleanInput($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        // Basic validation
        if (empty($identifier) || empty($password)) {
            setFlashMessage(MSG_ERROR, 'Please fill in all required fields.');
        } else {
            // Attempt login
            $loginResult = $user->login($identifier, $password);
            
            if ($loginResult['success']) {
                // Handle remember me
                if ($rememberMe) {
                    // Set remember me cookie (implement as needed)
                    setcookie('remember_token', 'token_here', time() + (30 * 24 * 60 * 60), '/', '', true, true);
                }
                
                setFlashMessage(MSG_SUCCESS, $loginResult['message']);
                
                // Redirect to intended page or dashboard
                $redirectUrl = $_SESSION['intended_url'] ?? 'index.php';
                unset($_SESSION['intended_url']);
                redirect($redirectUrl);
            } else {
                setFlashMessage(MSG_ERROR, $loginResult['message']);
            }
        }
    }
}

// Set page title
$pageTitle = 'Login - ' . APP_NAME;
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <h2 class="card-title mb-2">Welcome Back</h2>
                        <p class="text-muted">Sign in to your account to continue</p>
                    </div>
                    
                    <!-- Login Form -->
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <?php echo csrfTokenField(); ?>
                        
                        <!-- Email/Username Field -->
                        <div class="mb-3">
                            <label for="identifier" class="form-label">Email or Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="identifier" 
                                       name="identifier" 
                                       placeholder="Enter your email or username"
                                       value="<?php echo sanitizeOutput($_POST['identifier'] ?? ''); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please enter your email or username.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter your password"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword"
                                        tabindex="-1">
                                    <i class="far fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Remember Me & Forgot Password -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="remember_me" 
                                           name="remember_me"
                                           <?php echo isset($_POST['remember_me']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="remember_me">
                                        Remember me
                                    </label>
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <a href="index.php?page=forgot-password" class="text-decoration-none">
                                    Forgot password?
                                </a>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Sign In
                            </button>
                        </div>
                        
                        <!-- Divider -->
                        <div class="text-center mb-3">
                            <div class="divider">
                                <span class="divider-text">or</span>
                            </div>
                        </div>
                        
                        <!-- Social Login (Optional) -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-danger w-100" disabled>
                                    <i class="fab fa-google me-2"></i>
                                    Google
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-primary w-100" disabled>
                                    <i class="fab fa-facebook-f me-2"></i>
                                    Facebook
                                </button>
                            </div>
                        </div>
                        
                        <!-- Registration Link -->
                        <div class="text-center">
                            <p class="mb-0">
                                Don't have an account? 
                                <a href="index.php?page=register" class="text-decoration-none fw-bold">
                                    Create one here
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    By signing in, you agree to our 
                    <a href="index.php?page=terms" class="text-decoration-none">Terms of Service</a> 
                    and 
                    <a href="index.php?page=privacy" class="text-decoration-none">Privacy Policy</a>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Demo Account Information -->
<?php if (APP_DEBUG): ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Demo Account</h6>
                <p class="mb-2">You can use the following demo account for testing:</p>
                <ul class="mb-0">
                    <li><strong>Email:</strong> admin@example.com</li>
                    <li><strong>Username:</strong> admin</li>
                    <li><strong>Password:</strong> password</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.card {
    border-radius: 1rem;
}

.input-group-text {
    background-color: var(--bs-light);
    border-color: var(--bs-border-color);
}

.divider {
    position: relative;
    margin: 1rem 0;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background-color: var(--bs-border-color);
}

.divider-text {
    background-color: white;
    padding: 0 1rem;
    color: var(--bs-secondary);
    font-size: 0.875rem;
}

.btn-outline-danger:disabled,
.btn-outline-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 576px) {
    .card-body {
        padding: 2rem !important;
    }
}
</style>

<script>
$(document).ready(function() {
    // Password visibility toggle
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const passwordFieldType = passwordField.attr('type');
        const icon = $(this).find('i');
        
        if (passwordFieldType === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('far fa-eye').addClass('fas fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fas fa-eye-slash').addClass('far fa-eye');
        }
    });
    
    // Form validation
    $('.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Auto-focus on first input
    $('#identifier').focus();
    
    // Handle Enter key in password field
    $('#password').keypress(function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
    
    // Demo account quick fill (development only)
    <?php if (APP_DEBUG): ?>
    $(document).on('click', '.alert-info', function() {
        $('#identifier').val('admin@example.com');
        $('#password').val('password');
    });
    <?php endif; ?>
    
    // Enhanced security: Clear form on page unload
    $(window).on('beforeunload', function() {
        $('#password').val('');
    });
    
    // Disable form submission on double-click
    let formSubmitted = false;
    $('form').on('submit', function() {
        if (formSubmitted) {
            return false;
        }
        formSubmitted = true;
        
        // Re-enable after 3 seconds
        setTimeout(function() {
            formSubmitted = false;
        }, 3000);
    });
});
</script>
