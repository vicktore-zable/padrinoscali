<?php

class InstagramGraphApi
{
    private string $businessId;
    private string $token;
    private string $apiVersion;
    private ?PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->businessId = defined('IG_BUSINESS_ID') ? IG_BUSINESS_ID : '';
        $this->token = defined('FB_PAGE_TOKEN') ? FB_PAGE_TOKEN : '';
        $this->apiVersion = defined('FB_API_VERSION') ? FB_API_VERSION : 'v19.0';
        $this->db = $db;
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->businessId);
    }

    public function getConfigStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'business_id' => $this->businessId ?: '(vacio)',
            'has_token' => !empty($this->token),
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
            error_log("InstagramGraphApi cURL error: $error");
            return null;
        }

        $data = json_decode($response, true);
        if ($httpCode !== 200) {
            $msg = $data['error']['message'] ?? "HTTP $httpCode";
            error_log("InstagramGraphApi error: $msg");
            if ($httpCode === 401 || $httpCode === 403) {
                throw new RuntimeException("Token inválido o sin permisos de Instagram: $msg");
            }
            return null;
        }

        return $data;
    }

    public function fetchMedia(int $limit = 50): array
    {
        if (!$this->isConfigured()) return [];

        $params = [
            'fields' => 'id,caption,media_type,media_url,permalink,timestamp,like_count,comments_count',
            'limit' => min(100, $limit),
        ];

        $result = $this->get("{$this->businessId}/media", $params);
        if (!$result || !isset($result['data'])) return [];

        $media = [];
        foreach ($result['data'] as $item) {
            $media[] = [
                'ig_media_id' => $item['id'],
                'caption' => $item['caption'] ?? '',
                'media_type' => $item['media_type'] ?? 'IMAGE',
                'media_url' => $item['media_url'] ?? '',
                'permalink' => $item['permalink'] ?? '',
                'timestamp' => $item['timestamp'] ?? null,
                'like_count' => (int)($item['like_count'] ?? 0),
                'comments_count' => (int)($item['comments_count'] ?? 0),
            ];
        }

        return $media;
    }

    public function fetchComments(string $igMediaId, int $limit = 100): array
    {
        $params = [
            'fields' => 'text,username,timestamp',
            'limit' => min(100, $limit),
        ];

        $result = $this->get("{$igMediaId}/comments", $params);
        if (!$result || !isset($result['data'])) return [];

        $comments = [];
        foreach ($result['data'] as $item) {
            $comments[] = [
                'ig_comment_id' => $item['id'],
                'ig_media_id' => $igMediaId,
                'username' => $item['username'] ?? 'unknown',
                'texto' => $item['text'] ?? '',
                'timestamp' => $item['timestamp'] ?? null,
            ];
        }

        return $comments;
    }

    public function syncComments(int $maxMedia = 50): array
    {
        if (!$this->db) return ['error' => 'DB not connected'];
        if (!$this->isConfigured()) return ['error' => 'Instagram Graph API no configurado'];

        $stats = ['media' => 0, 'comments' => 0, 'errors' => []];

        try {
            $media = $this->fetchMedia($maxMedia);

            foreach ($media as $item) {
                try {
                    $this->upsertMedia($item);
                    $stats['media']++;

                    $comments = $this->fetchComments($item['ig_media_id']);
                    foreach ($comments as $c) {
                        $this->upsertComment($c);
                        $stats['comments']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors'][] = "Media {$item['ig_media_id']}: " . $e->getMessage();
                }
            }
        } catch (\Exception $e) {
            $stats['errors'][] = $e->getMessage();
        }

        return $stats;
    }

    private function upsertMedia(array $item): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO ig_media (ig_media_id, caption, media_type, media_url, permalink, timestamp, like_count, comments_count)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                caption = VALUES(caption),
                like_count = VALUES(like_count),
                comments_count = VALUES(comments_count),
                updated_at = NOW()
        ");
        $stmt->execute([
            $item['ig_media_id'],
            $item['caption'],
            $item['media_type'],
            $item['media_url'],
            $item['permalink'],
            $item['timestamp'],
            $item['like_count'],
            $item['comments_count'],
        ]);
    }

    private function upsertComment(array $c): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO ig_comments (ig_comment_id, ig_media_id, username, texto, timestamp)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                texto = VALUES(texto),
                timestamp = VALUES(timestamp)
        ");
        $stmt->execute([
            $c['ig_comment_id'],
            $c['ig_media_id'],
            $c['username'],
            $c['texto'],
            $c['timestamp'],
        ]);
    }

    // Legacy: extract mentions from maestro_instagram.json into DB
    public function syncLegacyMenciones(): array
    {
        if (!$this->db) return ['error' => 'DB not connected'];

        $jsonPath = __DIR__ . '/../storage/maestro_instagram.json';
        if (!file_exists($jsonPath)) return ['error' => 'JSON not found'];

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!$data || !isset($data['timeline'])) return ['error' => 'Invalid JSON'];

        $existing = $this->db->query("SELECT DISTINCT post_url FROM ig_menciones")->fetchAll(PDO::FETCH_COLUMN);
        $existingUrls = array_flip($existing);
        $inserted = 0;

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO ig_menciones (post_url, fecha, username, texto_contexto, categoria)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($data['timeline'] as $posts) {
            foreach ($posts as $p) {
                $url = $p['url'] ?? '';
                if (isset($existingUrls[$url]) || empty($p['menciones'])) continue;

                foreach ($p['menciones'] as $username) {
                    $stmt->execute([
                        $url, $p['fecha'] ?? null, $username,
                        mb_substr($p['texto'] ?? '', 0, 500),
                        $p['categoria'] ?? 'general',
                    ]);
                    if ($stmt->rowCount() > 0) $inserted++;
                }
                $existingUrls[$url] = true;
            }
        }

        return ['inserted' => $inserted];
    }
}
