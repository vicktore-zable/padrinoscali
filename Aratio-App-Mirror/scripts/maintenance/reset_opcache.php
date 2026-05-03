<?php
// Reset completo de OPcache
header('Content-Type: text/plain; charset=utf-8');

echo "=== RESET OPCACHE ===\n\n";

// Mostrar estado actual
echo "OPcache habilitado: " . (function_exists('opcache_get_status') ? 'Sí' : 'No') . "\n";

if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status) {
        echo "Scripts en cache: " . count($status['scripts'] ?? []) . "\n";
        
        // Mostrar scripts relacionados con portal
        foreach ($status['scripts'] ?? [] as $path => $info) {
            if (strpos($path, 'portal') !== false || strpos($path, 'mod_lider') !== false || strpos($path, 'mod_colab') !== false) {
                echo "  - $path\n";
            }
        }
    }
}

// Reset completo
echo "\n=== EJECUTANDO RESET ===\n";
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "opcache_reset(): " . ($result ? 'OK' : 'FAIL') . "\n";
}

// Invalidar archivos específicos
$filesToInvalidate = [
    '/home/u156469157/domains/aratio.mrmtech.net/public_html/pages/portal_login.php',
    '/home/u156469157/domains/aratio.mrmtech.net/public_html/pages/portal_landing.php',
    '/home/u156469157/domains/aratio.mrmtech.net/public_html/index.php',
    '/home/u156469157/domains/aratio.mrmtech.net/public_html/mod_lider/src/bootstrap.php',
];

if (function_exists('opcache_invalidate')) {
    foreach ($filesToInvalidate as $file) {
        $result = opcache_invalidate($file, true);
        echo "opcache_invalidate($file): " . ($result ? 'OK' : 'NO CACHEADO') . "\n";
    }
}

echo "\n=== VERIFICACIÓN ===\n";
echo "Contenido de portal_login.php:\n";
echo file_get_contents('/home/u156469157/domains/aratio.mrmtech.net/public_html/pages/portal_login.php');
echo "\n\n=== FIN ===\n";
