<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';
$campanaId = (int)($_SESSION['campana_activa'] ?? 0);

try {
    switch ($action) {
        case 'ranking':
            handleRanking($db, $campanaId);
            break;
        case 'detalle':
            handleDetalle($db, $campanaId);
            break;
        case 'feed':
            handleFeed($db);
            break;
        case 'notificaciones':
            handleNotificaciones($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("Lideres API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function handleRanking(PDO $db, int $campanaId): void
{
    $orden = $_GET['orden'] ?? 'seguidores';
    $limite = min(100, max(1, (int)($_GET['limite'] ?? 50)));

    $orderCol = match ($orden) {
        'eventos' => 'total_eventos',
        'contactadas' => 'personas_contactadas',
        'donaciones' => 'donaciones_gestionadas',
        default => 'num_seguidores'
    };

    $stmt = $db->prepare("
        SELECT c.id, c.documento, c.nombres, c.apellidos, c.perfil,
               c.municipio, c.barrio, c.telefono, c.email,
               c.dato_potencial, c.nivel_participacion,
               c.fecha_nacimiento,
               (SELECT COUNT(*) FROM colaboradores s 
                WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
                  AND (s.estado IS NULL OR s.estado NOT IN ('inactivo','Inactivo'))
               ) AS num_seguidores,
               (SELECT COUNT(*) FROM colaboradores s 
                WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
               ) AS total_seguidores,
               (SELECT COUNT(*) FROM eventos e 
                JOIN colaboradores s ON e.colaborador_id = s.id
                WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
               ) AS total_eventos,
               (SELECT COALESCE(SUM(d.monto), 0) FROM donaciones d 
                JOIN colaboradores s ON d.colaborador_id = s.id
                WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
                  AND d.estado = 'confirmada'
               ) AS donaciones_gestionadas,
               (SELECT COALESCE(SUM(ac.personas_contactadas), 0) FROM acciones_comunitarias ac 
                JOIN colaboradores s ON ac.colaborador_id = s.id
                WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
               ) AS personas_contactadas,
               (SELECT COUNT(*) FROM actividad_colaborador ac 
                JOIN colaboradores s ON ac.colaborador_id = s.id
                WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
                  AND ac.creado_en >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               ) AS actividad_30d
        FROM colaboradores c
        WHERE c.campana_id = ?
          AND c.perfil = 'Lider'
          AND EXISTS (SELECT 1 FROM colaboradores s WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id)
        HAVING num_seguidores > 0
        ORDER BY $orderCol DESC
        LIMIT $limite
    ");
    $stmt->execute([$campanaId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalLideres = count($data);
    $totalSeguidores = array_sum(array_column($data, 'num_seguidores'));

    jsonResponse(['success' => true, 'data' => $data, 'total_lideres' => $totalLideres, 'total_seguidores' => $totalSeguidores]);
}

function handleDetalle(PDO $db, int $campanaId): void
{
    $documento = $_GET['documento'] ?? '';

    if (empty($documento)) {
        jsonResponse(['success' => false, 'message' => 'documento requerido'], 400);
    }

    $stmt = $db->prepare("
        SELECT c.*,
               (SELECT COUNT(*) FROM colaboradores s 
                WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
               ) AS total_seguidores,
               (SELECT COUNT(*) FROM colaboradores s 
                WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
                  AND (s.estado IS NULL OR s.estado NOT IN ('inactivo','Inactivo'))
               ) AS seguidores_activos
        FROM colaboradores c
        WHERE c.documento = ? AND c.campana_id = ?
    ");
    $stmt->execute([$documento, $campanaId]);
    $lider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lider) {
        jsonResponse(['success' => false, 'message' => 'Líder no encontrado'], 404);
    }

    // Seguidores
    $stmt = $db->prepare("
        SELECT id, documento, nombres, apellidos, perfil, nivel_participacion,
               municipio, barrio, telefono, dato_potencial,
               DATE_FORMAT(creado_en, '%Y-%m-%d') AS creado_en
        FROM colaboradores
        WHERE lider_directo = ? AND campana_id = ?
        ORDER BY creado_en DESC
    ");
    $stmt->execute([$documento, $campanaId]);
    $lider['seguidores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Perfiles de seguidores
    $stmt = $db->prepare("
        SELECT perfil, COUNT(*) AS total
        FROM colaboradores
        WHERE lider_directo = ? AND campana_id = ?
        GROUP BY perfil ORDER BY total DESC
    ");
    $stmt->execute([$documento, $campanaId]);
    $lider['perfiles_seguidores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['success' => true, 'data' => $lider]);
}

function handleFeed(PDO $db): void
{
    $documento = $_GET['documento'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 30;
    $offset = ($page - 1) * $perPage;

    if (empty($documento)) {
        jsonResponse(['success' => false, 'message' => 'documento requerido'], 400);
    }

    $stmt = $db->prepare("SELECT id, campana_id FROM colaboradores WHERE documento = ?");
    $stmt->execute([$documento]);
    $lider = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$lider) {
        jsonResponse(['success' => false, 'message' => 'Líder no encontrado'], 404);
    }

    $countStmt = $db->prepare("
        SELECT COUNT(*) FROM actividad_colaborador ac
        JOIN colaboradores s ON ac.colaborador_id = s.id
        WHERE s.lider_directo = ? AND s.campana_id = ?
    ");
    $countStmt->execute([$documento, $lider['campana_id']]);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT ac.*, s.nombres, s.apellidos, s.perfil, s.municipio
        FROM actividad_colaborador ac
        JOIN colaboradores s ON ac.colaborador_id = s.id
        WHERE s.lider_directo = ? AND s.campana_id = ?
        ORDER BY ac.creado_en DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute([$documento, $lider['campana_id']]);
    $actividad = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        'success' => true,
        'data' => $actividad,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $perPage)
    ]);
}

function handleNotificaciones(PDO $db): void
{
    $documento = $_GET['documento'] ?? '';

    if (empty($documento)) {
        // Si no hay documento, devolver resumen global
        $stmt = $db->query("
            SELECT COUNT(*) AS total FROM colaboradores
            WHERE perfil = 'Lider'
              AND EXISTS (SELECT 1 FROM colaboradores s WHERE s.lider_directo = colaboradores.documento)
        ");
        $totalLideres = (int)$stmt->fetchColumn();

        jsonResponse(['success' => true, 'data' => [
            'total_lideres' => $totalLideres,
            'total_seguidores' => (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE lider_directo IS NOT NULL")->fetchColumn(),
        ]]);
        return;
    }

    $stmt = $db->prepare("SELECT id, campana_id FROM colaboradores WHERE documento = ?");
    $stmt->execute([$documento]);
    $lider = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$lider) {
        jsonResponse(['success' => false, 'message' => 'Líder no encontrado'], 404);
    }

    // Cumpleaños próximos (próximos 7 días)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM colaboradores
        WHERE lider_directo = ? AND campana_id = ?
          AND fecha_nacimiento IS NOT NULL
          AND DATE_FORMAT(fecha_nacimiento, '%m-%d') BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d')
    ");
    $stmt->execute([$documento, $lider['campana_id']]);
    $cumpleanosProximos = (int)$stmt->fetchColumn();

    // Seguidores nuevos (últimos 7 días)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM colaboradores
        WHERE lider_directo = ? AND campana_id = ?
          AND creado_en >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$documento, $lider['campana_id']]);
    $nuevosSeguidores = (int)$stmt->fetchColumn();

    // Actividad reciente del equipo (últimos 7 días)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM actividad_colaborador ac
        JOIN colaboradores s ON ac.colaborador_id = s.id
        WHERE s.lider_directo = ? AND s.campana_id = ?
          AND ac.creado_en >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$documento, $lider['campana_id']]);
    $actividadReciente = (int)$stmt->fetchColumn();

    jsonResponse(['success' => true, 'data' => [
        'cumpleanos_proximos' => $cumpleanosProximos,
        'nuevos_seguidores' => $nuevosSeguidores,
        'actividad_reciente' => $actividadReciente,
    ]]);
}
