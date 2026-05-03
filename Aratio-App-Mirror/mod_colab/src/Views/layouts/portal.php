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
                        'aratio-blue': '<?= COLOR_PRIMARY ?>',
                        'aratio-gold': '<?= COLOR_SECONDARY ?>',
                        'aratio-accent': '<?= COLOR_ACCENT ?>',
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
    </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true }" x-init="lucide.createIcons()">
    
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 premium-sidebar transition-transform lg:translate-x-0"
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
                <!--
                <a href="?page=portal_profile" class="nav-link flex items-center gap-3 <?= (isset($_GET['page']) && $_GET['page'] === 'portal_profile') ? 'active' : '' ?>">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span>Mi Perfil</span>
                </a>
                -->
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
                    <a href="?page=logout" class="text-gray-400 hover:text-red-500 transition-colors p-2 hover:bg-red-50 rounded-lg">
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
</body>
</html>
