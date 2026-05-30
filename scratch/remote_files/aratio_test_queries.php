
<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = getDB();
    $stmt = $db->query("SELECT id, codigo, nombre, municipio, departamento FROM campanas LIMIT 1");
    print_r($stmt->fetchAll());
    echo "Campanas OK!\n";
} catch (Exception $e) {
    echo "Error campanas: " . $e->getMessage() . "\n";
}

try {
    $db = getDB();
    $stmt = $db->query("SELECT id, nombre, email, telefono, rol, estado, ultimo_acceso, created_at FROM usuarios LIMIT 1");
    print_r($stmt->fetchAll());
    echo "Usuarios OK!\n";
} catch (Exception $e) {
    echo "Error usuarios: " . $e->getMessage() . "\n";
}
