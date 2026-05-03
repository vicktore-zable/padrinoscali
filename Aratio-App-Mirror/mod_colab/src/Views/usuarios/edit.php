<?php
/**
 * Vista de Edición de Usuario
 */

use App\Utils\Security;

$pageTitle = $pageTitle ?? 'Editar Usuario';
$pageDescription = 'Editar información de usuario';
$usuario = $usuario ?? [];
$tipos = $tipos ?? TIPOS_USUARIO;
$colaboradores = $colaboradores ?? [];
$sesiones = $sesiones ?? [];
$old = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($pageDescription ?? 'Modificar información del usuario') ?></p>
        </div>
        <a href="/usuarios" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver
        </a>
    </div>
</div>

<!-- Formulario -->
<form action="/usuarios/<?= $usuario['id'] ?>/update" method="POST" class="space-y-6">
    <?= Security::csrfField() ?>

    <!-- Información Básica -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Información Básica</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Usuario -->
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-700 mb-2">
                        Usuario <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="usuario"
                           name="usuario"
                           value="<?= htmlspecialchars($old['usuario'] ?? $usuario['usuario'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    <?php if (isset($errors['usuario'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['usuario']) ?></p>
                    <?php endif; ?>
                    <p class="mt-1 text-xs text-gray-500">Solo letras, números y guión bajo (4-50 caracteres)</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?= htmlspecialchars($old['email'] ?? $usuario['email'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Nueva Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Nueva Contraseña <span class="text-gray-500">(dejar en blanco para no cambiar)</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (isset($errors['password'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['password']) ?></p>
                    <?php endif; ?>
                    <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres, incluir mayúsculas, minúsculas, números y símbolos</p>
                </div>

                <!-- Tipo de Usuario -->
                <div>
                    <label for="tipo_usuario" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Usuario <span class="text-red-500">*</span>
                    </label>
                    <select id="tipo_usuario"
                            name="tipo_usuario"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($tipos as $key => $label): ?>
                            <option value="<?= $key ?>"
                                    <?= ($old['tipo_usuario'] ?? $usuario['tipo_usuario'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Campaña Asociada -->
                <div>
                    <label for="campana_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Campaña Asociada (Restricción)
                    </label>
                    <select id="campana_id"
                            name="campana_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todas las campañas (Admin General)</option>
                        <?php foreach ($campanas as $campana): ?>
                            <option value="<?= $campana['id'] ?>" <?= ((int)($old['campana_id'] ?? $usuario['campana_id'] ?? 0) === (int)$campana['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($campana['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Si se selecciona, el usuario solo verá colaboradores de esta campaña.</p>
                </div>

                <!-- Colaborador Asociado -->
                <div class="md:col-span-2">
                    <label for="documento_colaborador" class="block text-sm font-medium text-gray-700 mb-2">
                        Colaborador Asociado (Opcional)
                    </label>
                    <select id="documento_colaborador"
                            name="documento_colaborador"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Ninguno</option>
                        <?php foreach ($colaboradores as $colab): ?>
                            <option value="<?= htmlspecialchars($colab['documento']) ?>"
                                    <?= ($old['documento_colaborador'] ?? $usuario['documento_colaborador'] ?? '') === $colab['documento'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($colab['nombres'] . ' ' . $colab['apellidos']) ?> - <?= htmlspecialchars($colab['documento']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Vincular este usuario con un colaborador existente</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuración -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Configuración</h2>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <!-- Activo -->
                <div class="flex items-center">
                    <input type="checkbox"
                           id="activo"
                           name="activo"
                           <?= ($old['activo'] ?? $usuario['activo'] ?? false) ? 'checked' : '' ?>
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    <label for="activo" class="ml-2 text-sm font-medium text-gray-900">
                        Usuario Activo
                    </label>
                </div>

                <!-- Requiere 2FA -->
                <div class="flex items-center">
                    <input type="checkbox"
                           id="require_2fa"
                           name="require_2fa"
                           <?= ($old['require_2fa'] ?? $usuario['require_2fa'] ?? false) ? 'checked' : '' ?>
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    <label for="require_2fa" class="ml-2 text-sm font-medium text-gray-900">
                        Requiere Autenticación de Dos Factores (2FA)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Sesiones Activas -->
    <?php if (!empty($sesiones)): ?>
    <div class="card">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h2 class="card-title">Sesiones Activas (<?= count($sesiones) ?>)</h2>
                <button type="button"
                        @click="cerrarTodasSesiones()"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Cerrar Todas
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="space-y-3">
                <?php foreach ($sesiones as $sesion): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-900">
                                    Sesión activa desde <?= date('d/m/Y H:i', strtotime($sesion['created_at'])) ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                IP: <?= htmlspecialchars($sesion['ip_address'] ?? 'Desconocida') ?> |
                                Última actividad: <?= date('d/m/Y H:i', strtotime($sesion['last_activity'])) ?>
                            </p>
                        </div>
                        <button type="button"
                                @click="cerrarSesion('<?= $sesion['id'] ?>')"
                                class="ml-4 text-sm text-red-600 hover:text-red-900">
                            Cerrar
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Información del Sistema -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Información del Sistema</h2>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID de Usuario</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($usuario['id'] ?? '') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($usuario['updated_at'])) ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Intentos Fallidos</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= $usuario['intentos_fallidos'] ?? 0 ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Botones -->
    <div class="flex justify-end space-x-4">
        <a href="/usuarios" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancelar
        </a>
        <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Actualizar Usuario
        </button>
    </div>
</form>

<?php ob_start(); ?>
<script>
    function cerrarSesion(sesionId) {
        if (!confirm('¿Cerrar esta sesión?')) return;

        fetch(`/usuarios/<?= $usuario['id'] ?>/close-sessions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
            },
            body: JSON.stringify({ sesion_id: sesionId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo cerrar la sesión'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cerrar la sesión');
        });
    }

    function cerrarTodasSesiones() {
        if (!confirm('¿Cerrar TODAS las sesiones activas de este usuario?')) return;

        fetch(`/usuarios/<?= $usuario['id'] ?>/close-sessions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudieron cerrar las sesiones'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cerrar las sesiones');
        });
    }
</script>
<?php $scripts = ob_get_clean(); ?>
