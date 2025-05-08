<?php
require_once __DIR__ . '/../config/config.php';

// Start session with secure settings
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Initialize security middleware
$middleware = new Middleware();
$middleware->handle();

// Initialize security class
$security = Security::getInstance();

// Generate CSRF token for forms
$csrfToken = $security->generateCSRFToken();

// Route handling
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Basic routing
switch ($request) {
    case '/health':
        require __DIR__ . '/../app/controllers/HealthController.php';
        $controller = new HealthController();
        $controller->check();
        break;

    case '/':
    case '/dashboard':
        require __DIR__ . '/../app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;
        
    case '/login':
        if ($method === 'POST') {
            require __DIR__ . '/../app/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->login();
        } else {
            require __DIR__ . '/../app/views/auth/login.php';
        }
        break;
        
    case '/logout':
        require __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
        
    default:
        header('HTTP/1.1 404 Not Found');
        require __DIR__ . '/../app/views/errors/404.php';
        break;
} 