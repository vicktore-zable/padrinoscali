<?php
// Root index.php - Aratio
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

// Helper debug (optional)
// file_put_contents('debug.log', date('Y-m-d H:i:s') . " - " . $requestUri . "\n", FILE_APPEND);

// Delegates to mod_colab
$routes = ['/', '/index.php', '/login', '/logout', '/dashboard', '/portal', '/registro-simpatizante', '/registro-lider', '/mapa-puestos'];

$shouldDelegate = false;
if ($requestUri === '/' || $requestUri === '/index.php') {
    $shouldDelegate = true;
} else {
    foreach ($routes as $route) {
        if ($route !== '/' && $route !== '/index.php' && strpos($requestUri, $route) === 0) {
            $shouldDelegate = true;
            break;
        }
    }
}

// Skip legacy if page parameter is present (unless it's a priority route)
if (isset($_GET['page']) && !in_array($requestUri, ['/login', '/dashboard'])) {
    $shouldDelegate = false;
}

if ($shouldDelegate) {
    $_GET['route'] = ltrim($requestUri, '/');
    if ($_GET['route'] === 'index.php' || $_GET['route'] === '') {
        $_GET['route'] = '/';
    }
    require_once __DIR__ . '/mod_colab/public/index.php';
    exit;
}

// Legacy Routing
require_once __DIR__ . '/config/config.php';
requireAuth();
// ... rest of legacy index.php ...
include 'layout.php'; // Example
?>
