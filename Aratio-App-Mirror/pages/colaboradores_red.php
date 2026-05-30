<?php
/**
 * Página: Red Jerárquica de Colaboradores
 * Visualización interactiva de la estructura líder-seguidor con Vis.js
 */

// Variables de la página
$pageTitle = "Red Jerárquica";
$campanaId = $_SESSION['campana_activa'] ?? null;

// Si no hay campaña activa pero hay un root_doc, intentamos recuperarla
if (!$campanaId && isset($_GET['root_doc'])) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.campana_id, cam.nombre as campana_nombre 
        FROM colaboradores c 
        JOIN campanas cam ON c.campana_id = cam.id 
        WHERE c.documento = ? 
        LIMIT 1
    ");
    $stmt->execute([$_GET['root_doc']]);
    $result = $stmt->fetch();
    
    if ($result) {
        $campanaId = $result['campana_id'];
        $_SESSION['campana_activa'] = $campanaId;
        $_SESSION['campana_nombre'] = $result['campana_nombre'];
    }
}

$campanaName = $_SESSION['campana_nombre'] ?? 'Sin campaña';

if (!$campanaId) {
    echo '<div class="p-6"><div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
        <p>Seleccione una campaña para ver la red jerárquica.</p>
    </div></div>';
    return;
}
?>

<!-- Vis.js Network CDN -->
<script src="https://unpkg.com/vis-network@9.1.6/standalone/umd/vis-network.min.js"></script>

