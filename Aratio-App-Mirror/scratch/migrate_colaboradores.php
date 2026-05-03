<?php
require 'config/config.php';
$db = getDB();
try {
    $db->exec("ALTER TABLE colaboradores ADD COLUMN foto VARCHAR(255) DEFAULT NULL AFTER mesa_votacion");
    echo "Columna 'foto' añadida exitosamente.";
} catch (Exception $e) {
    echo "Error o ya existe: " . $e->getMessage();
}
