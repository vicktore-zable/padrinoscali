<?php
/**
 * API REST para obtener Territorios en formato GeoJSON
 * Útil para visualizar polígonos en mapas (Leaflet / Mapbox)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Intentar localizar el archivo de configuración
    $config_paths = [
        __DIR__ . '/config/config.php',
        __DIR__ . '/../config/config.php',
        dirname(__DIR__) . '/config/config.php'
    ];
    
    $configPath = null;
    foreach ($config_paths as $path) {
        if (file_exists($path)) {
            $configPath = $path;
            break;
        }
    }
    
    if (!$configPath) {
        throw new Exception('Configuración del sistema no encontrada');
    }
    
    require_once $configPath;
    
    // Obtener conexión a BD (usa la función estándar del proyecto)
    $db = getDB();
    if (!$db) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
    }

    // Parámetros de filtrado
    $municipio = $_GET['municipio'] ?? null;
    $territorio = $_GET['territorio'] ?? null; // Ej: "Comuna 1" o "Comuna 01"
    $tipo = $_GET['tipo'] ?? null;             // Ej: "Urbano" o "Rural"
    $barrio = $_GET['barrio'] ?? null;         // Ej: "Aguacatal"

    if (!$municipio) {
        echo json_encode([
            'type' => 'FeatureCollection',
            'features' => [],
            'message' => 'Parámetro municipio es requerido'
        ]);
        exit;
    }

    // Consulta con ST_AsGeoJSON para que MySQL devuelva JSON directamente
    $sql = "SELECT 
                id, 
                departamento, 
                municipio, 
                Tipo_territorio, 
                cod_mpio,
                Código,
                Territorio, 
                barrio, 
                ST_AsGeoJSON(geometria) as geometry_json 
            FROM territorios 
            WHERE municipio = ?";
            
    $params = [$municipio];

    if ($territorio) {
        // Limpiamos el territorio para que coincida "Comuna 1" con "Comuna 01" si fuera necesario
        // Usamos LIKE para mayor flexibilidad
        $sql .= " AND (Territorio = ? OR Territorio LIKE ?)";
        $params[] = $territorio;
        $cleanTerritorio = str_replace(' 0', ' ', $territorio);
        $params[] = "%$cleanTerritorio%";
    }

    if ($barrio) {
        $sql .= " AND barrio = ?";
        $params[] = $barrio;
    }

    if ($tipo) {
        $sql .= " AND Tipo_territorio = ?";
        $params[] = $tipo;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $features = [];
    foreach ($results as $row) {
        $geometry = json_decode($row['geometry_json'], true);
        unset($row['geometry_json']); // Limpiar para no duplicar datos
        
        $features[] = [
            'type' => 'Feature',
            'id' => $row['id'],
            'geometry' => $geometry,
            'properties' => $row
        ];
    }

    $response = [
        'type' => 'FeatureCollection',
        'metadata' => [
            'municipio' => $municipio,
            'count' => count($features),
            'generated_at' => date('Y-m-d H:i:s')
        ],
        'features' => $features
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'type' => 'FeatureCollection',
        'features' => [],
        'error' => $e->getMessage()
    ]);
}
