<?php
require 'h:/Mi unidad/2026/Cali/Edisongiraldo.com/config/config.php';
try {
    $db = getDB();
    
    $stmt = $db->query('SHOW COLUMNS FROM colaboradores');
    echo "--- Tabla colaboradores ---\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' (' . $row['Type'] . ")\n";
    }
    
    $stmt = $db->query('SHOW COLUMNS FROM curriculum');
    echo "\n--- Tabla curriculum ---\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' (' . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
