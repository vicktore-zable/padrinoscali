<?php
/**
 * Diagnóstico completo de configuración PHP en producción
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Configuration Check - Production</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .section { margin: 20px 0; padding: 15px; background: #334155; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td, th { padding: 8px; text-align: left; border-bottom: 1px solid #475569; }
        th { background: #1e293b; }
    </style>
</head>
<body>
<h1>🔍 PHP Configuration Check - Production</h1>

<?php
// 1. Versión de PHP
echo "<div class='section'>";
echo "<h2>1. Versión de PHP</h2>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
echo "<strong>PHP SAPI:</strong> " . php_sapi_name() . "<br>";
echo "<strong>Zend Version:</strong> " . zend_version() . "<br>";
echo "</div>";

// 2. Extensiones críticas
echo "<div class='section'>";
echo "<h2>2. Extensiones Críticas para el Sistema</h2>";
echo "<table>";
echo "<tr><th>Extensión</th><th>Estado</th><th>Notas</th></tr>";

$critical_extensions = [
    'pdo' => 'Base de datos',
    'pdo_mysql' => 'Conexión MySQL',
    'session' => 'Sesiones PHP',
    'json' => 'Procesamiento JSON',
    'mbstring' => 'Strings multibyte',
    'openssl' => 'Encriptación/HTTPS',
    'hash' => 'Password hashing',
    'curl' => 'HTTP requests',
    'fileinfo' => 'Detección tipo archivos',
    'gd' => 'Procesamiento imágenes',
    'zip' => 'Compresión archivos'
];

foreach ($critical_extensions as $ext => $desc) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "<span class='success'>✓ CARGADA</span>" : "<span class='error'>✗ NO DISPONIBLE</span>";
    echo "<tr><td><strong>$ext</strong></td><td>$status</td><td>$desc</td></tr>";
}

echo "</table>";
echo "</div>";

// 3. Configuración de Sesiones
echo "<div class='section'>";
echo "<h2>3. Configuración de Sesiones</h2>";
echo "<table>";

$session_config = [
    'session.save_handler' => 'Método de guardado',
    'session.save_path' => 'Ruta de guardado',
    'session.use_cookies' => 'Usar cookies',
    'session.use_only_cookies' => 'Solo cookies',
    'session.cookie_lifetime' => 'Vida de cookie',
    'session.cookie_httponly' => 'HTTP Only',
    'session.cookie_secure' => 'Secure (HTTPS)',
    'session.gc_maxlifetime' => 'Tiempo máximo sesión',
    'session.cookie_samesite' => 'SameSite policy'
];

foreach ($session_config as $key => $desc) {
    $value = ini_get($key);
    echo "<tr><td><strong>$key</strong></td><td>$value</td><td>$desc</td></tr>";
}

echo "</table>";

// Probar iniciar sesión
echo "<br><strong>Prueba de Sesión:</strong><br>";
if (session_status() === PHP_SESSION_NONE) {
    try {
        session_start();
        echo "<span class='success'>✓ session_start() exitoso</span><br>";
        echo "Session ID: " . session_id() . "<br>";

        $_SESSION['test'] = 'test_value';
        echo "Escribir en sesión: <span class='success'>✓ OK</span><br>";

        if ($_SESSION['test'] === 'test_value') {
            echo "Leer de sesión: <span class='success'>✓ OK</span><br>";
        } else {
            echo "Leer de sesión: <span class='error'>✗ ERROR</span><br>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    }
} else {
    echo "<span class='warning'>⚠ Sesión ya iniciada</span><br>";
}

echo "</div>";

// 4. Directorio de sesiones
echo "<div class='section'>";
echo "<h2>4. Verificación de Directorios</h2>";

$session_path = session_save_path();
echo "<strong>Session save path:</strong> $session_path<br>";

if (!empty($session_path)) {
    echo "<strong>Existe:</strong> " . (is_dir($session_path) ? "<span class='success'>✓ Sí</span>" : "<span class='error'>✗ No</span>") . "<br>";
    echo "<strong>Escribible:</strong> " . (is_writable($session_path) ? "<span class='success'>✓ Sí</span>" : "<span class='error'>✗ No</span>") . "<br>";
    echo "<strong>Permisos:</strong> " . (is_dir($session_path) ? decoct(fileperms($session_path) & 0777) : 'N/A') . "<br>";
}

// Verificar otros directorios críticos
$app_root = dirname(__DIR__);
$dirs_to_check = [
    'storage/cache' => $app_root . '/storage/cache',
    'storage/logs' => $app_root . '/storage/logs',
    'storage/sessions' => $app_root . '/storage/sessions'
];

echo "<br><strong>Directorios de aplicación:</strong><br>";
echo "<table>";
echo "<tr><th>Directorio</th><th>Existe</th><th>Escribible</th><th>Permisos</th></tr>";

foreach ($dirs_to_check as $name => $path) {
    $exists = is_dir($path);
    $writable = is_writable($path);
    $perms = $exists ? decoct(fileperms($path) & 0777) : 'N/A';

    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>" . ($exists ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td>";
    echo "<td>" . ($writable ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td>";
    echo "<td>$perms</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// 5. Funciones de Password
echo "<div class='section'>";
echo "<h2>5. Funciones de Password</h2>";

$password_functions = [
    'password_hash',
    'password_verify',
    'password_needs_rehash',
    'password_get_info'
];

echo "<table>";
echo "<tr><th>Función</th><th>Disponible</th></tr>";

foreach ($password_functions as $func) {
    $available = function_exists($func);
    echo "<tr><td>$func</td><td>" . ($available ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td></tr>";
}

echo "</table>";

// Probar password_hash
echo "<br><strong>Prueba de password_hash:</strong><br>";
$test_password = 'Admin123!';
$test_hash = password_hash($test_password, PASSWORD_BCRYPT);
echo "Hash generado: " . substr($test_hash, 0, 30) . "...<br>";
echo "Verificación: " . (password_verify($test_password, $test_hash) ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ ERROR</span>") . "<br>";

echo "</div>";

// 6. Configuraciones PHP relevantes
echo "<div class='section'>";
echo "<h2>6. Otras Configuraciones Relevantes</h2>";
echo "<table>";

$other_config = [
    'display_errors' => 'Mostrar errores',
    'error_reporting' => 'Nivel de errores',
    'log_errors' => 'Log de errores',
    'error_log' => 'Archivo de log',
    'max_execution_time' => 'Tiempo máximo ejecución',
    'memory_limit' => 'Límite de memoria',
    'post_max_size' => 'Tamaño máximo POST',
    'upload_max_filesize' => 'Tamaño máximo upload',
    'date.timezone' => 'Timezone'
];

foreach ($other_config as $key => $desc) {
    $value = ini_get($key);
    echo "<tr><td><strong>$key</strong></td><td>$value</td><td>$desc</td></tr>";
}

echo "</table>";
echo "</div>";

// 7. Variables de Servidor
echo "<div class='section'>";
echo "<h2>7. Variables de Servidor</h2>";
echo "<table>";

$server_vars = [
    'SERVER_SOFTWARE',
    'DOCUMENT_ROOT',
    'SCRIPT_FILENAME',
    'SERVER_PROTOCOL',
    'REQUEST_METHOD',
    'HTTPS'
];

foreach ($server_vars as $var) {
    $value = $_SERVER[$var] ?? 'No definida';
    echo "<tr><td><strong>$var</strong></td><td>$value</td></tr>";
}

echo "</table>";
echo "</div>";

// 8. Todas las extensiones cargadas
echo "<div class='section'>";
echo "<h2>8. Todas las Extensiones Cargadas</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";
echo "</div>";

?>

<div class='section'>
<h2>✅ Siguiente Paso</h2>
<p>Compara esta configuración con tu servidor local para identificar diferencias.</p>
<p><strong>Link a phpinfo() completo:</strong> <a href="/info.php" style="color: #3b82f6;">info.php</a></p>
</div>

</body>
</html>
