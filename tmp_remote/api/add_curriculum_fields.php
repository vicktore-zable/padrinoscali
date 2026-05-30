<?php
require_once __DIR__ . '/../config/config.php';

try {
    $db = getDB();
    
    // Check if table curriculum exists
    $stmt = $db->query("SHOW TABLES LIKE 'curriculum'");
    if ($stmt->rowCount() == 0) {
        // Create table if it doesn't exist just in case
        $db->exec("
            CREATE TABLE curriculum (
                id INT AUTO_INCREMENT PRIMARY KEY,
                colaborador_id INT NOT NULL,
                resumen_profesional TEXT,
                formacion_academica JSON,
                experiencia_laboral JSON,
                participacion_politica JSON,
                habilidades TEXT,
                idiomas TEXT,
                reconocimientos TEXT,
                referencias TEXT,
                observaciones TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE
            )
        ");
        echo "Created table curriculum.\n";
    }

    $columns = [
        'hijos_data' => 'JSON DEFAULT NULL',
        'hijos_discapacidad' => 'TINYINT(1) DEFAULT 0',
        'equipo_futbol' => 'VARCHAR(100) DEFAULT NULL',
        'practica_deportiva' => 'VARCHAR(255) DEFAULT NULL'
    ];

    foreach ($columns as $col => $def) {
        $stmt = $db->query("SHOW COLUMNS FROM curriculum LIKE '$col'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE curriculum ADD COLUMN $col $def");
            echo "Added column $col to curriculum.\n";
        } else {
            echo "Column $col already exists.\n";
        }
    }
    
    echo "Migration completed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
