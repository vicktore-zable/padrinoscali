<?php
/**
 * Vista de Creación de Campaña
 */

use App\Utils\Security;

$pageTitle = $pageTitle ?? 'Nueva Campaña';
$old = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="mt-2 text-sm text-gray-600">Registre una nueva campaña</p>
        </div>
        <a href="/campanas" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
            Volver
        </a>
    </div>

    <!-- Formulario -->
    <form action="/campanas/store" method="POST" class="space-y-6">
        <?= Security::csrfField() ?>

        <div class="card">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ID Externo (Código) -->
                <div>
                    <label for="codigo_externo" class="block text-sm font-medium text-gray-700 mb-2">
                        Código Externo (aratio ID) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="codigo_externo" name="codigo_externo" 
                           placeholder="Ej: CAMP-2027-003"
                           value="<?= htmlspecialchars($old['codigo_externo'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    <?php if (isset($errors['codigo_externo'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $errors['codigo_externo'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de la Campaña <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre" 
                           placeholder="Ej: Concejo de Yumbo 2028"
                           value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Candidato -->
                <div>
                    <label for="candidato_nombre" class="block text-sm font-medium text-gray-700 mb-2">Nombre del Candidato</label>
                    <input type="text" id="candidato_nombre" name="candidato_nombre" 
                           value="<?= htmlspecialchars($old['candidato_nombre'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Territorio -->
                <div>
                    <label for="territorio" class="block text-sm font-medium text-gray-700 mb-2">Territorio (Dpto, Ciudad)</label>
                    <input type="text" id="territorio" name="territorio" 
                           placeholder="Ej: Valle, Yumbo"
                           value="<?= htmlspecialchars($old['territorio'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Fechas -->
                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" 
                           value="<?= htmlspecialchars($old['fecha_inicio'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" 
                           value="<?= htmlspecialchars($old['fecha_fin'] ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Descripción -->
                <div class="md:col-span-2">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
                </div>

                <!-- Estado -->
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="activo" name="activo" value="1" 
                           <?= ($old['activo'] ?? true) ? 'checked' : '' ?>
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="activo" class="text-sm font-medium text-gray-700">Campaña Activa</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="/campanas" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Crear Campaña</button>
        </div>
    </form>
</div>
