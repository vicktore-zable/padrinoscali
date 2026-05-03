<?php
/**
 * Vista de Creación de Usuario
 */

use App\Utils\Security;

$pageTitle = $pageTitle ?? 'Nuevo Usuario';
$pageDescription = 'Crear un nuevo usuario en el sistema';
$tipos = $tipos ?? TIPOS_USUARIO;
$colaboradores = $colaboradores ?? [];
$old = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($pageDescription ?? 'Crear un nuevo usuario del sistema') ?></p>
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
<form action="/usuarios/store" method="POST" class="space-y-6">
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
                           value="<?= htmlspecialchars($old['usuario'] ?? '') ?>"
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
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
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
                            <option value="<?= $key ?>" <?= ($old['tipo_usuario'] ?? '') === $key ? 'selected' : '' ?>>
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
                            <option value="<?= $campana['id'] ?>" <?= ((int)($old['campana_id'] ?? 0) === (int)$campana['id'] || (int)($input['campana_id'] ?? 0) === (int)$campana['id']) ? 'selected' : '' ?>>
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
                                    <?= ($old['documento_colaborador'] ?? '') === $colab['documento'] ? 'selected' : '' ?>>
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
                           <?= ($old['activo'] ?? true) ? 'checked' : '' ?>
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
                           <?= ($old['require_2fa'] ?? false) ? 'checked' : '' ?>
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    <label for="require_2fa" class="ml-2 text-sm font-medium text-gray-900">
                        Requiere Autenticación de Dos Factores (2FA)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="flex justify-end space-x-4">
        <a href="/usuarios" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancelar
        </a>
        <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Crear Usuario
        </button>
    </div>
</form>
