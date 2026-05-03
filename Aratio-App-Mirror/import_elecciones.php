<?php
/**
 * Importador de alto rendimiento para mod_elecciones con campo Corporacion
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (php_sapi_name() === 'cli' && !isset($_SESSION)) {
    $_SESSION = [];
}

require_once __DIR__ . '/config/config.php';

set_time_limit(0);
ini_set('memory_limit', '1024M');

$db = getDB();
$csvFile = 'H:\Mi unidad\2026\yumbo\Corporaciones_valle_2019_2023.csv';
$batchSize = 2500;

if (!file_exists($csvFile)) {
    die("Archivo no encontrado: $csvFile\n");
}

$handle = fopen($csvFile, 'r');
if (!$handle) die("No se pudo abrir el archivo.\n");

// Saltar BOM si existe
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($handle);
}

$header = fgetcsv($handle, 0, ';'); // Leer encabezado

$count = 0;
$batch = [];
$startTime = microtime(true);

echo "Iniciando importación desde: $csvFile\n";

while (($data = fgetcsv($handle, 0, ';')) !== false) {
    if (count($data) < 10) continue; // Al menos hasta Corporacion (9)

    // Datos del archivo anterior sugerían 2023 por defecto o heurística
    // Mantendremos 2023 como base según el dashboard del usuario pero idealmente
    // el archivo tendría este dato. Como no lo tiene, seguiremos con 2023.
    $anio = 2023; 
    
    $batch[] = [
        'anio' => $anio,
        'departamento' => $data[0],
        'municipio' => $data[1],
        'puesto' => $data[2],
        'mesa' => (int)$data[3],
        'comuna' => $data[4],
        'partido' => $data[5],
        'candidato' => $data[6],
        'votos' => (int)$data[7],
        'corporacion' => $data[9] // Nueva columna en index 9
    ];

    $count++;

    if (count($batch) >= $batchSize) {
        insertBatch($db, $batch);
        $batch = [];
        if ($count % 25000 === 0) {
            echo "Procesados: $count registros... (" . round(microtime(true) - $startTime, 2) . "s)\n";
        }
    }
}

if (!empty($batch)) {
    insertBatch($db, $batch);
}

fclose($handle);

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);
echo "Finalizado exitosamente. Total: $count registros en $duration segundos.\n";

function insertBatch($db, $rows) {
    $sql = "INSERT INTO mod_elecciones (anio, departamento, municipio, puesto, mesa, comuna, partido, candidato, votos, corporacion) VALUES ";
    $placeholders = [];
    $values = [];
    
    foreach ($rows as $row) {
        $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $values[] = $row['anio'];
        $values[] = $row['departamento'];
        $values[] = $row['municipio'];
        $values[] = $row['puesto'];
        $values[] = $row['mesa'];
        $values[] = $row['comuna'];
        $row['partido'] = substr($row['partido'], 0, 255); // Truncar si es muy largo
        $values[] = $row['partido'];
        $values[] = $row['candidato'];
        $values[] = $row['votos'];
        $values[] = $row['corporacion'];
    }
    
    $sql .= implode(', ', $placeholders);
    $stmt = $db->prepare($sql);
    $stmt->execute($values);
}
