<?php
/**
 * Router para servidor de desarrollo PHP (php -S)
 * Emula las reglas de .htaccess
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

// Si el archivo existe físicamente, servirlo
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Emulación de .htaccess
if ($uri === '/registro-simpatizante' || $uri === '/registro-simpatizante/') {
    // Usar archivos standalone descargados de producción
    require 'registro_simpatizante.php';
} elseif ($uri === '/registro-lider' || $uri === '/registro-lider/') {
    require 'registro-lider.php';
} elseif ($uri === '/login.php') {
    require 'login.php';
} elseif (preg_match('/^\/(login|dashboard|portal|mapa-puestos|configuracion|ayuda)/', $uri)) {
    $_GET['page'] = ltrim($uri, '/');
    require 'index.php';
} elseif (preg_match('/^\/api\/colaboradores/', $uri)) {
    require 'mod_colab/public/index.php';
} elseif (preg_match('/^\/territorios/', $uri)) {
    require 'mod_colab/public/index.php';
} else {
    // Si es la raíz o no coincide con nada anterior
    if ($uri === '/' || $uri === '') {
        readfile('index.html');
        exit;
    } else {
        // Para cualquier otra ruta, usar index.php como fallback (comportamiento de ErrorDocument local)
        require 'index.php';
    }
}
