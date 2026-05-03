<?php
use App\Utils\Helpers;

$pageTitle = 'Dashboard Staging';
$pageDescription = 'Pruebas de gráficos avanzados - Chord Diagram';
?>

<style>
/* Estilos para gráficos D3 */
.chart-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
}

.chord path.chord-ribbon {
    fill-opacity: 0.7;
    stroke: #000;
    stroke-width: 0.5px;
    transition: fill-opacity 0.3s;
}

.chord path.chord-ribbon:hover {
    fill-opacity: 1;
}

.chord .group-arc {
    stroke: #fff;
    stroke-width: 2px;
}

.chord .group-label {
    font-size: 10px;
    font-weight: 600;
    fill: #374151;
}

.d3-tooltip {
    position: fixed;
    background: rgba(17, 24, 39, 0.95);
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    pointer-events: none;
    z-index: 9999;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    max-width: 300px;
}

.d3-tooltip .tip-title {
    font-weight: 700;
    margin-bottom: 6px;
    font-size: 14px;
    color: #60a5fa;
}

.d3-tooltip .tip-value {
    color: #fbbf24;
    font-weight: 600;
}

/* Treemap labels */
.treemap-label {
    font-size: 11px;
    font-weight: 600;
    fill: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

/* Sunburst */
.sunburst-path {
    stroke: #fff;
    stroke-width: 1px;
    cursor: pointer;
    transition: opacity 0.2s;
}

.sunburst-path:hover {
    opacity: 0.8;
}

/* Force graph */
.force-link {
    stroke: #94a3b8;
    stroke-opacity: 0.5;
}

.force-node {
    cursor: grab;
}

.force-node:active {
    cursor: grabbing;
}

.force-label {
    font-size: 9px;
    fill: #374151;
    text-anchor: middle;
    pointer-events: none;
    font-weight: 500;
}

/* Network principal */
.network-container {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 12px;
    position: relative;
    overflow: hidden;
}

.network-legend {
    position: absolute;
    bottom: 16px;
    left: 16px;
    background: rgba(255,255,255,0.95);
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}

.legend-item:last-child {
    margin-bottom: 0;
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}
</style>

<!-- Header -->
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            Dashboard Staging
            <span class="px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">EXPERIMENTAL</span>
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Gráficos avanzados con D3.js - Red Jerárquica Multinivel</p>
    </div>
    <a href="/dashboard" class="btn btn-outline">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Volver al Dashboard
    </a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <?php foreach ($mainStats as $stat): ?>
    <div class="stat-card stat-card-<?= $stat['color'] ?>">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400"><?= $stat['title'] ?></p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $stat['value'] ?></p>
            </div>
            <div class="w-12 h-12 rounded-full bg-<?= $stat['color'] ?>-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-<?= $stat['color'] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php if ($stat['icon'] === 'users'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    <?php elseif ($stat['icon'] === 'user-check'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <?php elseif ($stat['icon'] === 'trending-up'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    <?php endif; ?>
                </svg>
            </div>
        </div>
        <div class="text-sm text-<?= $stat['color'] ?>-600 font-medium"><?= $stat['change'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ========== PRINCIPAL: Red Jerárquica Multinivel ========== -->
<div class="card mb-6">
    <div class="card-header border-b border-gray-200">
        <h3 class="card-title flex items-center gap-2 text-lg">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            Red Jerárquica Multinivel
            <span class="text-sm font-normal text-gray-500 ml-2">— Arrastra los nodos para explorar</span>
        </h3>
        <p class="text-sm text-gray-500 mt-1">
            Visualización de la red completa de líderes y seguidores. El tamaño del nodo indica la cantidad de seguidores directos.
        </p>
    </div>
    <div class="p-2">
        <div id="force-network-main" class="network-container" style="height: 600px;">
            <!-- Leyenda -->
            <div class="network-legend">
                <div class="font-semibold mb-2 text-gray-700">Tamaño = Seguidores</div>
                <div class="legend-item">
                    <div class="legend-dot" style="background: #3b82f6; width: 20px; height: 20px;"></div>
                    <span>Líder con muchos seguidores</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background: #10b981; width: 12px; height: 12px;"></div>
                    <span>Líder con pocos seguidores</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background: #94a3b8; width: 8px; height: 8px;"></div>
                    <span>Sin seguidores</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 1: Chord y Sunburst -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    <!-- Chord Diagram: Perfil ↔ Municipio -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Chord: Perfil ↔ Municipio
            </h3>
            <p class="text-sm text-gray-500 mt-1">Relaciones entre perfiles y municipios principales</p>
        </div>
        <div class="p-4">
            <div id="chord-diagram" class="chart-container" style="height: 500px;"></div>
        </div>
    </div>

    <!-- Sunburst: Jerarquía Geográfica -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Sunburst: Jerarquía Geográfica
            </h3>
            <p class="text-sm text-gray-500 mt-1">Departamento → Municipio → Colaboradores</p>
        </div>
        <div class="p-4">
            <div id="sunburst-chart" class="chart-container" style="height: 500px;"></div>
        </div>
    </div>

</div>

<!-- Row 2: Treemap y Top Líderes (secundario) -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    <!-- Treemap: Distribución por Perfil -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                Treemap: Distribución por Perfil
            </h3>
            <p class="text-sm text-gray-500 mt-1">Proporción visual de colaboradores por perfil</p>
        </div>
        <div class="p-4">
            <div id="treemap-chart" style="height: 400px;"></div>
        </div>
    </div>

    <!-- Force Graph Secundario: Top Líderes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Top 10 Líderes
            </h3>
            <p class="text-sm text-gray-500 mt-1">Red simplificada de los principales líderes</p>
        </div>
        <div class="p-4">
            <div id="force-graph-secondary" style="height: 400px;"></div>
        </div>
    </div>

</div>

<!-- Row 3: Radar y Donut -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    <!-- Radar Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Radar: Métricas por Perfil
            </h3>
            <p class="text-sm text-gray-500 mt-1">Comparativa de los top 6 perfiles</p>
        </div>
        <div class="p-4">
            <div id="radar-chart" class="chart-container" style="height: 400px;"></div>
        </div>
    </div>

    <!-- Donut -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                </svg>
                Donut: Top Municipios
            </h3>
            <p class="text-sm text-gray-500 mt-1">Distribución de colaboradores por municipio</p>
        </div>
        <div class="p-4">
            <div id="donut-chart" class="chart-container" style="height: 400px;"></div>
        </div>
    </div>

</div>

<!-- Tooltip global -->
<div id="d3-tooltip" class="d3-tooltip" style="display: none;"></div>

<!-- Cargar D3.js -->
<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Iniciando gráficos D3.js...');

    const tooltip = d3.select('#d3-tooltip');

    function showTooltip(event, html) {
        tooltip.style('display', 'block')
            .html(html)
            .style('left', (event.clientX + 15) + 'px')
            .style('top', (event.clientY - 10) + 'px');
    }

    function hideTooltip() {
        tooltip.style('display', 'none');
    }

    // Datos desde PHP
    const chordData = <?= json_encode($chordData ?? ['matrix' => [], 'names' => []]) ?>;
    const sunburstData = <?= json_encode($sunburstData ?? ['name' => 'Root', 'children' => []]) ?>;
    const profileData = <?= json_encode($chartData['profiles'] ?? ['labels' => [], 'data' => []]) ?>;
    const topLeaders = <?= json_encode(array_slice($topLeaders ?? [], 0, 10)) ?>;
    const forceNetworkData = <?= json_encode($forceNetworkData ?? ['nodes' => [], 'links' => []]) ?>;

    console.log('Datos cargados:', { chordData, sunburstData, profileData, topLeaders, forceNetworkData });

    // ==========================================
    // PRINCIPAL: FORCE NETWORK (Red Jerárquica)
    // ==========================================
    function renderForceNetworkMain() {
        const container = d3.select('#force-network-main');
        container.selectAll('svg').remove();

        const nodes = forceNetworkData.nodes || [];
        const links = forceNetworkData.links || [];

        if (nodes.length === 0) {
            container.append('div')
                .attr('class', 'flex items-center justify-center h-full text-gray-500')
                .html('<p class="text-center text-lg">No hay datos de red jerárquica</p>');
            return;
        }

        const rect = container.node().getBoundingClientRect();
        const width = rect.width || 900;
        const height = 580;

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height);

        // Escala de tamaño MUY acentuada para ver diferencias
        const maxSeguidores = Math.max(...nodes.map(n => n.seguidores), 1);
        const sizeScale = d3.scalePow()
            .exponent(0.6)  // Exponente para acentuar diferencias
            .domain([0, maxSeguidores])
            .range([6, 50]); // Rango grande para ver diferencias

        // Colores por cantidad de seguidores
        const colorScale = d3.scaleSequential()
            .domain([0, maxSeguidores])
            .interpolator(d3.interpolateBlues);

        // Color especial para líderes vs no líderes
        function getNodeColor(d) {
            if (d.seguidores > 10) return '#1e40af'; // Azul oscuro - muchos seguidores
            if (d.seguidores > 5) return '#3b82f6';  // Azul - varios seguidores
            if (d.seguidores > 0) return '#10b981';  // Verde - pocos seguidores
            return '#94a3b8'; // Gris - sin seguidores
        }

        const simulation = d3.forceSimulation(nodes)
            .force('link', d3.forceLink(links)
                .id(d => d.id)
                .distance(80)
                .strength(0.5))
            .force('charge', d3.forceManyBody()
                .strength(d => -100 - d.seguidores * 10)) // Más repulsión para nodos grandes
            .force('center', d3.forceCenter(width / 2, height / 2))
            .force('collision', d3.forceCollide()
                .radius(d => sizeScale(d.seguidores) + 5));

        // Enlaces con flechas
        svg.append('defs').append('marker')
            .attr('id', 'arrowhead')
            .attr('viewBox', '-0 -5 10 10')
            .attr('refX', 20)
            .attr('refY', 0)
            .attr('orient', 'auto')
            .attr('markerWidth', 6)
            .attr('markerHeight', 6)
            .append('path')
            .attr('d', 'M 0,-5 L 10,0 L 0,5')
            .attr('fill', '#94a3b8');

        const link = svg.append('g')
            .selectAll('line')
            .data(links)
            .join('line')
            .attr('class', 'force-link')
            .attr('stroke-width', 1.5)
            .attr('marker-end', 'url(#arrowhead)');

        // Nodos
        const node = svg.append('g')
            .selectAll('g')
            .data(nodes)
            .join('g')
            .attr('class', 'force-node')
            .call(d3.drag()
                .on('start', dragstarted)
                .on('drag', dragged)
                .on('end', dragended));

        // Círculos con tamaño acentuado
        node.append('circle')
            .attr('r', d => sizeScale(d.seguidores))
            .attr('fill', d => getNodeColor(d))
            .attr('stroke', '#fff')
            .attr('stroke-width', d => d.seguidores > 5 ? 3 : 2)
            .attr('opacity', 0.9)
            .on('mouseover', function(event, d) {
                d3.select(this)
                    .attr('stroke', '#fbbf24')
                    .attr('stroke-width', 4)
                    .attr('opacity', 1);

                // Resaltar enlaces conectados
                link.style('stroke', l =>
                    l.source.id === d.id || l.target.id === d.id ? '#f59e0b' : '#94a3b8'
                ).style('stroke-width', l =>
                    l.source.id === d.id || l.target.id === d.id ? 3 : 1.5
                );

                showTooltip(event, `
                    <div class="tip-title">${d.name}</div>
                    <div><strong>Perfil:</strong> ${d.perfil}</div>
                    <div><strong>Municipio:</strong> ${d.municipio || 'N/A'}</div>
                    <div><strong>Seguidores directos:</strong> <span class="tip-value">${d.seguidores}</span></div>
                `);
            })
            .on('mouseout', function(event, d) {
                d3.select(this)
                    .attr('stroke', '#fff')
                    .attr('stroke-width', d.seguidores > 5 ? 3 : 2)
                    .attr('opacity', 0.9);

                link.style('stroke', '#94a3b8').style('stroke-width', 1.5);
                hideTooltip();
            });

        // Labels solo para líderes importantes
        node.filter(d => d.seguidores > 3)
            .append('text')
            .attr('class', 'force-label')
            .attr('dy', d => sizeScale(d.seguidores) + 12)
            .text(d => d.name.split(' ')[0]);

        simulation.on('tick', () => {
            link.attr('x1', d => d.source.x)
                .attr('y1', d => d.source.y)
                .attr('x2', d => d.target.x)
                .attr('y2', d => d.target.y);

            node.attr('transform', d => `translate(${d.x}, ${d.y})`);
        });

        function dragstarted(event) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            event.subject.fx = event.subject.x;
            event.subject.fy = event.subject.y;
        }

        function dragged(event) {
            event.subject.fx = event.x;
            event.subject.fy = event.y;
        }

        function dragended(event) {
            if (!event.active) simulation.alphaTarget(0);
            event.subject.fx = null;
            event.subject.fy = null;
        }

        console.log('Force Network Main renderizado con', nodes.length, 'nodos y', links.length, 'enlaces');
    }

    // ==========================================
    // CHORD DIAGRAM
    // ==========================================
    function renderChordDiagram() {
        const container = d3.select('#chord-diagram');
        container.selectAll('*').remove();

        if (!chordData.matrix || chordData.matrix.length === 0) {
            container.append('div')
                .attr('class', 'flex items-center justify-center h-full text-gray-500')
                .html('<p class="text-center">No hay datos suficientes<br>para el chord diagram</p>');
            return;
        }

        const width = 500;
        const height = 500;
        const innerRadius = Math.min(width, height) * 0.35;
        const outerRadius = innerRadius + 20;

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height)
            .append('g')
            .attr('transform', `translate(${width/2}, ${height/2})`)
            .attr('class', 'chord');

        const chord = d3.chord()
            .padAngle(0.05)
            .sortSubgroups(d3.descending);

        const arc = d3.arc()
            .innerRadius(innerRadius)
            .outerRadius(outerRadius);

        const ribbon = d3.ribbon()
            .radius(innerRadius);

        const names = chordData.names;
        const matrix = chordData.matrix;
        const chords = chord(matrix);

        const color = d3.scaleOrdinal()
            .domain(d3.range(names.length))
            .range(d3.schemeTableau10.concat(d3.schemePastel1));

        const group = svg.append('g')
            .selectAll('g')
            .data(chords.groups)
            .join('g');

        group.append('path')
            .attr('class', 'group-arc')
            .attr('fill', d => color(d.index))
            .attr('d', arc)
            .on('mouseover', function(event, d) {
                svg.selectAll('.chord-ribbon')
                    .style('opacity', c => c.source.index === d.index || c.target.index === d.index ? 1 : 0.1);
                showTooltip(event, `
                    <div class="tip-title">${names[d.index]}</div>
                    <div>Total: <span class="tip-value">${d3.sum(matrix[d.index])}</span> colaboradores</div>
                `);
            })
            .on('mouseout', function() {
                svg.selectAll('.chord-ribbon').style('opacity', 0.7);
                hideTooltip();
            });

        group.append('text')
            .attr('class', 'group-label')
            .each(d => { d.angle = (d.startAngle + d.endAngle) / 2; })
            .attr('dy', '0.35em')
            .attr('transform', d => `
                rotate(${(d.angle * 180 / Math.PI - 90)})
                translate(${outerRadius + 8})
                ${d.angle > Math.PI ? 'rotate(180)' : ''}
            `)
            .attr('text-anchor', d => d.angle > Math.PI ? 'end' : null)
            .text(d => names[d.index].length > 15 ? names[d.index].substring(0, 15) + '...' : names[d.index]);

        svg.append('g')
            .attr('fill-opacity', 0.7)
            .selectAll('path')
            .data(chords)
            .join('path')
            .attr('class', 'chord-ribbon')
            .attr('d', ribbon)
            .attr('fill', d => color(d.source.index))
            .attr('stroke', d => d3.rgb(color(d.source.index)).darker())
            .on('mouseover', function(event, d) {
                d3.select(this).style('opacity', 1);
                showTooltip(event, `
                    <div class="tip-title">${names[d.source.index]} ↔ ${names[d.target.index]}</div>
                    <div>Conexiones: <span class="tip-value">${d.source.value}</span></div>
                `);
            })
            .on('mouseout', function() {
                d3.select(this).style('opacity', 0.7);
                hideTooltip();
            });

        console.log('Chord diagram renderizado');
    }

    // ==========================================
    // SUNBURST CHART
    // ==========================================
    function renderSunburst() {
        const container = d3.select('#sunburst-chart');
        container.selectAll('*').remove();

        if (!sunburstData.children || sunburstData.children.length === 0) {
            container.append('div')
                .attr('class', 'flex items-center justify-center h-full text-gray-500')
                .html('<p class="text-center">No hay datos geográficos<br>para el sunburst</p>');
            return;
        }

        const width = 500;
        const height = 500;
        const radius = Math.min(width, height) / 2;

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height)
            .append('g')
            .attr('transform', `translate(${width/2}, ${height/2})`);

        const partition = d3.partition()
            .size([2 * Math.PI, radius]);

        const root = d3.hierarchy(sunburstData)
            .sum(d => d.value || 0)
            .sort((a, b) => b.value - a.value);

        partition(root);

        const color = d3.scaleOrdinal(d3.schemeTableau10);

        const arc = d3.arc()
            .startAngle(d => d.x0)
            .endAngle(d => d.x1)
            .padAngle(d => Math.min((d.x1 - d.x0) / 2, 0.005))
            .padRadius(radius / 2)
            .innerRadius(d => d.y0)
            .outerRadius(d => d.y1 - 1);

        svg.selectAll('path')
            .data(root.descendants().filter(d => d.depth))
            .join('path')
            .attr('class', 'sunburst-path')
            .attr('fill', d => { while (d.depth > 1) d = d.parent; return color(d.data.name); })
            .attr('fill-opacity', d => 1 - d.depth * 0.2)
            .attr('d', arc)
            .on('mouseover', function(event, d) {
                d3.select(this).attr('fill-opacity', 1);
                const path = d.ancestors().map(n => n.data.name).reverse().slice(1).join(' → ');
                showTooltip(event, `
                    <div class="tip-title">${d.data.name}</div>
                    <div style="font-size: 11px; color: #9ca3af; margin-bottom: 4px;">${path}</div>
                    <div>Colaboradores: <span class="tip-value">${d.value}</span></div>
                `);
            })
            .on('mouseout', function(event, d) {
                d3.select(this).attr('fill-opacity', 1 - d.depth * 0.2);
                hideTooltip();
            });

        svg.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '-0.3em')
            .attr('font-size', '24px')
            .attr('font-weight', 'bold')
            .attr('fill', '#374151')
            .text(root.value);

        svg.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '1.2em')
            .attr('font-size', '12px')
            .attr('fill', '#6b7280')
            .text('Total');

        console.log('Sunburst renderizado');
    }

    // ==========================================
    // TREEMAP
    // ==========================================
    function renderTreemap() {
        const container = d3.select('#treemap-chart');
        container.selectAll('*').remove();

        if (!profileData.labels || profileData.labels.length === 0) {
            container.append('div')
                .attr('class', 'flex items-center justify-center h-full text-gray-500')
                .html('<p>No hay datos de perfiles</p>');
            return;
        }

        const rect = container.node().getBoundingClientRect();
        const width = rect.width || 500;
        const height = 380;

        const data = {
            name: 'Colaboradores',
            children: profileData.labels.map((label, i) => ({
                name: label,
                value: profileData.data[i] || 0
            })).filter(d => d.value > 0)
        };

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height);

        const root = d3.treemap()
            .size([width, height])
            .paddingOuter(4)
            .paddingInner(2)
            .round(true)
            (d3.hierarchy(data).sum(d => d.value).sort((a, b) => b.value - a.value));

        const color = d3.scaleOrdinal(d3.schemeTableau10);

        const leaf = svg.selectAll('g')
            .data(root.leaves())
            .join('g')
            .attr('transform', d => `translate(${d.x0},${d.y0})`);

        leaf.append('rect')
            .attr('fill', (d, i) => color(i))
            .attr('fill-opacity', 0.85)
            .attr('width', d => Math.max(0, d.x1 - d.x0))
            .attr('height', d => Math.max(0, d.y1 - d.y0))
            .attr('rx', 4)
            .style('cursor', 'pointer')
            .on('mouseover', function(event, d) {
                d3.select(this).attr('fill-opacity', 1);
                const percent = ((d.value / root.value) * 100).toFixed(1);
                showTooltip(event, `
                    <div class="tip-title">${d.data.name}</div>
                    <div>Colaboradores: <span class="tip-value">${d.value}</span></div>
                    <div>Porcentaje: <span class="tip-value">${percent}%</span></div>
                `);
            })
            .on('mouseout', function() {
                d3.select(this).attr('fill-opacity', 0.85);
                hideTooltip();
            });

        leaf.append('text')
            .attr('class', 'treemap-label')
            .attr('x', 6)
            .attr('y', 18)
            .text(d => {
                const w = d.x1 - d.x0;
                if (w < 60) return '';
                const maxChars = Math.floor(w / 8);
                return d.data.name.length > maxChars ? d.data.name.substring(0, maxChars) + '...' : d.data.name;
            });

        leaf.append('text')
            .attr('class', 'treemap-label')
            .attr('x', 6)
            .attr('y', 36)
            .attr('fill-opacity', 0.8)
            .text(d => d.x1 - d.x0 > 50 ? d.value : '');

        console.log('Treemap renderizado');
    }

    // ==========================================
    // FORCE GRAPH SECUNDARIO (Top Líderes)
    // ==========================================
    function renderForceGraphSecondary() {
        const container = d3.select('#force-graph-secondary');
        container.selectAll('*').remove();

        if (!topLeaders || topLeaders.length === 0) {
            container.append('div')
                .attr('class', 'flex items-center justify-center h-full text-gray-500')
                .html('<p>No hay datos de líderes</p>');
            return;
        }

        const rect = container.node().getBoundingClientRect();
        const width = rect.width || 500;
        const height = 380;

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height);

        const nodes = topLeaders.map((leader, i) => ({
            id: i,
            name: leader.nombre_completo || (leader.nombres + ' ' + leader.apellidos) || 'Líder ' + i,
            perfil: leader.perfil || 'Sin perfil',
            seguidores: parseInt(leader.total_seguidores_directos) || 1
        }));

        const maxSeg = Math.max(...nodes.map(n => n.seguidores), 1);

        // Escala de tamaño acentuada
        const sizeScale = d3.scalePow()
            .exponent(0.5)
            .domain([0, maxSeg])
            .range([10, 45]);

        const links = [];
        if (nodes.length > 1) {
            for (let i = 1; i < nodes.length; i++) {
                links.push({ source: 0, target: i, value: nodes[i].seguidores });
            }
        }

        const simulation = d3.forceSimulation(nodes)
            .force('link', d3.forceLink(links).id(d => d.id).distance(100))
            .force('charge', d3.forceManyBody().strength(-300))
            .force('center', d3.forceCenter(width / 2, height / 2))
            .force('collision', d3.forceCollide().radius(d => sizeScale(d.seguidores) + 8));

        const color = d3.scaleOrdinal(d3.schemeTableau10);

        const link = svg.append('g')
            .selectAll('line')
            .data(links)
            .join('line')
            .attr('class', 'force-link')
            .attr('stroke-width', d => Math.min(d.value / 3, 4) + 1);

        const node = svg.append('g')
            .selectAll('g')
            .data(nodes)
            .join('g')
            .attr('class', 'force-node')
            .call(d3.drag()
                .on('start', dragstarted)
                .on('drag', dragged)
                .on('end', dragended));

        node.append('circle')
            .attr('r', d => sizeScale(d.seguidores))
            .attr('fill', (d, i) => color(i))
            .attr('stroke', 'white')
            .attr('stroke-width', 2)
            .on('mouseover', function(event, d) {
                d3.select(this).attr('stroke-width', 4).attr('stroke', '#fbbf24');
                showTooltip(event, `
                    <div class="tip-title">${d.name}</div>
                    <div>Perfil: ${d.perfil}</div>
                    <div>Seguidores: <span class="tip-value">${d.seguidores}</span></div>
                `);
            })
            .on('mouseout', function() {
                d3.select(this).attr('stroke-width', 2).attr('stroke', 'white');
                hideTooltip();
            });

        node.append('text')
            .attr('class', 'force-label')
            .attr('dy', d => sizeScale(d.seguidores) + 14)
            .text(d => d.name.split(' ')[0]);

        simulation.on('tick', () => {
            link.attr('x1', d => d.source.x)
                .attr('y1', d => d.source.y)
                .attr('x2', d => d.target.x)
                .attr('y2', d => d.target.y);
            node.attr('transform', d => `translate(${d.x}, ${d.y})`);
        });

        function dragstarted(event) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            event.subject.fx = event.subject.x;
            event.subject.fy = event.subject.y;
        }

        function dragged(event) {
            event.subject.fx = event.x;
            event.subject.fy = event.y;
        }

        function dragended(event) {
            if (!event.active) simulation.alphaTarget(0);
            event.subject.fx = null;
            event.subject.fy = null;
        }

        console.log('Force Graph Secundario renderizado');
    }

    // ==========================================
    // RADAR CHART
    // ==========================================
    function renderRadarChart() {
        const container = d3.select('#radar-chart');
        container.selectAll('*').remove();

        if (!profileData.labels || profileData.labels.length < 3) {
            container.append('div')
                .attr('class', 'flex items-center justify-center h-full text-gray-500')
                .html('<p>Se necesitan al menos 3 perfiles</p>');
            return;
        }

        const width = 400;
        const height = 400;
        const margin = 70;
        const radius = Math.min(width, height) / 2 - margin;

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height)
            .append('g')
            .attr('transform', `translate(${width/2}, ${height/2})`);

        const topData = profileData.labels.slice(0, 6).map((label, i) => ({
            axis: label,
            value: profileData.data[i] || 0
        }));

        const maxValue = Math.max(...topData.map(d => d.value));
        const total = topData.length;
        const angleSlice = Math.PI * 2 / total;

        const rScale = d3.scaleLinear().domain([0, maxValue]).range([0, radius]);

        const levels = 5;
        for (let level = 1; level <= levels; level++) {
            svg.append('circle')
                .attr('r', radius * level / levels)
                .attr('fill', 'none')
                .attr('stroke', '#e5e7eb')
                .attr('stroke-width', 1);
        }

        topData.forEach((d, i) => {
            const angle = angleSlice * i - Math.PI / 2;
            svg.append('line')
                .attr('x1', 0).attr('y1', 0)
                .attr('x2', rScale(maxValue) * Math.cos(angle))
                .attr('y2', rScale(maxValue) * Math.sin(angle))
                .attr('stroke', '#d1d5db')
                .attr('stroke-width', 1);

            svg.append('text')
                .attr('x', (radius + 20) * Math.cos(angle))
                .attr('y', (radius + 20) * Math.sin(angle))
                .attr('text-anchor', 'middle')
                .attr('dy', '0.35em')
                .attr('font-size', '10px')
                .attr('fill', '#374151')
                .text(d.axis.length > 12 ? d.axis.substring(0, 12) + '...' : d.axis);
        });

        const radarLine = d3.lineRadial()
            .radius(d => rScale(d.value))
            .angle((d, i) => i * angleSlice)
            .curve(d3.curveLinearClosed);

        svg.append('path')
            .datum(topData)
            .attr('d', radarLine)
            .attr('fill', '#3b82f6')
            .attr('fill-opacity', 0.35)
            .attr('stroke', '#3b82f6')
            .attr('stroke-width', 2);

        topData.forEach((d, i) => {
            const angle = angleSlice * i - Math.PI / 2;
            svg.append('circle')
                .attr('cx', rScale(d.value) * Math.cos(angle))
                .attr('cy', rScale(d.value) * Math.sin(angle))
                .attr('r', 5)
                .attr('fill', '#3b82f6')
                .attr('stroke', 'white')
                .attr('stroke-width', 2)
                .style('cursor', 'pointer')
                .on('mouseover', function(event) {
                    d3.select(this).attr('r', 8);
                    showTooltip(event, `
                        <div class="tip-title">${d.axis}</div>
                        <div>Colaboradores: <span class="tip-value">${d.value}</span></div>
                    `);
                })
                .on('mouseout', function() {
                    d3.select(this).attr('r', 5);
                    hideTooltip();
                });
        });

        console.log('Radar chart renderizado');
    }

    // ==========================================
    // DONUT CHART
    // ==========================================
    function renderDonutChart() {
        const container = d3.select('#donut-chart');
        container.selectAll('*').remove();

        const municipioNames = chordData.names ? chordData.names.slice(6) : [];
        const municipioValues = [];

        if (chordData.matrix && chordData.matrix.length > 6) {
            for (let i = 6; i < chordData.matrix.length; i++) {
                municipioValues.push(d3.sum(chordData.matrix[i]));
            }
        }

        if (municipioNames.length === 0) {
            container.append('div')
                .attr('class', 'flex items-center justify-center h-full text-gray-500')
                .html('<p>No hay datos de municipios</p>');
            return;
        }

        const width = 400;
        const height = 400;
        const radius = Math.min(width, height) / 2 - 20;

        const svg = container.append('svg')
            .attr('width', width)
            .attr('height', height)
            .append('g')
            .attr('transform', `translate(${width/2}, ${height/2})`);

        const data = municipioNames.map((name, i) => ({
            name: name,
            value: municipioValues[i] || 0
        })).filter(d => d.value > 0);

        const pie = d3.pie().value(d => d.value).sort(null);
        const arc = d3.arc().innerRadius(radius * 0.5).outerRadius(radius);
        const color = d3.scaleOrdinal(d3.schemeTableau10);

        const arcs = svg.selectAll('arc')
            .data(pie(data))
            .join('g');

        arcs.append('path')
            .attr('d', arc)
            .attr('fill', (d, i) => color(i))
            .attr('stroke', 'white')
            .attr('stroke-width', 2)
            .style('cursor', 'pointer')
            .on('mouseover', function(event, d) {
                d3.select(this).attr('opacity', 0.8);
                const total = d3.sum(data, x => x.value);
                const percent = ((d.data.value / total) * 100).toFixed(1);
                showTooltip(event, `
                    <div class="tip-title">${d.data.name}</div>
                    <div>Colaboradores: <span class="tip-value">${d.data.value}</span></div>
                    <div>Porcentaje: <span class="tip-value">${percent}%</span></div>
                `);
            })
            .on('mouseout', function() {
                d3.select(this).attr('opacity', 1);
                hideTooltip();
            });

        const total = d3.sum(data, d => d.value);
        svg.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '-0.3em')
            .attr('font-size', '28px')
            .attr('font-weight', 'bold')
            .attr('fill', '#374151')
            .text(total);

        svg.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '1.3em')
            .attr('font-size', '12px')
            .attr('fill', '#6b7280')
            .text('Colaboradores');

        console.log('Donut chart renderizado');
    }

    // Renderizar todos
    try {
        renderForceNetworkMain();
        renderChordDiagram();
        renderSunburst();
        renderTreemap();
        renderForceGraphSecondary();
        renderRadarChart();
        renderDonutChart();
        console.log('Todos los gráficos renderizados correctamente');
    } catch (error) {
        console.error('Error renderizando gráficos:', error);
    }

    // Re-render en resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            renderForceNetworkMain();
            renderTreemap();
            renderForceGraphSecondary();
        }, 250);
    });
});
</script>
