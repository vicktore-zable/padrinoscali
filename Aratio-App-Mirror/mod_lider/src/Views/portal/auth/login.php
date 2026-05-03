<?php
/**
 * Vista de Login del Portal
 * Usa el layout 'portal_public'
 */
?>
<div class="min-h-screen bg-gradient-to-br from-[#002244] to-[#004488] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">

        <!-- Logo y Título -->
        <div class="text-center mb-8">
            <h1 class="text-5xl font-black text-white mb-2 tracking-tighter">A RATIO</h1>
            <p class="text-[#FFD700] uppercase tracking-[0.3em] font-bold text-xs">Leader Portal</p>
        </div>

        <!-- Card de Login -->
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-10 border border-white/20">

            <h2 class="text-3xl font-extrabold text-[#002244] mb-8 text-center tracking-tight">
                Iniciar Sesión
            </h2>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <?php
                    $flash = $_SESSION['flash'];
                    unset($_SESSION['flash']);
                ?>
                <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-blue-50 text-blue-600 border border-blue-100' ?> flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form action="?page=portal_auth" method="POST" class="space-y-6">
                <?= \App\Utils\Security::csrfField() ?>

                <!-- Usuario -->
                <div>
                    <label for="documento" class="block text-xs font-bold text-[#002244] uppercase tracking-wider mb-2">
                        Documento de Identidad
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="credit-card" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="text"
                               id="documento"
                               name="documento"
                               class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 focus:ring-2 focus:ring-[#FFD700] focus:border-[#FFD700] transition-all outline-none"
                               placeholder="Ingrese su documento"
                               required
                               autofocus>
                    </div>
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-xs font-bold text-[#002244] uppercase tracking-wider mb-2">
                        Contraseña (Número de Teléfono)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="password"
                               id="password"
                               name="password"
                               class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl text-gray-900 focus:ring-2 focus:ring-[#FFD700] focus:border-[#FFD700] transition-all outline-none"
                               placeholder="Ingrese su número de teléfono"
                               required>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox"
                               id="remember"
                               name="remember"
                               class="h-4 w-4 text-[#002244] focus:ring-[#FFD700] border-gray-300 rounded focus:ring-offset-0">
                        <label for="remember" class="ml-2 block text-sm text-gray-600">
                            Recordarme
                        </label>
                    </div>
                </div>

                <!-- Botón Submit -->
                <button type="submit" class="w-full btn-premium-gold py-4 rounded-2xl shadow-lg flex items-center justify-center gap-3">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    <span>Ingresar al Portal</span>
                </button>
            </form>

            <!-- Info adicional -->
            <div class="mt-8 text-center border-t border-gray-100 pt-6">
                <p class="text-sm text-gray-500">¿No tiene una cuenta?
                    <a href="?page=registro_lider" class="font-bold text-[#002244] hover:underline">
                        Regístrese aquí
                    </a>
                </p>
                <a href="/" class="inline-block mt-4 text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    ← Volver al Inicio
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-white/50">
            <p>&copy; <?= date('Y') ?> A RATIO. Todos los derechos reservados.</p>
        </div>
    </div>
</div>
