<?php
use App\Utils\Helpers;
?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    <!-- Network Visualization -->
    <div class="lg:col-span-3">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <div>
                    <h3 class="card-title">Red Jerárquica</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Padrino: <?= Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos']) ?>
                    </p>
                </div>
                <div class="flex gap-2">
                    <button onclick="fitNetwork()" class="btn btn-sm btn-outline" title="Ajustar vista">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                    </button>
                    <button onclick="exportNetwork()" class="btn btn-sm btn-outline" title="Exportar imagen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </button>
                    <button onclick="togglePhysics()" class="btn btn-sm btn-outline" title="Toggle física">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Network Container -->
            <div id="network-container" class="relative">
                <div id="network" style="width: 100%; height: 600px;"></div>

                <!-- Loading Overlay -->
                <div id="network-loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="text-center">
                        <div class="spinner w-12 h-12 border-4 border-primary-600 mx-auto mb-4"></div>
                        <p class="text-gray-600">Cargando red jerárquica...</p>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="border-t border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Leyenda:</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-perfil-lider-opinion"></div>
                        <span class="text-sm text-gray-600">Padrino de Opinión</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-perfil-influencer"></div>
                        <span class="text-sm text-gray-600">Influencer</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-perfil-militante"></div>
                        <span class="text-sm text-gray-600">Militante</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-full bg-perfil-simpatizante"></div>
                        <span class="text-sm text-gray-600">Simpatizante</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    * El tamaño del nodo representa la cantidad de seguidores directos
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Sidebar -->
    <div class="lg:col-span-1">

        <!-- Network Stats -->
        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Estadísticas de Red</h3>
            <div class="space-y-4" id="network-stats">
                <div>
                    <p class="text-sm text-gray-600">Total Colaboradores</p>
                    <p class="text-2xl font-bold text-gray-900" id="stat-total">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Niveles Jerárquicos</p>
                    <p class="text-2xl font-bold text-gray-900" id="stat-niveles">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Nodos Visibles</p>
                    <p class="text-2xl font-bold text-gray-900" id="stat-visible">-</p>
                </div>
            </div>
        </div>

        <!-- Root Node Info -->
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Padrino Principal</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Nombre</p>
                    <p class="font-medium text-gray-900">
                        <?= Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos']) ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Documento</p>
                    <p class="font-medium text-gray-900 font-mono"><?= $colaborador['documento'] ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Perfil</p>
                    <?= Helpers::estadoBadge($colaborador['perfil']) ?>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Territorio</p>
                    <p class="text-sm text-gray-900">
                        <?= htmlspecialchars($colaborador['municipio']) ?>,
                        <?= htmlspecialchars($colaborador['departamento']) ?>
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200">
                <a href="/colaboradores/<?= $colaborador['id'] ?>" class="btn btn-sm btn-primary w-full">
                    Ver Perfil Completo
                </a>
            </div>
        </div>

        <!-- Controls -->
        <div class="card mt-6">
            <h3 class="font-semibold text-gray-900 mb-4">Controles</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-600 mb-2 block">Diseño</label>
                    <select id="layout-select" class="form-input text-sm" onchange="changeLayout(this.value)">
                        <option value="hierarchical">Jerárquico</option>
                        <option value="force">Fuerza</option>
                        <option value="circular">Circular</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600 mb-2 block">Dirección</label>
                    <select id="direction-select" class="form-input text-sm" onchange="changeDirection(this.value)">
                        <option value="UD">Arriba a Abajo</option>
                        <option value="DU">Abajo a Arriba</option>
                        <option value="LR">Izquierda a Derecha</option>
                        <option value="RL">Derecha a Izquierda</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Node Detail Modal -->
<div id="nodeModal" class="modal-backdrop hidden" x-data="{ show: false, node: null }">
    <div class="modal" x-show="show" @click.away="show = false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Detalle del Colaborador</h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body" id="node-detail">
                    <p class="text-gray-600">Seleccione un nodo para ver detalles...</p>
                </div>
                <div class="modal-footer">
                    <button @click="show = false" class="btn btn-outline">
                        Cerrar
                    </button>
                    <button onclick="viewFullProfile()" class="btn btn-primary">
                        Ver Perfil Completo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vis.js Network Library -->
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<link href="https://unpkg.com/vis-network/styles/vis-network.css" rel="stylesheet" type="text/css" />

<script>
let network;
let networkData;
let selectedNodeId = null;
let physicsEnabled = true;

