<?php

class FacebookApi
{
    private string $pageId;
    private string $token;
    private string $apiVersion;
    private ?PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->pageId = defined('FB_PAGE_ID') ? FB_PAGE_ID : '';
        $this->token = defined('FB_PAGE_TOKEN') ? FB_PAGE_TOKEN : '';
        $this->apiVersion = defined('FB_API_VERSION') ? FB_API_VERSION : 'v19.0';
        $this->db = $db;
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->pageId);
    }

    public function getConfigStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'page_id' => $this->pageId ?: '(vacio)',
            'has_token' => !empty($this->token),
            'token_preview' => !empty($this->token) ? substr($this->token, 0, 10) . '...' : '(vacio)',
            'api_version' => $this->apiVersion,
        ];
    }

    private function get(string $endpoint, array $params = []): ?array
    {
        $params['access_token'] = $this->token;
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$endpoint}?" . http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("FacebookApi cURL error: $error");
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $msg = $data['error']['message'] ?? "HTTP $httpCode";
            error_log("FacebookApi error: $msg");
            if ($httpCode === 401 || $httpCode === 403) {
                throw new RuntimeException("Token de Facebook expirado o inválido: $msg");
            }
            return null;
        }

        return $data;
    }

    public function fetchPosts(int $limit = 100, string $since = ''): array
    {
        if (!$this->isConfigured()) return [];

        $params = [
            'fields' => 'message,created_time,permalink_url,type,full_picture,likes.summary(true),comments.summary(true),shares',
            'limit' => min(100, $limit),
        ];
        if ($since) $params['since'] = $since;

        $result = $this->get("{$this->pageId}/posts", $params);
        if (!$result || !isset($result['data'])) return [];

        $posts = [];
        foreach ($result['data'] as $item) {
            $posts[] = [
                'fb_post_id' => $item['id'],
                'page_id' => $this->pageId,
                'texto' => $item['message'] ?? '',
                'fecha' => $item['created_time'] ?? null,
                'url' => $item['permalink_url'] ?? '',
                'tipo' => $item['type'] ?? 'status',
                'likes_count' => $item['likes']['summary']['total_count'] ?? 0,
                'comments_count' => $item['comments']['summary']['total_count'] ?? 0,
                'shares_count' => $item['shares']['count'] ?? 0,
                'metadata_json' => json_encode([
                    'full_picture' => $item['full_picture'] ?? null,
                ]),
            ];
        }

        return $posts;
    }

    public function fetchReactions(string $fbPostId, int $limit = 500): array
    {
        $params = [
            'fields' => 'id,name,type',
            'limit' => min(500, $limit),
        ];

        $result = $this->get("{$fbPostId}/reactions", $params);
        if (!$result || !isset($result['data'])) return [];

        $reactions = [];
        foreach ($result['data'] as $item) {
            $reactions[] = [
                'fb_post_id' => $fbPostId,
                'fb_user_id' => $item['id'],
                'user_name' => $item['name'],
                'tipo_reaccion' => $item['type'],
            ];
        }

        return $reactions;
    }

    public function fetchComments(string $fbPostId, int $limit = 500): array
    {
        $params = [
            'fields' => 'from{id,name},message,created_time',
            'limit' => min(500, $limit),
        ];

        $result = $this->get("{$fbPostId}/comments", $params);
        if (!$result || !isset($result['data'])) return [];

        $comments = [];
        foreach ($result['data'] as $item) {
            $from = $item['from'] ?? [];
            $comments[] = [
                'fb_post_id' => $fbPostId,
                'fb_comment_id' => $item['id'],
                'fb_user_id' => $from['id'] ?? '',
                'user_name' => $from['name'] ?? 'Unknown',
                'texto' => $item['message'] ?? '',
                'fecha' => $item['created_time'] ?? null,
            ];
        }

        return $comments;
    }

    public function syncAll(int $maxPosts = 50): array
    {
        if (!$this->db) return ['error' => 'DB not connected'];
        if (!$this->isConfigured()) return ['error' => 'Facebook no configurado'];

        $stats = ['posts' => 0, 'reactions' => 0, 'comments' => 0, 'errors' => []];

        try {
            $posts = $this->fetchPosts($maxPosts);

            foreach ($posts as $post) {
                try {
                    $this->upsertPost($post);
                    $stats['posts']++;

                    $reactions = $this->fetchReactions($post['fb_post_id']);
                    foreach ($reactions as $r) {
                        $this->upsertReaction($r);
                        $stats['reactions']++;
                    }

                    $comments = $this->fetchComments($post['fb_post_id']);
                    foreach ($comments as $c) {
                        $this->upsertComment($c);
                        $stats['comments']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors'][] = "Post {$post['fb_post_id']}: " . $e->getMessage();
                }
            }

            $this->updateCommenters();
            $this->runAutoMatch();

        } catch (\Exception $e) {
            $stats['errors'][] = $e->getMessage();
        }

        return $stats;
    }

    private function upsertPost(array $post): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO fb_posts (fb_post_id, page_id, texto, fecha, url, tipo, likes_count, comments_count, shares_count, metadata_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                texto = VALUES(texto),
                likes_count = VALUES(likes_count),
                comments_count = VALUES(comments_count),
                shares_count = VALUES(shares_count),
                metadata_json = VALUES(metadata_json),
                updated_at = NOW()
        ");
        $stmt->execute([
            $post['fb_post_id'],
            $post['page_id'],
            $post['texto'],
            $post['fecha'],
            $post['url'],
            $post['tipo'],
            $post['likes_count'],
            $post['comments_count'],
            $post['shares_count'],
            $post['metadata_json'],
        ]);
    }

    private function upsertReaction(array $r): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO fb_reactions (fb_post_id, fb_user_id, user_name, tipo_reaccion)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                user_name = VALUES(user_name),
                tipo_reaccion = VALUES(tipo_reaccion),
                fecha = NOW()
        ");
        $stmt->execute([$r['fb_post_id'], $r['fb_user_id'], $r['user_name'], $r['tipo_reaccion']]);
    }

    private function upsertComment(array $c): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO fb_comments (fb_post_id, fb_comment_id, fb_user_id, user_name, texto, fecha)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                texto = VALUES(texto),
                user_name = VALUES(user_name),
                fecha = VALUES(fecha)
        ");
        $stmt->execute([$c['fb_post_id'], $c['fb_comment_id'], $c['fb_user_id'], $c['user_name'], $c['texto'], $c['fecha']]);
    }

    private function updateCommenters(): void
    {
        $this->db->exec("
            INSERT INTO fb_commenters (fb_user_id, user_name, total_reactions, total_comments)
            SELECT u.fb_user_id, u.user_name,
                   COUNT(DISTINCT r.id) as total_reactions,
                   COUNT(DISTINCT c.id) as total_comments
            FROM (
                SELECT fb_user_id, user_name FROM fb_reactions
                UNION
                SELECT fb_user_id, user_name FROM fb_comments
            ) u
            LEFT JOIN fb_reactions r ON r.fb_user_id = u.fb_user_id
            LEFT JOIN fb_comments c ON c.fb_user_id = u.fb_user_id
            GROUP BY u.fb_user_id, u.user_name
            ON DUPLICATE KEY UPDATE
                user_name = VALUES(user_name),
                total_reactions = VALUES(total_reactions),
                total_comments = VALUES(total_comments),
                last_seen = NOW()
        ");
    }

    private function runAutoMatch(): void
    {
        require_once __DIR__ . '/SocialCRM.php';
        $crm = new SocialCRM($this->db);
        $crm->autoMatchCommenters();
    }
}
