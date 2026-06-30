<?php
/**
 * API: Compromisos
 * CRUD completo para gestión de compromisos (Metodología 5 preguntas)
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
    error_log("API compromisos error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
}

/**
 * GET: Listar compromisos de una campaña
 */
function handleGet($db, $userId) {
    $campanaId = $_GET['campana_id'] ?? null;
    $compromisoId = $_GET['id'] ?? null;

    if ($compromisoId) {
        // Obtener compromiso específico
        $stmt = $db->prepare("
            SELECT c.*, u.nombre as usuario_nombre
            FROM compromisos c
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$compromisoId]);
        $compromiso = $stmt->fetch();

        if ($compromiso) {
            jsonResponse(['success' => true, 'data' => $compromiso]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Compromiso no encontrado'], 404);
        }
    } elseif ($campanaId) {
        // Listar compromisos de la campaña
        $stmt = $db->prepare("
            SELECT c.*, u.nombre as usuario_nombre
            FROM compromisos c
            LEFT JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.campana_id = ?
            ORDER BY c.fecha_compromiso DESC
        ");
        $stmt->execute([$campanaId]);
        $compromisos = $stmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $compromisos]);
    } else {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }
}

/**
 * POST: Crear nuevo compromiso
 */
function handlePost($db, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validaciones - Metodología 5 preguntas
    $required = [
        // QUÉ
        'campana_id', 'tipo', 'titulo', 'descripcion',
        // QUIÉN
        'lider_nombre', 'lider_telefono',
        // CUÁNDO
        'fecha_compromiso',
        // DÓNDE
        'departamento', 'municipio'
        // CÓMO (opcional: metodologia, presupuesto_estimado, beneficiarios_estimados)
    ];

    foreach ($required as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            jsonResponse(['success' => false, 'message' => "Campo requerido: $field"], 400);
        }
    }

    // Insertar compromiso
    $stmt = $db->prepare("
        INSERT INTO compromisos (
            campana_id, usuario_id, tipo, titulo, descripcion,
            lider_nombre, lider_telefono, lider_email, lider_cargo,
            fecha_compromiso, fecha_cumplimiento_estimada,
            departamento, municipio, tipo_territorio, territorio, barrio,
            latitud, longitud,
            metodologia, presupuesto_estimado, beneficiarios_estimados,
            prioridad, estado, avance_porcentaje, observaciones
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $data['campana_id'],
        $userId,
        $data['tipo'],
        sanitize($data['titulo']),
        sanitize($data['descripcion']),
        sanitize($data['lider_nombre']),
        sanitize($data['lider_telefono']),
        sanitize($data['lider_email'] ?? null),
        sanitize($data['lider_cargo'] ?? null),
        $data['fecha_compromiso'],
        $data['fecha_cumplimiento_estimada'] ?? null,
        sanitize($data['departamento']),
        sanitize($data['municipio']),
        sanitize($data['tipo_territorio'] ?? null),
        sanitize($data['territorio'] ?? null),
        sanitize($data['barrio'] ?? null),
        $data['latitud'] ?? null,
        $data['longitud'] ?? null,
        sanitize($data['metodologia'] ?? null),
        $data['presupuesto_estimado'] ?? null,
        $data['beneficiarios_estimados'] ?? null,
        $data['prioridad'] ?? 'media',
        $data['estado'] ?? 'pendiente',
        $data['avance_porcentaje'] ?? 0,
        sanitize($data['observaciones'] ?? null)
    ]);

    $compromisoId = $db->lastInsertId();

    if (class_exists('ActivityLogger') && !empty($data['colaborador_id'])) {
        ActivityLogger::log(
            (int)$data['colaborador_id'],
            'compromiso_creado',
            "Compromiso: {$data['titulo']}",
            ['compromiso_id' => $compromisoId, 'titulo' => $data['titulo'], 'tipo' => $data['tipo']],
            'compromisos',
            $compromisoId,
            $userId
        );
    }

    jsonResponse([
        'success' => true,
        'message' => 'Compromiso registrado exitosamente',
        'data' => ['id' => $compromisoId]
    ], 201);
}

/**
 * PUT: Actualizar compromiso
 */
function handlePut($db, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    // Verificar que el compromiso existe
    $stmt = $db->prepare("SELECT id FROM compromisos WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Compromiso no encontrado'], 404);
    }

    // Actualizar
    $stmt = $db->prepare("
        UPDATE compromisos SET
            tipo = ?,
            titulo = ?,
            descripcion = ?,
            lider_nombre = ?,
            lider_telefono = ?,
            lider_email = ?,
            lider_cargo = ?,
            fecha_compromiso = ?,
            fecha_cumplimiento_estimada = ?,
            fecha_cumplimiento_real = ?,
            departamento = ?,
            municipio = ?,
            tipo_territorio = ?,
            territorio = ?,
            barrio = ?,
            latitud = ?,
            longitud = ?,
            metodologia = ?,
            presupuesto_estimado = ?,
            beneficiarios_estimados = ?,
            prioridad = ?,
            estado = ?,
            avance_porcentaje = ?,
            observaciones = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $data['tipo'],
        sanitize($data['titulo']),
        sanitize($data['descripcion']),
        sanitize($data['lider_nombre']),
        sanitize($data['lider_telefono']),
        sanitize($data['lider_email'] ?? null),
        sanitize($data['lider_cargo'] ?? null),
        $data['fecha_compromiso'],
        $data['fecha_cumplimiento_estimada'] ?? null,
        $data['fecha_cumplimiento_real'] ?? null,
        sanitize($data['departamento']),
        sanitize($data['municipio']),
        sanitize($data['tipo_territorio'] ?? null),
        sanitize($data['territorio'] ?? null),
        sanitize($data['barrio'] ?? null),
        $data['latitud'] ?? null,
        $data['longitud'] ?? null,
        sanitize($data['metodologia'] ?? null),
        $data['presupuesto_estimado'] ?? null,
        $data['beneficiarios_estimados'] ?? null,
        $data['prioridad'] ?? 'media',
        $data['estado'] ?? 'pendiente',
        $data['avance_porcentaje'] ?? 0,
        sanitize($data['observaciones'] ?? null),
        $id
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Compromiso actualizado exitosamente'
    ]);
}

/**
 * DELETE: Eliminar compromiso
 */
function handleDelete($db, $userId) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    // Verificar que existe
    $stmt = $db->prepare("SELECT id FROM compromisos WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Compromiso no encontrado'], 404);
    }

    // Eliminar
    $stmt = $db->prepare("DELETE FROM compromisos WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse([
        'success' => true,
        'message' => 'Compromiso eliminado exitosamente'
    ]);
}
