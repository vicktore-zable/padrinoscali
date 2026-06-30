<?php

require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

try {
    switch ($action) {
        case 'conversaciones':
            handleConversaciones($db);
            break;
        case 'conversacion':
            handleConversacion($db);
            break;
        case 'enviar':
            handleEnviar($db, $userId);
            break;
        case 'broadcast':
            handleBroadcast($db, $userId);
            break;
        case 'archivar':
            handleArchivar($db);
            break;
        case 'marcar_leido':
            handleMarcarLeido($db);
            break;
        case 'plantillas':
            handlePlantillas($db);
            break;
        case 'stats':
            handleStats($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("ALAS WhatsApp Messages API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function handleConversaciones(PDO $db): void
{
    $search = $_GET['search'] ?? '';
    $estado = $_GET['estado'] ?? 'activa';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $where = ["wc.estado = ?"];
    $params = [$estado];

    if ($search) {
        $where[] = "(c.nombres LIKE ? OR c.apellidos LIKE ? OR c.documento LIKE ?)";
        $s = "%{$search}%";
        $params = array_merge($params, [$s, $s, $s]);
    }

    $whereSQL = implode(' AND ', $where);

    $countStmt = $db->prepare("
        SELECT COUNT(*) FROM whatsapp_conversaciones wc
        JOIN colaboradores c ON wc.colaborador_id = c.id
        WHERE {$whereSQL}
    ");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT wc.*, 
               c.nombres, c.apellidos, c.documento, c.perfil, c.foto,
               c.telefono, c.telefono_whatsapp,
               c.municipio, c.barrio
        FROM whatsapp_conversaciones wc
        JOIN colaboradores c ON wc.colaborador_id = c.id
        WHERE {$whereSQL}
        ORDER BY wc.unread DESC, wc.ultimo_timestamp DESC
        LIMIT {$limit} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        'success' => true,
        'data' => $conversaciones,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
}

function handleConversacion(PDO $db): void
{
    $id = (int)($_GET['id'] ?? 0);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 50;
    $offset = ($page - 1) * $limit;

    $stmt = $db->prepare("
        SELECT wc.*, c.nombres, c.apellidos, c.documento, c.perfil, c.foto
        FROM whatsapp_conversaciones wc
        JOIN colaboradores c ON wc.colaborador_id = c.id
        WHERE wc.id = ?
    ");
    $stmt->execute([$id]);
    $conversacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conversacion) {
        jsonResponse(['success' => false, 'message' => 'Conversación no encontrada'], 404);
    }

    $totalStmt = $db->prepare("SELECT COUNT(*) FROM whatsapp_mensajes WHERE conversacion_id = ?");
    $totalStmt->execute([$id]);
    $total = (int)$totalStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT * FROM whatsapp_mensajes 
        WHERE conversacion_id = ?
        ORDER BY timestamp DESC
        LIMIT {$limit} OFFSET {$offset}
    ");
    $stmt->execute([$id]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        'success' => true,
        'data' => [
            'conversacion' => $conversacion,
            'mensajes' => array_reverse($mensajes)
        ],
        'total' => $total,
        'page' => $page,
        'totalPages' => ceil($total / $limit)
    ]);
}

function handleEnviar(PDO $db, ?int $userId): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $convId = (int)($input['conversacion_id'] ?? 0);
    $texto = trim($input['texto'] ?? '');

    if (!$convId || !$texto) {
        jsonResponse(['success' => false, 'message' => 'Datos incompletos'], 400);
    }

    $stmt = $db->prepare("
        SELECT wc.*, c.nombres, c.apellidos, c.telefono, c.telefono_whatsapp
        FROM whatsapp_conversaciones wc
        JOIN colaboradores c ON wc.colaborador_id = c.id
        WHERE wc.id = ?
    ");
    $stmt->execute([$convId]);
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conv) {
        jsonResponse(['success' => false, 'message' => 'Conversación no encontrada'], 404);
    }

    $telefono = $conv['telefono_whatsapp'] ?: $conv['telefono'];
    if (empty($telefono)) {
        jsonResponse(['success' => false, 'message' => 'Sin teléfono disponible'], 400);
    }

    $api = new WhatsAppCloudApi();
    $resultado = $api->sendText($telefono, $texto);

    $stmt = $db->prepare("
        INSERT INTO whatsapp_mensajes (conversacion_id, direccion, tipo, contenido)
        VALUES (?, 'enviado', 'texto', ?)
    ");
    $stmt->execute([$convId, $texto]);

    $db->prepare("
        UPDATE whatsapp_conversaciones SET ultimo_mensaje = ?, ultimo_tipo = 'enviado', ultimo_timestamp = NOW()
        WHERE id = ?
    ")->execute([$texto, $convId]);

    if ($conv['colaborador_id'] > 0 && class_exists('ActivityLogger')) {
        ActivityLogger::log(
            (int)$conv['colaborador_id'],
            'whatsapp_enviado',
            "WhatsApp enviado: " . mb_substr($texto, 0, 100),
            ['conversacion_id' => $convId, 'texto' => $texto],
            'whatsapp_mensajes',
            (int)$db->lastInsertId(),
            $userId
        );
    }

    jsonResponse([
        'success' => true,
        'message' => 'Mensaje enviado',
        'wa_result' => $resultado
    ]);
}

function handleBroadcast(PDO $db, ?int $userId): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $plantillaCodigo = $input['plantilla_codigo'] ?? '';
    $filtros = $input['filtros'] ?? [];
    $programadoPara = $input['programado_para'] ?? null;

    if (empty($plantillaCodigo)) {
        jsonResponse(['success' => false, 'message' => 'Plantilla requerida'], 400);
    }

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
        $where[] = "c.perfil LIKE ?";
        $params[] = '%' . $filtros['perfil'] . '%';
    }
    if (!empty($filtros['nivel'])) {
        $where[] = "c.nivel_participacion = ?";
        $params[] = $filtros['nivel'];
    }
    if (!empty($filtros['campana_id'])) {
        $where[] = "c.campana_id = ?";
        $params[] = (int)$filtros['campana_id'];
    }

    $stmt = $db->prepare("
        SELECT id, nombres, apellidos, telefono_whatsapp, telefono 
        FROM colaboradores c 
        WHERE {$where}
    ");
    $stmt->execute($params);
    $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $enqueued = 0;
    foreach ($colaboradores as $colab) {
        if (empty($colab['telefono_whatsapp']) && empty($colab['telefono'])) continue;

        $stmt = $db->prepare("
            INSERT INTO whatsapp_broadcast_queue 
                (plantilla_codigo, colaborador_id, variables_json, estado, programado_para)
            VALUES (?, ?, ?, 'pending', ?)
        ");
        $stmt->execute([
            $plantillaCodigo,
            $colab['id'],
            json_encode(['nombre' => $colab['nombres']], JSON_UNESCAPED_UNICODE),
            $programadoPara
        ]);
        $enqueued++;
    }

    jsonResponse([
        'success' => true,
        'message' => "Broadcast encolado para {$enqueued} colaboradores",
        'total' => $enqueued
    ]);
}

function handleArchivar(PDO $db): void
{
    $id = (int)($_GET['id'] ?? 0);
    $db->prepare("UPDATE whatsapp_conversaciones SET estado = 'archivada' WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true]);
}

function handleMarcarLeido(PDO $db): void
{
    $id = (int)($_GET['id'] ?? 0);
    $db->prepare("UPDATE whatsapp_conversaciones SET unread = 0 WHERE id = ?")->execute([$id]);
    $db->prepare("UPDATE whatsapp_mensajes SET leido = TRUE WHERE conversacion_id = ? AND direccion = 'recibido'")->execute([$id]);
    jsonResponse(['success' => true]);
}

function handlePlantillas(PDO $db): void
{
    $stmt = $db->query("SELECT * FROM whatsapp_plantillas ORDER BY nombre");
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function handleStats(PDO $db): void
{
    $activas = (int)$db->query("SELECT COUNT(*) FROM whatsapp_conversaciones WHERE estado = 'activa'")->fetchColumn();
    $noLeidos = (int)$db->query("SELECT SUM(unread) FROM whatsapp_conversaciones WHERE estado = 'activa'")->fetchColumn();
    $hoy = (int)$db->query("
        SELECT COUNT(*) FROM whatsapp_mensajes 
        WHERE DATE(timestamp) = CURDATE() AND direccion = 'recibido'
    ")->fetchColumn();
    $enviadosHoy = (int)$db->query("
        SELECT COUNT(*) FROM whatsapp_mensajes 
        WHERE DATE(timestamp) = CURDATE() AND direccion = 'enviado'
    ")->fetchColumn();
    $pendientes = (int)$db->query("
        SELECT COUNT(*) FROM whatsapp_broadcast_queue WHERE estado = 'pending'
    ")->fetchColumn();

    jsonResponse([
        'success' => true,
        'data' => [
            'conversaciones_activas' => $activas,
            'mensajes_no_leidos' => $noLeidos,
            'mensajes_recibidos_hoy' => $hoy,
            'mensajes_enviados_hoy' => $enviadosHoy,
            'broadcast_pendientes' => $pendientes
        ]
    ]);
}
