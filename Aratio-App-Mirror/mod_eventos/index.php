<?php
/**
 * mod_eventos - Standalone Gestión de Eventos
 * Entry for ?page=mod_eventos
 */
require_once __DIR__ . '/../config/config.php';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new Auth();
    $auth->logout();
    header('Location: /');
    exit;
}

$view = $_GET['view'] ?? 'eventos';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user_id'] ?? null;
$user_nombre = $_SESSION['user_nombre'] ?? 'Usuario';
$user_rol = $_SESSION['user_rol'] ?? 'Admin';
$db   = getDB();

if ($user) {
    $auth = new Auth();
    $campanas = $auth->getUserCampanas($user);
} else {
    $campanas = [];
}

if (!empty($_GET['campana_id'])) {
    $_SESSION['campana_activa'] = $_GET['campana_id'];
}
$campanaActivaId = $_SESSION['campana_activa'] ?? ($campanas[0]['id'] ?? 4);

$campanaActiva = null;
foreach ($campanas as $c) {
    if ($c['id'] == $campanaActivaId) {
        $campanaActiva = $c;
        break;
    }
}

$section = $view;
$isIncluded = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos — <?= $campanaActiva['nombre'] ?? 'Aratio' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        magenta: '#E6007E',
                        amber: '#F9B000',
                        purple: '#5B2A86',
                        green: '#41B853',
                        grafito: '#2F2F35'
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        *, body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .sidebar-active { background: rgba(91,42,134,0.08); color: #5B2A86; border-right: 4px solid #E6007E; }
        .glass-nav { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .btn-primary { background: linear-gradient(135deg, #E6007E, #5B2A86); color: white; padding: 0.625rem 1.25rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.875rem; transition: all 0.2s; }
        .btn-primary:hover { opacity: 0.9; }
        .leaflet-container { border-radius: 12px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    <!-- SIDEBAR -->
    <aside class="w-72 bg-white border-r border-gray-100 hidden lg:flex flex-col sticky top-0 h-screen z-50">
        <div class="p-8 border-b border-gray-100 flex items-center gap-4 bg-gradient-to-r from-white to-slate-50">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-xl transform -rotate-3 transition-transform hover:rotate-0"
                 style="background: linear-gradient(135deg, #E6007E, #5B2A86);">
                <i data-lucide="calendar" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tighter leading-none" style="color: #5B2A86;">Eventos</h1>
                <p class="text-[8px] font-black uppercase tracking-widest mt-1" style="color: #E6007E;">By Aratio Intelligence</p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-2 mt-4">
            <div class="px-4 mb-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Navegación</div>
            
            <a href="?page=mod_eventos&view=eventos" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all <?= $section === 'eventos' ? 'sidebar-active shadow-md' : 'text-gray-500 hover:bg-gray-50' ?>">
                <i data-lucide="calendar" class="w-5 h-5" style="color: #E6007E;"></i> Gestión Eventos
            </a>
            
            <a href="?page=mod_eventos&view=asistencia" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all <?= $section === 'asistencia' ? 'sidebar-active shadow-md' : 'text-gray-500 hover:bg-gray-50' ?>">
                <i data-lucide="list-checks" class="w-5 h-5" style="color: #5B2A86;"></i> Asistencia
            </a>

            <a href="?page=mod_eventos&view=reportes" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all <?= $section === 'reportes' ? 'sidebar-active shadow-md' : 'text-gray-500 hover:bg-gray-50' ?>">
                <i data-lucide="bar-chart-3" class="w-5 h-5" style="color: #F9B000;"></i> Reportes
            </a>

            <div class="pt-4 mt-4 border-t border-gray-100">
                <a href="?page=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm text-gray-400 hover:bg-slate-100 hover:text-[#5B2A86] transition-all group">
                    <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i> Volver al Panel
                </a>
            </div>
        </nav>

        <div class="p-6 border-t border-gray-50 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow-md"
                     style="background: linear-gradient(135deg, #E6007E, #5B2A86);">
                    <?= substr($user_nombre ?? 'U', 0, 1) ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-black truncate" style="color: #5B2A86;"><?= $user_nombre ?? 'Usuario' ?></p>
                    <p class="text-[10px] font-bold text-gray-400 uppercase"><?= $user_rol ?? 'Admin' ?></p>
                </div>
            </div>
            <a href="?action=logout" class="mt-4 flex items-center gap-2 text-[10px] font-black text-red-100 px-3 py-2 rounded-lg hover:bg-red-600 transition uppercase tracking-widest justify-center" style="background: rgba(239,68,68,0.7);">
                <i data-lucide="log-out" class="w-3 h-3"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col">
        
        <!-- Top Nav -->
        <header class="glass-nav sticky top-0 z-40 px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button class="lg:hidden p-2" style="color: #5B2A86;"><i data-lucide="menu" class="w-6 h-6"></i></button>
                <div class="hidden lg:flex flex-col">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Campaña Activa</span>
                    <h2 class="text-sm font-black" style="color: #5B2A86;"><?= $campanaActiva['nombre'] ?? 'Sin Campaña' ?></h2>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="relative group">
                    <select onchange="window.location.href='?page=mod_eventos&campana_id='+this.value+'&view=<?= $section ?>'" 
                            class="appearance-none bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-10 py-2 text-[11px] font-black outline-none focus:ring-2 transition-all cursor-pointer hover:bg-white shadow-sm"
                            style="color: #5B2A86; focus-ring-color: #E6007E;">
                        <?php foreach ($campanas as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $campanaActivaId ? 'selected' : '' ?>>
                                 <?= $c['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 absolute right-3 top-2.5 text-slate-400 pointer-events-none transition-transform group-hover:translate-y-0.5"></i>
                </div>

                <div class="h-8 w-px bg-slate-200"></div>

                <div class="flex items-center gap-3">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</span>
                        <span class="text-[10px] font-bold text-green-500 flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]"></div> 
                            SISTEMA ACTIVO
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-6 lg:p-8 flex-1 bg-slate-50/50">
            <?php 
                if ($section === 'eventos') {
                    $campanaId = $campanaActivaId;
                    include __DIR__ . '/pages/eventos.php';
                } elseif ($section === 'asistencia') {
                    $campanaId = $campanaActivaId;
                    include __DIR__ . '/pages/asistencia.php';
                } elseif ($section === 'reportes') {
                    $campanaId = $campanaActivaId;
                    include __DIR__ . '/pages/reportes.php';
                } else {
                    include __DIR__ . '/pages/eventos.php';
                }
            ?>
        </div>

    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
