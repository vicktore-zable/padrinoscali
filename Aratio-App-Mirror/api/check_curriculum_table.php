<?php
// Script para verificar y crear la tabla curriculum
require_once __DIR__ . '/../config/config.php';
$db = getDB();

try {
    echo "Verificando tabla 'curriculum'...\n";
    $stmt = $db->query("SHOW TABLES LIKE 'curriculum'");
    if ($stmt->fetch()) {
        echo "La tabla 'curriculum' YA EXISTE.\n";

        // Mostrar columnas
        $stmt = $db->query("DESCRIBE curriculum");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Columnas: " . implode(', ', $cols) . "\n";
    } else {
        echo "La tabla 'curriculum' NO EXISTE. Creando...\n";

        $sql = "CREATE TABLE curriculum (
            id INT AUTO_INCREMENT PRIMARY KEY,
            colaborador_id INT NOT NULL,
            experiencia_laboral JSON NULL,
            formacion_academica JSON NULL,
            participacion_politica JSON NULL,
            resumen_profesional TEXT NULL,
            habilidades TEXT NULL,
            idiomas TEXT NULL,
            reconocimientos TEXT NULL,
            referencias TEXT NULL,
            observaciones TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
            UNIQUE KEY unique_colaborador (colaborador_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $db->exec($sql);
        echo "Tabla 'curriculum' creada EXITOSAMENTE.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
