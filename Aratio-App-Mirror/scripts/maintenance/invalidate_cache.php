<?php
// Invalidar cache especificamente para portal_login.php
$files = [
    '/home/u156469157/domains/aratio.mrmtech.net/public_html/pages/portal_login.php',
    '/home/u156469157/domains/aratio.mrmtech.net/public_html/pages/portal_landing.php',
    '/home/u156469157/domains/aratio.mrmtech.net/public_html/index.php',
];

echo "<h1>Invalidando cache de archivos</h1>";

if (function_exists('opcache_invalidate')) {
    foreach ($files as $file) {
        if (opcache_invalidate($file, true)) {
            echo "<p style='color:green'>Cache invalidado: $file</p>";
        } else {
            echo "<p style='color:orange'>No se pudo invalidar: $file</p>";
        }
    }
} else {
    echo "<p style='color:red'>OPcache no disponible</p>";
}

// Mostrar contenido del archivo portal_login.php
echo "<h2>Contenido de portal_login.php:</h2>";
echo "<pre>";
echo htmlspecialchars(file_get_contents('/home/u156469157/domains/aratio.mrmtech.net/public_html/pages/portal_login.php'));
echo "</pre>";
