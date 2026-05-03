<?php
/**
 * Entry Point with Debug
 */

// Configurar UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Log function
function debugLog($message) {
    $logFile = __DIR__ . '/route-debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
}

debugLog("======= NEW REQUEST =======");
debugLog("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
debugLog("QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? ''));
debugLog("HTTP_HOST: " . $_SERVER['HTTP_HOST']);
debugLog("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);

// Mostrar errores en desarrollo
if (file_exists(__DIR__ . '/../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../.env');
    if (strpos($envContent, 'APP_ENV=development') !== false) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
}

// Definir constantes de ruta
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
if (!defined('APP_PATH')) define('APP_PATH', ROOT_PATH . '/src');
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', ROOT_PATH . '/config');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', ROOT_PATH . '/public');
if (!defined('CACHE_PATH')) define('CACHE_PATH', ROOT_PATH . '/storage/cache');
if (!defined('LOGS_PATH')) define('LOGS_PATH', ROOT_PATH . '/storage/logs');
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Crear directorios si no existen
foreach ([CACHE_PATH, LOGS_PATH, UPLOADS_PATH] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Cargar configuración
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar headers de seguridad
use App\Utils\Security;
Security::setSecurityHeaders();

// Forzar HTTPS en producción
if (APP_ENV === 'production') {
    Security::forceHttps();
}

// Obtener la URI y método
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

debugLog("After parse_url: {$requestUri}");
debugLog("APP_ENV: " . APP_ENV);

// Remover el prefijo de la aplicación si existe
$basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', PUBLIC_PATH);
debugLog("Base path: {$basePath}");

if (!empty($basePath) && $basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $beforeRemove = $requestUri;
    $requestUri = substr($requestUri, strlen($basePath));
    debugLog("Removed basePath: {$beforeRemove} -> {$requestUri}");
}

// CORRECCIÓN: Manejo del prefijo /colaboradores
debugLog("Before colaboradores check: {$requestUri}");

if (APP_ENV === 'production' && strpos($requestUri, '/colaboradores') === 0) {
    debugLog("Found /colaboradores prefix");

    $regex = '/^\/colaboradores(\/|$)/';
    $matches = preg_match($regex, $requestUri);
    debugLog("Regex match result: " . ($matches ? 'TRUE' : 'FALSE'));

    if (!preg_match($regex, $requestUri)) {
        $beforeRemove = $requestUri;
        $requestUri = substr($requestUri, strlen('/colaboradores'));
        debugLog("Removed /colaboradores prefix: {$beforeRemove} -> {$requestUri}");
    } else {
        debugLog("Kept /colaboradores prefix (matched regex)");
    }
}

// Asegurar que empiece con /
$requestUri = '/' . ltrim($requestUri, '/');
debugLog("Final URI for router: {$requestUri}");

// Router simple
try {
    // Cargar el router
    require_once APP_PATH . '/Core/Router.php';
    require_once APP_PATH . '/Core/Controller.php';

    $router = new App\Core\Router();

    // Cargar las rutas
    require_once ROOT_PATH . '/routes/web.php';

    debugLog("Dispatching to router...");

    // Despachar la ruta
    $router->dispatch($requestUri, $requestMethod);

    debugLog("Route dispatched successfully");

} catch (Exception $e) {
    debugLog("EXCEPTION: " . $e->getMessage());
    debugLog("File: " . $e->getFile() . ":" . $e->getLine());

    // Log del error
    \App\Utils\Logger::exception($e);

    // Mostrar página de error
    if (APP_DEBUG) {
        echo "<h1>Error</h1>";
        echo "<p><strong>Message:</strong> {$e->getMessage()}</p>";
        echo "<p><strong>File:</strong> {$e->getFile()}:{$e->getLine()}</p>";
        echo "<pre>{$e->getTraceAsString()}</pre>";
    } else {
        http_response_code(500);
        if (file_exists(APP_PATH . '/Views/errors/500.php')) {
            require APP_PATH . '/Views/errors/500.php';
        } else {
            echo "<h1>500 - Error Interno del Servidor</h1>";
            echo "<p>Ha ocurrido un error. Por favor contacte al administrador.</p>";
        }
    }
}
