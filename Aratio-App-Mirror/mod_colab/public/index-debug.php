<?php
/**
 * Index de Debug - Muestra errores detallados
 */

// Habilitar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Debug Index</h1>";
echo "<pre>";

try {
    echo "1. Configurando UTF-8... ";
    header('Content-Type: text/html; charset=UTF-8');
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
    echo "✓\n";

    echo "2. Definiendo constantes... ";
    define('ROOT_PATH', dirname(__DIR__));
    define('APP_PATH', ROOT_PATH . '/src');
    define('CONFIG_PATH', ROOT_PATH . '/config');
    define('PUBLIC_PATH', ROOT_PATH . '/public');
    define('CACHE_PATH', ROOT_PATH . '/storage/cache');
    define('LOGS_PATH', ROOT_PATH . '/storage/logs');
    define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
    echo "✓\n";

    echo "3. Verificando directorios...\n";
    foreach ([CACHE_PATH, LOGS_PATH, UPLOADS_PATH] as $dir) {
        echo "   - $dir: ";
        if (is_dir($dir)) {
            echo "✓ existe\n";
        } else {
            echo "✗ NO EXISTE - Intentando crear... ";
            if (@mkdir($dir, 0755, true)) {
                echo "✓ creado\n";
            } else {
                echo "✗ ERROR: No se pudo crear\n";
            }
        }
    }

    echo "4. Cargando config.php... ";
    require_once CONFIG_PATH . '/config.php';
    echo "✓\n";

    echo "5. Cargando constants.php... ";
    require_once CONFIG_PATH . '/constants.php';
    echo "✓\n";

    echo "6. Cargando database.php... ";
    require_once CONFIG_PATH . '/database.php';
    echo "✓\n";

    echo "7. Iniciando sesión... ";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "✓ (ID: " . session_id() . ")\n";

    echo "8. Configurando headers de seguridad... ";
    use App\Utils\Security;
    Security::setSecurityHeaders();
    echo "✓\n";

    echo "9. Verificando ambiente... ";
    echo "APP_ENV = " . APP_ENV . "\n";

    echo "10. Obteniendo URI... ";
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    echo "✓\n";
    echo "    Request URI: $requestUri\n";
    echo "    Method: $requestMethod\n";

    echo "11. Procesando base path... ";
    $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', PUBLIC_PATH);
    echo "Base path: '$basePath'\n";

    echo "12. Cargando Router... ";
    require_once APP_PATH . '/Core/Router.php';
    require_once APP_PATH . '/Core/Controller.php';
    $router = new App\Core\Router();
    echo "✓\n";

    echo "13. Cargando rutas... ";
    require_once ROOT_PATH . '/routes/web.php';
    echo "✓\n";

    echo "14. Despachando ruta '$requestUri'... ";
    ob_start();
    $router->dispatch($requestUri, $requestMethod);
    $output = ob_get_clean();
    echo "✓\n";

    echo "</pre>";
    echo $output;

} catch (Exception $e) {
    echo "\n\n❌ ERROR:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
