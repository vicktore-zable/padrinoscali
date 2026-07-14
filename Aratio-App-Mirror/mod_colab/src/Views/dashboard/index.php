<?php
use App\Utils\Helpers;

$pageTitle = 'Dashboard';
$pageDescription = 'Resumen general del sistema de colaboradores';
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <?php foreach ($mainStats as $stat): ?>
    <div class="stat-card stat-card-<?= $stat['color'] ?>">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400"><?= $stat['title'] ?></p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <?= $stat['value'] ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full bg-<?= $stat['color'] ?>-100 dark:bg-<?= $stat['color'] ?>-900 flex items-center justify-center">
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
        <div class="flex items-center text-sm">
            <span class="text-<?= $stat['color'] ?>-600 font-medium">
                <?= $stat['change'] ?>
            </span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts Row 1: Perfil y Estado -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Gráfico por Perfil -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Colaboradores por Perfil</h3>
        </div>
        <div id="chart-profiles" style="height: 300px;"></div>
    </div>

    <!-- Gráfico por Estado -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Distribución por Estado</h3>
        </div>
        <div id="chart-estados" style="height: 300px;"></div>
    </div>

</div>

<!-- Charts Row 2: Departamentos y Municipios -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Gráfico por Territorio/Departamento -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top 10 Departamentos</h3>
        </div>
        <div id="chart-territories" style="height: 300px;"></div>
    </div>

    <!-- Gráfico por Municipio (Barras) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top 15 Municipios por Colaboradores</h3>
        </div>
        <div id="chart-municipios-bar" style="height: 300px;"></div>
    </div>

</div>

<!-- Charts Row 3: Burbujas Municipios y Áreas de Interés -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Mapa de Burbujas por Municipio -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Distribución por Municipio (Padrinos)
            </h3>
            <p class="text-sm text-gray-500 mt-1">Tamaño de burbuja = Número de padrinos</p>
        </div>
        <div id="chart-municipios-bubble" style="height: 350px;"></div>
    </div>

    <!-- Gráfico de Burbujas por Áreas de Interés -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg class="w-5 h-5 inline-block mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                Áreas de Interés Consolidadas
            </h3>
            <p class="text-sm text-gray-500 mt-1">Tamaño de burbuja = Personas interesadas</p>
        </div>
        <div id="chart-areas-bubble" style="height: 350px;"></div>
    </div>

</div>

<!-- Charts Row 4: Evolución de Estados en el Tiempo -->
<div class="grid grid-cols-1 gap-6 mb-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg class="w-5 h-5 inline-block mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                Evolución de Estados en el Tiempo
            </h3>
            <p class="text-sm text-gray-500 mt-1">Tendencia de cambios de estado en los últimos 60 días</p>
        </div>
        <div id="chart-evolucion-estados" style="height: 350px;"></div>
    </div>
</div>

