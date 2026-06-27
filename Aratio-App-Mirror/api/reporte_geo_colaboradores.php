<?php
/**
 * API: Reporte Geográfico - Colaboradores por Territorio
 * Devuelve conteos de colaboradores/líderes agrupados por polígono territorial
 * Para colorear mapa Leaflet en reporte geográfico
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

try {
    $db = getDB();

    $campanaId = intval($_GET['campana_id'] ?? 0);
    $municipio = $_GET['municipio'] ?? '';
    $tipoTerritorio = $_GET['tipo_territorio'] ?? '';
    $territorio = $_GET['territorio'] ?? '';
    $barrio = $_GET['barrio'] ?? '';

    if (!$campanaId) {
        jsonResponse(['success' => false, 'message' => 'campana_id requerido'], 400);
    }

    if (!$municipio) {
        jsonResponse(['success' => false, 'message' => 'municipio requerido'], 400);
    }

    // Construir WHERE dinámico para territorios
    $whereT = ["t.municipio = ?"];
    $paramsT = [$municipio];

    if ($tipoTerritorio) {
        $whereT[] = "t.Tipo_territorio = ?";
        $paramsT[] = $tipoTerritorio;
    }
    if ($territorio) {
        $whereT[] = "t.Territorio = ?";
        $paramsT[] = $territorio;
    }
    if ($barrio) {
        $whereT[] = "t.barrio = ?";
        $paramsT[] = $barrio;
    }

    // Obtener conteos por territorio_id
    $sql = "
        SELECT
            t.id AS territorio_id,
            t.Tipo_territorio,
            t.Territorio,
            t.barrio,
            COUNT(c.id) AS total_colaboradores,
            SUM(CASE WHEN c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%' THEN 1 ELSE 0 END) AS total_lideres,
            SUM(CASE WHEN c.genero = 'F' THEN 1 ELSE 0 END) AS total_mujeres,
            SUM(CASE WHEN c.genero = 'M' THEN 1 ELSE 0 END) AS total_hombres
        FROM territorios t
        LEFT JOIN colaboradores c
            ON c.barrio = t.barrio
            AND c.departamento = t.departamento
            AND c.campana_id = ?
            AND (c.estado IS NULL OR c.estado NOT IN ('inactivo', 'Inactivo'))
        WHERE " . implode(" AND ", $whereT) . "
        GROUP BY t.id, t.Tipo_territorio, t.Territorio, t.barrio
        ORDER BY t.Territorio, t.barrio
    ";

    $paramsTFull = array_merge([$campanaId], $paramsT);
    $stmt = $db->prepare($sql);
    $stmt->execute($paramsTFull);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Resumen general para KPIs
    $totalColab = array_sum(array_column($data, 'total_colaboradores'));
    $totalLideres = array_sum(array_column($data, 'total_lideres'));
    $totalMujeres = array_sum(array_column($data, 'total_mujeres'));
    $totalHombres = array_sum(array_column($data, 'total_hombres'));

    // Agrupar por territorio (comuna/sector) para la lista lateral
    $agrupado = [];
    foreach ($data as $row) {
        $key = $row['Territorio'] ?: 'Sin territorio';
        if (!isset($agrupado[$key])) {
            $agrupado[$key] = [
                'nombre' => $key,
                'tipo' => $row['Tipo_territorio'],
                'total_colaboradores' => 0,
                'total_lideres' => 0,
                'total_mujeres' => 0,
                'total_hombres' => 0,
                'barrios' => []
            ];
        }
        $agrupado[$key]['total_colaboradores'] += intval($row['total_colaboradores']);
        $agrupado[$key]['total_lideres'] += intval($row['total_lideres']);
        $agrupado[$key]['total_mujeres'] += intval($row['total_mujeres']);
        $agrupado[$key]['total_hombres'] += intval($row['total_hombres']);
        $agrupado[$key]['barrios'][] = $row;
    }

    $listaTerritorios = array_values($agrupado);

    // Construir un mapa rápido territorio_id -> counts para colorear polígonos
    $mapaConteos = [];
    foreach ($data as $row) {
        $mapaConteos[intval($row['territorio_id'])] = [
            'total' => intval($row['total_colaboradores']),
            'lideres' => intval($row['total_lideres']),
            'mujeres' => intval($row['total_mujeres']),
            'hombres' => intval($row['total_hombres']),
            'barrio' => $row['barrio'],
            'territorio' => $row['Territorio'],
            'tipo' => $row['Tipo_territorio']
        ];
    }

    jsonResponse([
        'success' => true,
        'data' => $data,
        'resumen' => [
            'total_colaboradores' => intval($totalColab),
            'total_lideres' => intval($totalLideres),
            'total_mujeres' => intval($totalMujeres),
            'total_hombres' => intval($totalHombres)
        ],
        'lista_territorios' => $listaTerritorios,
        'mapa_conteos' => $mapaConteos,
        'filtros' => [
            'campana_id' => $campanaId,
            'municipio' => $municipio,
            'tipo_territorio' => $tipoTerritorio,
            'territorio' => $territorio,
            'barrio' => $barrio
        ]
    ]);

} catch (Exception $e) {
    error_log("API reporte_geo error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
