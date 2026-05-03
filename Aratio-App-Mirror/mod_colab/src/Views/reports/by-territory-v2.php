<?php
/**
 * Vista mejorada de Reporte por Territorios con tableros consolidados
 */
$pageTitle = 'Reporte por Territorios';
$pageDescription = 'Tableros consolidados por municipio con desglose detallado';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Reporte por Territorios</h1>
                <p class="text-gray-600">Tableros consolidados por municipio con desglose detallado</p>
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

    <?php if (empty($municipios)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>No hay datos disponibles.</strong> No se encontraron colaboradores con municipio asignado.
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Estadísticas generales -->
        <?php
            $totalGeneral = array_sum(array_column($municipios, 'total_colaboradores'));
            $totalBarrios = array_sum(array_column($municipios, 'total_barrios'));
            $totalTerritorios = array_sum(array_column($municipios, 'total_territorios'));
            $totalUrbano = array_sum(array_column($municipios, 'urbano'));
            $totalRural = array_sum(array_column($municipios, 'rural'));
        ?>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Colaboradores</p>
                        <p class="text-3xl font-bold mt-2"><?= number_format($totalGeneral) ?></p>
                    </div>
                    <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Municipios</p>
                        <p class="text-3xl font-bold mt-2"><?= count($municipios) ?></p>
                    </div>
                    <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Total Barrios</p>
                        <p class="text-3xl font-bold mt-2"><?= $totalBarrios ?></p>
                    </div>
                    <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Urbano / Rural</p>
                        <p class="text-3xl font-bold mt-2"><?= $totalUrbano ?> / <?= $totalRural ?></p>
                    </div>
                    <svg class="w-12 h-12 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-cyan-100 text-sm font-medium">Total Territorios</p>
                        <p class="text-3xl font-bold mt-2"><?= $totalTerritorios ?></p>
                    </div>
                    <svg class="w-12 h-12 text-cyan-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Tabla de Municipios -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">📍 Colaboradores por Municipio</h3>
                <p class="text-sm text-gray-600 mt-1">Haz clic en un municipio para ver el desglose detallado</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Municipio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% del Total</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Barrios</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Territorios</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Urbano</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Rural</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($municipios as $municipio): ?>
                            <?php
                                $porcentaje = $totalGeneral > 0 ? round(($municipio['total_colaboradores'] / $totalGeneral) * 100, 1) : 0;
                                $isSelected = ($municipioSeleccionado === $municipio['municipio']);
                            ?>
                            <tr class="hover:bg-blue-50 transition cursor-pointer <?= $isSelected ? 'bg-blue-100' : '' ?>"
                                onclick="window.location.href='/reports/territories?municipio=<?= urlencode($municipio['municipio']) ?>'">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($municipio['municipio']) ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($municipio['departamento']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-2xl font-bold text-blue-600"><?= number_format($municipio['total_colaboradores']) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-medium text-gray-900"><?= $porcentaje ?>%</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                            <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: <?= $porcentaje ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <?= $municipio['total_barrios'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                                        <?= $municipio['total_territorios'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= $municipio['urbano'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?= $municipio['rural'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="/reports/territories?municipio=<?= urlencode($municipio['municipio']) ?>"
                                       class="text-blue-600 hover:text-blue-900 font-medium text-sm"
                                       onclick="event.stopPropagation()">
                                        Ver Detalles →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detalles del Municipio Seleccionado -->
        <?php if ($municipioSeleccionado && $detalles): ?>
            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-t-lg shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">📊 Desglose Detallado: <?= htmlspecialchars($municipioSeleccionado) ?></h2>
                            <p class="text-blue-100 mt-1">Análisis completo de colaboradores por categorías</p>
                        </div>
                        <a href="/reports/territories" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition font-medium">
                            ✕ Cerrar
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Colaboradores por Perfil -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">👤</span>
                            Por Perfil
                        </h3>
                        <?php if (!empty($detalles['perfiles'])): ?>
                            <div class="space-y-3">
                                <?php foreach ($detalles['perfiles'] as $perfil): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($perfil['perfil']) ?></span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                            <?= $perfil['total'] ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">No hay datos de perfiles</p>
                        <?php endif; ?>
                    </div>

                    <!-- Colaboradores por Barrio -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="bg-purple-100 text-purple-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">🏘️</span>
                            Por Barrio
                        </h3>
                        <?php if (!empty($detalles['barrios'])): ?>
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                <?php foreach ($detalles['barrios'] as $barrio): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($barrio['barrio']) ?></span>
                                            <div class="flex gap-2 mt-1">
                                                <?php if ($barrio['urbano'] > 0): ?>
                                                    <span class="text-xs text-blue-600">🏙️ <?= $barrio['urbano'] ?></span>
                                                <?php endif; ?>
                                                <?php if ($barrio['rural'] > 0): ?>
                                                    <span class="text-xs text-green-600">🌾 <?= $barrio['rural'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-purple-100 text-purple-800">
                                            <?= $barrio['total'] ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">No hay datos de barrios</p>
                        <?php endif; ?>
                    </div>

                    <!-- Colaboradores por Territorio -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="bg-cyan-100 text-cyan-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">🗺️</span>
                            Por Territorio (Comunas/Corregimientos)
                        </h3>
                        <?php if (!empty($detalles['territorios'])): ?>
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                <?php foreach ($detalles['territorios'] as $territorio): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($territorio['territorio']) ?></span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <?php if ($territorio['tipo_territorio']): ?>
                                                    <span class="text-xs px-2 py-0.5 rounded <?= $territorio['tipo_territorio'] === 'Urbano' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                                                        <?= htmlspecialchars($territorio['tipo_territorio']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="text-xs text-gray-500"><?= $territorio['barrios'] ?> barrio(s)</span>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-cyan-100 text-cyan-800">
                                            <?= $territorio['total'] ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">No hay datos de territorios</p>
                        <?php endif; ?>
                    </div>

                    <!-- Colaboradores por Tipo de Territorio -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="bg-orange-100 text-orange-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">🏙️🌾</span>
                            Por Tipo de Territorio
                        </h3>
                        <?php if (!empty($detalles['tipos_territorio'])): ?>
                            <div class="space-y-3">
                                <?php foreach ($detalles['tipos_territorio'] as $tipo): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-gray-700">
                                                <?= $tipo['tipo_territorio'] === 'Urbano' ? '🏙️' : '🌾' ?>
                                                <?= htmlspecialchars($tipo['tipo_territorio']) ?>
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <?= $tipo['territorios'] ?> territorio(s) • <?= $tipo['barrios'] ?> barrio(s)
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold <?= $tipo['tipo_territorio'] === 'Urbano' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= $tipo['total'] ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">No hay datos de tipo de territorio</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
