<?php
/**
 * Vista de Detalle de Campaña
 */

$campana = $campana ?? [];
$stats = $stats ?? [];
$administradores = $administradores ?? [];
?>

<div class="mb-8 flex items-center justify-between">
    <div>
        <div class="flex items-center space-x-3">
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($campana['nombre']) ?></h1>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $campana['activo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= $campana['activo'] ? 'Activa' : 'Inactiva' ?>
            </span>
        </div>
        <p class="mt-2 text-sm text-gray-600">ID Externo: <?= htmlspecialchars($campana['codigo_externo']) ?></p>
    </div>
    <div class="flex space-x-3">
        <a href="/campanas/<?= $campana['id'] ?>/edit" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Editar
        </a>
        <a href="/colaboradores?campana_id=<?= $campana['id'] ?>" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Ver Colaboradores
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Columna Izquierda: Info y Stats -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card p-6 bg-white overflow-hidden relative">
                <div class="relative z-10">
                    <span class="text-sm font-medium text-gray-500 uppercase">Total Colaboradores</span>
                    <span class="block text-4xl font-bold text-gray-900 mt-2"><?= number_format($stats['total_colaboradores'] ?? 0) ?></span>
                </div>
            </div>
            <div class="card p-6 bg-white overflow-hidden relative">
                <div class="relative z-10">
                    <span class="text-sm font-medium text-gray-500 uppercase">Líderes Activos</span>
                    <span class="block text-4xl font-bold text-gray-900 mt-2"><?= number_format($stats['total_lideres'] ?? 0) ?></span>
                </div>
            </div>
            <div class="card p-6 bg-white overflow-hidden relative">
                <div class="relative z-10">
                    <span class="text-sm font-medium text-gray-500 uppercase">Colaboradores Nuevos</span>
                    <span class="block text-4xl font-bold text-green-600 mt-2"><?= number_format($stats['nuevos'] ?? 0) ?></span>
                </div>
            </div>
        </div>

        <!-- Información Detallada -->
        <div class="card p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 border-b pb-2">Información de la Campaña</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Candidato</h3>
                    <p class="text-gray-900"><?= htmlspecialchars($campana['candidato_nombre'] ?: 'No especificado') ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Territorio</h3>
                    <p class="text-gray-900"><?= htmlspecialchars($campana['territorio'] ?: 'No especificado') ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Fecha Inicio</h3>
                    <p class="text-gray-900"><?= $campana['fecha_inicio'] ? date('d/m/Y', strtotime($campana['fecha_inicio'])) : 'N/A' ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Fecha Fin</h3>
                    <p class="text-gray-900"><?= $campana['fecha_fin'] ? date('d/m/Y', strtotime($campana['fecha_fin'])) : 'N/A' ?></p>
                </div>
                <div class="md:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Descripción</h3>
                    <p class="text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($campana['descripcion'] ?: 'Sin descripción') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Administradores -->
    <div class="space-y-8">
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900">Administradores</h2>
                <a href="/usuarios/create?campana_id=<?= $campana['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Asignar Nuevo</a>
            </div>
            <div class="space-y-4">
                <?php foreach ($administradores as $admin): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold mr-3">
                                <?= strtoupper(substr($admin['usuario'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($admin['usuario']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($admin['email']) ?></p>
                            </div>
                        </div>
                        <a href="/usuarios/<?= $admin['id'] ?>/edit" class="text-gray-400 hover:text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($administradores)): ?>
                    <p class="text-sm text-gray-500 text-center py-4 italic">No hay administradores específicos para esta campaña.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-xl">
            <h3 class="text-xl font-bold mb-2">Ayuda</h3>
            <p class="text-blue-100 text-sm mb-4">Los administradores asignados a esta campaña solo podrán ver y gestionar los colaboradores que pertenezcan a la misma.</p>
            <div class="flex items-center text-xs bg-blue-700 bg-opacity-50 p-3 rounded-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Sincronizado con aratio.mrmtech.net</span>
            </div>
        </div>
    </div>
</div>
