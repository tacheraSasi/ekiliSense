<?php
/**
 * ekiliSense API Gateway v1
 * RESTful API for mobile and third-party integrations
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RateLimitMiddleware.php';
require_once __DIR__ . '/utils/Response.php';

class APIGateway {
    private $routes = [];
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->initializeRoutes();
    }
    
    private function initializeRoutes() {
        // Authentication routes
        $this->addRoute('POST', '/auth/login', 'AuthController', 'login', []);
        $this->addRoute('POST', '/auth/register', 'AuthController', 'register', []);
        $this->addRoute('POST', '/auth/refresh', 'AuthController', 'refresh', ['auth']);
        $this->addRoute('POST', '/auth/logout', 'AuthController', 'logout', ['auth']);
        
        // School management routes
        $this->addRoute('GET', '/schools/profile', 'SchoolController', 'getProfile', ['auth', 'rate_limit']);
        $this->addRoute('PUT', '/schools/profile', 'SchoolController', 'updateProfile', ['auth']);
        $this->addRoute('GET', '/schools/stats', 'SchoolController', 'getStats', ['auth']);
        
        // Student management routes
        $this->addRoute('GET', '/students', 'StudentController', 'list', ['auth', 'rate_limit']);
        $this->addRoute('POST', '/students', 'StudentController', 'create', ['auth']);
        $this->addRoute('GET', '/students/{id}', 'StudentController', 'get', ['auth']);
        $this->addRoute('PUT', '/students/{id}', 'StudentController', 'update', ['auth']);
        $this->addRoute('DELETE', '/students/{id}', 'StudentController', 'delete', ['auth']);
        
        // Teacher management routes
        $this->addRoute('GET', '/teachers', 'TeacherController', 'list', ['auth', 'rate_limit']);
        $this->addRoute('POST', '/teachers', 'TeacherController', 'create', ['auth']);
        $this->addRoute('GET', '/teachers/{id}', 'TeacherController', 'get', ['auth']);
        $this->addRoute('PUT', '/teachers/{id}', 'TeacherController', 'update', ['auth']);
        
        // Class management routes
        $this->addRoute('GET', '/classes', 'ClassController', 'list', ['auth', 'rate_limit']);
        $this->addRoute('POST', '/classes', 'ClassController', 'create', ['auth']);
        $this->addRoute('GET', '/classes/{id}', 'ClassController', 'get', ['auth']);
        
        // Assignment routes
        $this->addRoute('GET', '/assignments', 'AssignmentController', 'list', ['auth', 'rate_limit']);
        $this->addRoute('POST', '/assignments', 'AssignmentController', 'create', ['auth']);
        $this->addRoute('GET', '/assignments/{id}', 'AssignmentController', 'get', ['auth']);
        
        // Parent portal routes
        $this->addRoute('GET', '/parent/children', 'ParentController', 'getChildren', ['auth']);
        $this->addRoute('GET', '/parent/children/{id}/grades', 'ParentController', 'getGrades', ['auth']);
        $this->addRoute('GET', '/parent/children/{id}/attendance', 'ParentController', 'getAttendance', ['auth']);
        $this->addRoute('GET', '/parent/notifications', 'ParentController', 'getNotifications', ['auth']);
        
        // Notification routes
        $this->addRoute('GET', '/notifications', 'NotificationController', 'list', ['auth']);
        $this->addRoute('POST', '/notifications/mark-read', 'NotificationController', 'markRead', ['auth']);
    }
    
    private function addRoute($method, $path, $controller, $action, $middleware = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove /api/v1 prefix
        $path = preg_replace('#^/api/v1#', '', $path);
        
        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                
                try {
                    // Execute middleware
                    $this->executeMiddleware($route['middleware']);
                    
                    // Load and execute controller
                    $controllerFile = __DIR__ . '/controllers/' . $route['controller'] . '.php';
                    
                    if (!file_exists($controllerFile)) {
                        Response::error('Controller not found', 404);
                        return;
                    }
                    
                    require_once $controllerFile;
                    $controller = new $route['controller']($this->conn);
                    
                    if (!method_exists($controller, $route['action'])) {
                        Response::error('Action not found', 404);
                        return;
                    }
                    
                    // Call controller action with route parameters
                    call_user_func_array([$controller, $route['action']], $matches);
                    
                } catch (Exception $e) {
                    Response::error($e->getMessage(), 500);
                }
                
                return;
            }
        }
        
        Response::error('Endpoint not found', 404);
    }
    
    private function convertToRegex($path) {
        // Convert {id} to regex capture group
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    private function executeMiddleware($middleware) {
        foreach ($middleware as $mw) {
            switch ($mw) {
                case 'auth':
                    AuthMiddleware::verify();
                    break;
                case 'rate_limit':
                    RateLimitMiddleware::check();
                    break;
            }
        }
    }
}

// Initialize and handle request
$api = new APIGateway($conn);
$api->handleRequest();
