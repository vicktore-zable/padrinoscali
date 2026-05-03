<?php
/**
 * Script para restablecer la contraseña de admin
 */

// Definir constantes necesarias
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar configuración
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

echo "<h1>Restablecimiento de Contraseña Admin</h1>";

try {
    $db = Database::getInstance();

    $newPassword = 'Admin123!';
    // Usar BCRYPT con cost 12 como en la configuración
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    // Actualizar contraseña y limpiar intentos fallidos
    $sql = "UPDATE usuarios SET 
            password = ?, 
            intentos_fallidos = 0, 
            bloqueado_hasta = NULL,
            activo = 1
            WHERE usuario = 'admin'";

    $result = $db->query($sql, [$newHash]);

    if ($result) {
        echo "<h2 style='color:green'>✅ Contraseña restablecida exitosamente</h2>";
        echo "<p>Nueva contraseña: <strong>$newPassword</strong></p>";
        echo "<p>Ahora puedes intentar iniciar sesión.</p>";
    } else {
        echo "<h2 style='color:red'>❌ Error al actualizar la base de datos</h2>";
    }

} catch (Exception $e) {
    echo "<h2 style='color:red'>Error: " . $e->getMessage() . "</h2>";
}
