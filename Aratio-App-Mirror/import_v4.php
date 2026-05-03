<?php
/**
 * Script de importación V4 - Simplificado
 */
require_once __DIR__ . '/config/config.php';
header('Content-Type: text/plain; charset=utf-8');

set_time_limit(1800);
ini_set('memory_limit', '512M');

$sqlFile = __DIR__ . '/veredas_batch_2026.sql';

try {
    $db = getDB();
    
    $startCount = $db->query("SELECT COUNT(*) FROM territorios")->fetchColumn();
    echo "Conteo Inicial: $startCount\n";

    $handle = fopen($sqlFile, "r");
    echo "Procesando...\n";

    $executed = 0;
    $dupes = 0;
    $errors = 0;

    while (($line = fgets($handle)) !== false) {
        $q = trim($line);
        if (empty($q)) continue;
        
        try {
            $db->exec($q);
            $executed++;
            if ($executed % 100 == 0) echo "Paso: $executed\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $dupes++;
            } else {
                $errors++;
                echo "Err: " . substr($e->getMessage(), 0, 50) . "\n";
            }
        }
    }
    
    fclose($handle);
    
    $endCount = $db->query("SELECT COUNT(*) FROM territorios")->fetchColumn();
    echo "\nRESUMEN:\n";
    echo "- Nuevos registros: " . ($endCount - $startCount) . "\n";
    echo "- Sentencias ejecutadas: $executed\n";
    echo "- Duplicados saltados: $dupes\n";
    echo "- Errores: $errors\n";
    echo "- Conteo Final: $endCount\n";

} catch (Exception $e) {
    echo "FATAL: " . $e->getMessage();
}
