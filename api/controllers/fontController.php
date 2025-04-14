<?php
// index.php (in root folder)

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load configuration
require_once 'api/config/Config.php';
Config::init();

// Include required files
require_once 'api/controllers/FontController.php';
require_once 'api/controllers/FontGroupController.php';

// Parse URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/font-grouper-backend/', '', $uri); // Remove project folder from URI
$uri = explode('/', trim($uri, '/'));

// Route definitions
$routes = [
    // Font endpoints
    'POST api/fonts' => ['FontController', 'upload'],
    'GET api/fonts' => ['FontController', 'getAll'],
    'GET api/fonts/{id}' => ['FontController', 'getById'],
    'DELETE api/fonts/{id}' => ['FontController', 'delete'],
    
    // Font Group endpoints
    'POST api/font-groups' => ['FontGroupController', 'create'],
    'GET api/font-groups' => ['FontGroupController', 'getAll'],
    'GET api/font-groups/{id}' => ['FontGroupController', 'getById'],
    'PUT api/font-groups/{id}' => ['FontGroupController', 'update'],
    'DELETE api/font-groups/{id}' => ['FontGroupController', 'delete']
];

// Find matching route
$method = $_SERVER['REQUEST_METHOD'];
$routeKey = null;
$params = [];

$currentPath = implode('/', $uri);

foreach ($routes as $route => $handler) {
    $routeParts = explode(' ', $route);
    $routeMethod = $routeParts[0];
    $routePath = $routeParts[1];
    
    if ($routeMethod !== $method) {
        continue;
    }
    
    // Convert route path to regex pattern
    $pattern = preg_replace('/{[^}]+}/', '([^/]+)', $routePath);
    $pattern = str_replace('/', '\/', $pattern);
    $pattern = '/^' . $pattern . '$/';
    
    if (preg_match($pattern, $currentPath, $matches)) {
        $routeKey = $route;
        
        // Extract parameters
        $routePathParts = explode('/', $routePath);
        $currentPathParts = explode('/', $currentPath);
        
        for ($i = 0; $i < count($routePathParts); $i++) {
            if (isset($currentPathParts[$i]) && preg_match('/{([^}]+)}/', $routePathParts[$i], $paramMatches)) {
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
    
    // Call controller method
    if (!empty($params)) {
        call_user_func_array([$controller, $actionMethod], $params);
    } else {
        call_user_func([$controller, $actionMethod]);
    }
} else {
    // Debug info
    echo json_encode([
        'error' => 'Endpoint not found',
        'method' => $method,
        'path' => $currentPath,
        'available_routes' => array_keys($routes)
    ]);
}
?>