<!-- Tables Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Top Líderes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top 10 Líderes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Líder</th>
                        <th>Perfil</th>
                        <th class="text-right">Seguidores</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topLeaders as $leader): ?>
                    <tr>
                        <td>
                            <a href="/colaboradores/<?= $leader['id'] ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                                <?= e($leader['nombre_completo']) ?>
                            </a>
                        </td>
                        <td>
                            <?= Helpers::estadoBadge($leader['perfil']) ?>
                        </td>
                        <td class="text-right font-medium">
                            <?= number_format($leader['total_seguidores_directos']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Colaboradores Nuevos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Colaboradores Recientes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Perfil</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newColaboradores as $colaborador): ?>
                    <tr>
                        <td>
                            <a href="/colaboradores/<?= $colaborador['id'] ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                                <?= Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos']) ?>
                            </a>
                        </td>
                        <td>
                            <?= Helpers::estadoBadge($colaborador['perfil']) ?>
                        </td>
                        <td class="text-sm text-gray-600">
                            <?= Helpers::timeAgo($colaborador['created_at']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ApexCharts Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Colores
    const colors = {
        primary: '#3b82f6',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        purple: '#8b5cf6',
        pink: '#ec4899',
        indigo: '#6366f1',
        teal: '#14b8a6',
        orange: '#f97316'
    };

    // Paleta de colores variados
    const colorPalette = [
        colors.primary, colors.success, colors.warning, colors.danger, colors.info,
        colors.purple, colors.pink, colors.indigo, colors.teal, colors.orange,
        '#6b7280', '#64748b', '#84cc16', '#22c55e', '#0ea5e9'
    ];

    // Gráfico de Perfiles (Pie)
    const chartProfiles = new ApexCharts(document.querySelector("#chart-profiles"), {
        series: <?= json_encode($chartData['profiles']['data']) ?>,
        chart: {
            type: 'pie',
            height: 300
        },
        labels: <?= json_encode($chartData['profiles']['labels']) ?>,
        colors: colorPalette,
        legend: {
            position: 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: { width: 300 },
                legend: { position: 'bottom' }
            }
        }]
    });
    chartProfiles.render();

    // Gráfico de Estados (Donut)
    const chartEstados = new ApexCharts(document.querySelector("#chart-estados"), {
        series: <?= json_encode($chartData['estados']['data']) ?>,
        chart: {
            type: 'donut',
            height: 300
        },
        labels: <?= json_encode($chartData['estados']['labels']) ?>,
        colors: [colors.primary, colors.success, colors.warning, colors.orange, colors.danger],
        legend: {
            position: 'bottom'
        }
    });
    chartEstados.render();

    // Gráfico de Territorios/Departamentos (Bar horizontal)
    const chartTerritories = new ApexCharts(document.querySelector("#chart-territories"), {
        series: [{
            name: 'Colaboradores',
            data: <?= json_encode(array_slice($chartData['territories']['data'], 0, 10)) ?>
        }],
        chart: {
            type: 'bar',
            height: 300
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: <?= json_encode(array_slice($chartData['territories']['labels'], 0, 10)) ?>
        },
        colors: [colors.primary]
    });
    chartTerritories.render();

    // Gráfico de Municipios (Bar horizontal)
    const chartMunicipiosBar = new ApexCharts(document.querySelector("#chart-municipios-bar"), {
        series: [{
            name: 'Colaboradores',
            data: <?= json_encode($chartData['municipios']['data']) ?>
        }],
        chart: {
            type: 'bar',
            height: 300
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
                distributed: true
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: <?= json_encode($chartData['municipios']['labels']) ?>
        },
        colors: colorPalette,
        legend: { show: false },
        tooltip: {
            y: {
                formatter: function(val, opts) {
                    const departamentos = <?= json_encode($chartData['municipios']['departamentos']) ?>;
                    return val + ' colaboradores (' + departamentos[opts.dataPointIndex] + ')';
                }
            }
        }
    });
    chartMunicipiosBar.render();

    // Gráfico de Burbujas por Municipio (Líderes)
    <?php
    // Preparar datos para el gráfico de burbujas de municipios
    $bubbleMunicipios = [];
    $municipioLabels = $chartData['municipios']['labels'] ?? [];
    $municipioData = $chartData['municipios']['data'] ?? [];
    $municipioLideres = $chartData['municipios']['lideres'] ?? [];

    for ($i = 0; $i < count($municipioLabels); $i++) {
        $bubbleMunicipios[] = [
            'x' => $municipioLabels[$i],
            'y' => $municipioData[$i],
            'z' => max(5, $municipioLideres[$i] * 3) // Tamaño basado en líderes
        ];
    }
    ?>
    const chartMunicipiosBubble = new ApexCharts(document.querySelector("#chart-municipios-bubble"), {
        series: [{
            name: 'Municipios',
            data: <?= json_encode($bubbleMunicipios) ?>
        }],
        chart: {
            type: 'bubble',
            height: 350,
            toolbar: { show: true }
        },
        dataLabels: { enabled: false },
        fill: { opacity: 0.7 },
        xaxis: {
            type: 'category',
            labels: {
                rotate: -45,
                style: { fontSize: '10px' }
            }
        },
        yaxis: {
            title: { text: 'Total Colaboradores' }
        },
        colors: [colors.info],
        tooltip: {
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const data = w.config.series[seriesIndex].data[dataPointIndex];
                const lideres = <?= json_encode($municipioLideres) ?>;
                return '<div class="p-3">' +
                    '<strong>' + data.x + '</strong><br>' +
                    'Colaboradores: ' + data.y + '<br>' +
                    'Padrinos: ' + lideres[dataPointIndex] +
                    '</div>';
            }
        }
    });
    chartMunicipiosBubble.render();

    // Gráfico de Burbujas por Áreas de Interés
    <?php
    // Preparar datos para el gráfico de burbujas de áreas de interés
    $bubbleAreas = [];
    $areaLabels = $chartData['areas_interes']['labels'] ?? [];
    $areaData = $chartData['areas_interes']['data'] ?? [];

    for ($i = 0; $i < count($areaLabels); $i++) {
        $bubbleAreas[] = [
            'x' => $areaLabels[$i],
            'y' => $areaData[$i],
            'z' => max(10, $areaData[$i] * 2) // Tamaño proporcional
        ];
    }
    ?>
    const chartAreasBubble = new ApexCharts(document.querySelector("#chart-areas-bubble"), {
        series: [{
            name: 'Áreas de Interés',
            data: <?= json_encode($bubbleAreas) ?>
        }],
        chart: {
            type: 'bubble',
            height: 350,
            toolbar: { show: true }
        },
        dataLabels: { enabled: false },
        fill: { opacity: 0.7 },
        xaxis: {
            type: 'category',
            labels: {
                rotate: -45,
                style: { fontSize: '9px' },
                trim: true,
                maxHeight: 80
            }
        },
        yaxis: {
            title: { text: 'Personas Interesadas' }
        },
        colors: [colors.purple],
        tooltip: {
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const data = w.config.series[seriesIndex].data[dataPointIndex];
                return '<div class="p-3">' +
                    '<strong>' + data.x + '</strong><br>' +
                    'Personas: ' + data.y +
                    '</div>';
            }
        }
    });
    chartAreasBubble.render();

    // Gráfico de Evolución de Estados (Líneas)
    <?php
    $tendenciaData = $chartData['tendencia_estados'] ?? [];
    $hasTendenciaData = !empty($tendenciaData['fechas']);
    ?>
    <?php if ($hasTendenciaData): ?>
    const chartEvolucionEstados = new ApexCharts(document.querySelector("#chart-evolucion-estados"), {
        series: [
            {
                name: 'Crecieron',
                data: <?= json_encode($tendenciaData['crecio']) ?>
            },
            {
                name: 'Se mantienen',
                data: <?= json_encode($tendenciaData['igual']) ?>
            },
            {
                name: 'Decrecen',
                data: <?= json_encode($tendenciaData['decrece']) ?>
            },
            {
                name: 'Nuevos',
                data: <?= json_encode($tendenciaData['nuevos']) ?>
            },
            {
                name: 'Desvinculados',
                data: <?= json_encode($tendenciaData['desvinculados']) ?>
            }
        ],
        chart: {
            type: 'area',
            height: 350,
            stacked: true,
            toolbar: { show: true },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        colors: [colors.success, colors.info, colors.warning, colors.primary, colors.danger],
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.6,
                opacityTo: 0.1
            }
        },
        xaxis: {
            categories: <?= json_encode($tendenciaData['fechas']) ?>,
            type: 'datetime',
            labels: {
                datetimeFormatter: {
                    year: 'yyyy',
                    month: "MMM 'yy",
                    day: 'dd MMM',
                    hour: 'HH:mm'
                },
                style: { fontSize: '10px' }
            }
        },
        yaxis: {
            title: { text: 'Cantidad de Colaboradores' },
            labels: {
                formatter: function(val) {
                    return val.toFixed(0);
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center'
        },
        tooltip: {
            shared: true,
            intersect: false,
            x: {
                format: 'dd MMM yyyy'
            }
        },
        grid: {
            borderColor: '#e7e7e7'
        }
    });
    chartEvolucionEstados.render();
    <?php else: ?>
    // No hay datos de tendencia - mostrar mensaje
    document.getElementById('chart-evolucion-estados').innerHTML = `
        <div class="flex items-center justify-center h-full text-gray-500">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-lg font-medium">Sin datos de trazabilidad</p>
                <p class="text-sm mt-2">Los datos de evolución de estados aparecerán cuando haya registros históricos.</p>
            </div>
        </div>
    `;
    <?php endif; ?>

});
</script>
