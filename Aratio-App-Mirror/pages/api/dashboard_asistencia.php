<?php
/**
 * API: Dashboard de Asistencia
 * Proporciona datos agregados y filtrados para el dashboard de asistencia a eventos
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Verificar autenticación
session_start();
if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'No autorizado'], 401);
}

$db = getDB();

try {
    // Obtener parámetros de filtros
    $campanaId = $_GET['campana_id'] ?? null;
    $eventoId = $_GET['evento_id'] ?? null;
    $departamento = $_GET['departamento'] ?? null;
    $municipio = $_GET['municipio'] ?? null;
    $barrio = $_GET['barrio'] ?? null;
    $genero = $_GET['genero'] ?? null;
    $grupoEtareo = $_GET['grupo_etareo'] ?? null;
    $areaInteres = $_GET['area_interes'] ?? null;
    $habeasData = $_GET['habeas_data'] ?? null;

    if (!$campanaId) {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }

    // Construir query base con joins
    $whereConditions = ['e.campana_id = ?'];
    $params = [$campanaId];

    // Agregar filtros dinámicos
    if ($eventoId) {
        $whereConditions[] = 'a.evento_id = ?';
        $params[] = $eventoId;
    }
    if ($departamento) {
        $whereConditions[] = 'a.departamento = ?';
        $params[] = $departamento;
    }
    if ($municipio) {
        $whereConditions[] = 'a.municipio = ?';
        $params[] = $municipio;
    }
    if ($barrio) {
        $whereConditions[] = 'a.barrio LIKE ?';
        $params[] = '%' . $barrio . '%';
    }
    if ($genero) {
        $whereConditions[] = 'a.genero = ?';
        $params[] = $genero;
    }
    if ($grupoEtareo) {
        $whereConditions[] = 'a.grupo_etareo = ?';
        $params[] = $grupoEtareo;
    }
    if ($areaInteres) {
        $whereConditions[] = 'JSON_CONTAINS(a.areas_interes, ?, "$")';
        $params[] = '"' . $areaInteres . '"';
    }
    if ($habeasData !== null && $habeasData !== '') {
        $whereConditions[] = 'a.habeas_data = ?';
        $params[] = (int)$habeasData;
    }

    $whereClause = implode(' AND ', $whereConditions);

    // 1. OBTENER DATOS DE ASISTENTES
    $stmt = $db->prepare("
        SELECT
            a.id,
            a.nombre,
            a.documento,
            a.tipo_documento,
            a.telefono,
            a.email,
            a.fecha_nacimiento,
            a.genero,
            a.grupo_etareo,
            a.departamento,
            a.municipio,
            a.tipo_territorio,
            a.territorio,
            a.barrio,
            a.areas_interes,
            a.habeas_data,
            a.acepta_comunicaciones,
            a.notas,
            a.fecha_registro,
            a.metodo_registro,
            e.nombre as evento_nombre,
            e.fecha_inicio as evento_fecha
        FROM asistencia_eventos a
        INNER JOIN eventos e ON a.evento_id = e.id
        WHERE $whereClause
        ORDER BY a.fecha_registro DESC
    ");
    $stmt->execute($params);
    $asistentes = $stmt->fetchAll();

    // Decodificar areas_interes JSON
    foreach ($asistentes as &$asistente) {
        if ($asistente['areas_interes']) {
            $asistente['areas_interes'] = json_decode($asistente['areas_interes'], true);
        }
    }

    // 2. ESTADÍSTICAS GENERALES
    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN a.genero = 'masculino' THEN 1 ELSE 0 END) as masculino,
            SUM(CASE WHEN a.genero = 'femenino' THEN 1 ELSE 0 END) as femenino,
            SUM(CASE WHEN a.genero = 'otro' THEN 1 ELSE 0 END) as otro,
            SUM(CASE WHEN a.genero = 'prefiero-no-decir' THEN 1 ELSE 0 END) as no_especifica,
            SUM(CASE WHEN a.habeas_data = 1 THEN 1 ELSE 0 END) as habeas_data,
            SUM(CASE WHEN a.acepta_comunicaciones = 1 THEN 1 ELSE 0 END) as acepta_comunicaciones
        FROM asistencia_eventos a
        INNER JOIN eventos e ON a.evento_id = e.id
        WHERE $whereClause
    ");
    $stmt->execute($params);
    $stats = $stmt->fetch();

    // 3. DATOS PARA GRÁFICO DE GÉNERO
    $chartGenero = [
        'labels' => ['Masculino', 'Femenino', 'Otro', 'No especifica'],
        'datasets' => [[
            'data' => [
                (int)$stats['masculino'],
                (int)$stats['femenino'],
                (int)$stats['otro'],
                (int)$stats['no_especifica']
            ],
            'backgroundColor' => [
                'rgba(59, 130, 246, 0.8)',   // Azul
                'rgba(236, 72, 153, 0.8)',   // Rosa
                'rgba(168, 85, 247, 0.8)',   // Púrpura
                'rgba(156, 163, 175, 0.8)'   // Gris
            ],
            'borderColor' => [
                'rgba(59, 130, 246, 1)',
                'rgba(236, 72, 153, 1)',
                'rgba(168, 85, 247, 1)',
                'rgba(156, 163, 175, 1)'
            ],
            'borderWidth' => 2
        ]]
    ];

    // 4. DATOS PARA GRÁFICO DE GRUPO ETARIO
    $stmt = $db->prepare("
        SELECT
            a.grupo_etareo,
            COUNT(*) as cantidad
        FROM asistencia_eventos a
        INNER JOIN eventos e ON a.evento_id = e.id
        WHERE $whereClause AND a.grupo_etareo IS NOT NULL
        GROUP BY a.grupo_etareo
        ORDER BY
            CASE a.grupo_etareo
                WHEN 'Menor de 18' THEN 1
                WHEN '18-25' THEN 2
                WHEN '26-35' THEN 3
                WHEN '36-45' THEN 4
                WHEN '46-55' THEN 5
                WHEN '56-65' THEN 6
                WHEN 'Mayor de 65' THEN 7
                ELSE 8
            END
    ");
    $stmt->execute($params);
    $gruposEtareos = $stmt->fetchAll();

    $chartGrupoEtareo = [
        'labels' => array_column($gruposEtareos, 'grupo_etareo'),
        'datasets' => [[
            'label' => 'Asistentes',
            'data' => array_map('intval', array_column($gruposEtareos, 'cantidad')),
            'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
            'borderColor' => 'rgba(16, 185, 129, 1)',
            'borderWidth' => 2
        ]]
    ];

    // 5. DATOS PARA GRÁFICO DE ÁREAS DE INTERÉS
    // Extraer todas las áreas de interés de los JSON
    $areasInteresCount = [];
    foreach ($asistentes as $asistente) {
        if (!empty($asistente['areas_interes']) && is_array($asistente['areas_interes'])) {
            foreach ($asistente['areas_interes'] as $area) {
                if (!isset($areasInteresCount[$area])) {
                    $areasInteresCount[$area] = 0;
                }
                $areasInteresCount[$area]++;
            }
        }
    }

    // Ordenar por cantidad descendente y tomar top 10
    arsort($areasInteresCount);
    $areasInteresCount = array_slice($areasInteresCount, 0, 10, true);

    $chartAreasInteres = [
        'labels' => array_keys($areasInteresCount),
        'datasets' => [[
            'label' => 'Cantidad de personas',
            'data' => array_values($areasInteresCount),
            'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
            'borderColor' => 'rgba(245, 158, 11, 1)',
            'borderWidth' => 2
        ]]
    ];

    // 6. DATOS PARA GRÁFICO DE MUNICIPIOS (TOP 10)
    $stmt = $db->prepare("
        SELECT
            a.municipio,
            COUNT(*) as cantidad
        FROM asistencia_eventos a
        INNER JOIN eventos e ON a.evento_id = e.id
        WHERE $whereClause AND a.municipio IS NOT NULL
        GROUP BY a.municipio
        ORDER BY cantidad DESC
        LIMIT 10
    ");
    $stmt->execute($params);
    $municipios = $stmt->fetchAll();

    $chartMunicipios = [
        'labels' => array_column($municipios, 'municipio'),
        'datasets' => [[
            'label' => 'Asistentes',
            'data' => array_map('intval', array_column($municipios, 'cantidad')),
            'backgroundColor' => 'rgba(139, 92, 246, 0.8)',
            'borderColor' => 'rgba(139, 92, 246, 1)',
            'borderWidth' => 2
        ]]
    ];

    // 7. RESPUESTA COMPLETA
    jsonResponse([
        'success' => true,
        'data' => $asistentes,
        'stats' => $stats,
        'charts' => [
            'genero' => $chartGenero,
            'grupoEtareo' => $chartGrupoEtareo,
            'areasInteres' => $chartAreasInteres,
            'municipios' => $chartMunicipios
        ]
    ]);

} catch (Exception $e) {
    error_log("API dashboard_asistencia error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
}
