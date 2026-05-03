<?php
require_once __DIR__ . '/../config/config.php';
$db = getDB();

try {
    // 1. Agregar campos a jac_registros para el presidente
    $db->exec("ALTER TABLE jac_registros 
               ADD COLUMN IF NOT EXISTS cedula_presidente VARCHAR(50) DEFAULT NULL AFTER presidente,
               ADD COLUMN IF NOT EXISTS fecha_nacimiento_presidente DATE DEFAULT NULL AFTER cedula_presidente");
    
    echo "--- MIGRACIÓN EXITOSA ---\n";
    echo "Campos añadidos a jac_registros: cedula_presidente, fecha_nacimiento_presidente\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
