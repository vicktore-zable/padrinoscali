<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);

try {
    require_once __DIR__ . '/../includes/PhoneBanking.php';

    switch ($action) {
        case 'campanas':
            handleCampanas($db);
            break;
        case 'campana':
            handleCampana($db);
            break;
        case 'crear_campana':
            handleCrearCampana($db, $userId);
            break;
        case 'generar_cola':
            handleGenerarCola($db);
            break;
        case 'siguiente':
            handleSiguiente($db, $userId);
            break;
        case 'resultado':
            handleResultado($db, $userId);
            break;
        case 'mi_cola':
            handleMiCola($db, $userId);
            break;
        case 'historial':
            handleHistorial($db);
            break;
        case 'stats':
            handleStats($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("PhoneBanking API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function handleCampanas(PDO $db): void
{
    $estado = $_GET['estado'] ?? null;
    $campanas = PhoneBanking::getCampanas($estado);
    jsonResponse(['success' => true, 'data' => $campanas]);
}

function handleCampana(PDO $db): void
{
    $id = (int)($_GET['id'] ?? 0);
    $campana = PhoneBanking::getCampana($id);
    if (!$campana) {
        jsonResponse(['success' => false, 'message' => 'Campaña no encontrada'], 404);
    }
    jsonResponse(['success' => true, 'data' => $campana]);
}

function handleCrearCampana(PDO $db, int $userId): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $nombre = trim($input['nombre'] ?? '');
    if (empty($nombre)) {
        jsonResponse(['success' => false, 'message' => 'Nombre requerido'], 400);
    }

    $id = PhoneBanking::crearCampana(
        $nombre,
        trim($input['descripcion'] ?? ''),
        trim($input['objetivo'] ?? ''),
        $input['filtros'] ?? [],
        $userId
    );

    jsonResponse(['success' => true, 'message' => 'Campaña creada', 'data' => ['id' => $id]]);
}

function handleGenerarCola(PDO $db): void
{
    $campanaId = (int)($_GET['campana_id'] ?? 0);
    if (!$campanaId) jsonResponse(['success' => false, 'message' => 'campana_id requerido'], 400);

    $total = PhoneBanking::generarCola($campanaId);
    jsonResponse(['success' => true, 'message' => "Cola generada: $total colaboradores", 'total' => $total]);
}

function handleSiguiente(PDO $db, int $userId): void
{
    $campanaId = (int)($_GET['campana_id'] ?? 0);
    if (!$campanaId) jsonResponse(['success' => false, 'message' => 'campana_id requerido'], 400);

    $siguiente = PhoneBanking::siguienteLlamada($campanaId, $userId);
    if (!$siguiente) {
        jsonResponse(['success' => true, 'data' => null, 'message' => 'No hay más llamadas pendientes']);
        return;
    }

    jsonResponse(['success' => true, 'data' => $siguiente]);
}

function handleResultado(PDO $db, int $userId): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $colaId = (int)($input['cola_id'] ?? 0);
    $resultado = $input['resultado'] ?? '';

    if (!$colaId || !$resultado) {
        jsonResponse(['success' => false, 'message' => 'cola_id y resultado requeridos'], 400);
    }

    $ok = PhoneBanking::registrarResultado(
        $colaId,
        $resultado,
        trim($input['notas'] ?? ''),
        (int)($input['duracion_seg'] ?? 0),
        $userId
    );

    if (!$ok) {
        jsonResponse(['success' => false, 'message' => 'Resultado inválido'], 400);
    }

    jsonResponse(['success' => true, 'message' => 'Resultado registrado']);
}

function handleMiCola(PDO $db, int $userId): void
{
    $campanaId = (int)($_GET['campana_id'] ?? 0);
    $cola = PhoneBanking::getMiCola($userId, $campanaId);
    jsonResponse(['success' => true, 'data' => $cola]);
}

function handleHistorial(PDO $db): void
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $filtros = [];
    if (!empty($_GET['campana_id'])) $filtros['campana_id'] = (int)$_GET['campana_id'];
    if (!empty($_GET['agente_id'])) $filtros['agente_id'] = (int)$_GET['agente_id'];
    if (!empty($_GET['resultado'])) $filtros['resultado'] = $_GET['resultado'];

    $result = PhoneBanking::getHistorial($filtros, $page);
    jsonResponse(['success' => true] + $result);
}

function handleStats(PDO $db): void
{
    $campanaId = !empty($_GET['campana_id']) ? (int)$_GET['campana_id'] : null;
    $stats = PhoneBanking::getStats($campanaId);
    jsonResponse(['success' => true, 'data' => $stats]);
}
