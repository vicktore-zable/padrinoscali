<?php
/**
 * Vista de Reporte por Territorios
 */
$pageTitle = 'Reporte por Territorios';
$pageDescription = 'Distribución geográfica de colaboradores';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Reporte por Territorios</h1>
                <p class="text-gray-600">Distribución geográfica de colaboradores</p>
            </div>
            <div class="flex space-x-4">
                <a href="/reports" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    ← Volver
                </a>
                <a href="/reports/export?type=territories&format=csv" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                    📊 Exportar CSV
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($stats)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>No hay datos disponibles.</strong> No se encontraron estadísticas de territorios.
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Estadísticas generales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-blue-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($stats, 'total_colaboradores')) ?: array_sum(array_column($stats, 'total')); ?></div>
                <div class="text-gray-600">Total Colaboradores</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-green-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($stats, 'creciendo')); ?></div>
                <div class="text-gray-600">Creciendo</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-blue-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($stats, 'estancado')); ?></div>
                <div class="text-gray-600">Estancado</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-red-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($stats, 'decreciendo')); ?></div>
                <div class="text-gray-600">Decreciendo</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-green-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo count($territoriosData); ?></div>
                <div class="text-gray-600">Territorios Activos</div>
            </div>

        </div>

        <!-- Gráficos Superiores -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Gráfico Circular - Colaboradores por Municipio -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Colaboradores por Municipio</h3>
                <div class="relative">
                    <canvas id="citiesChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- Gráfico de Barras Verticales - Perfiles por Municipio -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" id="profilesTitle">Perfiles por Municipio</h3>
                <div class="relative">
                    <canvas id="profilesChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Barras - Territorios por Municipio -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4" id="territoriesTitle">Territorios por Municipio</h3>
            <div class="relative">
                <canvas id="communesChart" width="800" height="400"></canvas>
            </div>
        </div>

        <!-- Tabla General de Territorios -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Distribución por Territorios</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">País</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Municipio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barrio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Territorio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creciendo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estancado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Decreciendo</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $totalGeneral = array_sum(array_column($stats, 'total_colaboradores')) ?: array_sum(array_column($stats, 'total'));
                        foreach ($territoriosData as $territorioData):
                            $stat = $territorioData['stats'];
                            $porcentaje = $totalGeneral > 0 ? round((($stat['total_colaboradores'] ?? $stat['total'] ?? 0) / $totalGeneral) * 100, 1) : 0;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($territorioData['pais']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($territorioData['departamento']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($territorioData['municipio']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($stat['barrio'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo $stat['tipo_territorio'] === 'Municipio' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo htmlspecialchars($stat['tipo_territorio']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($territorioData['territorio']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $stat['total_colaboradores'] ?? $stat['total'] ?? 0; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $porcentaje; ?>%</div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo $stat['creciendo'] ?? 0; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo $stat['estancado'] ?? 0; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <?php echo $stat['decreciendo'] ?? 0; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para los gráficos
    const territoriesData = <?php echo json_encode($stats); ?>;
    const territoriesInfo = <?php echo json_encode($territoriosData); ?>;
    const profilesData = <?php echo json_encode($perfilesPorTerritorio); ?>;
    const colaboradoresPorMunicipio = <?php echo json_encode($colaboradoresPorMunicipio); ?>;
    const perfilesPorMunicipio = <?php echo json_encode($perfilesPorMunicipio); ?>;
    const territoriosPorMunicipio = <?php echo json_encode($territoriosPorMunicipio); ?>;

    // Colores para los gráficos
    const colors = [
        '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
        '#06B6D4', '#F97316', '#84CC16', '#EC4899', '#6B7280'
    ];

    // Gráfico Circular - Colaboradores por Municipio
    const citiesCtx = document.getElementById('citiesChart').getContext('2d');
    const citiesChart = new Chart(citiesCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(colaboradoresPorMunicipio),
            datasets: [{
                label: 'Colaboradores',
                data: Object.values(colaboradoresPorMunicipio),
                backgroundColor: colors.slice(0, Object.keys(colaboradoresPorMunicipio).length),
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const municipioName = Object.keys(colaboradoresPorMunicipio)[index];
                    updateProfilesChart(municipioName);
                    updateTerritoriesChart(municipioName);
                }
            }
        }
    });

    // Gráfico de Barras Horizontales - Colaboradores por Territorio (filtrado por municipio)
    const communesCtx = document.getElementById('communesChart').getContext('2d');
    let communesChart = new Chart(communesCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Colaboradores',
                data: [],
                backgroundColor: colors[0],
                borderColor: colors[0],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Barras horizontales
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Gráfico de Barras - Perfiles por Territorio (filtrado por municipio)
    const profilesCtx = document.getElementById('profilesChart').getContext('2d');
    let profilesChart = new Chart(profilesCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Cantidad',
                data: [],
                backgroundColor: colors[0],
                borderColor: colors[0],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Función para actualizar el gráfico de perfiles
    function updateProfilesChart(municipio) {
        // Filtrar perfiles por territorio dentro del municipio seleccionado
        const territoriesFiltered = territoriesData.filter(territory =>
            territory.municipio === municipio || territory.territorio === municipio
        );

        // Combinar perfiles de todos los territorios del municipio
        const combinedProfiles = {};
        territoriesFiltered.forEach(territory => {
            const territoryProfiles = profilesData[territory.territorio] || {};
            Object.keys(territoryProfiles).forEach(profile => {
                if (!combinedProfiles[profile]) {
                    combinedProfiles[profile] = 0;
                }
                combinedProfiles[profile] += territoryProfiles[profile];
            });
        });

        const labels = Object.keys(combinedProfiles);
        const data = Object.values(combinedProfiles);

        profilesChart.data.labels = labels;
        profilesChart.data.datasets[0].data = data;
        profilesChart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
        profilesChart.data.datasets[0].borderColor = colors.slice(0, labels.length);
        profilesChart.update();

        document.getElementById('profilesTitle').textContent = `Perfiles por Territorio en ${municipio}`;
    }

    // Función para actualizar el gráfico de territorios
    function updateTerritoriesChart(municipio) {
        // Filtrar territorios por municipio seleccionado
        const territoriesFiltered = territoriesData.filter(territory =>
            territory.municipio === municipio || territory.territorio === municipio
        );

        const labels = territoriesFiltered.map(t => t.territorio);
        const data = territoriesFiltered.map(t => t.total_colaboradores || t.total || 0);

        communesChart.data.labels = labels;
        communesChart.data.datasets[0].data = data;
        communesChart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
        communesChart.data.datasets[0].borderColor = colors.slice(0, labels.length);
        communesChart.update();

        document.getElementById('territoriesTitle').textContent = `Colaboradores por Territorio en ${municipio}`;
    }

    // Mostrar perfiles y territorios del primer municipio por defecto
    if (Object.keys(colaboradoresPorMunicipio).length > 0) {
        const firstMunicipio = Object.keys(colaboradoresPorMunicipio)[0];
        updateProfilesChart(firstMunicipio);
        updateTerritoriesChart(firstMunicipio);
    }
});
</script>
<?php $scripts = ob_get_clean(); ?>