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
            
            // Ruta física de este archivo (en /includes/)
            $thisFile = str_replace('\\', '/', __FILE__);
            // Ruta física de la raíz del servidor
            $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
            
            // La raíz del proyecto es el padre de /includes/
            $projectRoot = dirname(dirname($thisFile));
            
            if ($docRoot && stripos($projectRoot, $docRoot) === 0) {
                // El baseDir es la diferencia entre docRoot y projectRoot
                $baseDir = '/' . ltrim(substr($projectRoot, strlen($docRoot)), '/');
                $baseDir = rtrim($baseDir, '/') . '/';
            } else {
                // Fallback: intentar inferir desde el script actual si realpath falla
                $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
                $scriptFilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
                
                if ($scriptFilename && $scriptName && stripos($scriptFilename, $projectRoot) === 0) {
                    $relativeScript = substr($scriptFilename, strlen($projectRoot));
                    $baseDir = substr($scriptName, 0, strlen($scriptName) - strlen($relativeScript));
                    $baseDir = rtrim($baseDir, '/') . '/';
                } else {
                    $baseDir = '/aratio/'; // Last resort fallback for this project
                }
            }
            
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
