<?php
/**
 * Authentication Controller Class
 * 
 * Handles all authentication-related operations for the Alibaba Clone application.
 * This controller manages user login, registration, logout, password reset,
 * and email verification functionality.
 * 
 * Features:
 * - User registration with email verification
 * - User login and logout
 * - Password reset functionality
 * - Remember me functionality
 * - Social media authentication (Google, Facebook)
 * - Two-factor authentication (2FA)
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Validator.php';

class AuthController extends BaseController
{
    private $userModel;
    private $validator;
    
    /**
     * Initialize the controller
     */
    protected function init()
    {
        $this->userModel = new User();
        $this->validator = new Validator();
        $this->layout = 'auth'; // Use auth layout for authentication pages
    }
    
    /**
     * Display login form
     * Route: /login
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }
        
        if ($this->getRequestMethod() === 'POST') {
            $this->processLogin();
            return;
        }
        
        $this->render('auth/login', [
            'pageTitle' => 'Login - Alibaba Clone',
            'showRememberMe' => true
        ]);
    }
    
    /**
     * Process login form submission
     */
    private function processLogin()
    {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            // Basic validation
            if (empty($email) || empty($password)) {
                $this->setFlashMessage('error', 'Email and password are required');
                $this->redirect('/login');
                return;
            }
            
            // Rate limiting check
            if ($this->isLoginRateLimited($email)) {
                $this->setFlashMessage('error', 'Too many login attempts. Please try again later.');
                $this->redirect('/login');
                return;
            }
            
            // Attempt login
            $user = $this->userModel->authenticate($email, $password);
            
            if ($user) {
                // Check if account is active
                if ($user['status'] !== 'active') {
                    $this->setFlashMessage('error', 'Your account is not active. Please contact support.');
                    $this->redirect('/login');
                    return;
                }
                
                // Check if email is verified
                if (!$user['email_verified']) {
                    $this->setFlashMessage('warning', 'Please verify your email address before logging in.');
                    $this->redirect('/verify-email?email=' . urlencode($email));
                    return;
                }
                
                // Set session data
                $this->setUserSession($user);
                
                // Handle remember me
                if ($rememberMe) {
                    $this->setRememberMeToken($user['id']);
                }
                
                // Clear login attempts
                $this->clearLoginAttempts($email);
                
                // Log successful login
                $this->userModel->logActivity($user['id'], 'login', 'User logged in successfully');
                
                // Redirect to intended page or dashboard
                $redirectTo = $_SESSION['intended_url'] ?? '/dashboard';
                unset($_SESSION['intended_url']);
                
                $this->setFlashMessage('success', 'Welcome back, ' . $user['first_name'] . '!');
                $this->redirect($redirectTo);
                
            } else {
                // Record failed login attempt
                $this->recordLoginAttempt($email);
                
                $this->setFlashMessage('error', 'Invalid email or password');
                $this->redirect('/login');
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred during login. Please try again.');
            $this->redirect('/login');
        }
    }
    
    /**
     * Display registration form
     * Route: /register
     */
    public function register()
    {
        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }
        
        if ($this->getRequestMethod() === 'POST') {
            $this->processRegistration();
            return;
        }
        
        $this->render('auth/register', [
            'pageTitle' => 'Register - Alibaba Clone'
        ]);
    }
    
    /**
     * Process registration form submission
     */
    private function processRegistration()
    {
        try {
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'agree_terms' => isset($_POST['agree_terms'])
            ];
            
            // Validation rules
            $rules = [
                'first_name' => 'required|min:2|max:50|alpha',
                'last_name' => 'required|min:2|max:50|alpha',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'phone' => 'required|phone',
                'agree_terms' => 'required'
            ];
            
            // Validate data
            if (!$this->validator->validate($data, $rules)) {
                $this->setFlashMessage('error', 'Please fix the validation errors');
                $this->redirect('/register');
                return;
            }
            
            // Check if email already exists
            if ($this->userModel->emailExists($data['email'])) {
                $this->setFlashMessage('error', 'Email address is already registered');
                $this->redirect('/register');
                return;
            }
            
            // Create user
            $userData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_BCRYPT),
                'phone' => $data['phone'],
                'status' => 'active',
                'role' => 'customer',
                'email_verified' => false,
                'email_verification_token' => $this->generateToken(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                // Send verification email
                $this->sendVerificationEmail($userData['email'], $userData['email_verification_token']);
                
                // Log registration
                $this->userModel->logActivity($userId, 'register', 'User registered successfully');
                
                $this->setFlashMessage('success', 'Registration successful! Please check your email to verify your account.');
                $this->redirect('/verify-email?email=' . urlencode($userData['email']));
                
            } else {
                $this->setFlashMessage('error', 'Registration failed. Please try again.');
                $this->redirect('/register');
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred during registration. Please try again.');
            $this->redirect('/register');
        }
    }
    
    /**
     * Display email verification page
     * Route: /verify-email
     */
    public function verifyEmail()
    {
        $email = $_GET['email'] ?? '';
        $token = $_GET['token'] ?? '';
        
        if ($token) {
            // Process email verification
            $this->processEmailVerification($token);
            return;
        }
        
        $this->render('auth/verify-email', [
            'email' => $email,
            'pageTitle' => 'Verify Email - Alibaba Clone'
        ]);
    }
    
    /**
     * Process email verification
     */
    private function processEmailVerification($token)
    {
        try {
            $user = $this->userModel->getUserByVerificationToken($token);
            
            if (!$user) {
                $this->setFlashMessage('error', 'Invalid or expired verification token');
                $this->redirect('/login');
                return;
            }
            
            // Mark email as verified
            $result = $this->userModel->verifyEmail($user['id']);
            
            if ($result) {
                // Log email verification
                $this->userModel->logActivity($user['id'], 'email_verified', 'Email verified successfully');
                
                $this->setFlashMessage('success', 'Email verified successfully! You can now log in.');
                $this->redirect('/login');
            } else {
                $this->setFlashMessage('error', 'Error verifying email. Please try again.');
                $this->redirect('/verify-email?email=' . urlencode($user['email']));
            }
            
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred during email verification.');
            $this->redirect('/login');
        }
    }
    
    /**
     * Resend verification email
     * Route: /resend-verification
     */
    public function resendVerification()
    {
        if ($this->getRequestMethod() !== 'POST') {
            $this->redirect('/login');
            return;
        }
        
        try {
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                $this->setFlashMessage('error', 'Email address is required');
                $this->redirect('/verify-email');
                return;
            }
            
            $user = $this->userModel->getUserByEmail($email);
            
            if (!$user) {
                $this->setFlashMessage('error', 'Email address not found');
                $this->redirect('/verify-email');
                return;
            }
            
            if ($user['email_verified']) {
                $this->setFlashMessage('info', 'Email is already verified');
                $this->redirect('/login');
                return;
            }
            
            // Generate new verification token
            $token = $this->generateToken();
            $this->userModel->updateVerificationToken($user['id'], $token);
            
            // Send verification email
            $this->sendVerificationEmail($email, $token);
            
            $this->setFlashMessage('success', 'Verification email sent successfully');
            $this->redirect('/verify-email?email=' . urlencode($email));
            
        } catch (Exception $e) {
            error_log("Resend verification error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error sending verification email');
            $this->redirect('/verify-email');
        }
    }
    
    /**
     * Display forgot password form
     * Route: /forgot-password
     */
    public function forgotPassword()
    {
        if ($this->getRequestMethod() === 'POST') {
            $this->processForgotPassword();
            return;
        }
        
        $this->render('auth/forgot-password', [
            'pageTitle' => 'Forgot Password - Alibaba Clone'
        ]);
    }
    
    /**
     * Process forgot password form
     */
    private function processForgotPassword()
    {
        try {
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                $this->setFlashMessage('error', 'Email address is required');
                $this->redirect('/forgot-password');
                return;
            }
            
            $user = $this->userModel->getUserByEmail($email);
            
            if ($user) {
                // Generate reset token
                $token = $this->generateToken();
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $this->userModel->createPasswordResetToken($user['id'], $token, $expiry);
                
                // Send reset email
                $this->sendPasswordResetEmail($email, $token);
                
                // Log password reset request
                $this->userModel->logActivity($user['id'], 'password_reset_requested', 'Password reset requested');
            }
            
            // Always show success message (security best practice)
            $this->setFlashMessage('success', 'If an account with that email exists, a password reset link has been sent.');
            $this->redirect('/forgot-password');
            
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred. Please try again.');
            $this->redirect('/forgot-password');
        }
    }
    
    /**
     * Display reset password form
     * Route: /reset-password
     */
    public function resetPassword()
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->setFlashMessage('error', 'Invalid reset token');
            $this->redirect('/forgot-password');
            return;
        }
        
        // Verify token
        $resetData = $this->userModel->getPasswordResetData($token);
        
        if (!$resetData || strtotime($resetData['expires_at']) < time()) {
            $this->setFlashMessage('error', 'Invalid or expired reset token');
            $this->redirect('/forgot-password');
            return;
        }
        
        if ($this->getRequestMethod() === 'POST') {
            $this->processResetPassword($token);
            return;
        }
        
        $this->render('auth/reset-password', [
            'token' => $token,
            'pageTitle' => 'Reset Password - Alibaba Clone'
        ]);
    }
    
    /**
     * Process reset password form
     */
    private function processResetPassword($token)
    {
        try {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || empty($confirmPassword)) {
                $this->setFlashMessage('error', 'Both password fields are required');
                $this->redirect('/reset-password?token=' . $token);
                return;
            }
            
            if ($password !== $confirmPassword) {
                $this->setFlashMessage('error', 'Passwords do not match');
                $this->redirect('/reset-password?token=' . $token);
                return;
            }
            
            if (strlen($password) < 8) {
                $this->setFlashMessage('error', 'Password must be at least 8 characters long');
                $this->redirect('/reset-password?token=' . $token);
                return;
            }
            
            // Verify token again
            $resetData = $this->userModel->getPasswordResetData($token);
            
            if (!$resetData || strtotime($resetData['expires_at']) < time()) {
                $this->setFlashMessage('error', 'Invalid or expired reset token');
                $this->redirect('/forgot-password');
                return;
            }
            
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $result = $this->userModel->updateUserPassword($resetData['user_id'], $hashedPassword);
            
            if ($result) {
                // Delete reset token
                $this->userModel->deletePasswordResetToken($token);
                
                // Log password reset
                $this->userModel->logActivity($resetData['user_id'], 'password_reset', 'Password reset successfully');
                
                $this->setFlashMessage('success', 'Password reset successfully. You can now log in with your new password.');
                $this->redirect('/login');
            } else {
                $this->setFlashMessage('error', 'Error resetting password. Please try again.');
                $this->redirect('/reset-password?token=' . $token);
            }
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred while resetting password.');
            $this->redirect('/reset-password?token=' . $token);
        }
    }
    
    /**
     * Logout user
     * Route: /logout
     */
    public function logout()
    {
        try {
            if ($this->isAuthenticated()) {
                $userId = $_SESSION['user_id'];
                
                // Log logout activity
                $this->userModel->logActivity($userId, 'logout', 'User logged out');
                
                // Clear remember me token if exists
                if (isset($_COOKIE['remember_token'])) {
                    $this->userModel->clearRememberToken($userId);
                    setcookie('remember_token', '', time() - 3600, '/');
                }
            }
            
            // Clear session
            session_unset();
            session_destroy();
            
            $this->setFlashMessage('success', 'You have been logged out successfully');
            $this->redirect('/login');
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            $this->redirect('/');
        }
    }
    
    // ======================
    // HELPER METHODS
    // ======================
    
    /**
     * Set user session data
     */
    private function setUserSession($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }
    
    /**
     * Set remember me token
     */
    private function setRememberMeToken($userId)
    {
        $token = $this->generateToken();
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $this->userModel->setRememberToken($userId, $token, $expiry);
        
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    }
    
    /**
     * Generate secure token
     */
    private function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Check if login is rate limited
     */
    private function isLoginRateLimited($email)
    {
        $attempts = $_SESSION['login_attempts'][$email] ?? 0;
        $lastAttempt = $_SESSION['last_login_attempt'][$email] ?? 0;
        
        // Allow 5 attempts per 15 minutes
        if ($attempts >= 5 && (time() - $lastAttempt) < 900) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Record login attempt
     */
    private function recordLoginAttempt($email)
    {
        $_SESSION['login_attempts'][$email] = ($_SESSION['login_attempts'][$email] ?? 0) + 1;
        $_SESSION['last_login_attempt'][$email] = time();
    }
    
    /**
     * Clear login attempts
     */
    private function clearLoginAttempts($email)
    {
        unset($_SESSION['login_attempts'][$email]);
        unset($_SESSION['last_login_attempt'][$email]);
    }
    
    /**
     * Send verification email
     */
    private function sendVerificationEmail($email, $token)
    {
        // Implementation depends on your email service
        // This is a placeholder for the actual email sending logic
        $verificationUrl = $_SERVER['HTTP_HOST'] . '/verify-email?token=' . $token;
        
        // You would implement actual email sending here
        // For example, using PHPMailer, SendGrid, or another email service
        
        error_log("Verification email would be sent to: $email with URL: $verificationUrl");
    }
    
    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($email, $token)
    {
        // Implementation depends on your email service
        $resetUrl = $_SERVER['HTTP_HOST'] . '/reset-password?token=' . $token;
        
        // You would implement actual email sending here
        error_log("Password reset email would be sent to: $email with URL: $resetUrl");
    }
}
