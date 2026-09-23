<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/_security.php';
header('Content-Type: application/json');

$db = getDB();
$campanaId = isset($_GET['campana_id']) ? (int)$_GET['campana_id'] : null;
$action = $_GET['action'] ?? 'consolidado';

if (!$campanaId) {
    jsonResponse(['success' => false, 'message' => 'campana_id requerido'], 400);
    exit;
}

eventos_require_admin($campanaId);

if ($action === 'consolidado') {
    // Por barrio
    $stmt = $db->prepare("
        SELECT 
            ae.barrio,
            ae.municipio,
            COUNT(DISTINCT ae.evento_id) as total_eventos,
            COUNT(*) as total_asistentes,
            SUM(CASE WHEN ae.genero = 'masculino' THEN 1 ELSE 0 END) as masculino,
            SUM(CASE WHEN ae.genero = 'femenino' THEN 1 ELSE 0 END) as femenino,
            AVG(TIMESTAMPDIFF(YEAR, ae.fecha_nacimiento, CURDATE())) as edad_promedio
        FROM asistencia_eventos ae
        INNER JOIN eventos e ON ae.evento_id = e.id
        WHERE e.campana_id = ? AND ae.barrio IS NOT NULL AND ae.barrio != ''
        GROUP BY ae.barrio, ae.municipio
        ORDER BY total_asistentes DESC
        LIMIT 20
    ");
    $stmt->execute([$campanaId]);
    $porBarrio = $stmt->fetchAll();

    // Por municipio/comuna
    $stmt = $db->prepare("
        SELECT 
            ae.municipio,
            ae.departamento,
            COUNT(DISTINCT ae.evento_id) as total_eventos,
            COUNT(*) as total_asistentes,
            COUNT(DISTINCT ae.barrio) as barrios_distintos,
            SUM(CASE WHEN ae.genero = 'masculino' THEN 1 ELSE 0 END) as masculino,
            SUM(CASE WHEN ae.genero = 'femenino' THEN 1 ELSE 0 END) as femenino
        FROM asistencia_eventos ae
        INNER JOIN eventos e ON ae.evento_id = e.id
        WHERE e.campana_id = ? AND ae.municipio IS NOT NULL AND ae.municipio != ''
        GROUP BY ae.municipio, ae.departamento
        ORDER BY total_asistentes DESC
        LIMIT 20
    ");
    $stmt->execute([$campanaId]);
    $porMunicipio = $stmt->fetchAll();

    // Por tipo de evento
    $stmt = $db->prepare("
        SELECT 
            e.tipo,
            COUNT(DISTINCT e.id) as total_eventos,
            SUM(e.asistentes_confirmados) as total_asistentes,
            ROUND(AVG(e.asistentes_confirmados)) as promedio_asistentes
        FROM eventos e
        WHERE e.campana_id = ?
        GROUP BY e.tipo
        ORDER BY total_eventos DESC
    ");
    $stmt->execute([$campanaId]);
    $porTipo = $stmt->fetchAll();

    // Totales generales
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT ae.barrio) as barrios_con_asistencia,
            COUNT(DISTINCT ae.municipio) as municipios_con_asistencia,
            COUNT(DISTINCT ae.evento_id) as eventos_con_asistencia,
            COUNT(*) as total_asistencias
        FROM asistencia_eventos ae
        INNER JOIN eventos e ON ae.evento_id = e.id
        WHERE e.campana_id = ?
    ");
    $stmt->execute([$campanaId]);
    $totales = $stmt->fetch();

    jsonResponse([
        'success' => true,
        'data' => [
            'por_barrio' => $porBarrio,
            'por_municipio' => $porMunicipio,
            'por_tipo' => $porTipo,
            'totales' => $totales
        ]
    ]);
    exit;
}

jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
