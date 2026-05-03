<?php
/**
 * SISTEMA COLABORADORES - UTILIDAD DE RECUPERACIÓN
 * Este script restablece las contraseñas de todos los usuarios de semilla.
 * Contraseña: Admin123!
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "=== INICIANDO RECUPERACIÓN DE CONTRASEÑAS ===\n";

// Hash para 'Admin123!'
$newHash = '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // 1. Limpiar bloqueos e intentos fallidos
    $sql = "UPDATE usuarios SET 
            password = ?, 
            activo = 1, 
            intentos_fallidos = 0, 
            bloqueado_hasta = NULL";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$newHash]);

    echo "✓ Todas las contraseñas han sido restablecidas a: Admin123!\n";
    echo "✓ Cuentas activadas y desbloqueadas.\n";

    // 2. Limpiar sesiones para forzar nuevo login
    $conn->exec("TRUNCATE TABLE sesiones");
    echo "✓ Sesiones activas cerradas.\n";

    echo "\nPROCESO COMPLETADO CON ÉXITO.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
