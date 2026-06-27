<?php
/**
 * API: Colaboradores
 * CRUD completo + carga masiva Excel + búsqueda de líderes
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? null;

// Autenticación requerida para todo EXCEPTO para obtener líderes (público)
if ($action !== 'lideres') {
    requireAuth();
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'] ?? null;

// Soporte para _method=PUT en hosting que no soporta PUT nativo
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (isset($body['_method']) && strtoupper($body['_method']) === 'PUT') {
        $method = 'PUT';
    }
}

try {
    // Acciones especiales
    if ($action) {
        switch ($action) {
            case 'lideres':
                handleGetLideres($db);
                break;
            case 'import':
                handleImport($db, $userId);
                break;
            case 'plantilla_xlsx':
                handlePlantillaXlsx();
                break;
            case 'stats':
                handleStats($db);
                break;
            // ====== NUEVOS ENDPOINTS - Fase 2 ======
            case 'network':
                handleNetwork($db);
                break;
            case 'seguidores':
                handleSeguidores($db);
                break;
            case 'historial':
                handleHistorial($db);
                break;
            case 'curriculum':
                if ($method === 'GET') {
                    handleGetCurriculum($db);
                } else {
                    handleSaveCurriculum($db, $userId);
                }
                break;
            case 'cambiar_lider':
                handleCambiarLider($db, $userId);
                break;
            case 'reevaluar':
                handleReevaluar($db, $userId);
                break;
            case 'reportes':
                handleReportes($db);
                break;
            case 'export':
                handleExport($db);
                break;
            case 'plantilla':
                handlePlantilla();
                break;
            case 'stats_extended':
                handleStatsExtended($db);
                break;
            default:
                jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
        }
        exit;
    }

    // CRUD estándar
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
    error_log("API colaboradores error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
}

/**
 * Calcular estado basado en dato_potencial y dato_historico
 */
function calcularEstado($dato_potencial, $dato_historico)
{
    if ($dato_potencial == 0)
        return 'Nuevo';
    if ($dato_historico == 0)
        return 'Desvinculado';
    if ($dato_historico > $dato_potencial)
        return 'Creció';
    if ($dato_historico < $dato_potencial)
        return 'Decrece';
    return 'Igual';
}

/**
 * Calcular grupo etáreo basado en fecha de nacimiento
 */
function calcularGrupoEtareo($fecha_nacimiento)
{
    $nacimiento = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento)->y;

    if ($edad <= 17)
        return 'Joven (0-17)';
    if ($edad <= 28)
        return 'Juventud (18-28)';
    if ($edad <= 40)
        return 'Adulto Joven (29-40)';
    if ($edad <= 60)
        return 'Adulto (41-60)';
    return 'Mayor (61+)';
}

/**
 * GET: Listar colaboradores o buscar
 */
