<?php
/**
 * Base Controller Class
 * 
 * This abstract class provides common functionality for all controllers
 * in the Alibaba Clone application following MVC architecture pattern.
 * 
 * Features:
 * - Common methods for all controllers
 * - View rendering system
 * - Request handling
 * - Error handling
 * - Authentication checks
 */

abstract class BaseController
{
    protected $data = [];
    protected $view;
    protected $layout = 'default';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }
    
    /**
     * Initialize controller
     * Override in child classes for specific initialization
     */
    protected function init()
    {
        // Default initialization
    }
    
    /**
     * Render a view with layout
     * 
     * @param string $view View file name
     * @param array $data Data to pass to view
     * @param string $layout Layout to use
     */
    protected function render($view, $data = [], $layout = null)
    {
        $this->data = array_merge($this->data, $data);
        $layout = $layout ?: $this->layout;
        
        // Start output buffering
        ob_start();
        
        // Extract data for view
        extract($this->data);
        
        // Include the view file
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: " . $viewFile);
        }
        
        // Get view content
        $content = ob_get_clean();
        
        // Include layout if specified
        if ($layout && $layout !== 'none') {
            $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content; // Fallback to content only
            }
        } else {
            echo $content;
        }
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code
     */
    protected function redirect($url, $statusCode = 302)
    {
        header("Location: " . $url, true, $statusCode);
        exit();
    }
    
    /**
     * Return JSON response
     * 
     * @param array $data Data to return
     * @param int $statusCode HTTP status code
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get request method
     * 
     * @return string
     */
    protected function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Require authentication
     * Redirect to login if not authenticated
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Check if user has admin privileges
     * 
     * @return bool
     */
    protected function isAdmin()
    {
        return $this->isAuthenticated() && 
               isset($_SESSION['user_role']) && 
               $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Require admin privileges
     */
    protected function requireAdmin()
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     */
    protected function setFlashMessage($type, $message)
    {
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get and clear flash messages
     * 
     * @return array
     */
    protected function getFlashMessages()
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
    
    /**
     * Handle 404 error
     */
    protected function handle404()
    {
        http_response_code(404);
        $this->render('errors/404', [], 'error');
    }
    
    /**
     * Handle 500 error
     * 
     * @param string $message Error message
     */
    protected function handle500($message = 'Internal Server Error')
    {
        http_response_code(500);
        $this->render('errors/500', ['message' => $message], 'error');
    }
}
