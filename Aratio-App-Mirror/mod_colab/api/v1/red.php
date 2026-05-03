<?php
/**
 * API Endpoint: Red Jerárquica
 *
 * Endpoints para obtener datos de la red jerárquica de colaboradores
 * para visualización con Vis.js
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Respuesta JSON estandarizada
 */
function jsonResponse($success, $data = null, $message = '', $errors = []) {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Obtener parámetro de la URL
 */
function getParam($name, $default = null) {
    return $_GET[$name] ?? $default;
}

/**
 * Obtener color según perfil
 */
function getColorPerfil($perfil) {
    $colores = [
        'Lider Comunitario' => '#3b82f6',  // Azul
        'Lider Social' => '#10b981',        // Verde
        'Lider Ambiental' => '#22c55e',     // Verde claro
        'Lider Gremial' => '#f59e0b',       // Ámbar
        'Lider Empresarial' => '#8b5cf6',   // Púrpura
        'Lider Juvenil' => '#ec4899',       // Rosa
        'Influencer' => '#ef4444',          // Rojo
        'Lider Poblacional' => '#06b6d4',   // Cyan
        'Familia' => '#84cc16',             // Lima
        'Amigo' => '#14b8a6',               // Teal
        'Simpatizante' => '#94a3b8',        // Gris
        'Activista de Opinión' => '#f97316' // Naranja
    ];
    return $colores[$perfil] ?? '#64748b';
}

/**
 * Obtener tamaño del nodo según nivel de participación
 */
function getSizeNivel($nivel) {
    $sizes = [
        'Movilizador' => 35,
        'Activista de Opinión' => 28,
        'Aportante' => 25,
        'Simpatizante' => 18,
        'Inactivo' => 15
    ];
    return $sizes[$nivel] ?? 20;
}

/**
 * Obtener forma del nodo según nivel de participación
 */
function getShapeNivel($nivel) {
    $shapes = [
        'Movilizador' => 'star',
        'Activista de Opinión' => 'diamond',
        'Aportante' => 'square',
        'Simpatizante' => 'dot',
        'Inactivo' => 'dot'
    ];
    return $shapes[$nivel] ?? 'dot';
}

/**
 * Obtener color de la conexión según estado
 */
function getColorEstado($estado) {
    $colores = [
        'Activo' => '#10b981',      // Verde
        'Potencial' => '#f59e0b',   // Ámbar
        'Inactivo' => '#94a3b8'     // Gris
    ];
    return $colores[$estado] ?? '#64748b';
}

// ============================================
// ROUTER
// ============================================

$action = getParam('action', 'grafo');
$db = Database::getInstance();

try {
    switch ($action) {
        case 'grafo':
            // Obtener red jerárquica completa o de un líder específico
            $documento = getParam('documento');
            $niveles = (int)getParam('niveles', 5); // Niveles de profundidad

            if (!$documento) {
                jsonResponse(false, null, 'Documento de líder requerido', ['documento' => 'Campo requerido']);
            }

            // Obtener datos de la red usando stored procedure
            $sql = "CALL sp_obtener_red_jerarquica(?)";
            $result = $db->query($sql, [$documento]);

            if (!$result) {
                jsonResponse(false, null, 'No se encontró el líder especificado');
            }

            $colaboradores = $result->fetchAll(PDO::FETCH_ASSOC);

            // Construir nodos y aristas para Vis.js
            $nodes = [];
            $edges = [];
            $nodeMap = [];

            foreach ($colaboradores as $colab) {
                $nodeId = $colab['documento'];

                // Calcular cantidad de seguidores directos
                $sqlSeguidores = "SELECT COUNT(*) as total FROM colaboradores WHERE lider_directo = ?";
                $seguidoresResult = $db->fetchOne($sqlSeguidores, [$nodeId]);
                $totalSeguidores = $seguidoresResult['total'] ?? 0;

                // Crear nodo
                $node = [
                    'id' => $nodeId,
                    'label' => $colab['nombres'] . ' ' . $colab['apellidos'],
                    'title' => sprintf(
                        "<b>%s %s</b><br>Documento: %s<br>Perfil: %s<br>Nivel: %s<br>Estado: %s<br>Seguidores: %d<br>Potencial: %d",
                        $colab['nombres'],
                        $colab['apellidos'],
                        $colab['documento'],
                        $colab['perfil'],
                        $colab['nivel_participacion'],
                        $colab['estado'],
                        $totalSeguidores,
                        $colab['dato_potencial']
                    ),
                    'shape' => getShapeNivel($colab['nivel_participacion']),
                    'size' => getSizeNivel($colab['nivel_participacion']) + ($totalSeguidores * 2), // Tamaño basado en seguidores
                    'color' => [
                        'background' => getColorPerfil($colab['perfil']),
                        'border' => '#1e293b',
                        'highlight' => [
                            'background' => getColorPerfil($colab['perfil']),
                            'border' => '#fbbf24'
                        ]
                    ],
                    'font' => [
                        'color' => '#ffffff',
                        'size' => 14
                    ],
                    'borderWidth' => 2,
                    'nivel_jerarquico' => (int)$colab['nivel_jerarquico'],
                    'perfil' => $colab['perfil'],
                    'estado' => $colab['estado'],
                    'dato_potencial' => (int)$colab['dato_potencial'],
                    'seguidores' => $totalSeguidores
                ];

                $nodes[] = $node;
                $nodeMap[$nodeId] = true;

                // Crear arista si tiene líder
                if ($colab['lider_directo']) {
                    $edge = [
                        'from' => $colab['lider_directo'],
                        'to' => $nodeId,
                        'arrows' => 'to',
                        'color' => [
                            'color' => getColorEstado($colab['estado']),
                            'highlight' => '#fbbf24',
                            'hover' => '#fbbf24'
                        ],
                        'width' => max(1, (int)($colab['dato_potencial'] / 100)), // Ancho según potencial
                        'smooth' => [
                            'type' => 'cubicBezier',
                            'forceDirection' => 'vertical',
                            'roundness' => 0.4
                        ]
                    ];
                    $edges[] = $edge;
                }
            }

            jsonResponse(true, [
                'nodes' => $nodes,
                'edges' => $edges,
                'stats' => [
                    'total_nodos' => count($nodes),
                    'total_conexiones' => count($edges),
                    'niveles' => max(array_column($colaboradores, 'nivel_jerarquico'))
                ]
            ], 'Red jerárquica obtenida exitosamente');
            break;

        case 'metricas':
            // Obtener métricas de la red de un líder
            $documento = getParam('documento');

            if (!$documento) {
                jsonResponse(false, null, 'Documento de líder requerido', ['documento' => 'Campo requerido']);
            }

            // Obtener total de seguidores directos
            $sqlDirectos = "SELECT COUNT(*) as total FROM colaboradores WHERE lider_directo = ?";
            $directos = $db->fetchOne($sqlDirectos, [$documento]);

            // Obtener total de red completa
            $sqlRed = "CALL sp_obtener_red_jerarquica(?)";
            $result = $db->query($sqlRed, [$documento]);
            $red = $result->fetchAll(PDO::FETCH_ASSOC);
            $totalRed = count($red);

            // Estadísticas por perfil
            $perfiles = [];
            foreach ($red as $colab) {
                $perfil = $colab['perfil'];
                if (!isset($perfiles[$perfil])) {
                    $perfiles[$perfil] = 0;
                }
                $perfiles[$perfil]++;
            }

            // Estadísticas por estado
            $estados = [
                'Activo' => 0,
                'Potencial' => 0,
                'Inactivo' => 0
            ];
            foreach ($red as $colab) {
                $estado = $colab['estado'];
                if (isset($estados[$estado])) {
                    $estados[$estado]++;
                }
            }

            // Suma de dato potencial
            $potencialTotal = array_sum(array_column($red, 'dato_potencial'));

            jsonResponse(true, [
                'seguidores_directos' => (int)$directos['total'],
                'red_total' => $totalRed,
                'por_perfil' => $perfiles,
                'por_estado' => $estados,
                'potencial_total' => $potencialTotal,
                'promedio_potencial' => $totalRed > 0 ? round($potencialTotal / $totalRed, 2) : 0
            ], 'Métricas obtenidas exitosamente');
            break;

        case 'expandir':
            // Expandir un nodo específico (obtener sus seguidores directos)
            $documento = getParam('documento');

            if (!$documento) {
                jsonResponse(false, null, 'Documento requerido', ['documento' => 'Campo requerido']);
            }

            $sql = "
                SELECT
                    c.*,
                    'Activo' as estado
                FROM colaboradores c
                WHERE c.lider_directo = ?
                ORDER BY c.dato_potencial DESC
            ";

            $seguidores = $db->fetchAll($sql, [$documento]);

            $nodes = [];
            $edges = [];

            foreach ($seguidores as $colab) {
                $nodeId = $colab['documento'];

                // Contar seguidores del seguidor
                $sqlSub = "SELECT COUNT(*) as total FROM colaboradores WHERE lider_directo = ?";
                $subResult = $db->fetchOne($sqlSub, [$nodeId]);
                $totalSub = $subResult['total'] ?? 0;

                $nodes[] = [
                    'id' => $nodeId,
                    'label' => $colab['nombres'] . ' ' . $colab['apellidos'],
                    'title' => sprintf(
                        "<b>%s %s</b><br>Perfil: %s<br>Seguidores: %d",
                        $colab['nombres'],
                        $colab['apellidos'],
                        $colab['perfil'],
                        $totalSub
                    ),
                    'shape' => getShapeNivel($colab['nivel_participacion']),
                    'size' => getSizeNivel($colab['nivel_participacion']) + ($totalSub * 2),
                    'color' => [
                        'background' => getColorPerfil($colab['perfil']),
                        'border' => '#1e293b'
                    ]
                ];

                $edges[] = [
                    'from' => $documento,
                    'to' => $nodeId,
                    'arrows' => 'to',
                    'color' => getColorEstado($colab['estado'])
                ];
            }

            jsonResponse(true, [
                'nodes' => $nodes,
                'edges' => $edges
            ], 'Nodos expandidos exitosamente');
            break;

        default:
            jsonResponse(false, null, 'Acción no válida', ['action' => 'Acción desconocida']);
    }

} catch (Exception $e) {
    error_log("API Red Error: " . $e->getMessage());
    jsonResponse(false, null, 'Error al procesar la solicitud', ['error' => $e->getMessage()]);
}
