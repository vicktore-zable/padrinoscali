<?php
require 'root_config.php';
$db = getDB();
echo "--- Tablas en u577647812_aratio ---\n";
$stmt = $db->query('SHOW TABLES');
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));

echo "\n--- Estructura de 'jac' (si existe) ---\n";
try {
    $stmt = $db->query('DESCRIBE jac');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) {
    echo "Tabla 'jac' no encontrada.\n";
}

echo "\n--- Estructura de 'organizaciones' (si existe) ---\n";
try {
    $stmt = $db->query('DESCRIBE organizaciones');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) {
    echo "Tabla 'organizaciones' no encontrada.\n";
}
