<?php
/**
 * Ver logs de error de la aplicación
 */

echo "<h1>Logs de Error</h1>";
echo "<pre>";

// Logs de Laravel/PHP
$logPaths = [
    '../storage/logs/app.log' => 'Application Log',
    '../storage/logs/security.log' => 'Security Log',
    '/home/u156469157/domains/aratio.mrmtech.net/logs/error_log' => 'Server Error Log (si existe)',
];

foreach ($logPaths as $path => $title) {
    echo "\n========== {$title} ==========\n";

    if (file_exists($path)) {
        $content = file_get_contents($path);
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -100); // Últimas 100 líneas
        echo implode("\n", $lastLines);
    } else {
        echo "[ Archivo no existe: {$path} ]\n";
    }
}

echo "\n========== PHP Error Log ==========\n";
$phpErrorLog = ini_get('error_log');
echo "PHP error_log: {$phpErrorLog}\n";
if ($phpErrorLog && file_exists($phpErrorLog)) {
    $content = file_get_contents($phpErrorLog);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -50);
    echo implode("\n", $lastLines);
}

echo "</pre>";
