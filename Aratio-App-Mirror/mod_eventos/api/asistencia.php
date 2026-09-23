<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/_security.php';

header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            handlePost($db);
            break;
        case 'PUT':
            handlePut($db);
            break;
        case 'DELETE':
            handleDelete($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Método no soportado'], 405);
    }
} catch (Exception $e) {
    error_log("API asistencia error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
}

function handleGet($db) {
    $eventoId = $_GET['evento_id'] ?? null;

    if (!$eventoId) {
        jsonResponse(['success' => false, 'message' => 'ID de evento requerido'], 400);
    }

    $campanaId = eventos_campana_de_evento($db, (int)$eventoId);
    if ($campanaId === null) {
        jsonResponse(['success' => false, 'message' => 'Evento no encontrado'], 404);
    }
    eventos_require_admin($campanaId);

    $stmt = $db->prepare("
        SELECT
            id, nombre, documento, tipo_documento, telefono, email,
            fecha_nacimiento, genero, grupo_etareo,
            departamento, municipio, tipo_territorio, territorio, barrio, territorio_id,
            areas_interes, habeas_data, acepta_comunicaciones, autorizacion_imagenes,
            firma_digital,
            notas, fecha_registro, metodo_registro, asistio,
            created_at
        FROM asistencia_eventos
        WHERE evento_id = ?
        ORDER BY fecha_registro DESC
    ");
    $stmt->execute([$eventoId]);
    $asistentes = $stmt->fetchAll();

    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN genero = 'masculino' THEN 1 ELSE 0 END) as masculino,
            SUM(CASE WHEN genero = 'femenino' THEN 1 ELSE 0 END) as femenino,
            AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio,
            SUM(CASE WHEN metodo_registro = 'qr' THEN 1 ELSE 0 END) as via_qr,
            SUM(CASE WHEN metodo_registro = 'manual' THEN 1 ELSE 0 END) as manual
        FROM asistencia_eventos
        WHERE evento_id = ?
    ");
    $stmt->execute([$eventoId]);
    $stats = $stmt->fetch();

    jsonResponse([
        'success' => true,
        'data' => $asistentes,
        'stats' => $stats
    ]);
}

function handlePost($db) {
    // Endpoint público (QR) — sin auth, protegido con rate-limit + validación de firma.
    $ip = eventos_client_ip();
    $rate = eventos_rate_limit('asistencia_post:' . $ip, 20, 300);
    if (!$rate['allowed']) {
        jsonResponse(['success' => false, 'message' => 'Demasiados intentos. Intente de nuevo en unos minutos'], 429);
    }

    $data = json_decode(file_get_contents('php://input'), true) ?: [];

    $required = ['evento_id', 'nombre', 'documento', 'genero', 'departamento', 'municipio'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            jsonResponse(['success' => false, 'message' => "Campo requerido: $field"], 400);
        }
    }

    $eventoId = (int)$data['evento_id'];

    $stmt = $db->prepare("SELECT fecha_inicio, fecha_fin FROM eventos WHERE id = ?");
    $stmt->execute([$eventoId]);
    $evento = $stmt->fetch();

    if (!$evento) {
        jsonResponse(['success' => false, 'message' => 'Evento no encontrado'], 404);
    }

    $fechaCierre = new DateTime($evento['fecha_fin'] ?? $evento['fecha_inicio']);
    $fechaCierre->modify('+24 hours');
    $ahora = new DateTime();

    if ($ahora > $fechaCierre) {
        jsonResponse(['success' => false, 'message' => 'El registro para este evento ha sido cerrado (24 horas después de finalización)'], 403);
    }

    $stmt = $db->prepare("SELECT id FROM asistencia_eventos WHERE evento_id = ? AND documento = ?");
    $stmt->execute([$eventoId, $data['documento']]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Este documento ya está registrado en el evento'], 409);
    }

    $firma = eventos_validate_firma($data['firma'] ?? null);

    $areasInteres = !empty($data['areas_interes']) ? json_encode($data['areas_interes']) : null;

    $stmt = $db->prepare("
        INSERT INTO asistencia_eventos (
            evento_id, nombre, documento, tipo_documento, telefono, email,
            fecha_nacimiento, genero,
            departamento, municipio, tipo_territorio, territorio, barrio, territorio_id,
            areas_interes, firma_digital, notas,
            habeas_data, acepta_comunicaciones, autorizacion_imagenes,
            fecha_registro, metodo_registro, asistio
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            NOW(), ?, TRUE
        )
    ");

    $stmt->execute([
        $eventoId,
        sanitize($data['nombre']),
        sanitize($data['documento']),
        $data['tipo_documento'] ?? 'cc',
        sanitize($data['telefono'] ?? null),
        sanitize($data['email'] ?? null),
        $data['fecha_nacimiento'] ?? null,
        $data['genero'],
        sanitize($data['departamento']),
        sanitize($data['municipio']),
        sanitize($data['tipo_territorio'] ?? null),
        sanitize($data['territorio'] ?? null),
        sanitize($data['barrio'] ?? null),
        $data['territorio_id'] ?? null,
        $areasInteres,
        $firma,
        sanitize($data['observaciones'] ?? $data['notas'] ?? null),
        isset($data['habeas_data']) ? (bool)$data['habeas_data'] : false,
        isset($data['acepta_comunicaciones']) ? (bool)$data['acepta_comunicaciones'] : false,
        isset($data['autorizacion_imagenes']) ? (bool)$data['autorizacion_imagenes'] : false,
        $data['metodo_registro'] ?? 'qr'
    ]);

    $registroId = $db->lastInsertId();

    $stmt = $db->prepare("
        UPDATE eventos
        SET asistentes_confirmados = (
            SELECT COUNT(*) FROM asistencia_eventos WHERE evento_id = ?
        )
        WHERE id = ?
    ");
    $stmt->execute([$eventoId, $eventoId]);

    jsonResponse([
        'success' => true,
        'message' => 'Asistencia registrada exitosamente',
        'data' => ['id' => $registroId]
    ], 201);
}

function handlePut($db) {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    eventos_csrf_verify();

    $id = $data['id'] ?? null;
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    $stmt = $db->prepare("SELECT evento_id FROM asistencia_eventos WHERE id = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        jsonResponse(['success' => false, 'message' => 'Registro no encontrado'], 404);
    }

    $campanaId = eventos_campana_de_evento($db, (int)$registro['evento_id']);
    eventos_require_admin($campanaId);

    $updates = [];
    $params = [];

    if (isset($data['asistio'])) {
        $updates[] = "asistio = ?";
        $params[] = (bool)$data['asistio'];
    }

    // La columna real es `notas`; se acepta `observaciones` como alias por compatibilidad.
    if (isset($data['notas']) || isset($data['observaciones'])) {
        $updates[] = "notas = ?";
        $params[] = sanitize($data['notas'] ?? $data['observaciones']);
    }

    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'No hay campos para actualizar'], 400);
    }

    $params[] = $id;

    $sql = "UPDATE asistencia_eventos SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    jsonResponse([
        'success' => true,
        'message' => 'Registro actualizado exitosamente'
    ]);
}

function handleDelete($db) {
    $id = $_GET['id'] ?? null;
    eventos_csrf_verify();

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    $stmt = $db->prepare("SELECT evento_id FROM asistencia_eventos WHERE id = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch();

    if (!$registro) {
        jsonResponse(['success' => false, 'message' => 'Registro no encontrado'], 404);
    }

    $campanaId = eventos_campana_de_evento($db, (int)$registro['evento_id']);
    eventos_require_admin($campanaId);

    $stmt = $db->prepare("DELETE FROM asistencia_eventos WHERE id = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("
        UPDATE eventos
        SET asistentes_confirmados = (
            SELECT COUNT(*) FROM asistencia_eventos WHERE evento_id = ?
        )
        WHERE id = ?
    ");
    $stmt->execute([$registro['evento_id'], $registro['evento_id']]);

    jsonResponse([
        'success' => true,
        'message' => 'Registro eliminado exitosamente'
    ]);
}
