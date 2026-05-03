<?php
// Limpiar cache de OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache limpiado correctamente\n";
} else {
    echo "OPcache no esta disponible\n";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache limpiado\n";
}

echo "Cache limpiado. Probar: https://aratio.mrmtech.net/index.php?page=portal_login\n";
