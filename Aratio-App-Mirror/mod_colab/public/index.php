<?php
/**
 * Entry Point
 * Punto de entrada principal de la aplicación
 *
 * @package Colaboradores
 * @author A Ratio - Sistema de Inteligencia Política
 * @version 1.0
 */

// DEBUG HIT
file_put_contents(__DIR__ . '/hit.log', date('Y-m-d H:i:s') . " - HIT " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);

// REGRESIÓN: Redirección de colaboradores.aratio... a aratio.mrmtech...
// Solo si el host es el subdominio antiguo
if (isset($_SERVER['HTTP_HOST']) && stripos($_SERVER['HTTP_HOST'], 'colaboradores.aratio.mrmtech.net') !== false) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: https://aratio.mrmtech.net/registro-simpatizante');
    exit;
}

// Configurar UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Mostrar errores en desarrollo
if (file_exists(__DIR__ . '/../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../.env');
    if (strpos($envContent, 'APP_ENV=development') !== false) {
        // En desarrollo, mostrar errores pero cuidando el formato JSON si es API
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            ini_set('display_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        } else {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
    } else {
        ini_set('display_errors', 0);
        error_reporting(0);
    }
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Definir constantes de ruta (solo si no están definidas)
if (!defined('ROOT_PATH'))
    define('ROOT_PATH', dirname(__DIR__));
if (!defined('APP_PATH'))
    define('APP_PATH', ROOT_PATH . '/src');
if (!defined('CONFIG_PATH'))
    define('CONFIG_PATH', ROOT_PATH . '/config');
if (!defined('PUBLIC_PATH'))
    define('PUBLIC_PATH', ROOT_PATH . '/public');
if (!defined('CACHE_PATH'))
    define('CACHE_PATH', ROOT_PATH . '/storage/cache');
if (!defined('LOGS_PATH'))
    define('LOGS_PATH', ROOT_PATH . '/storage/logs');
if (!defined('UPLOADS_PATH'))
    define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Crear directorios si no existen
foreach ([CACHE_PATH, LOGS_PATH, UPLOADS_PATH] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true); // @ para suprimir errores de permisos
    }
}

// Cargar configuración
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    if (defined('SESSION_NAME')) {
        session_name(SESSION_NAME);
    }
    session_start();
}

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

// Remover el prefijo de la aplicación si existe (solo para subdirectorios)
$basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', PUBLIC_PATH);
if (!empty($basePath) && $basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// CORRECCIÓN: Manejo de prefijos en producción y local
// 1. Remover /api si viene de la redirección del root .htaccess
if (strpos($requestUri, '/api/') === 0) {
    $requestUri = substr($requestUri, 4); // Remover /api
}

// 2. Manejo de subdirectorios (si aplica)
if (APP_ENV === 'production' && strpos($requestUri, '/colaboradores') === 0) {
    // Solo remover el prefijo si es una ruta general (como /dashboard) que no está agrupada
    // En las rutas de mod_colab ya tenemos el grupo /colaboradores
    // Si la URI ya empieza por /colaboradores y el router TIENE el grupo, lo dejamos
}

// Asegurar que empiece con /
$requestUri = '/' . ltrim($requestUri, '/');
file_put_contents(ROOT_PATH . '/debug_route.log', "URI: " . $requestUri . " METHOD: " . $requestMethod . "\n", FILE_APPEND);
// Router simple
try {
    // Cargar el router
    require_once APP_PATH . '/Core/Router.php';
    require_once APP_PATH . '/Core/Controller.php';

    $router = new App\Core\Router();

    // Cargar las rutas
    require_once ROOT_PATH . '/routes/web.php';

    // Despachar la ruta
    $router->dispatch($requestUri, $requestMethod);

} catch (Exception $e) {
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