function handleGet($db, $userId)
{
    $campanaId = $_GET['campana_id'] ?? null;
    $colaboradorId = $_GET['id'] ?? null;
    $search = $_GET['search'] ?? null;
    $perfil = $_GET['perfil'] ?? null;
    $estado = $_GET['estado'] ?? null;
    $nivel = $_GET['nivel'] ?? null;
    $municipio = $_GET['municipio'] ?? null;
    $barrio = $_GET['barrio'] ?? null;

    if ($colaboradorId) {
        // Obtener colaborador específico
        $stmt = $db->prepare("
            SELECT c.*, u.nombre as usuario_nombre,
                   CONCAT_WS(' ', l.nombres, l.apellidos) as lider_nombre,
                   t.cod_mpio,
                   (SELECT COUNT(*) FROM colaboradores s WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id) as num_seguidores
            FROM colaboradores c
            LEFT JOIN usuarios u ON c.usuario_registro_id = u.id
            LEFT JOIN colaboradores l ON c.lider_directo = l.documento AND c.campana_id = l.campana_id
            LEFT JOIN territorios t ON c.territorio_id = t.id
            WHERE c.id = ?
        ");
        $stmt->execute([$colaboradorId]);
        $colaborador = $stmt->fetch();

        if ($colaborador) {
            // Calcular campos virtuales
            $colaborador['estado'] = calcularEstado($colaborador['dato_potencial'], $colaborador['dato_historico']);
            $colaborador['grupo_etareo'] = calcularGrupoEtareo($colaborador['fecha_nacimiento']);
            $colaborador['nombre_completo'] = $colaborador['nombres'] . ' ' . $colaborador['apellidos'];
            // Derivar cod_mpio si territorio_id es NULL (fallback)
            if (empty($colaborador['cod_mpio']) && !empty($colaborador['departamento']) && !empty($colaborador['municipio'])) {
                $stmtCm = $db->prepare("SELECT cod_mpio FROM territorios WHERE departamento = ? AND municipio = ? LIMIT 1");
                $stmtCm->execute([$colaborador['departamento'], $colaborador['municipio']]);
                $rowCm = $stmtCm->fetch();
                if ($rowCm) $colaborador['cod_mpio'] = $rowCm['cod_mpio'];
            }
            jsonResponse(['success' => true, 'data' => $colaborador]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
        }
    } elseif ($campanaId) {
        // Construir query con filtros
        $sql = "
            SELECT c.*, u.nombre as usuario_nombre,
                   CONCAT_WS(' ', l.nombres, l.apellidos) as lider_nombre,
                   (SELECT COUNT(*) FROM colaboradores s WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id) as num_seguidores
            FROM colaboradores c
            LEFT JOIN usuarios u ON c.usuario_registro_id = u.id
            LEFT JOIN colaboradores l ON c.lider_directo = l.documento AND c.campana_id = l.campana_id
            WHERE c.campana_id = ?
        ";
        $params = [$campanaId];

        if ($search) {
            $sql .= " AND (CONCAT_WS(' ', c.nombres, c.apellidos) LIKE ? OR c.documento LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($perfil) {
            $sql .= " AND c.perfil = ?";
            $params[] = $perfil;
        }

        if ($nivel) {
            $sql .= " AND c.nivel_participacion = ?";
            $params[] = $nivel;
        }

        if ($municipio) {
            $sql .= " AND c.municipio = ?";
            $params[] = $municipio;
        }

        if ($barrio) {
            if (strpos($barrio, ',') !== false) {
                $barrios = array_map('trim', explode(',', $barrio));
                $placeholders = implode(',', array_fill(0, count($barrios), '?'));
                $sql .= " AND c.barrio IN ($placeholders)";
                $params = array_merge($params, $barrios);
            } else {
                $sql .= " AND c.barrio = ?";
                $params[] = $barrio;
            }
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $colaboradores = $stmt->fetchAll();

        // Calcular campos virtuales y filtrar por estado si es necesario
        $result = [];
        foreach ($colaboradores as $c) {
            $c['estado'] = calcularEstado($c['dato_potencial'], $c['dato_historico']);
            $c['grupo_etareo'] = calcularGrupoEtareo($c['fecha_nacimiento']);
            $c['nombre_completo'] = $c['nombres'] . ' ' . $c['apellidos'];

            // Filtrar por estado si se especificó
            if ($estado && $c['estado'] !== $estado) {
                continue;
            }

            $result[] = $c;
        }

        jsonResponse(['success' => true, 'data' => $result, 'count' => count($result)]);
    } else {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }
}

/**
 * GET: Listar líderes disponibles para selector
 */
function handleGetLideres($db)
{
    $campanaId = $_GET['campana_id'] ?? null;
    $search = $_GET['search'] ?? '';

    if (!$campanaId) {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }

    $lideres = [];

    // 1. Obtener el candidato de la campaña
    try {
        // Verificar si existe tabla o columna de candidatos
        // Asumimos tabla candidatos unida a campanas
        $stmt = $db->prepare("
            SELECT c.id, cand.nombres, cand.apellidos, cand.nombre_completo as nombre_completo_cand
            FROM campanas c
            LEFT JOIN candidatos cand ON c.candidato_id = cand.id
            WHERE c.id = ?
        ");
        $stmt->execute([$campanaId]);
        $campana = $stmt->fetch();

        if ($campana && ($campana['nombres'] || $campana['nombre_completo_cand'])) {
            $nombreCandidato = $campana['nombre_completo_cand'] ?: ($campana['nombres'] . ' ' . $campana['apellidos']);
            
            $lideres[] = [
                'documento' => 'CANDIDATO-' . $campana['id'],
                'nombres' => $nombreCandidato, // Nombres completos en nombres para mostrar
                'apellidos' => '',
                'perfil' => 'Candidato',
                'nombre_completo' => $nombreCandidato . ' (Candidato)'
            ];
        }
    } catch (Exception $e) {
        // Ignorar si falla query de candidatos (tabla no existe, etc)
    }

    // 2. Obtener líderes
    $sql = "
        SELECT documento, nombres, apellidos, perfil
        FROM colaboradores
        WHERE campana_id = ?
        AND (perfil LIKE '%Lider%' OR perfil LIKE '%Líder%' OR perfil LIKE '%Candidat%')
    ";
    $params = [$campanaId];

    if ($search) {
        $sql .= " AND (CONCAT_WS(' ', nombres, apellidos) LIKE ? OR documento LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY nombres, apellidos LIMIT 50";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $dbLideres = $stmt->fetchAll();

        foreach ($dbLideres as $l) {
            $l['nombre_completo'] = $l['nombres'] . ' ' . $l['apellidos'] . ' (' . $l['perfil'] . ')';
            $lideres[] = $l;
        }
    } catch (Exception $e) {
        // Log error
    }

    jsonResponse(['success' => true, 'data' => $lideres]);
}

/**
 * GET: Estadísticas de colaboradores
 */
function handleStats($db)
{
    $campanaId = $_GET['campana_id'] ?? null;

    if (!$campanaId) {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }

    // Total colaboradores
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM colaboradores WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $total = $stmt->fetch()['total'];

    // Por perfil
    $stmt = $db->prepare("
        SELECT perfil, COUNT(*) as cantidad
        FROM colaboradores
        WHERE campana_id = ?
        GROUP BY perfil
    ");
    $stmt->execute([$campanaId]);
    $porPerfil = $stmt->fetchAll();

    // Por nivel participación
    $stmt = $db->prepare("
        SELECT nivel_participacion, COUNT(*) as cantidad
        FROM colaboradores
        WHERE campana_id = ?
        GROUP BY nivel_participacion
    ");
    $stmt->execute([$campanaId]);
    $porNivel = $stmt->fetchAll();

    // Calcular por estado (requiere cálculo en PHP)
    $stmt = $db->prepare("SELECT dato_potencial, dato_historico FROM colaboradores WHERE campana_id = ?");
    $stmt->execute([$campanaId]);
    $todos = $stmt->fetchAll();

    $porEstado = ['Nuevo' => 0, 'Desvinculado' => 0, 'Crecio' => 0, 'Decrece' => 0, 'Igual' => 0];
    foreach ($todos as $c) {
        $estado = calcularEstado($c['dato_potencial'], $c['dato_historico']);
        $porEstado[$estado]++;
    }

    jsonResponse([
        'success' => true,
        'data' => [
            'total' => $total,
            'por_perfil' => $porPerfil,
            'por_nivel' => $porNivel,
            'por_estado' => $porEstado
        ]
    ]);
}

/**
 * POST: Crear nuevo colaborador
 */
function handlePost($db, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);

    // Validaciones
    $required = ['campana_id', 'nombres', 'apellidos', 'documento', 'fecha_nacimiento', 'genero', 'perfil', 'departamento', 'municipio'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            jsonResponse(['success' => false, 'message' => "Campo requerido: $field"], 400);
        }
    }

    // Verificar documento único en la campaña
    $stmt = $db->prepare("SELECT id FROM colaboradores WHERE documento = ? AND campana_id = ?");
    $stmt->execute([$data['documento'], $data['campana_id']]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Ya existe un colaborador con este documento en la campaña'], 400);
    }

    // Verificar líder directo si se especifica
    if (!empty($data['lider_directo'])) {
        $stmt = $db->prepare("SELECT documento FROM colaboradores WHERE documento = ? AND campana_id = ?");
        $stmt->execute([$data['lider_directo'], $data['campana_id']]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'El líder directo especificado no existe en esta campaña'], 400);
        }
    }

    // Insertar colaborador
    $stmt = $db->prepare("
        INSERT INTO colaboradores (
            campana_id, nombres, apellidos, tipo_documento, documento,
            fecha_nacimiento, genero, perfil, nivel_participacion, areas_interes,
            dato_potencial, dato_historico, departamento, municipio,
            tipo_territorio, territorio, barrio, territorio_id, direccion, detalle_ubicacion,
            lider_directo, email, telefono, telefono_whatsapp,
            puesto_votacion, mesa_votacion, foto,
            observaciones, usuario_registro_id
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?
        )
    ");

    $areasInteres = isset($data['areas_interes']) ? json_encode($data['areas_interes']) : null;

    $foto = isset($data['foto']) ? saveBase64Image($data['foto'], 'colab_' . $data['documento'] . '_') : null;

    $stmt->execute([
        $data['campana_id'],
        sanitize($data['nombres']),
        sanitize($data['apellidos']),
        $data['tipo_documento'] ?? 'CC',
        sanitize($data['documento']),
        $data['fecha_nacimiento'],
        $data['genero'],
        $data['perfil'],
        $data['nivel_participacion'] ?? 'Simpatizante',
        $areasInteres,
        $data['dato_potencial'] ?? 0,
        $data['dato_historico'] ?? 0,
        sanitize($data['departamento']),
        sanitize($data['municipio']),
        sanitize($data['tipo_territorio'] ?? null),
        sanitize($data['territorio'] ?? null),
        sanitize($data['barrio'] ?? null),
        $data['territorio_id'] ?? $data['cod_mpio'] ?? null,
        sanitize($data['direccion'] ?? null),
        sanitize($data['detalle_ubicacion'] ?? null),
        sanitize($data['lider_directo'] ?? null),
        sanitize($data['email'] ?? null),
        sanitize($data['telefono'] ?? null),
        sanitize($data['telefono_whatsapp'] ?? null),
        sanitize($data['puesto_votacion'] ?? null),
        sanitize($data['mesa_votacion'] ?? null),
        $foto,
        sanitize($data['observaciones'] ?? null),
        $userId
    ]);

    $colaboradorId = $db->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Colaborador creado exitosamente',
        'data' => ['id' => $colaboradorId]
    ], 201);
}

/**
 * PUT: Actualizar colaborador
 */
function handlePut($db, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    // Verificar que existe
    $stmt = $db->prepare("SELECT id, campana_id, documento FROM colaboradores WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
    }

    // Verificar documento único si cambió
    if (isset($data['documento']) && $data['documento'] !== $existing['documento']) {
        $stmt = $db->prepare("SELECT id FROM colaboradores WHERE documento = ? AND campana_id = ? AND id != ?");
        $stmt->execute([$data['documento'], $existing['campana_id'], $id]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Ya existe otro colaborador con este documento'], 400);
        }
    }

    // Verificar líder directo si se especifica
    if (!empty($data['lider_directo'])) {
        // No puede ser su propio líder
        if ($data['lider_directo'] === ($data['documento'] ?? $existing['documento'])) {
            jsonResponse(['success' => false, 'message' => 'Un colaborador no puede ser su propio líder'], 400);
        }

        $stmt = $db->prepare("SELECT documento FROM colaboradores WHERE documento = ? AND campana_id = ?");
        $stmt->execute([$data['lider_directo'], $existing['campana_id']]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'El líder directo especificado no existe en esta campaña'], 400);
        }
    }

    $areasInteres = isset($data['areas_interes']) ? json_encode($data['areas_interes']) : null;

    // Actualizar
    $stmt = $db->prepare("
        UPDATE colaboradores SET
            nombres = ?,
            apellidos = ?,
            tipo_documento = ?,
            documento = ?,
            fecha_nacimiento = ?,
            genero = ?,
            perfil = ?,
            nivel_participacion = ?,
            areas_interes = ?,
            dato_potencial = ?,
            dato_historico = ?,
            departamento = ?,
            municipio = ?,
            tipo_territorio = ?,
            territorio = ?,
            barrio = ?,
            territorio_id = ?,
            direccion = ?,
            detalle_ubicacion = ?,
            lider_directo = ?,
            email = ?,
            telefono = ?,
            telefono_whatsapp = ?,
            puesto_votacion = ?,
            mesa_votacion = ?,
            foto = COALESCE(?, foto),
            observaciones = ?
        WHERE id = ?
    ");

    $foto = isset($data['foto']) ? saveBase64Image($data['foto'], 'colab_' . ($data['documento'] ?? $existing['documento']) . '_') : null;

    $stmt->execute([
        sanitize($data['nombres']),
        sanitize($data['apellidos']),
        $data['tipo_documento'] ?? 'CC',
        sanitize($data['documento']),
        $data['fecha_nacimiento'],
        $data['genero'],
        $data['perfil'],
        $data['nivel_participacion'] ?? 'Simpatizante',
        $areasInteres,
        $data['dato_potencial'] ?? 0,
        $data['dato_historico'] ?? 0,
        sanitize($data['departamento']),
        sanitize($data['municipio']),
        sanitize($data['tipo_territorio'] ?? null),
        sanitize($data['territorio'] ?? null),
        sanitize($data['barrio'] ?? null),
        $data['territorio_id'] ?? $data['cod_mpio'] ?? null,
        sanitize($data['direccion'] ?? null),
        sanitize($data['detalle_ubicacion'] ?? null),
        sanitize($data['lider_directo'] ?? null),
        sanitize($data['email'] ?? null),
        sanitize($data['telefono'] ?? null),
        sanitize($data['telefono_whatsapp'] ?? null),
        sanitize($data['puesto_votacion'] ?? null),
        sanitize($data['mesa_votacion'] ?? null),
        $foto,
        sanitize($data['observaciones'] ?? null),
        $id
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Colaborador actualizado exitosamente'
    ]);
}

