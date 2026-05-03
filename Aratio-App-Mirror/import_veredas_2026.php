<?php
/**
 * Script robusto para importar veredas_batch_2026.sql
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

set_time_limit(1800);
ini_set('memory_limit', '512M');

$sqlFile = __DIR__ . '/veredas_batch_2026.sql';

if (!file_exists($sqlFile)) {
    die("Error: El archivo SQL no existe: $sqlFile\n");
}

$fileSize = filesize($sqlFile);
echo "Archivo SQL encontrado. Tamaño: " . round($fileSize / 1024 / 1024, 2) . " MB\n";

try {
    $db = getDB();
    echo "Conectado a la BD.\n";

    $handle = fopen($sqlFile, "r");
    if (!$handle) {
        die("Error al abrir archivo.\n");
    }

    echo "Procesando...\n";

    $currentQuery = "";
    $queriesExecuted = 0;
    $errors = 0;

    while (($line = fgets($handle)) !== false) {
        $currentQuery .= $line;
        
        // Si encontramos un punto y coma al final de lo que llevamos acumulado
        if (preg_match('/;\s*$/', $currentQuery)) {
            try {
                $db->exec($currentQuery);
                $queriesExecuted++;
                if ($queriesExecuted % 50 == 0) {
                    echo "Importadas: $queriesExecuted\n";
                    flush();
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    $errors++;
                    echo "Error en $queriesExecuted: " . substr($e->getMessage(), 0, 100) . "\n";
                }
            }
            $currentQuery = "";
        }
    }

    if (!empty(trim($currentQuery))) {
         try {
            $db->exec($currentQuery);
            $queriesExecuted++;
        } catch (Exception $e) {
             if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                $errors++;
                echo "Error final: " . $e->getMessage() . "\n";
             }
        }
    }

    fclose($handle);

    echo "\nRESUMEN:\n";
    echo "- Sentencias exitosas: $queriesExecuted\n";
    echo "- Errores (no duplicados): $errors\n";
    echo "¡FIN!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
