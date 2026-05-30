<?php
/**
 * RouteHelper - Manejo dinámico de rutas para Aratio
 * Evita problemas de rutas estáticas al cambiar entre ambientes (Local/Desarrollo/Producción)
 */

class RouteHelper {
    private static $baseUrl = null;

    /**
     * Obtiene la URL base del sitio de forma automática
     */
    public static function getBaseUrl() {
        if (self::$baseUrl === null) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // Detectar subcarpeta si existe (ej: /aratiopro/)
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $dirName = dirname($scriptName);
            
            // Limpiar barras duplicadas y asegurar que termine en /
            $baseDir = rtrim($dirName, '/\\') . '/';
            
            // Si estamos en la raíz, evitar que sea solo / si se concatena luego
            if ($baseDir === '//' || $baseDir === '\\') $baseDir = '/';

            self::$baseUrl = $protocol . $host . $baseDir;
        }
        return self::$baseUrl;
    }

    /**
     * Genera una URL absoluta para una ruta relativa
     */
    public static function url($path = '') {
        $path = ltrim($path, '/');
        return self::getBaseUrl() . $path;
    }

    /**
     * Genera una URL para un asset (CSS, JS, Imágenes)
     */
    public static function asset($path) {
        return self::url('assets/' . ltrim($path, '/'));
    }
}

/**
 * Funciones globales para facilidad de uso
 */
if (!function_exists('url')) {
    function url($path = '') {
        return RouteHelper::url($path);
    }
}

if (!function_exists('asset')) {
    function asset($path) {
        return RouteHelper::asset($path);
    }
}
