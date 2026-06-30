<?php

require_once __DIR__ . '/ActivityLogger.php';

class EmailCampaigns
{
    private static ?PDO $db = null;

    private static function db(): PDO
    {
        if (self::$db === null) {
            self::$db = getDB();
        }
        return self::$db;
    }

    public static function sendMail(string $to, string $subject, string $htmlBody, ?string $from = null): bool
    {
        $from = $from ?: (defined('SMTP_FROM') ? SMTP_FROM : 'admin@aratio.mrmtech.net');
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Sistema Aratio';

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: Aratio/' . (defined('ALAS_VERSION') ? ALAS_VERSION : '2.12.0'),
        ];

        $body = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:20px">' .
                '<table align="center" style="max-width:600px;width:100%;background:#fff;border-radius:12px;overflow:hidden">' .
                '<tr><td style="padding:30px;background:linear-gradient(135deg,#2563eb,#7c3aed);text-align:center">' .
                '<h1 style="color:#fff;margin:0;font-size:24px">Padrinos Cali</h1></td></tr>' .
                '<tr><td style="padding:30px">' . $htmlBody . '</td></tr>' .
                '<tr><td style="padding:20px;background:#f9fafb;text-align:center;font-size:12px;color:#9ca3af">' .
                'Sistema Aratio — Padrinos Cali</td></tr></table></body></html>';

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public static function crearCampana(string $nombre, string $asunto, string $cuerpoHtml, array $filtros = [], ?string $programadaPara = null, ?int $usuarioId = null): int
    {
        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO email_campanas (nombre, asunto, cuerpo_html, programada_para, estado, creado_por)
            VALUES (?, ?, ?, ?, 'activa', ?)
        ");
        $stmt->execute([$nombre, $asunto, $cuerpoHtml, $programadaPara, $usuarioId]);
        $campanaId = (int)$db->lastInsertId();

        $total = self::generarCola($campanaId, $filtros);
        $db->prepare("UPDATE email_campanas SET total_destinatarios = ? WHERE id = ?")->execute([$total, $campanaId]);

        return $campanaId;
    }

