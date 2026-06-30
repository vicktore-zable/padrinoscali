<?php
require_once __DIR__ . '/config/config.php';

$pagina = $_GET['page'] ?? 'dashboard';

// Portal as a module: handle portal pages// Redirección condicional para mod_colab (Portal del Líder)
$portalPages = ['portal_landing', 'portal_login', 'portal_auth', 'portal_red', 'perfil_lider', 'dashboard_lider', 'portal_dashboard', 'portal_registrar_simpatizante', 'portal_eventos', 'portal_change_password', 'portal_perfil'];

// NUEVO: Redirección para mod_elecciones (Público) (Cambiado para no pisar el panel de admin)
if (isset($_GET['page']) && strpos($_GET['page'], 'public_elecciones') !== false) {
    require_once __DIR__ . '/mod_elecciones/public/index.php';
    exit;
}

// Enlace a modulo de Consulta Electoral desde landing (usar otro nombre para evitar conflicto)
if (isset($_GET['page']) && $_GET['page'] === 'consulta_electoral') {
    require_once __DIR__ . '/mod_elecciones/public/index.php';
    exit;
}

// API: Lista de colaboradores con filtros geográficos
if (isset($_GET['api']) && $_GET['api'] === 'colaboradores_list') {
    require_once __DIR__ . '/config/config.php';
    $db = getDB();
    $campana = $_GET['campana_id'] ?? null;
    $where = [];
    $params = [];
    if ($campana) { $where[] = "campana_id = ?"; $params[] = $campana; }
    if (!empty($_GET['departamento'])) { $where[] = "departamento = ?"; $params[] = $_GET['departamento']; }
    if (!empty($_GET['municipio'])) { $where[] = "municipio = ?"; $params[] = $_GET['municipio']; }
    if (!empty($_GET['tipo_territorio'])) { $where[] = "tipo_territorio = ?"; $params[] = $_GET['tipo_territorio']; }
    if (!empty($_GET['barrio'])) { $where[] = "barrio = ?"; $params[] = $_GET['barrio']; }
    $where[] = "estado NOT IN ('inactivo', 'Inactivo')";
    
    $sql = "SELECT documento, nombres, apellidos, perfil, nivel_participacion, municipio, barrio FROM colaboradores";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY municipio, barrio LIMIT 200";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    header('Content-Type: application/json');
    echo json_encode(['colaboradores' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// mod_organizaciones dashboard público
if (isset($_GET['page']) && $_GET['page'] === 'dashboard_organizaciones_publico') {
    require_once __DIR__ . '/mod_organizaciones/dashboard.php';
    exit;
}

if (isset($_GET['page']) && in_array($_GET['page'], $portalPages)) {
    require_once __DIR__ . '/pages/' . $pagina . '.php';
    exit; // Exit to avoid rendering the Magenta Aratio layout
}

// Global Auth for main Aratio system
$excludeAuth = ['landing', 'dashboard_organizaciones_publico']; // Public pages

// Default to landing if not logged in and no specific page requested
if (!isset($_GET['page']) && !isset($_SESSION['user_id'])) {
    $pagina = 'landing';
}

if ($pagina === 'landing') {
    require_once __DIR__ . '/landing.php';
    exit;
}

if (!in_array($pagina, $excludeAuth)) {
    requireAuth();
}

$user = getSessionUser();
$campanas = [];
if ($user) {
    $auth = new Auth();
    $campanas = $auth->getUserCampanas($user['id']);
}

// Obtener campaña activa de la sesión o la primera disponible
$campanaActiva = null;
if (!empty($_GET['campana'])) {
    $_SESSION['campana_activa'] = $_GET['campana'];
}
$campanaActivaId = $_SESSION['campana_activa'] ?? ($campanas[0]['id'] ?? null);

// BUG FIX: persistir en sesión si se usó el fallback (primera campaña)
// Sin esto, páginas como colaboradores_red.php leen NULL de la sesión
if ($campanaActivaId && empty($_SESSION['campana_activa'])) {
    $_SESSION['campana_activa'] = $campanaActivaId;
}

foreach ($campanas as $campana) {
    if ($campana['id'] == $campanaActivaId) {
        $campanaActiva = $campana;
        $_SESSION['campana_nombre'] = $campana['nombre'];
        break;
    }
}

$paginasPermitidas = [
    'dashboard', 'colaboradores', 'colaboradores_red', 'colaborador_detalle',
    'colaboradores_reportes', 'donaciones', 'eventos', 'acciones', 'compromisos',
    'reportes', 'grupos', 'elecciones', 'candidatos', 'campanas', 'usuarios',
    'ayuda', 'configuracion', 'registro_asistencia', 'organizaciones',
    'whatsapp_log', 'whatsapp_messages', 'workflows',
    'llamadas', 'emails', 'dashboard_territorial'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aratio - Sistema de Gestión Electoral</title>
    
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '<?= COLOR_PRIMARY ?>',
                        secondary: '<?= COLOR_SECONDARY ?>',
                        accent: '<?= COLOR_ACCENT ?>',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, <?= COLOR_PRIMARY ?> 0%, <?= COLOR_SECONDARY ?> 100%);
        }
        
        .sidebar {
            width: 280px;
            transition: all 0.3s;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
        
        .stat-card {
            @apply bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow;
        }
        
        .card {
            @apply bg-white rounded-xl shadow-sm p-6;
        }
        
        .input, .form-input, .form-select {
            @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all;
        }
        
        .btn-primary {
            @apply px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg font-medium hover:opacity-90 transition active:scale-95;
        }

        .btn-secondary {
            @apply px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition active:scale-95;
        }

        .btn-ghost {
            @apply px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition;
        }
        
        .badge {
            @apply px-3 py-1 text-xs font-medium rounded-full;
        }
        
        .badge-primary {
            @apply bg-primary/10 text-primary;
        }
        
        .badge-success {
            @apply bg-green-100 text-green-700;
        }
        
        .badge-warning {
            @apply bg-yellow-100 text-yellow-700;
        }
        
        .badge-info {
            @apply bg-blue-100 text-blue-700;
        }
        
        .badge-error {
            @apply bg-red-100 text-red-700;
        }

        .modal {
            @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50;
        }

        .modal-content {
            @apply bg-white rounded-xl p-6 w-full max-h-[90vh] overflow-y-auto;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="appData()" x-init="init()">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 fixed top-0 left-0 right-0 z-40">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Logo y Menu Mobile -->
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 gradient-bg rounded-lg flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Aratio</h1>
                        <p class="text-xs text-gray-500">Gestión Electoral</p>
                    </div>
                </div>
            </div>

            <!-- Selector de Campaña -->
            <?php if ($campanaActiva): ?>
            <div class="hidden md:block flex-1 max-w-md mx-8">
                <select 
                    class="input"
                    onchange="window.location.href='?campana='+this.value"
                >
                    <?php foreach ($campanas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $campanaActivaId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Usuario -->
            <div class="flex items-center gap-4" x-data="{ open: false }">
                <button class="hidden md:block btn-ghost">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </button>
                <div class="relative">
                    <button @click="open = !open" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center">
                            <span class="text-white font-bold"><?= substr($user['nombre'], 0, 1) ?></span>
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['nombre']) ?></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($user['rol']) ?></p>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                    </button>
                    
                    <!-- Dropdown -->
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                        <a href="?page=configuracion" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i data-lucide="settings" class="w-4 h-4 inline mr-2"></i>
                            Configuración
                        </a>
                        <a href="?page=ayuda" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i data-lucide="help-circle" class="w-4 h-4 inline mr-2"></i>
                            Ayuda
                        </a>
                        <hr class="my-2">
                            <a href="<?= url('logout.php') ?>" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i data-lucide="log-out" class="w-4 h-4 mr-3"></i>
                                Cerrar Sesión
                            </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside 
        class="sidebar fixed top-0 left-0 h-full bg-white border-r border-gray-200 z-50 pt-20"
        :class="{ 'open': sidebarOpen }"
    >
        <div class="p-6 h-full overflow-y-auto">
            <!-- Navegación Principal -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold uppercase text-gray-500 mb-3 px-4">Navegación Principal</h4>
                <nav class="space-y-1">
                    <a href="?page=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'dashboard' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span class="text-sm">Dashboard</span>
                    </a>
                    <a href="?page=colaboradores" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'colaboradores' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        <span class="text-sm">Padrinos</span>
                    </a>
                    <a href="?page=donaciones" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'donaciones' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                        <span class="text-sm">Donaciones</span>
                    </a>
                    <a href="?page=eventos" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'eventos' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                        <span class="text-sm">Eventos</span>
                    </a>
                    <a href="?page=acciones" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'acciones' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                        <span class="text-sm">Acciones Comunitarias</span>
                    </a>
                    <a href="?page=compromisos" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'compromisos' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="handshake" class="w-5 h-5"></i>
                        <span class="text-sm">Compromisos</span>
                    </a>
                    <a href="?page=reportes" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'reportes' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        <span class="text-sm">Reportes</span>
                    </a>
                    <a href="?page=dashboard_territorial" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'dashboard_territorial' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="globe" class="w-5 h-5"></i>
                        <span class="text-sm">Dashboard Territorial</span>
                    </a>
                    <a href="?page=whatsapp_log" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'whatsapp_log' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="cake" class="w-5 h-5"></i>
                        <span class="text-sm">Cumpleaños</span>
                    </a>
                </nav>
            </div>

            <!-- ALAS -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold uppercase text-gray-500 mb-3 px-4 flex items-center justify-between">
                    <span>ALAS</span>
                    <span x-show="alasUnread > 0" class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center" x-text="alasUnread"></span>
                </h4>
                <nav class="space-y-1">
                    <a href="?page=whatsapp_messages" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'whatsapp_messages' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                        <span class="text-sm flex-1">Inbox</span>
                        <span x-show="alasUnread > 0" class="bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center" x-text="alasUnread"></span>
                    </a>
                    <a href="?page=workflows" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'workflows' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                        <span class="text-sm">Workflows</span>
                    </a>
                    <a href="?page=llamadas" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'llamadas' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="phone-call" class="w-5 h-5"></i>
                        <span class="text-sm">Phone Banking</span>
                    </a>
                    <a href="?page=emails" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'emails' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                        <span class="text-sm">Email</span>
                    </a>
                </nav>
            </div>

            <!-- Social CRM -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold uppercase text-gray-500 mb-3 px-4">Social CRM</h4>
                <nav class="space-y-1">
                    <a href="?page=social_crm" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'social_crm' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                        <span class="text-sm">Social CRM</span>
                    </a>
                    <a href="?page=instagram_graph" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'instagram_graph' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="instagram" class="w-5 h-5"></i>
                        <span class="text-sm">IG Comentarios</span>
                    </a>
                    <a href="?page=actividad_instagram" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'actividad_instagram' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="camera" class="w-5 h-5"></i>
                        <span class="text-sm">Monitor Digital</span>
                    </a>
                    <a href="?page=mapa_instagram" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'mapa_instagram' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="map" class="w-5 h-5"></i>
                        <span class="text-sm">Mapa de Gestión</span>
                    </a>
                    <a href="?page=dashboard_territorial_social" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'dashboard_territorial_social' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="globe" class="w-5 h-5"></i>
                        <span class="text-sm">Dashboard Territorial</span>
                    </a>
                    <a href="?page=lideres" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'lideres' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="crown" class="w-5 h-5"></i>
                        <span class="text-sm">Lideres</span>
                    </a>
                </nav>
            </div>

            <!-- Administración -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold uppercase text-gray-500 mb-3 px-4">Administración</h4>
                <nav class="space-y-1">
                    <a href="?page=grupos" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'grupos' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="users-2" class="w-5 h-5"></i>
                        <span class="text-sm">Grupos Políticos</span>
                    </a>
                    <a href="?page=elecciones" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'elecciones' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                        <span class="text-sm">Elecciones</span>
                    </a>
                    <a href="https://aratio.mrmtech.net/dashboard?page=consulta_electoral" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50">
                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                        <span class="text-sm">Consulta Electoral</span>
                    </a>
                    <a href="?page=candidatos" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'candidatos' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="user-circle" class="w-5 h-5"></i>
                        <span class="text-sm">Candidatos</span>
                    </a>
                    <a href="?page=campanas" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'campanas' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="flag" class="w-5 h-5"></i>
                        <span class="text-sm">Campañas</span>
                    </a>
                    <!-- mod_organizaciones -->
                    <a href="<?= url('?page=organizaciones') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'organizaciones' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                        <span class="text-sm">Organizaciones</span>
                    </a>
                    <?php if ($auth->canManageUsers()): ?>
                    <a href="?page=usuarios" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'usuarios' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                        <span class="text-sm">Usuarios</span>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- Bottom -->
            <div class="mt-auto">
                <nav class="space-y-1">
                    <a href="?page=ayuda" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'ayuda' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                        <span class="text-sm">Ayuda</span>
                    </a>
                    <a href="?page=configuracion" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $pagina === 'configuracion' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                        <span class="text-sm">Configuración</span>
                    </a>
                </nav>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-[280px] pt-20 p-6">
        <?php
        $pageFile = __DIR__ . '/pages/' . $pagina . '.php';
        if (file_exists($pageFile)) {
            include $pageFile;
        } else {
            include __DIR__ . '/pages/dashboard.php';
        }
        ?>
    </main>

    <!-- ALAS Toast -->
    <div x-data="{ toast: { show: false, message: '', type: 'info' } }"
         @alas-toast.window="toast.message = $event.detail.message; toast.type = $event.detail.type || 'info'; toast.show = true; setTimeout(() => toast.show = false, 5000)"
         class="fixed bottom-6 right-6 z-50">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-4 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-4 opacity-0"
             :class="toast.type === 'success' ? 'bg-green-600' : toast.type === 'warning' ? 'bg-yellow-600' : toast.type === 'error' ? 'bg-red-600' : 'bg-gray-800'"
             class="px-5 py-3 rounded-xl text-white text-sm font-medium shadow-lg flex items-center gap-3 max-w-sm">
            <template x-if="toast.type === 'success'"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i></template>
            <template x-if="toast.type === 'warning'"><i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i></template>
            <template x-if="toast.type === 'error'"><i data-lucide="x-circle" class="w-5 h-5 flex-shrink-0"></i></template>
            <template x-if="toast.type === 'info'"><i data-lucide="bell" class="w-5 h-5 flex-shrink-0"></i></template>
            <span x-text="toast.message"></span>
            <button @click="toast.show = false" class="ml-auto text-white/70 hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function appData() {
            return {
                sidebarOpen: false,
                alasUnread: 0,
                alasPrevUnread: 0,
                alasPollInterval: null,

                init() {
                    lucide.createIcons();
                    
                    document.querySelectorAll('aside a').forEach(link => {
                        link.addEventListener('click', () => {
                            if (window.innerWidth < 1024) {
                                this.sidebarOpen = false;
                            }
                        });
                    });

                    this.pollAlas();
                    this.alasPollInterval = setInterval(() => this.pollAlas(), 30000);
                },

                destroy() {
                    if (this.alasPollInterval) {
                        clearInterval(this.alasPollInterval);
                    }
                },

                async pollAlas() {
                    try {
                        const resp = await fetch('api/whatsapp_messages.php?action=stats');
                        const json = await resp.json();
                        if (json.success) {
                            this.alasUnread = json.data.mensajes_no_leidos || 0;
                            if (this.alasUnread > this.alasPrevUnread) {
                                const diff = this.alasUnread - this.alasPrevUnread;
                                window.dispatchEvent(new CustomEvent('alas-toast', {
                                    detail: {
                                        message: diff + ' ' + (diff === 1 ? 'mensaje nuevo' : 'mensajes nuevos') + ' en ALAS Inbox',
                                        type: 'info'
                                    }
                                }));
                            }
                            this.alasPrevUnread = this.alasUnread;
                        }
                    } catch (e) { /* silent */ }
                }
            }
        }
    </script>
    <!-- QRCode.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</body>
</html>
