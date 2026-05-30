<?php
/**
 * ARATIO - Sistema de Gestión de Campaña Edison Giraldo
 * Archivo de Configuración Principal
 *
 * PRODUCCIÓN: edisongiraldo.com/aratio/
 * SERVIDOR:   157.173.208.254:65002
 * DB:         u577647812_aratio (Hostinger)
 * Última actualización: 2026-04-14
 */

// =============================================
// CONFIGURACIÓN DE ERRORES
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/home/u577647812/domains/edisongiraldo.com/public_html/php-errors.log');

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// =============================================
// DETECCIÓN DE ENTORNO
// =============================================
// localhost / 127.0.0.1 = desarrollo | else = producción Hostinger
// Detección de entorno: localhost / aratio.edisongiraldo.com = desarrollo | else = producción Hostinger
$currentHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($currentHost, ['localhost', 'aratio.localhost', 'edisongiraldo.localhost', '127.0.0.1', 'aratio.edisongiraldo.com']);

// Si estamos en CLI, verificar si es el entorno de Hostinger por la ruta absoluta
if (php_sapi_name() === 'cli') {
    $currentPath = realpath(__DIR__);
    if (strpos($currentPath, '/home/u577647812/') !== false) {
        $isLocal = false; // Estamos en el servidor de producción ejecutando CLI
    } else {
        $isLocal = true; // Estamos en PC local ejecutando CLI
    }
}

// =============================================
// CONFIGURACIÓN DE BASE DE DATOS
// =============================================
if ($isLocal) {
    // Desarrollo local — apunta a la misma DB de Hostinger para pruebas
    // Si estamos en Windows XAMPP, se conecta remoto a Hostinger
    define('DB_HOST', 'srv1540.hstgr.io'); 
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
define('DB_EXPECTED_SERVER', '157.173.208.254');
define('DB_CHARSET', 'utf8mb4');

// =============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// =============================================
define('APP_NAME', 'Aratio — Edison Giraldo');
define('APP_VERSION', '2.0.0');

// APP_SUBPATH dinámico según el HOST
// Si es el dominio personalizado, no hay subfolder. Si es localhost, es /aratio
$detectedSubpath = ($currentHost === 'aratio.edisongiraldo.com') ? '' : '/aratio';
if (!$isLocal) $detectedSubpath = '/aratio'; // En producción siempre es /aratio

define('APP_SUBPATH', $detectedSubpath);

define('APP_URL',
    $isLocal
        ? 'http://' . $currentHost . APP_SUBPATH
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
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// =============================================
// CONFIGURACIÓN DE PAGINACIÓN
// =============================================
define('ITEMS_PER_PAGE', 20);

// =============================================
// COLORES CORPORATIVOS — Edison Giraldo
// =============================================
define('COLOR_PRIMARY', 'hsla(220, 93%, 50%, 1.00)');   // Azul Edison
define('COLOR_SECONDARY', 'hsla(45, 100%, 50%, 1.00)'); // Dorado/Amarillo Premium
define('COLOR_ACCENT', 'hsla(220, 93%, 30%, 1.00)');    // Azul Oscuro Accent

// =============================================
// CONFIGURACIÓN DE EMAIL
// =============================================
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'admin@aratio.mrmtech.net');
define('SMTP_PASS', 'tu_password_email'); // Configurar después
define('SMTP_FROM', 'admin@aratio.mrmtech.net');
define('SMTP_FROM_NAME', 'Sistema Aratio — Edison Giraldo');

// Marcador para saber que este archivo se cargó correctamente
define('ROOT_CONFIG_LOADED', true);