<div x-data="redJerarquica()" x-init="init()" class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i data-lucide="network" class="w-7 h-7 mr-2 text-fuchsia-600"></i>
                Red Jerárquica
            </h1>
            <p class="text-gray-600 mt-1">Visualización de estructura líder-seguidor</p>
        </div>

        <div class="flex gap-2 mt-4 md:mt-0">
            <button @click="togglePhysics()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center">
                <i data-lucide="zap" class="w-4 h-4 mr-2"></i>
                <span x-text="physicsEnabled ? 'Física: ON' : 'Física: OFF'"></span>
            </button>
            <button @click="fitNetwork()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center">
                <i data-lucide="maximize" class="w-4 h-4 mr-2"></i>
                Ajustar Vista
            </button>
            <a href="index.php?page=colaboradores" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Volver
            </a>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Área del grafo -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Loading -->
                <div x-show="loading" class="h-[600px] flex items-center justify-center">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-fuchsia-600 mx-auto mb-4"></div>
                        <p class="text-gray-600">Cargando red...</p>
                    </div>
                </div>

                <!-- Network Container -->
                <div x-show="!loading" id="network-container" class="h-[600px] w-full"></div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Estadísticas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                    Estadísticas
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total nodos:</span>
                        <span class="font-medium" x-text="stats.total_nodos"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Conexiones:</span>
                        <span class="font-medium" x-text="stats.total_aristas"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Líderes activos:</span>
                        <span class="font-medium" x-text="stats.lideres_activos"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sin líder:</span>
                        <span class="font-medium text-amber-600" x-text="stats.sin_lider"></span>
                    </div>
                </div>
            </div>

            <!-- Leyenda de colores -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i data-lucide="palette" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                    Perfiles
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-2" style="background-color: #FF00FF;"></span>
                        <span class="text-gray-700">Líder Comunitario</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-2" style="background-color: #3B82F6;"></span>
                        <span class="text-gray-700">Líder Social</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-2" style="background-color: #10B981;"></span>
                        <span class="text-gray-700">Líder Gremial</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-2" style="background-color: #F59E0B;"></span>
                        <span class="text-gray-700">Activista</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-2" style="background-color: #6B7280;"></span>
                        <span class="text-gray-700">Simpatizante</span>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i data-lucide="filter" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                    Filtros
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Perfil</label>
                        <select x-model="filtros.perfil" @change="applyFilter()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-fuchsia-500 focus:border-fuchsia-500">
                            <option value="">Todos</option>
                            <option value="Lider Comunitario">Líder Comunitario</option>
                            <option value="Lider Social">Líder Social</option>
                            <option value="Lider Gremial">Líder Gremial</option>
                            <option value="Activista">Activista</option>
                            <option value="Simpatizante">Simpatizante</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Mín. seguidores</label>
                        <input type="number" x-model.number="filtros.minSeguidores" @change="applyFilter()"
                               min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-fuchsia-500 focus:border-fuchsia-500">
                    </div>
                    <button @click="resetFilters()" class="w-full px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        Limpiar filtros
                    </button>
                </div>
            </div>

            <!-- Información del nodo seleccionado -->
            <div x-show="selectedNode" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i data-lucide="user" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                    Nodo Seleccionado
                </h3>
                <template x-if="selectedNode">
                    <div class="space-y-2 text-sm">
                        <p class="font-medium text-gray-800" x-text="selectedNode.label"></p>
                        <p class="text-gray-600">
                            <span class="text-gray-500">Perfil:</span>
                            <span x-text="selectedNode.perfil"></span>
                        </p>
                        <p class="text-gray-600">
                            <span class="text-gray-500">Estado:</span>
                            <span x-text="selectedNode.estado"></span>
                        </p>
                        <p class="text-gray-600">
                            <span class="text-gray-500">Seguidores:</span>
                            <span x-text="selectedNode.seguidores"></span>
                        </p>
                        <p class="text-gray-600">
                            <span class="text-gray-500">Municipio:</span>
                            <span x-text="selectedNode.municipio"></span>
                        </p>
                        <div class="pt-2 flex gap-2">
                            <a :href="'index.php?page=colaborador_detalle&id=' + selectedNode.colaborador_id"
                               class="flex-1 px-3 py-2 bg-fuchsia-600 text-white text-center rounded-lg hover:bg-fuchsia-700 text-xs">
                                Ver Detalle
                            </a>
                            <button @click="focusNode(selectedNode.id)"
                                    class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs">
                                Centrar
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function redJerarquica() {
    return {
        loading: true,
        network: null,
        allNodes: [],
        allEdges: [],
        stats: {
            total_nodos: 0,
            total_aristas: 0,
            sin_lider: 0,
            lideres_activos: 0
        },
        physicsEnabled: true,
        selectedNode: null,
        filtros: {
            perfil: '',
            minSeguidores: 0
        },
        campanaId: <?= json_encode($campanaId) ?>,

        async init() {
            await this.loadNetwork();
            // Reinicializar iconos Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        },

        async loadNetwork() {
            this.loading = true;
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const rootDoc = urlParams.get('root_doc') || '';
                const response = await fetch(`api/colaboradores.php?action=network&campana_id=${this.campanaId}&root_doc=${rootDoc}`);
                const result = await response.json();

                if (result.success) {
                    this.allNodes = result.data.nodes;
                    this.allEdges = result.data.edges;
                    this.stats = result.data.stats;
                    this.renderNetwork(this.allNodes, this.allEdges);
                } else {
                    console.error('Error:', result.message);
                }
            } catch (error) {
                console.error('Error cargando red:', error);
            } finally {
                this.loading = false;
            }
        },

        renderNetwork(nodes, edges) {
            const container = document.getElementById('network-container');

            const data = {
                nodes: new vis.DataSet(nodes),
                edges: new vis.DataSet(edges)
            };

            const options = {
                layout: {
                    hierarchical: {
                        enabled: false
                    }
                },
                physics: {
                    enabled: this.physicsEnabled,
                    stabilization: {
                        iterations: 150
                    },
                    barnesHut: {
                        gravitationalConstant: -8000,
                        centralGravity: 0.3,
                        springLength: 150,
                        springConstant: 0.04,
                        damping: 0.09
                    }
                },
                nodes: {
                    shape: 'dot',
                    font: {
                        size: 12,
                        color: '#333'
                    },
                    borderWidth: 2,
                    shadow: true
                },
                edges: {
                    color: {
                        color: '#aaa',
                        highlight: '#FF00FF',
                        hover: '#FF00FF'
                    },
                    smooth: {
                        type: 'cubicBezier',
                        forceDirection: 'vertical',
                        roundness: 0.4
                    }
                },
                interaction: {
                    hover: true,
                    tooltipDelay: 200,
                    hideEdgesOnDrag: true
                }
            };

            this.network = new vis.Network(container, data, options);

            // Evento de selección de nodo
            this.network.on('selectNode', (params) => {
                if (params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    this.selectedNode = nodes.find(n => n.id === nodeId);
                }
            });

            this.network.on('deselectNode', () => {
                this.selectedNode = null;
            });

            // Doble clic para abrir detalle
            this.network.on('doubleClick', (params) => {
                if (params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    const node = nodes.find(n => n.id === nodeId);
                    if (node) {
                        window.location.href = `index.php?page=colaborador_detalle&id=${node.colaborador_id}`;
                    }
                }
            });
        },

        togglePhysics() {
            this.physicsEnabled = !this.physicsEnabled;
            if (this.network) {
                this.network.setOptions({
                    physics: { enabled: this.physicsEnabled }
                });
            }
        },

        fitNetwork() {
            if (this.network) {
                this.network.fit({
                    animation: {
                        duration: 500,
                        easingFunction: 'easeInOutQuad'
                    }
                });
            }
        },

        focusNode(nodeId) {
            if (this.network) {
                this.network.focus(nodeId, {
                    scale: 1.5,
                    animation: {
                        duration: 500,
                        easingFunction: 'easeInOutQuad'
                    }
                });
            }
        },

        applyFilter() {
            let filteredNodes = this.allNodes;
            let filteredEdges = this.allEdges;

            // Filtrar por perfil
            if (this.filtros.perfil) {
                const docsWithPerfil = new Set();
                filteredNodes = this.allNodes.filter(n => {
                    if (n.perfil === this.filtros.perfil) {
                        docsWithPerfil.add(n.id);
                        return true;
                    }
                    return false;
                });
                filteredEdges = this.allEdges.filter(e =>
                    docsWithPerfil.has(e.from) || docsWithPerfil.has(e.to)
                );
            }

            // Filtrar por mínimo de seguidores
            if (this.filtros.minSeguidores > 0) {
                const docsWithSeguidores = new Set();
                filteredNodes = filteredNodes.filter(n => {
                    if (n.seguidores >= this.filtros.minSeguidores) {
                        docsWithSeguidores.add(n.id);
                        return true;
                    }
                    return false;
                });
                filteredEdges = filteredEdges.filter(e =>
                    docsWithSeguidores.has(e.from) && docsWithSeguidores.has(e.to)
                );
            }

            this.renderNetwork(filteredNodes, filteredEdges);
        },

        resetFilters() {
            this.filtros.perfil = '';
            this.filtros.minSeguidores = 0;
            this.renderNetwork(this.allNodes, this.allEdges);
        }
    };
}
</script>
