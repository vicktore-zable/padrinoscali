<?php
/**
 * Vista Mejorada de Reporte de Líderes
 * Ranking con estadísticas avanzadas
 */
$pageTitle = 'Ranking de Líderes';
$pageDescription = 'Top líderes por número de seguidores directos y métricas de red';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">🏆 Ranking de Líderes</h1>
                <p class="text-gray-600">Top líderes por número de seguidores directos y métricas de red</p>
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
        <?php
            $totalLideres = count($leaders);
            $totalSeguidores = array_sum(array_column($leaders, 'total_seguidores_directos'));
            $maxSeguidores = max(array_column($leaders, 'total_seguidores_directos'));
            $promedioSeguidores = $totalLideres > 0 ? round($totalSeguidores / $totalLideres, 1) : 0;
        ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Líderes</p>
                        <p class="text-3xl font-bold mt-2"><?= number_format($totalLideres) ?></p>
                    </div>
                    <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Seguidores</p>
                        <p class="text-3xl font-bold mt-2"><?= number_format($totalSeguidores) ?></p>
                    </div>
                    <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Máximo Seguidores</p>
                        <p class="text-3xl font-bold mt-2"><?= number_format($maxSeguidores) ?></p>
                    </div>
                    <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Promedio Seguidores</p>
                        <p class="text-3xl font-bold mt-2"><?= number_format($promedioSeguidores, 1) ?></p>
                    </div>
                    <svg class="w-12 h-12 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Top 3 Podio -->
        <?php if (count($leaders) >= 3): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">🏅 Top 3 - Podio de Líderes</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Segundo Lugar -->
                <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-6 rounded-lg shadow-lg transform md:translate-y-8">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-300 text-gray-700 text-2xl font-bold mb-4">
                            2
                        </div>
                        <div class="mb-2">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white text-gray-700 text-xl font-bold shadow-lg">
                                <?php
                                    $names = explode(' ', $leaders[1]['nombre_completo']);
                                    echo strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
                                ?>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1"><?= htmlspecialchars($leaders[1]['nombre_completo']) ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($leaders[1]['perfil']) ?></p>
                        <div class="bg-white rounded-lg p-3 shadow">
                            <p class="text-3xl font-bold text-gray-700"><?= number_format($leaders[1]['total_seguidores_directos']) ?></p>
                            <p class="text-xs text-gray-500">Seguidores</p>
                        </div>
                    </div>
                </div>

                <!-- Primer Lugar -->
                <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 p-6 rounded-lg shadow-xl transform">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-yellow-400 text-yellow-900 text-3xl font-bold mb-4 shadow-lg">
                            👑
                        </div>
                        <div class="mb-2">
                            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-white text-yellow-700 text-2xl font-bold shadow-lg">
                                <?php
                                    $names = explode(' ', $leaders[0]['nombre_completo']);
                                    echo strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
                                ?>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-1"><?= htmlspecialchars($leaders[0]['nombre_completo']) ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($leaders[0]['perfil']) ?></p>
                        <div class="bg-white rounded-lg p-4 shadow-lg">
                            <p class="text-4xl font-bold text-yellow-600"><?= number_format($leaders[0]['total_seguidores_directos']) ?></p>
                            <p class="text-xs text-gray-500">Seguidores</p>
                        </div>
                    </div>
                </div>

                <!-- Tercer Lugar -->
                <div class="bg-gradient-to-br from-orange-100 to-orange-200 p-6 rounded-lg shadow-lg transform md:translate-y-8">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-300 text-orange-900 text-2xl font-bold mb-4">
                            3
                        </div>
                        <div class="mb-2">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white text-orange-700 text-xl font-bold shadow-lg">
                                <?php
                                    $names = explode(' ', $leaders[2]['nombre_completo']);
                                    echo strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
                                ?>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1"><?= htmlspecialchars($leaders[2]['nombre_completo']) ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($leaders[2]['perfil']) ?></p>
                        <div class="bg-white rounded-lg p-3 shadow">
                            <p class="text-3xl font-bold text-orange-600"><?= number_format($leaders[2]['total_seguidores_directos']) ?></p>
                            <p class="text-xs text-gray-500">Seguidores</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla completa de líderes -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">📊 Ranking Completo de Líderes</h3>
                <p class="text-sm text-gray-600 mt-1">Top <?= count($leaders) ?> líderes ordenados por número de seguidores directos</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Líder</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Territorio</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Seguidores Directos</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Potencial Promedio</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $ranking = 1;
                        foreach ($leaders as $leader):
                            $bgClass = $ranking <= 3 ? 'bg-blue-50' : 'hover:bg-gray-50';
                        ?>
                        <tr class="<?= $bgClass ?> transition">
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if ($ranking <= 3): ?>
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full font-bold text-lg shadow
                                        <?= $ranking === 1 ? 'bg-yellow-100 text-yellow-800' : ($ranking === 2 ? 'bg-gray-200 text-gray-800' : 'bg-orange-100 text-orange-800') ?>">
                                        <?= $ranking ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-lg font-semibold text-gray-700"><?= $ranking ?>º</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow">
                                            <span class="text-lg font-bold text-white">
                                                <?php
                                                    $names = explode(' ', $leader['nombre_completo']);
                                                    echo strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">
                                            <?= htmlspecialchars($leader['nombre_completo']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">ID: <?= $leader['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($leader['documento']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($leader['perfil']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($leader['territorio'] ?? 'Sin asignar') ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex items-center px-3 py-1 rounded-lg bg-green-50">
                                    <svg class="w-4 h-4 text-green-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <span class="text-lg font-bold text-green-700"><?= number_format($leader['total_seguidores_directos']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm font-medium text-gray-700">
                                    <?= number_format($leader['promedio_potencial_seguidores'] ?? 0, 1) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="/colaboradores/<?= $leader['id'] ?>"
                                   class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 transition">
                                    Ver Perfil
                                </a>
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
