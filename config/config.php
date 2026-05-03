<?php
/**
 * ARATIO - Sistema de Gestión de Campaña Edison Giraldo
 * Archivo de Configuración Principal
 *
 * PRODUCCIÓN: edisongiraldo.com/aratio/
 * SERVIDOR:   157.173.208.254:65002
 * DB:         u577647812_aratio (Hostinger)
 * SSH:        Clave ED25519 (sin password)
 * Última actualización: 2026-05-02
 */

// =============================================
// CONFIGURACIÓN DE ERRORES
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/home/u577647812/domains/edisongiraldo.com/public_html/php-errors.log');

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// =============================================
// DETECCIÓN DE ENTORNO
// =============================================
// localhost / 127.0.0.1 = desarrollo | else = producción Hostinger
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'aratio.localhost', '127.0.0.1'])
           || php_sapi_name() === 'cli';

// =============================================
// CONFIGURACIÓN DE BASE DE DATOS
// =============================================
if ($isLocal) {
    // Desarrollo local — apunta a la misma DB de Hostinger para pruebas
    define('DB_HOST', 'auth-db690.hstgr.io');
    define('DB_NAME', 'u577647812_aratio');
    define('DB_USER', 'u577647812_aratio');
    define('DB_PASS', 'v6xSHUWhjrxE');
} else {
    // Producción — edisongiraldo.com (Hostinger usa localhost internamente)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u577647812_aratio');
    define('DB_USER', 'u577647812_aratio');
    define('DB_PASS', 'v6xSHUWhjrxE');
}
define('DB_CHARSET', 'utf8mb4');

// =============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// =============================================
define('APP_NAME', 'Aratio — Edison Giraldo');
define('APP_VERSION', '2.0.0');
define('APP_SUBPATH', '/aratio'); // Subdirectorio en producción

define('APP_URL',
    $isLocal
        ? 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8012') . '/aratio'
        : 'https://edisongiraldo.com/aratio'
);
define('APP_ENV', $isLocal ? 'development' : 'production');

// =============================================
// CONFIGURACIÓN DE SESIONES
// =============================================
define('SESSION_LIFETIME', 7200); // 2 horas en segundos
define('SESSION_NAME', 'ARATIO_EDISON_SESSION');

// =============================================
// CONFIGURACIÓN DE SEGURIDAD
// =============================================
define('JWT_SECRET', '5f8d2a1c9e3b476085142398561234567890abcdef1234567890abcdef123456'); // CAMBIAR EN PRODUCCIÓN
define('BACKUP_KEY', 'a18f4e2b8c9d0e1f2a3b4c5d6e7f8g9h');
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutos

// =============================================
// CONFIGURACIÓN DE ARCHIVOS
// =============================================
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// =============================================
// CONFIGURACIÓN DE PAGINACIÓN
// =============================================
define('ITEMS_PER_PAGE', 20);

// =============================================
// COLORES CORPORATIVOS — Edison Giraldo
// =============================================
define('COLOR_PRIMARY', 'hsla(220, 93%, 50%, 1.00)');   // Magenta
define('COLOR_SECONDARY', '#FFD700'); // Dorado

// =============================================
// CONFIGURACIÓN DE EMAIL
// =============================================
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'admin@aratio.mrmtech.net');
define('SMTP_PASS', 'tu_password_email'); // Configurar después
define('SMTP_FROM', 'admin@aratio.mrmtech.net');
define('SMTP_FROM_NAME', 'Sistema Aratio — Edison Giraldo');

// =============================================
// RUTAS DEL SISTEMA
// =============================================
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('API_PATH', BASE_PATH . '/api');
define('ASSETS_PATH', BASE_PATH . '/assets');

// =============================================
// AUTOLOADER
// =============================================
spl_autoload_register(function ($class) {
    $file = INCLUDES_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
    // Autocarga para SimpleXLSX
    if ($class === 'Shuchkin\SimpleXLSX') {
        require_once INCLUDES_PATH . '/SimpleXLSX.php';
    }
    if ($class === 'Shuchkin\SimpleXLSXGen') {
        require_once INCLUDES_PATH . '/SimpleXLSXGen.php';
    }
});

// =============================================
// FUNCIONES GLOBALES
// =============================================

/**
 * Obtener conexión a la base de datos
 */
function getDB()
{
    return DatabaseManager::getInstance()->getConnection();
}

/**
 * Sanitizar entrada
 */
function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generar token aleatorio
 */
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Hash de password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verificar password
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Respuesta JSON estándar
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtener usuario de sesión actual
 */
