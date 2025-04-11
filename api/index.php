<?php
// api/index.php

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable CORS (for local development)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoloader
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file = __DIR__ . '/' . $class . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Parse URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

// Ensure the first segment is 'api'
if ($uri[0] !== 'api') {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
    exit;
}

// Route definitions
$routes = [
    // Font endpoints
    'POST /api/fonts' => ['Controllers\FontController', 'upload'],
    'GET /api/fonts' => ['Controllers\FontController', 'getAll'],
    'GET /api/fonts/{id}' => ['Controllers\FontController', 'getById'],
    'DELETE /api/fonts/{id}' => ['Controllers\FontController', 'delete'],
    
    // Font Group endpoints
    'POST /api/font-groups' => ['Controllers\FontGroupController', 'create'],
    'GET /api/font-groups' => ['Controllers\FontGroupController', 'getAll'],
    'GET /api/font-groups/{id}' => ['Controllers\FontGroupController', 'getById'],
    'PUT /api/font-groups/{id}' => ['Controllers\FontGroupController', 'update'],
    'DELETE /api/font-groups/{id}' => ['Controllers\FontGroupController', 'delete']
];

// Find matching route
$method = $_SERVER['REQUEST_METHOD'];
$routeKey = null;
$params = [];

foreach ($routes as $route => $handler) {
    $routeParts = explode(' ', $route);
    $routeMethod = $routeParts[0];
    $routePath = $routeParts[1];
    
    // Skip if method doesn't match
    if ($routeMethod !== $method) {
        continue;
    }
    
    // Convert route path to regex pattern
    $pattern = preg_replace('/{[^}]+}/', '([^/]+)', $routePath);
    $pattern = str_replace('/', '\/', $pattern);
    $pattern = '/^' . $pattern . '$/';
    
    // Match current URI against pattern
    $currentPath = '/' . implode('/', $uri);
    if (preg_match($pattern, $currentPath, $matches)) {
        $routeKey = $route;
        
        // Extract parameters
        $routePathParts = explode('/', $routePath);
        $currentPathParts = explode('/', $currentPath);
        
        for ($i = 0; $i < count($routePathParts); $i++) {
            if (preg_match('/{([^}]+)}/', $routePathParts[$i], $paramMatches)) {
                $params[$paramMatches[1]] = $currentPathParts[$i];
            }
        }
        
        break;
    }
}

// Handle route
if ($routeKey) {
    $handler = $routes[$routeKey];
    $controllerClass = $handler[0];
    $actionMethod = $handler[1];
    
    $controller = new $controllerClass();
    
    // Call controller method with parameters
    if (!empty($params)) {
        call_user_func_array([$controller, $actionMethod], array_values($params));
    } else {
        call_user_func([$controller, $actionMethod]);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
?>