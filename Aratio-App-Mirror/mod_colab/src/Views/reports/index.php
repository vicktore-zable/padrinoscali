<?php
/**
 * Vista de Reportes
 */
$pageTitle = 'Reportes y Estadísticas';
$pageDescription = 'Genera y exporta reportes del sistema';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Reportes y Estadísticas</h1>
        <p class="text-gray-600">Genera y exporta reportes del sistema</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="/reports/profiles" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
            <div class="text-blue-500 mb-4">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Por Perfiles</h3>
            <p class="text-gray-600 text-sm">Estadísticas agrupadas por tipo de perfil</p>
        </a>

        <a href="/reports/territories" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
            <div class="text-green-500 mb-4">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Por Territorios</h3>
            <p class="text-gray-600 text-sm">Distribución geográfica de colaboradores</p>
        </a>

        <a href="/reports/leaders" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
            <div class="text-purple-500 mb-4">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Padrinos</h3>
            <p class="text-gray-600 text-sm">Ranking y métricas de padrinos</p>
        </a>

        <a href="/reports/growth" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
            <div class="text-orange-500 mb-4">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Crecimiento</h3>
            <p class="text-gray-600 text-sm">Evolución temporal del sistema</p>
        </a>
    </div>

    <div class="bg-green-50 border-l-4 border-green-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    <strong>Módulo operativo.</strong> Todas las funcionalidades de reportes están disponibles y funcionando correctamente.
                </p>
            </div>
        </div>
    </div>
</div>
