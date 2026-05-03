<?php
/**
 * Fix adicional: getActiveSessions() también usa NOW()
 */

header('Content-Type: text/html; charset=utf-8');

$file = dirname(__DIR__) . '/src/Models/Usuario.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix getActiveSessions</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        pre { background: #0f172a; padding: 15px; border-radius: 5px; }
        button { padding: 15px 30px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; }
    </style>
</head>
<body>
<h1>🔧 Fix getActiveSessions()</h1>

<h2>Problema Adicional Encontrado:</h2>
<pre class="error">
getActiveSessions() - Línea 579:
AND expira_en > NOW()

Esto causa que /profile y otras rutas fallen porque NOW() no coincide con UTC_TIMESTAMP()
</pre>

<h2>Solución:</h2>
<pre class="success">
Cambiar de: AND expira_en > NOW()
Cambiar a:   AND expira_en > UTC_TIMESTAMP()
</pre>

<?php
if (!isset($_POST['apply'])) {
    echo "<button onclick='document.getElementById(\"form\").submit()'>🔧 Aplicar Fix</button>";
    echo "<form id='form' method='POST'><input type='hidden' name='apply' value='1'></form>";
} else {
    if (!file_exists($file)) {
        echo "<p class='error'>❌ Archivo no encontrado</p>";
        exit;
    }

    $content = file_get_contents($file);

    // Backup
    $backupFile = $file . '.backup_getactivesessions_' . date('YmdHis');
    copy($file, $backupFile);
    echo "<p class='success'>✓ Backup: $backupFile</p>";

    // Fix en getActiveSessions
    $oldCode = "AND expira_en > NOW()";
    $newCode = "AND expira_en > UTC_TIMESTAMP()";

    // Verificar si ya está corregido
    if (strpos($content, "AND expira_en > UTC_TIMESTAMP()") !== false) {
        echo "<p class='success'>✅ Ya estaba corregido</p>";
    } else if (strpos($content, $oldCode) !== false) {
        $content = str_replace($oldCode, $newCode, $content);
        file_put_contents($file, $content);
        echo "<p class='success'>✅ Fix aplicado correctamente</p>";

        echo "<h2>Cambio realizado:</h2>";
        echo "<pre class='error'>ANTES: $oldCode</pre>";
        echo "<pre class='success'>DESPUÉS: $newCode</pre>";
    } else {
        echo "<p class='error'>❌ No se encontró el código a reemplazar</p>";
    }

    echo "<h2>✅ Próximo Paso:</h2>";
    echo "<p>Ahora <strong>resetea las sesiones</strong>:</p>";
    echo "<p><a href='/reset-all-sessions.php' style='color: #3b82f6; font-size: 18px;'>→ Reset All Sessions</a></p>";
    echo "<p>Después prueba acceder a /profile</p>";
}
?>

</body>
</html>
