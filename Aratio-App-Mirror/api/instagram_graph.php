<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';

try {
    require_once __DIR__ . '/../includes/InstagramGraphApi.php';
    $ig = new InstagramGraphApi($db);

    switch ($action) {
        case 'status':
            jsonResponse(['success' => true, 'data' => $ig->getConfigStatus()]);
            break;

        case 'sync':
            if (!$ig->isConfigured()) {
                jsonResponse(['success' => false, 'message' => 'Instagram Graph API no configurado. Configura IG_BUSINESS_ID y FB_PAGE_TOKEN en root_config.php'], 400);
            }
            $max = min(100, max(1, (int)($_GET['max'] ?? 20)));
            $result = $ig->syncComments($max);
            jsonResponse(['success' => true, 'data' => [
                'sync_result' => $result,
                'message' => "Sincronizados {$result['media']} media, {$result['comments']} comentarios",
            ]]);
            break;

        case 'media':
            $stmt = $db->query("SELECT * FROM ig_media ORDER BY timestamp DESC LIMIT 50");
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'comments':
            $mediaId = $_GET['ig_media_id'] ?? '';
            if (!empty($mediaId)) {
                $stmt = $db->prepare("SELECT c.*, col.nombres, col.apellidos FROM ig_comments c LEFT JOIN colaboradores col ON c.colaborador_id = col.id WHERE c.ig_media_id = ? ORDER BY c.timestamp DESC");
                $stmt->execute([$mediaId]);
            } else {
                $stmt = $db->query("SELECT c.*, col.nombres, col.apellidos FROM ig_comments c LEFT JOIN colaboradores col ON c.colaborador_id = col.id ORDER BY c.timestamp DESC LIMIT 100");
            }
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'commenters':
            $stmt = $db->query("
                SELECT username, COUNT(*) as total_comments,
                       COUNT(DISTINCT ig_media_id) as posts_commentados,
                       MAX(timestamp) as ultimo_comentario,
                       (SELECT nombres FROM colaboradores WHERE instagram_username = c.username LIMIT 1) as nombres,
                       (SELECT apellidos FROM colaboradores WHERE instagram_username = c.username LIMIT 1) as apellidos
                FROM ig_comments c
                GROUP BY username
                ORDER BY total_comments DESC
                LIMIT 100
            ");
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'sync_legacy':
            $result = $ig->syncLegacyMenciones();
            jsonResponse(['success' => true, 'data' => $result]);
            break;

        case 'stats':
            $totalMedia = (int)$db->query("SELECT COUNT(*) FROM ig_media")->fetchColumn();
            $totalComments = (int)$db->query("SELECT COUNT(*) FROM ig_comments")->fetchColumn();
            $totalCommenters = (int)$db->query("SELECT COUNT(DISTINCT username) FROM ig_comments")->fetchColumn();
            $totalMenciones = (int)$db->query("SELECT COUNT(*) FROM ig_menciones")->fetchColumn();
            $matchedMenciones = (int)$db->query("SELECT COUNT(*) FROM ig_menciones WHERE colaborador_id IS NOT NULL")->fetchColumn();

            jsonResponse(['success' => true, 'data' => [
                'configured' => $ig->isConfigured(),
                'total_media' => $totalMedia,
                'total_comments' => $totalComments,
                'total_commenters' => $totalCommenters,
                'total_menciones' => $totalMenciones,
                'matched_menciones' => $matchedMenciones,
                'config' => $ig->getConfigStatus(),
            ]]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("InstagramGraph API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
}
