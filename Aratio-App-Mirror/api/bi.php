<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);

$filtros = [
    'campana_id' => (int)((isset($_GET['campana_id']) && $_GET['campana_id'] !== '') ? $_GET['campana_id'] : ($_SESSION['campana_activa'] ?? 0)),
    'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-d', strtotime('-12 months')),
    'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d'),
    'territorio_id' => (int)($_GET['territorio_id'] ?? 0),
    'tipo_territorio' => $_GET['tipo_territorio'] ?? '',
    'perfil' => $_GET['perfil'] ?? '',
    'lider_id' => (int)($_GET['lider_id'] ?? 0),
];

try {
    switch ($action) {
        case 'panorama':
            handlePanorama($db, $filtros);
            break;
        case 'territorio':
            handleTerritorio($db, $filtros);
            break;
        case 'red':
            handleRedSocial($db, $filtros);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida: ' . $action], 400);
    }
} catch (Exception $e) {
    error_log("BI API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

// ──────────────────────────────────────────────
// HELPER: convertir filas de territorios a GeoJSON FeatureCollection
// ──────────────────────────────────────────────
function rowsToGeoJSON(array $rows, string $countField, string $labelField, array $palette, string $tooltipTemplate = ''): array
{
    if (empty($rows)) return ['type' => 'FeatureCollection', 'features' => []];
    $max = max(array_column($rows, $countField)) ?: 1;
    $features = [];
    foreach ($rows as $r) {
        $n = (int)$r[$countField];
        $ratio = $max > 0 ? $n / $max : 0;
        $level = $ratio >= 0.67 ? 0 : ($ratio >= 0.33 ? 1 : 2);
        $geometry = json_decode($r['geometry_json'] ?? 'null', true);
        if (!$geometry) continue;
        $label = $r[$labelField] ?? '';
        $tooltip = $tooltipTemplate
            ? str_replace(['{label}','{count}','{nivel}'], [$label, $n, ['Alta','Media','Baja'][$level]], $tooltipTemplate)
            : "<b>{$label}</b><br>{$n} — {$countField}";
        $features[] = [
            'type' => 'Feature',
            'geometry' => $geometry,
            'properties' => [
                'label' => $label, 'count' => $n,
                'nivel' => ['Alta','Media','Baja'][$level],
                'color' => $palette[$level * 2] ?? '#666',
                'fillColor' => $palette[$level * 2 + 1] ?? '#999',
                'tooltip' => $tooltip,
                'popup' => "<b>{$label}</b><br>{$countField}: <b>{$n}</b><br>Cobertura: <b style='color:" . ($palette[$level*2]??'#666') . "'>" . ['Alta','Media','Baja'][$level] . "</b>",
            ]
        ];
    }
    return ['type' => 'FeatureCollection', 'features' => $features];
}

// ──────────────────────────────────────────────
// HELPER: variación porcentual vs periodo anterior
// ──────────────────────────────────────────────
function variacion(int $actual, int $anterior): array
{
    $pct = $anterior > 0 ? round(($actual - $anterior) / $anterior * 100) : ($actual > 0 ? 100 : 0);
    return [
        'valor' => $actual,
        'vs_periodo' => $pct,
        'tendencia' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'stable'),
    ];
}

// ──────────────────────────────────────────────
// PANORAMA — KPIs ejecutivos + alertas + tendencia
// ──────────────────────────────────────────────
function handlePanorama(PDO $db, array $f): void
{
    $cid = $f['campana_id'];
    $fd = $f['fecha_desde'];
    $fh = $f['fecha_hasta'];
    $monthAgo = date('Y-m-d', strtotime('-1 month'));
    $periodBefore = date('Y-m-d', strtotime('-2 months'));

    // KPIs con variación
    $total = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid")->fetchColumn();
    $totalAnt = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid AND created_at < '$monthAgo'")->fetchColumn();
    $kpi['colaboradores'] = variacion($total, $totalAnt);

    $lideres = (int)$db->query("SELECT COUNT(DISTINCT c.id) FROM colaboradores c WHERE c.campana_id=$cid AND (c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%')")->fetchColumn();
    $lideresAnt = (int)$db->query("SELECT COUNT(DISTINCT c.id) FROM colaboradores c WHERE c.campana_id=$cid AND (c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%') AND c.created_at < '$monthAgo'")->fetchColumn();
    $kpi['lideres'] = variacion($lideres, $lideresAnt);

    $activos = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid AND (estado IS NULL OR estado NOT IN ('inactivo','Inactivo'))")->fetchColumn();
    $activosAnt = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid AND (estado IS NULL OR estado NOT IN ('inactivo','Inactivo')) AND created_at < '$monthAgo'")->fetchColumn();
    $kpi['activos'] = variacion($activos, $activosAnt);

    $donaciones = (int)$db->query("SELECT COALESCE(SUM(monto),0) FROM donaciones WHERE campana_id=$cid AND estado='confirmada' AND fecha_donacion >= '$fd' AND fecha_donacion <= '$fh'")->fetchColumn();
    $donacionesAnt = (int)$db->query("SELECT COALESCE(SUM(monto),0) FROM donaciones WHERE campana_id=$cid AND estado='confirmada' AND fecha_donacion >= '$periodBefore' AND fecha_donacion < '$monthAgo'")->fetchColumn();
    $kpi['donaciones_periodo'] = variacion((int)$donaciones, (int)$donacionesAnt);

    $eventos = (int)$db->query("SELECT COUNT(*) FROM eventos WHERE campana_id=$cid AND fecha_inicio >= '$fd' AND fecha_inicio <= '$fh'")->fetchColumn();
    $eventosAnt = (int)$db->query("SELECT COUNT(*) FROM eventos WHERE campana_id=$cid AND fecha_inicio >= '$periodBefore' AND fecha_inicio < '$monthAgo'")->fetchColumn();
    $kpi['eventos_periodo'] = variacion($eventos, $eventosAnt);

    $whatsappActivos = (int)$db->query("SELECT COUNT(*) FROM whatsapp_conversaciones WHERE estado='activa'")->fetchColumn();
    $totalColab = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid")->fetchColumn();
    $tasaWA = $totalColab > 0 ? round($whatsappActivos / $totalColab * 100) : 0;
    $whatsappAnt = (int)$db->query("SELECT COUNT(*) FROM whatsapp_conversaciones WHERE estado='activa' AND ultimo_timestamp < '$monthAgo'")->fetchColumn();
    $tasaAnt = $totalColab > 0 ? round($whatsappAnt / $totalColab * 100) : 0;
    $kpi['whatsapp_tasa'] = variacion($tasaWA, $tasaAnt);

    // Inactivos (derivado: total - activos)
    $inactivos = max(0, $total - $activos);
    $kpi['inactivos'] = variacion($inactivos, 0);

    // Alertas
    $alertas = [];
    $sinTrabajo = (int)$db->query("SELECT COUNT(DISTINCT c.id) FROM colaboradores c LEFT JOIN zonas_trabajo_social z ON c.id=z.colaborador_id AND z.activo=1 WHERE c.campana_id=$cid AND (c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%') AND z.id IS NULL")->fetchColumn();
    if ($sinTrabajo > 0) $alertas[] = ['tipo' => 'warning', 'mensaje' => "{$sinTrabajo} líderes sin trabajo social"];
    $sinLider = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid AND (lider_directo IS NULL OR lider_directo='') AND (perfil NOT LIKE '%Lider%' AND perfil NOT LIKE '%Líder%')")->fetchColumn();
    if ($sinLider > 0) $alertas[] = ['tipo' => 'warning', 'mensaje' => "{$sinLider} seguidores sin líder asignado"];

    // Tendencia 12m
    $stmt = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes, COUNT(*) AS total FROM colaboradores WHERE campana_id=$cid AND created_at >= '$fd' GROUP BY mes ORDER BY mes");
    $tendencia = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Distribuciones: rol, estado, genero, nivel_participacion
    $distribuciones = [];

    $stmt = $db->query("SELECT perfil AS label, COUNT(*) AS total FROM colaboradores WHERE campana_id=$cid GROUP BY perfil ORDER BY total DESC");
    $distribuciones['rol'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmt = $db->query("SELECT COALESCE(NULLIF(estado,''), 'Sin estado') AS label, COUNT(*) AS total FROM colaboradores WHERE campana_id=$cid GROUP BY estado ORDER BY total DESC");
    $distribuciones['estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmt = $db->query("SELECT COALESCE(NULLIF(genero,''), 'Sin género') AS label, COUNT(*) AS total FROM colaboradores WHERE campana_id=$cid GROUP BY genero ORDER BY total DESC");
    $distribuciones['genero'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmt = $db->query("SELECT nivel_participacion AS label, COUNT(*) AS total FROM colaboradores WHERE campana_id=$cid AND nivel_participacion IS NOT NULL AND nivel_participacion != '' GROUP BY nivel_participacion ORDER BY total DESC");
    $distribuciones['nivel_participacion'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Top rankings
    $stmt = $db->query("SELECT c2.nombres, c2.apellidos, c2.municipio, COUNT(c1.id) AS seguidores FROM colaboradores c1 JOIN colaboradores c2 ON c1.lider_directo = c2.documento WHERE c1.campana_id=$cid AND c1.lider_directo IS NOT NULL AND c1.lider_directo != '' GROUP BY c1.lider_directo ORDER BY seguidores DESC LIMIT 10");
    $topLideres = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmt = $db->query("SELECT territorio, tipo_territorio, COUNT(*) AS total, SUM(CASE WHEN perfil LIKE '%Lider%' OR perfil LIKE '%Líder%' THEN 1 ELSE 0 END) AS lideres FROM colaboradores WHERE campana_id=$cid AND territorio IS NOT NULL AND territorio != '' GROUP BY territorio ORDER BY total DESC LIMIT 10");
    $topTerritorios = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmt = $db->query("SELECT barrio, territorio, COUNT(*) AS total, SUM(CASE WHEN perfil LIKE '%Lider%' OR perfil LIKE '%Líder%' THEN 1 ELSE 0 END) AS lideres FROM colaboradores WHERE campana_id=$cid AND barrio IS NOT NULL AND barrio != '' GROUP BY barrio ORDER BY total DESC LIMIT 10");
    $topBarrios = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    jsonResponse(['success' => true, 'data' => compact('kpi','alertas','tendencia','distribuciones','topLideres','topTerritorios','topBarrios')]);
}

// ──────────────────────────────────────────────
// TERRITORIO — Mapas + KPIs + Brechas
// ──────────────────────────────────────────────
function handleTerritorio(PDO $db, array $f): void
{
    $cid = $f['campana_id'];

    // KPIs geográficos
    $comunas = (int)$db->query("SELECT COUNT(DISTINCT territorio) FROM colaboradores WHERE campana_id=$cid AND tipo_territorio='Comuna' AND territorio IS NOT NULL")->fetchColumn();
    $barrios = (int)$db->query("SELECT COUNT(DISTINCT barrio) FROM colaboradores WHERE campana_id=$cid AND barrio IS NOT NULL")->fetchColumn();
    $urbano = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid AND tipo_territorio='Comuna'")->fetchColumn();
    $rural = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid AND tipo_territorio IN ('Corregimiento','Rural','Vereda')")->fetchColumn();
    $total = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid")->fetchColumn();
    $kpis = [
        'comunas' => $comunas, 'barrios' => $barrios,
        'urbano_pct' => $total > 0 ? round($urbano / $total * 100) : 0,
        'rural_pct' => $total > 0 ? round($rural / $total * 100) : 0,
    ];

    // Paletas por capa
    $palettes = [
        'viven'    => ['#1d4ed8','#3b82f6','#2563eb','#60a5fa','#93c5fd','#bfdbfe'],
        'zonas'    => ['#16a34a','#22c55e','#ca8a04','#eab308','#dc2626','#ef4444'],
        'actividad'=> ['#7c3aed','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe'],
        'compromisos'=> ['#7c3aed','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe'],
        'eventos'  => ['#ea580c','#f97316','#fb923c','#fdba74','#fed7aa'],
        'instagram'=> ['#db2777','#ec4899','#f472b6','#f9a8d4','#fbcfe8'],
    ];

    $mapas = [];

    // —— Dónde viven (colaboradores por barrio) ——
    $stmt = $db->query("SELECT c.municipio, c.barrio, COUNT(*) AS total FROM colaboradores c WHERE c.campana_id=$cid AND c.municipio IS NOT NULL AND c.municipio!='' GROUP BY c.municipio, c.barrio ORDER BY total DESC LIMIT 200");
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $geoStmt = $db->query("SELECT id, municipio, barrio, Territorio, Tipo_territorio, ST_AsGeoJSON(geometria) AS geometry_json FROM territorios WHERE geometria IS NOT NULL");
    $territorioPorKey = [];
    $territorioPorMuni = [];
    foreach ($geoStmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
        $key = strtolower(trim($t['municipio'] ?? '')) . '|' . strtolower(trim($t['barrio'] ?? ''));
        $territorioPorKey[$key] = $t;
        $mk = strtolower(trim($t['municipio'] ?? ''));
        if (!isset($territorioPorMuni[$mk])) $territorioPorMuni[$mk] = $t;
    }

    $featuresViven = [];
    foreach ($groups as $g) {
        $n = (int)$g['total'];
        $key = strtolower(trim($g['municipio'])) . '|' . strtolower(trim($g['barrio'] ?? ''));
        $match = $territorioPorKey[$key] ?? $territorioPorMuni[strtolower(trim($g['municipio']))] ?? null;
        if (!$match) continue;
        $geometry = json_decode($match['geometry_json'], true);
        if (!$geometry) continue;
        if ($n >= 5) { $color='#1d4ed8'; $fillColor='#3b82f6'; $nivel='Alta'; }
        elseif ($n >= 3) { $color='#2563eb'; $fillColor='#60a5fa'; $nivel='Media'; }
        else { $color='#93c5fd'; $fillColor='#bfdbfe'; $nivel='Baja'; }
        $featuresViven[] = [
            'type'=>'Feature', 'geometry'=>$geometry,
            'properties'=>[
                'barrio'=>$g['barrio']??'', 'Territorio'=>$match['Territorio']??$g['municipio'],
                'municipio'=>$g['municipio'], 'Tipo_territorio'=>$match['Tipo_territorio']??'',
                'n_colaboradores'=>$n, 'color'=>$color, 'fillColor'=>$fillColor, 'nivel'=>$nivel,
                'tooltip'=>"<b>{$match['Territorio']} {$g['barrio']}</b><br>{$n} colaborador(es) — {$nivel}",
            ]
        ];
    }
    $mapas['viven'] = ['type'=>'FeatureCollection', 'features'=>$featuresViven];

    // —— Zonas de trabajo ——
    $stmt = $db->prepare("SELECT z.territorio_id, t.barrio, t.Territorio, t.municipio, t.Tipo_territorio, COUNT(DISTINCT z.colaborador_id) AS n_responsables, ST_AsGeoJSON(t.geometria) AS geometry_json FROM zonas_trabajo_social z JOIN territorios t ON z.territorio_id=t.id JOIN colaboradores c ON z.colaborador_id=c.id WHERE z.activo=1 AND c.campana_id=? AND t.geometria IS NOT NULL GROUP BY z.territorio_id ORDER BY n_responsables DESC");
    $stmt->execute([$cid]);
    $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $featuresZonas = [];
    foreach ($zonas as $z) {
        $geometry = json_decode($z['geometry_json'], true);
        if (!$geometry) continue;
        $n = (int)$z['n_responsables'];
        if ($n>=5) { $color='#16a34a'; $fillColor='#22c55e'; $nivel='Alta'; }
        elseif ($n>=3) { $color='#ca8a04'; $fillColor='#eab308'; $nivel='Media'; }
        else { $color='#dc2626'; $fillColor='#ef4444'; $nivel='Baja'; }
        $featuresZonas[] = [
            'type'=>'Feature','geometry'=>$geometry,
            'properties'=>['territorio_id'=>$z['territorio_id'],'barrio'=>$z['barrio'],'Territorio'=>$z['Territorio'],'municipio'=>$z['municipio'],'Tipo_territorio'=>$z['Tipo_territorio'],'n_responsables'=>$n,'color'=>$color,'fillColor'=>$fillColor,'nivel'=>$nivel,
            'tooltip'=>"<b>{$z['Territorio']} {$z['barrio']}</b><br>{$n} responsable(s) — {$nivel}",
            ]
        ];
    }
    $mapas['zonas'] = ['type'=>'FeatureCollection', 'features'=>$featuresZonas];

    // —— Compromisos ——
    $stmt = $db->prepare("SELECT c.territorio, c.barrio, COUNT(*) AS total, ST_AsGeoJSON(t.geometria) AS geometry_json FROM compromisos c LEFT JOIN territorios t ON LOWER(TRIM(c.municipio))=LOWER(TRIM(t.municipio)) AND LOWER(TRIM(c.barrio))=LOWER(TRIM(t.barrio)) WHERE c.campana_id=? AND t.geometria IS NOT NULL GROUP BY c.barrio ORDER BY total DESC LIMIT 50");
    $stmt->execute([$cid]);
    $mapas['compromisos'] = rowsToGeoJSON($stmt->fetchAll(PDO::FETCH_ASSOC),'total','barrio',$palettes['compromisos']);

    // —— Eventos ——
    $stmt = $db->prepare("SELECT t.Territorio, t.barrio, COUNT(*) AS total, ST_AsGeoJSON(t.geometria) AS geometry_json FROM eventos e JOIN territorios t ON e.territorio_id=t.id WHERE e.campana_id=? AND t.geometria IS NOT NULL GROUP BY e.territorio_id ORDER BY total DESC LIMIT 50");
    $stmt->execute([$cid]);
    $mapas['eventos'] = rowsToGeoJSON($stmt->fetchAll(PDO::FETCH_ASSOC),'total','Territorio',$palettes['eventos']);

    // —— Acciones comunitarias ——
    $stmt = $db->prepare("SELECT c.territorio, c.barrio, COUNT(*) AS total, ST_AsGeoJSON(t.geometria) AS geometry_json FROM acciones_comunitarias c LEFT JOIN territorios t ON LOWER(TRIM(c.municipio))=LOWER(TRIM(t.municipio)) AND LOWER(TRIM(c.barrio))=LOWER(TRIM(t.barrio)) WHERE c.campana_id=? AND t.geometria IS NOT NULL GROUP BY c.barrio ORDER BY total DESC LIMIT 50");
    $stmt->execute([$cid]);
    $mapas['acciones'] = rowsToGeoJSON($stmt->fetchAll(PDO::FETCH_ASSOC),'total','barrio',$palettes['actividad']);

    // —— Instagram (colaboradores con IG por territorio) ——
    $stmt = $db->prepare("SELECT c.territorio, c.barrio, COUNT(*) AS total, ST_AsGeoJSON(t.geometria) AS geometry_json FROM colaboradores c LEFT JOIN territorios t ON LOWER(TRIM(c.municipio))=LOWER(TRIM(t.municipio)) AND LOWER(TRIM(c.barrio))=LOWER(TRIM(t.barrio)) WHERE c.campana_id=? AND t.geometria IS NOT NULL AND c.instagram_username IS NOT NULL AND c.instagram_username!='' GROUP BY c.barrio ORDER BY total DESC LIMIT 50");
    $stmt->execute([$cid]);
    $mapas['instagram'] = rowsToGeoJSON($stmt->fetchAll(PDO::FETCH_ASSOC),'total','barrio',$palettes['instagram']);

    // —— Brechas de cobertura ——
    $stmt = $db->query("SELECT c.territorio, c.tipo_territorio, COUNT(*) AS colaboradores, SUM(CASE WHEN c.perfil LIKE '%Lider%' OR c.perfil LIKE '%Líder%' THEN 1 ELSE 0 END) AS lideres FROM colaboradores c WHERE c.campana_id=$cid AND c.territorio IS NOT NULL AND c.territorio!='' GROUP BY c.territorio ORDER BY colaboradores DESC LIMIT 20");
    $territorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $brechas = [];
    foreach ($territorios as $t) {
        $conZona = (int)$db->query("SELECT COUNT(DISTINCT z.colaborador_id) FROM zonas_trabajo_social z JOIN colaboradores c ON z.colaborador_id=c.id WHERE c.campana_id=$cid AND c.territorio='{$db->quote($t['territorio'])}' AND z.activo=1")->fetchColumn();
        $lideres = (int)$t['lideres'];
        $brechas[] = [
            'territorio' => $t['territorio'],
            'tipo' => $t['tipo_territorio'],
            'colaboradores' => (int)$t['colaboradores'],
            'lideres' => $lideres,
            'con_zona' => $conZona,
            'sin_zona' => max(0, $lideres - $conZona),
            'cobertura_pct' => $lideres > 0 ? round($conZona / $lideres * 100) : 0,
        ];
    }

    jsonResponse(['success'=>true, 'data'=>compact('kpis','mapas','brechas')]);
}

// ──────────────────────────────────────────────
// RED SOCIAL — Jerarquía, líderes, distribución
// ──────────────────────────────────────────────
function handleRedSocial(PDO $db, array $f): void
{
    $cid = $f['campana_id'];

    // Líderes = personas que aparecen como lider_directo de al menos 1 colaborador
    $lideres = (int)$db->query("SELECT COUNT(*) FROM (SELECT lider_directo FROM colaboradores WHERE campana_id=$cid AND lider_directo IS NOT NULL AND lider_directo!='' GROUP BY lider_directo) AS sub")->fetchColumn();

    // Seguidores = colaboradores con lider_directo asignado
    $totalSeguidores = (int)$db->query("SELECT COUNT(*) FROM colaboradores WHERE campana_id=$cid AND lider_directo IS NOT NULL AND lider_directo!=''")->fetchColumn();

    $kpis = [
        'lideres' => $lideres,
        'seguidores_totales' => $totalSeguidores,
        'seguidores_por_lider' => $lideres > 0 ? round($totalSeguidores / $lideres, 1) : 0,
        'profundidad_max' => 0,
    ];

    // Distribución de líderes por rango de seguidores
    $stmt = $db->query("
        SELECT CASE
            WHEN seg_count=0 THEN '0 seguidores'
            WHEN seg_count BETWEEN 1 AND 4 THEN '1-4'
            WHEN seg_count BETWEEN 5 AND 9 THEN '5-9'
            WHEN seg_count BETWEEN 10 AND 19 THEN '10-19'
            ELSE '20+'
        END AS rango, COUNT(*) AS total, MIN(seg_count) AS orden
        FROM (
            SELECT l.documento, COUNT(c.id) AS seg_count
            FROM colaboradores l
            JOIN colaboradores c ON l.documento=c.lider_directo AND c.campana_id=$cid
            WHERE l.campana_id=$cid
            GROUP BY l.documento
        ) sub
        GROUP BY rango ORDER BY orden
    ");
    $distribucion = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Top 15 padrinos = líderes con más seguidores
    $stmt = $db->query("
        SELECT l.id, l.documento, l.nombres, l.apellidos, l.municipio, l.territorio, l.telefono,
               COUNT(c.id) AS seguidores,
               SUM(CASE WHEN c.estado IS NULL OR c.estado NOT IN ('inactivo','Inactivo') THEN 1 ELSE 0 END) AS activos,
               ROUND(SUM(CASE WHEN c.nivel_participacion IS NOT NULL AND c.nivel_participacion>0 THEN c.nivel_participacion ELSE 0 END) / GREATEST(COUNT(c.id),1), 1) AS participacion_promedio
        FROM colaboradores l
        JOIN colaboradores c ON l.documento=c.lider_directo AND c.campana_id=$cid
        WHERE l.campana_id=$cid
        GROUP BY l.id
        ORDER BY seguidores DESC LIMIT 15
    ");
    $topLideres = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    jsonResponse(['success'=>true, 'data'=>compact('kpis','distribucion','topLideres')]);
}
