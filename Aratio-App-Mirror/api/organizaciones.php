<?php
/**
 * API REST - Módulo Organizaciones
 * Sistema Aratio v1.7.0
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── Helpers ──────────────────────────────────────────────────────────────────

function requireAuthApi() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
}

function getInput() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function san($v) { return htmlspecialchars(strip_tags(trim($v ?? '')), ENT_QUOTES, 'UTF-8'); }

// ── GET ───────────────────────────────────────────────────────────────────────

if ($method === 'GET') {

    // ── Listado Organizaciones ─────────────────────────────────────────────────────────
    if ($action === 'list' || $action === '') {
        $campanaId = intval($_GET['campana_id'] ?? 0);
        if (!$campanaId) { echo json_encode(['success'=>false,'message'=>'campana_id requerido']); exit; }

        $where  = 'WHERE r.id_campana = ?';
        $params = [$campanaId];
        if (!empty($_GET['municipio']))       { $where .= ' AND r.municipio = ?';        $params[] = $_GET['municipio']; }
        if (!empty($_GET['estado']))          { $where .= ' AND r.estado = ?';           $params[] = $_GET['estado']; }
        
        if (!empty($_GET['search'])) {
            $q = '%'.$_GET['search'].'%';
            $where .= ' AND (r.nombre_jac LIKE ? OR r.presidente LIKE ? OR r.sector LIKE ?)';
            array_push($params, $q, $q, $q);
        }

        $limit = !empty($_GET['limit']) ? 'LIMIT '.intval($_GET['limit']) : '';
        $stmt = $db->prepare("
            SELECT r.*,
                   u.nombre AS registrado_por,
                   r.tipo_organizacion,
                   p.id     AS plancha_activa_id,
                   p.nombre AS plancha_activa_nombre
            FROM jac_registros r
            LEFT JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN jac_planchas p ON p.jac_id = r.id AND p.estado = 'activa'
            $where ORDER BY r.created_at DESC $limit
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true,'data'=>$rows,'total'=>count($rows)]);
        exit;
    }

    // ── Get una Organización ───────────────────────────────────────────────────────
    if ($action === 'get') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM jac_registros WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo $row ? json_encode(['success'=>true,'data'=>$row])
                  : json_encode(['success'=>false,'message'=>'No encontrado']);
        exit;
    }

    // ── Planchas de una Organización (incluye bloques v1.7.0) ────────────────────
    if ($action === 'planchas') {
        $jacId = intval($_GET['jac_id'] ?? 0);
        if (!$jacId) { echo json_encode(['success'=>false,'message'=>'jac_id requerido']); exit; }

        $stmt = $db->prepare("SELECT * FROM jac_planchas WHERE jac_id = ? ORDER BY estado DESC, created_at DESC");
        $stmt->execute([$jacId]);
        $planchas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($planchas as &$p) {
            $mStmt = $db->prepare("SELECT * FROM jac_plancha_miembros WHERE plancha_id = ? ORDER BY bloque ASC, orden ASC");
            $mStmt->execute([$p['id']]);
            $p['miembros'] = $mStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['success'=>true,'data'=>$planchas,'total'=>count($planchas)]);
        exit;
    }

    // ── Datos para mapa Leaflet (reconstruido para v1.7.0) ────────────────────
    if ($action === 'mapa') {
        $campanaId = intval($_GET['campana_id'] ?? 0);
        if (!$campanaId) { echo json_encode(['success'=>false,'message'=>'campana_id requerido']); exit; }

        $where  = 'WHERE j.id_campana = ?';
        $params = [$campanaId];
        if (!empty($_GET['municipio']))   { $where .= ' AND j.municipio = ?';        $params[] = $_GET['municipio']; }
        if (!empty($_GET['tipo']))        { $where .= ' AND j.tipo_territorio = ?'; $params[] = $_GET['tipo']; }
        if (!empty($_GET['tipo_organizacion'])) { $where .= ' AND j.tipo_organizacion = ?'; $params[] = $_GET['tipo_organizacion']; }
        if (!empty($_GET['territorio']))  { $where .= ' AND j.territorio_valle = ?'; $params[] = $_GET['territorio']; }
        if (!empty($_GET['barrio']))      { $where .= ' AND j.sector = ?';           $params[] = $_GET['barrio']; }

        // JACs con centroide del barrio desde tabla territorios
        $stmt = $db->prepare("
            SELECT j.*,
                   p.id AS plancha_id, p.nombre AS plancha_nombre,
                   ST_Y(ST_Centroid(t.geometria)) AS lat,
                   ST_X(ST_Centroid(t.geometria)) AS lng,
                   ST_AsGeoJSON(t.geometria)       AS geojson
            FROM jac_registros j
            LEFT JOIN jac_planchas p ON p.jac_id = j.id AND p.estado = 'activa'
            LEFT JOIN territorios t ON LOWER(t.barrio) = LOWER(j.sector)
                                   AND LOWER(t.municipio) = LOWER(j.municipio)
            $where
            ORDER BY j.municipio, j.sector
        ");
        $stmt->execute($params);
        $jacs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Enriquecer con miembros clave (Directivo)
        foreach ($jacs as &$jac) {
            if ($jac['plancha_id']) {
                $mStmt = $db->prepare("SELECT orden, nombre, cargo, bloque FROM jac_plancha_miembros WHERE plancha_id = ? ORDER BY bloque, orden");
                $mStmt->execute([$jac['plancha_id']]);
                $jac['plancha_miembros'] = $mStmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $jac['plancha_miembros'] = [];
            }
            if ($jac['geojson']) {
                $jac['geojson'] = json_decode($jac['geojson'], true);
            }
        }

        // Agrupar por sector_nombre para el frontend
        $groups = [];
        foreach ($jacs as $jac) {
            $key = ($jac['municipio'] ?? '') . '|' . ($jac['sector'] ?? 'Sin Barrio');
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'municipio'     => $jac['municipio'],
                    'sector_nombre' => $jac['sector'] ?? 'Sin Barrio',
                    'tipo_organizacion' => $jac['tipo_organizacion'] ?? 'JAC',
                    'lat'           => $jac['lat'],
                    'lng'           => $jac['lng'],
                    'geom'          => $jac['geojson'],
                    'jacs'          => []
                ];
            }
            // Mapeo compacto para el frontend
            $planchaData = null;
            if ($jac['plancha_id']) {
                $planchaData = [
                    'nombre_plancha' => $jac['plancha_nombre'],
                    'miembros' => array_map(function($m) {
                        return [
                            'nombre_completo' => $m['nombre'],
                            'cargo' => $m['cargo'] ? $m['cargo'] : $m['bloque']
                        ];
                    }, $jac['plancha_miembros'])
                ];
            }

            $groups[$key]['jacs'][] = [
                'id' => $jac['id'],
                'nombre' => $jac['nombre_jac'],
                'tipo_organizacion' => $jac['tipo_organizacion'] ?? 'Entidad',
                'presidente' => $jac['presidente'] ?? 'No asignado',
                'telefono' => $jac['telefono'] ?? 'N/A',
                'direccion' => $jac['direccion'] ?? 'No registrada',
                'email' => $jac['email'] ?? 'Sin email',
                'meta_votos' => intval($jac['votos_comprometidos'] ?? 0),
                'afiliados' => intval($jac['afiliados_count'] ?? 0),
                'observaciones' => $jac['observaciones'] ?? '',
                'plancha' => $planchaData
            ];
        }

        echo json_encode(['success'=>true,'data'=>array_values($groups),'total'=>count($groups)], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success'=>false,'message'=>'Accion no valida']);
    exit;
}

// ── POST ─────────────────────────────────────────────────────────────────────

if ($method === 'POST') {

    // ── Crear Plancha ─────────────────────────────────────────────────────────
    if ($action === 'plancha') {
        requireAuthApi();
        $data     = getInput();
        $jacId    = intval($data['jac_id'] ?? 0);
        $nombre   = san($data['nombre'] ?? '');
        $miembros = $data['miembros'] ?? [];

        if (!$jacId || !$nombre) {
            echo json_encode(['success'=>false,'message'=>'jac_id y nombre requeridos']); exit;
        }

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO jac_planchas (jac_id, nombre, estado) VALUES (?,?,'inactiva')");
            $stmt->execute([$jacId, $nombre]);
            $planchaId = $db->lastInsertId();

            $mStmt = $db->prepare("INSERT INTO jac_plancha_miembros (plancha_id, orden, cedula, nombre, fecha_nacimiento, cargo, telefono, bloque, genero) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($miembros as $m) {
                $mStmt->execute([
                    $planchaId,
                    intval($m['orden'] ?? 1),
                    san($m['cedula']),
                    san($m['nombre']),
                    !empty($m['fecha_nacimiento']) ? $m['fecha_nacimiento'] : null,
                    san($m['cargo'] ?? ''),
                    san($m['telefono'] ?? ''),
                    $m['bloque'],
                    $m['genero'] ?? 'O'
                ]);
            }

            $db->commit();
            echo json_encode(['success'=>true,'message'=>'Plancha creada','id'=>$planchaId]);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        }
        exit;
    }

    // ── Crear Organización (POST sin action= o action=create) ───────────────────────
    // El JS del modal llama fetch(API+'organizaciones.php', {method:'POST', body:...})
    // sin ?action=, por eso la condición acepta action vacío o 'create'
    requireAuthApi();
    $data = getInput();

    if (empty($data['nombre_jac'])) {
        echo json_encode(['success'=>false,'message'=>'nombre_jac es requerido']);
        exit;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO jac_registros
                (id_campana, depto, nombre_jac, tipo_organizacion, presidente, telefono, email, direccion,
                 municipio, tipo_territorio, territorio_valle_nombre, territorio_valle,
                 sector, comuna, votos_comprometidos, afiliados_count, estado, observaciones, usuario_id)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            intval($data['id_campana']),
            san($data['depto'] ?? 'VALLE DEL CAUCA'),
            san($data['nombre_jac']),
            san($data['tipo_organizacion'] ?? 'JAC'),
            san($data['presidente'] ?? ''),
            san($data['telefono'] ?? ''),
            san($data['email'] ?? ''),
            san($data['direccion'] ?? ''),
            san($data['municipio'] ?? ''),
            san($data['tipo_territorio'] ?? ''),
            san($data['territorio_valle_nombre'] ?? ''),
            san($data['territorio_valle_nombre'] ?? ''), // campo legacy
            san($data['sector'] ?? ''),
            san($data['comuna'] ?? ''),
            intval($data['votos_comprometidos'] ?? 0),
            intval($data['afiliados_count'] ?? 0),
            $data['estado'] ?? 'activa',
            san($data['observaciones'] ?? ''),
            intval($_SESSION['user_id'] ?? 0)
        ]);
        echo json_encode(['success'=>true,'message'=>'Organización creada','id'=>$db->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
    exit;
}

// ── PUT ───────────────────────────────────────────────────────────────────────

if ($method === 'PUT') {
    requireAuthApi();
    $data = getInput();

    if ($action === 'activar_plancha') {
        $id = intval($_GET['id'] ?? $data['id'] ?? 0);
        $pStmt = $db->prepare("SELECT jac_id FROM jac_planchas WHERE id = ?");
        $pStmt->execute([$id]);
        $p = $pStmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) { echo json_encode(['success'=>false,'message'=>'No encontrada']); exit; }

        $db->beginTransaction();
        $db->prepare("UPDATE jac_planchas SET estado='inactiva' WHERE jac_id = ?")->execute([$p['jac_id']]);
        $db->prepare("UPDATE jac_planchas SET estado='activa' WHERE id = ?")->execute([$id]);
        $db->commit();
        echo json_encode(['success'=>true,'message'=>'Plancha activada']);
        exit;
    }

    if ($action === 'plancha') {
        $id = intval($data['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'id requerido']); exit; }

        $db->beginTransaction();
        try {
            $db->prepare("UPDATE jac_planchas SET nombre=? WHERE id=?")->execute([san($data['nombre']), $id]);
            $db->prepare("DELETE FROM jac_plancha_miembros WHERE plancha_id=?")->execute([$id]);
            $mStmt = $db->prepare("INSERT INTO jac_plancha_miembros (plancha_id, orden, cedula, nombre, fecha_nacimiento, cargo, telefono, bloque, genero) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($data['miembros'] as $m) {
                $mStmt->execute([
                    $id, 
                    intval($m['orden'] ?? 1), 
                    san($m['cedula']), 
                    san($m['nombre']),
                    !empty($m['fecha_nacimiento']) ? $m['fecha_nacimiento'] : null,
                    san($m['cargo'] ?? ''),
                    san($m['telefono'] ?? ''),
                    $m['bloque'],
                    $m['genero'] ?? 'O'
                ]);
            }
            $db->commit();
            echo json_encode(['success'=>true,'message'=>'Plancha actualizada']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        }
        exit;
    }

    // Actualizar Organización
    $id = intval($data['id'] ?? 0);
    $db->prepare("
        UPDATE jac_registros SET
            depto=?, nombre_jac=?, tipo_organizacion=?, presidente=?, telefono=?, email=?, direccion=?,
            municipio=?, tipo_territorio=?, territorio_valle_nombre=?, territorio_valle=?,
            sector=?, comuna=?, votos_comprometidos=?, afiliados_count=?, estado=?, observaciones=?
        WHERE id=?
    ")->execute([
        san($data['depto'] ?? 'VALLE DEL CAUCA'),
        san($data['nombre_jac']),
        san($data['tipo_organizacion'] ?? 'JAC'),
        san($data['presidente']), san($data['telefono']), san($data['email']), san($data['direccion']),
        san($data['municipio']), san($data['tipo_territorio']), san($data['territorio_valle_nombre']), san($data['territorio_valle_nombre']),
        san($data['sector']), san($data['comuna']),
        intval($data['votos_comprometidos']), intval($data['afiliados_count']), $data['estado'], san($data['observaciones']),
        $id
    ]);
    echo json_encode(['success'=>true,'message'=>'Organización actualizada']);
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    requireAuthApi();
    $id = intval($_GET['id'] ?? 0);
    if ($action === 'plancha') {
        $db->prepare("DELETE FROM jac_plancha_miembros WHERE plancha_id=?")->execute([$id]);
        $db->prepare("DELETE FROM jac_planchas WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true]); exit;
    }
    // Delete Organización
    $pIds = $db->prepare("SELECT id FROM jac_planchas WHERE jac_id=?");
    $pIds->execute([$id]);
    foreach ($pIds->fetchAll(PDO::FETCH_COLUMN) as $pid) {
        $db->prepare("DELETE FROM jac_plancha_miembros WHERE plancha_id=?")->execute([$pid]);
    }
    $db->prepare("DELETE FROM jac_planchas WHERE jac_id=?")->execute([$id]);
    $db->prepare("DELETE FROM jac_registros WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true]); exit;
}
