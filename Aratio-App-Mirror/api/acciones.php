<?php
/**
 * API: Acciones Comunitarias
 * CRUD completo para gestión de acciones comunitarias
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'];

try {
    switch ($method) {
        case 'GET':
            handleGet($db, $userId);
            break;

        case 'POST':
            handlePost($db, $userId);
            break;

        case 'PUT':
            handlePut($db, $userId);
            break;

        case 'DELETE':
            handleDelete($db, $userId);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Método no soportado'], 405);
    }
} catch (Exception $e) {
    error_log("API acciones error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
}

/**
 * GET: Listar acciones de una campaña
 */
function handleGet($db, $userId) {
    $campanaId = $_GET['campana_id'] ?? null;
    $accionId = $_GET['id'] ?? null;

    if ($accionId) {
        // Obtener acción específica
        $stmt = $db->prepare("
            SELECT a.*, u.nombre as usuario_nombre
            FROM acciones_comunitarias a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$accionId]);
        $accion = $stmt->fetch();

        if ($accion) {
            jsonResponse(['success' => true, 'data' => $accion]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Acción no encontrada'], 404);
        }
    } elseif ($campanaId) {
        // Listar acciones de la campaña
        $stmt = $db->prepare("
            SELECT a.*, u.nombre as usuario_nombre
            FROM acciones_comunitarias a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.campana_id = ?
            ORDER BY a.fecha_accion DESC
        ");
        $stmt->execute([$campanaId]);
        $acciones = $stmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $acciones]);
    } else {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }
}

/**
 * POST: Crear nueva acción
 */
function handlePost($db, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validaciones
    $required = ['campana_id', 'tipo', 'descripcion', 'fecha_accion', 'departamento', 'municipio'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            jsonResponse(['success' => false, 'message' => "Campo requerido: $field"], 400);
        }
    }

    // Insertar acción
    $stmt = $db->prepare("
        INSERT INTO acciones_comunitarias (
            campana_id, usuario_id, tipo, descripcion, fecha_accion,
            departamento, municipio, tipo_territorio, territorio, barrio,
            latitud, longitud, personas_contactadas, compromisos_obtenidos, observaciones
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $data['campana_id'],
        $userId,
        $data['tipo'],
        sanitize($data['descripcion']),
        $data['fecha_accion'],
        sanitize($data['departamento']),
        sanitize($data['municipio']),
        sanitize($data['tipo_territorio'] ?? null),
        sanitize($data['territorio'] ?? null),
        sanitize($data['barrio'] ?? null),
        $data['latitud'] ?? null,
        $data['longitud'] ?? null,
        $data['personas_contactadas'] ?? 0,
        $data['compromisos_obtenidos'] ?? 0,
        sanitize($data['observaciones'] ?? null)
    ]);

    $accionId = $db->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Acción comunitaria registrada exitosamente',
        'data' => ['id' => $accionId]
    ], 201);
}

/**
 * PUT: Actualizar acción
 */
function handlePut($db, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    // Verificar que la acción existe
    $stmt = $db->prepare("SELECT id FROM acciones_comunitarias WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Acción no encontrada'], 404);
    }

    // Actualizar
    $stmt = $db->prepare("
        UPDATE acciones_comunitarias SET
            tipo = ?,
            descripcion = ?,
            fecha_accion = ?,
            departamento = ?,
            municipio = ?,
            tipo_territorio = ?,
            territorio = ?,
            barrio = ?,
            latitud = ?,
            longitud = ?,
            personas_contactadas = ?,
            compromisos_obtenidos = ?,
            observaciones = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $data['tipo'],
        sanitize($data['descripcion']),
        $data['fecha_accion'],
        sanitize($data['departamento']),
        sanitize($data['municipio']),
        sanitize($data['tipo_territorio'] ?? null),
        sanitize($data['territorio'] ?? null),
        sanitize($data['barrio'] ?? null),
        $data['latitud'] ?? null,
        $data['longitud'] ?? null,
        $data['personas_contactadas'] ?? 0,
        $data['compromisos_obtenidos'] ?? 0,
        sanitize($data['observaciones'] ?? null),
        $id
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Acción actualizada exitosamente'
    ]);
}

/**
 * DELETE: Eliminar acción
 */
function handleDelete($db, $userId) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    // Verificar que existe
    $stmt = $db->prepare("SELECT id FROM acciones_comunitarias WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Acción no encontrada'], 404);
    }

    // Eliminar
    $stmt = $db->prepare("DELETE FROM acciones_comunitarias WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse([
        'success' => true,
        'message' => 'Acción eliminada exitosamente'
    ]);
}
