<?php
/**
 * API: Asistencia a Eventos
 * Gestión de registros de asistencia vía QR
 * ENDPOINT PÚBLICO - No requiere autenticación
 */

require_once __DIR__ . '/../config/config.php';

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
    error_log("API asistencia_eventos error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
}

/**
 * GET: Obtener asistentes de un evento
 * Requiere: ?evento_id=X
 */
function handleGet($db) {
    $eventoId = $_GET['evento_id'] ?? null;

    if (!$eventoId) {
        jsonResponse(['success' => false, 'message' => 'ID de evento requerido'], 400);
    }

    $stmt = $db->prepare("
        SELECT
            id, nombre, documento, tipo_documento, telefono, email,
            fecha_nacimiento, genero, grupo_etareo,
            departamento, municipio, tipo_territorio, territorio, barrio, territorio_id,
            areas_interes, habeas_data, acepta_comunicaciones,
            notas, fecha_registro, metodo_registro, asistio,
            created_at
        FROM asistencia_eventos
        WHERE evento_id = ?
        ORDER BY fecha_registro DESC
    ");
    $stmt->execute([$eventoId]);
    $asistentes = $stmt->fetchAll();

    // Estadísticas
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

/**
 * POST: Registrar asistencia
 * PÚBLICO - No requiere autenticación
 */
function handlePost($db) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validaciones
    $required = ['evento_id', 'nombre', 'documento', 'genero', 'departamento', 'municipio'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            jsonResponse(['success' => false, 'message' => "Campo requerido: $field"], 400);
        }
    }

    $eventoId = $data['evento_id'];

    // Verificar que el evento existe y no está cerrado
    $stmt = $db->prepare("SELECT fecha_inicio, fecha_fin FROM eventos WHERE id = ?");
    $stmt->execute([$eventoId]);
    $evento = $stmt->fetch();

    if (!$evento) {
        jsonResponse(['success' => false, 'message' => 'Evento no encontrado'], 404);
    }

    // Verificar cierre (fecha_fin + 24 horas)
    $fechaCierre = new DateTime($evento['fecha_fin'] ?? $evento['fecha_inicio']);
    $fechaCierre->modify('+24 hours');
    $ahora = new DateTime();

    if ($ahora > $fechaCierre) {
        jsonResponse(['success' => false, 'message' => 'El registro para este evento ha sido cerrado (24 horas después de finalización)'], 403);
    }

    // Verificar duplicado (mismo documento en mismo evento)
    $stmt = $db->prepare("SELECT id FROM asistencia_eventos WHERE evento_id = ? AND documento = ?");
    $stmt->execute([$eventoId, $data['documento']]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Este documento ya está registrado en el evento'], 409);
    }

    // Preparar datos
    $edad = null;
    if (!empty($data['fecha_nacimiento'])) {
        $nacimiento = new DateTime($data['fecha_nacimiento']);
        $edad = $ahora->diff($nacimiento)->y;
    } elseif (!empty($data['edad'])) {
        $edad = (int)$data['edad'];
    }

    // Obtener IP y User Agent
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Preparar areas_interes como JSON
    $areasInteres = !empty($data['areas_interes']) ? json_encode($data['areas_interes']) : null;

    // Insertar registro
    $stmt = $db->prepare("
        INSERT INTO asistencia_eventos (
            evento_id, nombre, documento, tipo_documento, telefono, email,
            fecha_nacimiento, genero,
            departamento, municipio, tipo_territorio, territorio, barrio, territorio_id,
            areas_interes, firma_digital, notas,
            habeas_data, acepta_comunicaciones,
            fecha_registro, metodo_registro, asistio
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?,
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
        $data['firma'] ?? null,
        sanitize($data['observaciones'] ?? null),
        isset($data['habeas_data']) ? (bool)$data['habeas_data'] : false,
        isset($data['acepta_comunicaciones']) ? (bool)$data['acepta_comunicaciones'] : false,
        $data['metodo_registro'] ?? 'qr'
    ]);

    $registroId = $db->lastInsertId();

    // Actualizar contador de asistentes confirmados en evento
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

/**
 * PUT: Actualizar registro de asistencia (admin)
 * Requiere autenticación
 */
function handlePut($db) {
    // Verificar autenticación
    session_start();
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    // Campos actualizables por admin
    $updates = [];
    $params = [];

    if (isset($data['asistio'])) {
        $updates[] = "asistio = ?";
        $params[] = (bool)$data['asistio'];
    }

    if (isset($data['observaciones'])) {
        $updates[] = "observaciones = ?";
        $params[] = sanitize($data['observaciones']);
    }

    if (isset($data['sentimiento_analizado'])) {
        $updates[] = "sentimiento_analizado = ?";
        $params[] = $data['sentimiento_analizado'];
    }

    if (isset($data['sentimiento_score'])) {
        $updates[] = "sentimiento_score = ?";
        $params[] = (float)$data['sentimiento_score'];
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

/**
 * DELETE: Eliminar registro de asistencia (admin)
 * Requiere autenticación
 */
function handleDelete($db) {
    // Verificar autenticación
    session_start();
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'No autorizado'], 401);
    }

    $id = $_GET['id'] ?? null;

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    // Obtener evento_id antes de eliminar
    $stmt = $db->prepare("SELECT evento_id FROM asistencia_eventos WHERE id = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch();

    if (!$registro) {
        jsonResponse(['success' => false, 'message' => 'Registro no encontrado'], 404);
    }

    // Eliminar registro
    $stmt = $db->prepare("DELETE FROM asistencia_eventos WHERE id = ?");
    $stmt->execute([$id]);

    // Actualizar contador de asistentes
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
