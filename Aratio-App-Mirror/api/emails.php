<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);

try {
    require_once __DIR__ . '/../includes/EmailCampaigns.php';

    switch ($action) {
        case 'campanas':
            jsonResponse(['success' => true, 'data' => EmailCampaigns::getCampanas()]);
            break;
        case 'campana':
            handleCampana();
            break;
        case 'crear_campana':
            handleCrearCampana($userId);
            break;
        case 'generar_cola':
            handleGenerarCola();
            break;
        case 'procesar':
            handleProcesar();
            break;
        case 'plantillas':
            jsonResponse(['success' => true, 'data' => EmailCampaigns::getPlantillas()]);
            break;
        case 'crear_plantilla':
            handleCrearPlantilla();
            break;
        case 'historial':
            handleHistorial();
            break;
        case 'stats':
            jsonResponse(['success' => true, 'data' => EmailCampaigns::getStats()]);
            break;
        case 'track_open':
            handleTrackOpen();
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("EmailCampaigns API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function handleCampana(): void
{
    $id = (int)($_GET['id'] ?? 0);
    $campana = EmailCampaigns::getCampana($id);
    if (!$campana) jsonResponse(['success' => false, 'message' => 'Campaña no encontrada'], 404);
    jsonResponse(['success' => true, 'data' => $campana]);
}

function handleCrearCampana(int $userId): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['nombre']) || empty($input['asunto']) || empty($input['cuerpo_html'])) {
        jsonResponse(['success' => false, 'message' => 'nombre, asunto y cuerpo_html requeridos'], 400);
    }

    $id = EmailCampaigns::crearCampana(
        $input['nombre'],
        $input['asunto'],
        $input['cuerpo_html'],
        $input['filtros'] ?? [],
        $input['programada_para'] ?? null,
        $userId
    );

    jsonResponse(['success' => true, 'message' => 'Campaña creada', 'data' => ['id' => $id]]);
}

function handleGenerarCola(): void
{
    $campanaId = (int)($_GET['campana_id'] ?? 0);
    if (!$campanaId) jsonResponse(['success' => false, 'message' => 'campana_id requerido'], 400);

    $total = EmailCampaigns::generarCola($campanaId, []);
    jsonResponse(['success' => true, 'message' => "Cola generada: $total destinatarios", 'total' => $total]);
}

function handleProcesar(): void
{
    $resultado = EmailCampaigns::procesarCola(20);
    jsonResponse(['success' => true] + $resultado);
}

function handleCrearPlantilla(): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['nombre']) || empty($input['asunto']) || empty($input['cuerpo_html'])) {
        jsonResponse(['success' => false, 'message' => 'nombre, asunto y cuerpo_html requeridos'], 400);
    }

    EmailCampaigns::crearPlantilla(
        $input['nombre'],
        $input['asunto'],
        $input['cuerpo_html'],
        $input['variables'] ?? []
    );

    jsonResponse(['success' => true, 'message' => 'Plantilla guardada']);
}

function handleHistorial(): void
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $campanaId = (int)($_GET['campana_id'] ?? 0);
    $result = EmailCampaigns::getHistorial($campanaId, $page);
    jsonResponse(['success' => true] + $result);
}

function handleTrackOpen(): void
{
    $campanaId = (int)($_GET['campana_id'] ?? 0);
    $email = $_GET['email'] ?? '';

    if ($campanaId && $email) {
        EmailCampaigns::procesarApertura($email, $campanaId);
    }

    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}
