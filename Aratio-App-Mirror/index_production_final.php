<?php
require_once __DIR__ . '/config/config.php';

// --- LOGICA DE LANDING PAGE ---
// Si no hay una página específica solicitada Y no hay sesión, mostrar index.html
if (!isset($_GET['page']) && !isset($_SESSION['user_id'])) {
    if (file_exists(__DIR__ . '/index.html')) {
        include __DIR__ . '/index.html';
        exit;
    }
}

$pagina = $_GET['page'] ?? 'dashboard';

// Portal as a module: handle portal pages before the main layout starts
$portalPages = [
    'portal_landing', 'portal_login', 'portal_auth', 
    'portal_dashboard', 'portal_red', 'portal_eventos',
    'registro_lider', 'registro_simpatizante'
];

if (in_array($pagina, $portalPages)) {
    require_once __DIR__ . '/pages/' . $pagina . '.php';
    exit; // Exit to avoid rendering the Magenta Aratio layout
}

// Global Auth for main Aratio system
$excludeAuth = []; // Main system pages that don't need auth (if any)
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
    'ayuda', 'configuracion', 'registro_asistencia'
];

// ... (El resto del archivo index.php sigue igual para mantener el diseño Magenta)
include 'index_remote.php'; // Usamos el resto de la estructura ya existente para no duplicar código
