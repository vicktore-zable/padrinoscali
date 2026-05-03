<?php
/**
 * Vista de Reporte de Crecimiento
 */
$pageTitle = 'Reporte de Crecimiento';
$pageDescription = 'Evolución temporal del sistema';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Reporte de Crecimiento</h1>
                <p class="text-gray-600">Evolución temporal del sistema</p>
            </div>
            <div class="flex space-x-4">
                <a href="/reports" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    ← Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($growth)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>No hay datos disponibles.</strong> No se encontraron datos de crecimiento.
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($growth, 'total')); ?></div>
                <div class="text-gray-600">Total Registros</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-green-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($growth, 'creciendo')); ?></div>
                <div class="text-gray-600">Estado Creciendo</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-yellow-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($growth, 'estancado')); ?></div>
                <div class="text-gray-600">Estado Estancado</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-red-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($growth, 'decreciendo')); ?></div>
                <div class="text-gray-600">Estado Decreciendo</div>
            </div>
        </div>

        <!-- Gráfico de crecimiento -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tendencia de Crecimiento Mensual</h3>
            <div class="h-64">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Datos Mensuales</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creciendo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estancado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Decreciendo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tendencia</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $previousTotal = 0;
                        foreach ($growth as $month):
                            $tendencia = '';
                            $tendenciaClass = '';
                            if ($previousTotal > 0) {
                                $diff = $month['total'] - $previousTotal;
                                if ($diff > 0) {
                                    $tendencia = '+' . $diff . ' ↑';
                                    $tendenciaClass = 'text-green-600';
                                } elseif ($diff < 0) {
                                    $tendencia = $diff . ' ↓';
                                    $tendenciaClass = 'text-red-600';
                                } else {
                                    $tendencia = '0 →';
                                    $tendenciaClass = 'text-gray-600';
                                }
                            }
                            $previousTotal = $month['total'];
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($month['mes']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $month['total']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo $month['creciendo']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <?php echo $month['estancado']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <?php echo $month['decreciendo']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm <?php echo $tendenciaClass; ?>"><?php echo $tendencia; ?></div>
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
<?php if (!empty($growth)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('growthChart').getContext('2d');
    const growthData = <?php echo json_encode($growth); ?>;

    const labels = growthData.map(item => item.mes);
    const totalData = growthData.map(item => item.total);
    const creciendoData = growthData.map(item => item.creciendo);
    const estancadoData = growthData.map(item => item.estancado);
    const decreciendoData = growthData.map(item => item.decreciendo);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total',
                data: totalData,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Creciendo',
                data: creciendoData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }, {
                label: 'Estancado',
                data: estancadoData,
                borderColor: 'rgb(245, 158, 11)',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4
            }, {
                label: 'Decreciendo',
                data: decreciendoData,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Crecimiento Mensual del Sistema'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>
<?php $scripts = ob_get_clean(); ?>