    public static function generarCola(int $campanaId, array $filtros = []): int
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT asunto, cuerpo_html FROM email_campanas WHERE id = ?");
        $stmt->execute([$campanaId]);
        $campana = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$campana) return 0;

        $where = ["(c.estado IS NULL OR c.estado NOT IN ('inactivo','Inactivo'))"];
        $where[] = "(c.email IS NOT NULL AND c.email != '' AND c.email LIKE '%@%')";
        $params = [];

        if (!empty($filtros['municipio'])) {
            $where[] = "c.municipio = ?";
            $params[] = $filtros['municipio'];
        }
        if (!empty($filtros['perfil'])) {
            $where[] = "c.perfil = ?";
            $params[] = $filtros['perfil'];
        }
        if (!empty($filtros['territorio_id'])) {
            $where[] = "c.territorio_id = ?";
            $params[] = (int)$filtros['territorio_id'];
        }
        if (!empty($filtros['lider_id'])) {
            $where[] = "c.lider_directo = ?";
            $params[] = (int)$filtros['lider_id'];
        }

        $stmt = $db->prepare("
            SELECT id, nombres, apellidos, email FROM colaboradores c
            WHERE " . implode(' AND ', $where) . "
            ORDER BY c.municipio, c.barrio
        ");
        $stmt->execute($params);
        $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $insertados = 0;
        $insertStmt = $db->prepare("
            INSERT INTO email_cola (campana_id, colaborador_id, email_destino, asunto, cuerpo_html, estado)
            VALUES (?, ?, ?, ?, ?, 'pendiente')
        ");
        foreach ($colaboradores as $colab) {
            $replace = ['{{nombre}}' => $colab['nombres']];
            $asunto = str_replace(array_keys($replace), array_values($replace), $campana['asunto']);
            $cuerpo = str_replace(array_keys($replace), array_values($replace), $campana['cuerpo_html']);

            $insertStmt->execute([$campanaId, $colab['id'], $colab['email'], $asunto, $cuerpo]);
            if ($insertStmt->rowCount() > 0) $insertados++;
        }

        return $insertados;
    }

    public static function procesarCola(int $limit = 20): array
    {
        $db = self::db();
        $resultados = ['enviados' => 0, 'fallidos' => 0, 'errores' => []];

        $stmt = $db->prepare("
            SELECT ec.*, c.nombres, c.apellidos
            FROM email_cola ec
            JOIN email_campanas c ON ec.campana_id = c.id
            WHERE ec.estado = 'pendiente'
              AND (c.estado = 'activa' OR (c.estado = 'enviando'))
            ORDER BY ec.id ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pendientes as $item) {
            try {
                $ok = self::sendMail($item['email_destino'], $item['asunto'], $item['cuerpo_html']);
                if ($ok) {
                    $db->prepare("UPDATE email_cola SET estado = 'enviado', enviado_en = NOW() WHERE id = ?")->execute([$item['id']]);
                    $db->prepare("UPDATE email_campanas SET enviados = enviados + 1 WHERE id = ?")->execute([$item['campana_id']]);
                    $db->prepare("INSERT INTO email_log (campana_id, colaborador_id, email_destino, estado) VALUES (?, ?, ?, 'enviado')")
                        ->execute([$item['campana_id'], $item['colaborador_id'], $item['email_destino']]);
                    $resultados['enviados']++;
                } else {
                    throw new \Exception('mail() returned false');
                }
            } catch (\Throwable $e) {
                $db->prepare("UPDATE email_cola SET estado = 'fallido', error = ?, reintentos = reintentos + 1 WHERE id = ?")
                    ->execute([$e->getMessage(), $item['id']]);
                $db->prepare("INSERT INTO email_log (campana_id, colaborador_id, email_destino, estado, error) VALUES (?, ?, ?, 'fallido', ?)")
                    ->execute([$item['campana_id'], $item['colaborador_id'], $item['email_destino'], $e->getMessage()]);
                $resultados['fallidos']++;
                $resultados['errores'][] = $item['email_destino'] . ': ' . $e->getMessage();
            }
        }

        $stmt = $db->query("
            SELECT id FROM email_campanas
            WHERE estado = 'activa'
              AND total_destinatarios > 0
              AND enviados >= total_destinatarios
        ");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $db->prepare("UPDATE email_campanas SET estado = 'completada' WHERE id = ?")->execute([$row['id']]);
        }

        return $resultados;
    }

    public static function getCampanas(): array
    {
        $db = self::db();
        return $db->query("
            SELECT e.*,
                   (SELECT COUNT(*) FROM email_cola WHERE campana_id = e.id AND estado = 'pendiente') AS pendientes
            FROM email_campanas e
            ORDER BY e.creado_en DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCampana(int $id): ?array
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT e.*,
                   (SELECT COUNT(*) FROM email_cola WHERE campana_id = e.id AND estado = 'pendiente') AS pendientes,
                   (SELECT COUNT(*) FROM email_log WHERE campana_id = e.id AND estado = 'abierto') AS abiertos_real
            FROM email_campanas e WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $campana = $stmt->fetch(PDO::FETCH_ASSOC);
        return $campana ?: null;
    }

    public static function getPlantillas(): array
    {
        $db = self::db();
        return $db->query("SELECT * FROM email_plantillas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function crearPlantilla(string $nombre, string $asunto, string $cuerpoHtml, array $variables = []): int
    {
        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO email_plantillas (nombre, asunto, cuerpo_html, variables)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE asunto = VALUES(asunto), cuerpo_html = VALUES(cuerpo_html), variables = VALUES(variables)
        ");
        $stmt->execute([$nombre, $asunto, $cuerpoHtml, json_encode($variables, JSON_UNESCAPED_UNICODE)]);
        return (int)$db->lastInsertId();
    }

    public static function getStats(): array
    {
        $db = self::db();
        $total = (int)$db->query("SELECT COUNT(*) FROM email_cola")->fetchColumn();
        $enviados = (int)$db->query("SELECT COUNT(*) FROM email_cola WHERE estado = 'enviado'")->fetchColumn();
        $fallidos = (int)$db->query("SELECT COUNT(*) FROM email_cola WHERE estado = 'fallido'")->fetchColumn();
        $pendientes = (int)$db->query("SELECT COUNT(*) FROM email_cola WHERE estado = 'pendiente'")->fetchColumn();
        $abiertos = (int)$db->query("SELECT COUNT(*) FROM email_log WHERE estado = 'abierto'")->fetchColumn();
        $hoy = (int)$db->query("SELECT COUNT(*) FROM email_log WHERE DATE(creado_en) = CURDATE() AND estado = 'enviado'")->fetchColumn();
        $campanasActivas = (int)$db->query("SELECT COUNT(*) FROM email_campanas WHERE estado IN ('activa','enviando')")->fetchColumn();

        $tasaApertura = $enviados > 0 ? round($abiertos / $enviados * 100, 1) : 0;

        return [
            'total' => $total,
            'enviados' => $enviados,
            'fallidos' => $fallidos,
            'pendientes' => $pendientes,
            'abiertos' => $abiertos,
            'hoy' => $hoy,
            'campanas_activas' => $campanasActivas,
            'tasa_apertura' => $tasaApertura,
        ];
    }

    public static function getHistorial(int $campanaId = 0, int $page = 1, int $perPage = 50): array
    {
        $db = self::db();
        $where = '';
        $params = [];
        if ($campanaId) {
            $where = "WHERE campana_id = ?";
            $params[] = $campanaId;
        }
        $offset = ($page - 1) * $perPage;

        $count = (int)$db->query("SELECT COUNT(*) FROM email_log $where")->fetchColumn();

        $stmt = $db->prepare("
            SELECT el.*, c.nombres, c.apellidos, ca.nombre AS campana_nombre
            FROM email_log el
            LEFT JOIN colaboradores c ON el.colaborador_id = c.id
            JOIN email_campanas ca ON el.campana_id = ca.id
            $where
            ORDER BY el.creado_en DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['data' => $data, 'total' => $count, 'page' => $page, 'pages' => ceil($count / $perPage)];
    }

    public static function procesarApertura(string $email, int $campanaId): void
    {
        $db = self::db();
        $db->prepare("
            UPDATE email_cola SET estado = 'abierto', abierto_en = NOW()
            WHERE email_destino = ? AND campana_id = ? AND estado = 'enviado'
        ")->execute([$email, $campanaId]);
        $db->prepare("
            UPDATE email_campanas SET abiertos = abiertos + 1 WHERE id = ?
        ")->execute([$campanaId]);
        $db->prepare("
            INSERT INTO email_log (campana_id, email_destino, estado) VALUES (?, ?, 'abierto')
        ")->execute([$campanaId, $email]);
    }
}
