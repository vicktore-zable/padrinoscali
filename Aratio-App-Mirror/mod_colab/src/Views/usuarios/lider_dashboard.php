<?php
/**
 * Vista de Dashboard para Líderes
 * Muestra estadísticas y colaboradores asociados
 */

use App\Utils\Security;
use App\Utils\Helpers;

$pageTitle = $pageTitle ?? 'Mi Equipo';
$stats = $stats ?? [];
$colaboradores_directos = $colaboradores_directos ?? [];
$red_completa = $red_completa ?? [];
$lider = $lider ?? [];
$layout = 'default';
ob_start();
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="mt-2 text-sm text-gray-600">
                Bienvenido <?= htmlspecialchars($lider['nombres'] . ' ' . $lider['apellidos']) ?>
            </p>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Total Directos -->
    <div class="stat-card stat-card-primary">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Colaboradores Directos</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['total_directos'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Total Red -->
    <div class="stat-card stat-card-success">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total en Red</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['total_red'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Activos -->
    <div class="stat-card stat-card-info">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-emerald-100 rounded-full p-3">
                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 011.414 1.414L8.414 9.5H5a1 1 0 100 2h3.414l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Creciendo</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['activos'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Inactivos -->
    <div class="stat-card stat-card-warning">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 01-1.414 1.414L10 10.586l-1.293-1.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Decreciendo</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['inactivos'] ?? 0 ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Colaboradores Directos -->
<?php if (!empty($colaboradores_directos)): ?>
<div class="card mb-8">
    <div class="card-header">
        <h2 class="card-title">Colaboradores Directos</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Territorio</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($colaboradores_directos as $colaborador): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($colaborador['nombres'] . ' ' . $colaborador['apellidos']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($colaborador['documento']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($colaborador['perfil'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $estadoColors = [
                                'Nuevo' => 'bg-blue-100 text-blue-800',
                                'Creció' => 'bg-green-100 text-green-800',
                                'Decrece' => 'bg-red-100 text-red-800',
                                'Igual' => 'bg-yellow-100 text-yellow-800'
                            ];
                            $colorClass = $estadoColors[$colaborador['estado']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $colorClass ?>">
                                <?= htmlspecialchars($colaborador['estado'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($colaborador['nivel_participacion'] ?? 'N/A') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($colaborador['territorio'] ?? 'N/A') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Resumen de Red Completa -->
<?php if (!empty($red_completa)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Resumen de Red Completa</h2>
        <p class="text-sm text-gray-600 mt-1">Vista general de toda tu red de colaboradores</p>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $perfiles = array_count_values(array_column($red_completa, 'perfil'));
            $estados = array_count_values(array_column($red_completa, 'estado'));
            $territorios = array_count_values(array_column($red_completa, 'territorio'));
            ?>

            <!-- Perfiles -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Perfiles</h3>
                <div class="space-y-2">
                    <?php foreach ($perfiles as $perfil => $count): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($perfil) ?></span>
                            <span class="text-sm font-medium text-gray-900"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Estados -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Estados</h3>
                <div class="space-y-2">
                    <?php foreach ($estados as $estado => $count): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($estado) ?></span>
                            <span class="text-sm font-medium text-gray-900"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Territorios -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Territorios</h3>
                <div class="space-y-2">
                    <?php foreach ($territorios as $territorio => $count): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($territorio) ?></span>
                            <span class="text-sm font-medium text-gray-900"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . "/../layouts/{$layout}.php";