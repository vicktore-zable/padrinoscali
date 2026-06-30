<?php
/**
 * API Endpoint: Instagram Sync
 *
 * Actions:
 *   ?action=start  → Lee JSON maestro y marca completado
 *   ?action=status → Lee estado del proceso
 */

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$statusFile = BASE_PATH . '/storage/instagram_sync_status.json';
$masterPath = BASE_PATH . '/storage/maestro_instagram.json';

switch ($action) {

    case 'start':
        if (!file_exists($masterPath)) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encuentra el archivo maestro_instagram.json'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $data = json_decode(file_get_contents($masterPath), true);
        $total = $data['estadisticas']['total_publicaciones'] ?? 0;
        $timelineCount = count($data['timeline'] ?? []);

        file_put_contents($statusFile, json_encode([
            'status' => 'started',
            'message' => 'Procesando datos de Instagram...',
            'current' => 0,
            'total' => (int)$total,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE));

        echo json_encode([
            'status' => 'started',
            'message' => 'Procesando ' . $total . ' publicaciones en ' . $timelineCount . ' periodos...'
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'status':
        if (!file_exists($statusFile)) {
            echo json_encode([
                'status' => 'idle',
                'message' => 'No hay sincronizaciones recientes.'
            ], JSON_UNESCAPED_UNICODE);
            break;
        }

        $status = json_decode(file_get_contents($statusFile), true);

        if ($status['status'] === 'started') {
            $data = json_decode(file_get_contents($masterPath), true);
            $total = $data['estadisticas']['total_publicaciones'] ?? 0;
            $status['current'] = (int)$total;
            $status['total'] = (int)$total;
            $status['status'] = 'completed';
            $status['message'] = 'Se cargaron ' . $total . ' publicaciones correctamente';
            file_put_contents($statusFile, json_encode($status, JSON_UNESCAPED_UNICODE));
        }

        echo json_encode($status, JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Acción no válida. Usa ?action=start o ?action=status'
        ], JSON_UNESCAPED_UNICODE);
        break;
}
