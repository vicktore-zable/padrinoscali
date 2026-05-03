<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Portal de Líder' ?> - Aratio</title>
    
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'aratio-blue': '<?= COLOR_PRIMARY ?>',
                        'aratio-navy': '<?= COLOR_SECONDARY ?>',
                        'aratio-gold': '<?= COLOR_ACCENT ?>',
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        :root {
            --aratio-blue-dark: <?= COLOR_PRIMARY ?>;
            --aratio-blue-light: <?= COLOR_SECONDARY ?>;
            --aratio-gold: <?= COLOR_ACCENT ?>;
            --aratio-gold-dark: <?= COLOR_SECONDARY ?>;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #f8fafc; /* Light Slate 50 */
            color: #1e293b; /* Slate 800 */
        }
        
        .premium-sidebar {
            background: #ffffff;
            border-right: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 4px 0 24px -12px rgba(0,0,0,0.05);
        }
        
        .nav-link {
            transition: all 0.3s;
            border-radius: 12px;
            margin: 4px 12px;
            padding: 10px 16px;
            color: #64748b; /* Slate 500 */
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: rgba(0, 68, 136, 0.05); 
            color: #004488;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, rgba(0, 34, 68, 0.08) 0%, rgba(0, 68, 136, 0.08) 100%);
            color: #002244;
            border: 1px solid rgba(0, 34, 68, 0.1);
        }
        
        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            border-color: rgba(0, 68, 136, 0.2);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #002244 0%, #004488 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stats-card-blue {
            border-left: 4px solid #002244;
            background: linear-gradient(90deg, #f0f4f8 0%, #ffffff 100%);
        }
        
        .stats-card-gold {
            border-left: 4px solid #DAA520;
            background: linear-gradient(90deg, #fffdf0 0%, #ffffff 100%);
        }

        /* Helpers for Blue theme */
        .text-aratio-blue { color: #002244; } /* Using darker blue for text for better readability */
        .text-aratio-blue-light { color: #004488; }
        .bg-aratio-blue\/5 { background-color: rgba(0, 34, 68, 0.05); }
        .bg-aratio-blue\/10 { background-color: rgba(0, 34, 68, 0.1); }
        .border-aratio-blue\/20 { border-color: rgba(0, 34, 68, 0.2); }
        .hover\:bg-aratio-blue\/20:hover { background-color: rgba(0, 34, 68, 0.2); }
        .hover\:text-aratio-blue:hover { color: #002244; }
        .from-aratio-blue { --tw-gradient-from: #002244; var(--tw-gradient-from-position); }
        .to-aratio-navy { --tw-gradient-to: #004488; var(--tw-gradient-to-position); }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        [x-cloak] { display: none !important; }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
    </style>
</head>
    <body class="h-full" 
          x-data="{ 
            sidebarOpen: true, 
            profileModal: false, 
            memberModal: false, 
            currentMember: {},
            geo: {
                departamentos: [],
                loading: false,
                error: null,
                async fetch(accion, params = {}) {
                    this.loading = true;
                    this.error = null;
                    try {
                        let url = '<?= url('api/territorios.php') ?>?accion=' + accion;
                        Object.keys(params).forEach(k => {
                            if(params[k]) url += `&${k}=${encodeURIComponent(params[k])}`;
                        });
                        console.log('Geo Fetch:', url);
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const text = await res.text();
                        console.log('Geo Raw:', text.substring(0, 100));
                        
                        // Intentar limpiar el texto si tiene basura de PHP (advertencias)
                        let jsonData = text;
                        if (text.indexOf('{') > 0) {
                            jsonData = text.substring(text.indexOf('{'));
                        }
                        
                        const data = JSON.parse(jsonData);
                        if (!data.success) throw new Error(data.message || 'Error en API');
                        return data.data || [];
                    } catch (e) {
                        console.error('Geo Error:', e);
                        this.error = e.message;
                        return [];
                    } finally {
                        this.loading = false;
                    }
                }
            },
            async initGeo() {
                try {
                    const depts = await this.geo.fetch('departamentos');
                    this.geo.departamentos = Array.isArray(depts) ? depts : [];
                    console.log('✅ Geo Depts Loaded:', this.geo.departamentos.length);
                } catch (e) {
                    console.error('❌ initGeo Failed:', e);
                }
            },
            // Helper para sincronizar un set de datos geográficos
            async syncGeo(data, options) {
                console.log('Syncing Geo:', JSON.stringify(data));
                try {
                    if (data.departamento) {
                        const mupios = await this.geo.fetch('municipios', { departamento: data.departamento });
                        options.municipios = Array.isArray(mupios) ? mupios : [];
                        
                        if (data.municipio) {
                            const [ters, ps] = await Promise.all([
                                this.geo.fetch('territorios', { departamento: data.departamento, municipio: data.municipio }),
                                this.geo.fetch('puestos', { departamento: data.departamento, municipio: data.municipio })
                            ]);
                            options.territorios = Array.isArray(ters) ? ters : [];
                            options.puestos = Array.isArray(ps) ? ps : [];

                            if (data.territorio) {
                                const bars = await this.geo.fetch('barrios', { 
                                    departamento: data.departamento, 
                                    municipio: data.municipio, 
                                    territorio: data.territorio 
                                });
                                options.barrios = Array.isArray(bars) ? bars : [];
                            }
                        }
                    }
                } catch (e) {
                    console.error('Sync Error:', e);
                }
            }
          }" 
          x-init="lucide.createIcons(); initGeo();">
    
    <!-- Mobile Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm lg:hidden" 
         x-cloak></div>

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 premium-sidebar transition-transform duration-300 lg:translate-x-0"
           :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
        
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="p-8">
                <h1 class="text-2xl font-extrabold gradient-text tracking-tighter">ARATIO</h1>
                <p class="text-[10px] text-aratio-gold uppercase tracking-[0.2em] font-semibold">Leader Portal</p>
            </div>
            
            <!-- Nav -->
            <nav class="flex-1 space-y-2 mt-4">
                <a href="?page=portal_dashboard" class="nav-link flex items-center gap-3 <?= (isset($_GET['page']) && $_GET['page'] === 'portal_dashboard') ? 'active' : '' ?>">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="?page=portal_red" class="nav-link flex items-center gap-3 <?= (isset($_GET['page']) && $_GET['page'] === 'portal_red') ? 'active' : '' ?>">
                    <i data-lucide="network" class="w-5 h-5"></i>
                    <span>Mi Red</span>
                </a>
                <!-- 
                <a href="?page=portal_mapa" class="nav-link flex items-center gap-3 <?= (isset($_GET['page']) && $_GET['page'] === 'portal_mapa') ? 'active' : '' ?>">
                    <i data-lucide="map" class="w-5 h-5"></i>
                    <span>Mapa Puestos</span>
                </a>
                -->
                <a href="?page=portal_eventos" class="nav-link flex items-center gap-3 <?= (isset($_GET['page']) && $_GET['page'] === 'portal_eventos') ? 'active' : '' ?>">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                    <span>Eventos</span>
                </a>
                <button @click="profileModal = true" class="w-full nav-link flex items-center gap-3">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span>Mi Perfil</span>
                </button>
            </nav>
            
            <!-- User Info Sidebar bottom -->
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-aratio-blue to-aratio-navy p-[2px] shadow-sm">
                        <div class="w-full h-full rounded-full bg-white flex items-center justify-center font-bold text-sm text-gray-700">
                            <?= strtoupper(substr($_SESSION['user']['nombres'] ?? 'L', 0, 1)) ?>
                        </div>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-bold text-gray-700 truncate"><?= htmlspecialchars($_SESSION['user']['nombres'] ?? 'Líder') ?></p>
                        <p class="text-[10px] text-green-500 font-semibold uppercase tracking-wider flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                        </p>
                    </div>
                    <a href="<?= url('logout.php') ?>" class="text-gray-400 hover:text-red-500 transition-colors p-2 hover:bg-red-50 rounded-lg">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64 flex flex-col min-h-screen">
        <!-- Top Navbar -->
        <header class="h-16 flex items-center justify-between px-8 border-b border-gray-100 flex-shrink-0 bg-white/80 backdrop-blur-md sticky top-0 z-40">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-700">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <div class="flex-1"></div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2 text-sm text-gray-500 font-medium bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
                    <i data-lucide="calendar" class="w-4 h-4 text-aratio-blue"></i>
                    <span><?= date('d M, Y') ?></span>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="flex-1">
            <main class="p-8">
                <!-- Flash Message Placeholder -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="mb-8 p-4 rounded-xl bg-aratio-blue/10 border border-aratio-blue/20 text-aratio-blue flex items-center justify-between font-medium">
                        <span><?= $_SESSION['flash']['message'] ?></span>
                        <?php unset($_SESSION['flash']); ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>
        </div>

        <!-- Footer -->
        <footer class="p-8 border-t border-gray-200 text-gray-400 text-xs flex justify-between items-center bg-white">
            <p>&copy; <?= date('Y') ?> ARATIO Management System</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-aratio-blue transition-colors">Soporte</a>
                <a href="#" class="hover:text-aratio-blue transition-colors">Términos</a>
            </div>
        </footer>
    </div>

    <!-- Modal Perfil (Líder) -->
    <div x-show="profileModal" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in" 
         @keydown.escape.window="profileModal = false">
        
        <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl overflow-y-auto max-h-[90vh] animate-fade-in-up" 
             @click.away="profileModal = false">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">Editar Mi Perfil</h3>
                <button @click="profileModal = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <i data-lucide="x" class="w-6 h-6 text-gray-400"></i>
                </button>
            </div>
            
            <form action="?page=portal_dashboard" method="POST" class="space-y-4" x-data="{ 
                form: {
                    departamento: '<?= strtoupper($lider['departamento'] ?? '') ?>',
                    municipio: '<?= $lider['municipio'] ?? '' ?>',
                    tipo_territorio: '<?= $lider['tipo_territorio'] ?? '' ?>',
                    territorio: '<?= $lider['territorio'] ?? '' ?>',
                    barrio: '<?= $lider['barrio'] ?? '' ?>',
                    puesto_votacion: '<?= $lider['puesto_votacion'] ?? '' ?>',
                    mesa_votacion: '<?= $lider['mesa_votacion'] ?? '' ?>'
                },
                opts: { municipios: [], tipos: [], territorios: [], barrios: [], puestos: [] },
                isSyncing: false
            }" x-init="isSyncing = true; await syncGeo(form, opts); isSyncing = false;">
                <template x-if="geo.error">
                    <div class="p-3 bg-red-50 border border-red-100 text-red-600 text-xs rounded-xl flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span x-text="geo.error"></span>
                    </div>
                </template>
                
                <input type="hidden" name="action" value="update_profile">
                <?= \App\Utils\Security::csrfField() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" :class="geo.loading || isSyncing ? 'opacity-50 pointer-events-none' : ''">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nombre Completo</label>
                        <input type="text" name="nombres" value="<?= htmlspecialchars($lider['nombres'] ?? $_SESSION['user']['nombres'] ?? '') ?>" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Correo Electrónico</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($lider['email'] ?? $_SESSION['user']['email'] ?? '') ?>" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Teléfono (Contraseña)</label>
                        <input type="text" name="telefono" value="<?= htmlspecialchars($lider['celular'] ?? $lider['telefono'] ?? '') ?>" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                    </div>

                    <!-- Geografía -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Departamento</label>
                        <select name="departamento" x-model="form.departamento" 
                                @change="form.municipio=''; form.tipo_territorio=''; form.territorio=''; form.barrio=''; opts.municipios = await geo.fetch('municipios', {departamento: form.departamento})" 
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="d in geo.departamentos" :key="d">
                                <option :value="d" x-text="d"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Municipio</label>
                        <select name="municipio" x-model="form.municipio" 
                                @change="form.territorio=''; form.barrio=''; form.puesto_votacion='';
                                        const ters = await geo.fetch('territorios', {departamento: form.departamento, municipio: form.municipio});
                                        opts.territorios = Array.isArray(ters) ? ters : [];
                                        const ps = await geo.fetch('puestos', {departamento: form.departamento, municipio: form.municipio});
                                        opts.puestos = Array.isArray(ps) ? ps : [];" 
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="(m, index) in opts.municipios" :key="m.cod_mpio || index">
                                <option :value="m.municipio" x-text="m.municipio"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Territorio (Comuna/Correg.)</label>
                        <select name="territorio" x-model="form.territorio" 
                                @change="form.barrio=''; 
                                        const bars = await geo.fetch('barrios', {departamento: form.departamento, municipio: form.municipio, territorio: form.territorio});
                                        opts.barrios = Array.isArray(bars) ? bars : [];" 
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="(t, index) in opts.territorios" :key="index">
                                <option :value="t" x-text="t"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Barrio / Vereda</label>
                        <select name="barrio" x-model="form.barrio" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="b in opts.barrios" :key="b">
                                <option :value="b" x-text="b"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Votación -->
                    <div class="md:col-span-2 pt-2 border-t border-gray-100">
                        <h4 class="text-[10px] font-bold text-aratio-gold uppercase tracking-widest mb-3">Información de Votación</h4>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Puesto de Votación</label>
                        <select name="puesto_votacion" x-model="form.puesto_votacion" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="(p, index) in opts.puestos" :key="p.id || index">
                                <option :value="p.puesto" x-text="p.puesto"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Mesa</label>
                        <input type="text" name="mesa_votacion" x-model="form.mesa_votacion" placeholder="Mesa #" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="profileModal = false" class="flex-1 px-6 py-3 border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm">Cancelar</button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-aratio-blue text-white font-bold rounded-xl shadow-lg hover:shadow-aratio-blue/20 transition-all text-sm">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Miembro del Equipo -->
    <div x-show="memberModal" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in" 
         @keydown.escape.window="memberModal = false">
        
        <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl overflow-y-auto max-h-[90vh] animate-fade-in-up" 
             @click.away="memberModal = false">
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black text-gray-900 tracking-tight">Editar Colaborador</h3>
                <button @click="memberModal = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <i data-lucide="x" class="w-6 h-6 text-gray-400"></i>
                </button>
            </div>
            
            <form action="?page=portal_dashboard" method="POST" class="space-y-4" x-data="{ 
                opts: { municipios: [], tipos: [], territorios: [], barrios: [], puestos: [] },
                isSyncing: false
            }" x-init="$watch('memberModal', async (open) => {
                if(open) {
                    isSyncing = true;
                    if(currentMember.departamento) currentMember.departamento = currentMember.departamento.toUpperCase();
                    opts = { municipios: [], tipos: [], territorios: [], barrios: [], puestos: [] };
                    await syncGeo(currentMember, opts);
                    isSyncing = false;
                }
            })">
                <template x-if="geo.error">
                    <div class="p-3 bg-red-50 border border-red-100 text-red-600 text-xs rounded-xl flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span x-text="geo.error"></span>
                    </div>
                </template>
                
                <input type="hidden" name="action" value="update_team">
                <input type="hidden" name="id" :value="currentMember.id">
                <?= \App\Utils\Security::csrfField() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" :class="geo.loading || isSyncing ? 'opacity-50 pointer-events-none' : ''">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nombres</label>
                        <input type="text" name="nombres" x-model="currentMember.nombres" class="w-full px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Apellidos</label>
                        <input type="text" name="apellidos" x-model="currentMember.apellidos" class="w-full px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Correo Electrónico</label>
                        <input type="email" name="email" x-model="currentMember.email" class="w-full px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Celular / Teléfono</label>
                        <input type="text" name="telefono" x-model="currentMember.celular" class="w-full px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                    </div>

                    <!-- Geografía Miembro -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Departamento</label>
                        <select name="departamento" x-model="currentMember.departamento" 
                                @change="currentMember.municipio=''; currentMember.tipo_territorio=''; currentMember.territorio=''; currentMember.barrio=''; 
                                        opts.municipios = await geo.fetch('municipios', {departamento: currentMember.departamento})" 
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="d in geo.departamentos" :key="d">
                                <option :value="d" x-text="d"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Municipio</label>
                        <select name="municipio" x-model="currentMember.municipio" 
                                @change="currentMember.territorio=''; currentMember.barrio=''; currentMember.puesto_votacion='';
                                        const ters = await geo.fetch('territorios', {departamento: currentMember.departamento, municipio: currentMember.municipio});
                                        opts.territorios = Array.isArray(ters) ? ters : [];
                                        const ps = await geo.fetch('puestos', {departamento: currentMember.departamento, municipio: currentMember.municipio});
                                        opts.puestos = Array.isArray(ps) ? ps : [];" 
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="(m, index) in opts.municipios" :key="m.cod_mpio || index">
                                <option :value="m.municipio" x-text="m.municipio"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Territorio</label>
                        <select name="territorio" x-model="currentMember.territorio" 
                                @change="currentMember.barrio=''; 
                                        const bars = await geo.fetch('barrios', {departamento: currentMember.departamento, municipio: currentMember.municipio, territorio: currentMember.territorio});
                                        opts.barrios = Array.isArray(bars) ? bars : [];" 
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="(t, index) in opts.territorios" :key="index">
                                <option :value="t" x-text="t"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Barrio / Vereda</label>
                        <select name="barrio" x-model="currentMember.barrio" class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="b in opts.barrios" :key="b">
                                <option :value="b" x-text="b"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Votación Miembro -->
                    <div class="md:col-span-2 pt-2 border-t border-gray-100">
                        <h4 class="text-[10px] font-bold text-aratio-gold uppercase tracking-widest mb-2">Información de Votación</h4>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Puesto de Votación</label>
                        <select name="puesto_votacion" x-model="currentMember.puesto_votacion" class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                            <option value="">Seleccione...</option>
                            <template x-for="(p, index) in opts.puestos" :key="p.id || index">
                                <option :value="p.puesto" x-text="p.puesto"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Mesa</label>
                        <input type="text" name="mesa_votacion" x-model="currentMember.mesa_votacion" placeholder="Mesa #" class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm focus:ring-1 focus:ring-aratio-blue outline-none">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="memberModal = false" class="flex-1 px-6 py-2.5 border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm">Cancelar</button>
                    <button type="submit" class="flex-1 px-6 py-2.5 bg-aratio-gold text-aratio-blue font-bold rounded-xl shadow-lg hover:shadow-aratio-gold/20 transition-all text-sm">Actualizar Datos</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
    </script>
</body>
</html>
