<?php
/**
 * User Model Class
 * 
 * This class handles all user-related database operations and business logic.
 * It provides methods for user authentication, registration, profile management.
 * 
 * Features:
 * - User registration with validation
 * - Secure password hashing
 * - User authentication
 * - Profile management
 * - Role-based access control
 * - Account status management
 */

require_once 'Database.php';
require_once 'Validator.php';
require_once __DIR__ . '/../config/constants.php';

class User
{
    private $db;
    private $validator;
    
    // User properties
    private $id;
    private $username;
    private $email;
    private $firstName;
    private $lastName;
    private $role;
    private $status;
    private $createdAt;
    private $updatedAt;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->validator = new Validator();
    }
    
    /**
     * Register a new user
     * 
     * @param array $userData User data
     * @return array Result with success status and message
     */
    public function register($userData)
    {
        try {
            // Validate input data
            $validation = $this->validateRegistrationData($userData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ];
            }
            
            // Check if user already exists
            if ($this->userExists($userData['email'], $userData['username'])) {
                return [
                    'success' => false,
                    'message' => 'User with this email or username already exists'
                ];
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Prepare user data for insertion
            $insertData = [
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $hashedPassword,
                'first_name' => $userData['firstName'],
                'last_name' => $userData['lastName'],
                'role' => ROLE_USER,
                'status' => STATUS_ACTIVE,
                'created_at' => date(DATETIME_FORMAT),
                'updated_at' => date(DATETIME_FORMAT)
            ];
            
            // Insert user into database
            $userId = $this->db->insert('users', $insertData);
            
            if ($userId) {
                return [
                    'success' => true,
                    'message' => 'User registered successfully',
                    'user_id' => $userId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to register user'
                ];
            }
            
        } catch (Exception $e) {
            error_log("User registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Registration failed due to server error'
            ];
        }
    }
    
    /**
     * Authenticate user login
     * 
     * @param string $identifier Email or username
     * @param string $password Password
     * @return array Authentication result
     */
    public function login($identifier, $password)
    {
        try {
            // Find user by email or username
            $user = $this->findByEmailOrUsername($identifier);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials'
                ];
            }
            
            // Check if account is active
            if ($user['status'] !== STATUS_ACTIVE) {
                return [
                    'success' => false,
                    'message' => 'Account is not active'
                ];
            }
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Update last login
                $this->updateLastLogin($user['id']);
                
                // Set user session data
                $this->setUserSession($user);
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $this->sanitizeUserData($user)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials'
                ];
            }
            
        } catch (Exception $e) {
            error_log("User login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login failed due to server error'
            ];
        }
    }
    
    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|false User data or false if not found
     */
    public function getById($userId)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $user = $this->db->fetch($sql, ['id' => $userId]);
        
        if ($user) {
            return $this->sanitizeUserData($user);
        }
        
        return false;
    }
    
    /**
     * Get user by email
     * 
     * @param string $email Email address
     * @return array|false User data or false if not found
     */
    public function getByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $user = $this->db->fetch($sql, ['email' => $email]);
        
        if ($user) {
            return $this->sanitizeUserData($user);
        }
        
        return false;
    }
    
    /**
     * Update user profile
     * 
     * @param int $userId User ID
     * @param array $data Updated data
     * @return array Update result
     */
    public function updateProfile($userId, $data)
    {
        try {
            // Validate update data
            $validation = $this->validateUpdateData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ];
            }
            
            // Prepare update data
            $updateData = [
                'first_name' => $data['firstName'],
                'last_name' => $data['lastName'],
                'updated_at' => date(DATETIME_FORMAT)
            ];
            
            // Add email if provided and different
            if (isset($data['email']) && !empty($data['email'])) {
                $currentUser = $this->getById($userId);
                if ($currentUser['email'] !== $data['email']) {
                    // Check if new email already exists
                    if ($this->emailExists($data['email'])) {
                        return [
                            'success' => false,
                            'message' => 'Email already exists'
                        ];
                    }
                    $updateData['email'] = $data['email'];
                }
            }
            
            // Update user
            $affected = $this->db->update('users', $updateData, 'id = :id', ['id' => $userId]);
            
            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No changes made'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Update failed due to server error'
            ];
        }
    }
    
    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Change result
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            // Get current user
            $user = $this->db->fetch("SELECT password FROM users WHERE id = :id", ['id' => $userId]);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Validate new password
            if (!$this->validator->validatePassword($newPassword)) {
                return [
                    'success' => false,
                    'message' => 'New password does not meet requirements'
                ];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $updateData = [
                'password' => $hashedPassword,
                'updated_at' => date(DATETIME_FORMAT)
            ];
            
            $affected = $this->db->update('users', $updateData, 'id = :id', ['id' => $userId]);
            
            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to change password'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Password change failed due to server error'
            ];
        }
    }
    
    /**
     * Get all users (admin function)
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array Users list
     */
    public function getAllUsers($page = 1, $limit = DEFAULT_ITEMS_PER_PAGE)
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT id, username, email, first_name, last_name, role, status, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $users = $this->db->fetchAll($sql, ['limit' => $limit, 'offset' => $offset]);
        
        // Get total count
        $totalSql = "SELECT COUNT(*) FROM users";
        $total = $this->db->fetchColumn($totalSql);
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Delete user
     * 
     * @param int $userId User ID
     * @return array Delete result
     */
    public function deleteUser($userId)
    {
        try {
            $affected = $this->db->delete('users', 'id = :id', ['id' => $userId]);
            
            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'User deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
        } catch (Exception $e) {
            error_log("User deletion error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Deletion failed due to server error'
            ];
        }
    }
    
    /**
     * Check if user exists by email or username
     * 
     * @param string $email Email
     * @param string $username Username
     * @return bool
     */
    private function userExists($email, $username)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email OR username = :username";
        $count = $this->db->fetchColumn($sql, ['email' => $email, 'username' => $username]);
        return $count > 0;
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email
     * @return bool
     */
    private function emailExists($email)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $count = $this->db->fetchColumn($sql, ['email' => $email]);
        return $count > 0;
    }
    
    /**
     * Find user by email or username
     * 
     * @param string $identifier Email or username
     * @return array|false
     */
    private function findByEmailOrUsername($identifier)
    {
        $sql = "SELECT * FROM users WHERE email = :identifier OR username = :identifier";
        return $this->db->fetch($sql, ['identifier' => $identifier]);
    }
    
    /**
     * Update last login timestamp
     * 
     * @param int $userId User ID
     */
    private function updateLastLogin($userId)
    {
        $updateData = ['last_login' => date(DATETIME_FORMAT)];
        $this->db->update('users', $updateData, 'id = :id', ['id' => $userId]);
    }
    
    /**
     * Set user session data
     * 
     * @param array $user User data
     */
    private function setUserSession($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
    }
    
    /**
     * Remove sensitive data from user array
     * 
     * @param array $user User data
     * @return array Sanitized user data
     */
    private function sanitizeUserData($user)
    {
        unset($user['password']);
        return $user;
    }
    
    /**
     * Validate registration data
     * 
     * @param array $data Registration data
     * @return array Validation result
     */
    private function validateRegistrationData($data)
    {
        $errors = [];
        
        if (!$this->validator->validateUsername($data['username'] ?? '')) {
            $errors[] = 'Invalid username';
        }
        
        if (!$this->validator->validateEmail($data['email'] ?? '')) {
            $errors[] = 'Invalid email address';
        }
        
        if (!$this->validator->validatePassword($data['password'] ?? '')) {
            $errors[] = 'Password does not meet requirements';
        }
        
        if (!$this->validator->validateName($data['firstName'] ?? '')) {
            $errors[] = 'Invalid first name';
        }
        
        if (!$this->validator->validateName($data['lastName'] ?? '')) {
            $errors[] = 'Invalid last name';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate update data
     * 
     * @param array $data Update data
     * @return array Validation result
     */
    private function validateUpdateData($data)
    {
        $errors = [];
        
        if (isset($data['email']) && !$this->validator->validateEmail($data['email'])) {
            $errors[] = 'Invalid email address';
        }
        
        if (!$this->validator->validateName($data['firstName'] ?? '')) {
            $errors[] = 'Invalid first name';
        }
        
        if (!$this->validator->validateName($data['lastName'] ?? '')) {
            $errors[] = 'Invalid last name';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Logout user
     */
    public static function logout()
    {
        session_destroy();
        session_start();
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public static function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Check if current user has specific role
     * 
     * @param string $role Role to check
     * @return bool
     */
    public static function hasRole($role)
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
}
?>
