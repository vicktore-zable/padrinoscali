<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';

try {
    require_once __DIR__ . '/../includes/MessengerBot.php';
    $bot = new MessengerBot($db);

    switch ($action) {
        case 'status':
            $stats = $bot->getFunnelStats();
            jsonResponse([
                'success' => true,
                'data' => [
                    'config' => $bot->getConfigStatus(),
                    'funnel' => $stats,
                ]
            ]);
            break;

        case 'scan':
            $sinceHours = max(1, min(168, (int)($_GET['hours'] ?? 24)));
            $result = $bot->scanComments($sinceHours);
            jsonResponse(['success' => true, 'data' => $result]);
            break;

        case 'funnel':
            $stats = $bot->getFunnelStats();
            jsonResponse(['success' => true, 'data' => $stats]);
            break;

        case 'triggers':
            $triggers = $bot->getTriggers();
            jsonResponse(['success' => true, 'data' => $triggers]);
            break;

        case 'save_trigger':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            if (empty($input['keyword']) || empty($input['auto_reply_template'])) {
                jsonResponse(['success' => false, 'message' => 'keyword y auto_reply_template requeridos'], 400);
            }
            $bot->saveTrigger($input);
            jsonResponse(['success' => true, 'message' => 'Trigger guardado']);
            break;

        case 'delete_trigger':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) jsonResponse(['success' => false, 'message' => 'id requerido'], 400);
            $bot->deleteTrigger($id);
            jsonResponse(['success' => true, 'message' => 'Trigger eliminado']);
            break;

        case 'capture':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $result = $bot->registerCapture($input);
            if (!$result['success']) {
                jsonResponse($result, 400);
            }
            jsonResponse(['success' => true, 'data' => $result]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => "Acción no válida: $action"], 400);
    }
} catch (\Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
