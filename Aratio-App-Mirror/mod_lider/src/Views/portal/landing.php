<?php
/**
 * Vista de Landing Page del Portal de Líderes
 * Usa el layout 'portal_public'
 */
?>
<!-- Hero Section -->
<div class="relative bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
            <svg class="hidden lg:block absolute right-0 inset-y-0 h-full w-48 text-white transform translate-x-1/2" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <polygon points="50,0 100,0 50,100 0,100" />
            </svg>

            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="sm:text-center lg:text-left">
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-[#002244]/5 border border-[#002244]/10 text-[#002244] text-[10px] font-bold uppercase tracking-[0.2em] mb-6">
                        Aratio Leader Portal
                    </div>
                    <h1 class="text-5xl tracking-tight font-black text-gray-900 sm:text-6xl md:text-7xl">
                        <span class="block xl:inline">Empodera tu</span>
                        <span class="block gradient-text">Liderazgo</span>
                    </h1>
                    <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 font-light leading-relaxed">
                        Bienvenido al centro estratégico de tu campaña. Una herramienta premium diseñada para líderes que buscan resultados, organización y crecimiento exponencial de su red.
                    </p>
                    <div class="mt-8 sm:mt-12 flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                        <a href="?page=portal_login" class="flex items-center justify-center px-8 py-4 rounded-2xl text-lg btn-premium-gold shadow-xl">
                            <i data-lucide="log-in" class="w-5 h-5 mr-3"></i>
                            Ingresar al Portal
                        </a>
                        <a href="?page=registro_lider" class="flex items-center justify-center px-8 py-4 rounded-2xl text-lg bg-[#002244] text-[#FFD700] hover:bg-[#003366] transition-all shadow-lg">
                            <i data-lucide="user-plus" class="w-5 h-5 mr-3"></i>
                            Registrarse
                        </a>
                        <a href="?page=consulta_electoral" class="flex items-center justify-center px-8 py-4 rounded-2xl text-lg border-2 border-[#FFD700] text-[#002244] hover:bg-[#FFD700]/10 transition-all shadow-lg font-bold">
                            <i data-lucide="vote" class="w-5 h-5 mr-3"></i>
                            Consulta Elecciones
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 hero-gradient flex items-center justify-center">
        <div class="relative">
            <div class="absolute inset-0 bg-[#FFD700] opacity-10 blur-[100px] rounded-full"></div>
            <i data-lucide="network" class="w-64 h-64 text-[#FFD700] opacity-20 relative z-10 animate-pulse"></i>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-16">
            <h2 class="text-xs text-[#DAA520] font-bold tracking-[0.3em] uppercase mb-3">Estrategia & Control</h2>
            <p class="text-4xl font-black tracking-tight text-[#002244] sm:text-5xl">
                Tecnología al servicio de tu red
            </p>
        </div>

        <div class="mt-10">
            <dl class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-12 md:gap-y-10">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-[#002244] to-[#004488] text-[#FFD700] mb-6 shadow-lg">
                        <i data-lucide="layers" class="w-8 h-8"></i>
                    </div>
                    <dt class="text-xl font-bold text-gray-900 mb-3">Gestión Multinivel</dt>
                    <dd class="text-gray-500 leading-relaxed">
                        Visualiza y gestiona tu estructura completa. Conoce el impacto real de cada líder en tu red de forma jerárquica y organizada.
                    </dd>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-[#002244] to-[#004488] text-[#FFD700] mb-6 shadow-lg">
                        <i data-lucide="bar-chart-3" class="w-8 h-8"></i>
                    </div>
                    <dt class="text-xl font-bold text-gray-900 mb-3">Métricas en Tiempo Real</dt>
                    <dd class="text-gray-500 leading-relaxed">
                        Toma decisiones basadas en datos. Monitorea el crecimiento diario, metas alcanzadas y efectividad territorial al instante.
                    </dd>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-[#002244] to-[#004488] text-[#FFD700] mb-6 shadow-lg">
                        <i data-lucide="shield-check" class="w-8 h-8"></i>
                    </div>
                    <dt class="text-xl font-bold text-gray-900 mb-3">Seguridad Aratio</dt>
                    <dd class="text-gray-500 leading-relaxed">
                        Tu información es tu activo más valioso. Protegemos cada dato con los más altos estándares de cifrado y privacidad.
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<footer class="bg-[#002244] py-12">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-white/40 text-sm tracking-widest uppercase font-bold">&copy; <?= date('Y') ?> Aratio Management System. All Rights Reserved.</p>
    </div>
</footer>
