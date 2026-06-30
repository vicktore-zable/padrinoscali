<?php

class MessengerBot
{
    private string $pageId;
    private string $token;
    private string $apiVersion;
    private string $baseUrl;
    private ?PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->pageId = defined('FB_PAGE_ID') ? FB_PAGE_ID : '';
        $this->token = defined('FB_PAGE_TOKEN') ? FB_PAGE_TOKEN : '';
        $this->apiVersion = defined('FB_API_VERSION') ? FB_API_VERSION : 'v19.0';
        $this->baseUrl = defined('APP_URL') ? APP_URL : '';
        $this->db = $db;
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->pageId);
    }

    public function getConfigStatus(): array
    {
        $canMessage = $this->isConfigured();
        $triggers = $this->db ? (int)$this->db->query("SELECT COUNT(*) FROM mandami_triggers WHERE enabled = 1")->fetchColumn() : 0;
        $pending = $this->db ? (int)$this->db->query("SELECT COUNT(*) FROM mandami_capture_flow WHERE dm_sent = 0 AND formulario_completado = 0")->fetchColumn() : 0;

        return [
            'configured' => $canMessage,
            'page_id' => $this->pageId ?: '(vacio)',
            'has_token' => !empty($this->token),
            'triggers_enabled' => $triggers,
            'pending_captures' => $pending,
        ];
    }

    /**
     * Scan recent FB comments against trigger keywords
     */
    public function scanComments(int $sinceHours = 24): array
    {
        if (!$this->db) return ['error' => 'DB not connected'];
        if (!$this->isConfigured()) return ['error' => 'Facebook no configurado'];

        $stats = ['scanned' => 0, 'matched' => 0, 'dm_sent' => 0, 'errors' => []];

        try {
            $triggers = $this->db->query("SELECT * FROM mandami_triggers WHERE enabled = 1")->fetchAll(PDO::FETCH_ASSOC);
            if (empty($triggers)) return $stats;

            $since = date('Y-m-d H:i:s', strtotime("-{$sinceHours} hours"));
            $stmt = $this->db->prepare("
                SELECT c.*, p.texto as post_texto
                FROM fb_comments c
                JOIN fb_posts p ON p.fb_post_id = c.fb_post_id
                WHERE c.fecha >= ? AND c.fb_user_id != ''
                ORDER BY c.fecha DESC
                LIMIT 200
            ");
            $stmt->execute([$since]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($comments as $comment) {
                $stats['scanned']++;
                $textoLower = mb_strtolower($comment['texto']);

                foreach ($triggers as $trigger) {
                    $keyword = mb_strtolower(trim($trigger['keyword']));
                    if (str_contains($textoLower, $keyword)) {
                        $alreadyReplied = $this->db->prepare("SELECT COUNT(*) FROM mandami_capture_flow WHERE fb_comment_id = ?");
                        $alreadyReplied->execute([$comment['fb_comment_id']]);
                        if ($alreadyReplied->fetchColumn() > 0) continue;

                        $result = $this->processTrigger($trigger, $comment);
                        if ($result) $stats['dm_sent']++;
                        $stats['matched']++;
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $stats['errors'][] = $e->getMessage();
        }

        return $stats;
    }

    /**
     * Process a trigger match: send DM + register in capture_flow
     */
    private function processTrigger(array $trigger, array $comment): bool
    {
        $landingUrl = '';
        if (!empty($trigger['landing_url'])) {
            $landingUrl = $this->baseUrl . $trigger['landing_url']
                . '&fb_user_id=' . urlencode($comment['fb_user_id'])
                . '&user_name=' . urlencode($comment['user_name'])
                . '&keyword=' . urlencode($trigger['keyword']);
        }

        $message = str_replace(
            ['{{nombre}}', '{{landing_url}}', '{{keyword}}'],
            [$comment['user_name'], $landingUrl, $trigger['keyword']],
            $trigger['auto_reply_template']
        );

        $dmResult = $this->sendDM($comment['fb_user_id'], $message);
        $dmMessageId = $dmResult['message_id'] ?? null;

        $insert = $this->db->prepare("
            INSERT INTO mandami_capture_flow (fb_user_id, user_name, trigger_keyword, fb_post_id, fb_comment_id, dm_sent, dm_sent_at, dm_message_id)
            VALUES (?, ?, ?, ?, ?, 1, NOW(), ?)
        ");
        $insert->execute([
            $comment['fb_user_id'],
            $comment['user_name'],
            $trigger['keyword'],
            $comment['fb_post_id'],
            $comment['fb_comment_id'],
            $dmMessageId,
        ]);

        return (bool)$dmMessageId;
    }

    /**
     * Send a DM via Facebook Messenger Send API
     */
    public function sendDM(string $fbUserId, string $message): array
    {
        if (!$this->isConfigured()) return ['error' => 'No configurado'];

        $url = "https://graph.facebook.com/{$this->apiVersion}/me/messages?access_token={$this->token}";

        $payload = [
            'recipient' => ['id' => $fbUserId],
            'message' => ['text' => $message],
            'messaging_type' => 'MESSAGE_TAG',
            'tag' => 'CONFIRMED_EVENT_UPDATE',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['message_id'])) {
            return ['success' => true, 'message_id' => $data['message_id']];
        }

        $errorMsg = $data['error']['message'] ?? "HTTP $httpCode";
        error_log("MessengerBot DM error: $errorMsg");
        return ['error' => $errorMsg];
    }

    /**
     * Register a form submission from the capture landing page
     */
    public function registerCapture(array $data): array
    {
        if (!$this->db) return ['success' => false, 'message' => 'DB not connected'];

        $fbUserId = $data['fb_user_id'] ?? '';
        $nombres = $data['nombres'] ?? '';
        $comuna = $data['comuna'] ?? '';
        $celular = $data['celular'] ?? '';
        $keyword = $data['keyword'] ?? '';

        if (empty($fbUserId) || empty($nombres)) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        $flow = $this->db->prepare("SELECT id FROM mandami_capture_flow WHERE fb_user_id = ? ORDER BY created_at DESC LIMIT 1");
        $flow->execute([$fbUserId]);
        $existing = $flow->fetch(PDO::FETCH_ASSOC);

        $colaboradorId = $this->findOrCreateCollaborator($nombres, $comuna, $celular);

        if ($existing) {
            $update = $this->db->prepare("
                UPDATE mandami_capture_flow SET
                    link_clicked = 1,
                    link_clicked_at = NOW(),
                    formulario_completado = 1,
                    formulario_completado_at = NOW(),
                    nombres = ?,
                    comuna = ?,
                    celular = ?,
                    colaborador_id = ?
                WHERE id = ?
            ");
            $update->execute([$nombres, $comuna, $celular, $colaboradorId, $existing['id']]);
        } else {
            $insert = $this->db->prepare("
                INSERT INTO mandami_capture_flow (fb_user_id, user_name, trigger_keyword, link_clicked, link_clicked_at, formulario_completado, formulario_completado_at, nombres, comuna, celular, colaborador_id)
                VALUES (?, ?, ?, 1, NOW(), 1, NOW(), ?, ?, ?, ?)
            ");
            $insert->execute([$fbUserId, $nombres, $keyword, $nombres, $comuna, $celular, $colaboradorId]);
        }

        if ($colaboradorId) {
            $this->updateColaboradorSocial($colaboradorId, $fbUserId);
        }

        return ['success' => true, 'colaborador_id' => $colaboradorId];
    }

    private function findOrCreateCollaborator(string $nombres, string $comuna, string $celular): ?int
    {
        if (empty($celular)) {
            $check = $this->db->prepare("SELECT id FROM colaboradores WHERE nombres LIKE ? LIMIT 1");
            $check->execute(["%$nombres%"]);
            $found = $check->fetch(PDO::FETCH_ASSOC);
            return $found ? (int)$found['id'] : null;
        }

        $check = $this->db->prepare("SELECT id FROM colaboradores WHERE celular = ? OR documento = ? LIMIT 1");
        $check->execute([$celular, $celular]);
        $found = $check->fetch(PDO::FETCH_ASSOC);

        if ($found) return (int)$found['id'];

        $territorioId = $this->resolveTerritorioFromComuna($comuna);

        $insert = $this->db->prepare("
            INSERT INTO colaboradores (nombres, celular, municipio, territorio_id, estado, perfil, fecha_creacion, nivel_participacion)
            VALUES (?, ?, 'CALI', ?, 'Potencial', 'Simpatizante', CURDATE(), 'Bajo')
        ");
        $insert->execute([$nombres, $celular, $territorioId]);
        return (int)$this->db->lastInsertId();
    }

    private function resolveTerritorioFromComuna(?string $comuna): ?int
    {
        if (empty($comuna)) return null;

        $num = preg_replace('/[^0-9]/', '', $comuna);
        if (empty($num)) return null;

        $stmt = $this->db->prepare("SELECT id FROM territorios WHERE (nombre_completo LIKE ? OR nombre LIKE ?) AND tipo = 'Comuna' LIMIT 1");
        $like = "%Comuna $num%";
        $stmt->execute([$like, $like]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        return $t ? (int)$t['id'] : null;
    }

    private function updateColaboradorSocial(int $colaboradorId, string $fbUserId): void
    {
        $stmt = $this->db->prepare("SELECT facebook_id FROM colaboradores WHERE id = ?");
        $stmt->execute([$colaboradorId]);
        $col = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($col && empty($col['facebook_id'])) {
            $update = $this->db->prepare("UPDATE colaboradores SET facebook_id = ? WHERE id = ?");
            $update->execute([$fbUserId, $colaboradorId]);
        }
    }

    /**
     * Funnel statistics
     */
    public function getFunnelStats(): array
    {
        if (!$this->db) return [];

        $total = (int)$this->db->query("SELECT COUNT(*) FROM mandami_capture_flow")->fetchColumn();
        $dmSent = (int)$this->db->query("SELECT COUNT(*) FROM mandami_capture_flow WHERE dm_sent = 1")->fetchColumn();
        $linkClicked = (int)$this->db->query("SELECT COUNT(*) FROM mandami_capture_flow WHERE link_clicked = 1")->fetchColumn();
        $formCompleted = (int)$this->db->query("SELECT COUNT(*) FROM mandami_capture_flow WHERE formulario_completado = 1")->fetchColumn();
        $conColaborador = (int)$this->db->query("SELECT COUNT(*) FROM mandami_capture_flow WHERE colaborador_id IS NOT NULL")->fetchColumn();

        $byKeyword = $this->db->query("
            SELECT trigger_keyword, COUNT(*) as total,
                   SUM(CASE WHEN formulario_completado = 1 THEN 1 ELSE 0 END) as completados
            FROM mandami_capture_flow
            GROUP BY trigger_keyword
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $byComuna = $this->db->query("
            SELECT comuna, COUNT(*) as total
            FROM mandami_capture_flow
            WHERE comuna IS NOT NULL AND comuna != ''
            GROUP BY comuna
            ORDER BY total DESC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);

        $recent = $this->db->query("
            SELECT c.*, col.nombres as col_nombres, col.apellidos as col_apellidos
            FROM mandami_capture_flow c
            LEFT JOIN colaboradores col ON col.id = c.colaborador_id
            ORDER BY c.created_at DESC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total' => $total,
            'dm_sent' => $dmSent,
            'link_clicked' => $linkClicked,
            'formulario_completado' => $formCompleted,
            'con_colaborador' => $conColaborador,
            'tasa_dm' => $total > 0 ? round($dmSent / $total * 100, 1) : 0,
            'tasa_click' => $dmSent > 0 ? round($linkClicked / $dmSent * 100, 1) : 0,
            'tasa_conversion' => $linkClicked > 0 ? round($formCompleted / $linkClicked * 100, 1) : 0,
            'por_keyword' => $byKeyword,
            'por_comuna' => $byComuna,
            'recientes' => $recent,
        ];
    }

    public function getTriggers(): array
    {
        if (!$this->db) return [];
        return $this->db->query("SELECT * FROM mandami_triggers ORDER BY keyword")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveTrigger(array $data): bool
    {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("
            INSERT INTO mandami_triggers (keyword, label, auto_reply_template, landing_url, enabled)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                label = VALUES(label),
                auto_reply_template = VALUES(auto_reply_template),
                landing_url = VALUES(landing_url),
                enabled = VALUES(enabled)
        ");
        return $stmt->execute([
            $data['keyword'],
            $data['label'] ?? '',
            $data['auto_reply_template'],
            $data['landing_url'] ?? null,
            $data['enabled'] ?? 1,
        ]);
    }

    public function deleteTrigger(int $id): bool
    {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("DELETE FROM mandami_triggers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function recordLinkClick(string $fbUserId): void
    {
        if (!$this->db) return;
        $stmt = $this->db->prepare("
            UPDATE mandami_capture_flow SET
                link_clicked = 1,
                link_clicked_at = NOW()
            WHERE fb_user_id = ? AND link_clicked = 0
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$fbUserId]);
    }
}
