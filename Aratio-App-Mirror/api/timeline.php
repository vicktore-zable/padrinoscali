<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            handleList($db);
            break;
        case 'stats':
            handleStatsByColaborador($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("ALAS Timeline API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function handleList(PDO $db): void
{
    $colaboradorId = (int)($_GET['colaborador_id'] ?? 0);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(50, max(10, (int)($_GET['per_page'] ?? 20)));
    $tipos = isset($_GET['tipos']) ? (array)$_GET['tipos'] : [];

    if (!$colaboradorId) {
        jsonResponse(['success' => false, 'message' => 'colaborador_id requerido'], 400);
    }

    require_once __DIR__ . '/../includes/ActivityLogger.php';
    $result = ActivityLogger::getByColaborador($colaboradorId, $tipos, $page, $perPage);

    jsonResponse(['success' => true, 'data' => $result]);
}

function handleStatsByColaborador(PDO $db): void
{
    $colaboradorId = (int)($_GET['colaborador_id'] ?? 0);

    if (!$colaboradorId) {
        jsonResponse(['success' => false, 'message' => 'colaborador_id requerido'], 400);
    }

    require_once __DIR__ . '/../includes/ActivityLogger.php';
    jsonResponse(['success' => true, 'data' => ActivityLogger::getStats($colaboradorId)]);
}
