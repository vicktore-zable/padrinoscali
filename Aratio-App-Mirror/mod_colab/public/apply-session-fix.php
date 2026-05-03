<?php
/**
 * Script para aplicar el fix de session_write_close() en AuthController
 * Este script parchea el archivo para agregar session_write_close() antes del redirect
 */

header('Content-Type: text/html; charset=utf-8');

$file = dirname(__DIR__) . '/src/Controllers/AuthController.php';

if (!file_exists($file)) {
    die("❌ ERROR: Archivo no encontrado: $file");
}

$content = file_get_contents($file);

// Verificar si ya tiene el fix aplicado
if (strpos($content, 'session_write_close()') !== false) {
    echo "<h1>✅ Fix Ya Aplicado</h1>";
    echo "<p>El archivo AuthController.php ya tiene session_write_close() - no es necesario parchear.</p>";
    exit;
}

// Buscar el patrón a reemplazar
$search = "            Logger::auth('login', \$usuario, true);

            // Redirigir al dashboard apropiado según rol
            \$this->redirect('/dashboard');";

$replace = "            Logger::auth('login', \$usuario, true);

            // IMPORTANTE: Forzar escritura de sesión antes de redirigir
            // Sin esto, PHP puede terminar antes de escribir la sesión y se pierden los datos
            session_write_close();

            // Redirigir al dashboard apropiado según rol
            \$this->redirect('/dashboard');";

// Aplicar el parche
$newContent = str_replace($search, $replace, $content);

if ($newContent === $content) {
    echo "<h1>⚠️ No Se Pudo Aplicar</h1>";
    echo "<p>No se encontró el patrón exacto para parchear.</p>";
    echo "<p>Verifica manualmente el archivo en la línea 127-130.</p>";
    exit;
}

// Hacer backup
$backupFile = $file . '.backup_' . date('YmdHis');
copy($file, $backupFile);

// Escribir archivo corregido
file_put_contents($file, $newContent);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
    .success { color: #10b981; }
    .warning { color: #f59e0b; }
    pre { background: #0f172a; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style>";
echo "</head>";
echo "<body>";

echo "<h1 class='success'>✅ Fix Aplicado Correctamente</h1>";

echo "<h2>Cambios Realizados:</h2>";
echo "<p>Archivo modificado: <strong>$file</strong></p>";
echo "<p>Backup creado: <strong>$backupFile</strong></p>";

echo "<h3>Líneas agregadas:</h3>";
echo "<pre style='color: #10b981;'>";
echo htmlspecialchars("// IMPORTANTE: Forzar escritura de sesión antes de redirigir
// Sin esto, PHP puede terminar antes de escribir la sesión y se pierden los datos
session_write_close();");
echo "</pre>";

echo "<h2>✅ Próximos Pasos:</h2>";
echo "<ol>";
echo "<li>Prueba hacer login en: <a href='/login' style='color: #3b82f6;'>https://colaboradores.aratio.mrmtech.net/login</a></li>";
echo "<li>Usa: <strong>admin</strong> / <strong>Admin123!</strong></li>";
echo "<li>Debes ser redirigido a /dashboard correctamente</li>";
echo "<li>Si funciona, elimina este archivo por seguridad: <code>apply-session-fix.php</code></li>";
echo "</ol>";

echo "<h2 class='warning'>⚠️ Si Algo Sale Mal:</h2>";
echo "<p>Restaura el backup ejecutando:</p>";
echo "<pre>cp $backupFile $file</pre>";

echo "</body>";
echo "</html>";
