<?php
/**
 * Mostrar últimos errores
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Logs de Error</h1>";
echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 20px; overflow-x: auto; white-space: pre-wrap;'>";

$logPaths = [
    '../storage/logs/app.log' => 'Application Log',
    '/home/u156469157/domains/aratio.mrmtech.net/logs/error_log' => 'Server Error Log',
];

foreach ($logPaths as $path => $title) {
    echo "\n========== {$title} ==========\n";
    
    if (file_exists($path)) {
        $lines = file($path);
        $last = array_slice($lines, -100);
        echo htmlspecialchars(implode('', $last));
    } else {
        echo "[ No existe: {$path} ]\n";
    }
}

echo "</pre>";
