<?php
/**
 * Fix de Timezone para Sesiones
 * Corrige el problema de expira_en vs created_at con diferentes zonas horarias
 */

header('Content-Type: text/html; charset=utf-8');

$file = dirname(__DIR__) . '/src/Models/Usuario.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Timezone Sessions</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        pre { background: #0f172a; padding: 15px; border-radius: 5px; }
        button { padding: 15px 30px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; }
    </style>
</head>
<body>
<h1>🕐 Fix Timezone Sessions</h1>

<h2>Problema Detectado:</h2>
<pre class="error">
created_at usa UTC (MySQL CURRENT_TIMESTAMP)
expira_en usa hora local PHP (date())

Resultado: expira_en < created_at → sesión "expirada" inmediatamente
</pre>

<h2>Solución:</h2>
<pre class="success">
Cambiar de: date('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])
Cambiar a: gmdate('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])

gmdate() devuelve hora UTC, igual que MySQL CURRENT_TIMESTAMP
</pre>

<?php
if (!isset($_POST['apply'])) {
    echo "<button onclick='document.getElementById(\"form\").submit()'>🔧 Aplicar Fix</button>";
    echo "<form id='form' method='POST'><input type='hidden' name='apply' value='1'></form>";
} else {
    echo "<h2>Aplicando Fix...</h2>";

    if (!file_exists($file)) {
        echo "<p class='error'>❌ Archivo no encontrado: $file</p>";
        exit;
    }

    $content = file_get_contents($file);

    // Verificar si ya está corregido
    if (strpos($content, "gmdate('Y-m-d H:i:s'") !== false) {
        echo "<p class='success'>✅ Fix ya aplicado anteriormente</p>";
        exit;
    }

    // Hacer backup
    $backupFile = $file . '.backup_timezone_' . date('YmdHis');
    copy($file, $backupFile);
    echo "<p class='success'>✓ Backup creado: $backupFile</p>";

    // Aplicar el cambio
    $oldCode = "\$expiresIn = date('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime']);";
    $newCode = "\$expiresIn = gmdate('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime']); // gmdate = UTC, igual que MySQL";

    $newContent = str_replace($oldCode, $newCode, $content);

    if ($newContent === $content) {
        echo "<p class='error'>❌ No se encontró el código a reemplazar</p>";
        echo "<p>Busca manualmente en Usuario.php línea 472</p>";
        exit;
    }

    // Guardar
    file_put_contents($file, $newContent);

    echo "<p class='success'>✅ Fix aplicado correctamente</p>";

    echo "<h3>Código modificado:</h3>";
    echo "<pre class='error'>ANTES:\n";
    echo htmlspecialchars($oldCode);
    echo "</pre>";

    echo "<pre class='success'>DESPUÉS:\n";
    echo htmlspecialchars($newCode);
    echo "</pre>";

    echo "<h2>✅ Próximos Pasos:</h2>";
    echo "<ol>";
    echo "<li><strong>Prueba el login nuevamente</strong> en: <a href='/login' style='color: #3b82f6;'>Login</a></li>";
    echo "<li>Usuario: <strong>admin</strong> / Password: <strong>Admin123!</strong></li>";
    echo "<li>Debería redirigir a <strong>/dashboard</strong> correctamente</li>";
    echo "<li>Si funciona, elimina este archivo por seguridad</li>";
    echo "</ol>";

    echo "<p class='warning'>⚠ Si no funciona, restaura el backup:</p>";
    echo "<pre>cp $backupFile $file</pre>";
}
?>

</body>
</html>
