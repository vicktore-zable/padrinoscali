<?php
require_once __DIR__ . '/config/config.php';
header('Content-Type: text/plain; charset=utf-8');
try {
    $db = getDB();
    // Get 10 veredas from the municipalities we just added
    $sql = "SELECT municipio, barrio as vereda, id FROM territorios 
            WHERE municipio NOT IN ('Cali', 'Buenaventura', 'Palmira', 'Jamundí', 'Cartago', 'Yumbo') 
            AND Tipo_territorio = 'Rural' 
            LIMIT 10";
    $stmt = $db->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Muestreo de Veredas Integradas:\n";
    foreach ($results as $row) {
        echo "- {$row['municipio']}: {$row['vereda']} (ID: {$row['id']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
