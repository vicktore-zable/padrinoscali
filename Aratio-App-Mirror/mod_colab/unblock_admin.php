<?php
/**
 * Script para desbloquear al usuario admin
 */

// Definir constantes necesarias
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar configuración
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

try {
    $db = Database::getInstance();

    // 1. Desbloquear usuario admin
    $sql = "UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE usuario = 'admin'";
    $result = $db->query($sql);

    echo "<h1>Desbloqueo de Usuario</h1>";
    echo "<p>Resultado actualización usuario: " . ($result ? 'EXITO' : 'FALLO') . "</p>";

    // 2. Verificar estado actual
    $user = $db->fetchOne("SELECT usuario, intentos_fallidos, bloqueado_hasta FROM usuarios WHERE usuario = 'admin'");

    echo "<h2>Estado Actual:</h2>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";

    // 3. Limpiar rate limits (si existe la tabla, o simulado si es por archivo/cache)
    // Nota: El rate limit por IP en AuthController usa Security::checkRateLimit que suele usar una tabla 'rate_limits' o archivos.
    // Vamos a intentar limpiar la tabla rate_limits si existe.
    try {
        $db->query("DELETE FROM rate_limits");
        echo "<p>Tabla rate_limits limpiada (si existía).</p>";
    } catch (Exception $e) {
        echo "<p>No se pudo limpiar rate_limits (quizás no usa tabla): " . $e->getMessage() . "</p>";
    }

} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
