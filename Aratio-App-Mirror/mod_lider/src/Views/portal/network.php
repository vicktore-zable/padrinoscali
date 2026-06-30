<?php
/**
 * Vista de Mi Red para el Portal del Líder - Versión Premium
 */
?>
<!-- Vis.js CSS -->
<style type="text/css">
    #mynetwork {
        width: 100%;
        height: calc(100vh - 280px);
        background: rgba(0, 0, 0, 0.2);
    }
    
    .vis-network:focus {
        outline: none;
    }
</style>

<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Mi Red <span class="gradient-text">Estratégica</span></h2>
            <p class="text-gray-500">Mapa jerárquico de tu estructura electoral en tiempo real.</p>
        </div>
        <div class="flex gap-3">
             <button onclick="network.fit()" class="px-6 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="maximize" class="w-4 h-4"></i>
                Centrar Red
            </button>
            <a href="?page=portal_dashboard" class="px-6 py-2 bg-gradient-to-r from-[#002244] to-[#004488] rounded-xl text-sm font-bold text-white shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                Panel Principal
            </a>
        </div>
    </div>
</div>

<div class="glass-card overflow-hidden p-1 relative shadow-md bg-white border-gray-100">
    <div id="loading-network" class="absolute inset-0 flex items-center justify-center bg-white/90 z-10 transition-opacity duration-500">
        <div class="text-center">
            <div class="w-16 h-16 border-4 border-[#002244]/20 border-t-[#002244] rounded-full animate-spin mx-auto mb-4"></div>
            <span class="text-[#002244] font-bold uppercase tracking-widest text-xs">Mapeando estructura...</span>
        </div>
    </div>
    
    <div id="mynetwork"></div>
    
    <!-- Legend -->
    <div class="p-4 border-t border-gray-100 bg-gray-50/50 grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full bg-[#002244] shadow-[0_0_10px_rgba(0,34,68,0.4)]"></span>
            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Líderes Aratio</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full bg-[#DAA520] shadow-[0_0_10px_rgba(218,165,32,0.4)]"></span>
            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Simpatizantes</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full bg-cyan-600"></span>
            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Referidos Directos</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full bg-slate-400"></span>
            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Otros Niveles</span>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script type="text/javascript">
    let network = null;
    const container = document.getElementById("mynetwork");
    const loader = document.getElementById("loading-network");
    
    const options = {
        nodes: {
            shape: "dot",
            size: 24,
            font: {
                size: 12,
                color: "#1e293b", // Slate 800
                face: "'Outfit', sans-serif",
                strokeWidth: 2,
                strokeColor: "#ffffff"
            },
            borderWidth: 3,
            shadow: {
                enabled: true,
                color: 'rgba(0,0,0,0.1)',
                size: 10,
                x: 5,
                y: 5
            }
        },
        edges: {
            width: 2,
            color: { 
                color: "#cbd5e1", // Slate 300 
                highlight: "#004488", /* Blue Light */
                hover: "#DAA520" /* Gold */
            },
            smooth: {
                type: "cubicBezier",
                forceDirection: "vertical",
                roundness: 0.5
            },
            arrows: {
                to: { enabled: true, scaleFactor: 0.6 }
            }
        },
        layout: {
            hierarchical: {
                direction: "UD",
                sortMethod: "directed",
                nodeSpacing: 200,
                levelSeparation: 180,
                edgeMinimization: true,
                parentCentralization: true
            }
        },
        physics: false,
        interaction: {
            hover: true,
            tooltipDelay: 100,
            zoomView: true,
            dragView: true
        }
    };

    async function loadNetwork() {
        console.log('Loading network for liderId:', liderId);
        console.log('Fetching network data from', `api/colaboradores/${liderId}/network-data`);
        try {
            const liderId = <?= json_encode($liderId) ?>;
            // Usar la ruta relativa para mantener el contexto del subdirectorio (ej. /aratio/)
            const response = await fetch(`api/colaboradores/${liderId}/network-data`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('Response status:', response.status);
            // Si la sesión expiró (401), redirigir a login
            if (response.status === 401) {
                const errorData = await response.json();
                console.warn('Auth error:', errorData);
                if (errorData.redirect) {
                    window.location.href = errorData.redirect;
                    return;
                }
            }

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            
            // Limpiar texto de avisos PHP si existen
            let jsonData = text;
            if (text.indexOf('{') > 0) {
                jsonData = text.substring(text.indexOf('{'));
            } else if (text.indexOf('[') > 0 && (text.indexOf('[') < text.indexOf('{') || text.indexOf('{') === -1)) {
                // En caso de que sea un array puro []
                jsonData = text.substring(text.indexOf('['));
            }

            const data = JSON.parse(jsonData);

            if (data.success) {
                // Adaptar nodos para el tema light
                const stylizedNodes = data.data.nodes.map(node => {
                    // Colores premium basados en perfil
                    let color = "#3b82f6"; // Default blue
                    if (node.perfil === 'Líder') color = "#002244"; // Aratio Dark Blue
                    if (node.perfil === 'Simpatizante') color = "#DAA520"; // Gold Dark
                    
                    return {
                        ...node,
                        color: {
                            background: color,
                            border: "#ffffff",
                            highlight: { background: "#ffffff", border: color },
                            hover: { background: color, border: "#ffffff" }
                        }
                    };
                });

                const nodes = new vis.DataSet(stylizedNodes);
                const edges = new vis.DataSet(data.data.edges);
                
                const networkData = { nodes: nodes, edges: edges };
                network = new vis.Network(container, networkData, options);
                
                network.on("stabilizationIterationsDone", function () {
                    loader.style.opacity = '0';
                    setTimeout(() => loader.style.display = 'none', 500);
                });
                
                // Si la estabilización es rápida o manual
                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => loader.style.display = 'none', 500);
                }, 1000);

                if (data.data.nodes.length === 0) {
                    container.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-gray-500"><i data-lucide="share-2" class="w-12 h-12 mb-4 text-gray-300"></i><p class="text-sm font-medium">No hay estructura jerárquica para mostrar</p></div>';
                    loader.style.display = 'none';
                    lucide.createIcons();
                }

            } else {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500 bg-red-50/50"><span class="font-medium">Error al procesar red estratégica.</span></div>';
                loader.style.display = 'none';
            }
        } catch (error) {
            console.error('Network load error:', error);
            container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500 bg-red-50/50"><span class="font-medium">Fallo de conexión con el núcleo.</span></div>';
            loader.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', loadNetwork);
</script>
