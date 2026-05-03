<?php
/**
 * Controlador del Dashboard
 * Página principal después del login
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;
use App\Models\Usuario;

class DashboardController extends Controller {
    /**
     * Modelo de Colaborador
     * @var Colaborador
     */
    private Colaborador $colaboradorModel;

    /**
     * Modelo de Usuario
     * @var Usuario
     */
    private Usuario $usuarioModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->colaboradorModel = new Colaborador();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Mostrar dashboard principal
     */
    public function index(): void {
        try {
            $campanaId = $this->user['campana_id'] ?? null;

            // Obtener estadísticas generales
            // Nota: Se podría actualizar el SP para recibir campana_id, 
            // pero para esta implementación usaremos filtros en el modelo.
            $db = $this->db();
            $stats = $db->getEstadisticasGenerales(); // Se recomienda actualizar SP

            // Filtros para métodos del modelo
            $filters = $campanaId ? ['campana_id' => $campanaId] : [];

            // Estadísticas por perfil
            $statsByProfile = $this->colaboradorModel->getEstadisticasPorPerfil($filters);

            // Estadísticas por territorio
            $statsByTerritory = $this->colaboradorModel->getEstadisticasPorTerritorio($filters);

            // Top líderes (con más seguidores)
            $topLeaders = $this->colaboradorModel->getTopLeaders(10, $campanaId);

            // Actividad reciente (últimos cambios de líder)
            $recentActivity = $this->colaboradorModel->getRecentActivity(10, $campanaId);

            // Colaboradores nuevos (últimos 30 días)
            $newColaboradores = $this->colaboradorModel->getRecentColaboradores(30, $campanaId);

            // Estadísticas por municipio (para gráficos de barras y burbujas)
            $statsByMunicipio = $this->colaboradorModel->getColaboradoresPorMunicipio(15, $campanaId);

            // Áreas de interés consolidadas
            $areasInteres = $this->colaboradorModel->getAreasInteresConsolidadas($campanaId);

            // Tendencia general de dato potencial (evolución de estados)
            $tendenciaEstados = $this->colaboradorModel->getTendenciaGeneralPotencial(60, $campanaId);

            // Preparar datos para gráficos
            $chartData = [
                'profiles' => [
                    'labels' => array_column($statsByProfile, 'perfil'),
                    'data' => array_column($statsByProfile, 'total')
                ],
                'territories' => [
                    'labels' => array_column($statsByTerritory, 'departamento'),
                    'data' => array_column($statsByTerritory, 'total')
                ],
                'estados' => [
                    'labels' => ['Nuevo', 'Creció', 'Igual', 'Decrece', 'Desvinculado'],
                    'data' => [
                        $stats['total_nuevos'] ?? 0,
                        $stats['total_crecio'] ?? 0,
                        $stats['total_igual'] ?? 0,
                        $stats['total_decrece'] ?? 0,
                        $stats['total_desvinculados'] ?? 0
                    ]
                ],
                'municipios' => [
                    'labels' => array_column($statsByMunicipio, 'municipio'),
                    'data' => array_map('intval', array_column($statsByMunicipio, 'total_colaboradores')),
                    'lideres' => array_map('intval', array_column($statsByMunicipio, 'total_lideres')),
                    'departamentos' => array_column($statsByMunicipio, 'departamento')
                ],
                'areas_interes' => [
                    'labels' => array_column($areasInteres, 'area'),
                    'data' => array_map('intval', array_column($areasInteres, 'total'))
                ],
                'tendencia_estados' => [
                    'fechas' => array_column($tendenciaEstados, 'fecha'),
                    'promedio_potencial' => array_map('floatval', array_column($tendenciaEstados, 'promedio_potencial')),
                    'colaboradores_activos' => array_map('intval', array_column($tendenciaEstados, 'colaboradores_activos')),
                    'nuevos' => array_map('intval', array_column($tendenciaEstados, 'nuevos')),
                    'crecio' => array_map('intval', array_column($tendenciaEstados, 'crecio')),
                    'igual' => array_map('intval', array_column($tendenciaEstados, 'igual')),
                    'decrece' => array_map('intval', array_column($tendenciaEstados, 'decrece')),
                    'desvinculados' => array_map('intval', array_column($tendenciaEstados, 'desvinculados'))
                ]
            ];

            // Tarjetas de estadísticas principales
            $mainStats = [
                [
                    'title' => 'Total Colaboradores',
                    'value' => number_format($stats['total_colaboradores'] ?? 0),
                    'icon' => 'users',
                    'color' => 'primary',
                    'change' => '+' . ($stats['nuevos_mes'] ?? 0) . ' este mes'
                ],
                [
                    'title' => 'Líderes Activos',
                    'value' => number_format($stats['total_lideres'] ?? 0),
                    'icon' => 'user-check',
                    'color' => 'success',
                    'change' => 'Con seguidores'
                ],
                [
                    'title' => 'Promedio Seguidores',
                    'value' => number_format($stats['promedio_seguidores'] ?? 0, 1),
                    'icon' => 'trending-up',
                    'color' => 'info',
                    'change' => 'Por líder'
                ],
                [
                    'title' => 'Nuevos (30 días)',
                    'value' => count($newColaboradores),
                    'icon' => 'user-plus',
                    'color' => 'warning',
                    'change' => 'Últimos 30 días'
                ]
            ];

            $this->view('dashboard.index', [
                'stats' => $stats,
                'mainStats' => $mainStats,
                'statsByProfile' => $statsByProfile,
                'statsByTerritory' => $statsByTerritory,
                'topLeaders' => $topLeaders,
                'recentActivity' => $recentActivity,
                'newColaboradores' => $newColaboradores,
                'chartData' => $chartData
            ]);

        } catch (\Exception $e) {
            \App\Utils\Logger::exception($e, ['action' => 'dashboard']);
            $this->view('errors.500', ['error' => $e->getMessage()], null);
        }
    }

    /**
     * Dashboard Staging - Gráficos avanzados experimentales
     */
    public function staging(): void {
        try {
            // Obtener estadísticas generales
            $db = $this->db();
            $stats = $db->getEstadisticasGenerales();

            // Estadísticas por perfil
            $statsByProfile = $this->colaboradorModel->getEstadisticasPorPerfil();

            // Estadísticas por territorio
            $statsByTerritory = $this->colaboradorModel->getEstadisticasPorTerritorio();

            // Top líderes (con más seguidores)
            $topLeaders = $this->colaboradorModel->getTopLeaders(10);

            // Colaboradores nuevos (últimos 30 días)
            $newColaboradores = $this->colaboradorModel->getRecentColaboradores(30);

            // Estadísticas por municipio
            $statsByMunicipio = $this->colaboradorModel->getColaboradoresPorMunicipio(15);

            // Preparar datos para gráficos básicos
            $chartData = [
                'profiles' => [
                    'labels' => array_column($statsByProfile, 'perfil'),
                    'data' => array_map('intval', array_column($statsByProfile, 'total'))
                ],
                'territories' => [
                    'labels' => array_column($statsByTerritory, 'departamento'),
                    'data' => array_map('intval', array_column($statsByTerritory, 'total'))
                ]
            ];

            // =============================================
            // CHORD DIAGRAM DATA: Perfil ↔ Departamento
            // =============================================
            $chordData = $this->prepareChordData();

            // =============================================
            // SANKEY DIAGRAM DATA: Perfil → Nivel → Estado
            // =============================================
            $sankeyData = $this->prepareSankeyData();

            // =============================================
            // SUNBURST DATA: Jerarquía geográfica
            // =============================================
            $sunburstData = $this->prepareSunburstData();

            // =============================================
            // FORCE NETWORK DATA: Red jerárquica completa
            // =============================================
            $forceNetworkData = $this->prepareForceNetworkData();

            // Tarjetas de estadísticas principales
            $mainStats = [
                [
                    'title' => 'Total Colaboradores',
                    'value' => number_format($stats['total_colaboradores'] ?? 0),
                    'icon' => 'users',
                    'color' => 'primary',
                    'change' => '+' . ($stats['nuevos_mes'] ?? 0) . ' este mes'
                ],
                [
                    'title' => 'Líderes Activos',
                    'value' => number_format($stats['total_lideres'] ?? 0),
                    'icon' => 'user-check',
                    'color' => 'success',
                    'change' => 'Con seguidores'
                ],
                [
                    'title' => 'Promedio Seguidores',
                    'value' => number_format($stats['promedio_seguidores'] ?? 0, 1),
                    'icon' => 'trending-up',
                    'color' => 'info',
                    'change' => 'Por líder'
                ],
                [
                    'title' => 'Nuevos (30 días)',
                    'value' => count($newColaboradores),
                    'icon' => 'user-plus',
                    'color' => 'warning',
                    'change' => 'Últimos 30 días'
                ]
            ];

            $this->view('dashboard.staging', [
                'stats' => $stats,
                'mainStats' => $mainStats,
                'statsByProfile' => $statsByProfile,
                'statsByTerritory' => $statsByTerritory,
                'topLeaders' => $topLeaders,
                'newColaboradores' => $newColaboradores,
                'chartData' => $chartData,
                'chordData' => $chordData,
                'sankeyData' => $sankeyData,
                'sunburstData' => $sunburstData,
                'forceNetworkData' => $forceNetworkData
            ]);

        } catch (\Exception $e) {
            \App\Utils\Logger::exception($e, ['action' => 'dashboard-staging']);
            $this->view('errors.500', ['error' => $e->getMessage()], null);
        }
    }

    /**
     * Preparar datos para Chord Diagram (Perfil ↔ Municipio)
     */
    private function prepareChordData(): array {
        $db = $this->db();

        // Obtener colaboradores con perfil y municipio
        $sql = "SELECT perfil, municipio, COUNT(*) as total
                FROM colaboradores
                WHERE perfil IS NOT NULL AND municipio IS NOT NULL
                AND perfil != '' AND municipio != ''
                GROUP BY perfil, municipio
                ORDER BY total DESC";

        $data = $db->fetchAll($sql);

        if (empty($data)) {
            return ['matrix' => [], 'names' => []];
        }

        // Obtener top municipios por cantidad de colaboradores
        $municipioTotals = [];
        foreach ($data as $row) {
            $muni = $row['municipio'];
            if (!isset($municipioTotals[$muni])) {
                $municipioTotals[$muni] = 0;
            }
            $municipioTotals[$muni] += (int)$row['total'];
        }
        arsort($municipioTotals);
        $topMunicipios = array_slice(array_keys($municipioTotals), 0, 8);

        // Obtener top perfiles
        $perfilTotals = [];
        foreach ($data as $row) {
            $perfil = $row['perfil'];
            if (!isset($perfilTotals[$perfil])) {
                $perfilTotals[$perfil] = 0;
            }
            $perfilTotals[$perfil] += (int)$row['total'];
        }
        arsort($perfilTotals);
        $topPerfiles = array_slice(array_keys($perfilTotals), 0, 6);

        $names = array_merge($topPerfiles, $topMunicipios);
        $n = count($names);

        // Crear matriz de adyacencia
        $matrix = array_fill(0, $n, array_fill(0, $n, 0));

        foreach ($data as $row) {
            if (!in_array($row['perfil'], $topPerfiles)) continue;
            if (!in_array($row['municipio'], $topMunicipios)) continue;

            $perfilIdx = array_search($row['perfil'], $names);
            $muniIdx = array_search($row['municipio'], $names);

            if ($perfilIdx !== false && $muniIdx !== false) {
                $matrix[$perfilIdx][$muniIdx] = (int)$row['total'];
                $matrix[$muniIdx][$perfilIdx] = (int)$row['total'];
            }
        }

        return [
            'matrix' => $matrix,
            'names' => $names
        ];
    }

    /**
     * Preparar datos para Sankey Diagram (Perfil → Nivel → Estado)
     */
    private function prepareSankeyData(): array {
        $db = $this->db();

        // Obtener distribución: Perfil → Nivel de Participación → Estado
        $sql = "SELECT
                    perfil,
                    nivel_participacion,
                    estado,
                    COUNT(*) as total
                FROM colaboradores
                WHERE perfil IS NOT NULL AND nivel_participacion IS NOT NULL
                GROUP BY perfil, nivel_participacion, estado
                ORDER BY total DESC
                LIMIT 50";

        $data = $db->fetchAll($sql);

        if (empty($data)) {
            return ['nodes' => [], 'links' => []];
        }

        $nodes = [];
        $nodeIndex = [];
        $links = [];

        // Agregar nodos de perfiles (top 5)
        $perfiles = array_unique(array_column($data, 'perfil'));
        $perfiles = array_slice($perfiles, 0, 5);
        foreach ($perfiles as $perfil) {
            $nodes[] = ['name' => $perfil, 'category' => 'perfil'];
            $nodeIndex[$perfil] = count($nodes) - 1;
        }

        // Agregar nodos de niveles
        $niveles = array_unique(array_column($data, 'nivel_participacion'));
        $niveles = array_filter($niveles);
        foreach ($niveles as $nivel) {
            if (!isset($nodeIndex[$nivel])) {
                $nodes[] = ['name' => $nivel, 'category' => 'nivel'];
                $nodeIndex[$nivel] = count($nodes) - 1;
            }
        }

        // Agregar nodos de estados
        $estados = array_unique(array_column($data, 'estado'));
        $estados = array_filter($estados);
        foreach ($estados as $estado) {
            if (!isset($nodeIndex[$estado])) {
                $nodes[] = ['name' => $estado, 'category' => 'estado'];
                $nodeIndex[$estado] = count($nodes) - 1;
            }
        }

        // Crear links agrupados por perfil-nivel y nivel-estado
        $perfilNivel = [];
        $nivelEstado = [];

        foreach ($data as $row) {
            if (!in_array($row['perfil'], $perfiles)) continue;

            $key1 = $row['perfil'] . '|' . $row['nivel_participacion'];
            if (!isset($perfilNivel[$key1])) {
                $perfilNivel[$key1] = 0;
            }
            $perfilNivel[$key1] += (int)$row['total'];

            if ($row['nivel_participacion'] && $row['estado']) {
                $key2 = $row['nivel_participacion'] . '|' . $row['estado'];
                if (!isset($nivelEstado[$key2])) {
                    $nivelEstado[$key2] = 0;
                }
                $nivelEstado[$key2] += (int)$row['total'];
            }
        }

        // Convertir a links
        foreach ($perfilNivel as $key => $value) {
            [$source, $target] = explode('|', $key);
            if (isset($nodeIndex[$source]) && isset($nodeIndex[$target])) {
                $links[] = [
                    'source' => $source,
                    'target' => $target,
                    'value' => $value
                ];
            }
        }

        foreach ($nivelEstado as $key => $value) {
            [$source, $target] = explode('|', $key);
            if (isset($nodeIndex[$source]) && isset($nodeIndex[$target])) {
                $links[] = [
                    'source' => $source,
                    'target' => $target,
                    'value' => $value
                ];
            }
        }

        return [
            'nodes' => $nodes,
            'links' => $links
        ];
    }

    /**
     * Preparar datos para Sunburst (Jerarquía geográfica)
     */
    private function prepareSunburstData(): array {
        $db = $this->db();

        // Obtener datos jerárquicos: Departamento → Municipio → Count
        $sql = "SELECT
                    departamento,
                    municipio,
                    COUNT(*) as total
                FROM colaboradores
                WHERE departamento IS NOT NULL AND departamento != ''
                GROUP BY departamento, municipio
                ORDER BY departamento, total DESC";

        $data = $db->fetchAll($sql);

        if (empty($data)) {
            return ['name' => 'Colaboradores', 'children' => []];
        }

        // Agrupar por departamento
        $departamentos = [];
        foreach ($data as $row) {
            $depto = $row['departamento'] ?: 'Sin departamento';
            $municipio = $row['municipio'] ?: 'Sin municipio';

            if (!isset($departamentos[$depto])) {
                $departamentos[$depto] = [];
            }

            $departamentos[$depto][] = [
                'name' => $municipio,
                'value' => (int)$row['total']
            ];
        }

        // Construir estructura jerárquica
        $children = [];
        foreach ($departamentos as $depto => $municipios) {
            // Limitar municipios por departamento para mejor visualización
            $municipios = array_slice($municipios, 0, 8);
            $children[] = [
                'name' => $depto,
                'children' => $municipios
            ];
        }

        // Ordenar por total de colaboradores y limitar departamentos
        usort($children, function($a, $b) {
            $sumA = array_sum(array_column($a['children'], 'value'));
            $sumB = array_sum(array_column($b['children'], 'value'));
            return $sumB - $sumA;
        });

        $children = array_slice($children, 0, 8);

        return [
            'name' => 'Colaboradores',
            'children' => $children
        ];
    }

    /**
     * Preparar datos para Force Network (Red jerárquica multinivel)
     */
    private function prepareForceNetworkData(): array {
        try {
            $db = $this->db();

            // Consulta simplificada usando solo la vista que ya existe
            $sql = "SELECT
                        id,
                        documento,
                        CONCAT(nombres, ' ', apellidos) as nombre,
                        perfil,
                        municipio,
                        lider_directo,
                        COALESCE(total_seguidores_directos, 0) as seguidores
                    FROM v_colaboradores_completo
                    ORDER BY total_seguidores_directos DESC
                    LIMIT 80";

            $colaboradores = $db->fetchAll($sql);

            if (empty($colaboradores)) {
                return ['nodes' => [], 'links' => []];
            }

            // Crear mapa de documentos a índices
            $docToIndex = [];
            $nodes = [];

            foreach ($colaboradores as $i => $col) {
                $docToIndex[$col['documento']] = $i;
                $nodes[] = [
                    'id' => $i,
                    'documento' => $col['documento'],
                    'name' => $col['nombre'] ?? 'Sin nombre',
                    'perfil' => $col['perfil'] ?? 'Sin perfil',
                    'municipio' => $col['municipio'] ?? '',
                    'seguidores' => (int)($col['seguidores'] ?? 0),
                    'isLeader' => (int)($col['seguidores'] ?? 0) > 0
                ];
            }

            // Crear enlaces basados en relaciones líder-seguidor
            $links = [];
            foreach ($colaboradores as $col) {
                if (!empty($col['lider_directo']) && isset($docToIndex[$col['lider_directo']])) {
                    $sourceIdx = $docToIndex[$col['lider_directo']]; // líder
                    $targetIdx = $docToIndex[$col['documento']];      // seguidor

                    $links[] = [
                        'source' => $sourceIdx,
                        'target' => $targetIdx,
                        'value' => 1
                    ];
                }
            }

            return [
                'nodes' => $nodes,
                'links' => $links
            ];
        } catch (\Exception $e) {
            \App\Utils\Logger::error('Error en prepareForceNetworkData: ' . $e->getMessage());
            return ['nodes' => [], 'links' => []];
        }
    }
}