function getSessionUser()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? AND estado = 'activo'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Verificar autenticación — redirige a /aratio/login.php
 */
function requireAuth()
{
    if (!isset($_SESSION['user_id'])) {
        if (isAjaxRequest()) {
            jsonResponse(['success' => false, 'message' => 'No autorizado'], 401);
        } else {
            header('Location: ' . APP_SUBPATH . '/login.php');
            exit;
        }
    }
}

/**
 * Verificar si es petición AJAX
 */
function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * GET helper
 */
function get($key, $default = null)
{
    return $_GET[$key] ?? $default;
}

/**
 * POST helper
 */
function post($key, $default = null)
{
    return $_POST[$key] ?? $default;
}

/**
 * Obtener input JSON del body
 */
function getJsonInput()
{
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y')
{
    if (empty($date)) return '';
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Formatear moneda (pesos colombianos)
 */
function formatCurrency($amount)
{
    return '$' . number_format($amount, 0, ',', '.');
}

/**
 * Subir archivo al servidor
 */
function uploadFile($file, $subfolder = '')
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Parámetros inválidos'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error al subir archivo'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'El archivo es demasiado grande'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    $uploadDir = UPLOAD_PATH . $subfolder;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Error al mover archivo'];
    }
    return [
        'success'  => true,
        'filename' => $filename,
        'path'     => $filepath,
        'url'      => APP_SUBPATH . '/uploads/' . $subfolder . '/' . $filename
    ];
}

// =============================================
// HEADERS DE SEGURIDAD
// =============================================
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Configuración de sesión
ini_set('session.cookie_httponly', '1');
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_path', APP_SUBPATH . '/');
} else {
    ini_set('session.cookie_secure', '0');
}
ini_set('session.cookie_samesite', 'Strict');

// Iniciar sesión
session_name(SESSION_NAME);
session_start();

// =============================================
// SISTEMA DE CACHÉ Y RATE LIMITER
// =============================================
require_once __DIR__ . '/../includes/DatabaseManager.php';
require_once __DIR__ . '/../includes/CacheManager.php';
require_once __DIR__ . '/../includes/RateLimiter.php';

$cacheDir     = __DIR__ . '/../cache';
$cacheEnabled = is_dir($cacheDir) && is_writable($cacheDir);

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
    $cacheEnabled = is_dir($cacheDir) && is_writable($cacheDir);
}

if (!isset($GLOBALS['cache'])) {
    $GLOBALS['cache'] = new CacheManager($cacheDir, $cacheEnabled);
}

$rateLimitDir = $cacheDir . '/rate_limit';
if ($cacheEnabled && !is_dir($rateLimitDir)) {
    @mkdir($rateLimitDir, 0755, true);
}

if (!isset($GLOBALS['rateLimiter'])) {
    try {
        $GLOBALS['rateLimiter'] = new RateLimiter($rateLimitDir, 100, 60);
        if (!$cacheEnabled) {
            $GLOBALS['rateLimiter']->setEnabled(false);
        }
    } catch (Exception $e) {
        $GLOBALS['rateLimiter'] = new RateLimiter('/tmp', 100, 60);
        $GLOBALS['rateLimiter']->setEnabled(false);
        error_log("RateLimiter init error: " . $e->getMessage());
    }
}

// Helpers caché
if (!function_exists('cache_remember')) {
    function cache_remember($key, $callback, $ttl = 600) {
        if (!isset($GLOBALS['cache'])) return $callback();
        return $GLOBALS['cache']->remember($key, $callback, $ttl);
    }
}
if (!function_exists('cache_invalidate')) {
    function cache_invalidate($pattern) {
        if (!isset($GLOBALS['cache'])) return 0;
        return $GLOBALS['cache']->invalidatePattern($pattern);
    }
}
if (!function_exists('rate_limit_check')) {
    function rate_limit_check($identifier) {
        if (!isset($GLOBALS['rateLimiter'])) return ['allowed' => true, 'remaining' => 999];
        return $GLOBALS['rateLimiter']->check($identifier);
    }
}

// TTL de caché
define('CACHE_TTL_DEPARTAMENTOS', 86400); // 24h
define('CACHE_TTL_MUNICIPIOS',    86400); // 24h
define('CACHE_TTL_TERRITORIOS',   86400); // 24h
define('CACHE_TTL_CAMPANAS',        600); // 10m
define('CACHE_TTL_COLABORADORES',   300); // 5m
define('CACHE_TTL_EVENTOS',         180); // 3m
define('CACHE_TTL_DONACIONES',      300); // 5m
define('CACHE_TTL_DASHBOARD',       120); // 2m
