<?php
/**
 * SNIPPET PARA AGREGAR A config/config.php
 * 
 * Copia este código y pégalo AL FINAL de tu archivo config/config.php
 * Este código integra el sistema de optimización de BD
 */

// ============================================
// SISTEMA DE OPTIMIZACIÓN DE BASE DE DATOS
// ============================================

// Cargar clases de optimización
require_once __DIR__ . '/../includes/DatabaseManager.php';
require_once __DIR__ . '/../includes/CacheManager.php';
require_once __DIR__ . '/../includes/RateLimiter.php';

/**
 * Función optimizada para obtener conexión a BD
 * REEMPLAZA la función getDB() existente si la tienes
 */
if (!function_exists('getDB')) {
    function getDB()
    {
        return DatabaseManager::getInstance()->getConnection();
    }
}

/**
 * Instancias globales de caché y rate limiter
 * Accesibles desde cualquier parte de la aplicación
 */
if (!isset($GLOBALS['cache'])) {
    $GLOBALS['cache'] = new CacheManager(
        __DIR__ . '/../cache',  // Directorio de caché
        true                     // Habilitado
    );
}

if (!isset($GLOBALS['rateLimiter'])) {
    $GLOBALS['rateLimiter'] = new RateLimiter(
        __DIR__ . '/../cache/rate_limit',  // Directorio de rate limit
        60,   // Máximo 60 requests
        60    // Por minuto
    );
}

/**
 * Función helper para caché
 * Uso: $data = cache_remember('key', function() { return getData(); }, 600);
 */
if (!function_exists('cache_remember')) {
    function cache_remember($key, $callback, $ttl = 600)
    {
        return $GLOBALS['cache']->remember($key, $callback, $ttl);
    }
}

/**
 * Función helper para invalidar caché
 * Uso: cache_invalidate('colaboradores_*');
 */
if (!function_exists('cache_invalidate')) {
    function cache_invalidate($pattern)
    {
        return $GLOBALS['cache']->invalidatePattern($pattern);
    }
}

/**
 * Función helper para rate limiting
 * Uso: rate_limit_check($userId);
 */
if (!function_exists('rate_limit_check')) {
    function rate_limit_check($identifier)
    {
        return $GLOBALS['rateLimiter']->check($identifier);
    }
}

// ============================================
// CONFIGURACIÓN DE TTL POR TIPO DE DATO
// ============================================

define('CACHE_TTL_DEPARTAMENTOS', 86400);  // 24 horas (datos estáticos)
define('CACHE_TTL_MUNICIPIOS', 86400);     // 24 horas
define('CACHE_TTL_TERRITORIOS', 86400);    // 24 horas
define('CACHE_TTL_CAMPANAS', 600);         // 10 minutos
define('CACHE_TTL_COLABORADORES', 300);    // 5 minutos
define('CACHE_TTL_EVENTOS', 180);          // 3 minutos
define('CACHE_TTL_DONACIONES', 300);       // 5 minutos
define('CACHE_TTL_DASHBOARD', 120);        // 2 minutos

// ============================================
// LOGGING DE OPTIMIZACIÓN (OPCIONAL)
// ============================================

// Descomentar para ver estadísticas en logs
/*
register_shutdown_function(function() {
    $stats = DatabaseManager::getInstance()->getStats();
    error_log("DB Stats: " . json_encode($stats));
});
*/

// ============================================
// FIN DE OPTIMIZACIÓN
// ============================================
