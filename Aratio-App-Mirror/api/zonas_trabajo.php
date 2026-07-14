<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? null) === 'PUT') {
    $method = 'PUT';
} else {
    $method = $_SERVER['REQUEST_METHOD'];
}
$action = $_GET['action'] ?? null;

requireAuth();

try {
    $db = getDB();
    $userId = $_SESSION['usuario_id'] ?? null;

    if ($action === 'geo') {
        $colaboradorId = $_GET['colaborador_id'] ?? null;
        $municipio = $_GET['municipio'] ?? null;

        if ($colaboradorId) {
            $conSeguidores = $_GET['con_seguidores'] ?? false;

            $sql = "
                SELECT z.id, z.colaborador_id, z.territorio_id, z.impacto_estimado, z.descripcion,
                       t.departamento, t.municipio, t.Tipo_territorio, t.Territorio, t.barrio, t.cod_mpio,
                       ST_AsGeoJSON(t.geometria) as geometry_json,
                       CONCAT_WS(' ', c.nombres, c.apellidos) as colaborador_nombre
                FROM zonas_trabajo_social z
                JOIN territorios t ON z.territorio_id = t.id
                JOIN colaboradores c ON z.colaborador_id = c.id
                WHERE z.activo = 1
            ";

            if ($conSeguidores) {
                $stmt = $db->prepare("SELECT documento, campana_id FROM colaboradores WHERE id = ?");
                $stmt->execute([$colaboradorId]);
                $colab = $stmt->fetch();
                if ($colab) {
                    $sql .= " AND (z.colaborador_id = ?
                        OR z.colaborador_id IN (
                            SELECT id FROM colaboradores
                            WHERE lider_directo = ? AND campana_id = ?
                        ))";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$colaboradorId, $colab['documento'], $colab['campana_id']]);
                } else {
                    $sql .= " AND z.colaborador_id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$colaboradorId]);
                }
            } else {
                $sql .= " AND z.colaborador_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$colaboradorId]);
            }
        } elseif ($municipio) {
            $stmt = $db->prepare("
                SELECT z.id, z.colaborador_id, z.territorio_id, z.impacto_estimado, z.descripcion,
                       t.departamento, t.municipio, t.Tipo_territorio, t.Territorio, t.barrio, t.cod_mpio,
                       ST_AsGeoJSON(t.geometria) as geometry_json,
                       CONCAT_WS(' ', c.nombres, c.apellidos) as colaborador_nombre
                FROM zonas_trabajo_social z
                JOIN territorios t ON z.territorio_id = t.id
                JOIN colaboradores c ON z.colaborador_id = c.id
                WHERE t.municipio = ? AND z.activo = 1
            ");
            $stmt->execute([$municipio]);
        } else {
            $stmt = $db->prepare("
                SELECT z.id, z.colaborador_id, z.territorio_id, z.impacto_estimado, z.descripcion,
                       t.departamento, t.municipio, t.Tipo_territorio, t.Territorio, t.barrio, t.cod_mpio,
                       ST_AsGeoJSON(t.geometria) as geometry_json,
                       CONCAT_WS(' ', c.nombres, c.apellidos) as colaborador_nombre
                FROM zonas_trabajo_social z
                JOIN territorios t ON z.territorio_id = t.id
                JOIN colaboradores c ON z.colaborador_id = c.id
                WHERE z.activo = 1
            ");
            $stmt->execute();
        }

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $features = [];

        foreach ($results as $row) {
            $geometry = json_decode($row['geometry_json'], true);
            unset($row['geometry_json']);
            $features[] = [
                'type' => 'Feature',
                'id' => $row['id'],
                'geometry' => $geometry,
                'properties' => $row
            ];
        }

        jsonResponse([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }

    if ($action === 'colaboradores_con_zonas') {
        $stmt = $db->prepare("
            SELECT DISTINCT z.colaborador_id, CONCAT_WS(' ', c.nombres, c.apellidos) as nombre, c.documento
            FROM zonas_trabajo_social z
            JOIN colaboradores c ON z.colaborador_id = c.id
            WHERE z.activo = 1
            ORDER BY nombre
        ");
        $stmt->execute();
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    if ($action === 'con_seguidores') {
        $colaboradorId = $_GET['colaborador_id'] ?? null;
        if (!$colaboradorId) {
            jsonResponse(['success' => false, 'message' => 'colaborador_id requerido'], 400);
        }

        $stmt = $db->prepare("SELECT documento, campana_id FROM colaboradores WHERE id = ?");
        $stmt->execute([$colaboradorId]);
        $colab = $stmt->fetch();
        if (!$colab) {
            jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
        }

        $stmt = $db->prepare("
            SELECT z.id, z.colaborador_id, z.territorio_id, z.impacto_estimado, z.descripcion, z.activo,
                   t.departamento, t.municipio, t.Tipo_territorio, t.Territorio, t.barrio, t.cod_mpio,
                   c.nombres as responsable_nombres, c.apellidos as responsable_apellidos, c.documento as responsable_doc,
                   CASE WHEN z.colaborador_id = ? THEN 'propia' ELSE 'seguidor' END as tipo
            FROM zonas_trabajo_social z
            JOIN territorios t ON z.territorio_id = t.id
            JOIN colaboradores c ON z.colaborador_id = c.id
            WHERE z.activo = 1
              AND (z.colaborador_id = ?
                   OR z.colaborador_id IN (
                       SELECT id FROM colaboradores
                       WHERE lider_directo = ? AND campana_id = ?
                   ))
            ORDER BY tipo, t.Territorio, t.barrio
        ");
        $stmt->execute([$colaboradorId, $colaboradorId, $colab['documento'], $colab['campana_id']]);
        $zonas = $stmt->fetchAll();

        jsonResponse([
            'success' => true,
            'data' => $zonas,
            'total' => count($zonas),
            'propias' => count(array_filter($zonas, fn($z) => $z['tipo'] === 'propia')),
            'seguidores' => count(array_filter($zonas, fn($z) => $z['tipo'] === 'seguidor'))
        ]);
    }

    switch ($method) {
        case 'GET':
            $colaboradorId = $_GET['colaborador_id'] ?? null;
            if (!$colaboradorId) {
                jsonResponse(['success' => false, 'message' => 'colaborador_id requerido'], 400);
            }
            $stmt = $db->prepare("
                SELECT z.id, z.colaborador_id, z.territorio_id, z.impacto_estimado, z.descripcion, z.activo,
                       t.departamento, t.municipio, t.Tipo_territorio, t.Territorio, t.barrio, t.cod_mpio,
                       t.Código
                FROM zonas_trabajo_social z
                JOIN territorios t ON z.territorio_id = t.id
                WHERE z.colaborador_id = ?
                ORDER BY t.Territorio, t.barrio
            ");
            $stmt->execute([$colaboradorId]);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['colaborador_id']) || empty($data['territorio_id'])) {
                jsonResponse(['success' => false, 'message' => 'colaborador_id y territorio_id requeridos'], 400);
            }

            $stmt = $db->prepare("
                INSERT INTO zonas_trabajo_social (colaborador_id, territorio_id, impacto_estimado, descripcion)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['colaborador_id'],
                $data['territorio_id'],
                $data['impacto_estimado'] ?? 0,
                $data['descripcion'] ?? null
            ]);
            $id = $db->lastInsertId();

            jsonResponse(['success' => true, 'message' => 'Zona de trabajo agregada', 'id' => $id], 201);
            break;

        case 'PUT':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                jsonResponse(['success' => false, 'message' => 'id requerido'], 400);
            }
            $rawBody = file_get_contents('php://input');
            $data = json_decode($rawBody, true);
            if (!$data) {
                $data = $_POST;
            }

            $stmt = $db->prepare("
                UPDATE zonas_trabajo_social 
                SET territorio_id = ?, impacto_estimado = ?, descripcion = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['territorio_id'],
                $data['impacto_estimado'] ?? 0,
                $data['descripcion'] ?? null,
                $id
            ]);
            jsonResponse(['success' => true, 'message' => 'Zona actualizada']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                jsonResponse(['success' => false, 'message' => 'id requerido'], 400);
            }
            $stmt = $db->prepare("DELETE FROM zonas_trabajo_social WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(['success' => true, 'message' => 'Zona eliminada']);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Método no soportado'], 405);
    }
} catch (Exception $e) {
    error_log("API zonas_trabajo error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}