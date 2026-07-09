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
        SELECT DATE_FORMAT(creado_en, '%Y-%m') AS mes, COUNT(*) AS total
        FROM colaboradores
        WHERE campana_id = $campanaId AND creado_en >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
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
