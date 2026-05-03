<!-- Hero Section -->
<section class="py-20 bg-gradient-to-br from-primary-600 to-primary-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">
                <?= htmlspecialchars($title) ?>
            </h1>
            <p class="text-xl md:text-2xl text-primary-100 mb-8 max-w-3xl mx-auto">
                <?= htmlspecialchars($description) ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/login" class="btn bg-white text-primary-600 hover:bg-gray-100 text-lg px-8 py-3">
                    Acceder al Sistema
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                Características Principales
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Una solución completa para la gestión eficiente de redes de colaboradores
            </p>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($features as $feature): ?>
            <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow duration-300">
                <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php if ($feature['icon'] === 'users'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        <?php elseif ($feature['icon'] === 'network-wired'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        <?php elseif ($feature['icon'] === 'chart-line'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        <?php elseif ($feature['icon'] === 'shield-alt'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        <?php elseif ($feature['icon'] === 'file-import'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        <?php else: ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        <?php endif; ?>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">
                    <?= htmlspecialchars($feature['title']) ?>
                </h3>
                <p class="text-gray-600">
                    <?= htmlspecialchars($feature['description']) ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-20 bg-primary-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-5xl font-bold mb-2">100%</div>
                <div class="text-primary-100">Seguro y Confiable</div>
            </div>
            <div>
                <div class="text-5xl font-bold mb-2">24/7</div>
                <div class="text-primary-100">Disponibilidad</div>
            </div>
            <div>
                <div class="text-5xl font-bold mb-2">∞</div>
                <div class="text-primary-100">Colaboradores</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-gray-900 mb-6">
            ¿Listo para comenzar?
        </h2>
        <p class="text-xl text-gray-600 mb-8">
            Acceda al sistema para gestionar su red de colaboradores de manera eficiente
        </p>
        <a href="/login" class="btn btn-primary text-lg px-8 py-3 inline-flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Iniciar Sesión
        </a>
    </div>
</section>
