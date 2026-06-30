<?php

require_once __DIR__ . '/WorkflowEngine.php';
require_once __DIR__ . '/ActivityLogger.php';

class PhoneBanking
{
    private static ?PDO $db = null;

    const RESULTADOS = [
        'contestó' => 'Contestó',
        'no_contesta' => 'No contestó',
        'llamar_despues' => 'Llamar después',
        'no_interesado' => 'No interesado',
        'equivocado' => 'Número equivocado',
        'ocupado' => 'Ocupado',
        'otro' => 'Otro',
    ];

    private static function db(): PDO
    {
        if (self::$db === null) {
            self::$db = getDB();
        }
        return self::$db;
    }

    public static function crearCampana(string $nombre, string $descripcion = '', string $objetivo = '', array $filtros = [], ?int $usuarioId = null): int
    {
        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO llamadas_campanas (nombre, descripcion, objetivo, filtros_json, estado, creado_por)
            VALUES (?, ?, ?, ?, 'activa', ?)
        ");
        $stmt->execute([$nombre, $descripcion, $objetivo, json_encode($filtros, JSON_UNESCAPED_UNICODE), $usuarioId]);
        $campanaId = (int)$db->lastInsertId();

        $total = self::generarCola($campanaId);
        $db->prepare("UPDATE llamadas_campanas SET total_colaboradores = ? WHERE id = ?")->execute([$total, $campanaId]);

        return $campanaId;
    }

