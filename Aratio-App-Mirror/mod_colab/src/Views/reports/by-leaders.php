<?php
/**
 * Vista de Reporte de Líderes
 */
$pageTitle = 'Reporte de Líderes';
$pageDescription = 'Ranking y métricas de líderes';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Reporte de Líderes</h1>
                <p class="text-gray-600">Ranking y métricas de líderes</p>
            </div>
            <div class="flex space-x-4">
                <a href="/reports" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    ← Volver
                </a>
                <a href="/reports/export?type=leaders&format=csv" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                    📊 Exportar CSV
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($leaders)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>No hay datos disponibles.</strong> No se encontraron líderes con seguidores.
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
                <div class="text-2xl font-bold text-gray-900"><?php echo count($leaders); ?></div>
                <div class="text-gray-600">Total Líderes</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-green-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($leaders, 'total_seguidores_directos')); ?></div>
                <div class="text-gray-600">Total Seguidores</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-purple-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo max(array_column($leaders, 'total_seguidores_directos')); ?></div>
                <div class="text-gray-600">Máx. Seguidores</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-orange-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?php echo round(array_sum(array_column($leaders, 'total_seguidores_directos')) / count($leaders), 1); ?></div>
                <div class="text-gray-600">Promedio Seguidores</div>
            </div>
        </div>

        <!-- Tabla de líderes -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Ranking de Líderes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Líder</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Territorio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seguidores Directos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Red Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $ranking = 1;
                        foreach ($leaders as $leader):
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($ranking <= 3): ?>
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                            <?php echo $ranking === 1 ? 'bg-yellow-100 text-yellow-800' : ($ranking === 2 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800'); ?>">
                                            <?php echo $ranking; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm font-medium text-gray-900"><?php echo $ranking; ?>º</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                <?php echo strtoupper(substr($leader['nombres'], 0, 1) . substr($leader['apellidos'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($leader['nombres'] . ' ' . $leader['apellidos']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($leader['documento']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($leader['perfil']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($leader['territorio']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $leader['total_seguidores_directos']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $leader['total_red_completa']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo $leader['estado'] === 'Creciendo' ? 'bg-green-100 text-green-800' : ($leader['estado'] === 'Estancado' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo htmlspecialchars($leader['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php
                        $ranking++;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>