/**
 * DELETE: Eliminar colaborador
 */
function handleDelete($db, $userId)
{
    $id = $_GET['id'] ?? null;

    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    // Verificar que existe
    $stmt = $db->prepare("SELECT id, documento, campana_id FROM colaboradores WHERE id = ?");
    $stmt->execute([$id]);
    $colaborador = $stmt->fetch();
    if (!$colaborador) {
        jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
    }

    // Actualizar referencias de lider_directo a NULL
    $stmt = $db->prepare("UPDATE colaboradores SET lider_directo = NULL WHERE lider_directo = ? AND campana_id = ?");
    $stmt->execute([$colaborador['documento'], $colaborador['campana_id']]);

    // Eliminar
    $stmt = $db->prepare("DELETE FROM colaboradores WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse([
        'success' => true,
        'message' => 'Colaborador eliminado exitosamente'
    ]);
}

/**
 * POST: Importar colaboradores desde Excel
 */
function handleImport($db, $userId)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Método debe ser POST'], 405);
    }

    $campanaId = $_POST['campana_id'] ?? null;
    $liderDocumento = $_POST['lider_documento'] ?? null; // Documento del líder que sube

    if (!$campanaId) {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(['success' => false, 'message' => 'Archivo no recibido o error de subida'], 400);
    }

    $file = $_FILES['archivo'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
        jsonResponse(['success' => false, 'message' => 'Formato no soportado. Use xlsx, xls o csv'], 400);
    }

    // Mover archivo temporal
    $uploadDir = __DIR__ . '/../uploads/imports/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $tmpPath = $uploadDir . uniqid('import_') . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $tmpPath);

    // Procesar archivo
    $resultado = procesarArchivoImport($db, $tmpPath, $campanaId, $userId, $liderDocumento, $ext);

    if ($resultado['exitosos'] > 0) {
        cache_invalidate("colaboradores_*");
    }

    // Guardar log de importación
    $stmt = $db->prepare("
        INSERT INTO importaciones_colaboradores
        (campana_id, nombre_archivo, registros_procesados, registros_exitosos, registros_fallidos, errores, usuario_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $campanaId,
        $file['name'],
        $resultado['procesados'],
        $resultado['exitosos'],
        $resultado['fallidos'],
        json_encode($resultado['errores']),
        $userId
    ]);

    // Eliminar archivo temporal
    @unlink($tmpPath);

    jsonResponse([
        'success' => true,
        'message' => "Importación completada: {$resultado['exitosos']} exitosos, {$resultado['fallidos']} fallidos",
        'data' => $resultado
    ]);
}

/**
 * Procesar archivo de importación (CSV)
 */
function procesarArchivoImport($db, $filePath, $campanaId, $userId, $liderDocumento, $ext)
{
    $resultado = [
        'procesados' => 0,
        'exitosos' => 0,
        'fallidos' => 0,
        'errores' => []
    ];

    if ($ext === 'xlsx') {
        if (!class_exists('Shuchkin\SimpleXLSX')) {
            $resultado['errores'][] = ['fila' => 0, 'error' => 'Librería SimpleXLSX no encontrada'];
            return $resultado;
        }

        if ($xlsx = \Shuchkin\SimpleXLSX::parse($filePath)) {
            $headers = null;
            $map = [];

            foreach ($xlsx->rows() as $filaIndex => $row) {
                if ($filaIndex === 0) {
                    $headers = array_map('trim', array_map('strtolower', $row));
                    $map = array_flip($headers);
                    continue;
                }

                $resultado['procesados']++;
                $fila = $filaIndex + 1;

                try {
                    $idxLider = $map['lider_documento'] ?? $map['lider_directo'] ?? $map['lider'] ?? -1;
                    $data = [
                        'nombres' => $row[$map['nombres'] ?? -1] ?? null,
                        'apellidos' => $row[$map['apellidos'] ?? -1] ?? null,
                        'tipo_documento' => $row[$map['tipo_documento'] ?? -1] ?? 'CC',
                        'documento' => $row[$map['documento'] ?? -1] ?? null,
                        'fecha_nacimiento' => $row[$map['fecha_nacimiento'] ?? -1] ?? null,
                        'genero' => $row[$map['genero'] ?? -1] ?? null,
                        'perfil' => $row[$map['perfil'] ?? -1] ?? null,
                        'nivel_participacion' => $row[$map['nivel_participacion'] ?? -1] ?? 'Simpatizante',
                        'departamento' => $row[$map['departamento'] ?? -1] ?? null,
                        'municipio' => $row[$map['municipio'] ?? -1] ?? null,
                        'tipo_territorio' => $row[$map['tipo_territorio'] ?? -1] ?? null,
                        'territorio' => $row[$map['territorio'] ?? -1] ?? null,
                        'barrio' => $row[$map['barrio'] ?? -1] ?? null,
                        'dato_potencial' => intval($row[$map['dato_potencial'] ?? -1] ?? 0),
                        'dato_historico' => intval($row[$map['dato_historico'] ?? -1] ?? 0),
                        'puesto_votacion' => $row[$map['puesto_votacion'] ?? -1] ?? null,
                        'mesa_votacion' => $row[$map['mesa_votacion'] ?? -1] ?? null,
                        'lider_documento' => $row[$idxLider] ?? null,
                        'observaciones' => $row[$map['observaciones'] ?? -1] ?? null,
                    ];

                    if (empty($data['documento'])) continue;

                    procesarFilaUnica($db, $data, $campanaId, $userId, $liderDocumento, $resultado);

                } catch (Exception $e) {
                    $resultado['fallidos']++;
                    $resultado['errores'][] = ['fila' => $fila, 'error' => $e->getMessage()];
                }
            }
        } else {
            $resultado['errores'][] = ['fila' => 0, 'error' => \Shuchkin\SimpleXLSX::parseError()];
        }
    } elseif ($ext === 'csv') {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $resultado['errores'][] = ['fila' => 0, 'error' => 'No se pudo abrir el archivo'];
            return $resultado;
        }

        // Detectar delimitador (punto y coma o coma)
        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);

        // Omitir BOM si existe
        if (strpos($firstLine, "\xEF\xBB\xBF") === 0) {
            fseek($handle, 3);
        }

        // Leer encabezados
        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            $resultado['errores'][] = ['fila' => 0, 'error' => 'Archivo vacío o sin encabezados'];
            fclose($handle);
            return $resultado;
        }

        $headers = array_map('trim', array_map('strtolower', $headers));
        $map = array_flip($headers);

        $fila = 1;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $fila++;
            $resultado['procesados']++;

            try {
                $idxLider = $map['lider_documento'] ?? $map['lider_directo'] ?? $map['lider'] ?? -1;
                $data = [
                    'nombres' => $row[$map['nombres'] ?? -1] ?? null,
                    'apellidos' => $row[$map['apellidos'] ?? -1] ?? null,
                    'tipo_documento' => $row[$map['tipo_documento'] ?? -1] ?? 'CC',
                    'documento' => $row[$map['documento'] ?? -1] ?? null,
                    'fecha_nacimiento' => $row[$map['fecha_nacimiento'] ?? -1] ?? null,
                    'genero' => $row[$map['genero'] ?? -1] ?? null,
                    'perfil' => $row[$map['perfil'] ?? -1] ?? null,
                    'nivel_participacion' => $row[$map['nivel_participacion'] ?? -1] ?? 'Simpatizante',
                    'departamento' => $row[$map['departamento'] ?? -1] ?? null,
                    'municipio' => $row[$map['municipio'] ?? -1] ?? null,
                    'tipo_territorio' => $row[$map['tipo_territorio'] ?? -1] ?? null,
                    'territorio' => $row[$map['territorio'] ?? -1] ?? null,
                    'barrio' => $row[$map['barrio'] ?? -1] ?? null,
                    'dato_potencial' => intval($row[$map['dato_potencial'] ?? -1] ?? 0),
                    'dato_historico' => intval($row[$map['dato_historico'] ?? -1] ?? 0),
                    'puesto_votacion' => $row[$map['puesto_votacion'] ?? -1] ?? null,
                    'mesa_votacion' => $row[$map['mesa_votacion'] ?? -1] ?? null,
                    'lider_documento' => $row[$idxLider] ?? null,
                    'observaciones' => $row[$map['observaciones'] ?? -1] ?? null,
                ];

                if (empty($data['documento'])) continue;

                procesarFilaUnica($db, $data, $campanaId, $userId, $liderDocumento, $resultado);

            } catch (Exception $e) {
                $resultado['fallidos']++;
                $resultado['errores'][] = ['fila' => $fila, 'error' => $e->getMessage()];
            }
        }
        fclose($handle);
    }

    return $resultado;
}

