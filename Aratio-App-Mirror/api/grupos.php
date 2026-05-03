<?php
/**
 * API: Grupos Políticos
 * CRUD endpoints for political groups
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
            $stmt = $db->prepare("SELECT * FROM grupos_politicos ORDER BY nombre ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(['success' => true, 'data' => $data]);
            break;

        case 'POST':
            $data = getJsonInput();
            $required = ['nombre', 'sigla', 'tipo', 'color'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    jsonResponse(['success' => false, 'message' => "El campo $field es requerido"], 400);
                }
            }

            $stmt = $db->prepare("
                INSERT INTO grupos_politicos (
                    nombre, sigla, tipo, color, logo_url, fecha_fundacion,
                    representante_legal, email, telefono, web, numero_afiliados, activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nombre'],
                $data['sigla'],
                $data['tipo'],
                $data['color'],
                $data['logo_url'] ?? null,
                $data['fecha_fundacion'] ?? null,
                $data['representante_legal'] ?? null,
                $data['email'] ?? null,
                $data['telefono'] ?? null,
                $data['web'] ?? null,
                $data['numero_afiliados'] ?? null,
                $data['activo'] ?? 1
            ]);

            jsonResponse(['success' => true, 'message' => 'Grupo político creado exitosamente', 'id' => $db->lastInsertId()]);
            break;

        case 'PUT':
            $data = getJsonInput();
            if (empty($data['id'])) {
                jsonResponse(['success' => false, 'message' => 'ID de grupo requerido'], 400);
            }

            $stmt = $db->prepare("
                UPDATE grupos_politicos SET
                    nombre = ?, sigla = ?, tipo = ?, color = ?, logo_url = ?,
                    fecha_fundacion = ?, representante_legal = ?, email = ?,
                    telefono = ?, web = ?, numero_afiliados = ?, activo = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['nombre'],
                $data['sigla'],
                $data['tipo'],
                $data['color'],
                $data['logo_url'] ?? null,
                $data['fecha_fundacion'] ?? null,
                $data['representante_legal'] ?? null,
                $data['email'] ?? null,
                $data['telefono'] ?? null,
                $data['web'] ?? null,
                $data['numero_afiliados'] ?? null,
                $data['activo'] ?? 1,
                $data['id']
            ]);

            jsonResponse(['success' => true, 'message' => 'Grupo político actualizado exitosamente']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (empty($id)) {
                jsonResponse(['success' => false, 'message' => 'ID de grupo requerido'], 400);
            }

            // Verificar si el grupo tiene candidatos asociados
            $stmt = $db->prepare("SELECT COUNT(*) FROM candidatos WHERE grupo_politico_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                jsonResponse(['success' => false, 'message' => "No se puede eliminar: tiene $count candidato(s) asociado(s)"], 400);
            }

            $stmt = $db->prepare("DELETE FROM grupos_politicos WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(['success' => true, 'message' => 'Grupo político eliminado exitosamente']);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
    }
} catch (Exception $e) {
    error_log('Error en API grupos: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
}
?>