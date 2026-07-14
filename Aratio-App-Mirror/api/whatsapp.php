<?php

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

requireAuth();

$db = getDB();
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'cumpleanos':
            handleCumpleanos($db);
            break;
        case 'historial':
            handleHistorial($db);
            break;
        case 'reenviar':
            handleReenviar($db);
            break;
        case 'stats':
            handleStats($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    error_log("WhatsApp API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno'], 500);
}

function handleCumpleanos(PDO $db): void
{
    $rango = $_GET['rango'] ?? 'hoy';
    $desde = $_GET['desde'] ?? null;
    $hasta = $_GET['hasta'] ?? null;

    switch ($rango) {
        case 'hoy':
            $condicion = "DATE_FORMAT(c.fecha_nacimiento, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d')";
            break;
        case 'semana':
            $condicion = "
                DATE_FORMAT(c.fecha_nacimiento, '%m-%d') BETWEEN 
                DATE_FORMAT(NOW(), '%m-%d') AND 
                DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 7 DAY), '%m-%d')
            ";
            break;
        case 'mes':
            $condicion = "DATE_FORMAT(c.fecha_nacimiento, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d')";
            $condicion .= " AND DATE_FORMAT(c.fecha_nacimiento, '%m-%d') <= DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 30 DAY), '%m-%d')";
            break;
        case 'personalizado':
            if (!$desde || !$hasta) {
                jsonResponse(['success' => false, 'message' => 'Se requieren desde y hasta'], 400);
            }
            $condicion = $db->quote($desde) . " <= DATE_FORMAT(c.fecha_nacimiento, '%m-%d')";
            $condicion .= " AND DATE_FORMAT(c.fecha_nacimiento, '%m-%d') <= " . $db->quote($hasta);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Rango no válido'], 400);
    }

    $sql = "
        SELECT c.id, c.nombres, c.apellidos, c.fecha_nacimiento, c.perfil, 
               c.telefono, c.telefono_whatsapp, c.departamento, c.municipio,
               TIMESTAMPDIFF(YEAR, c.fecha_nacimiento, CURDATE()) as edad,
               DATEDIFF(
                   CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(c.fecha_nacimiento, '%m-%d')),
                   CURDATE()
               ) as dias_faltantes,
               DATE_FORMAT(c.fecha_nacimiento, '%d de %M') as fecha_exacta,
               wl.estado as ultimo_estado, wl.sent_at as ultimo_envio, wl.id as log_id
        FROM colaboradores c
        LEFT JOIN (
            SELECT colaborador_id, estado, sent_at, id
            FROM whatsapp_log
            WHERE id IN (
                SELECT MAX(id) FROM whatsapp_log GROUP BY colaborador_id
            )
        ) wl ON wl.colaborador_id = c.id
        WHERE {$condicion}
          AND (c.estado IS NULL OR c.estado NOT IN ('inactivo','Inactivo'))
        ORDER BY dias_faltantes ASC, DATE_FORMAT(c.fecha_nacimiento, '%m-%d')
    ";

    $stmt = $db->query($sql);
    $cumpleaneros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['success' => true, 'data' => $cumpleaneros]);
}

function handleHistorial(PDO $db): void
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $estado = $_GET['estado'] ?? '';
    $perfil = $_GET['perfil'] ?? '';
    $desde = $_GET['desde'] ?? '';
    $hasta = $_GET['hasta'] ?? '';
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];

    if ($estado) {
        $where[] = "wl.estado = ?";
        $params[] = $estado;
    }
    if ($perfil) {
        $where[] = "wl.perfil LIKE ?";
        $params[] = '%' . $perfil . '%';
    }
    if ($desde) {
        $where[] = "wl.sent_at >= ?";
        $params[] = $desde . ' 00:00:00';
    }
    if ($hasta) {
        $where[] = "wl.sent_at <= ?";
        $params[] = $hasta . ' 23:59:59';
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countSql = "SELECT COUNT(*) FROM whatsapp_log wl {$whereSQL}";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $sql = "
        SELECT wl.*, c.documento
        FROM whatsapp_log wl
        LEFT JOIN colaboradores c ON wl.colaborador_id = c.id
        {$whereSQL}
        ORDER BY wl.sent_at DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        'success' => true,
        'data' => $rows,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
}

function handleReenviar(PDO $db): void
{
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    $stmt = $db->prepare("
        SELECT c.* FROM colaboradores c WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $colaborador = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$colaborador) {
        jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
    }

    $telefono = $colaborador['telefono_whatsapp'] ?: $colaborador['telefono'];
    if (empty($telefono)) {
        jsonResponse(['success' => false, 'message' => 'Sin teléfono disponible'], 400);
    }

    $api = new WhatsAppApi();
    $template = $api->getTemplate($colaborador['perfil']);
    $mensaje = $api->fillTemplate($template, $colaborador['nombres']);
    $telefonoFormateado = $api->formatPhone($telefono);

    $resultado = $api->send($telefonoFormateado, $mensaje);

    $templateName = 'birthday_' . strtolower(str_replace(' ', '_', trim($colaborador['perfil'] ?? 'default')));
    $estado = $resultado['success'] ? 'enviado' : 'fallido';
    $api->log($db, $colaborador, $templateName, $mensaje, $estado, $resultado['error'] ?? null);

    if (class_exists('ActivityLogger') && $resultado['success']) {
        ActivityLogger::log(
            $id,
            'cumpleaños',
            "Felicitación de cumpleaños enviada ({$colaborador['perfil']})",
            ['template' => $templateName, 'resultado' => $estado],
            'whatsapp_log',
            null
        );
    }

    jsonResponse([
        'success' => $resultado['success'],
        'message' => $resultado['success'] ? 'Mensaje enviado correctamente' : 'Error: ' . ($resultado['error'] ?? 'Desconocido')
    ]);
}

function handleStats(PDO $db): void
{
    $hoy = date('Y-m-d');

    $stats = [];

    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_envios,
            SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as total_exitosos,
            SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as total_fallidos,
            SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as total_pendientes
        FROM whatsapp_log
        WHERE DATE(sent_at) = CURDATE()
    ");
    $stats['hoy'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_envios,
            SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as total_exitosos,
            SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as total_fallidos
        FROM whatsapp_log
        WHERE MONTH(sent_at) = MONTH(CURDATE()) AND YEAR(sent_at) = YEAR(CURDATE())
    ");
    $stats['mes'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->query("
        SELECT COUNT(*) as total
        FROM colaboradores c
        WHERE DATE_FORMAT(c.fecha_nacimiento, '%m-%d') BETWEEN 
              DATE_FORMAT(CURDATE(), '%m-%d') AND 
              DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d')
          AND (c.estado IS NULL OR c.estado NOT IN ('inactivo','Inactivo'))
    ");
    $stats['proximos_7_dias'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("
        SELECT 
            ROUND(
                (SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) * 100.0) / 
                NULLIF(COUNT(*), 0), 1
            ) as tasa_exito
        FROM whatsapp_log
    ");
    $stats['tasa_exito'] = $stmt->fetchColumn() ?: 0;

    jsonResponse(['success' => true, 'data' => $stats]);
}
