<?php

class SocialCRM
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function autoMatchCommenters(): array
    {
        $stats = ['matched' => 0, 'pending' => 0, 'errors' => []];

        $stmt = $this->db->query("
            SELECT c.* FROM fb_commenters c
            WHERE c.colaborador_id IS NULL AND c.estado = 'nuevo'
            ORDER BY (c.total_reactions + c.total_comments) DESC
            LIMIT 100
        ");
        $commenters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($commenters as $commenter) {
            try {
                $result = $this->matchByName($commenter['user_name']);
                if ($result['confidence'] >= 80) {
                    $this->assignCollaborator('fb_commenters', $commenter['id'], $result['colaborador_id']);
                    $this->createActivity($result['colaborador_id'], $commenter);
                    $stats['matched']++;
                } elseif ($result['confidence'] >= 50) {
                    $this->db->prepare("UPDATE fb_commenters SET estado = 'pendiente_revision' WHERE id = ?")
                        ->execute([$commenter['id']]);
                    $stats['pending']++;
                }
            } catch (\Exception $e) {
                $stats['errors'][] = $commenter['user_name'] . ': ' . $e->getMessage();
            }
        }

        return $stats;
    }

    public function matchByName(string $name): array
    {
        $name = trim($name);
        if (empty($name)) return ['colaborador_id' => null, 'confidence' => 0];

        $parts = explode(' ', $name);
        $firstName = $parts[0] ?? '';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        $candidates = [];

        if (!empty($firstName) && !empty($lastName)) {
            $stmt = $this->db->prepare("
                SELECT id, nombres, apellidos, email, telefono
                FROM colaboradores
                WHERE (nombres LIKE ? OR nombres LIKE ?)
                  AND (apellidos LIKE ? OR apellidos LIKE ?)
                LIMIT 5
            ");
            $likeFirst = "%$firstName%";
            $likeLast = "%$lastName%";

            $possibleFirst = explode(' ', $firstName)[0];
            $possibleLast = explode(' ', $lastName)[0];
            $stmt->execute([$likeFirst, "%$possibleFirst%", $likeLast, "%$possibleLast%"]);
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if (empty($candidates) && !empty($firstName)) {
            $stmt = $this->db->prepare("
                SELECT id, nombres, apellidos, email, telefono
                FROM colaboradores
                WHERE nombres LIKE ?
                   OR apellidos LIKE ?
                LIMIT 5
            ");
            $likeName = "%$firstName%";
            $stmt->execute([$likeName, $likeName]);
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if (empty($candidates)) {
            return ['colaborador_id' => null, 'confidence' => 0, 'candidates' => []];
        }

        $best = null;
        $bestConfidence = 0;

        foreach ($candidates as $c) {
            $fullName = $c['nombres'] . ' ' . $c['apellidos'];
            $confidence = $this->calculateSimilarity($name, $fullName);

            if ($confidence > $bestConfidence) {
                $bestConfidence = $confidence;
                $best = $c;
            }
        }

        return [
            'colaborador_id' => $best['id'],
            'confidence' => (int)$bestConfidence,
            'colaborador' => $best,
            'candidates' => $candidates,
        ];
    }

    public function calculateSimilarity(string $a, string $b): float
    {
        $a = mb_strtolower(trim($a));
        $b = mb_strtolower(trim($b));

        if ($a === $b) return 100;

        $partsA = explode(' ', $a);
        $partsB = explode(' ', $b);

        $matches = 0;
        foreach ($partsA as $pa) {
            foreach ($partsB as $pb) {
                if ($pa === $pb) $matches++;
            }
        }

        $total = max(count($partsA), count($partsB));
        $wordMatch = $total > 0 ? ($matches / $total) * 100 : 0;

        $lev = levenshtein($a, $b);
        $maxLen = max(strlen($a), strlen($b));
        $levSimilarity = $maxLen > 0 ? (1 - $lev / $maxLen) * 100 : 0;

        return max($wordMatch, $levSimilarity);
    }

    public function assignCollaborator(string $table, int $id, int $colaboradorId): void
    {
        $allowed = ['fb_commenters', 'social_leads'];
        if (!in_array($table, $allowed)) return;

        $this->db->prepare("UPDATE $table SET colaborador_id = ?, estado = 'convertido' WHERE id = ?")
            ->execute([$colaboradorId, $id]);

        $this->db->prepare("UPDATE colaboradores SET facebook_id = (
            SELECT fb_user_id FROM $table WHERE id = ?
        ) WHERE id = ?")->execute([$id, $colaboradorId]);
    }

    public function getCommenterSuggestions(int $limit = 50): array
    {
        $stmt = $this->db->query("
            SELECT c.*, 
                   (c.total_reactions + c.total_comments) as total_interacciones
            FROM fb_commenters c
            WHERE c.colaborador_id IS NULL
            ORDER BY total_interacciones DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMatchedCommenters(int $limit = 50): array
    {
        $stmt = $this->db->query("
            SELECT c.*, col.nombres, col.apellidos, col.documento, col.perfil,
                   (c.total_reactions + c.total_comments) as total_interacciones
            FROM fb_commenters c
            JOIN colaboradores col ON c.colaborador_id = col.id
            ORDER BY total_interacciones DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLeads(string $estado = 'nuevo', int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*, col.nombres, col.apellidos, col.documento
            FROM social_leads l
            LEFT JOIN colaboradores col ON l.colaborador_id = col.id
            WHERE l.estado = ?
            ORDER BY l.total_interacciones DESC
            LIMIT $limit
        ");
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTimeline(int $colaboradorId, int $limit = 20): array
    {
        $items = [];

        $stmt = $this->db->prepare("
            SELECT r.fb_post_id, r.user_name, r.tipo_reaccion, r.fecha, p.texto as post_texto, p.url as post_url
            FROM fb_reactions r
            JOIN fb_posts p ON r.fb_post_id = p.fb_post_id
            WHERE r.colaborador_id = ?
            ORDER BY r.fecha DESC
            LIMIT $limit
        ");
        $stmt->execute([$colaboradorId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $items[] = [
                'tipo' => 'facebook_reaction',
                'descripcion' => "Reaccionó con {$r['tipo_reaccion']} en Facebook",
                'fecha' => $r['fecha'],
                'url' => $r['post_url'],
                'metadata' => ['post_texto' => $r['post_texto']],
            ];
        }

        $stmt = $this->db->prepare("
            SELECT c.texto, c.fecha, c.user_name, p.url as post_url, p.texto as post_texto
            FROM fb_comments c
            JOIN fb_posts p ON c.fb_post_id = p.fb_post_id
            WHERE c.colaborador_id = ?
            ORDER BY c.fecha DESC
            LIMIT $limit
        ");
        $stmt->execute([$colaboradorId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $items[] = [
                'tipo' => 'facebook_comment',
                'descripcion' => "Comentó en Facebook: " . mb_substr($c['texto'], 0, 100),
                'fecha' => $c['fecha'],
                'url' => $c['post_url'],
                'metadata' => ['texto' => $c['texto'], 'post_texto' => $c['post_texto']],
            ];
        }

        $stmt = $this->db->prepare("
            SELECT m.* FROM ig_menciones m
            WHERE m.colaborador_id = ?
            ORDER BY m.fecha DESC
            LIMIT $limit
        ");
        $stmt->execute([$colaboradorId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $m) {
            $items[] = [
                'tipo' => 'instagram_mention',
                'descripcion' => "Mencionado en Instagram ({$m['categoria']})",
                'fecha' => $m['fecha'],
                'url' => $m['post_url'],
                'metadata' => ['texto_contexto' => $m['texto_contexto']],
            ];
        }

        usort($items, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));

        return array_slice($items, 0, $limit);
    }

    public function getStats(): array
    {
        $totalPosts = (int)$this->db->query("SELECT COUNT(*) FROM fb_posts")->fetchColumn();
        $totalReactions = (int)$this->db->query("SELECT COUNT(*) FROM fb_reactions")->fetchColumn();
        $totalComments = (int)$this->db->query("SELECT COUNT(*) FROM fb_comments")->fetchColumn();
        $totalCommenters = (int)$this->db->query("SELECT COUNT(*) FROM fb_commenters")->fetchColumn();
        $matched = (int)$this->db->query("SELECT COUNT(*) FROM fb_commenters WHERE colaborador_id IS NOT NULL")->fetchColumn();
        $pending = (int)$this->db->query("SELECT COUNT(*) FROM fb_commenters WHERE estado = 'pendiente_revision'")->fetchColumn();
        $leadsNuevos = (int)$this->db->query("SELECT COUNT(*) FROM social_leads WHERE estado = 'nuevo'")->fetchColumn();

        $matchRate = $totalCommenters > 0 ? round(($matched / $totalCommenters) * 100) : 0;

        return [
            'total_posts' => $totalPosts,
            'total_reactions' => $totalReactions,
            'total_comments' => $totalComments,
            'total_commenters' => $totalCommenters,
            'matched' => $matched,
            'pending_review' => $pending,
            'leads_nuevos' => $leadsNuevos,
            'match_rate' => $matchRate,
        ];
    }

    private function createActivity(int $colaboradorId, array $commenter): void
    {
        if (!class_exists('ActivityLogger')) {
            require_once __DIR__ . '/ActivityLogger.php';
        }
        $log = new ActivityLogger($this->db);
        $log->log(
            $colaboradorId,
            'social_fb_match',
            "Identificado en Facebook como {$commenter['user_name']} ({$commenter['total_reactions']} reacciones, {$commenter['total_comments']} comentarios)",
            json_encode(['fb_user_id' => $commenter['fb_user_id'], 'source' => 'auto_match']),
            'fb_commenters',
            $commenter['id']
        );
    }
}
