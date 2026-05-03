<?php
/**
 * Fix COMPLETO de timezone en Usuario.php
 * Corrige TODOS los usos de date() relacionados con sesiones
 */

header('Content-Type: text/html; charset=utf-8');

$file = dirname(__DIR__) . '/src/Models/Usuario.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix All Timezone Issues</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        pre { background: #0f172a; padding: 15px; border-radius: 5px; overflow-x: auto; }
        button { padding: 15px 30px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; }
        .change { margin: 20px 0; padding: 15px; background: #334155; border-radius: 5px; }
    </style>
</head>
<body>
<h1>🕐 Fix All Timezone Issues</h1>

<?php
if (!isset($_POST['apply'])) {
    echo "<h2>Problemas a Corregir:</h2>";

    echo "<div class='change'>";
    echo "<h3>1. createSession() - Línea 472</h3>";
    echo "<pre class='error'>ANTES:\n\$expiresIn = date('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime']);</pre>";
    echo "<pre class='success'>DESPUÉS:\n\$expiresIn = gmdate('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime']);</pre>";
    echo "</div>";

    echo "<div class='change'>";
    echo "<h3>2. verifySession() - Línea 530</h3>";
    echo "<pre class='error'>ANTES:\n'ultima_actividad' => date('Y-m-d H:i:s')</pre>";
    echo "<pre class='success'>DESPUÉS:\n'ultima_actividad' => gmdate('Y-m-d H:i:s')</pre>";
    echo "</div>";

    echo "<div class='change'>";
    echo "<h3>3. verifySession() - Línea 531</h3>";
    echo "<pre class='error'>ANTES:\n'expira_en' => date('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])</pre>";
    echo "<pre class='success'>DESPUÉS:\n'expira_en' => gmdate('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])</pre>";
    echo "</div>";

    echo "<div class='change'>";
    echo "<h3>4. verifySession() - Línea 511</h3>";
    echo "<pre class='error'>ANTES:\nAND s.expira_en > NOW()</pre>";
    echo "<pre class='success'>DESPUÉS:\nAND s.expira_en > UTC_TIMESTAMP()</pre>";
    echo "<p>NOW() usa timezone del servidor MySQL, UTC_TIMESTAMP() siempre usa UTC</p>";
    echo "</div>";

    echo "<button onclick='document.getElementById(\"form\").submit()'>🔧 Aplicar TODOS los Fixes</button>";
    echo "<form id='form' method='POST'><input type='hidden' name='apply' value='1'></form>";

} else {
    echo "<h2>Aplicando Fixes...</h2>";

    if (!file_exists($file)) {
        echo "<p class='error'>❌ Archivo no encontrado: $file</p>";
        exit;
    }

    $content = file_get_contents($file);

    // Backup
    $backupFile = $file . '.backup_timezone_complete_' . date('YmdHis');
    copy($file, $backupFile);
    echo "<p class='success'>✓ Backup creado: $backupFile</p>";

    $changes = 0;

    // Fix 1: createSession() - línea 472
    $old1 = "\$expiresIn = date('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime']);";
    $new1 = "\$expiresIn = gmdate('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime']); // UTC";
    if (strpos($content, $old1) !== false) {
        $content = str_replace($old1, $new1, $content);
        echo "<p class='success'>✓ Fix 1: createSession() corregido</p>";
        $changes++;
    } else {
        echo "<p class='warning'>⚠ Fix 1: Ya aplicado o no encontrado</p>";
    }

    // Fix 2: verifySession() - última_actividad
    $old2 = "'ultima_actividad' => date('Y-m-d H:i:s'),";
    $new2 = "'ultima_actividad' => gmdate('Y-m-d H:i:s'), // UTC";
    if (strpos($content, $old2) !== false) {
        $content = str_replace($old2, $new2, $content);
        echo "<p class='success'>✓ Fix 2: ultima_actividad corregido</p>";
        $changes++;
    } else {
        echo "<p class='warning'>⚠ Fix 2: Ya aplicado o no encontrado</p>";
    }

    // Fix 3: verifySession() - expira_en
    $old3 = "'expira_en' => date('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])";
    $new3 = "'expira_en' => gmdate('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime']) // UTC";
    if (strpos($content, $old3) !== false) {
        $content = str_replace($old3, $new3, $content);
        echo "<p class='success'>✓ Fix 3: expira_en corregido</p>";
        $changes++;
    } else {
        echo "<p class='warning'>⚠ Fix 3: Ya aplicado o no encontrado</p>";
    }

    // Fix 4: verifySession() - NOW() -> UTC_TIMESTAMP()
    $old4 = "AND s.expira_en > NOW()";
    $new4 = "AND s.expira_en > UTC_TIMESTAMP()";
    if (strpos($content, $old4) !== false) {
        $content = str_replace($old4, $new4, $content);
        echo "<p class='success'>✓ Fix 4: NOW() → UTC_TIMESTAMP() corregido</p>";
        $changes++;
    } else {
        echo "<p class='warning'>⚠ Fix 4: Ya aplicado o no encontrado</p>";
    }

    if ($changes > 0) {
        file_put_contents($file, $content);
        echo "<h2 class='success'>✅ {$changes} Fix(es) Aplicado(s)</h2>";

        echo "<h2>✅ Próximos Pasos CRÍTICOS:</h2>";
        echo "<ol>";
        echo "<li><strong>RESETEAR SESIONES:</strong> <a href='/reset-all-sessions.php' style='color: #3b82f6; font-size: 18px;'>reset-all-sessions.php</a></li>";
        echo "<li>Las sesiones viejas TODAVÍA tienen fechas incorrectas</li>";
        echo "<li>Después del reset, hacer login: admin / Admin123!</li>";
        echo "<li>Probar navegación entre páginas</li>";
        echo "</ol>";
    } else {
        echo "<h2 class='warning'>⚠ Ningún cambio necesario</h2>";
        echo "<p>Todos los fixes ya estaban aplicados</p>";
    }
}
?>

</body>
</html>