// ============================================
// NUEVAS FUNCIONES - Fase 2: Integración Completa
// ============================================

/**
 * GET: Red jerárquica con soporte para Lazy Loading e Interactividad
 */
function handleNetwork($db)
{
    $campanaId = $_GET['campana_id'] ?? null;
    $rootDoc = $_GET['root_doc'] ?? null;
    $depth = isset($_GET['depth']) ? (int) $_GET['depth'] : 2;

    if (!$campanaId) {
        jsonResponse(['success' => false, 'message' => 'Requiere campana_id'], 400);
    }

    // Usar procedimiento almacenado si existe rootDoc (Lazy Loading/Focus), sino cargar red base completa
    if ($rootDoc) {
        // Cuando hay focus, solo mostrar la red de ese colaborador específico
        // Usar query recursiva CTE en lugar de stored procedure (que no existe en producción)
        $stmt = $db->prepare("
            WITH RECURSIVE red_jerarquica AS (
                -- Nodo raíz
                SELECT 
                    id, documento, nombres, apellidos, perfil, nivel_participacion,
                    dato_potencial, dato_historico, lider_directo, municipio,
                    0 as nivel_jerarquico
                FROM colaboradores
                WHERE documento = ? AND campana_id = ?
                
                UNION ALL
                
                -- Descendientes recursivos
                SELECT 
                    c.id, c.documento, c.nombres, c.apellidos, c.perfil, c.nivel_participacion,
                    c.dato_potencial, c.dato_historico, c.lider_directo, c.municipio,
                    rj.nivel_jerarquico + 1
                FROM colaboradores c
                INNER JOIN red_jerarquica rj ON c.lider_directo = rj.documento
                WHERE c.campana_id = ?
            )
            SELECT * FROM red_jerarquica
            ORDER BY nivel_jerarquico, nombres
        ");
        $stmt->execute([$rootDoc, $campanaId, $campanaId]);
        $colaboradores = $stmt->fetchAll();

        // NO agregar el líder directo cuando hay focus - solo mostrar la red del colaborador
        // Esto asegura que cada nodo vea únicamente su propia red jerárquica
    } else {
        // Carga inicial completa de la campaña (Para que las conexiones aparezcan de inmediato)
        $stmt = $db->prepare("
            SELECT id, documento, nombres, apellidos, perfil, nivel_participacion,
                   dato_potencial, dato_historico, lider_directo, municipio
            FROM colaboradores
            WHERE campana_id = ?
        ");
        $stmt->execute([$campanaId]);
        $colaboradores = $stmt->fetchAll();
    }

    $nodes = [];
    $edges = [];
    $documentoToId = []; // Para verificar existencia de nodos al crear aristas
    $perfilColors = [
        'Candidato' => '#FFD700',
        'Lider Comunitario' => '#FF00FF',
        'Lider Social' => '#3B82F6',
        'Lider Gremial' => '#10B981',
        'Activista' => '#F59E0B',
        'Simpatizante' => '#6B7280'
    ];

    // Verificar quiénes tienen seguidores para marcar expansibilidad visual (+)
    $stmtSeg = $db->prepare("
        SELECT lider_directo, COUNT(*) as total 
        FROM colaboradores 
        WHERE campana_id = ? AND lider_directo IS NOT NULL 
        GROUP BY lider_directo
    ");
    $stmtSeg->execute([$campanaId]);
    $seguidoresCount = [];
    $counts = $stmtSeg->fetchAll();
    foreach ($counts as $row) {
        $seguidoresCount[$row['lider_directo']] = $row['total'];
    }

    // Mapear documentos a IDs para verificar conexiones
    foreach ($colaboradores as $c) {
        $documentoToId[$c['documento']] = $c['id'];
    }

    foreach ($colaboradores as $c) {
        $numSeguidores = $seguidoresCount[$c['documento']] ?? 0;
        $estado = calcularEstado($c['dato_potencial'], $c['dato_historico']);

        // Calcular tamaño del nodo basado en seguidores (como en la versión "perfecta")
        if ($c['perfil'] === 'Candidato') {
            $nodeSize = 100; // Mucho más grande que el resto
            $mass = 5;      // Más masa para que sea "pesado" y central
        } else {
            $nodeSize = min(30 + ($numSeguidores * 3), 60);
            $mass = 1 + ($numSeguidores * 0.1); // Un poco más pesado si tiene seguidores
        }

        $nodes[] = [
            'id' => $c['documento'],
            'real_id' => $c['id'],
            'label' => $c['nombres'] . ' ' . mb_substr($c['apellidos'], 0, 1, 'UTF-8') . '.',
            'title' => "<b>" . htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) . "</b><br>" .
                "Perfil: " . htmlspecialchars($c['perfil']) . "<br>" .
                "Seguidores directos: " . $numSeguidores . "<br>" .
                "Potencial: " . ($c['dato_potencial'] ?? 0),
            'color' => $perfilColors[$c['perfil']] ?? '#6B7280',
            'perfil' => $c['perfil'],
            'hasChildren' => ($numSeguidores > 0),
            'seguidores' => $numSeguidores,
            'colaborador_id' => $c['id'],
            'estado' => $estado,
            'size' => $nodeSize,
            'mass' => $mass ?? 1
        ];

        // Añadir arista si tiene líder y el líder también está en este set de datos
        if (!empty($c['lider_directo']) && isset($documentoToId[$c['lider_directo']])) {
            $edges[] = [
                'from' => $c['lider_directo'],
                'to' => $c['documento'],
                'arrows' => 'to'
            ];
        }
    }

    // Calcular estadísticas de la red actual
    $totalNodos = count($nodes);
    $totalAristas = count($edges);
    $sinLiderCount = 0;

    
    // PHP no Tiene Set nativo como JS, usaremos un array asociativo para unicidad
    $lideresActivosMap = [];
    
    foreach ($colaboradores as $c) {
        if (empty($c['lider_directo'])) {
            $sinLiderCount++;
        } else {
            $lideresActivosMap[$c['lider_directo']] = true;
        }
    }
    
    $stats = [
        'total_nodos' => $totalNodos,
        'total_aristas' => $totalAristas,
        'lideres_activos' => count($lideresActivosMap),
        'sin_lider' => $sinLiderCount
    ];

    jsonResponse([
        'success' => true,
        'data' => [
            'nodes' => $nodes,
            'edges' => $edges,
            'stats' => $stats
        ]
    ]);
}

/**
 * GET: Seguidores directos de un colaborador
 */
function handleSeguidores($db)
{
    $colaboradorId = $_GET['id'] ?? null;

    if (!$colaboradorId) {
        jsonResponse(['success' => false, 'message' => 'ID de colaborador requerido'], 400);
    }

    // Obtener documento del colaborador
    $stmt = $db->prepare("SELECT documento, campana_id FROM colaboradores WHERE id = ?");
    $stmt->execute([$colaboradorId]);
    $colaborador = $stmt->fetch();

    if (!$colaborador) {
        jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
    }

    // Obtener seguidores directos
    $stmt = $db->prepare("
        SELECT id, documento, nombres, apellidos, perfil, nivel_participacion,
               dato_potencial, dato_historico, departamento, municipio, created_at
        FROM colaboradores
        WHERE lider_directo = ? AND campana_id = ?
        ORDER BY nombres, apellidos
    ");
    $stmt->execute([$colaborador['documento'], $colaborador['campana_id']]);
    $seguidores = $stmt->fetchAll();

    // Calcular campos virtuales
    foreach ($seguidores as &$s) {
        $s['estado'] = calcularEstado($s['dato_potencial'], $s['dato_historico']);
        $s['nombre_completo'] = $s['nombres'] . ' ' . $s['apellidos'];
    }

    // Calcular estadísticas adicionales para la vista "Gestión de Red" estilo Dashboard
    // 1. Obtener la red descendente total (Nivel 1 + Nivel N)
    $totalRed = count($seguidores); // Valor por defecto: Nivel 1. Se sobreescribe si se pueden leer más niveles

    // Calcular desglose de barrio/territorio
    $desgloseTerritorio = [];

    try {
        require_once __DIR__ . '/../mod_lider/src/Models/Colaborador.php';
        $colaboradorModel = new \App\Models\Colaborador();

        // Recursividad para la estructura descendente
        $downline = $colaboradorModel->getDownlineDocumentos($colaborador['documento']);
        $totalRed = count($downline);

        // Desglose de distribución barrial
        if (!empty($downline)) {
            $desgloseTerritorio = $colaboradorModel->getNeighborhoodBreakdown($downline);
        }

    } catch (\Exception $e) {
        // En caso de fallar (ej. la clase modelo no se encuentra en el autoloader), 
        // fallback al nivel básico (directo) sin romper la API
        $totalRed = count($seguidores);
        $downline = array_column($seguidores, 'documento');
    }

    // Retorno Extendido
    jsonResponse([
        'success' => true,
        'data' => $seguidores,
        'count' => count($seguidores),
        'stats' => [
            'total_directos' => count($seguidores),
            'total_red' => $totalRed,
            'desglose_territorio' => $desgloseTerritorio
        ]
    ]);
}

/**
 * GET: Historial de cambios de un colaborador (líder y estados)
 */
function handleHistorial($db)
{
    $colaboradorId = $_GET['id'] ?? null;

    if (!$colaboradorId) {
        jsonResponse(['success' => false, 'message' => 'ID de colaborador requerido'], 400);
    }

    $historial = [];

    // Historial de cambios de líder
    try {
        $stmt = $db->prepare("
            SELECT hcl.*, u.nombre as usuario_nombre,
                   la.nombres as lider_anterior_nombres, la.apellidos as lider_anterior_apellidos,
                   ln.nombres as lider_nuevo_nombres, ln.apellidos as lider_nuevo_apellidos
            FROM historial_cambios_lider hcl
            LEFT JOIN usuarios u ON hcl.usuario_cambio = u.id
            LEFT JOIN colaboradores la ON hcl.lider_anterior = la.documento AND hcl.campana_id = la.campana_id
            LEFT JOIN colaboradores ln ON hcl.lider_nuevo = ln.documento AND hcl.campana_id = ln.campana_id
            WHERE hcl.colaborador_id = ?
            ORDER BY hcl.created_at DESC
        ");
        $stmt->execute([$colaboradorId]);
        $cambiosLider = $stmt->fetchAll();

        foreach ($cambiosLider as $cl) {
            $historial[] = [
                'tipo' => 'cambio_lider',
                'fecha' => $cl['created_at'],
                'descripcion' => 'Cambio de líder directo',
                'detalle' => [
                    'lider_anterior' => $cl['lider_anterior'] ? "{$cl['lider_anterior_nombres']} {$cl['lider_anterior_apellidos']} ({$cl['lider_anterior']})" : 'Sin líder',
                    'lider_nuevo' => $cl['lider_nuevo'] ? "{$cl['lider_nuevo_nombres']} {$cl['lider_nuevo_apellidos']} ({$cl['lider_nuevo']})" : 'Sin líder'
                ],
                'motivo' => $cl['motivo'],
                'usuario' => $cl['usuario_nombre']
            ];
        }
    } catch (Exception $e) {
        // Tabla puede no existir aún
    }

    // Historial de estados
    try {
        $stmt = $db->prepare("
            SELECT he.*, u.nombre as usuario_nombre
            FROM historial_estados he
            LEFT JOIN usuarios u ON he.usuario_id = u.id
            WHERE he.colaborador_id = ?
            ORDER BY he.created_at DESC
        ");
        $stmt->execute([$colaboradorId]);
        $cambiosEstado = $stmt->fetchAll();

        foreach ($cambiosEstado as $ce) {
            $historial[] = [
                'tipo' => $ce['tipo_cambio'],
                'fecha' => $ce['created_at'],
                'descripcion' => $ce['tipo_cambio'] === 'reevaluacion' ? 'Reevaluación de potencial' : 'Actualización de datos',
                'detalle' => [
                    'dato_potencial' => "{$ce['dato_potencial_anterior']} → {$ce['dato_potencial_nuevo']}",
                    'dato_historico' => "{$ce['dato_historico_anterior']} → {$ce['dato_historico_nuevo']}",
                    'estado' => "{$ce['estado_anterior']} → {$ce['estado_nuevo']}"
                ],
                'motivo' => $ce['motivo'],
                'usuario' => $ce['usuario_nombre']
            ];
        }
    } catch (Exception $e) {
        // Tabla puede no existir aún
    }

    // Ordenar por fecha descendente
    usort($historial, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));

    jsonResponse([
        'success' => true,
        'data' => $historial
    ]);
}

/**
 * GET: Obtener curriculum de un colaborador
 */
function handleGetCurriculum($db)
{
    $colaboradorId = $_GET['id'] ?? null;

    if (!$colaboradorId) {
        jsonResponse(['success' => false, 'message' => 'ID de colaborador requerido'], 400);
    }

    try {
        $stmt = $db->prepare("SELECT * FROM curriculum WHERE colaborador_id = ?");
        $stmt->execute([$colaboradorId]);
        $curriculum = $stmt->fetch();

        if ($curriculum) {
            // Decodificar JSON
            $curriculum['experiencia_laboral'] = json_decode($curriculum['experiencia_laboral'] ?? '[]', true);
            $curriculum['formacion_academica'] = json_decode($curriculum['formacion_academica'] ?? '[]', true);
            $curriculum['participacion_politica'] = json_decode($curriculum['participacion_politica'] ?? '[]', true);
            $curriculum['hijos_data'] = json_decode($curriculum['hijos_data'] ?? '[]', true);
        } else {
            // Devolver estructura vacía
            $curriculum = [
                'colaborador_id' => $colaboradorId,
                'experiencia_laboral' => [],
                'formacion_academica' => [],
                'participacion_politica' => [],
                'resumen_profesional' => '',
                'habilidades' => '',
                'idiomas' => '',
                'reconocimientos' => '',
                'referencias' => '',
                'observaciones' => '',
                'hijos_data' => [],
                'hijos_discapacidad' => 0,
                'equipo_futbol' => '',
                'practica_deportiva' => ''
            ];
        }

        jsonResponse(['success' => true, 'data' => $curriculum]);
    } catch (Exception $e) {
        // Tabla puede no existir
        jsonResponse([
            'success' => true,
            'data' => [
                'colaborador_id' => $colaboradorId,
                'experiencia_laboral' => [],
                'formacion_academica' => [],
                'participacion_politica' => [],
                'hijos_data' => [],
                '_nota' => 'Tabla curriculum no disponible. Ejecute la migración.'
            ]
        ]);
    }
}

/**
 * POST: Guardar curriculum de un colaborador
 */
function handleSaveCurriculum($db, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $colaboradorId = $data['colaborador_id'] ?? null;

    if (!$colaboradorId) {
        jsonResponse(['success' => false, 'message' => 'ID de colaborador requerido'], 400);
    }

    // Verificar que el colaborador existe
    $stmt = $db->prepare("SELECT id FROM colaboradores WHERE id = ?");
    $stmt->execute([$colaboradorId]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
    }

    try {
        // Verificar si ya existe
        $stmt = $db->prepare("SELECT id FROM curriculum WHERE colaborador_id = ?");
        $stmt->execute([$colaboradorId]);
        $exists = $stmt->fetch();

        $experienciaLaboral = json_encode($data['experiencia_laboral'] ?? []);
        $formacionAcademica = json_encode($data['formacion_academica'] ?? []);
        $participacionPolitica = json_encode($data['participacion_politica'] ?? []);
        $resumenProfesional = $data['resumen_profesional'] ?? null;
        $habilidades = $data['habilidades'] ?? null;
        $idiomas = $data['idiomas'] ?? null;
        $reconocimientos = $data['reconocimientos'] ?? null;
        $referencias = $data['referencias'] ?? null;
        $observaciones = $data['observaciones'] ?? null;
        $hijosData = json_encode($data['hijos_data'] ?? []);
        $hijosDiscapacidad = isset($data['hijos_discapacidad']) && $data['hijos_discapacidad'] ? 1 : 0;
        $equipoFutbol = $data['equipo_futbol'] ?? null;
        $practicaDeportiva = $data['practica_deportiva'] ?? null;

        if ($exists) {
            // Actualizar
            $stmt = $db->prepare("
                UPDATE curriculum SET
                    experiencia_laboral = ?,
                    formacion_academica = ?,
                    participacion_politica = ?,
                    resumen_profesional = ?,
                    habilidades = ?,
                    idiomas = ?,
                    reconocimientos = ?,
                    referencias = ?,
                    observaciones = ?,
                    hijos_data = ?,
                    hijos_discapacidad = ?,
                    equipo_futbol = ?,
                    practica_deportiva = ?
                WHERE colaborador_id = ?
            ");
            $stmt->execute([
                $experienciaLaboral,
                $formacionAcademica,
                $participacionPolitica,
                $resumenProfesional,
                $habilidades,
                $idiomas,
                $reconocimientos,
                $referencias,
                $observaciones,
                $hijosData,
                $hijosDiscapacidad,
                $equipoFutbol,
                $practicaDeportiva,
                $colaboradorId
            ]);
        } else {
            // Insertar
            $stmt = $db->prepare("
                INSERT INTO curriculum (
                    colaborador_id,
                    experiencia_laboral,
                    formacion_academica,
                    participacion_politica,
                    resumen_profesional,
                    habilidades,
                    idiomas,
                    reconocimientos,
                    referencias,
                    observaciones,
                    hijos_data,
                    hijos_discapacidad,
                    equipo_futbol,
                    practica_deportiva
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $colaboradorId,
                $experienciaLaboral,
                $formacionAcademica,
                $participacionPolitica,
                $resumenProfesional,
                $habilidades,
                $idiomas,
                $reconocimientos,
                $referencias,
                $observaciones,
                $hijosData,
                $hijosDiscapacidad,
                $equipoFutbol,
                $practicaDeportiva
            ]);
        }

        jsonResponse(['success' => true, 'message' => 'Curriculum guardado exitosamente']);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error al guardar curriculum. Verifique que la tabla existe.'], 500);
    }
}

/**
 * POST: Cambiar líder directo con registro en historial
 */
function handleCambiarLider($db, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $colaboradorId = $data['colaborador_id'] ?? null;
    $nuevoLider = $data['nuevo_lider'] ?? null; // Puede ser null para quitar líder
    $motivo = $data['motivo'] ?? '';

    if (!$colaboradorId) {
        jsonResponse(['success' => false, 'message' => 'ID de colaborador requerido'], 400);
    }

    // Obtener colaborador actual
    $stmt = $db->prepare("SELECT id, documento, campana_id, lider_directo FROM colaboradores WHERE id = ?");
    $stmt->execute([$colaboradorId]);
    $colaborador = $stmt->fetch();

    if (!$colaborador) {
        jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
    }

    $liderAnterior = $colaborador['lider_directo'];

    // Validar nuevo líder si se especifica
    if ($nuevoLider) {
        // No puede ser su propio líder
        if ($nuevoLider === $colaborador['documento']) {
            jsonResponse(['success' => false, 'message' => 'Un colaborador no puede ser su propio líder'], 400);
        }

        // Verificar que el nuevo líder existe en la campaña
        $stmt = $db->prepare("SELECT documento FROM colaboradores WHERE documento = ? AND campana_id = ?");
        $stmt->execute([$nuevoLider, $colaborador['campana_id']]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'El nuevo líder no existe en esta campaña'], 400);
        }
    }

    try {
        $db->beginTransaction();

        // Actualizar líder directo
        $stmt = $db->prepare("UPDATE colaboradores SET lider_directo = ? WHERE id = ?");
        $stmt->execute([$nuevoLider, $colaboradorId]);

        // Registrar en historial
        try {
            $stmt = $db->prepare("
                INSERT INTO historial_cambios_lider
                (colaborador_id, colaborador_documento, lider_anterior, lider_nuevo, motivo, usuario_cambio, campana_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $colaboradorId,
                $colaborador['documento'],
                $liderAnterior,
                $nuevoLider,
                $motivo,
                $userId,
                $colaborador['campana_id']
            ]);
        } catch (Exception $e) {
            // Tabla puede no existir, continuar sin historial
        }

        $db->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Líder actualizado exitosamente',
            'data' => [
                'lider_anterior' => $liderAnterior,
                'lider_nuevo' => $nuevoLider
            ]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error al cambiar líder: ' . $e->getMessage()], 500);
    }
}

/**
 * POST: Reevaluar dato_potencial con registro en historial
 */
function handleReevaluar($db, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $colaboradorId = $data['colaborador_id'] ?? null;
    $nuevoPotencial = $data['dato_potencial'] ?? null;
    $motivo = $data['motivo'] ?? '';

    if (!$colaboradorId || $nuevoPotencial === null) {
        jsonResponse(['success' => false, 'message' => 'ID de colaborador y dato_potencial requeridos'], 400);
    }

    // Obtener colaborador actual
    $stmt = $db->prepare("SELECT id, campana_id, dato_potencial, dato_historico FROM colaboradores WHERE id = ?");
    $stmt->execute([$colaboradorId]);
    $colaborador = $stmt->fetch();

    if (!$colaborador) {
        jsonResponse(['success' => false, 'message' => 'Colaborador no encontrado'], 404);
    }

    $potencialAnterior = $colaborador['dato_potencial'];
    $historicoAnterior = $colaborador['dato_historico'];
    $estadoAnterior = calcularEstado($potencialAnterior, $historicoAnterior);

    // El dato_historico se actualiza al valor anterior del potencial
    $nuevoHistorico = $potencialAnterior;
    $estadoNuevo = calcularEstado($nuevoPotencial, $nuevoHistorico);

    try {
        $db->beginTransaction();

        // Actualizar colaborador
        $stmt = $db->prepare("UPDATE colaboradores SET dato_potencial = ?, dato_historico = ? WHERE id = ?");
        $stmt->execute([$nuevoPotencial, $nuevoHistorico, $colaboradorId]);

        // Registrar en historial
        try {
            $stmt = $db->prepare("
                INSERT INTO historial_estados
                (colaborador_id, campana_id, dato_potencial_anterior, dato_potencial_nuevo,
                 dato_historico_anterior, dato_historico_nuevo, estado_anterior, estado_nuevo,
                 motivo, tipo_cambio, usuario_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'reevaluacion', ?)
            ");
            $stmt->execute([
                $colaboradorId,
                $colaborador['campana_id'],
                $potencialAnterior,
                $nuevoPotencial,
                $historicoAnterior,
                $nuevoHistorico,
                $estadoAnterior,
                $estadoNuevo,
                $motivo,
                $userId
            ]);
        } catch (Exception $e) {
            // Tabla puede no existir
        }

        $db->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Reevaluación realizada exitosamente',
            'data' => [
                'dato_potencial_anterior' => $potencialAnterior,
                'dato_potencial_nuevo' => $nuevoPotencial,
                'dato_historico' => $nuevoHistorico,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo
            ]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error en reevaluación: ' . $e->getMessage()], 500);
    }
}

/**
 * GET: Reportes de colaboradores
 */
function handleReportes($db)
{
    $campanaId = $_GET['campana_id'] ?? null;
    $tipo = $_GET['tipo'] ?? 'general';

    if (!$campanaId) {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }

    $data = [];

    switch ($tipo) {
        case 'perfil':
            // Distribución por perfil
            $stmt = $db->prepare("
                SELECT perfil, COUNT(*) as cantidad,
                       AVG(dato_potencial) as promedio_potencial
                FROM colaboradores WHERE campana_id = ?
                GROUP BY perfil ORDER BY cantidad DESC
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'nivel':
            // Distribución por nivel de participación
            $stmt = $db->prepare("
                SELECT nivel_participacion, COUNT(*) as cantidad
                FROM colaboradores WHERE campana_id = ?
                GROUP BY nivel_participacion ORDER BY cantidad DESC
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'territorio':
            // Distribución por territorio
            $stmt = $db->prepare("
                SELECT departamento, municipio, COUNT(*) as cantidad
                FROM colaboradores WHERE campana_id = ?
                GROUP BY departamento, municipio
                ORDER BY cantidad DESC
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'lideres':
            // Top líderes con más seguidores
            $stmt = $db->prepare("
                SELECT l.id, l.documento, l.nombres, l.apellidos, l.perfil, l.municipio,
                       COUNT(s.id) as total_seguidores
                FROM colaboradores l
                INNER JOIN colaboradores s ON l.documento = s.lider_directo AND l.campana_id = s.campana_id
                WHERE l.campana_id = ?
                GROUP BY l.id, l.documento, l.nombres, l.apellidos, l.perfil, l.municipio
                ORDER BY total_seguidores DESC
                LIMIT 20
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'crecimiento':
            // Colaboradores con mejor crecimiento
            $stmt = $db->prepare("
                SELECT id, documento, nombres, apellidos, perfil,
                       dato_potencial, dato_historico,
                       (dato_historico - dato_potencial) as crecimiento
                FROM colaboradores
                WHERE campana_id = ? AND dato_historico > dato_potencial
                ORDER BY crecimiento DESC
                LIMIT 20
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'genero':
            // Distribución por género
            $stmt = $db->prepare("
                SELECT genero, COUNT(*) as cantidad
                FROM colaboradores WHERE campana_id = ?
                GROUP BY genero ORDER BY cantidad DESC
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'edad':
            // Distribución por rango de edad
            // Usamos una lógica similar a calcularGrupoEtareo pero en SQL
            $stmt = $db->prepare("
                SELECT 
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) <= 17 THEN 'Joven (0-17)'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) <= 28 THEN 'Juventud (18-28)'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) <= 40 THEN 'Adulto Joven (29-40)'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) <= 60 THEN 'Adulto (41-60)'
                        ELSE 'Mayor (61+)'
                    END as rango_edad,
                    COUNT(*) as cantidad
                FROM colaboradores 
                WHERE campana_id = ? AND fecha_nacimiento IS NOT NULL AND fecha_nacimiento != '0000-00-00'
                GROUP BY rango_edad
                ORDER BY MIN(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()))
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'territorio_detalle':
            // Distribución detallada por municipio, comuna/territorio y barrio
            $stmt = $db->prepare("
                SELECT municipio, tipo_territorio, territorio, barrio, COUNT(*) as cantidad
                FROM colaboradores WHERE campana_id = ?
                GROUP BY municipio, tipo_territorio, territorio, barrio
                ORDER BY cantidad DESC
                LIMIT 100
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'lideres_detalle':
            // Análisis de líderes por territorio
            $stmt = $db->prepare("
                SELECT municipio, territorio, barrio, COUNT(*) as total_lideres
                FROM colaboradores 
                WHERE campana_id = ? AND perfil LIKE '%Lider%'
                GROUP BY municipio, territorio, barrio
                ORDER BY total_lideres DESC
            ");
            $stmt->execute([$campanaId]);
            $data = $stmt->fetchAll();
            break;

        case 'general':
        default:
            // Resumen general
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM colaboradores WHERE campana_id = ?");
            $stmt->execute([$campanaId]);
            $total = $stmt->fetch()['total'];

            // Por estado (calculado)
            $stmt = $db->prepare("SELECT dato_potencial, dato_historico FROM colaboradores WHERE campana_id = ?");
            $stmt->execute([$campanaId]);
            $todos = $stmt->fetchAll();

            $porEstado = ['Nuevo' => 0, 'Desvinculado' => 0, 'Crecio' => 0, 'Decrece' => 0, 'Igual' => 0];
            foreach ($todos as $c) {
                $estado = calcularEstado($c['dato_potencial'], $c['dato_historico']);
                $porEstado[$estado]++;
            }

            // Líderes sin seguidores
            $stmt = $db->prepare("
                SELECT COUNT(*) as sin_seguidores
                FROM colaboradores c
                WHERE c.campana_id = ?
                AND c.perfil LIKE '%Lider%'
                AND NOT EXISTS (
                    SELECT 1 FROM colaboradores s
                    WHERE s.lider_directo = c.documento AND s.campana_id = c.campana_id
                )
            ");
            $stmt->execute([$campanaId]);
            $sinSeguidores = $stmt->fetch()['sin_seguidores'];

            // Colaboradores sin líder
            $stmt = $db->prepare("
                SELECT COUNT(*) as sin_lider
                FROM colaboradores
                WHERE campana_id = ? AND (lider_directo IS NULL OR lider_directo = '')
            ");
            $stmt->execute([$campanaId]);
            $sinLider = $stmt->fetch()['sin_lider'];

            $data = [
                'total' => $total,
                'por_estado' => $porEstado,
                'lideres_sin_seguidores' => $sinSeguidores,
                'colaboradores_sin_lider' => $sinLider
            ];
    }

    jsonResponse(['success' => true, 'tipo' => $tipo, 'data' => $data]);
}

/**
 * GET: Exportar colaboradores a CSV/Excel
 */
function handleExport($db)
{
    $campanaId = $_GET['campana_id'] ?? null;
    $formato = $_GET['formato'] ?? 'xlsx';

    if (!$campanaId) {
        jsonResponse(['success' => false, 'message' => 'ID de campaña requerido'], 400);
    }

    // Obtener datos
    $stmt = $db->prepare("
        SELECT c.*,
               l.nombres as lider_nombres, l.apellidos as lider_apellidos
        FROM colaboradores c
        LEFT JOIN colaboradores l ON c.lider_directo = l.documento AND c.campana_id = l.campana_id
        WHERE c.campana_id = ?
        ORDER BY c.nombres, c.apellidos
    ");
    $stmt->execute([$campanaId]);
    $colaboradores = $stmt->fetchAll();

    // Calcular campos virtuales
    foreach ($colaboradores as &$c) {
        $c['estado'] = calcularEstado($c['dato_potencial'], $c['dato_historico']);
        $c['grupo_etareo'] = calcularGrupoEtareo($c['fecha_nacimiento']);
        $c['lider_nombre'] = trim(($c['lider_nombres'] ?? '') . ' ' . ($c['lider_apellidos'] ?? ''));
    }

    if ($formato === 'xlsx') {
        if (!class_exists('Shuchkin\SimpleXLSXGen')) {
            jsonResponse(['success' => false, 'message' => 'Librería SimpleXLSXGen no encontrada'], 500);
        }

        $items = [
            ['Nombres', 'Apellidos', 'Tipo Doc', 'Documento', 'Fecha Nacimiento', 'Género', 'Perfil', 'Nivel Participación', 'Estado', 'Grupo Etáreo', 'Dato Potencial', 'Dato Histórico', 'Departamento', 'Municipio', 'Tipo Territorio', 'Territorio', 'Barrio', 'Dirección', 'Detalle Ubicación', 'Líder Directo', 'Nombre Líder', 'Email', 'Teléfono', 'WhatsApp', 'Observaciones', 'Fecha Registro']
        ];

        foreach ($colaboradores as $c) {
            $items[] = [
                $c['nombres'],
                $c['apellidos'],
                $c['tipo_documento'],
                $c['documento'],
                $c['fecha_nacimiento'],
                $c['genero'],
                $c['perfil'],
                $c['nivel_participacion'],
                $c['estado'],
                $c['grupo_etareo'],
                $c['dato_potencial'],
                $c['dato_historico'],
                $c['departamento'],
                $c['municipio'],
                $c['tipo_territorio'],
                $c['territorio'],
                $c['barrio'],
                $c['direccion'] ?? '',
                $c['detalle_ubicacion'] ?? '',
                $c['lider_directo'],
                $c['lider_nombre'],
                $c['email'],
                $c['telefono'],
                $c['telefono_whatsapp'] ?? '',
                $c['observaciones'],
                $c['created_at']
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($items)->downloadAs('colaboradores_' . date('Y-m-d') . '.xlsx');
        exit;

    } elseif ($formato === 'csv') {
        // Generar CSV mejorado (punto y coma y BOM)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="colaboradores_' . date('Y-m-d') . '.csv"');

        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');

        // Encabezados
        fputcsv($output, ['Nombres', 'Apellidos', 'Tipo Doc', 'Documento', 'Fecha Nacimiento', 'Género', 'Perfil', 'Nivel Participación', 'Estado', 'Grupo Etáreo', 'Dato Potencial', 'Dato Histórico', 'Departamento', 'Municipio', 'Tipo Territorio', 'Territorio', 'Barrio', 'Dirección', 'Detalle Ubicación', 'Líder Directo', 'Nombre Líder', 'Email', 'Teléfono', 'WhatsApp', 'Observaciones', 'Fecha Registro'], ';');

        foreach ($colaboradores as $c) {
            fputcsv($output, [
                $c['nombres'], $c['apellidos'], $c['tipo_documento'], $c['documento'], $c['fecha_nacimiento'],
                $c['genero'], $c['perfil'], $c['nivel_participacion'], $c['estado'], $c['grupo_etareo'],
                $c['dato_potencial'], $c['dato_historico'], $c['departamento'], $c['municipio'],
                $c['tipo_territorio'], $c['territorio'], $c['barrio'], $c['direccion'] ?? '',
                $c['detalle_ubicacion'] ?? '', $c['lider_directo'], $c['lider_nombre'], $c['email'],
                $c['telefono'], $c['telefono_whatsapp'] ?? '', $c['observaciones'], $c['created_at']
            ], ';');
        }
        fclose($output);
        exit;
    }
}

function handlePlantillaXlsx()
{
    if (!class_exists('Shuchkin\SimpleXLSXGen')) {
        jsonResponse(['success' => false, 'message' => 'Librería SimpleXLSXGen no encontrada'], 500);
    }

    $items = [
        ['nombres', 'apellidos', 'tipo_documento', 'documento', 'fecha_nacimiento', 'genero', 'perfil', 'nivel_participacion', 'dato_potencial', 'dato_historico', 'departamento', 'municipio', 'tipo_territorio', 'territorio', 'barrio', 'puesto_votacion', 'mesa_votacion', 'lider_documento', 'observaciones'],
        ['Juan', 'Pérez García', 'CC', '12345678', '1985-06-15', 'Masculino', 'Lider Comunitario', 'Aportante', '50', '45', 'Valle del Cauca', 'Cali', 'Comuna', 'Comuna 5', 'Barrio El Poblado', 'Lugar Colegio X', '14', '', 'Ejemplo de observación']
    ];

    \Shuchkin\SimpleXLSXGen::fromArray($items)->downloadAs('plantilla_colaboradores.xlsx');
    exit;
}

/**
 * Función auxiliar para procesar una fila de importación
 */
function procesarFilaUnica($db, $data, $campanaId, $userId, $liderDocumento, &$resultado)
{
    $documento = sanitize($data['documento']);

    // Verificar existencia
    $stmt = $db->prepare("SELECT id FROM colaboradores WHERE documento = ? AND campana_id = ?");
    $stmt->execute([$documento, $campanaId]);
    $existing = $stmt->fetch();

    $liderFinal = !empty($data['lider_documento']) ? sanitize($data['lider_documento']) : $liderDocumento;

    if ($existing) {
        $stmtUpdate = $db->prepare("
            UPDATE colaboradores SET
                nombres = COALESCE(?, nombres),
                apellidos = COALESCE(?, apellidos),
                lider_directo = ?,
                perfil = COALESCE(?, perfil),
                nivel_participacion = COALESCE(?, nivel_participacion),
                departamento = COALESCE(?, departamento),
                municipio = COALESCE(?, municipio),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUpdate->execute([
            !empty($data['nombres']) ? sanitize($data['nombres']) : null,
            !empty($data['apellidos']) ? sanitize($data['apellidos']) : null,
            $liderFinal,
            !empty($data['perfil']) ? sanitize($data['perfil']) : null,
            !empty($data['nivel_participacion']) ? sanitize($data['nivel_participacion']) : null,
            !empty($data['departamento']) ? sanitize($data['departamento']) : null,
            !empty($data['municipio']) ? sanitize($data['municipio']) : null,
            $existing['id']
        ]);
        $resultado['exitosos']++;
    } else {
        $required = ['nombres', 'apellidos', 'documento'];
        foreach ($required as $field) {
            if (empty($data[$field])) throw new Exception("Campo requerido vacío para nuevo registro: $field");
        }

        $stmtInsert = $db->prepare("
            INSERT INTO colaboradores (
                campana_id, nombres, apellidos, tipo_documento, documento,
                fecha_nacimiento, genero, perfil, nivel_participacion,
                dato_potencial, dato_historico, departamento, municipio,
                tipo_territorio, territorio, barrio, lider_directo,
                puesto_votacion, mesa_votacion,
                observaciones, usuario_registro_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInsert->execute([
            $campanaId, sanitize($data['nombres']), sanitize($data['apellidos']),
            $data['tipo_documento'], $documento, $data['fecha_nacimiento'], $data['genero'],
            $data['perfil'], $data['nivel_participacion'], $data['dato_potencial'],
            $data['dato_historico'], sanitize($data['departamento']), sanitize($data['municipio']),
            sanitize($data['tipo_territorio']), sanitize($data['territorio']), sanitize($data['barrio']),
            $liderFinal, sanitize($data['puesto_votacion']), sanitize($data['mesa_votacion']),
            sanitize($data['observaciones']), $userId
        ]);
        $resultado['exitosos']++;
    }
}
// La función saveBase64Image ha sido movida a config/config.php para uso global

