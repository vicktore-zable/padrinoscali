<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

try {
    echo "Checking 'territorios' table...\n";
    $stmt = $db->query("SHOW TABLES LIKE 'territorios'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "Table 'territorios' exists.\n";
        $count = $db->query("SELECT COUNT(*) FROM territorios")->fetchColumn();
        echo "Total rows: $count\n";
        
        if ($count > 0) {
            echo "Sample data:\n";
            $sample = $db->query("SELECT * FROM territorios LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            print_r($sample);
            
            echo "\nDistinct departments:\n";
            $deps = $db->query("SELECT DISTINCT departamento FROM territorios LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
            print_r($deps);
        } else {
            echo "Table 'territorios' is EMPTY.\n";
        }
    } else {
        echo "Table 'territorios' DOES NOT EXIST.\n";
    }

    echo "\nChecking 'puestos_votacion' table...\n";
    $stmt = $db->query("SHOW TABLES LIKE 'puestos_votacion'");
    if ($stmt->fetch()) {
        $count = $db->query("SELECT COUNT(*) FROM puestos_votacion")->fetchColumn();
        echo "Table 'puestos_votacion' exists with $count rows.\n";
    } else {
        echo "Table 'puestos_votacion' DOES NOT EXIST.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
