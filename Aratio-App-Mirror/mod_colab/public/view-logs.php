<?php
/**
 * Ver logs de la aplicación en tiempo real
 */

header('Content-Type: text/html; charset=utf-8');

$logFile = dirname(__DIR__) . '/storage/logs/app.log';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Application Logs</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #0f172a; color: #e2e8f0; }
        .debug { color: #6b7280; }
        .info { color: #3b82f6; }
        .warning { color: #f59e0b; }
        .error { color: #ef4444; }
        .success { color: #10b981; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        .log-entry { margin: 5px 0; padding: 8px; background: #1e293b; border-radius: 3px; }
        .highlight { background: #fef3c7; color: #92400e; font-weight: bold; }
        button { padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 10px 5px 10px 0; }
    </style>
</head>
<body>
<h1>📋 Application Logs</h1>

<div style="margin: 20px 0;">
    <button onclick="location.reload()">↻ Refrescar</button>
    <button onclick="filterLogs('AUTH')">🔒 Solo AUTH</button>
    <button onclick="filterLogs('DEBUG')">🐛 Solo DEBUG</button>
    <button onclick="filterLogs('ERROR')">❌ Solo ERROR</button>
    <button onclick="filterLogs('')">📄 Todos</button>
</div>

<?php
if (!file_exists($logFile)) {
    echo "<p class='error'>❌ Archivo de log no existe: $logFile</p>";
    echo "<p>El directorio storage/logs debe tener permisos de escritura.</p>";
    exit;
}

// Leer últimas 200 líneas
$lines = file($logFile);
$lines = array_slice($lines, -200);

echo "<p>Mostrando últimas <strong>" . count($lines) . "</strong> líneas del log</p>";
echo "<p>Archivo: <code>$logFile</code></p>";

echo "<div id='log-container'>";

foreach ($lines as $line) {
    $line = htmlspecialchars($line);

    // Colorear según el nivel
    $class = 'log-entry';
    if (strpos($line, '[DEBUG]') !== false) {
        $class .= ' debug';
    } elseif (strpos($line, '[ERROR]') !== false) {
        $class .= ' error';
    } elseif (strpos($line, '[WARNING]') !== false) {
        $class .= ' warning';
    } elseif (strpos($line, '[INFO]') !== false) {
        $class .= ' info';
    }

    // Resaltar líneas importantes
    if (strpos($line, 'DEBUG AUTH') !== false ||
        strpos($line, 'session') !== false ||
        strpos($line, 'login') !== false) {
        $class .= ' highlight';
    }

    echo "<div class='$class'><pre>$line</pre></div>";
}

echo "</div>";
?>

<script>
function filterLogs(keyword) {
    const entries = document.querySelectorAll('.log-entry');
    entries.forEach(entry => {
        if (keyword === '' || entry.textContent.includes(keyword)) {
            entry.style.display = 'block';
        } else {
            entry.style.display = 'none';
        }
    });
}
</script>

<div style="margin-top: 40px; padding: 15px; background: #1e293b; border-radius: 5px;">
<h2>🔍 Qué buscar:</h2>
<ul>
<li><strong>[DEBUG AUTH]</strong> - Mensajes del AuthMiddleware sobre sesiones</li>
<li><strong>session_exists</strong> - ¿Hay sesión iniciada?</li>
<li><strong>user_exists</strong> - ¿Existe $_SESSION['user']?</li>
<li><strong>session_token</strong> - ¿Se guardó el token?</li>
<li><strong>verify_result</strong> - ¿El token es válido en BD?</li>
</ul>
</div>

</body>
</html>
