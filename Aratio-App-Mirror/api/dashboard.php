<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';
$campanaId = (int)($_GET['campana_id'] ?? $_SESSION['campana_activa'] ?? 0);
$userId = (int)($_SESSION['user_id'] ?? 0);

try {
    switch ($action) {
        case 'kpi':
            handleKpi($db, $campanaId);
            break;
        case 'geo':
            handleGeo($db, $campanaId);
            break;
        case 'tendencias':
            handleTendencias($db, $campanaId);
            break;
        case 'recientes':
            handleRecientes($db, $campanaId);
            break;
        case 'alas_stats':
            handleAlasStats($db);
            break;
        case 'zonas_semaforo':
            handleZonasSemaforo($db, $campanaId);
            break;
        case 'distribucion':
            handleDistribucion($db, $campanaId);
            break;
        case 'otros_mapas':
            handleOtrosMapas($db, $campanaId);
            break;
        case 'lideres_por_territorio':
            handleLideresPorTerritorio($db, $campanaId);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function handleKpi(PDO $db, int $campanaId): void
{
    $data = [];

    // Colaboradores (total general)
    $stmt = $db->prepare("SELECT COUNT(*) FROM colaboradores WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $data['colaboradores'] = (int)$stmt->fetchColumn();

    // Padrinos = colaboradores con perfil de liderazgo
    $stmt = $db->prepare("SELECT COUNT(*) FROM colaboradores WHERE campana_id = ? AND (perfil LIKE '%Lider%' OR perfil LIKE '%Líder%' OR perfil LIKE '%Candidat%')");
    $stmt->execute([$campanaId]);
    $data['padrinos'] = (int)$stmt->fetchColumn();

    // Seguidores = colaboradores que NO son padrinos
    $data['seguidores'] = max(0, $data['colaboradores'] - $data['padrinos']);

    // Activos
    $stmt = $db->prepare("SELECT COUNT(*) FROM colaboradores WHERE campana_id = ? AND (estado IS NULL OR estado NOT IN ('inactivo','Inactivo'))");
    $stmt->execute([$campanaId]);
    $data['activos'] = (int)$stmt->fetchColumn();

    // Lideres directos (personas que tienen seguidores asignados)
    $stmt = $db->prepare("SELECT COUNT(DISTINCT lider_directo) FROM colaboradores WHERE campana_id = ? AND lider_directo IS NOT NULL AND lider_directo != ''");
    $stmt->execute([$campanaId]);
    $data['lideres'] = (int)$stmt->fetchColumn();

    // Perfiles
    $stmt = $db->prepare("SELECT perfil, COUNT(*) AS total FROM colaboradores WHERE campana_id = ? GROUP BY perfil ORDER BY total DESC");
    $stmt->execute([$campanaId]);
    $data['perfiles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Municipios
    $stmt = $db->prepare("SELECT municipio, COUNT(*) AS total FROM colaboradores WHERE campana_id = ? AND municipio IS NOT NULL GROUP BY municipio ORDER BY total DESC LIMIT 10");
    $stmt->execute([$campanaId]);
    $data['top_municipios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data['total_municipios'] = (int)$db->query("SELECT COUNT(DISTINCT municipio) FROM colaboradores WHERE campana_id = $campanaId AND municipio IS NOT NULL")->fetchColumn();

    // Geografía detallada
    $data['comunas'] = (int)$db->query("SELECT COUNT(DISTINCT territorio) FROM colaboradores WHERE campana_id = $campanaId AND tipo_territorio = 'Comuna' AND territorio IS NOT NULL")->fetchColumn();
    $data['barrios'] = (int)$db->query("SELECT COUNT(DISTINCT barrio) FROM colaboradores WHERE campana_id = $campanaId AND barrio IS NOT NULL")->fetchColumn();
    $data['urbano'] = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND tipo_territorio = 'Comuna'")->fetchColumn();
    $data['rural'] = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id = $campanaId AND tipo_territorio IN ('Corregimiento','Rural','Vereda')")->fetchColumn();

    // Donaciones
    $stmt = $db->prepare("SELECT COUNT(*) FROM donaciones WHERE campana_id = ? AND estado = 'confirmada'");
    $stmt->execute([$campanaId]);
    $data['donaciones'] = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(monto), 0) FROM donaciones WHERE campana_id = ? AND estado = 'confirmada'");
    $stmt->execute([$campanaId]);
    $data['recaudado'] = (float)$stmt->fetchColumn();

    // Eventos
    $stmt = $db->prepare("SELECT COUNT(*) FROM eventos WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $data['eventos'] = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM eventos WHERE campana_id = ? AND fecha_inicio >= NOW()");
    $stmt->execute([$campanaId]);
    $data['eventos_proximos'] = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(asistentes_confirmados), 0) FROM eventos WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $data['asistentes'] = (int)$stmt->fetchColumn();

    // Asistencia real desde tabla asistencia_eventos
    $stmt = $db->prepare("SELECT COUNT(*) FROM asistencia_eventos ae JOIN eventos e ON ae.evento_id = e.id WHERE e.campana_id = ? AND ae.asistio = 1");
    $stmt->execute([$campanaId]);
    $data['asistencia_real'] = (int)$stmt->fetchColumn();

    // Acciones
    $stmt = $db->prepare("SELECT COUNT(*) FROM acciones_comunitarias WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $data['acciones'] = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COALESCE(SUM(personas_contactadas), 0) FROM acciones_comunitarias WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $data['contactadas'] = (int)$stmt->fetchColumn();

    // Compromisos
    $stmt = $db->prepare("SELECT COUNT(*) FROM compromisos WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $data['compromisos'] = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM compromisos WHERE campana_id = ? AND estado = 'cumplido'");
    $stmt->execute([$campanaId]);
    $data['compromisos_cumplidos'] = (int)$stmt->fetchColumn();

    // Instagram (desde storage/maestro_instagram.json)
    $igStats = ['total_publicaciones' => 0];
    $igPath = __DIR__ . '/../storage/instagram_data.json';
    if (file_exists($igPath)) {
        $igData = json_decode(file_get_contents($igPath), true);
        $igStats = $igData['estadisticas'] ?? $igStats;
    }
    $data['instagram'] = (int)($igStats['total_publicaciones'] ?? 0);

    // Facebook (desde tabla fb_posts)
    $data['facebook'] = (int)$db->query("SELECT COUNT(*) FROM fb_posts")->fetchColumn();

    // WhatsApp (ALAS)
    $data['whatsapp_activos'] = (int)$db->query("SELECT COUNT(*) FROM whatsapp_conversaciones WHERE estado = 'activa'")->fetchColumn();
    $data['whatsapp_no_leidos'] = (int)$db->query("SELECT COALESCE(SUM(unread), 0) FROM whatsapp_conversaciones WHERE estado = 'activa'")->fetchColumn();

    // Phone Banking
    $data['llamadas'] = (int)$db->query("SELECT COUNT(*) FROM llamadas_log WHERE DATE(creado_en) = CURDATE()")->fetchColumn();
    $data['llamadas_pendientes'] = (int)$db->query("SELECT COUNT(*) FROM llamadas_cola WHERE estado = 'pendiente'")->fetchColumn();

    // Email
    $data['emails_enviados'] = (int)$db->query("SELECT COUNT(*) FROM email_log WHERE DATE(creado_en) = CURDATE() AND estado = 'enviado'")->fetchColumn();
    $data['emails_pendientes'] = (int)$db->query("SELECT COUNT(*) FROM email_cola WHERE estado = 'pendiente'")->fetchColumn();

    jsonResponse(['success' => true, 'data' => $data]);
}

function handleGeo(PDO $db, int $campanaId): void
{
    $stmt = $db->prepare("
        SELECT municipio, COUNT(*) AS total,
               SUM(CASE WHEN perfil = 'Lider' THEN 1 ELSE 0 END) AS lideres,
               SUM(CASE WHEN perfil = 'Movilizador' THEN 1 ELSE 0 END) AS movilizadores,
               SUM(CASE WHEN perfil = 'Simpatizante' THEN 1 ELSE 0 END) AS simpatizantes
        FROM colaboradores
        WHERE campana_id = ? AND municipio IS NOT NULL
        GROUP BY municipio
        ORDER BY total DESC
    ");
    $stmt->execute([$campanaId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = array_sum(array_column($data, 'total'));

    jsonResponse(['success' => true, 'data' => $data, 'total' => $total]);
}

function handleTendencias(PDO $db, int $campanaId): void
{
    // Colaboradores registrados por mes (últimos 12)
    $stmt = $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes, COUNT(*) AS total
        FROM colaboradores
        WHERE campana_id = $campanaId AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY mes ORDER BY mes ASC
    ");
    $colabTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Donaciones por mes
    $stmt = $db->query("
        SELECT DATE_FORMAT(fecha_donacion, '%Y-%m') AS mes, COUNT(*) AS total, SUM(monto) AS monto
        FROM donaciones
        WHERE campana_id = $campanaId AND estado = 'confirmada' AND fecha_donacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY mes ORDER BY mes ASC
    ");
    $donacionTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Eventos por mes
    $stmt = $db->query("
        SELECT DATE_FORMAT(fecha_inicio, '%Y-%m') AS mes, COUNT(*) AS total
        FROM eventos
        WHERE campana_id = $campanaId AND fecha_inicio >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY mes ORDER BY mes ASC
    ");
    $eventoTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['success' => true, 'data' => [
        'colaboradores' => $colabTrend ?: [],
        'donaciones' => $donacionTrend ?: [],
        'eventos' => $eventoTrend ?: [],
    ]]);
}

function handleRecientes(PDO $db, int $campanaId): void
{
    // Últimos colaboradores
    $stmt = $db->prepare("SELECT id, nombres, apellidos, perfil, municipio, creado_en FROM colaboradores WHERE campana_id = ? ORDER BY creado_en DESC LIMIT 10");
    $stmt->execute([$campanaId]);
    $data['colaboradores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Últimas donaciones
    $stmt = $db->prepare("SELECT d.monto, d.fecha_donacion, c.nombres, c.apellidos FROM donaciones d JOIN colaboradores c ON d.colaborador_id = c.id WHERE d.campana_id = ? AND d.estado = 'confirmada' ORDER BY d.fecha_donacion DESC LIMIT 10");
    $stmt->execute([$campanaId]);
    $data['donaciones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Próximos eventos
    $stmt = $db->prepare("SELECT id, nombre, fecha_inicio, lugar FROM eventos WHERE campana_id = ? AND fecha_inicio >= NOW() ORDER BY fecha_inicio ASC LIMIT 10");
    $stmt->execute([$campanaId]);
    $data['eventos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Actividad ALAS reciente
    $stmt = $db->prepare("
        SELECT ac.*, c.nombres, c.apellidos FROM actividad_colaborador ac
        JOIN colaboradores c ON ac.colaborador_id = c.id
        WHERE c.campana_id = ?
        ORDER BY ac.creado_en DESC LIMIT 15
    ");
    $stmt->execute([$campanaId]);
    $data['actividad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['success' => true, 'data' => $data]);
}

function handleAlasStats(PDO $db): void
{
    $data = [];

    // WhatsApp
    $data['whatsapp'] = [
        'conversaciones' => (int)$db->query("SELECT COUNT(*) FROM whatsapp_conversaciones WHERE estado = 'activa'")->fetchColumn(),
        'no_leidos' => (int)$db->query("SELECT COALESCE(SUM(unread), 0) FROM whatsapp_conversaciones WHERE estado = 'activa'")->fetchColumn(),
        'broadcast_pendientes' => (int)$db->query("SELECT COUNT(*) FROM whatsapp_broadcast_queue WHERE estado = 'pending'")->fetchColumn(),
    ];

    // Workflows
    $data['workflows'] = [
        'reglas_activas' => (int)$db->query("SELECT COUNT(*) FROM workflow_reglas WHERE activo = TRUE")->fetchColumn(),
        'ejecuciones_hoy' => (int)$db->query("SELECT COUNT(*) FROM workflow_log WHERE DATE(ejecutado_en) = CURDATE()")->fetchColumn(),
        'pendientes' => (int)$db->query("SELECT COUNT(*) FROM workflow_acciones_pendientes WHERE estado = 'pending'")->fetchColumn(),
    ];

    // Phone Banking
    $data['phone'] = [
        'llamadas_hoy' => (int)$db->query("SELECT COUNT(*) FROM llamadas_log WHERE DATE(creado_en) = CURDATE()")->fetchColumn(),
        'pendientes' => (int)$db->query("SELECT COUNT(*) FROM llamadas_cola WHERE estado = 'pendiente'")->fetchColumn(),
        'campanas_activas' => (int)$db->query("SELECT COUNT(*) FROM llamadas_campanas WHERE estado IN ('activa','pausada')")->fetchColumn(),
    ];

    // Email
    $data['email'] = [
        'enviados_hoy' => (int)$db->query("SELECT COUNT(*) FROM email_log WHERE DATE(creado_en) = CURDATE() AND estado = 'enviado'")->fetchColumn(),
        'pendientes' => (int)$db->query("SELECT COUNT(*) FROM email_cola WHERE estado = 'pendiente'")->fetchColumn(),
        'campanas_activas' => (int)$db->query("SELECT COUNT(*) FROM email_campanas WHERE estado IN ('activa','enviando')")->fetchColumn(),
    ];

    jsonResponse(['success' => true, 'data' => $data]);
}

function handleZonasSemaforo(PDO $db, int $campanaId): void
{
    $totalLideres = (int)$db->query("SELECT COUNT(DISTINCT c.id) FROM colaboradores c WHERE c.campana_id = $campanaId AND (c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%')")->fetchColumn();
    $conTrabajo = (int)$db->query("SELECT COUNT(DISTINCT z.colaborador_id) FROM zonas_trabajo_social z JOIN colaboradores c ON z.colaborador_id = c.id WHERE c.campana_id = $campanaId AND z.activo = 1 AND (c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%')")->fetchColumn();
    $sinTrabajo = max(0, $totalLideres - $conTrabajo);

    $stmt = $db->prepare("
        SELECT z.territorio_id, t.barrio, t.Territorio, t.municipio,
               COUNT(DISTINCT z.colaborador_id) AS n_responsables,
               t.Tipo_territorio,
               ST_AsGeoJSON(t.geometria) AS geometry_json
        FROM zonas_trabajo_social z
        JOIN territorios t ON z.territorio_id = t.id
        JOIN colaboradores c ON z.colaborador_id = c.id
        WHERE z.activo = 1 AND c.campana_id = ? AND t.geometria IS NOT NULL
        GROUP BY z.territorio_id, t.barrio
        ORDER BY n_responsables DESC
    ");
    $stmt->execute([$campanaId]);
    $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $features = [];
    foreach ($zonas as $z) {
        $geometry = json_decode($z['geometry_json'], true);
        $n = (int)$z['n_responsables'];
        if ($n >= 5) {
            $color = '#16a34a';
            $fillColor = '#22c55e';
            $nivel = 'Alta';
        } elseif ($n >= 3) {
            $color = '#ca8a04';
            $fillColor = '#eab308';
            $nivel = 'Media';
        } else {
            $color = '#dc2626';
            $fillColor = '#ef4444';
            $nivel = 'Baja';
        }
        $features[] = [
            'type' => 'Feature',
            'geometry' => $geometry,
            'properties' => [
                'territorio_id' => $z['territorio_id'],
                'barrio' => $z['barrio'],
                'Territorio' => $z['Territorio'],
                'municipio' => $z['municipio'],
                'Tipo_territorio' => $z['Tipo_territorio'],
                'n_responsables' => $n,
                'color' => $color,
                'fillColor' => $fillColor,
                'nivel' => $nivel
            ]
        ];
    }

    jsonResponse([
        'success' => true,
        'data' => [
            'lideres_total' => $totalLideres,
            'con_trabajo' => $conTrabajo,
            'sin_trabajo' => $sinTrabajo,
            'geojson' => [
                'type' => 'FeatureCollection',
                'features' => $features
            ]
        ]
    ]);
}

function handleDistribucion(PDO $db, int $campanaId): void
{
    $stmt = $db->prepare("
        SELECT c.territorio, c.barrio, c.municipio, COUNT(*) AS total,
               SUM(CASE WHEN c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%' THEN 1 ELSE 0 END) AS lideres
        FROM colaboradores c
        WHERE c.campana_id = ? AND c.barrio IS NOT NULL AND c.barrio != ''
        GROUP BY c.territorio, c.barrio, c.municipio
        ORDER BY total DESC LIMIT 15
    ");
    $stmt->execute([$campanaId]);
    $barrios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
        SELECT c.territorio, c.tipo_territorio, COUNT(*) AS total,
               SUM(CASE WHEN c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%' THEN 1 ELSE 0 END) AS lideres
        FROM colaboradores c
        WHERE c.campana_id = ? AND c.territorio IS NOT NULL AND c.territorio != ''
        GROUP BY c.territorio
        ORDER BY total DESC LIMIT 15
    ");
    $stmt->execute([$campanaId]);
    $territorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['success' => true, 'data' => [
        'barrios' => $barrios ?: [],
        'territorios' => $territorios ?: [],
    ]]);
}

function handleOtrosMapas(PDO $db, int $campanaId): void
{
    $getGeoJSON = function(array $rows, string $labelField, string $countField, array $palette): array {
        if (empty($rows)) return ['type'=>'FeatureCollection', 'features'=>[]];
        $max = max(array_column($rows, $countField)) ?: 1;
        $features = [];
        foreach ($rows as $r) {
            $n = (int)$r[$countField];
            $ratio = $n / $max;
            $level = $ratio >= 0.67 ? 0 : ($ratio >= 0.33 ? 1 : 2);
            $geometry = json_decode($r['geometry_json'], true);
            if (!$geometry) continue;
            $label = $r[$labelField] ?? '';
            $features[] = [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'label' => $label, 'count' => $n,
                    'nivel' => ['Alta','Media','Baja'][$level],
                    'color' => $palette[$level * 2], 'fillColor' => $palette[$level * 2 + 1],
                    'tooltip' => "<b>{$label}</b><br>{$n} — {$countField}",
                    'popup' => "<b>{$label}</b><br>{$countField}: <b>{$n}</b><br>Cobertura: <b style='color:{$palette[$level*2]}'>" . ['Alta','Media','Baja'][$level] . "</b>",
                ]
            ];
        }
        return ['type' => 'FeatureCollection', 'features' => $features];
    };

    $palettes = [
        'compromisos' => ['#7c3aed','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe'],
        'eventos'     => ['#ea580c','#f97316','#fb923c','#fdba74','#fed7aa'],
        'acciones'    => ['#0d9488','#14b8a6','#2dd4bf','#5eead4','#ccfbf1'],
        'instagram'   => ['#db2777','#ec4899','#f472b6','#f9a8d4','#fbcfe8'],
    ];

    // Compromisos
    $stmt = $db->prepare("SELECT c.territorio, c.barrio, COUNT(*) AS total, ST_AsGeoJSON(t.geometria) AS geometry_json FROM compromisos c LEFT JOIN territorios t ON LOWER(TRIM(c.municipio))=LOWER(TRIM(t.municipio)) AND LOWER(TRIM(c.barrio))=LOWER(TRIM(t.barrio)) WHERE c.campana_id=? AND t.geometria IS NOT NULL GROUP BY c.barrio ORDER BY total DESC LIMIT 50");
    $stmt->execute([$campanaId]);
    $c = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $datos['compromisos'] = $getGeoJSON($c, 'barrio', 'total', $palettes['compromisos']);

    // Eventos
    $stmt = $db->prepare("SELECT t.Territorio, t.barrio, COUNT(*) AS total, ST_AsGeoJSON(t.geometria) AS geometry_json FROM eventos e JOIN territorios t ON e.territorio_id=t.id WHERE e.campana_id=? AND t.geometria IS NOT NULL GROUP BY e.territorio_id ORDER BY total DESC LIMIT 50");
    $stmt->execute([$campanaId]);
    $e = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $datos['eventos'] = $getGeoJSON($e, 'Territorio', 'total', $palettes['eventos']);

    // Acciones comunitarias
    $stmt = $db->prepare("SELECT c.territorio, c.barrio, COUNT(*) AS total, ST_AsGeoJSON(t.geometria) AS geometry_json FROM acciones_comunitarias c LEFT JOIN territorios t ON LOWER(TRIM(c.municipio))=LOWER(TRIM(t.municipio)) AND LOWER(TRIM(c.barrio))=LOWER(TRIM(t.barrio)) WHERE c.campana_id=? AND t.geometria IS NOT NULL GROUP BY c.barrio ORDER BY total DESC LIMIT 50");
    $stmt->execute([$campanaId]);
    $a = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $datos['acciones'] = $getGeoJSON($a, 'barrio', 'total', $palettes['acciones']);

    // Instagram — colaboradores con IG por territorio
    $stmt = $db->prepare("SELECT c.territorio, c.barrio, COUNT(*) AS total, ST_AsGeoJSON(t.geometria) AS geometry_json FROM colaboradores c LEFT JOIN territorios t ON LOWER(TRIM(c.municipio))=LOWER(TRIM(t.municipio)) AND LOWER(TRIM(c.barrio))=LOWER(TRIM(t.barrio)) WHERE c.campana_id=? AND t.geometria IS NOT NULL AND c.instagram_username IS NOT NULL AND c.instagram_username != '' GROUP BY c.barrio ORDER BY total DESC LIMIT 50");
    $stmt->execute([$campanaId]);
    $ig = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $datos['instagram'] = $getGeoJSON($ig, 'barrio', 'total', $palettes['instagram']);

    jsonResponse(['success' => true, 'data' => $datos]);
}

function handleLideresPorTerritorio(PDO $db, int $campanaId): void
{
    // Get all collaborators grouped by municipio + barrio
    $stmt = $db->prepare("
        SELECT c.municipio, c.barrio, COUNT(*) AS n_colaboradores
        FROM colaboradores c
        WHERE c.campana_id = ? AND c.municipio IS NOT NULL AND c.municipio != ''
        GROUP BY c.municipio, c.barrio
        ORDER BY n_colaboradores DESC
    ");
    $stmt->execute([$campanaId]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all territory geometries — index by key for O(1) lookup
    $geoStmt = $db->query("SELECT id, municipio, barrio, Territorio, Tipo_territorio, ST_AsGeoJSON(geometria) AS geometry_json FROM territorios WHERE geometria IS NOT NULL");
    $territorioPorKey = [];
    $territorioPorMuni = [];
    foreach ($geoStmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
        $key = strtolower(trim($t['municipio'] ?? '')) . '|' . strtolower(trim($t['barrio'] ?? ''));
        $territorioPorKey[$key] = $t;
        $mk = strtolower(trim($t['municipio'] ?? ''));
        if (!isset($territorioPorMuni[$mk])) {
            $territorioPorMuni[$mk] = $t;
        }
    }

    $totalColaboradores = 0;
    $features = [];

    foreach ($groups as $g) {
        $n = (int)$g['n_colaboradores'];
        $totalColaboradores += $n;

        $barrio = $g['barrio'] ?? '';
        $municipio = $g['municipio'] ?? '';
        $key = strtolower(trim($municipio)) . '|' . strtolower(trim($barrio));
        $match = $territorioPorKey[$key] ?? $territorioPorMuni[strtolower(trim($municipio))] ?? null;

        if (!$match) continue;

        $geometry = json_decode($match['geometry_json'], true);
        if (!$geometry) continue;

        if ($n >= 5) {
            $color = '#1d4ed8';
            $fillColor = '#3b82f6';
            $nivel = 'Alta';
        } elseif ($n >= 3) {
            $color = '#2563eb';
            $fillColor = '#60a5fa';
            $nivel = 'Media';
        } else {
            $color = '#93c5fd';
            $fillColor = '#bfdbfe';
            $nivel = 'Baja';
        }

        $features[] = [
            'type' => 'Feature',
            'geometry' => $geometry,
            'properties' => [
                'territorio_id' => $match['id'],
                'barrio' => $match['barrio'] ?? $barrio,
                'Territorio' => $match['Territorio'] ?? $municipio,
                'municipio' => $municipio,
                'Tipo_territorio' => $match['Tipo_territorio'] ?? '',
                'n_colaboradores' => $n,
                'color' => $color,
                'fillColor' => $fillColor,
                'nivel' => $nivel
            ]
        ];
    }

    jsonResponse([
        'success' => true,
        'data' => [
            'total_colaboradores' => $totalColaboradores,
            'geojson' => [
                'type' => 'FeatureCollection',
                'features' => $features
            ]
        ]
    ]);
}
