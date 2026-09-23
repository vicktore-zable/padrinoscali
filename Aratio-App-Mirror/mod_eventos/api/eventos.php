<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

$db = getDB();
$user = getSessionUser();

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $input = getJsonInput();
    if (!empty($input['_method'])) {
        $method = strtoupper($input['_method']);
    }
}

header('Content-Type: application/json; charset=utf-8');

try {
    switch ($method) {
        case 'GET':
            $id = $_GET['id'] ?? null;
            $campanaId = $_GET['campana_id'] ?? null;
            $action = $_GET['action'] ?? null;

            if ($action === 'listar_responsables') {
                $usuarios = $db->query("SELECT id, nombre, email, telefono, documento_colaborador FROM usuarios WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
                $usuarios = array_map(function($u) {
                    $u['tipo'] = 'usuario';
                    $u['value'] = (string)$u['id'];
                    return $u;
                }, $usuarios);

                $colaboradores = $db->query("
                    SELECT DISTINCT c.documento, CONCAT(c.nombres, ' ', c.apellidos) as nombre, c.email, c.telefono
                    FROM colaboradores c
                    INNER JOIN colaboradores ld ON ld.lider_directo = c.documento
                    WHERE c.estado NOT IN ('inactivo','Inactivo')
                    ORDER BY nombre
                ")->fetchAll();

                $documentosExistentes = array_column($usuarios, 'documento_colaborador');
                $documentosExistentes = array_filter($documentosExistentes);
                $nuevos = [];
                foreach ($colaboradores as $c) {
                    if (!in_array($c['documento'], $documentosExistentes)) {
                        $nuevos[] = [
                            'id' => 'col_' . $c['documento'],
                            'nombre' => $c['nombre'],
                            'email' => $c['email'],
                            'telefono' => $c['telefono'],
                            'documento' => $c['documento'],
                            'tipo' => 'colaborador',
                            'value' => 'col_' . $c['documento']
                        ];
                    }
                }

                jsonResponse(['success' => true, 'data' => array_merge($usuarios, $nuevos)]);
                break;
            }

            if ($id) {
                $stmt = $db->prepare("
                    SELECT e.*, u.nombre as responsable_nombre, u.telefono as responsable_telefono
                    FROM eventos e
                    LEFT JOIN usuarios u ON e.responsable_id = u.id
                    WHERE e.id = ?
                ");
                $stmt->execute([$id]);
                $evento = $stmt->fetch();

                if ($evento) {
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
                $auth = new Auth();
                if (!$auth->hasAccessToCampana($user['id'], $campanaId)) {
                    jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
                }

                $stmt = $db->prepare("
                    SELECT e.*, u.nombre as responsable_nombre, u.telefono as responsable_telefono
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

            $required = ['campana_id', 'nombre', 'tipo', 'fecha_inicio', 'ubicacion', 'estado'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    jsonResponse(['success' => false, 'message' => "El campo $field es requerido"], 400);
                }
            }

            $auth = new Auth();
            if (!$auth->hasAccessToCampana($user['id'], $data['campana_id'])) {
                jsonResponse(['success' => false, 'message' => 'No tienes permiso para agregar eventos a esta campaña'], 403);
            }

            $responsableId = $user['id'];
            if (!empty($data['responsable_id'])) {
                if (is_numeric($data['responsable_id'])) {
                    $responsableId = (int)$data['responsable_id'];
                } elseif (strpos($data['responsable_id'], 'col_') === 0) {
                    $doc = substr($data['responsable_id'], 4);
                    $stmtU = $db->prepare("SELECT id FROM usuarios WHERE documento_colaborador = ?");
                    $stmtU->execute([$doc]);
                    $existe = $stmtU->fetch();
                    if ($existe) {
                        $responsableId = $existe['id'];
                    } else {
                        $stmtC = $db->prepare("SELECT nombres, apellidos, email, telefono FROM colaboradores WHERE documento = ?");
                        $stmtC->execute([$doc]);
                        $col = $stmtC->fetch();
                        if ($col) {
                            $db->prepare("INSERT INTO usuarios (usuario, nombre, email, telefono, password, rol, estado, documento_colaborador, creado_por) VALUES (?, ?, ?, ?, ?, 'coordinador', 'activo', ?, ?)")->execute([
                                $doc,
                                trim($col['nombres'] . ' ' . $col['apellidos']),
                                $col['email'] ?? ($doc . '@aratio.tmp'),
                                $col['telefono'] ?? '',
                                password_hash($doc, PASSWORD_BCRYPT),
                                $doc,
                                $user['id']
                            ]);
                            $responsableId = $db->lastInsertId();
                        }
                    }
                }
            }

            $stmt = $db->prepare("
                INSERT INTO eventos (
                    campana_id, responsable_id, nombre, tipo, descripcion,
                    fecha_inicio, fecha_fin, ubicacion, direccion,
                    departamento, municipio, tipo_territorio, territorio, barrio, territorio_id,
                    latitud, longitud,
                    asistentes_esperados, asistentes_confirmados, presupuesto, estado, notas,
                    telefono_contacto, reevaluacion_estrategica
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $success = $stmt->execute([
                $data['campana_id'],
                $responsableId,
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
                $data['territorio'] ?? null,
                $data['barrio'] ?? null,
                $data['territorio_id'] ?? null,
                $data['latitud'] ?? null,
                $data['longitud'] ?? null,
                $data['asistentes_esperados'] ?? 0,
                $data['asistentes_confirmados'] ?? 0,
                $data['presupuesto'] ?? 0,
                $data['estado'],
                $data['notas'] ?? null,
                $data['telefono_contacto'] ?? null,
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

            $stmt = $db->prepare("SELECT campana_id FROM eventos WHERE id = ?");
            $stmt->execute([$data['id']]);
            $evento = $stmt->fetch();

            if (!$evento) {
                jsonResponse(['success' => false, 'message' => 'Evento no encontrado'], 404);
            }

            $auth = new Auth();
            if (!$auth->hasAccessToCampana($user['id'], $evento['campana_id'])) {
                jsonResponse(['success' => false, 'message' => 'No tienes permiso para editar este evento'], 403);
            }

            $responsableId = null;
            if (!empty($data['responsable_id'])) {
                if (is_numeric($data['responsable_id'])) {
                    $responsableId = (int)$data['responsable_id'];
                } elseif (strpos($data['responsable_id'], 'col_') === 0) {
                    $doc = substr($data['responsable_id'], 4);
                    $stmtU = $db->prepare("SELECT id FROM usuarios WHERE documento_colaborador = ?");
                    $stmtU->execute([$doc]);
                    $existe = $stmtU->fetch();
                    if ($existe) {
                        $responsableId = $existe['id'];
                    } else {
                        $stmtC = $db->prepare("SELECT nombres, apellidos, email, telefono FROM colaboradores WHERE documento = ?");
                        $stmtC->execute([$doc]);
                        $col = $stmtC->fetch();
                        if ($col) {
                            $db->prepare("INSERT INTO usuarios (usuario, nombre, email, telefono, password, rol, estado, documento_colaborador, creado_por) VALUES (?, ?, ?, ?, ?, 'coordinador', 'activo', ?, ?)")->execute([
                                $doc,
                                trim($col['nombres'] . ' ' . $col['apellidos']),
                                $col['email'] ?? ($doc . '@aratio.tmp'),
                                $col['telefono'] ?? '',
                                password_hash($doc, PASSWORD_BCRYPT),
                                $doc,
                                $user['id']
                            ]);
                            $responsableId = $db->lastInsertId();
                        }
                    }
                }
            }

            $stmt = $db->prepare("
                UPDATE eventos SET
                    nombre = ?, tipo = ?, descripcion = ?,
                    fecha_inicio = ?, fecha_fin = ?, ubicacion = ?,
                    direccion = ?, departamento = ?, municipio = ?, 
                    tipo_territorio = ?, barrio = ?, territorio = ?, territorio_id = ?, 
                    latitud = ?, longitud = ?,
                    asistentes_esperados = ?, asistentes_confirmados = ?, presupuesto = ?,
                    estado = ?, notas = ?, telefono_contacto = ?,
                    responsable_id = ?, reevaluacion_estrategica = ?
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
                $data['territorio'] ?? null,
                $data['territorio_id'] ?? null,
                $data['latitud'] ?? null,
                $data['longitud'] ?? null,
                $data['asistentes_esperados'] ?? 0,
                $data['asistentes_confirmados'] ?? 0,
                $data['presupuesto'] ?? 0,
                $data['estado'],
                $data['notas'] ?? null,
                $data['telefono_contacto'] ?? null,
                $responsableId,
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
            $input = getJsonInput();
            $id = $input['id'] ?? $_GET['id'] ?? null;

            if (empty($id)) {
                jsonResponse(['success' => false, 'message' => 'ID de evento requerido'], 400);
            }

            $stmt = $db->prepare("SELECT campana_id FROM eventos WHERE id = ?");
            $stmt->execute([$id]);
            $evento = $stmt->fetch();

            if (!$evento) {
                jsonResponse(['success' => false, 'message' => 'Evento no encontrado'], 404);
            }

            $auth = new Auth();
            if (!$auth->hasAccessToCampana($user['id'], $evento['campana_id'])) {
                jsonResponse(['success' => false, 'message' => 'No tienes permiso para eliminar este evento'], 403);
            }

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
