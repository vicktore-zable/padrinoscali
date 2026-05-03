<?php
/**
 * Vista cuando el colaborador líder no se encuentra
 */

$pageTitle = $pageTitle ?? 'Mi Equipo';
$layout = 'default';
ob_start();
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="mt-2 text-sm text-gray-600">Información de tu equipo de colaboradores</p>
        </div>
    </div>
</div>

<!-- Mensaje de error -->
<div class="card">
    <div class="card-body text-center py-12">
        <svg class="mx-auto h-24 w-24 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Colaborador no encontrado</h3>
        <p class="text-gray-500 mb-6">
            Tu cuenta está asociada a un colaborador que no existe en el sistema.
            Contacta al administrador para corregir esta asociación.
        </p>
        <a href="/dashboard" class="btn btn-primary">
            Ir al Dashboard
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . "/../layouts/{$layout}.php";