    public static function generarCola(int $campanaId): int
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT filtros_json FROM llamadas_campanas WHERE id = ?");
        $stmt->execute([$campanaId]);
        $campana = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$campana) return 0;

        $filtros = json_decode($campana['filtros_json'], true) ?? [];
        $where = ["(c.estado IS NULL OR c.estado NOT IN ('inactivo','Inactivo'))"];
        $params = [];

        if (!empty($filtros['territorio_id'])) {
            $where[] = "c.territorio_id = ?";
            $params[] = (int)$filtros['territorio_id'];
        }
        if (!empty($filtros['municipio'])) {
            $where[] = "c.municipio = ?";
            $params[] = $filtros['municipio'];
        }
        if (!empty($filtros['perfil'])) {
            $where[] = "c.perfil = ?";
            $params[] = $filtros['perfil'];
        }
        if (!empty($filtros['lider_id'])) {
            $where[] = "c.lider_directo = ?";
            $params[] = (int)$filtros['lider_id'];
        }
        if (!empty($filtros['campana_id'])) {
            $where[] = "c.campana_id = ?";
            $params[] = (int)$filtros['campana_id'];
        }

        $where[] = "(c.telefono IS NOT NULL AND c.telefono != '')";

        $stmt = $db->prepare("
            SELECT id, nombres, apellidos, telefono, telefono_whatsapp
            FROM colaboradores c WHERE " . implode(' AND ', $where) . "
            ORDER BY c.municipio, c.barrio
        ");
        $stmt->execute($params);
        $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $insertados = 0;
        $insertStmt = $db->prepare("
            INSERT IGNORE INTO llamadas_cola (campana_id, colaborador_id, estado)
            VALUES (?, ?, 'pendiente')
        ");
        foreach ($colaboradores as $colab) {
            $insertStmt->execute([$campanaId, $colab['id']]);
            if ($insertStmt->rowCount() > 0) $insertados++;
        }

        return $insertados;
    }

    public static function siguienteLlamada(int $campanaId, int $agenteId): ?array
    {
        $db = self::db();
        $db->prepare("
            UPDATE llamadas_cola SET estado = 'saltada', agente_id = NULL
            WHERE campana_id = ? AND agente_id = ? AND estado = 'en_progreso'
        ")->execute([$campanaId, $agenteId]);

        $stmt = $db->prepare("
            SELECT lc.id, lc.colaborador_id, c.nombres, c.apellidos, c.telefono, c.telefono_whatsapp,
                   c.municipio, c.barrio, c.perfil, c.documento,
                   ca.objetivo, ca.nombre AS campana_nombre
            FROM llamadas_cola lc
            JOIN colaboradores c ON lc.colaborador_id = c.id
            JOIN llamadas_campanas ca ON lc.campana_id = ca.id
            WHERE lc.campana_id = ? AND lc.estado = 'pendiente'
            ORDER BY lc.id ASC
            LIMIT 1
        ");
        $stmt->execute([$campanaId]);
        $siguiente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$siguiente) return null;

        $db->prepare("
            UPDATE llamadas_cola SET estado = 'en_progreso', agente_id = ?, contactado_en = NOW()
            WHERE id = ?
        ")->execute([$agenteId, $siguiente['id']]);

        return $siguiente;
    }

    public static function registrarResultado(int $colaId, string $resultado, string $notas = '', int $duracionSeg = 0, int $agenteId = 0): bool
    {
        if (!array_key_exists($resultado, self::RESULTADOS)) return false;

        $db = self::db();
        $stmt = $db->prepare("
            SELECT lc.*, c.id AS colaborador_id, c.telefono, c.telefono_whatsapp
            FROM llamadas_cola lc
            JOIN colaboradores c ON lc.colaborador_id = c.id
            WHERE lc.id = ?
        ");
        $stmt->execute([$colaId]);
        $cola = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cola) return false;

        $db->prepare("
            UPDATE llamadas_cola SET estado = 'completada', duracion_seg = ?, resultado = ?
            WHERE id = ?
        ")->execute([$duracionSeg, $resultado, $colaId]);

        $db->prepare("
            INSERT INTO llamadas_log (cola_id, campana_id, colaborador_id, agente_id, resultado, notas, duracion_seg)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $colaId, $cola['campana_id'], $cola['colaborador_id'],
            $agenteId ?: $cola['agente_id'], $resultado, $notas, $duracionSeg
        ]);

        $db->prepare("
            UPDATE llamadas_campanas SET llamadas_completadas = llamadas_completadas + 1 WHERE id = ?
        ")->execute([$cola['campana_id']]);

        $usuarioId = $agenteId ?: $cola['agente_id'];
        if ($cola['colaborador_id'] && class_exists('ActivityLogger')) {
            ActivityLogger::log(
                (int)$cola['colaborador_id'],
                'llamada_' . $resultado,
                "Llamada: " . self::RESULTADOS[$resultado] . ($notas ? " — $notas" : ''),
                ['campana_id' => $cola['campana_id'], 'resultado' => $resultado, 'duracion_seg' => $duracionSeg],
                'llamadas_log',
                (int)$db->lastInsertId(),
                $usuarioId
            );
        }

        if (class_exists('WorkflowEngine')) {
            WorkflowEngine::trigger('llamada.finalizada', [
                'resultado' => $resultado,
                'notas' => $notas,
                'duracion_seg' => $duracionSeg,
                'campana_id' => $cola['campana_id'],
            ], (int)$cola['colaborador_id']);
        }

        return true;
    }

    public static function getCampanas(?string $estado = null): array
    {
        $db = self::db();
        $sql = "
            SELECT c.*,
                   (SELECT COUNT(*) FROM llamadas_cola WHERE campana_id = c.id) AS en_cola,
                   (SELECT COUNT(*) FROM llamadas_log WHERE campana_id = c.id) AS total_llamadas
            FROM llamadas_campanas c
        ";
        $params = [];
        if ($estado) {
            $sql .= " WHERE c.estado = ?";
            $params[] = $estado;
        }
        $sql .= " ORDER BY c.creado_en DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCampana(int $id): ?array
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM llamadas_cola WHERE campana_id = c.id) AS en_cola,
                   (SELECT COUNT(*) FROM llamadas_log WHERE campana_id = c.id) AS total_llamadas,
                   (SELECT COUNT(*) FROM llamadas_cola WHERE campana_id = c.id AND estado = 'pendiente') AS pendientes
            FROM llamadas_campanas c WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $campana = $stmt->fetch(PDO::FETCH_ASSOC);
        return $campana ?: null;
    }

    public static function getMiCola(int $agenteId, int $campanaId): array
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT lc.*, c.nombres, c.apellidos, c.telefono, c.municipio, c.barrio, c.perfil
            FROM llamadas_cola lc
            JOIN colaboradores c ON lc.colaborador_id = c.id
            WHERE lc.agente_id = ? AND lc.campana_id = ? AND lc.estado IN ('en_progreso','pendiente')
            ORDER BY lc.contactado_en DESC
        ");
        $stmt->execute([$agenteId, $campanaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getHistorial(array $filtros = [], int $page = 1, int $perPage = 50): array
    {
        $db = self::db();
        $where = [];
        $params = [];

        if (!empty($filtros['campana_id'])) {
            $where[] = "ll.campana_id = ?";
            $params[] = (int)$filtros['campana_id'];
        }
        if (!empty($filtros['agente_id'])) {
            $where[] = "ll.agente_id = ?";
            $params[] = (int)$filtros['agente_id'];
        }
        if (!empty($filtros['resultado'])) {
            $where[] = "ll.resultado = ?";
            $params[] = $filtros['resultado'];
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $count = (int)$db->query("SELECT COUNT(*) FROM llamadas_log ll $whereSQL")->fetchColumn();

        $stmt = $db->prepare("
            SELECT ll.*, c.nombres, c.apellidos, c.telefono, c.municipio,
                   ca.nombre AS campana_nombre,
                   u.nombre AS agente_nombre
            FROM llamadas_log ll
            JOIN colaboradores c ON ll.colaborador_id = c.id
            JOIN llamadas_campanas ca ON ll.campana_id = ca.id
            LEFT JOIN usuarios u ON ll.agente_id = u.id
            $whereSQL
            ORDER BY ll.creado_en DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['data' => $data, 'total' => $count, 'page' => $page, 'pages' => ceil($count / $perPage)];
    }

    public static function getStats(?int $campanaId = null): array
    {
        $db = self::db();
        $where = $campanaId ? "WHERE campana_id = " . (int)$campanaId : '';

        $total = (int)$db->query("SELECT COUNT(*) FROM llamadas_log $where")->fetchColumn();
        $hoy = (int)$db->query("SELECT COUNT(*) FROM llamadas_log $where AND DATE(creado_en) = CURDATE()")->fetchColumn();
        $semana = (int)$db->query("SELECT COUNT(*) FROM llamadas_log $where AND YEARWEEK(creado_en) = YEARWEEK(CURDATE())")->fetchColumn();

        $resultados = $db->query("
            SELECT resultado, COUNT(*) AS total
            FROM llamadas_log $where
            GROUP BY resultado ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_KEY_PAIR);

        $topAgentes = $db->query("
            SELECT u.nombre, COUNT(*) AS total
            FROM llamadas_log ll
            JOIN usuarios u ON ll.agente_id = u.id
            $where
            GROUP BY ll.agente_id ORDER BY total DESC LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        $campanasActivas = (int)$db->query("SELECT COUNT(*) FROM llamadas_campanas WHERE estado IN ('activa','pausada')")->fetchColumn();
        $pendientes = (int)$db->query("SELECT COUNT(*) FROM llamadas_cola WHERE estado = 'pendiente'")->fetchColumn();
        $duracionPromedio = (int)$db->query("SELECT COALESCE(AVG(duracion_seg), 0) FROM llamadas_log $where")->fetchColumn();

        return [
            'total' => $total,
            'hoy' => $hoy,
            'semana' => $semana,
            'pendientes' => $pendientes,
            'campanas_activas' => $campanasActivas,
            'duracion_promedio' => $duracionPromedio,
            'resultados' => $resultados,
            'top_agentes' => $topAgentes,
        ];
    }
}
