<?php
/**
 * Script standalone para regenerar contraseñas en producción
 * NO DEPENDE DEL FRAMEWORK - Conexión directa a BD
 * EJECUTAR UNA VEZ Y LUEGO ELIMINAR
 */

// Configuración de base de datos
define('DB_HOST', 'auth-db690.hstgr.io');
define('DB_NAME', 'u156469157_aratio');
define('DB_USER', 'u156469157_aratio');
define('DB_PASS', '15zxCeBbvgsR');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Passwords - Production</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        button { padding: 15px 30px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 20px 0; }
        button:hover { background: #2563eb; }
        pre { background: #0f172a; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<h1>🔧 Fix Passwords - Production Database</h1>

<?php
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "<p class='success'>✓ Conexión a base de datos exitosa</p>";

    if (!isset($_POST['action'])) {
        // Mostrar estado actual
        echo "<h2>Estado Actual de Usuarios</h2><pre>";

        $stmt = $pdo->query("SELECT id, usuario, email, tipo_usuario, activo, intentos_fallidos, bloqueado_hasta,
                             LEFT(password, 30) as hash_preview
                             FROM usuarios ORDER BY id");
        $users = $stmt->fetchAll();

        foreach ($users as $user) {
            echo "\n<strong>ID: {$user['id']} - {$user['usuario']}</strong>\n";
            echo "Email: {$user['email']}\n";
            echo "Tipo: {$user['tipo_usuario']}\n";
            echo "Activo: " . ($user['activo'] ? 'Sí' : 'NO') . "\n";
            echo "Intentos fallidos: {$user['intentos_fallidos']}\n";
            echo "Bloqueado: " . ($user['bloqueado_hasta'] ?: 'No') . "\n";
            echo "Hash: {$user['hash_preview']}...\n";

            // Probar verificación con Admin123!
            $fullHash = $pdo->query("SELECT password FROM usuarios WHERE id = {$user['id']}")->fetch()['password'];
            $verify = password_verify('Admin123!', $fullHash);
            echo "Verificación 'Admin123!': " . ($verify ? "<span class='success'>✓ FUNCIONA</span>" : "<span class='error'>✗ NO FUNCIONA</span>") . "\n";
            echo str_repeat('-', 60) . "\n";
        }

        echo "</pre>";

        echo '<form method="POST">';
        echo '<button type="submit" name="action" value="regenerate">🔧 REGENERAR TODAS LAS CONTRASEÑAS</button>';
        echo '<p class="warning">⚠ Esto cambiará la contraseña de TODOS los usuarios a: Admin123!</p>';
        echo '</form>';

    } else if ($_POST['action'] === 'regenerate') {
        echo "<h2>Regenerando Contraseñas</h2><pre>";

        $password = 'Admin123!';
        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

        echo "Password: <strong>$password</strong>\n";
        echo "Nuevo hash: $newHash\n";
        echo "Verificación del hash: " . (password_verify($password, $newHash) ? "<span class='success'>✓ OK</span>" : "<span class='error'>✗ ERROR</span>") . "\n\n";

        // Actualizar todos los usuarios
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, intentos_fallidos = 0, bloqueado_hasta = NULL");
        $result = $stmt->execute([$newHash]);

        if ($result) {
            $affected = $stmt->rowCount();
            echo "<span class='success'>✓ {$affected} usuarios actualizados correctamente</span>\n\n";

            // Mostrar usuarios actualizados
            $users = $pdo->query("SELECT id, usuario, email, tipo_usuario FROM usuarios ORDER BY id")->fetchAll();
            echo "Usuarios actualizados:\n";
            foreach ($users as $user) {
                echo "  • <strong>{$user['usuario']}</strong> ({$user['email']}) - {$user['tipo_usuario']}\n";
            }

            echo "\n<span class='success'>✓ PROCESO COMPLETADO</span>\n\n";
            echo "Credenciales de acceso:\n";
            echo "  Usuario: <strong>admin</strong> (o cualquier otro usuario)\n";
            echo "  Contraseña: <strong>$password</strong>\n\n";
            echo "<span class='warning'>⚠ IMPORTANTE: Eliminar este archivo inmediatamente:</span>\n";
            echo "  rm /public_html/mod_colab/public/fix-pwd-standalone.php\n";

        } else {
            echo "<span class='error'>✗ Error al actualizar contraseñas</span>\n";
        }

        echo "</pre>";

        echo '<form method="POST">';
        echo '<button type="submit">↻ Verificar Estado Nuevamente</button>';
        echo '</form>';
    }

} catch (PDOException $e) {
    echo "<p class='error'>✗ ERROR DE CONEXIÓN: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

</body>
</html>
