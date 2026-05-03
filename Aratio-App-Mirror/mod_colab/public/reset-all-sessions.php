<?php
/**
 * Reset completo de todas las sesiones
 * Limpia sesiones viejas y hace logout completo
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset All Sessions</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        button { padding: 15px 30px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; }
        pre { background: #0f172a; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
<h1>🔄 Reset All Sessions</h1>

<?php
if (!isset($_POST['confirm'])) {
    echo "<h2>⚠ Advertencia</h2>";
    echo "<p>Esto va a:</p>";
    echo "<ul>";
    echo "<li>Eliminar TODAS las sesiones de la base de datos</li>";
    echo "<li>Cerrar tu sesión actual (logout)</li>";
    echo "<li>Forzarte a hacer login nuevamente</li>";
    echo "</ul>";

    echo "<p><strong>Después de hacer esto, debes:</strong></p>";
    echo "<ol>";
    echo "<li>Ir a <a href='/login' style='color: #3b82f6;'>/login</a></li>";
    echo "<li>Hacer login con: admin / Admin123!</li>";
    echo "<li>Verificar que puedas navegar entre páginas</li>";
    echo "</ol>";

    echo "<form method='POST'>";
    echo "<button type='submit' name='confirm' value='1'>🔄 SÍ, RESETEAR TODAS LAS SESIONES</button>";
    echo "</form>";
} else {
    echo "<h2>Ejecutando Reset...</h2>";

    try {
        $db = \App\Config\Database::getInstance();

        // 1. Contar sesiones actuales
        $count = $db->fetchOne("SELECT COUNT(*) as total FROM sesiones")['total'];
        echo "<p>Sesiones actuales en BD: <strong>$count</strong></p>";

        // 2. Eliminar todas las sesiones de BD
        $db->query("DELETE FROM sesiones");
        echo "<p class='success'>✓ Todas las sesiones eliminadas de la base de datos</p>";

        // 3. Destruir sesión PHP actual
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        echo "<p class='success'>✓ Sesión PHP destruida</p>";

        echo "<h2 class='success'>✅ Reset Completado</h2>";

        echo "<p>Ahora haz login nuevamente:</p>";
        echo "<ol>";
        echo "<li>Ve a: <a href='/login' style='color: #3b82f6; font-size: 18px;'><strong>/login</strong></a></li>";
        echo "<li>Usuario: <strong>admin</strong></li>";
        echo "<li>Password: <strong>Admin123!</strong></li>";
        echo "<li>Prueba navegar a /colaboradores, /reportes, etc.</li>";
        echo "</ol>";

        echo "<p class='success'>Con el fix de timezone aplicado, las nuevas sesiones deberían funcionar correctamente.</p>";

    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

</body>
</html>
