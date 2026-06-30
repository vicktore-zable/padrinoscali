<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'reglas':
            handleReglas($db);
            break;
        case 'regla':
            handleRegla($db);
            break;
        case 'log':
            handleLog($db);
            break;
        case 'stats':
            handleStats($db);
            break;
        case 'toggle':
            handleToggle($db);
            break;
        case 'ejecutar_ahora':
            handleEjecutarAhora($db);
            break;
        case 'pendientes':
            handlePendientes($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("ALAS Workflows API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function handleReglas(PDO $db): void
{
    $stmt = $db->query("
        SELECT *, 
               ROUND(CASE WHEN ejecuciones_total > 0 
                    THEN (ejecuciones_exitosas * 100.0 / ejecuciones_total) 
                    ELSE 0 END, 1) as tasa_exito
        FROM workflow_reglas 
        ORDER BY prioridad ASC
    ");
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function handleRegla(PDO $db): void
{
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM workflow_reglas WHERE id = ?");
    $stmt->execute([$id]);
    $regla = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$regla) {
        jsonResponse(['success' => false, 'message' => 'Regla no encontrada'], 404);
    }

    $logStmt = $db->prepare("
        SELECT * FROM workflow_log WHERE regla_id = ? ORDER BY ejecutado_en DESC LIMIT 20
    ");
    $logStmt->execute([$id]);

    $regla['log_reciente'] = $logStmt->fetchAll(PDO::FETCH_ASSOC);
    $regla['acciones_json'] = json_decode($regla['acciones_json'], true);
    $regla['condiciones_json'] = json_decode($regla['condiciones_json'], true);

    jsonResponse(['success' => true, 'data' => $regla]);
}

function handleLog(PDO $db): void
{
    $reglaId = (int)($_GET['regla_id'] ?? 0);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $where = "";
    $params = [];

    if ($reglaId) {
        $where = "WHERE wl.regla_id = ?";
        $params[] = $reglaId;
    }

    $countStmt = $db->prepare("SELECT COUNT(*) FROM workflow_log wl {$where}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT wl.*, r.nombre as regla_nombre, 
               c.nombres, c.apellidos, c.documento
        FROM workflow_log wl
        JOIN workflow_reglas r ON wl.regla_id = r.id
        LEFT JOIN colaboradores c ON wl.colaborador_id = c.id
        {$where}
        ORDER BY wl.ejecutado_en DESC
        LIMIT {$limit} OFFSET {$offset}
    ");
    $stmt->execute($params);

    jsonResponse([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
}

function handleStats(PDO $db): void
{
    require_once __DIR__ . '/../includes/WorkflowEngine.php';
    jsonResponse(['success' => true, 'data' => WorkflowEngine::getStats()]);
}

function handleToggle(PDO $db): void
{
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT activo FROM workflow_reglas WHERE id = ?");
    $stmt->execute([$id]);
    $actual = $stmt->fetchColumn();

    if ($actual === false) {
        jsonResponse(['success' => false, 'message' => 'Regla no encontrada'], 404);
    }

    $nuevo = $actual ? 0 : 1;
    $db->prepare("UPDATE workflow_reglas SET activo = ? WHERE id = ?")->execute([$nuevo, $id]);

    jsonResponse(['success' => true, 'activo' => (bool)$nuevo]);
}

function handleEjecutarAhora(PDO $db): void
{
    $reglaId = (int)($_GET['regla_id'] ?? 0);
    $colaboradorId = (int)($_GET['colaborador_id'] ?? 0);

    if (!$reglaId || !$colaboradorId) {
        jsonResponse(['success' => false, 'message' => 'regla_id y colaborador_id requeridos'], 400);
    }

    require_once __DIR__ . '/../includes/WorkflowEngine.php';

    $stmt = $db->prepare("SELECT * FROM workflow_reglas WHERE id = ?");
    $stmt->execute([$reglaId]);
    $regla = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$regla) {
        jsonResponse(['success' => false, 'message' => 'Regla no encontrada'], 404);
    }

    WorkflowEngine::trigger($regla['trigger_evento'], ['colaborador_id' => $colaboradorId], $colaboradorId);

    jsonResponse(['success' => true, 'message' => 'Regla ejecutada manualmente']);
}

function handlePendientes(PDO $db): void
{
    $stmt = $db->query("
        SELECT wap.*, r.nombre as regla_nombre, c.nombres, c.apellidos
        FROM workflow_acciones_pendientes wap
        JOIN workflow_reglas r ON wap.regla_id = r.id
        LEFT JOIN colaboradores c ON wap.colaborador_id = c.id
        WHERE wap.estado = 'pending'
        ORDER BY wap.programado_para ASC, wap.id ASC
        LIMIT 50
    ");

    jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}
