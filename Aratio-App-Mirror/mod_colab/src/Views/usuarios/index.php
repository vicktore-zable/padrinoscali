<?php
/**
 * Vista de Listado de Usuarios
 * Gestión completa de usuarios del sistema
 */

use App\Utils\Security;
use App\Utils\Helpers;

$pageTitle = $pageTitle ?? 'Gestión de Usuarios';
$pageDescription = $pageDescription ?? 'Administrar usuarios del sistema';
$usuarios = $usuarios ?? [];
$tipos = $tipos ?? TIPOS_USUARIO;
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($pageDescription ?? 'Administrar usuarios del sistema') ?></p>
        </div>
        <?php if (has_permission($_SESSION['user']['tipo_usuario'] ?? '', 'usuarios', 'crear')): ?>
        <a href="/usuarios/create" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash'])): ?>
    <?php
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $alertColors = [
            'success' => 'bg-green-50 border-green-400 text-green-700',
            'error' => 'bg-red-50 border-red-400 text-red-700',
            'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-700',
            'info' => 'bg-blue-50 border-blue-400 text-blue-700'
        ];
        $alertClass = $alertColors[$flash['type']] ?? $alertColors['info'];
    ?>
    <div class="mb-6 border-l-4 p-4 <?= $alertClass ?>" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-center justify-between">
            <p><?= htmlspecialchars($flash['message'] ?? '') ?></p>
            <button @click="show = false" class="ml-4">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php
    $totalUsuarios = count($usuarios);
    $activos = count(array_filter($usuarios, fn($u) => $u['activo']));
    $admins = count(array_filter($usuarios, fn($u) => $u['tipo_usuario'] === 'admin'));
    $lideres = count(array_filter($usuarios, fn($u) => $u['tipo_usuario'] === 'lider'));
    $consultas = count(array_filter($usuarios, fn($u) => $u['tipo_usuario'] === 'consulta'));
    ?>
    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Usuarios</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $totalUsuarios ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Activos</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $activos ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Administradores</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $admins ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Padrinos</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $lideres ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Usuarios -->
<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Lista de Usuarios</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Gestión completa de usuarios del sistema</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Colaborador</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">2FA</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay usuarios</h3>
                                <p class="mt-1 text-sm text-gray-500">No se encontraron usuarios registrados en el sistema.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($usuario['usuario'] ?? '') ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: <?= $usuario['id'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($usuario['email'] ?? '') ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $tipoColors = [
                                    'admin' => 'bg-purple-100 text-purple-800',
                                    'lider' => 'bg-blue-100 text-blue-800',
                                    'consulta' => 'bg-gray-100 text-gray-800'
                                ];
                                $colorClass = $tipoColors[$usuario['tipo_usuario']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                    <?= htmlspecialchars($tipos[$usuario['tipo_usuario']] ?? $usuario['tipo_usuario']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($usuario['colaborador_nombre'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($usuario['activo']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        Activo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                <?php if ($usuario['require_2fa']): ?>
                                    <svg class="mx-auto h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                <?php else: ?>
                                    <svg class="mx-auto h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                                    </svg>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y', strtotime($usuario['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <?php if (has_permission($_SESSION['user']['tipo_usuario'] ?? '', 'usuarios', 'editar')): ?>
                                    <a href="/usuarios/<?= $usuario['id'] ?>/edit"
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                        title="Editar usuario">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <button @click="toggleBlock(<?= $usuario['id'] ?>)"
                                            class="text-yellow-600 hover:text-yellow-900 transition-colors duration-200"
                                            title="<?= $usuario['activo'] ? 'Desactivar usuario' : 'Activar usuario' ?>">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    </button>

                                    <button @click="closeSessions(<?= $usuario['id'] ?>)"
                                            class="text-orange-600 hover:text-orange-900 transition-colors duration-200"
                                            title="Cerrar todas las sesiones">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                    </button>
                                    <?php endif; ?>

                                    <?php if (has_permission($_SESSION['user']['tipo_usuario'] ?? '', 'usuarios', 'eliminar')): ?>
                                    <button @click="deleteUser(<?= $usuario['id'] ?>)"
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                            title="Eliminar usuario">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php ob_start(); ?>
<style>
    /* Estilos adicionales para asegurar correcta visualización */
    .stat-card-info {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        padding: 1.5rem;
        border-left-width: 4px;
        border-left-color: #06b6d4;
    }
    
    table.min-w-full {
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    table.min-w-full th {
        padding: 0.75rem 1.5rem;
        text-align: left;
        vertical-align: middle;
        font-weight: 500;
        font-size: 0.75rem;
        line-height: 1rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background-color: #f9fafb;
    }
    
    table.min-w-full td {
        padding: 1rem 1.5rem;
        text-align: left;
        vertical-align: middle;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #111827;
        white-space: nowrap;
    }
    
    table.min-w-full tbody tr {
        transition: background-color 0.15s ease-in-out;
    }
    
    table.min-w-full tbody tr:hover {
        background-color: #f9fafb;
    }
    
    .divide-y > * + *{
        border-top-width: 1px;
        border-color: #e5e7eb;
    }
    
    /* Asegurar espaciado en badges y botones */
    .inline-flex {
        display: inline-flex;
        align-items: center;
    }
    
    .space-x-2 > * + * {
        margin-left: 0.5rem;
    }
    
    /* Fix icon sizes */
    svg.w-5 {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    svg.w-6 {
        width: 1.5rem;
        height: 1.5rem;
    }
    
    svg.h-5 {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    svg.h-6 {
        width: 1.5rem;
        height: 1.5rem;
    }
    
    /* Fix stat card icon containers */
    .w-10.h-10 {
        width: 2.5rem;
        height: 2.5rem;
    }
    
    .w-8.h-8 {
        width: 2rem;
        height: 2rem;
    }
</style>

<script>
    function usuariosTable() {
        return {
            async toggleBlock(userId) {
                if (!confirm('¿Está seguro de cambiar el estado de este usuario?')) return;

                try {
                    const response = await fetch(`/usuarios/${userId}/toggle-block`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo cambiar el estado'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cambiar el estado del usuario');
                }
            },

            async closeSessions(userId) {
                if (!confirm('¿Cerrar todas las sesiones activas de este usuario?')) return;

                try {
                    const response = await fetch(`/usuarios/${userId}/close-sessions`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert('Error: ' + (data.message || 'No se pudieron cerrar las sesiones'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cerrar las sesiones');
                }
            },

            async deleteUser(userId) {
                if (!confirm('¿ELIMINAR este usuario permanentemente?\n\nEsta acción no se puede deshacer.')) return;

                try {
                    const response = await fetch(`/usuarios/${userId}/delete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar el usuario'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al eliminar el usuario');
                }
            }
        }
    }
</script>
<?php $scripts = ob_get_clean(); ?>
