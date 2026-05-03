<?php
/**
 * Verificar si los fixes de timezone están aplicados
 */

header('Content-Type: text/html; charset=utf-8');

$file = dirname(__DIR__) . '/src/Models/Usuario.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Fixes Applied</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        pre { background: #0f172a; padding: 15px; border-radius: 5px; white-space: pre-wrap; word-wrap: break-word; }
        .check { margin: 15px 0; padding: 15px; background: #334155; border-radius: 5px; }
    </style>
</head>
<body>
<h1>🔍 Verificar Fixes Aplicados</h1>

<?php
if (!file_exists($file)) {
    echo "<p class='error'>❌ Archivo no encontrado: $file</p>";
    exit;
}

$content = file_get_contents($file);

echo "<h2>Estado de los Fixes:</h2>";

$allFixed = true;

// Check 1: createSession() usa gmdate
echo "<div class='check'>";
echo "<h3>1. createSession() - Línea 472</h3>";
if (strpos($content, "gmdate('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])") !== false &&
    strpos($content, "// UTC") !== false) {
    echo "<p class='success'>✅ CORREGIDO - Usa gmdate()</p>";
} else if (strpos($content, "date('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])") !== false) {
    echo "<p class='error'>❌ NO CORREGIDO - Todavía usa date()</p>";
    $allFixed = false;
} else {
    echo "<p class='warning'>⚠ No se encontró el patrón esperado</p>";
}
echo "</div>";

// Check 2: verifySession() ultima_actividad usa gmdate
echo "<div class='check'>";
echo "<h3>2. verifySession() - ultima_actividad</h3>";
if (strpos($content, "'ultima_actividad' => gmdate('Y-m-d H:i:s')") !== false) {
    echo "<p class='success'>✅ CORREGIDO - Usa gmdate()</p>";
} else if (strpos($content, "'ultima_actividad' => date('Y-m-d H:i:s')") !== false) {
    echo "<p class='error'>❌ NO CORREGIDO - Todavía usa date()</p>";
    $allFixed = false;
} else {
    echo "<p class='warning'>⚠ No se encontró el patrón esperado</p>";
}
echo "</div>";

// Check 3: verifySession() expira_en usa gmdate
echo "<div class='check'>";
echo "<h3>3. verifySession() - expira_en</h3>";
if (strpos($content, "'expira_en' => gmdate('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])") !== false) {
    echo "<p class='success'>✅ CORREGIDO - Usa gmdate()</p>";
} else if (strpos($content, "'expira_en' => date('Y-m-d H:i:s', time() + SESSION_CONFIG['lifetime'])") !== false) {
    echo "<p class='error'>❌ NO CORREGIDO - Todavía usa date()</p>";
    $allFixed = false;
} else {
    echo "<p class='warning'>⚠ No se encontró el patrón esperado</p>";
}
echo "</div>";

// Check 4: verifySession() usa UTC_TIMESTAMP
echo "<div class='check'>";
echo "<h3>4. verifySession() - WHERE clause</h3>";
if (strpos($content, "AND s.expira_en > UTC_TIMESTAMP()") !== false) {
    echo "<p class='success'>✅ CORREGIDO - Usa UTC_TIMESTAMP()</p>";
} else if (strpos($content, "AND s.expira_en > NOW()") !== false) {
    echo "<p class='error'>❌ NO CORREGIDO - Todavía usa NOW()</p>";
    $allFixed = false;
} else {
    echo "<p class='warning'>⚠ No se encontró el patrón esperado</p>";
}
echo "</div>";

// Check 5: getActiveSessions() usa UTC_TIMESTAMP
echo "<div class='check'>";
echo "<h3>5. getActiveSessions() - WHERE clause</h3>";
$pattern = "WHERE usuario_id = ?\n                  AND expira_en > UTC_TIMESTAMP()";
if (strpos($content, "AND expira_en > UTC_TIMESTAMP()") !== false) {
    echo "<p class='success'>✅ CORREGIDO - Usa UTC_TIMESTAMP()</p>";
} else {
    echo "<p class='error'>❌ NO CORREGIDO - Todavía usa NOW()</p>";
    $allFixed = false;
}
echo "</div>";

echo "<hr>";

if ($allFixed) {
    echo "<h2 class='success'>✅ TODOS LOS FIXES ESTÁN APLICADOS</h2>";
    echo "<p>Ahora necesitas:</p>";
    echo "<ol>";
    echo "<li><a href='/reset-all-sessions.php' style='color: #3b82f6;'>Resetear todas las sesiones</a></li>";
    echo "<li>Hacer login de nuevo</li>";
    echo "<li>Probar /profile y /settings</li>";
    echo "</ol>";
} else {
    echo "<h2 class='error'>❌ ALGUNOS FIXES NO ESTÁN APLICADOS</h2>";
    echo "<p>Necesitas ejecutar:</p>";
    echo "<ol>";
    echo "<li><a href='/fix-all-timezone-issues.php' style='color: #3b82f6;'>Fix All Timezone Issues</a></li>";
    echo "<li><a href='/fix-get-active-sessions.php' style='color: #3b82f6;'>Fix getActiveSessions</a></li>";
    echo "<li><a href='/reset-all-sessions.php' style='color: #3b82f6;'>Reset All Sessions</a></li>";
    echo "<li>Login nuevamente</li>";
    echo "</ol>";
}
?>

</body>
</html>
