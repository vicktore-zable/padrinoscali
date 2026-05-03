<?php
/**
 * Script temporal para importar territorios con polígonos
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

// Aumentar límites para archivo de 14MB
set_time_limit(600);
ini_set('memory_limit', '256M');

$sqlFile = __DIR__ . '/territorios_con_poligonos.sql';

if (!file_exists($sqlFile)) {
    die("Error: El archivo SQL no existe en el servidor: $sqlFile\n");
}

try {
    $db = getDB();
    echo "Conectado a la base de datos...\n";

    $handle = fopen($sqlFile, "r");
    if (!$handle) {
        die("Error: No se pudo abrir el archivo para lectura.\n");
    }

    echo "Iniciando importación línea por línea...\n";

    $currentQuery = "";
    $queriesExecuted = 0;

    while (($line = fgets($handle)) !== false) {
        $trimmedLine = trim($line);
        if (empty($trimmedLine) || strpos($trimmedLine, '--') === 0) continue;

        $currentQuery .= $line;

        // Si la línea termina en punto y coma, es el fin de una sentencia
        if (substr($trimmedLine, -1) === ';') {
            try {
                $db->exec($currentQuery);
                $queriesExecuted++;
                if ($queriesExecuted % 10 == 0) {
                    echo "Progreso: Sentencias ejecutadas: $queriesExecuted\n";
                    flush();
                }
            } catch (Exception $e) {
                echo "Error en sentencia $queriesExecuted: " . $e->getMessage() . "\n";
                echo "Query problemático (fragmento): " . substr($currentQuery, 0, 100) . "...\n";
                // Continuamos con el siguiente
            }
            $currentQuery = "";
        }
    }

    fclose($handle);

    echo "\n¡PROCESO FINALIZADO!\n";
    echo "Total de sentencias ejecutadas: $queriesExecuted\n";

    // Limpieza opcional
    // unlink($sqlFile);

} catch (Exception $e) {
    echo "\nERROR CRÍTICO:\n";
    echo $e->getMessage() . "\n";
}
