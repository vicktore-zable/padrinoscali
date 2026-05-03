<?php
require 'root_config.php';
$db = getDB();

echo "--- Iniciando Migración v2.1.0 ---\n";

// 1. Agregar columna 'tipo_organizacion' a 'jac_registros'
try {
    $db->exec("ALTER TABLE jac_registros ADD COLUMN tipo_organizacion VARCHAR(50) DEFAULT 'JAC' AFTER nombre_jac");
    echo "Columna 'tipo_organizacion' agregada a 'jac_registros'.\n";
} catch(Exception $e) {
    echo "Nota: Columna 'tipo_organizacion' ya existe o error: " . $e->getMessage() . "\n";
}

// 2. Asegurar columna 'foto' en 'colaboradores' (ya verificado que existe, pero por seguridad)
try {
    $db->exec("ALTER TABLE colaboradores ADD COLUMN IF NOT EXISTS foto VARCHAR(255) AFTER mesa_votacion");
    echo "Columna 'foto' verificada en 'colaboradores'.\n";
} catch(Exception $e) {
    echo "Error en 'foto': " . $e->getMessage() . "\n";
}

// 3. Agregar columna 'bloque' a 'jac_plancha_miembros' si no existe
try {
    $db->exec("ALTER TABLE jac_plancha_miembros ADD COLUMN bloque VARCHAR(50) DEFAULT 'Directivo' AFTER telefono");
    echo "Columna 'bloque' agregada a 'jac_plancha_miembros'.\n";
} catch(Exception $e) {
    echo "Nota: Columna 'bloque' ya existe o error: " . $e->getMessage() . "\n";
}

echo "--- Migración Finalizada ---\n";
