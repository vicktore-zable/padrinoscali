<?php
/**
 * API: Leader Network Data
 * Endpoint dedicado para el grafo vis.js del Portal del Líder
 * Retorna nodos y aristas en formato vis.js
 *
 * GET /aratio/api/leader-network.php
 * Requiere sesión de líder activa
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar sesión de líder
if (!isset($_SESSION['user']) || ($_SESSION['user']['tipo_usuario'] ?? '') !== 'lider') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado', 'redirect' => '?page=portal_landing']);
    exit;
}

$colaboradorId = $_SESSION['user']['colaborador_id'] ?? null;
if (!$colaboradorId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No se encontró el líder']);
    exit;
}

try {
    $db = getDB();

    // Obtener datos del líder
    $stmtLider = $db->prepare("SELECT * FROM colaboradores WHERE id = ?");
    $stmtLider->execute([$colaboradorId]);
    $lider = $stmtLider->fetch();
    if (!$lider) {
        echo json_encode(['success' => false, 'message' => 'Líder no encontrado']);
        exit;
    }

    $liderDocumento = $lider['documento'];
    $campanaId = $lider['campana_id'] ?? null;

    // Consulta recursiva CTE: obtener toda la red descendiente del líder
    $sql = "
        WITH RECURSIVE red_jerarquica AS (
            SELECT 
                id, documento, nombres, apellidos, perfil, nivel_participacion,
                dato_potencial, dato_historico, lider_directo, municipio, barrio,
                0 as nivel_jerarquico
            FROM colaboradores
            WHERE documento = ? AND campana_id = ?
            
            UNION ALL
            
            SELECT 
                c.id, c.documento, c.nombres, c.apellidos, c.perfil, c.nivel_participacion,
                c.dato_potencial, c.dato_historico, c.lider_directo, c.municipio, c.barrio,
                rj.nivel_jerarquico + 1
            FROM colaboradores c
            INNER JOIN red_jerarquica rj ON c.lider_directo = rj.documento
            WHERE c.campana_id = ?
        )
        SELECT * FROM red_jerarquica
        ORDER BY nivel_jerarquico, nombres
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([$liderDocumento, $campanaId, $campanaId]);
    $colaboradores = $stmt->fetchAll();

    // Contar seguidores directos de cada nodo
    $stmtSeg = $db->prepare("
        SELECT lider_directo, COUNT(*) as total 
        FROM colaboradores 
        WHERE campana_id = ? AND lider_directo IS NOT NULL 
        GROUP BY lider_directo
    ");
    $stmtSeg->execute([$campanaId]);
    $seguidoresCount = [];
    foreach ($stmtSeg->fetchAll() as $row) {
        $seguidoresCount[$row['lider_directo']] = $row['total'];
    }

    // Construir nodos y aristas para vis.js
    $nodes = [];
    $edges = [];
    $documentoToId = [];
    $miembros = [];

    $perfilColors = [
        'Candidato' => '#FFD700',
        'Lider Comunitario' => '#FF00FF',
        'Lider Social' => '#3B82F6',
        'Lider Gremial' => '#10B981',
        'Activista' => '#F59E0B',
        'Simpatizante' => '#6B7280'
    ];

    foreach ($colaboradores as $c) {
        $documentoToId[$c['documento']] = $c['id'];
        $numSeguidores = $seguidoresCount[$c['documento']] ?? 0;

        // El líder raíz es más grande
        $esLiderRaiz = ($c['documento'] === $liderDocumento);
        $nodeSize = $esLiderRaiz ? 80 : min(25 + ($numSeguidores * 3), 50);
        $mass = $esLiderRaiz ? 5 : 1 + ($numSeguidores * 0.1);

        // Color del nodo
        $color = $perfilColors[$c['perfil']] ?? '#6B7280';
        if ($esLiderRaiz) $color = '#002244';

        $nodes[] = [
            'id' => $c['documento'],
            'real_id' => $c['id'],
            'label' => $c['nombres'] . ' ' . mb_substr($c['apellidos'], 0, 1, 'UTF-8') . '.',
            'title' => "<b>" . htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) . "</b><br>" .
                "Perfil: " . htmlspecialchars($c['perfil'] ?? 'N/A') . "<br>" .
                "Seguidores: " . $numSeguidores . "<br>" .
                "Nivel: " . $c['nivel_jerarquico'],
            'color' => $color,
            'perfil' => $c['perfil'],
            'level' => $c['nivel_jerarquico'],
            'hasChildren' => ($numSeguidores > 0),
            'seguidores' => $numSeguidores,
            'colaborador_id' => $c['id'],
            'size' => $nodeSize,
            'mass' => $mass,
            'font' => $esLiderRaiz ? ['size' => 14, 'color' => '#ffffff', 'face' => 'Inter'] : ['size' => 11, 'color' => '#ffffff', 'face' => 'Inter']
        ];

        // Arista (conexión al líder)
        if (!empty($c['lider_directo']) && isset($documentoToId[$c['lider_directo']])) {
            $edges[] = [
                'from' => $c['lider_directo'],
                'to' => $c['documento'],
                'arrows' => 'to',
                'color' => ['color' => '#cbd5e1', 'highlight' => '#002244'],
                'smooth' => ['type' => 'cubicBezier', 'forceDirection' => 'vertical', 'roundness' => 0.4]
            ];
        }

        // Datos para la tabla de miembros
        $miembros[] = [
            'id' => $c['id'],
            'documento' => $c['documento'],
            'nombres' => $c['nombres'],
            'apellidos' => $c['apellidos'],
            'perfil' => $c['perfil'] ?? '',
            'nivel_participacion' => $c['nivel_participacion'] ?? '',
            'nivel' => $c['nivel_jerarquico'],
            'municipio' => $c['municipio'] ?? '',
            'barrio' => $c['barrio'] ?? '',
            'seguidores' => $numSeguidores,
            'dato_potencial' => $c['dato_potencial'] ?? 0,
            'lider_directo' => $c['lider_directo'] ?? ''
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'nodes'   => $nodes,
            'edges'   => $edges,
            'miembros'=> $miembros,
            'stats'   => [
                'total_nodos' => count($nodes),
                'total_aristas'=> count($edges),
                'lider_documento' => $liderDocumento
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("Leader network error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar la red']);
}
