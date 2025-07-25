<?php
/**
 * User Controller Class
 * 
 * Handles all user-related operations for the Alibaba Clone application.
 * This controller manages user registration, authentication, profile management,
 * and user dashboard functionality.
 * 
 * Features:
 * - User registration and login
 * - Profile management
 * - Password reset
 * - User dashboard
 * - Order history
 * - Wishlist management
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../classes/User.php';

class UserController extends BaseController
{
    private $userModel;
    
    /**
     * Initialize the controller
     */
    protected function init()
    {
        $this->userModel = new User();
    }
    
    /**
     * Display user dashboard
     * Route: /dashboard
     */
    public function dashboard()
    {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            
            // Get user data
            $user = $this->userModel->getUserById($userId);
            
            // Get recent orders
            $recentOrders = $this->userModel->getUserOrders($userId, 5);
            
            // Get wishlist items
            $wishlistItems = $this->userModel->getWishlistItems($userId, 5);
            
            // Get account statistics
            $stats = [
                'total_orders' => $this->userModel->getUserOrderCount($userId),
                'wishlist_count' => $this->userModel->getWishlistCount($userId),
                'total_spent' => $this->userModel->getTotalSpent($userId)
            ];
            
            $this->render('user/dashboard', [
                'user' => $user,
                'recentOrders' => $recentOrders,
                'wishlistItems' => $wishlistItems,
                'stats' => $stats,
                'pageTitle' => 'Dashboard - ' . $user['first_name']
            ]);
            
        } catch (Exception $e) {
            error_log("User dashboard error: " . $e->getMessage());
            $this->handle500("Error loading dashboard");
        }
    }
    
    /**
     * Display user profile
     * Route: /profile
     */
    public function profile()
    {
        $this->requireAuth();
        
        if ($this->getRequestMethod() === 'POST') {
            $this->updateProfile();
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $user = $this->userModel->getUserById($userId);
            
            $this->render('user/profile', [
                'user' => $user,
                'pageTitle' => 'Profile - ' . $user['first_name']
            ]);
            
        } catch (Exception $e) {
            error_log("User profile error: " . $e->getMessage());
            $this->handle500("Error loading profile");
        }
    }
    
    /**
     * Update user profile
     */
    private function updateProfile()
    {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'gender' => $_POST['gender'] ?? null,
                'bio' => $_POST['bio'] ?? null
            ];
            
            // Basic validation
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                $this->setFlashMessage('error', 'First name, last name, and email are required');
                $this->redirect('/profile');
                return;
            }
            
            // Check if email is already taken by another user
            if ($this->userModel->isEmailTaken($data['email'], $userId)) {
                $this->setFlashMessage('error', 'Email is already taken by another user');
                $this->redirect('/profile');
                return;
            }
            
            $result = $this->userModel->updateUser($userId, $data);
            
            if ($result) {
                // Update session data
                $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
                $_SESSION['user_email'] = $data['email'];
                
                $this->setFlashMessage('success', 'Profile updated successfully');
            } else {
                $this->setFlashMessage('error', 'Error updating profile');
            }
            
            $this->redirect('/profile');
            
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error updating profile');
            $this->redirect('/profile');
        }
    }
    
    /**
     * Display user orders
     * Route: /orders
     */
    public function orders()
    {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            $page = $_GET['page'] ?? 1;
            $status = $_GET['status'] ?? null;
            $limit = 10;
            
            $orders = $this->userModel->getUserOrders($userId, $limit, $page, $status);
            $totalOrders = $this->userModel->getUserOrderCount($userId, $status);
            $totalPages = ceil($totalOrders / $limit);
            
            $this->render('user/orders', [
                'orders' => $orders,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'currentStatus' => $status,
                'pageTitle' => 'My Orders'
            ]);
            
        } catch (Exception $e) {
            error_log("User orders error: " . $e->getMessage());
            $this->handle500("Error loading orders");
        }
    }
    
    /**
     * Display order details
     * Route: /orders/{id}
     */
    public function orderDetails($orderId)
    {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            $order = $this->userModel->getUserOrder($userId, $orderId);
            
            if (!$order) {
                $this->handle404();
                return;
            }
            
            $orderItems = $this->userModel->getOrderItems($orderId);
            
            $this->render('user/order-details', [
                'order' => $order,
                'orderItems' => $orderItems,
                'pageTitle' => 'Order #' . $order['order_number']
            ]);
            
        } catch (Exception $e) {
            error_log("Order details error: " . $e->getMessage());
            $this->handle500("Error loading order details");
        }
    }
    
    /**
     * Display user wishlist
     * Route: /wishlist
     */
    public function wishlist()
    {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            $page = $_GET['page'] ?? 1;
            $limit = 20;
            
            $wishlistItems = $this->userModel->getWishlistItems($userId, $limit, $page);
            $totalItems = $this->userModel->getWishlistCount($userId);
            $totalPages = ceil($totalItems / $limit);
            
            $this->render('user/wishlist', [
                'wishlistItems' => $wishlistItems,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'pageTitle' => 'My Wishlist'
            ]);
            
        } catch (Exception $e) {
            error_log("Wishlist error: " . $e->getMessage());
            $this->handle500("Error loading wishlist");
        }
    }
    
    /**
     * Add product to wishlist (AJAX)
     * Route: /wishlist/add
     */
    public function addToWishlist()
    {
        if (!$this->isAjax() || $this->getRequestMethod() !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            $productId = $_POST['product_id'] ?? null;
            
            if (!$productId) {
                $this->jsonResponse(['success' => false, 'message' => 'Product ID is required'], 400);
                return;
            }
            
            $result = $this->userModel->addToWishlist($userId, $productId);
            
            $this->jsonResponse([
                'success' => $result,
                'message' => $result ? 'Added to wishlist' : 'Error adding to wishlist'
            ]);
            
        } catch (Exception $e) {
            error_log("Add to wishlist error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error adding to wishlist'], 500);
        }
    }
    
    /**
     * Remove product from wishlist (AJAX)
     * Route: /wishlist/remove
     */
    public function removeFromWishlist()
    {
        if (!$this->isAjax() || $this->getRequestMethod() !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            $productId = $_POST['product_id'] ?? null;
            
            if (!$productId) {
                $this->jsonResponse(['success' => false, 'message' => 'Product ID is required'], 400);
                return;
            }
            
            $result = $this->userModel->removeFromWishlist($userId, $productId);
            
            $this->jsonResponse([
                'success' => $result,
                'message' => $result ? 'Removed from wishlist' : 'Error removing from wishlist'
            ]);
            
        } catch (Exception $e) {
            error_log("Remove from wishlist error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error removing from wishlist'], 500);
        }
    }
    
    /**
     * Display addresses
     * Route: /addresses
     */
    public function addresses()
    {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            $addresses = $this->userModel->getUserAddresses($userId);
            
            $this->render('user/addresses', [
                'addresses' => $addresses,
                'pageTitle' => 'My Addresses'
            ]);
            
        } catch (Exception $e) {
            error_log("Addresses error: " . $e->getMessage());
            $this->handle500("Error loading addresses");
        }
    }
    
    /**
     * Add new address
     * Route: /addresses/add
     */
    public function addAddress()
    {
        $this->requireAuth();
        
        if ($this->getRequestMethod() === 'POST') {
            $this->storeAddress();
            return;
        }
        
        $this->render('user/add-address', [
            'pageTitle' => 'Add Address'
        ]);
    }
    
    /**
     * Store new address
     */
    private function storeAddress()
    {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            
            $data = [
                'title' => $_POST['title'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'company' => $_POST['company'] ?? '',
                'address_line_1' => $_POST['address_line_1'] ?? '',
                'address_line_2' => $_POST['address_line_2'] ?? '',
                'city' => $_POST['city'] ?? '',
                'state' => $_POST['state'] ?? '',
                'postal_code' => $_POST['postal_code'] ?? '',
                'country' => $_POST['country'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];
            
            // Basic validation
            $required = ['title', 'first_name', 'last_name', 'address_line_1', 'city', 'state', 'postal_code', 'country'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->setFlashMessage('error', 'All required fields must be filled');
                    $this->redirect('/addresses/add');
                    return;
                }
            }
            
            $result = $this->userModel->addUserAddress($userId, $data);
            
            if ($result) {
                $this->setFlashMessage('success', 'Address added successfully');
                $this->redirect('/addresses');
            } else {
                $this->setFlashMessage('error', 'Error adding address');
                $this->redirect('/addresses/add');
            }
            
        } catch (Exception $e) {
            error_log("Add address error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error adding address');
            $this->redirect('/addresses/add');
        }
    }
    
    /**
     * Change password
     * Route: /change-password
     */
    public function changePassword()
    {
        $this->requireAuth();
        
        if ($this->getRequestMethod() === 'POST') {
            $this->updatePassword();
            return;
        }
        
        $this->render('user/change-password', [
            'pageTitle' => 'Change Password'
        ]);
    }
    
    /**
     * Update password
     */
    private function updatePassword()
    {
        $this->requireAuth();
        
        try {
            $userId = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $this->setFlashMessage('error', 'All fields are required');
                $this->redirect('/change-password');
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                $this->setFlashMessage('error', 'New passwords do not match');
                $this->redirect('/change-password');
                return;
            }
            
            if (strlen($newPassword) < 8) {
                $this->setFlashMessage('error', 'Password must be at least 8 characters long');
                $this->redirect('/change-password');
                return;
            }
            
            // Verify current password
            if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
                $this->setFlashMessage('error', 'Current password is incorrect');
                $this->redirect('/change-password');
                return;
            }
            
            // Update password
            $result = $this->userModel->updatePassword($userId, $newPassword);
            
            if ($result) {
                $this->setFlashMessage('success', 'Password changed successfully');
                $this->redirect('/profile');
            } else {
                $this->setFlashMessage('error', 'Error changing password');
                $this->redirect('/change-password');
            }
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Error changing password');
            $this->redirect('/change-password');
        }
    }
}
