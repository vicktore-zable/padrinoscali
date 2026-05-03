<?php
// Diagnostico completo del archivo portal_login.php
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTICO COMPLETO ===\n\n";

// 1. Verificar contenido del archivo
$filePath = '/home/u156469157/domains/aratio.mrmtech.net/public_html/pages/portal_login.php';
echo "1. Contenido de portal_login.php:\n";
echo str_repeat('-', 50) . "\n";
echo file_get_contents($filePath);
echo "\n" . str_repeat('-', 50) . "\n\n";

// 2. Verificar si mod_lider existe
echo "2. Verificando mod_lider:\n";
$modLiderPath = '/home/u156469157/domains/aratio.mrmtech.net/public_html/mod_lider';
echo "   - Directorio mod_lider existe: " . (is_dir($modLiderPath) ? 'SI' : 'NO') . "\n";
echo "   - bootstrap.php existe: " . (file_exists($modLiderPath . '/src/bootstrap.php') ? 'SI' : 'NO') . "\n";
echo "   - config.php existe: " . (file_exists($modLiderPath . '/config/config.php') ? 'SI' : 'NO') . "\n";
echo "   - database.php existe: " . (file_exists($modLiderPath . '/config/database.php') ? 'SI' : 'NO') . "\n";
echo "   - PortalAuthController.php existe: " . (file_exists($modLiderPath . '/src/Controllers/PortalAuthController.php') ? 'SI' : 'NO') . "\n";
echo "\n";

// 3. Verificar mod_colab
echo "3. Verificando mod_colab:\n";
$modColabPath = '/home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab';
echo "   - Directorio mod_colab existe: " . (is_dir($modColabPath) ? 'SI' : 'NO') . "\n";
echo "   - PortalAuthController.php existe: " . (file_exists($modColabPath . '/src/Controllers/PortalAuthController.php') ? 'SI' : 'NO') . "\n";
echo "\n";

// 4. Estado de OPcache
echo "4. Estado de OPcache:\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    echo "   - OPcache habilitado: SI\n";
    echo "   - Scripts en cache: " . count($status['scripts'] ?? []) . "\n";
    
    // Buscar scripts de portal
    foreach ($status['scripts'] ?? [] as $path => $info) {
        if (strpos($path, 'portal_login') !== false) {
            echo "   - portal_login.php en cache: SI\n";
            echo "     Path: $path\n";
            echo "     Timestamp: " . date('Y-m-d H:i:s', $info['timestamp'] ?? 0) . "\n";
        }
    }
} else {
    echo "   - OPcache habilitado: NO\n";
}
echo "\n";

// 5. Limpiar cache
echo "5. Limpiando cache:\n";
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "   - opcache_reset(): " . ($result ? 'OK' : 'FAIL') . "\n";
}
if (function_exists('opcache_invalidate')) {
    $result = opcache_invalidate($filePath, true);
    echo "   - opcache_invalidate(portal_login.php): " . ($result ? 'OK' : 'NO CACHEADO') . "\n";
}
echo "\n";

echo "=== FIN DEL DIAGNOSTICO ===\n";
