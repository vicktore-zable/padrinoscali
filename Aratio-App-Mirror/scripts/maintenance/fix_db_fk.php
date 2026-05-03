<?php
require_once __DIR__ . '/../../config/config.php';
try {
    $db = getDB();
    
    echo "1. Intentando eliminar FK antigua...\n";
    // El nombre de la CONSTRAINT puede variar, pero el error indicó reportes_diaD_ibfk_1
    try {
        $db->exec("ALTER TABLE reportes_diaD DROP FOREIGN KEY reportes_diaD_ibfk_1");
        echo "FK reportes_diaD_ibfk_1 eliminada.\n";
    } catch(Exception $e) {
        echo "Aviso: No se pudo eliminar reportes_diaD_ibfk_1 (quiza no existe): " . $e->getMessage() . "\n";
    }

    echo "2. Agregando FK correcta hacia colaboradores(id)...\n";
    $db->exec("ALTER TABLE reportes_diaD ADD CONSTRAINT fk_reportes_colaborador FOREIGN KEY (id_colaborador) REFERENCES colaboradores(id) ON DELETE CASCADE");
    echo "FK fk_reportes_colaborador agregada exitosamente.\n";

} catch (Exception $e) {
    echo "ERROR CRITICO: " . $e->getMessage();
}
