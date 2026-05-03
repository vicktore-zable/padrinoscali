<?php
/**
 * Script temporal para importar territorios_21022026.sql
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

// Aumentar límites para archivo grande
set_time_limit(1200);
ini_set('memory_limit', '512M');

$sqlFile = __DIR__ . '/territorios_21022026.sql';

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
                // Si es un error de duplicado (ID), es aceptable si ya existen records
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    // echo "Saliendo: ID duplicado\n";
                } else {
                    echo "Error en sentencia $queriesExecuted: " . $e->getMessage() . "\n";
                    echo "Query problemático (fragmento): " . substr($currentQuery, 0, 100) . "...\n";
                }
            }
            $currentQuery = "";
        }
    }

    fclose($handle);

    echo "\n¡PROCESO FINALIZADO!\n";
    echo "Total de sentencias ejecutadas: $queriesExecuted\n";

} catch (Exception $e) {
    echo "\nERROR CRÍTICO:\n";
    echo $e->getMessage() . "\n";
}