// Cargar datos de la red
async function loadNetworkData() {
    try {
        const documento = '<?= $colaborador['documento'] ?>';
        const response = await fetch(`/api/v1/red.php?action=grafo&documento=${documento}&niveles=10`);
        const data = await response.json();

        if (data.success) {
            networkData = data.data;
            initNetwork(networkData);
            updateStats(networkData);
            // Cargar métricas adicionales
            loadMetrics(documento);
        } else {
            App.toast('Error al cargar red: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        App.toast('Error al cargar red', 'error');
    } finally {
        document.getElementById('network-loading').style.display = 'none';
    }
}

// Cargar métricas de la red
async function loadMetrics(documento) {
    try {
        const response = await fetch(`/api/v1/red.php?action=metricas&documento=${documento}`);
        const data = await response.json();

        if (data.success) {
            const metrics = data.data;
            console.log('Métricas de red:', metrics);
            // Actualizar estadísticas adicionales si existen elementos en el DOM
        }
    } catch (error) {
        console.error('Error al cargar métricas:', error);
    }
}

// Inicializar red
function initNetwork(data) {
    const container = document.getElementById('network');

    const options = {
        layout: {
            hierarchical: {
                direction: 'UD',
                sortMethod: 'directed',
                levelSeparation: 150,
                nodeSpacing: 200
            }
        },
        physics: {
            hierarchicalRepulsion: {
                centralGravity: 0.0,
                springLength: 100,
                springConstant: 0.01,
                nodeDistance: 150,
                damping: 0.09
            },
            solver: 'hierarchicalRepulsion'
        },
        nodes: {
            borderWidth: 2,
            borderWidthSelected: 4,
            font: {
                size: 14,
                color: '#374151'
            }
        },
        edges: {
            smooth: {
                type: 'cubicBezier',
                forceDirection: 'vertical',
                roundness: 0.4
            },
            color: {
                color: '#94a3b8',
                highlight: '#3b82f6'
            },
            width: 2
        },
        interaction: {
            hover: true,
            navigationButtons: true,
            keyboard: true
        }
    };

    network = new vis.Network(container, {
        nodes: new vis.DataSet(data.nodes),
        edges: new vis.DataSet(data.edges)
    }, options);

    // Eventos
    network.on('click', function(params) {
        if (params.nodes.length > 0) {
            selectedNodeId = params.nodes[0];
            showNodeDetail(selectedNodeId);
        }
    });

    network.on('doubleClick', function(params) {
        if (params.nodes.length > 0) {
            const nodeId = params.nodes[0];
            window.location.href = `/colaboradores/${nodeId}`;
        }
    });

    network.on('hoverNode', function() {
        container.style.cursor = 'pointer';
    });

    network.on('blurNode', function() {
        container.style.cursor = 'default';
    });
}

// Mostrar detalle de nodo
function showNodeDetail(nodeId) {
    const node = networkData.nodes.find(n => n.id === nodeId);

    if (node) {
        const detail = `
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Nombre</p>
                    <p class="font-medium text-gray-900">${node.label}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Documento</p>
                    <p class="font-medium text-gray-900 font-mono">${node.id}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Información</p>
                    <p class="text-sm text-gray-900">${node.title}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Nivel Jerárquico</p>
                    <p class="font-medium text-gray-900">${node.level}</p>
                </div>
            </div>
        `;

        document.getElementById('node-detail').innerHTML = detail;
        document.getElementById('nodeModal').classList.remove('hidden');
        Alpine.raw(document.getElementById('nodeModal').__x.$data).show = true;
    }
}

// Ver perfil completo
function viewFullProfile() {
    if (selectedNodeId) {
        window.location.href = `/colaboradores/${selectedNodeId}`;
    }
}

// Ajustar vista
function fitNetwork() {
    if (network) {
        network.fit({
            animation: {
                duration: 1000,
                easingFunction: 'easeInOutQuad'
            }
        });
    }
}

// Cambiar diseño
function changeLayout(layout) {
    if (!network) return;

    const options = layout === 'hierarchical' ? {
        layout: {
            hierarchical: {
                direction: document.getElementById('direction-select').value,
                sortMethod: 'directed'
            }
        }
    } : layout === 'force' ? {
        layout: {
            hierarchical: false
        },
        physics: {
            enabled: true,
            barnesHut: {
                gravitationalConstant: -2000,
                centralGravity: 0.3,
                springLength: 95,
                springConstant: 0.04
            }
        }
    } : {
        layout: {
            hierarchical: false
        },
        physics: {
            enabled: false
        }
    };

    network.setOptions(options);

    if (layout === 'circular') {
        const positions = {};
        const nodes = networkData.nodes;
        const radius = 300;
        const angleStep = (2 * Math.PI) / nodes.length;

        nodes.forEach((node, index) => {
            const angle = index * angleStep;
            positions[node.id] = {
                x: radius * Math.cos(angle),
                y: radius * Math.sin(angle)
            };
        });

        network.setOptions({ physics: false });
        network.body.data.nodes.update(nodes.map((node, index) => ({
            id: node.id,
            x: positions[node.id].x,
            y: positions[node.id].y
        })));
    }

    fitNetwork();
}

// Cambiar dirección
function changeDirection(direction) {
    if (network && document.getElementById('layout-select').value === 'hierarchical') {
        network.setOptions({
            layout: {
                hierarchical: {
                    direction: direction,
                    sortMethod: 'directed'
                }
            }
        });
    }
}

// Toggle física
function togglePhysics() {
    if (network) {
        physicsEnabled = !physicsEnabled;
        network.setOptions({ physics: { enabled: physicsEnabled } });
        App.toast(physicsEnabled ? 'Física activada' : 'Física desactivada', 'info');
    }
}

// Exportar red como imagen
function exportNetwork() {
    if (network) {
        const canvas = network.canvas.frame.canvas;
        const link = document.createElement('a');
        link.download = 'red-jerarquica.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        App.toast('Imagen exportada', 'success');
    }
}

// Actualizar estadísticas
function updateStats(data) {
    document.getElementById('stat-total').textContent = data.stats.total;
    document.getElementById('stat-niveles').textContent = data.stats.niveles;
    document.getElementById('stat-visible').textContent = data.nodes.length;
}

// Cargar al inicio
document.addEventListener('DOMContentLoaded', loadNetworkData);
</script>
