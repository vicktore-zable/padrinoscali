<?php
/**
 * Vista de Reinicio de Contraseña (Nueva Contraseña)
 * Usa el layout 'portal_public'
 */
?>
<div class="min-h-screen bg-gradient-to-br from-[#002244] to-[#004488] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">

        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-5xl font-black text-white mb-2 tracking-tighter">A RATIO</h1>
            <p class="text-[#FFD700] uppercase tracking-[0.3em] font-bold text-xs">Nueva Contraseña</p>
        </div>

        <!-- Card -->
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-10 border border-white/20">

            <h2 class="text-2xl font-extrabold text-[#002244] mb-4 text-center tracking-tight">
                Restablecer Contraseña
            </h2>
            <p class="text-gray-500 text-center mb-8 text-sm">
                Por favor, elija una nueva contraseña segura para su cuenta.
            </p>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <?php
                    $flash = $_SESSION['flash'];
                    unset($_SESSION['flash']);
                ?>
                <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100' ?> flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form action="?page=portal_reset" method="POST" class="space-y-6">
                <?= \App\Utils\Security::csrfField() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div>
                    <label for="password" class="block text-xs font-bold text-[#002244] uppercase tracking-wider mb-2">
                        Nueva Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="password"
                               id="password"
                               name="password"
                               class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 focus:ring-2 focus:ring-[#FFD700] focus:border-[#FFD700] transition-all outline-none"
                               placeholder="Mínimo 8 caracteres"
                               required
                               autofocus>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-xs font-bold text-[#002244] uppercase tracking-wider mb-2">
                        Confirmar Nueva Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="shield-check" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 focus:ring-2 focus:ring-[#FFD700] focus:border-[#FFD700] transition-all outline-none"
                               placeholder="Repita la contraseña"
                               required>
                    </div>
                </div>

                <button type="submit" class="w-full btn-premium-gold py-4 rounded-2xl shadow-lg flex items-center justify-center gap-3">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    <span>Actualizar Contraseña</span>
                </button>
            </form>
        </div>
    </div>
</div>
