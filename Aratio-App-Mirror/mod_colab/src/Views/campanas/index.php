<?php
/**
 * Vista de Listado de Campañas
 */

use App\Utils\Security;

$pageTitle = $pageTitle ?? 'Gestión de Campañas';
$campanas = $campanas ?? [];
?>

<!-- Header -->
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
        <p class="mt-2 text-sm text-gray-600">Administración de campañas y grupos de colaboradores</p>
    </div>
    <a href="/campanas/create" class="btn btn-primary">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nueva Campaña
    </a>
</div>

<!-- Grid de Campañas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($campanas as $campana): ?>
        <div class="card hover:shadow-lg transition-shadow duration-300">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $campana['activo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= $campana['activo'] ? 'Activa' : 'Inactiva' ?>
                    </span>
                    <span class="text-xs font-mono text-gray-500"><?= htmlspecialchars($campana['codigo_externo']) ?></span>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 mb-2 truncate" title="<?= htmlspecialchars($campana['nombre']) ?>">
                    <?= htmlspecialchars($campana['nombre']) ?>
                </h3>
                
                <p class="text-sm text-gray-600 mb-4 line-clamp-2 min-h-[2.5rem]">
                    <?= htmlspecialchars($campana['descripcion'] ?: 'Sin descripción') ?>
                </p>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                        <span class="block text-2xl font-bold text-blue-600"><?= number_format($campana['stats']['total_colaboradores'] ?? 0) ?></span>
                        <span class="text-xs text-blue-800 uppercase font-semibold">Colaboradores</span>
                    </div>
                    <div class="bg-indigo-50 p-3 rounded-lg text-center">
                        <span class="block text-2xl font-bold text-indigo-600"><?= number_format($campana['stats']['total_lideres'] ?? 0) ?></span>
                        <span class="text-xs text-indigo-800 uppercase font-semibold">Padrinos</span>
                    </div>
                </div>

                <div class="space-y-2 text-sm text-gray-600 mb-6">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Candidato: <?= htmlspecialchars($campana['candidato_nombre'] ?: 'N/A') ?></span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Territorio: <?= htmlspecialchars($campana['territorio'] ?: 'N/A') ?></span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <a href="/campanas/<?= $campana['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Ver Detalles</a>
                    <div class="flex space-x-2">
                        <a href="/campanas/<?= $campana['id'] ?>/edit" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($campanas)): ?>
        <div class="col-span-full bg-gray-50 rounded-xl p-12 text-center border-2 border-dashed border-gray-200">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">No hay campañas registradas</h3>
            <p class="text-gray-500 mt-2">Comience creando una nueva campaña para agrupar sus colaboradores.</p>
            <a href="/campanas/create" class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Nueva Campaña
            </a>
        </div>
    <?php endif; ?>
</div>
