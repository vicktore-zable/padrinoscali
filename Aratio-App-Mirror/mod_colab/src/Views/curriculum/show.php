<?php
/**
 * Vista de Visualización de Curriculum
 */

use App\Utils\Security;

$colaborador = $colaborador ?? [];
$curriculum = $curriculum ?? [];

/**
 * Helper function to safely format dates
 * @param string|null $date
 * @return string
 */
function formatDate(?string $date): string {
    if (empty($date)) {
        return 'N/A';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'N/A';
    }
    return date('m/Y', $timestamp);
}
?>

<!-- Action Buttons -->
<div class="mb-6 flex justify-end space-x-3">
    <a href="/curriculum/<?= $colaborador['id'] ?>/edit" class="btn btn-primary">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Editar
    </a>
    <a href="/colaboradores/<?= $colaborador['id'] ?>" class="btn btn-secondary">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Volver
    </a>
</div>

<!-- Resumen Profesional -->
<?php if (!empty($curriculum['resumen_profesional'])): ?>
<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">Resumen Profesional</h2>
    </div>
    <div class="card-body">
        <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($curriculum['resumen_profesional'] ?? '') ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Experiencia Laboral -->
<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">Experiencia Laboral</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($curriculum['experiencia_laboral'])): ?>
            <div class="space-y-6">
                <?php foreach ($curriculum['experiencia_laboral'] as $index => $exp): ?>
                    <div class="border-l-4 border-blue-500 pl-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars($exp['cargo'] ?? '') ?>
                                </h3>
                                <p class="text-md text-gray-700 font-medium">
                                    <?= htmlspecialchars($exp['empresa'] ?? '') ?>
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?= formatDate($exp['fecha_inicio'] ?? null) ?> -
                                    <?= isset($exp['actual']) && $exp['actual'] ? 'Actualidad' : formatDate($exp['fecha_fin'] ?? null) ?>
                                </p>
                                <?php if (!empty($exp['descripcion'])): ?>
                                    <p class="text-gray-600 mt-2 whitespace-pre-line"><?= htmlspecialchars($exp['descripcion']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-8">No hay experiencia laboral registrada</p>
        <?php endif; ?>
    </div>
</div>

<!-- Formación Académica -->
<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">Formación Académica</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($curriculum['formacion_academica'])): ?>
            <div class="space-y-6">
                <?php foreach ($curriculum['formacion_academica'] as $index => $form): ?>
                    <div class="border-l-4 border-green-500 pl-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars($form['titulo'] ?? '') ?>
                                </h3>
                                <p class="text-md text-gray-700 font-medium">
                                    <?= htmlspecialchars($form['institucion'] ?? '') ?>
                                </p>
                                <div class="flex items-center space-x-3 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?= htmlspecialchars($form['nivel'] ?? '') ?>
                                    </span>
                                    <p class="text-sm text-gray-500">
                                        <?= formatDate($form['fecha_inicio'] ?? null) ?> -
                                        <?= isset($form['en_curso']) && $form['en_curso'] ? 'En curso' : formatDate($form['fecha_fin'] ?? null) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-8">No hay formación académica registrada</p>
        <?php endif; ?>
    </div>
</div>

<!-- Participación Política y Social -->
<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">Participación Política y Social</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($curriculum['participacion_politica'])): ?>
            <div class="space-y-6">
                <?php foreach ($curriculum['participacion_politica'] as $index => $part): ?>
                    <div class="border-l-4 border-purple-500 pl-4 bg-gradient-to-r from-purple-50 to-transparent p-4 rounded-r-lg">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Encabezado con badges -->
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <?php if (!empty($part['tipo'])): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                                            📋 <?= htmlspecialchars($part['tipo']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($part['ambito'])): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            🌍 <?= htmlspecialchars($part['ambito']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($part['actual']) && $part['actual']): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                            ✓ Activo
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Cargo y Organización -->
                                <h3 class="text-xl font-bold text-gray-900 mb-1">
                                    <?= htmlspecialchars($part['cargo'] ?? '') ?>
                                </h3>
                                <p class="text-lg text-purple-700 font-semibold mb-2">
                                    <?= htmlspecialchars($part['organizacion'] ?? '') ?>
                                </p>

                                <!-- Período -->
                                <p class="text-sm text-gray-600 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <?= formatDate($part['fecha_inicio'] ?? null) ?> -
                                    <?= isset($part['actual']) && $part['actual'] ? '<strong>Actualidad</strong>' : formatDate($part['fecha_fin'] ?? null) ?>
                                </p>

                                <!-- Población Beneficiada -->
                                <?php if (!empty($part['poblacion_beneficiada'])): ?>
                                    <div class="mb-3 flex items-center text-sm">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <strong>Población beneficiada:</strong> ~<?= number_format($part['poblacion_beneficiada']) ?> personas
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <!-- Descripción -->
                                <?php if (!empty($part['descripcion'])): ?>
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <h4 class="text-sm font-bold text-gray-700 mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Funciones y Responsabilidades:
                                        </h4>
                                        <p class="text-gray-700 whitespace-pre-line text-sm leading-relaxed"><?= htmlspecialchars($part['descripcion']) ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Logros -->
                                <?php if (!empty($part['logros'])): ?>
                                    <div class="mt-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                                        <h4 class="text-sm font-bold text-green-800 mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                            </svg>
                                            Logros y Actividades Destacadas:
                                        </h4>
                                        <p class="text-gray-800 whitespace-pre-line text-sm leading-relaxed"><?= htmlspecialchars($part['logros']) ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Redes y Alianzas -->
                                <?php if (!empty($part['redes_alianzas'])): ?>
                                    <div class="mt-3 p-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200">
                                        <h4 class="text-sm font-bold text-blue-800 mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Redes y Alianzas:
                                        </h4>
                                        <p class="text-gray-800 whitespace-pre-line text-sm leading-relaxed"><?= htmlspecialchars($part['redes_alianzas']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="mt-4 text-gray-500 font-medium">No hay participación política o social registrada</p>
                <p class="mt-2 text-sm text-gray-400">Agregue información sobre la participación del colaborador en organizaciones, movimientos o actividades comunitarias</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Información Adicional -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Habilidades -->
    <?php if (!empty($curriculum['habilidades'])): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Habilidades</h2>
        </div>
        <div class="card-body">
            <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($curriculum['habilidades']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Idiomas -->
    <?php if (!empty($curriculum['idiomas'])): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Idiomas</h2>
        </div>
        <div class="card-body">
            <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($curriculum['idiomas']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reconocimientos -->
    <?php if (!empty($curriculum['reconocimientos'])): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Reconocimientos</h2>
        </div>
        <div class="card-body">
            <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($curriculum['reconocimientos']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Referencias -->
    <?php if (!empty($curriculum['referencias'])): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Referencias</h2>
        </div>
        <div class="card-body">
            <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($curriculum['referencias']) ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Observaciones -->
<?php if (!empty($curriculum['observaciones'])): ?>
<div class="card mt-6">
    <div class="card-header">
        <h2 class="card-title">Observaciones</h2>
    </div>
    <div class="card-body">
        <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($curriculum['observaciones']) ?></p>
    </div>
</div>
<?php endif; ?>
