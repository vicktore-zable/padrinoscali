<?php
/**
 * Mostrar errores de PHP
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Errors</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .error { color: #ef4444; background: #7f1d1d; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #0f172a; padding: 15px; border-radius: 5px; white-space: pre-wrap; }
    </style>
</head>
<body>
<h1>🔍 Mostrar Errores PHP</h1>

<?php
echo "<h2>1. Cargar config.php</h2>";
try {
    require_once dirname(__DIR__) . '/config/config.php';
    echo "<p style='color: #10b981;'>✓ config.php cargado</p>";
} catch (Throwable $e) {
    echo "<div class='error'>";
    echo "<strong>Error en config.php:</strong><br>";
    echo $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    exit;
}

echo "<h2>2. Cargar database.php</h2>";
try {
    require_once dirname(__DIR__) . '/config/database.php';
    echo "<p style='color: #10b981;'>✓ database.php cargado</p>";
} catch (Throwable $e) {
    echo "<div class='error'>";
    echo "<strong>Error en database.php:</strong><br>";
    echo $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
    exit;
}

echo "<h2>3. Probar Usuario.php</h2>";
try {
    $usuario = new \App\Models\Usuario();
    echo "<p style='color: #10b981;'>✓ Usuario.php cargado</p>";
} catch (Throwable $e) {
    echo "<div class='error'>";
    echo "<strong>Error en Usuario.php:</strong><br>";
    echo $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";

    echo "<h3>Posibles causas:</h3>";
    echo "<ul>";
    echo "<li>Error de sintaxis en Usuario.php después de aplicar fixes</li>";
    echo "<li>Falta alguna clase o namespace</li>";
    echo "<li>Problema con la base de datos</li>";
    echo "</ul>";
    exit;
}

echo "<h2>4. Ver últimas líneas del log de errores</h2>";
$logFile = dirname(__DIR__) . '/storage/logs/app.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -30);
    echo "<pre>";
    foreach ($lastLines as $line) {
        if (strpos($line, 'ERROR') !== false) {
            echo "<span style='color: #ef4444;'>$line</span>";
        } else {
            echo $line;
        }
    }
    echo "</pre>";
} else {
    echo "<p>No existe archivo de log</p>";
}

echo "<h2 style='color: #10b981;'>✅ Todo funciona correctamente</h2>";
?>

</body>
</html>
