<?php
/**
 * Script para regenerar contraseñas en producción
 * EJECUTAR UNA VEZ Y LUEGO ELIMINAR
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== REGENERANDO CONTRASEÑAS ===\n\n";

try {
    $db = \App\Config\Database::getInstance();
    
    $password = 'Admin123!';
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    
    echo "Nueva contraseña: $password\n";
    echo "Nuevo hash: $hash\n";
    echo "Verificación: " . (password_verify($password, $hash) ? '✓ CORRECTO' : '✗ ERROR') . "\n\n";
    
    // Actualizar todos los usuarios
    $result = $db->query(
        "UPDATE usuarios SET password = ?, intentos_fallidos = 0, bloqueado_hasta = NULL",
        [$hash]
    );
    
    echo "✓ Contraseñas actualizadas para todos los usuarios\n\n";
    
    // Verificar
    $users = $db->fetchAll("SELECT id, usuario, email FROM usuarios");
    echo "Usuarios actualizados:\n";
    foreach ($users as $user) {
        echo "- {$user['usuario']} ({$user['email']})\n";
    }
    
    echo "\n✓ PROCESO COMPLETADO\n";
    echo "\nCredenciales de acceso:\n";
    echo "Usuario: admin (o cualquier usuario de la lista)\n";
    echo "Contraseña: $password\n";
    echo "\n⚠ IMPORTANTE: Eliminar este archivo después de ejecutar\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
