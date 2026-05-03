<?php
/**
 * Vista cuando líder no tiene colaborador asociado
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

<!-- Mensaje informativo -->
<div class="card">
    <div class="card-body text-center py-12">
        <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes colaborador asociado</h3>
        <p class="text-gray-500 mb-6">
            Tu cuenta de usuario no está asociada a un colaborador en el sistema.
            Contacta al administrador para que asocie tu cuenta con tu perfil de colaborador.
        </p>
        <a href="/dashboard" class="btn btn-primary">
            Ir al Dashboard
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . "/../layouts/{$layout}.php";