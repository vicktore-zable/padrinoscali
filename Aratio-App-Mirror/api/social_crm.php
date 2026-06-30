<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';

try {
    require_once __DIR__ . '/../includes/SocialCRM.php';
    $crm = new SocialCRM($db);

    switch ($action) {
        case 'leads':
            $estado = $_GET['estado'] ?? 'nuevo';
            jsonResponse(['success' => true, 'data' => $crm->getLeads($estado)]);
            break;

        case 'timeline':
            $colaboradorId = (int)($_GET['colaborador_id'] ?? 0);
            if (!$colaboradorId) jsonResponse(['success' => false, 'message' => 'colaborador_id requerido'], 400);
            jsonResponse(['success' => true, 'data' => $crm->getTimeline($colaboradorId)]);
            break;

        case 'stats':
            jsonResponse(['success' => true, 'data' => $crm->getStats()]);
            break;

        case 'match_leads':
            $leadId = (int)($_GET['lead_id'] ?? 0);
            $colaboradorId = (int)($_GET['colaborador_id'] ?? 0);
            if (!$leadId || !$colaboradorId) {
                jsonResponse(['success' => false, 'message' => 'lead_id y colaborador_id requeridos'], 400);
            }
            $crm->assignCollaborator('social_leads', $leadId, $colaboradorId);
            jsonResponse(['success' => true, 'message' => 'Lead asignado correctamente']);
            break;

        case 'instagram_menciones':
            $stmt = $db->query("
                SELECT m.*, col.nombres, col.apellidos
                FROM ig_menciones m
                LEFT JOIN colaboradores col ON m.colaborador_id = col.id
                ORDER BY m.fecha DESC
                LIMIT 100
            ");
            $menciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(['success' => true, 'data' => $menciones]);
            break;

        case 'instagram_stats':
            $stmt = $db->query("
                SELECT categoria, COUNT(*) as total,
                       COUNT(DISTINCT username) as usuarios_unicos,
                       COUNT(DISTINCT CASE WHEN colaborador_id IS NOT NULL THEN username END) as identificados
                FROM ig_menciones
                GROUP BY categoria
                ORDER BY total DESC
            ");
            $porCategoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->query("SELECT COUNT(*) FROM ig_menciones");
            $total = (int)$stmt->fetchColumn();
            $stmt = $db->query("SELECT COUNT(*) FROM ig_menciones WHERE colaborador_id IS NOT NULL");
            $identificados = (int)$stmt->fetchColumn();

            jsonResponse(['success' => true, 'data' => [
                'total_menciones' => $total,
                'identificados' => $identificados,
                'tasa_match' => $total > 0 ? round(($identificados / $total) * 100) : 0,
                'por_categoria' => $porCategoria,
            ]]);
            break;

        case 'cargar_ig_menciones':
            $jsonPath = __DIR__ . '/../storage/maestro_instagram.json';
            if (!file_exists($jsonPath)) {
                jsonResponse(['success' => false, 'message' => 'Archivo JSON no encontrado'], 404);
            }

            $data = json_decode(file_get_contents($jsonPath), true);
            if (!$data || !isset($data['timeline'])) {
                jsonResponse(['success' => false, 'message' => 'JSON inválido'], 400);
            }

            $insertados = 0;
            $stmt = $db->prepare("
                INSERT IGNORE INTO ig_menciones (post_url, fecha, username, texto_contexto, categoria)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($data['timeline'] as $mes => $posts) {
                foreach ($posts as $post) {
                    if (!empty($post['menciones'])) {
                        foreach ($post['menciones'] as $username) {
                            $stmt->execute([
                                $post['url'] ?? '',
                                $post['fecha'] ?? null,
                                $username,
                                mb_substr($post['texto'] ?? '', 0, 500),
                                $post['categoria'] ?? 'general',
                            ]);
                            if ($stmt->rowCount() > 0) $insertados++;
                        }
                    }
                }
            }

            jsonResponse(['success' => true, 'data' => ['insertados' => $insertados]]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("SocialCRM API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
}
