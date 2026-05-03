<?php
/**
 * Página: Red Jerárquica de Colaboradores - Versión Interactiva Premium
 * Visualización dinámica con carga por niveles y múltiples layouts
 */

// Variables de la página
$pageTitle = "Red Jerárquica Interactiva";
$campanaId = $_SESSION['campana_activa'] ?? null;
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

<div x-data="redInteractiva()" x-init="init()" class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i data-lucide="network" class="w-7 h-7 mr-2 text-fuchsia-600"></i>
                Red Interactiva
                <span
                    class="ml-3 px-2 py-1 bg-fuchsia-100 text-fuchsia-700 text-xs rounded-full uppercase tracking-wider font-semibold">Beta
                    Premium</span>
            </h1>
            <p class="text-gray-600 mt-1">Explora la estructura de la campaña por niveles</p>
        </div>

        <div class="flex flex-wrap gap-2 mt-4 md:mt-0">
            <!-- Selector de Layout Dropdown -->
            <div class="relative inline-block text-left" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" type="button"
                    class="inline-flex items-center gap-x-1.5 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <i data-lucide="layout" class="w-4 h-4 mr-1 text-fuchsia-600"></i>
                    Cambiar Vista
                    <i data-lucide="chevron-down" class="w-4 h-4 ml-1 text-gray-400"></i>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    style="display: none;">
                    <div class="py-1">
                        <button @click="changeLayout('hierarchical'); open = false"
                            class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-fuchsia-50 hover:text-fuchsia-700">
                            Jerárquico
                        </button>
                        <button @click="changeLayout('force'); open = false"
                            class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-fuchsia-50 hover:text-fuchsia-700">
                            Orgánico
                        </button>
                        <button @click="changeLayout('circular'); open = false"
                            class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-fuchsia-50 hover:text-fuchsia-700">
                            Radial
                        </button>
                    </div>
                </div>
            </div>

            <template x-if="focusId">
                <button @click="window.location.href='index.php?page=colaboradores_red'"
                    class="px-4 py-2 bg-fuchsia-100 text-fuchsia-700 border border-fuchsia-200 rounded-lg hover:bg-fuchsia-200 flex items-center">
                    <i data-lucide="network" class="w-4 h-4 mr-2"></i>
                    Ver Red Completa
                </button>
            </template>

            <button @click="fitNetwork()"
                class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center">
                <i data-lucide="maximize" class="w-4 h-4 mr-2"></i>
                Ajustar
            </button>

            <a href="index.php?page=colaboradores"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Volver
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden relative group">
                <!-- Overlay de carga -->
                <div x-show="loading"
                    class="absolute inset-0 bg-white/80 z-20 flex items-center justify-center backdrop-blur-sm">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-fuchsia-600 mx-auto mb-4">
                        </div>
                        <p class="text-gray-600 font-medium">Sincronizando red...</p>
                    </div>
                </div>

                <!-- Contenedor del Grafo -->
                <div id="network-container" class="h-[650px] w-full bg-slate-50 cursor-grab active:cursor-grabbing">
                </div>

                <!-- Hint visual -->
                <div
                    class="absolute bottom-4 left-4 bg-white/90 px-3 py-1.5 rounded-full text-[10px] text-gray-500 shadow-sm border border-gray-100 flex items-center gap-2">
                    <i data-lucide="info" class="w-3 h-3 text-fuchsia-500"></i>
                    Haz clic en nodos con <span class="w-2 h-2 rounded-full bg-fuchsia-500 inline-block"></span> para
                    expandir niveles
                </div>
            </div>
        </div>

        <!-- Sidebar Informativo -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Nodo Seleccionado -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 transition-all duration-300"
                :class="selectedNode ? 'border-fuchsia-200 ring-1 ring-fuchsia-50' : ''">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="user" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                    Detalle del Nodo
                </h3>

                <div x-show="!selectedNode" class="text-center py-10">
                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="mouse-pointer-2" class="w-6 h-6 text-gray-300"></i>
                    </div>
                    <p class="text-xs text-gray-400">Selecciona un colaborador para ver su información</p>
                </div>

                <template x-if="selectedNode">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-sm"
                                :style="'background-color: ' + selectedNode.color">
                                <span x-text="selectedNode.label.substring(0,1)"></span>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-base" x-text="selectedNode.label"></p>
                                <p class="text-xs font-medium px-2 py-0.5 rounded-full inline-block"
                                    :class="getPerfilClass(selectedNode.perfil)" x-text="selectedNode.perfil"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Seguidores</p>
                                <p class="text-lg font-bold text-gray-700" x-text="selectedNode.seguidores"></p>
                            </div>
                            <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Estado</p>
                                <p class="text-xs font-bold text-gray-700 pt-1"
                                    x-text="selectedNode.estado || 'Normal'"></p>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a :href="'index.php?page=colaborador_detalle&id=' + (selectedNode.real_id || selectedNode.colaborador_id)"
                                class="w-full py-2.5 bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-lg font-semibold text-sm transition flex items-center justify-center gap-2 shadow-sm shadow-fuchsia-100">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                Ver Perfil Completo
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Leyenda -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-3 text-sm">Leyenda de Perfiles</h3>
                <div class="space-y-2">
                    <template x-for="(color, perfil) in profileColors" :key="perfil">
                        <div
                            class="flex items-center text-xs text-gray-600 p-1.5 hover:bg-gray-50 rounded-md transition">
                            <span class="w-2.5 h-2.5 rounded-full mr-2.5 shadow-sm"
                                :style="'background-color: ' + color"></span>
                            <span x-text="perfil"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function redInteractiva() {
        return {
            loading: true,
            network: null,
            nodes: new vis.DataSet(),
            edges: new vis.DataSet(),
            selectedNode: null,
            currentLayout: 'force',
            campanaId: <?= json_encode($campanaId) ?>,
            profileColors: <?= json_encode(COLORES_PERFIL) ?>,

            init() {
                // Obtener parámetro 'focus' de la URL
                const urlParams = new URLSearchParams(window.location.search);
                this.focusId = urlParams.get('focus');

                this.loadInitialNetwork();
                if (typeof lucide !== 'undefined') lucide.createIcons();
            },

            async loadInitialNetwork() {
                this.loading = true;
                try {
                    let url = `api/colaboradores.php?action=network&campana_id=${this.campanaId}`;
                    // Si hay focus, pedimos modo filtrado a la API (rama + líder)
                    if (this.focusId) {
                        url += `&root_doc=${this.focusId}`;
                    }

                    const res = await fetch(url);
                    const data = await res.json();

                    if (data.success) {
                        this.nodes.clear();
                        this.edges.clear();
                        this.addNodesWithPulse(data.data.nodes);
                        this.edges.add(data.data.edges);
                        this.renderNetwork();

                        // Si hay focus, centrar en ese nodo después de renderizar (cuando se estabilice)
                        if (this.focusId) {
                            // Cambiamos el comportamiento a jerárquico por defecto para mayor claridad
                            this.currentLayout = 'hierarchical';
                            this.network.setOptions(this.getNetworkOptions());
                        }
                    }
                } catch (e) { console.error('Error:', e); }
                this.loading = false;
            },

            addNodesWithPulse(nodesList) {
                const processedNodes = nodesList.map(node => {
                    if (node.hasChildren) {
                        node.shadow = { enabled: true, color: 'rgba(255, 0, 255, 0.4)', size: 10, x: 0, y: 0 };
                        node.borderWidth = 3;
                        node.color = { border: '#FF00FF', background: node.color || '#6B7280' };
                    }
                    return node;
                });
                this.nodes.add(processedNodes);
            },

            renderNetwork() {
                const container = document.getElementById('network-container');
                const data = { nodes: this.nodes, edges: this.edges };
                const options = this.getNetworkOptions();

                this.network = new vis.Network(container, data, options);

                // Evento para enfoque automático cuando la red termina de cargarse
                this.network.once('stabilized', () => {
                    if (this.focusId) {
                        const node = this.nodes.get(this.focusId);
                        if (node) {
                            this.network.selectNodes([this.focusId]);
                            this.selectedNode = node;
                            this.network.focus(this.focusId, {
                                scale: 1.0,
                                offset: { x: 0, y: 0 },
                                animation: { duration: 1200, easingFunction: 'easeInOutQuart' }
                            });
                        }
                    }
                });

                this.network.on('click', (params) => {
                    if (params.nodes.length > 0) {
                        const nodeId = params.nodes[0];
                        const node = this.nodes.get(nodeId);
                        this.selectedNode = node;
                        if (node.hasChildren) this.expandNode(nodeId);
                    } else {
                        this.selectedNode = null;
                    }
                });
            },

            async expandNode(nodeId) {
                this.loading = true;
                try {
                    const res = await fetch(`api/colaboradores.php?action=network&campana_id=${this.campanaId}&root_doc=${nodeId}`);
                    const data = await res.json();
                    if (data.success) {
                        data.data.nodes.forEach(n => { if (!this.nodes.get(n.id)) this.addNodesWithPulse([n]); });
                        data.data.edges.forEach(e => {
                            const edgeId = e.from + '-' + e.to;
                            if (!this.edges.get(edgeId)) { e.id = edgeId; this.edges.add(e); }
                        });

                        // Quitar el "pulso" del nodo ya expandido
                        this.nodes.update({ id: nodeId, hasChildren: false, shadow: { enabled: false }, borderWidth: 1 });
                    }
                } catch (e) { console.error(e); }
                this.loading = false;
            },

            getNetworkOptions() {
                return {
                    layout: { hierarchical: { enabled: this.currentLayout === 'hierarchical', direction: 'UD', sortMethod: 'directed', nodeSpacing: 150 } },
                    physics: {
                        enabled: this.currentLayout === 'force',
                        barnesHut: { gravitationalConstant: -3000, centralGravity: 0.3, springLength: 120, damping: 0.09 },
                        stabilization: { iterations: 100 }
                    },
                    nodes: { shape: 'dot', size: 25, font: { size: 11, face: 'Inter' }, shadow: true },
                    edges: { smooth: { type: 'cubicBezier', forceDirection: 'vertical', roundness: 0.5 }, color: '#cbd5e1' },
                    interaction: { hover: true, tooltipDelay: 100 }
                };
            },

            changeLayout(type) {
                this.currentLayout = type;
                this.network.setOptions(this.getNetworkOptions());
                if (type === 'circular') {
                    this.network.setOptions({ layout: { hierarchical: false }, physics: { enabled: true, repulsion: { nodeSpacing: 200 } } });
                }
                setTimeout(() => this.fitNetwork(), 300);
            },

            fitNetwork() { this.network.fit({ animation: { duration: 500, easingFunction: 'easeInOutQuad' } }); },

            getPerfilClass(perfil) {
                const map = {
                    'Candidato': 'bg-yellow-100 text-yellow-800',
                    'Lider Comunitario': 'bg-fuchsia-100 text-fuchsia-700',
                    'Lider Social': 'bg-blue-100 text-blue-700',
                    'Lider Gremial': 'bg-emerald-100 text-emerald-700',
                    'Activista': 'bg-amber-100 text-amber-700'
                };
                return map[perfil] || 'bg-gray-100 text-gray-700';
            }
        };
    }
</script>