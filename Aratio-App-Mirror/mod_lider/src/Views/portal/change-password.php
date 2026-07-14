<?php
/**
 * Vista: Cambiar Contraseña del Líder
 * Usa el layout 'portal'
 */
?>
<div class="max-w-lg mx-auto py-12 px-4">

    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-aratio-blue/10 mb-4">
            <i data-lucide="key-round" class="w-8 h-8 text-aratio-blue"></i>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Cambiar Contraseña</h1>
        <p class="text-gray-500 mt-1">Actualiza tu contraseña de acceso al portal</p>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100' ?> flex items-center gap-3">
            <i data-lucide="<?= $flash['type'] === 'error' ? 'alert-circle' : 'check-circle' ?>" class="w-5 h-5"></i>
            <span class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

        <form action="?page=portal_change_password" method="POST" class="space-y-6">
            <?= \App\Utils\Security::csrfField() ?>

            <!-- Contraseña Actual -->
            <div>
                <label for="current_password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Contraseña Actual (o Teléfono)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           class="block w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-aratio-blue focus:border-aratio-blue transition-all outline-none"
                           placeholder="Tu teléfono o contraseña actual"
                           required>
                </div>
            </div>

            <!-- Nueva Contraseña -->
            <div>
                <label for="new_password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Nueva Contraseña
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="shield" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="password"
                           id="new_password"
                           name="new_password"
                           class="block w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-aratio-blue focus:border-aratio-blue transition-all outline-none"
                           placeholder="Mínimo 8 caracteres"
                           required
                           minlength="8">
                </div>
                <p class="mt-1 text-xs text-gray-400">Debe tener mayúscula, minúscula y número.</p>
            </div>

            <!-- Confirmar Contraseña -->
            <div>
                <label for="confirm_password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Confirmar Nueva Contraseña
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="shield-check" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="password"
                           id="confirm_password"
                           name="confirm_password"
                           class="block w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-aratio-blue focus:border-aratio-blue transition-all outline-none"
                           placeholder="Repite la nueva contraseña"
                           required
                           minlength="8">
                </div>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <a href="?page=portal_dashboard"
                   class="flex-1 text-center px-6 py-3 rounded-xl border border-gray-200 text-gray-600 font-medium hover:bg-gray-50 transition-all">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex-1 px-6 py-3 rounded-xl bg-aratio-blue text-white font-bold hover:opacity-90 transition-all shadow-lg shadow-aratio-blue/20">
                    Guardar Contraseña
                </button>
            </div>
        </form>

    </div>

    <!-- Info -->
    <div class="mt-6 p-4 bg-amber-50 border border-amber-100 rounded-xl">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-amber-500 mt-0.5"></i>
            <p class="text-sm text-amber-700">
                Si olvidas tu contraseña, puedes usar tu <strong>número de teléfono</strong> para ingresar siempre.
            </p>
        </div>
    </div>

</div>
