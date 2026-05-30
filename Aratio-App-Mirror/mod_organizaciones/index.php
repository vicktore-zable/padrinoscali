<?php
/**
 * mod_organizaciones - Standalone Portal
 * Entry point for aratio.mrmtech.net/organizaciones
 */
require_once __DIR__ . '/../config/config.php';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new Auth();
    $auth->logout();
    header('Location: /');
    exit;
}

$view = $_GET['view'] ?? 'dashboard'; // Default to public dashboard
$isPublicView = ($view === 'dashboard');

if (!$isPublicView && !isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user_id'] ?? null;
$user_nombre = $_SESSION['user_nombre'] ?? 'Usuario';
$user_rol = $_SESSION['user_rol'] ?? 'Admin';
$db   = getDB();

// Handle Campaign Active
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

// Minimal info for public view if campanaActiva is null
if (!$campanaActiva && $isPublicView) {
    $stmt = $db->prepare("SELECT id, nombre FROM campanas WHERE id = ?");
    $stmt->execute([$campanaActivaId]);
    $campanaActiva = $stmt->fetch(PDO::FETCH_ASSOC);
}

$section = $view;
$isIncluded = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizaciones Portal — <?= $campanaActiva['nombre'] ?? 'Aratio' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 
                        primary: '<?= COLOR_PRIMARY ?>', 
                        secondary: '<?= COLOR_SECONDARY ?>', 
                        accent: '<?= COLOR_ACCENT ?>' 
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        *, body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .sidebar-active { background: rgba(30, 58, 95, 0.1); color: #1e3a5f; border-right: 4px solid #1e3a5f; }
        .glass-nav { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    <!-- ── SIDEBAR ────────────────────────────────────────────────────────── -->
    <aside class="w-72 bg-white border-r border-gray-100 hidden lg:flex flex-col sticky top-0 h-screen z-50">
        <div class="p-8 border-b border-gray-100 flex items-center gap-4 bg-gradient-to-r from-white to-slate-50">
            <div class="w-12 h-12 bg-[#1e3a5f] rounded-2xl flex items-center justify-center shadow-xl transform -rotate-3 transition-transform hover:rotate-0">
                <i data-lucide="building-2" class="w-6 h-6 text-[#d4af37]"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-primary tracking-tighter leading-none">Organizaciones</h1>
                <p class="text-[8px] font-black text-accent uppercase tracking-widest mt-1 opacity-80">By Aratio Intelligence</p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-2 mt-4">
            <div class="px-4 mb-2 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Navegación</div>
            
            <a href="?view=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all <?= $section === 'dashboard' ? 'sidebar-active shadow-md' : 'text-gray-500 hover:bg-gray-50' ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard Geo
            </a>
            
            <?php if ($user): ?>
            <a href="?view=gestion" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all <?= $section === 'gestion' ? 'sidebar-active shadow-md' : 'text-gray-500 hover:bg-gray-50' ?>">
                <i data-lucide="users" class="w-5 h-5"></i> Gestión
            </a>
            <a href="?view=interes" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm transition-all <?= $section === 'interes' ? 'sidebar-active shadow-md' : 'text-gray-500 hover:bg-gray-50' ?>">
                <i data-lucide="target" class="w-5 h-5"></i> Grupos de Interés
            </a>
            <?php else: ?>
            <a href="<?= url('login.php') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm text-gray-400 hover:bg-slate-100 hover:text-primary transition-all group">
                <i data-lucide="lock" class="w-5 h-5 group-hover:animate-pulse"></i> Acceso Privado
            </a>
            <?php endif; ?>
        </nav>

        <?php if ($user): ?>
        <div class="p-6 border-t border-gray-50 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-[#1e3a5f] to-[#2c5282] rounded-full flex items-center justify-center text-white font-bold shadow-md">
                    <?= substr($user_nombre ?? 'U', 0, 1) ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-black text-[#1e3a5f] truncate"><?= $user_nombre ?? 'Usuario' ?></p>
                    <p class="text-[10px] font-bold text-gray-400 uppercase"><?= $user_rol ?? 'Admin' ?></p>
                </div>
            </div>
            <a href="?action=logout" class="mt-4 flex items-center gap-2 text-[10px] font-black text-red-100 bg-red-500/80 px-3 py-2 rounded-lg hover:bg-red-600 transition uppercase tracking-widest justify-center">
                <i data-lucide="log-out" class="w-3 h-3"></i> Cerrar Sesión
            </a>
        </div>
        <?php endif; ?>
    </aside>

    <!-- ── MAIN CONTENT ────────────────────────────────────────────────────── -->
    <main class="flex-1 flex flex-col">
        
        <!-- Top Nav (Campaña & Perfil) -->
        <header class="glass-nav sticky top-0 z-40 px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button class="lg:hidden p-2 text-[#1e3a5f]"><i data-lucide="menu" class="w-6 h-6"></i></button>
                <div class="hidden lg:flex flex-col">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Campaña Activa</span>
                    <h2 class="text-sm font-black text-[#1e3a5f]"><?= $campanaActiva['nombre'] ?? 'Sin Campaña' ?></h2>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <!-- Selector de Campaña Compacto -->
                <div class="relative group">
                    <select onchange="window.location.href='?campana_id='+this.value+'&view=<?= $section ?>'" 
                            class="appearance-none bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-10 py-2 text-[11px] font-black text-primary outline-none focus:ring-2 focus:ring-primary transition-all cursor-pointer hover:bg-white shadow-sm">
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
                if ($section === 'dashboard') {
                    $isIncluded = true;
                    include __DIR__ . '/dashboard.php';
                } elseif ($section === 'interes') {
                    $isIncluded = true;
                    include __DIR__ . '/grupos_de_interes.php';
                } else {
                    $campanaId = $campanaActivaId; 
                    include __DIR__ . '/../pages/organizaciones.php';
                }
            ?>
        </div>

    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
