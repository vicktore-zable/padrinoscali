<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';

try {
    require_once __DIR__ . '/../includes/FacebookApi.php';
    require_once __DIR__ . '/../includes/SocialCRM.php';

    $fb = new FacebookApi($db);
    $crm = new SocialCRM($db);

    switch ($action) {
        case 'status':
            jsonResponse(['success' => true, 'data' => $fb->getConfigStatus()]);
            break;

        case 'sync':
            if (!$fb->isConfigured()) {
                jsonResponse(['success' => false, 'message' => 'Facebook no configurado. Configura FB_PAGE_TOKEN en root_config.php'], 400);
            }
            $maxPosts = min(100, max(1, (int)($_GET['max'] ?? 20)));
            $result = $fb->syncAll($maxPosts);
            $stats = $crm->getStats();
            jsonResponse([
                'success' => true,
                'data' => [
                    'sync_result' => $result,
                    'stats' => $stats,
                    'message' => "Sincronizados {$result['posts']} posts, {$result['reactions']} reacciones, {$result['comments']} comentarios",
                ]
            ]);
            break;

        case 'posts':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;

            $total = (int)$db->query("SELECT COUNT(*) FROM fb_posts")->fetchColumn();
            $stmt = $db->prepare("
                SELECT p.*,
                       (SELECT COUNT(*) FROM fb_reactions r WHERE r.fb_post_id = p.fb_post_id) as reactions_count,
                       (SELECT COUNT(*) FROM fb_comments c WHERE c.fb_post_id = p.fb_post_id) as comments_real_count
                FROM fb_posts p
                ORDER BY p.fecha DESC
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            jsonResponse(['success' => true, 'data' => $posts, 'total' => $total, 'page' => $page, 'pages' => ceil($total / $perPage)]);
            break;

        case 'post_detail':
            $fbPostId = $_GET['fb_post_id'] ?? '';
            if (empty($fbPostId)) jsonResponse(['success' => false, 'message' => 'fb_post_id requerido'], 400);

            $stmt = $db->prepare("SELECT * FROM fb_posts WHERE fb_post_id = ?");
            $stmt->execute([$fbPostId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$post) jsonResponse(['success' => false, 'message' => 'Post no encontrado'], 404);

            $stmt = $db->prepare("SELECT r.*, col.nombres, col.apellidos FROM fb_reactions r LEFT JOIN colaboradores col ON r.colaborador_id = col.id WHERE r.fb_post_id = ? ORDER BY r.fecha DESC");
            $stmt->execute([$fbPostId]);
            $post['reactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT c.*, col.nombres, col.apellidos FROM fb_comments c LEFT JOIN colaboradores col ON c.colaborador_id = col.id WHERE c.fb_post_id = ? ORDER BY c.fecha DESC");
            $stmt->execute([$fbPostId]);
            $post['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            jsonResponse(['success' => true, 'data' => $post]);
            break;

        case 'commenters':
            $filter = $_GET['filter'] ?? 'unmatched';
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));

            if ($filter === 'matched') {
                $data = $crm->getMatchedCommenters($limit);
            } else if ($filter === 'pending') {
                $stmt = $db->prepare("SELECT * FROM fb_commenters WHERE estado = 'pendiente_revision' ORDER BY (total_reactions + total_comments) DESC LIMIT $limit");
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $data = $crm->getCommenterSuggestions($limit);
            }

            jsonResponse(['success' => true, 'data' => $data]);
            break;

        case 'match':
            $commenterId = (int)($_GET['commenter_id'] ?? 0);
            $colaboradorId = (int)($_GET['colaborador_id'] ?? 0);

            if (!$commenterId || !$colaboradorId) {
                jsonResponse(['success' => false, 'message' => 'commenter_id y colaborador_id requeridos'], 400);
            }

            $crm->assignCollaborator('fb_commenters', $commenterId, $colaboradorId);
            jsonResponse(['success' => true, 'message' => 'Asignado correctamente']);
            break;

        case 'suggest':
            $name = $_GET['name'] ?? '';
            if (empty($name)) jsonResponse(['success' => false, 'message' => 'name requerido'], 400);
            $result = $crm->matchByName($name);
            jsonResponse(['success' => true, 'data' => $result]);
            break;

        case 'auto_match':
            $result = $crm->autoMatchCommenters();
            $stats = $crm->getStats();
            jsonResponse(['success' => true, 'data' => ['matched' => $result, 'stats' => $stats]]);
            break;

        case 'stats':
            jsonResponse(['success' => true, 'data' => $crm->getStats()]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("Facebook API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
}
