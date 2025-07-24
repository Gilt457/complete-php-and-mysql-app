<?php
/**
 * Registration Page
 * Handles new user registration with validation and security features
 */

require_once '../../includes/functions.php';

$pageTitle = 'Register - ' . SITE_NAME;
$errors = [];
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/profile');
}

// Handle registration form submission
if ($_POST) {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $acceptTerms = isset($_POST['accept_terms']);

    // Validation
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($firstName)) {
        $errors['first_name'] = 'First name is required';
    }

    if (empty($lastName)) {
        $errors['last_name'] = 'Last name is required';
    }

    if (!empty($phone) && !preg_match('/^[\+]?[1-9][\d]{0,15}$/', str_replace([' ', '-', '(', ')'], '', $phone))) {
        $errors['phone'] = 'Please enter a valid phone number';
    }

    if (!$acceptTerms) {
        $errors['accept_terms'] = 'You must accept the terms and conditions';
    }

    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $user = new User();
            
            // Check username
            if ($user->getUserByUsername($username)) {
                $errors['username'] = 'Username already exists';
            }
            
            // Check email
            if ($user->getUserByEmail($email)) {
                $errors['email'] = 'Email already registered';
            }

            // Register user if no errors
            if (empty($errors)) {
                $userData = [
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone
                ];

                $userId = $user->register($userData);
                
                if ($userId) {
                    $success = 'Registration successful! You can now log in.';
                    
                    // Auto-login the user
                    $loginResult = $user->login($username, $password);
                    if ($loginResult['success']) {
                        redirect('/profile');
                    }
                } else {
                    $errors['general'] = 'Registration failed. Please try again.';
                }
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred during registration. Please try again.';
            error_log('Registration error: ' . $e->getMessage());
        }
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row min-vh-100">
        <!-- Left side - Registration form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4">
            <div class="w-100" style="max-width: 500px;">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-3 fw-normal">Create Account</h1>
                    <p class="text-muted">Join us today and start shopping!</p>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $errors['general'] ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="needs-validation" novalidate data-validate>
                    <?= generateCSRFToken() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="<?= htmlspecialchars($firstName ?? '') ?>" 
                                   required
                                   data-min-length="2">
                            <?php if (isset($errors['first_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="<?= htmlspecialchars($lastName ?? '') ?>" 
                                   required
                                   data-min-length="2">
                            <?php if (isset($errors['last_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($username ?? '') ?>" 
                               required
                               data-min-length="3"
                               pattern="[a-zA-Z0-9_]+"
                               title="Username can only contain letters, numbers, and underscores">
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= $errors['username'] ?></div>
                        <?php endif; ?>
                        <div class="form-text">3-20 characters. Letters, numbers, and underscores only.</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($email ?? '') ?>" 
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" 
                               class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                               id="phone" 
                               name="phone" 
                               value="<?= htmlspecialchars($phone ?? '') ?>"
                               placeholder="+1 (555) 123-4567">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                        <?php endif; ?>
                        <div class="form-text">Optional. Include country code for international numbers.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" 
                                       name="password" 
                                       required
                                       data-min-length="8">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">
                                <small>
                                    Password must be at least 8 characters and contain:
                                    <ul class="mb-0 mt-1">
                                        <li>One uppercase letter</li>
                                        <li>One lowercase letter</li>
                                        <li>One number</li>
                                    </ul>
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input <?= isset($errors['accept_terms']) ? 'is-invalid' : '' ?>" 
                                   type="checkbox" 
                                   id="accept_terms" 
                                   name="accept_terms" 
                                   required
                                   <?= isset($acceptTerms) && $acceptTerms ? 'checked' : '' ?>>
                            <label class="form-check-label" for="accept_terms">
                                I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a> <span class="text-danger">*</span>
                            </label>
                            <?php if (isset($errors['accept_terms'])): ?>
                                <div class="invalid-feedback"><?= $errors['accept_terms'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="newsletter" 
                                   name="newsletter"
                                   value="1">
                            <label class="form-check-label" for="newsletter">
                                Subscribe to our newsletter for updates and special offers
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="mb-0">Already have an account? <a href="/login" class="text-decoration-none">Sign in here</a></p>
                    </div>
                </form>

                <!-- Social Registration (Optional) -->
                <div class="mt-4">
                    <div class="text-center mb-3">
                        <span class="text-muted">Or register with</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="registerWithGoogle()">
                                <i class="fab fa-google me-2"></i>Google
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="registerWithFacebook()">
                                <i class="fab fa-facebook-f me-2"></i>Facebook
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Marketing content -->
        <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-light">
            <div class="text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-shopping-bag text-primary" style="font-size: 4rem;"></i>
                </div>
                <h2 class="h3 mb-3">Join Our Community</h2>
                <p class="lead text-muted mb-4">
                    Discover amazing products, enjoy exclusive deals, and be part of our growing community of satisfied customers.
                </p>
                
                <div class="row text-center mt-5">
                    <div class="col-4">
                        <div class="mb-2">
                            <i class="fas fa-shipping-fast text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h6>Fast Shipping</h6>
                        <small class="text-muted">Free delivery on orders over $50</small>
                    </div>
                    <div class="col-4">
                        <div class="mb-2">
                            <i class="fas fa-award text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h6>Quality Products</h6>
                        <small class="text-muted">Curated selection of premium items</small>
                    </div>
                    <div class="col-4">
                        <div class="mb-2">
                            <i class="fas fa-headset text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h6>24/7 Support</h6>
                        <small class="text-muted">Always here to help you</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password visibility toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthIndicator = document.getElementById('password-strength');
    
    if (!strengthIndicator) return;
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
    
    const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const strengthColors = ['danger', 'warning', 'info', 'success', 'success'];
    
    strengthIndicator.textContent = strengthLevels[strength] || '';
    strengthIndicator.className = `text-${strengthColors[strength] || 'muted'}`;
});

// Real-time password confirmation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Username availability check (debounced)
let usernameTimeout;
document.getElementById('username').addEventListener('input', function() {
    clearTimeout(usernameTimeout);
    const username = this.value;
    
    if (username.length >= 3) {
        usernameTimeout = setTimeout(() => {
            checkUsernameAvailability(username);
        }, 500);
    }
});

async function checkUsernameAvailability(username) {
    try {
        const response = await fetch('/api/check-username.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= generateCSRFToken() ?>'
            },
            body: JSON.stringify({ username })
        });
        
        const result = await response.json();
        const usernameInput = document.getElementById('username');
        
        if (result.available) {
            usernameInput.classList.remove('is-invalid');
            usernameInput.classList.add('is-valid');
        } else {
            usernameInput.classList.remove('is-valid');
            usernameInput.classList.add('is-invalid');
        }
    } catch (error) {
        console.error('Error checking username:', error);
    }
}

// Social registration placeholders
function registerWithGoogle() {
    alert('Google registration would be implemented here');
}

function registerWithFacebook() {
    alert('Facebook registration would be implemented here');
}
</script>

<?php include '../../includes/footer.php'; ?>
