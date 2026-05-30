<?php
/**
 * API Endpoint: Instagram Sync
 * 
 * Solo funciona en entorno local (XAMPP).
 * En producción (Hostinger) devuelve 403.
 * 
 * Actions:
 *   ?action=start  → Lanza scraper Python en background
 *   ?action=status → Lee estado del proceso
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

// Solo permitir en local
if (!$isLocal) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'La sincronización solo está disponible en el entorno local (XAMPP).'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';
$statusFile = BASE_PATH . '/storage/instagram_sync_status.json';
$scraperPath = BASE_PATH . '/instagram_scraper.py';
$outputFile = BASE_PATH . '/storage/instagram_data.json';

switch ($action) {

    case 'start':
        // Verificar que no haya otro proceso corriendo (timeout 10 min)
        $existingStatus = null;
        if (file_exists($statusFile)) {
            $existingStatus = json_decode(file_get_contents($statusFile), true);
        }

        if ($existingStatus && $existingStatus['status'] === 'running') {
            $elapsed = time() - ($existingStatus['timestamp'] ?? 0);
            if ($elapsed < 600) {
                http_response_code(409);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ya hay una sincronización en curso.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        // Verificar que el scraper existe
        if (!file_exists($scraperPath)) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encuentra el scraper en: ' . $scraperPath
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Escribir status inicial
        file_put_contents($statusFile, json_encode([
            'status' => 'running',
            'message' => 'Iniciando sincronización...',
            'current' => 0,
            'total' => 0,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE));

        // Lanzar scraper en background (Windows)
        $cmd = 'start /B cmd /C python ' . escapeshellarg($scraperPath)
            . ' --max-posts 5000'
            . ' --since-date 2024-01-01'
            . ' --monthly'
            . ' --output ' . escapeshellarg($outputFile)
            . ' --status-file ' . escapeshellarg($statusFile)
            . ' --markdown ' . escapeshellarg(BASE_PATH . '/timeline_concejal.md');

        pclose(popen($cmd, 'r'));

        echo json_encode([
            'status' => 'started',
            'message' => 'Sincronización iniciada correctamente.'
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
