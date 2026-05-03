<?php
/**
 * Script para crear la tabla mod_elecciones
 * Maneja errores de entorno CLI
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Evitar errores de sesión en CLI
if (php_sapi_name() === 'cli' && !isset($_SESSION)) {
    $_SESSION = [];
}

require_once __DIR__ . '/config/config.php';

try {
    $db = getDB();
    $sql = file_get_contents(__DIR__ . '/database/mod_elecciones_schema.sql');
    
    if (!$sql) {
        die("No se pudo leer el archivo de schema.\n");
    }
    
    $db->exec($sql);
    echo "Tabla mod_elecciones creada o ya existía exitosamente.\n";
    
} catch (Exception $e) {
    echo "ERROR al crear la tabla: " . $e->getMessage() . "\n";
}
