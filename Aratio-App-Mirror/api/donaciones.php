<?php
/**
 * API: Donaciones
 * CRUD endpoints for donations
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user = getSessionUser();
$auth = new Auth();

header('Content-Type: application/json; charset=utf-8');

try {
    switch ($method) {
        case 'GET':
            // Obtener campañas del usuario
            $campanas = $auth->getUserCampanas($user['id']);
            $campanasIds = array_column($campanas, 'id');

            if (empty($campanasIds)) {
                jsonResponse(['success' => true, 'data' => []]);
                break;
            }

            $placeholders = str_repeat('?,', count($campanasIds) - 1) . '?';
            $stmt = $db->prepare("
                SELECT d.*, u.nombre as recaudador_nombre
                FROM donaciones d
                LEFT JOIN usuarios u ON d.recaudador_id = u.id
                WHERE d.campana_id IN ($placeholders)
                ORDER BY d.fecha_donacion DESC
            ");
            $stmt->execute($campanasIds);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(['success' => true, 'data' => $data]);
            break;

        case 'POST':
            $data = getJsonInput();
            $required = ['campana_id', 'tipo_donante', 'nombre_donante', 'monto', 'metodo_pago', 'fecha_donacion'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    jsonResponse(['success' => false, 'message' => "El campo $field es requerido"], 400);
                }
            }

            // Verificar acceso a campaña
            if (!$auth->hasAccessToCampana($user['id'], $data['campana_id'])) {
                jsonResponse(['success' => false, 'message' => 'No tienes acceso a esta campaña'], 403);
            }

            $stmt = $db->prepare("
                INSERT INTO donaciones (
                    campana_id, tipo_donante, nombre_donante, documento_donante, email_donante,
                    telefono_donante, direccion_donante, monto, metodo_pago, referencia_pago,
                    descripcion_especie, estado, fecha_donacion, recaudador_id, comprobante_url, notas
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['campana_id'],
                $data['tipo_donante'],
                $data['nombre_donante'],
                $data['documento_donante'] ?? null,
                $data['email_donante'] ?? null,
                $data['telefono_donante'] ?? null,
                $data['direccion_donante'] ?? null,
                $data['monto'],
                $data['metodo_pago'],
                $data['referencia_pago'] ?? null,
                $data['descripcion_especie'] ?? null,
                $data['estado'] ?? 'pendiente',
                $data['fecha_donacion'],
                $data['recaudador_id'] ?? $user['id'],
                $data['comprobante_url'] ?? null,
                $data['notas'] ?? null
            ]);

            $donacionId = $db->lastInsertId();

            if (class_exists('ActivityLogger')) {
                ActivityLogger::log(
                    (int)($data['colaborador_id'] ?? 0),
                    'donacion_hizo',
                    "Donación $" . number_format((float)$data['monto'], 0) . " ({$data['metodo_pago']})",
                    ['donacion_id' => $donacionId, 'monto' => $data['monto'], 'tipo' => $data['tipo_donante'], 'metodo' => $data['metodo_pago']],
                    'donaciones',
                    $donacionId,
                    $user['id']
                );
            }

            if (class_exists('WorkflowEngine') && !empty($data['colaborador_id'])) {
                try {
                    WorkflowEngine::trigger(WorkflowEngine::TRIGGER_DONACION_RECIBIDA, [
                        'donacion' => ['id' => $donacionId, 'monto' => $data['monto'], 'tipo' => $data['tipo_donante']],
                        'monto' => $data['monto'],
                        'colaborador_id' => $data['colaborador_id']
                    ], (int)$data['colaborador_id']);
                } catch (Throwable $e) {
                    error_log("ALAS trigger donacion: " . $e->getMessage());
                }
            }

            jsonResponse(['success' => true, 'message' => 'Donación registrada exitosamente', 'id' => $donacionId]);
            break;

        case 'PUT':
            $data = getJsonInput();
            if (empty($data['id'])) {
                jsonResponse(['success' => false, 'message' => 'ID de donación requerido'], 400);
            }

            $stmt = $db->prepare("
                UPDATE donaciones SET
                    tipo_donante = ?, nombre_donante = ?, documento_donante = ?, email_donante = ?,
                    telefono_donante = ?, direccion_donante = ?, monto = ?, metodo_pago = ?,
                    referencia_pago = ?, descripcion_especie = ?, estado = ?, fecha_donacion = ?,
                    comprobante_url = ?, notas = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['tipo_donante'],
                $data['nombre_donante'],
                $data['documento_donante'] ?? null,
                $data['email_donante'] ?? null,
                $data['telefono_donante'] ?? null,
                $data['direccion_donante'] ?? null,
                $data['monto'],
                $data['metodo_pago'],
                $data['referencia_pago'] ?? null,
                $data['descripcion_especie'] ?? null,
                $data['estado'] ?? 'pendiente',
                $data['fecha_donacion'],
                $data['comprobante_url'] ?? null,
                $data['notas'] ?? null,
                $data['id']
            ]);

            // Si se confirma, actualizar fecha de confirmación
            if ($data['estado'] === 'confirmada') {
                $db->prepare("UPDATE donaciones SET fecha_confirmacion = NOW() WHERE id = ?")->execute([$data['id']]);
            }

            jsonResponse(['success' => true, 'message' => 'Donación actualizada exitosamente']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (empty($id)) {
                jsonResponse(['success' => false, 'message' => 'ID de donación requerido'], 400);
            }

            $stmt = $db->prepare("DELETE FROM donaciones WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(['success' => true, 'message' => 'Donación eliminada exitosamente']);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
    }
} catch (Exception $e) {
    error_log('Error en API donaciones: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
}
?>