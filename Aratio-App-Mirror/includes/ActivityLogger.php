<?php

class ActivityLogger
{
    private static ?PDO $db = null;

    private static function db(): PDO
    {
        if (self::$db === null) {
            self::$db = getDB();
        }
        return self::$db;
    }

    public static function log(
        int $colaboradorId,
        string $tipo,
        string $descripcion,
        array $metadata = [],
        ?string $referenciaTabla = null,
        ?int $referenciaId = null,
        ?int $creadoPor = null
    ): int {
        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO actividad_colaborador 
                (colaborador_id, tipo, descripcion, metadata_json, referencia_tabla, referencia_id, creado_por, creado_en)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $colaboradorId,
            $tipo,
            $descripcion,
            json_encode($metadata, JSON_UNESCAPED_UNICODE),
            $referenciaTabla,
            $referenciaId,
            $creadoPor ?? ($_SESSION['user_id'] ?? null)
        ]);
        $activityId = (int)$db->lastInsertId();

        self::afterLog($colaboradorId, $tipo, $metadata);

        return $activityId;
    }

    public static function getByColaborador(
        int $colaboradorId,
        array $tipos = [],
        int $page = 1,
        int $perPage = 20
    ): array {
        $db = self::db();
        $where = "a.colaborador_id = ?";
        $params = [$colaboradorId];

        if (!empty($tipos)) {
            $placeholders = implode(',', array_fill(0, count($tipos), '?'));
            $where .= " AND a.tipo IN ({$placeholders})";
            $params = array_merge($params, $tipos);
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM actividad_colaborador a WHERE {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("
            SELECT a.*, u.nombre as creador_nombre
            FROM actividad_colaborador a
            LEFT JOIN usuarios u ON a.creado_por = u.id
            WHERE {$where}
            ORDER BY a.creado_en DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            $row['metadata_json'] = json_decode($row['metadata_json'], true);
        }

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    public static function getStats(int $colaboradorId): array
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT tipo, COUNT(*) as total
            FROM actividad_colaborador
            WHERE colaborador_id = ?
            GROUP BY tipo
            ORDER BY total DESC
        ");
        $stmt->execute([$colaboradorId]);
        $porTipo = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt = $db->prepare("SELECT COUNT(*) FROM actividad_colaborador WHERE colaborador_id = ?");
        $stmt->execute([$colaboradorId]);

        return [
            'total' => (int)$stmt->fetchColumn(),
            'por_tipo' => $porTipo
        ];
    }

    public static function getTipos(): array
    {
        return [
            'registro', 'evento_asistio', 'compromiso_creado', 'donacion_hizo',
            'whatsapp_enviado', 'whatsapp_recibido', 'estado_cambio', 'evaluacion',
            'lider_cambio', 'cumpleaños', 'telefonazo', 'simpatizante_registro'
        ];
    }

    private static function afterLog(int $colaboradorId, string $tipo, array $metadata): void
    {
        if (!class_exists('WorkflowEngine')) return;

        $triggerMap = [
            'registro' => 'colaborador.registrado',
        ];

        $trigger = $triggerMap[$tipo] ?? null;
        if ($trigger) {
            try {
                WorkflowEngine::trigger($trigger, $metadata, $colaboradorId);
            } catch (Throwable $e) {
                error_log("ALAS Workflow trigger error: " . $e->getMessage());
            }
        }
    }
}
