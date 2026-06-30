<?php
/**
 * API: Eventos
 * Endpoints para CRUD de eventos
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user = getSessionUser();

header('Content-Type: application/json; charset=utf-8');

try {
    switch ($method) {
        case 'GET':
            // Obtener un evento específico o listar por campaña
            $id = $_GET['id'] ?? null;
            $campanaId = $_GET['campana_id'] ?? null;

            if ($id) {
                $stmt = $db->prepare("SELECT * FROM eventos WHERE id = ?");
                $stmt->execute([$id]);
                $evento = $stmt->fetch();

                if ($evento) {
                    // Verificar acceso a la campaña del evento
                    $auth = new Auth();
                    if ($auth->hasAccessToCampana($user['id'], $evento['campana_id'])) {
                        jsonResponse(['success' => true, 'data' => $evento]);
                    } else {
                        jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
                    }
                } else {
                    jsonResponse(['success' => false, 'message' => 'Evento no encontrado'], 404);
                }
            } elseif ($campanaId) {
                // Verificar acceso
                $auth = new Auth();
                if (!$auth->hasAccessToCampana($user['id'], $campanaId)) {
                    jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
                }

                $stmt = $db->prepare("
                    SELECT e.*, u.nombre as responsable_nombre
                    FROM eventos e
                    LEFT JOIN usuarios u ON e.responsable_id = u.id
                    WHERE e.campana_id = ?
                    ORDER BY e.fecha_inicio DESC
                ");
                $stmt->execute([$campanaId]);
                jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Faltan parámetros'], 400);
            }
            break;

        case 'POST':
            $data = getJsonInput();

            // Validar campos requeridos
            $required = ['campana_id', 'nombre', 'tipo', 'fecha_inicio', 'ubicacion', 'estado'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    jsonResponse(['success' => false, 'message' => "El campo $field es requerido"], 400);
                }
            }

            // Verificar acceso a la campaña
            $auth = new Auth();
            if (!$auth->hasAccessToCampana($user['id'], $data['campana_id'])) {
                jsonResponse(['success' => false, 'message' => 'No tienes permiso para agregar eventos a esta campaña'], 403);
            }

            // Insertar evento
            $stmt = $db->prepare("
                INSERT INTO eventos (
                    campana_id, responsable_id, nombre, tipo, descripcion,
                    fecha_inicio, fecha_fin, ubicacion, direccion,
                    departamento, municipio, tipo_territorio, barrio, territorio_id,
                    latitud, longitud,
                    asistentes_esperados, asistentes_confirmados, presupuesto, estado, notas,
                    reevaluacion_estrategica
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $success = $stmt->execute([
                $data['campana_id'],
                $user['id'], 
                $data['nombre'],
                $data['tipo'],
                $data['descripcion'] ?? null,
                $data['fecha_inicio'],
                $data['fecha_fin'] ?? null,
                $data['ubicacion'],
                $data['direccion'] ?? null,
                $data['departamento'] ?? null,
                $data['municipio'] ?? null,
                $data['tipo_territorio'] ?? null,
                $data['barrio'] ?? null,
                $data['territorio_id'] ?? null,
                $data['latitud'] ?? null,
                $data['longitud'] ?? null,
                $data['asistentes_esperados'] ?? 0,
                $data['asistentes_confirmados'] ?? 0,
                $data['presupuesto'] ?? 0,
                $data['estado'],
                $data['notas'] ?? null,
                $data['reevaluacion_estrategica'] ?? null
            ]);

            if ($success) {
                $eventoId = $db->lastInsertId();

                if (class_exists('WorkflowEngine')) {
                    try {
                        WorkflowEngine::trigger(WorkflowEngine::TRIGGER_EVENTO_PROXIMO, [
                            'evento' => ['id' => $eventoId, 'nombre' => $data['nombre'], 'fecha' => $data['fecha_inicio'], 'lugar' => $data['ubicacion']],
                            'fecha_evento' => $data['fecha_inicio'],
                            'dias_antes' => 1,
                        ]);
                    } catch (Throwable $e) {
                        error_log("ALAS trigger evento.proximo: " . $e->getMessage());
                    }
                }

                jsonResponse(['success' => true, 'message' => 'Evento registrado exitosamente', 'id' => $eventoId]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Error al registrar el evento'], 500);
            }
            break;

        case 'PUT':
            $data = getJsonInput();

            if (empty($data['id'])) {
                jsonResponse(['success' => false, 'message' => 'ID de evento requerido'], 400);
            }

            // Obtener evento actual para verificar permisos
            $stmt = $db->prepare("SELECT campana_id FROM eventos WHERE id = ?");
            $stmt->execute([$data['id']]);
            $evento = $stmt->fetch();

            if (!$evento) {
                jsonResponse(['success' => false, 'message' => 'Evento no encontrado'], 404);
            }

            // Verificar acceso
            $auth = new Auth();
            if (!$auth->hasAccessToCampana($user['id'], $evento['campana_id'])) {
                jsonResponse(['success' => false, 'message' => 'No tienes permiso para editar este evento'], 403);
            }

            // Actualizar evento
            $stmt = $db->prepare("
                UPDATE eventos SET
                    nombre = ?, tipo = ?, descripcion = ?,
                    fecha_inicio = ?, fecha_fin = ?, ubicacion = ?,
                    direccion = ?, departamento = ?, municipio = ?, 
                    tipo_territorio = ?, barrio = ?, territorio_id = ?, 
                    latitud = ?, longitud = ?,
                    asistentes_esperados = ?, asistentes_confirmados = ?, presupuesto = ?,
                    estado = ?, notas = ?, reevaluacion_estrategica = ?
                WHERE id = ?
            ");

            $success = $stmt->execute([
                $data['nombre'],
                $data['tipo'],
                $data['descripcion'] ?? null,
                $data['fecha_inicio'],
                $data['fecha_fin'] ?? null,
                $data['ubicacion'],
                $data['direccion'] ?? null,
                $data['departamento'] ?? null,
                $data['municipio'] ?? null,
                $data['tipo_territorio'] ?? null,
                $data['barrio'] ?? null,
                $data['territorio_id'] ?? null,
                $data['latitud'] ?? null,
                $data['longitud'] ?? null,
                $data['asistentes_esperados'] ?? 0,
                $data['asistentes_confirmados'] ?? 0,
                $data['presupuesto'] ?? 0,
                $data['estado'],
                $data['notas'] ?? null,
                $data['reevaluacion_estrategica'] ?? null,
                $data['id']
            ]);

            if ($success) {
                jsonResponse(['success' => true, 'message' => 'Evento actualizado exitosamente']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Error al actualizar el evento'], 500);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;

            if (empty($id)) {
                jsonResponse(['success' => false, 'message' => 'ID de evento requerido'], 400);
            }

            // Obtener evento actual para verificar permisos
            $stmt = $db->prepare("SELECT campana_id FROM eventos WHERE id = ?");
            $stmt->execute([$id]);
            $evento = $stmt->fetch();

            if (!$evento) {
                jsonResponse(['success' => false, 'message' => 'Evento no encontrado'], 404);
            }

            // Verificar acceso
            $auth = new Auth();
            if (!$auth->hasAccessToCampana($user['id'], $evento['campana_id'])) {
                jsonResponse(['success' => false, 'message' => 'No tienes permiso para eliminar este evento'], 403);
            }

            // Eliminar
            $stmt = $db->prepare("DELETE FROM eventos WHERE id = ?");
            if ($stmt->execute([$id])) {
                jsonResponse(['success' => true, 'message' => 'Evento eliminado exitosamente']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Error al eliminar el evento'], 500);
            }
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
    }
} catch (Exception $e) {
    error_log("Error en API eventos: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
}
