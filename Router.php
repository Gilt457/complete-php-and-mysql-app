<?php
/**
 * Application Router
 * 
 * This class handles URL routing for the Alibaba Clone application.
 * It maps URLs to controller actions following MVC architecture.
 * 
 * Features:
 * - RESTful routing
 * - Route parameters
 * - Middleware support
 * - Route caching
 * - URL generation
 */

class Router
{
    private $routes = [];
    private $middlewares = [];
    private $currentRoute = null;
    
    /**
     * Add GET route
     */
    public function get($path, $action, $middleware = [])
    {
        $this->addRoute('GET', $path, $action, $middleware);
    }
    
    /**
     * Add POST route
     */
    public function post($path, $action, $middleware = [])
    {
        $this->addRoute('POST', $path, $action, $middleware);
    }
    
    /**
     * Add PUT route
     */
    public function put($path, $action, $middleware = [])
    {
        $this->addRoute('PUT', $path, $action, $middleware);
    }
    
    /**
     * Add DELETE route
     */
    public function delete($path, $action, $middleware = [])
    {
        $this->addRoute('DELETE', $path, $action, $middleware);
    }
    
    /**
     * Add route for any HTTP method
     */
    public function any($path, $action, $middleware = [])
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $action, $middleware);
        }
    }
    
    /**
     * Add a route
     */
    private function addRoute($method, $path, $action, $middleware = [])
    {
        $pattern = $this->convertToRegex($path);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'action' => $action,
            'middleware' => $middleware
        ];
    }
    
    /**
     * Convert route path to regex pattern
     */
    private function convertToRegex($path)
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $path);
        
        // Convert {id} to named capture groups
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^\/]+)', $pattern);
        
        // Add start and end anchors
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Dispatch the current request
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $this->getCurrentPath();
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                $this->currentRoute = $route;
                
                // Extract route parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Execute middlewares
                if (!$this->executeMiddlewares($route['middleware'])) {
                    return;
                }
                
                // Execute controller action
                $this->executeAction($route['action'], $params);
                return;
            }
        }
        
        // No route found - 404
        $this->handle404();
    }
    
    /**
     * Get current URL path
     */
    private function getCurrentPath()
    {
        $path = $_SERVER['REQUEST_URI'];
        
        // Remove query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // Remove base path if application is in subdirectory
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        return $path ?: '/';
    }
    
    /**
     * Execute middlewares
     */
    private function executeMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            if (is_string($middleware) && isset($this->middlewares[$middleware])) {
                $middlewareClass = $this->middlewares[$middleware];
                $middlewareInstance = new $middlewareClass();
                
                if (!$middlewareInstance->handle()) {
                    return false;
                }
            } elseif (is_callable($middleware)) {
                if (!$middleware()) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Execute controller action
     */
    private function executeAction($action, $params = [])
    {
        if (is_string($action)) {
            // Parse controller@method format
            if (strpos($action, '@') !== false) {
                list($controllerName, $methodName) = explode('@', $action);
            } else {
                $controllerName = $action;
                $methodName = 'index';
            }
            
            // Create controller instance
            $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
            
            if (!file_exists($controllerFile)) {
                throw new Exception("Controller file not found: " . $controllerFile);
            }
            
            require_once $controllerFile;
            
            if (!class_exists($controllerName)) {
                throw new Exception("Controller class not found: " . $controllerName);
            }
            
            $controller = new $controllerName();
            
            if (!method_exists($controller, $methodName)) {
                throw new Exception("Method not found: " . $controllerName . '::' . $methodName);
            }
            
            // Call the method with parameters
            call_user_func_array([$controller, $methodName], array_values($params));
            
        } elseif (is_callable($action)) {
            // Execute closure
            call_user_func_array($action, array_values($params));
        }
    }
    
    /**
     * Register middleware
     */
    public function middleware($name, $class)
    {
        $this->middlewares[$name] = $class;
    }
    
    /**
     * Handle 404 error
     */
    private function handle404()
    {
        http_response_code(404);
        
        // Try to load 404 controller or view
        if (file_exists(__DIR__ . '/controllers/ErrorController.php')) {
            require_once __DIR__ . '/controllers/ErrorController.php';
            $controller = new ErrorController();
            $controller->notFound();
        } else {
            // Fallback 404 page
            include __DIR__ . '/views/errors/404.php';
        }
    }
    
    /**
     * Generate URL for named route
     */
    public function url($name, $params = [])
    {
        // Implementation for named routes
        // This would require extending the route definition to include names
        return '/'; // Placeholder
    }
    
    /**
     * Get all registered routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
