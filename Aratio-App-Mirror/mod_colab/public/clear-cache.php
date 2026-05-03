<?php
/**
 * Script temporal para limpiar OPcache
 * Eliminar después de usar
 */

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpiado exitosamente<br>";
} else {
    echo "⚠️ OPcache no está habilitado<br>";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✅ APC cache limpiado<br>";
}

echo "<br><strong>Cache del sistema limpiado.</strong><br>";
echo "<a href='/'>Ir al inicio</a> | <a href='/login'>Ir al login</a>";
