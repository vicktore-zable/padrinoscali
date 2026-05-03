<?php
/**
 * Preparador de ambiente en Hostinger
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Crear directorios
$dirs = [
    'mod_elecciones',
    'mod_elecciones/public',
    'mod_elecciones/src',
    'mod_elecciones/src/Controllers',
    'mod_elecciones/src/Views'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Directorio creado: $dir\n";
        } else {
            echo "Error creando: $dir\n";
        }
    } else {
        echo "Ya existe: $dir\n";
    }
}

// 2. Verificar/Crear Tabla
require_once __DIR__ . '/config/config.php';
try {
    $db = getDB();
    $sql = "CREATE TABLE IF NOT EXISTS mod_elecciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        anio INT NOT NULL,
        departamento VARCHAR(100),
        municipio VARCHAR(100),
        puesto VARCHAR(255),
        mesa INT,
        comuna VARCHAR(255),
        partido VARCHAR(255),
        candidato VARCHAR(255),
        votos INT,
        corporacion VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_anio (anio),
        INDEX idx_municipio (municipio),
        INDEX idx_puesto (puesto),
        INDEX idx_candidato (candidato),
        INDEX idx_partido (partido),
        INDEX idx_corporacion (corporacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql);
    echo "Tabla mod_elecciones lista en Hostinger.\n";
    
    // Verificar si hay registros
    $count = $db->query("SELECT COUNT(*) FROM mod_elecciones")->fetchColumn();
    echo "Registros actuales: $count\n";
    
} catch (Exception $e) {
    echo "Error DB: " . $e->getMessage() . "\n";
}
