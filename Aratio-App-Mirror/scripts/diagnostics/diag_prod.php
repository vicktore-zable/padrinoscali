<?php
header('Content-Type: text/plain');
$files = [
    'pages/colaboradores.php',
    'pages/eventos.php',
    'registro_asistencia.php',
    'index.php'
];

echo "DIAGNÓSTICO DE ARCHIVOS EN PRODUCCIÓN\n";
echo "====================================\n\n";

foreach ($files as $f) {
    echo "Archivo: $f\n";
    if (file_exists($f)) {
        echo "  Existe: SÍ\n";
        echo "  Tamaño: " . filesize($f) . " bytes\n";
        echo "  MD5: " . md5_file($f) . "\n";
        echo "  Última mod: " . date("Y-m-d H:i:s", filemtime($f)) . "\n";
        
        $content = file_get_contents($f);
        if (strpos($content, 'municipio_raw') !== false) {
            echo "  Contiene 'municipio_raw': SÍ\n";
        } else {
            echo "  Contiene 'municipio_raw': NO (!!!)\n";
        }
    } else {
        echo "  Existe: NO\n";
    }
    echo "\n";
}

echo "RUTA ACTUAL: " . getcwd() . "\n";
echo "OPCACHE STATUS: " . (function_exists('opcache_get_status') ? 'Activado' : 'No disponible') . "\n";
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "OPCACHE RESET: ÉXITO\n";
    } else {
        echo "OPCACHE RESET: FALLÓ\n";
    }
}
?>
