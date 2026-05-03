<?php
/**
 * mod_jac - Dashboard Público
 * Juntas de Acción Comunal — Valle del Cauca
 * Acceso SIN autenticación requerida
 */
require_once __DIR__ . '/../config/config.php';

$campanaId = intval($_GET['campana_id'] ?? ($_SESSION['campana_activa'] ?? 0));

// Obtener datos de la campaña si se pasó ID
$campana  = null;
$stats    = ['total' => 0, 'activas' => 0, 'total_votos' => 0, 'total_afiliados' => 0, 'territorios_cubiertos' => 0, 'municipios_cubiertos' => 0];
$byMunicipio  = [];
$byTerritorio = [];
$recentJacs   = [];

if ($campanaId) {
    $db = getDB();
    try {
        // Campaña
        $stmt = $db->prepare("SELECT nombre, slogan FROM campanas WHERE id = ?");
        $stmt->execute([$campanaId]);
        $campana = $stmt->fetch(PDO::FETCH_ASSOC);

        // Stats globales
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN estado='activa' THEN 1 ELSE 0 END) AS activas,
                COALESCE(SUM(votos_comprometidos),0) AS total_votos,
                COALESCE(SUM(afiliados_count),0) AS total_afiliados,
                COUNT(DISTINCT territorio_valle) AS territorios_cubiertos,
                COUNT(DISTINCT municipio) AS municipios_cubiertos
            FROM jac_registros WHERE id_campana = ?
        ");
        $stmt->execute([$campanaId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $stats;

        // Por municipio para filtros iniciales
        $stmt = $db->prepare("
            SELECT municipio, COUNT(*) AS total_jacs, SUM(votos_comprometidos) AS votos, SUM(afiliados_count) AS afiliados
            FROM jac_registros WHERE id_campana = ?
            GROUP BY municipio ORDER BY votos DESC
        ");
        $stmt->execute([$campanaId]);
        $byMunicipio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        // La tabla puede no existir aún
    }
}

$campanaNombre = $campana['nombre'] ?? 'Campaña Aratio';
$campanaSlogan = $campana['slogan'] ?? 'Gestión Territorial';

if (isset($isIncluded)) {
    // Solo mostramos el contenido, el layout viene de mod_jac/index.php
    ?>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #1e3a5f 0%, #0f2035 50%, #1a3352 100%); }
        .gold-accent   { color: #0d9488; }
        .glass-card {
            background: rgba(255,255,255,0.97);
            border: 1px solid rgba(212,175,55,0.12);
            box-shadow: 0 8px 32px rgba(30,58,95,0.08);
            backdrop-filter: blur(10px);
        }
        #map { height: 600px; width: 100%; z-index: 10; border-radius: 1rem; }
        .leaflet-control-zoom { border: none !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; }
        .map-popup-custom .leaflet-popup-content-wrapper { border-radius: 12px; font-family: 'Inter', sans-serif; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .map-popup-custom .leaflet-popup-content { margin: 0; width: 280px !important; }
        .map-popup-custom .leaflet-popup-tip-container { display: none; }
        .plancha-member { font-size: 0.75rem; border-bottom: 1px solid #f1f5f9; padding: 4px 0; }
        .plancha-member:last-child { border-bottom: none; }
        @keyframes counter { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
        .animate-counter { animation: counter 0.6s ease-out forwards; }
        [x-cloak] { display: none !important; }
    </style>
    <div x-data="jacDashboard()">
    <?php
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Geo de Entidades — <?= htmlspecialchars($campanaNombre) ?></title>
    <meta name="description" content="Dashboard público de Entidades y Organizaciones — <?= htmlspecialchars($campanaNombre) ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#1e3a5f', secondary: '#0d9488' } } } }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        *, body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #1e3a5f 0%, #0f2035 50%, #1a3352 100%); }
        .gold-accent   { color: #0d9488; }
        .glass-card { background: rgba(255,255,255,0.97); border: 1px solid rgba(212,175,55,0.12); box-shadow: 0 8px 32px rgba(30,58,95,0.08); backdrop-filter: blur(10px); }
        #map { height: 600px; width: 100%; z-index: 10; border-radius: 1rem; }
        .leaflet-control-zoom { border: none !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; }
        .map-popup-custom .leaflet-popup-content-wrapper { border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); padding: 0; overflow: hidden; }
        .map-popup-custom .leaflet-popup-content { margin: 0; width: 280px !important; }
        .map-popup-custom .leaflet-popup-tip-container { display: none; }
        .plancha-member { font-size: 0.75rem; border-bottom: 1px solid #f1f5f9; padding: 4px 0; }
        .plancha-member:last-child { border-bottom: none; }
        @keyframes counter { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
        .animate-counter { animation: counter 0.6s ease-out forwards; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen" x-data="jacDashboard()">
<?php } ?>

<?php if (!isset($isIncluded)): ?>
<!-- ── HERO (Solo vista pública) ────────────────────────────────────────── -->
<div class="hero-gradient text-white pb-32 pt-16 px-4">
    <div class="max-w-full mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <a href="/?page=dashboard" class="inline-flex items-center gap-2 text-white/50 hover:text-white mb-6 text-sm font-bold transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Volver al Sistema Aratio
                </a>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 bg-[#0d9488] rounded-2xl flex items-center justify-center shadow-lg transform rotate-3">
                        <i data-lucide="map" class="w-7 h-7 text-[#1e3a5f]"></i>
                    </div>
                    <div>
                <h1 class="text-3xl font-black leading-tight mt-1">Entidades / Organizaciones</h1>
                    </div>
                </div>
                <h2 class="text-4xl md:text-5xl font-black mb-2 tracking-tight opacity-90">
                    <?= htmlspecialchars($campanaNombre) ?>
                </h2>
                <p class="text-white/60 font-medium text-lg flex items-center gap-2">
                    <i data-lucide="activity" class="w-4 h-4 text-green-400"></i>
                    En Vivo — Visualización Georreferenciada
                </p>
            </div>
            
            <div class="hidden lg:flex gap-3 text-xs font-black text-white px-5 py-4 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 shadow-xl items-center">
                <div class="flex flex-col gap-1 items-end">
                    <span class="text-white/50">Total Registros</span>
                    <span class="text-3xl text-[#0d9488] leading-none"><?= number_format($stats['total']) ?></span>
                </div>
                <div class="h-10 w-px bg-white/20 mx-3"></div>
                <div class="flex flex-col gap-1 items-end">
                    <span class="text-white/50">Votos Comprometidos</span>
                    <span class="text-3xl text-white leading-none"><?= number_format($stats['total_votos']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6 bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
    <div>
        <h1 class="text-3xl font-black text-[#1e3a5f] tracking-tighter">Dashboard Geográfico</h1>
        <p class="text-gray-400 font-bold text-sm">Visualización en tiempo real de las Entidades y Organizaciones.</p>
    </div>
    
    <div class="flex gap-8">
        <div class="flex flex-col">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Entidades Registradas</span>
            <span class="text-2xl font-black text-[#1e3a5f] leading-none"><?= number_format($stats['total']) ?></span>
        </div>
        <div class="w-px h-10 bg-gray-100"></div>
        <div class="flex flex-col">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Votos Proyectados</span>
            <span class="text-2xl font-black text-magenta leading-none"><?= number_format($stats['total_votos']) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── MAIN CONTENT (MAP + LAYERS) ─────────────────────────────────────── -->
<div class="max-w-full mx-auto px-4 <?= isset($isIncluded) ? '' : '-mt-24 relative z-10' ?> pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- SIDEBAR FILTROS -->
        <div class="lg:col-span-1 space-y-4">
            <div class="glass-card rounded-2xl p-5 border-t-4 border-t-[#1e3a5f]">
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="filter" class="w-5 h-5 text-[#1e3a5f]"></i>
                    <h3 class="font-black text-[#1e3a5f] uppercase tracking-wide text-sm">Filtros Territoriales</h3>
                </div>

                <div class="space-y-4">
                    <!-- Departamento -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Departamento</label>
                        <select x-model="filters.departamento" @change="fetchMunicipios" class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-[#1e3a5f] focus:border-[#1e3a5f] p-2.5 outline-none transition-shadow hover:shadow-sm">
                            <option value="">Seleccione...</option>
                            <template x-for="(dep, index) in dropdowns.departamentos" :key="'dep-'+index">
                                <option :value="dep" x-text="dep"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Municipio -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Municipio</label>
                        <select x-model="filters.municipio" @change="fetchTipos" :disabled="!filters.departamento" class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-[#1e3a5f] focus:border-[#1e3a5f] p-2.5 outline-none transition-shadow hover:shadow-sm disabled:bg-gray-50">
                            <option value="">Seleccione...</option>
                            <template x-for="(mun, index) in dropdowns.municipios" :key="'mun-'+index">
                                <option :value="mun" x-text="mun"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Tipo Territorio -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Tipo Territorio</label>
                        <select x-model="filters.tipo" @change="fetchSectores" :disabled="!filters.municipio" class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-[#1e3a5f] focus:border-[#1e3a5f] p-2.5 outline-none cursor-pointer disabled:bg-gray-50 disabled:cursor-not-allowed">
                            <option value="">Seleccione...</option>
                            <template x-for="(t, index) in dropdowns.tipos" :key="'tipo-'+index">
                                <option :value="t" x-text="t"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Sector/Comuna -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Comuna / Corregimiento</label>
                        <select x-model="filters.sector" @change="fetchBarrios" :disabled="!filters.tipo" class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-[#1e3a5f] focus:border-[#1e3a5f] p-2.5 outline-none cursor-pointer disabled:bg-gray-50 disabled:cursor-not-allowed">
                            <option value="">Seleccione...</option>
                            <template x-for="(s, index) in dropdowns.sectores" :key="'sector-'+index">
                                <option :value="s" x-text="s"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Barrio/Vereda -->
                    <!-- Barrio/Vereda -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Barrio / Vereda</label>
                        <select x-model="filters.barrio" @change="fetchMapaData" :disabled="!filters.sector" class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-[#1e3a5f] focus:border-[#1e3a5f] p-2.5 outline-none cursor-pointer disabled:bg-gray-50 disabled:cursor-not-allowed">
                            <option value="">Seleccione...</option>
                            <template x-for="(b, index) in dropdowns.barrios" :key="'barrio-'+index">
                                <option :value="b" x-text="b"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Tipo Organización -->
                    <div class="pt-2 border-t border-gray-100 mt-2">
                        <label class="block text-[10px] font-black justify-between text-[#1e3a5f] uppercase mb-1 flex items-center">
                            Filtro Específico
                            <i data-lucide="filter" class="w-3 h-3 text-gray-400"></i>
                        </label>
                        <select x-model="filters.tipo_organizacion" @change="fetchMapaData" class="w-full bg-gray-50 border border-gray-200 font-bold text-[#1e3a5f] text-sm rounded-lg focus:ring-[#1e3a5f] focus:border-[#1e3a5f] p-2.5 outline-none transition-shadow hover:shadow-sm cursor-pointer">
                            <option value=""> TODAS LAS ENTIDADES </option>
                            <option value="JAC">✅ Juntas de Acción Comunal</option>
                            <option value="Deporte">⚽ Grupos Deportivos</option>
                            <option value="Cultura">🎭 Gestores Culturales</option>
                            <option value="Ambiente">🌱 Gestores Ambientales</option>
                            <option value="Social">🤝 Gestores Sociales</option>
                            <option value="Otro">📌 Otros</option>
                        </select>
                    </div>
                </div>

                <button @click="resetFilters" class="mt-6 w-full py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-600 font-bold text-xs uppercase tracking-wider rounded-lg border border-gray-200 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Limpiar Filtros
                </button>
            </div>

            <!-- KPIs Dinámicos del Mapa -->
            <div class="glass-card rounded-2xl p-5 bg-gradient-to-br from-[#1e3a5f] to-[#2c5282] text-white border-none shadow-xl relative overflow-hidden">
                <div class="absolute -right-4 -top-4 opacity-10">
                    <i data-lucide="bar-chart-2" class="w-32 h-32"></i>
                </div>
                <h3 class="font-black uppercase tracking-widest text-[10px] text-white/70 mb-4">En Vista Actual</h3>
                
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <div>
                        <p class="text-3xl font-black text-[#0d9488]" x-text="mapStats.organizaciones">0</p>
                        <p class="text-[10px] font-bold uppercase text-white/70" x-text="filters.tipo_organizacion ? filters.tipo_organizacion + ' Encontradas' : 'Organizaciones'"></p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-green-400" x-text="mapStats.planchas">0</p>
                        <p class="text-[10px] font-bold uppercase text-white/70">Planchas Activas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- AREA MAPA -->
        <div class="lg:col-span-3 glass-card rounded-2xl p-2 relative shadow-2xl border border-gray-200/50">
            <!-- Loading Overlay -->
            <div x-show="loadingGrid" class="absolute inset-0 z-50 bg-white/80 backdrop-blur-sm rounded-2xl flex flex-col items-center justify-center">
                <div class="w-12 h-12 border-4 border-[#1e3a5f]/20 border-t-[#0d9488] rounded-full animate-spin"></div>
                <p class="mt-4 font-bold text-[#1e3a5f] text-sm animate-pulse">Cargando geometrías...</p>
            </div>
            
            <div id="map" class="shadow-inner relative z-10"></div>
            
            <!-- Leyenda del Mapa -->
            <div class="absolute bottom-6 right-6 z-20 bg-white/95 backdrop-blur-md p-3 rounded-xl shadow-lg border border-gray-100 text-xs shadow-xl hidden lg:block">
                <h4 class="font-black text-[#1e3a5f] uppercase mb-2">Leyenda Organizaciones</h4>
                <div class="space-y-1.5 font-medium text-gray-600">
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-[#3b82f6]"></div> Juntas de Acción Comunal</div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-[#f97316]"></div> Deporte</div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-[#ec4899]"></div> Cultura</div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-[#22c55e]"></div> Ambiente</div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-[#8b5cf6]"></div> Social / Otros</div>
                    <div class="mt-2 pt-2 border-t border-gray-200 text-[10px] font-bold text-gray-400">
                        *El número del ícono indica cantidad agrupada.
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── LISTADO DETALLADO ────────────────────────────────────────────── -->
    <div class="mt-8 glass-card rounded-3xl overflow-hidden border border-gray-200/60 shadow-lg">
        <div class="p-6 border-b border-gray-100 bg-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-[#1e3a5f]/10 rounded-xl">
                    <i data-lucide="users" class="w-5 h-5 text-[#1e3a5f]"></i>
                </div>
                <div>
                    <h3 class="font-black text-[#1e3a5f] text-lg">Top Sectores</h3>
                    <p class="text-xs text-gray-500 font-medium">Sectores con mayor concentración de Entidades</p>
                </div>
            </div>
            <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200 text-sm font-black text-[#1e3a5f]">
                <span x-text="mapData.length"></span> registros
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Municipio</th>
                        <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sector / Barrio</th>
                        <th class="text-center px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Entidades</th>
                        <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Top Plancha Activa</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <template x-for="row in sortedMapData" :key="row.sector_nombre + row.municipio">
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-[#1e3a5f]" x-text="row.municipio"></span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2 relative">
                                        <div class="w-3 h-3 rounded-full" :class="getMarkerColorClassByType(row.tipo_organizacion)"></div>
                                        <span class="font-bold text-gray-700" x-text="row.sector_nombre"></span>
                                    </div>
                                    <span class="text-[9px] font-bold text-gray-400 ml-5 uppercase" x-text="row.tipo_organizacion"></span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 font-black text-gray-800" x-text="row.jacs.length"></span>
                            </td>
                            <td class="px-4 py-4">
                                <template x-if="row.jacs[0] && row.jacs[0].plancha">
                                    <div>
                                        <p class="font-black text-green-700 text-xs uppercase" x-text="row.jacs[0].plancha.nombre_plancha"></p>
                                        <p class="text-[10px] text-gray-500 font-medium mt-0.5" x-text="row.jacs[0].plancha.miembros.length + ' miembros listados'"></p>
                                    </div>
                                </template>
                                <template x-if="!row.jacs[0] || !row.jacs[0].plancha">
                                    <span class="text-xs text-gray-400 italic">Sin plancha activa</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="mapData.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 font-medium bg-gray-50 border-t border-gray-100">
                            Ningún dato encontrado para los filtros aplicados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!isset($isIncluded)): ?>
<!-- ── FOOTER ────────────────────────────────────────────────────────────── -->
<footer class="bg-white border-t border-gray-200 mt-12 py-8 px-4 relative z-10">
    <div class="max-w-full mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-500 font-medium">
        <div class="flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4 text-green-600"></i>
            <span class="font-black uppercase tracking-widest text-gray-700">Aratio Digital Intelligence</span>
        </div>
        <span>Módulo JAC v2.0 Geo — <?= date('d M Y, H:i') ?></span>
    </div>
</footer>
<?php endif; ?>

<script>
lucide.createIcons();

document.addEventListener('alpine:init', () => {
    Alpine.data('jacDashboard', () => ({
        campanaId: <?= $campanaId ?>,
        loadingGrid: true,
        mapData: [],
        mapInstance: null,
        geoLayerGroup: null,
        markerGroup: null,
        
        // Filtros y Dropdowns
        filters: {
            departamento: 'VALLE DEL CAUCA', // Default para esta campaña
            municipio: '',
            tipo: '',
            sector: '',
            barrio: '',
            tipo_organizacion: ''
        },
        dropdowns: {
            departamentos: [],
            municipios: [],
            tipos: [],
            sectores: [],
            barrios: []
        },
        
        mapStats: {
            organizaciones: 0,
            planchas: 0
        },

        init() {
            this.initMap();
            this.fetchDepartamentos(); 
            if (this.campanaId) {
                this.fetchMapaData(); // Inicial carga todo
            } else {
                this.loadingGrid = false;
            }
        },
        
        initMap() {
            // Inicializar Leaflet centrando en Valle del Cauca as fallback
            this.mapInstance = L.map('map', { zoomControl: false, scrollWheelZoom: false }).setView([3.6218, -76.3533], 9);
            
            L.control.zoom({ position: 'topright' }).addTo(this.mapInstance);
            
            // Base layer clara/moderna
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(this.mapInstance);
            
            this.geoLayerGroup = L.featureGroup().addTo(this.mapInstance);
            this.markerGroup = L.layerGroup().addTo(this.mapInstance);
        },
        
        // ── LLAMADAS API FILTROS (CASCADA) ──
        async fetchDepartamentos() {
            try {
                const res = await fetch(`../api/territorios.php?accion=departamentos`);
                const json = await res.json();
                if(json.success) {
                    this.dropdowns.departamentos = json.data;
                    // Si ya tenemos Valle, cargar municipios
                    if(this.filters.departamento) this.fetchMunicipios();
                }
            } catch(e) { console.error(e); }
        },

        async fetchMunicipios() {
            this.filters.municipio = '';
            this.filters.tipo = '';
            this.filters.sector = '';
            this.filters.barrio = '';
            this.dropdowns.municipios = [];
            this.dropdowns.tipos = [];
            this.dropdowns.sectores = [];
            this.dropdowns.barrios = [];

            if (!this.filters.departamento) return;
            
            try {
                const res = await fetch(`../api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.filters.departamento)}`);
                const json = await res.json();
                if(json.success) {
                    // Asegurar valores únicos de municipios para evitar errores de x-for
                    this.dropdowns.municipios = [...new Set(json.data.map(m => (typeof m === 'object' ? m.municipio : m)))];
                }
            } catch(e) { console.error(e); }
            
            this.fetchMapaData();
        },

        async fetchTipos() {
            this.filters.tipo = '';
            this.filters.sector = '';
            this.filters.barrio = '';
            this.dropdowns.tipos = [];
            this.dropdowns.sectores = [];
            this.dropdowns.barrios = [];
            
            if (!this.filters.municipio) {
                this.fetchMapaData();
                return;
            }
            
            try {
                const res = await fetch(`../api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.filters.departamento)}&municipio=${encodeURIComponent(this.filters.municipio)}`);
                const json = await res.json();
                if(json.success) this.dropdowns.tipos = json.data;
            } catch(e) { console.error(e); }
            
            this.fetchMapaData();
        },
        
        async fetchSectores() {
            this.filters.sector = '';
            this.filters.barrio = '';
            this.dropdowns.sectores = [];
            this.dropdowns.barrios = [];
            
            if (!this.filters.tipo) {
                this.fetchMapaData();
                return;
            }
            
            try {
                const res = await fetch(`../api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.filters.departamento)}&municipio=${encodeURIComponent(this.filters.municipio)}&tipo_territorio=${encodeURIComponent(this.filters.tipo)}`);
                const json = await res.json();
                if(json.success) this.dropdowns.sectores = json.data;
            } catch(e) { console.error(e); }
            
            this.fetchMapaData();
        },

        async fetchBarrios() {
            this.filters.barrio = '';
            this.dropdowns.barrios = [];
            
            if (!this.filters.sector) {
                this.fetchMapaData();
                return;
            }
            
            try {
                const res = await fetch(`../api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.filters.departamento)}&municipio=${encodeURIComponent(this.filters.municipio)}&tipo_territorio=${encodeURIComponent(this.filters.tipo)}&territorio=${encodeURIComponent(this.filters.sector)}`);
                const json = await res.json();
                if(json.success) this.dropdowns.barrios = json.data;
            } catch(e) { console.error(e); }
            
            this.fetchMapaData();
        },
        
        resetFilters() {
            this.filters = { departamento: 'VALLE DEL CAUCA', municipio: '', tipo: '', sector: '', barrio: '', tipo_organizacion: '' };
            this.dropdowns.tipos = [];
            this.dropdowns.sectores = [];
            this.dropdowns.barrios = [];
            this.fetchMunicipios();
        },

        // ── CARGA DATOS MAPA (NUEVO ENDPOINT) ──
        async fetchMapaData() {
            if (!this.campanaId) return;
            this.loadingGrid = true;
            this.geoLayerGroup.clearLayers();
            this.markerGroup.clearLayers();
            
            let query = `../api/jac.php?action=mapa&campana_id=${this.campanaId}`;
            if (this.filters.municipio) query += `&municipio=${encodeURIComponent(this.filters.municipio)}`;
            if (this.filters.tipo)      query += `&tipo=${encodeURIComponent(this.filters.tipo)}`;
            if (this.filters.sector)    query += `&territorio=${encodeURIComponent(this.filters.sector)}`;
            if (this.filters.barrio)    query += `&barrio=${encodeURIComponent(this.filters.barrio)}`;
            if (this.filters.tipo_organizacion) query += `&tipo_organizacion=${encodeURIComponent(this.filters.tipo_organizacion)}`;
            
            try {
                const res = await fetch(query);
                const json = await res.json();
                
                if (json.success) {
                    this.mapData = json.data;
                    this.renderMapItems();
                    this.updateStats();
                } else {
                    console.error("Error from API:", json.error);
                }
            } catch (e) {
                console.error("Fetch error:", e);
            } finally {
                this.loadingGrid = false;
            }
        },
        
        renderMapItems() {
            let hasGeometries = false;
            
            this.mapData.forEach(item => {
                if (item.lat && item.lng && !isNaN(item.lat) && !isNaN(item.lng)) {
                    const firstJac = item.jacs && item.jacs.length > 0 ? item.jacs[0] : null;
                    if (!firstJac) return;

                    const color = this.getMarkerColorHexByType(firstJac.tipo_organizacion);
                    const totalJacs = item.jacs.length;
                    
                    // 1. Dibujar Polígono si existe
                    if (item.geom && item.geom.type) {
                        try {
                            let geojsonL = L.geoJSON(item.geom, {
                                style: {
                                    fillColor: color,
                                    weight: 2,
                                    opacity: 1,
                                    color: color,
                                    fillOpacity: 0.15,
                                    dashArray: '4'
                                }
                            });
                            this.geoLayerGroup.addLayer(geojsonL);
                            hasGeometries = true;
                        } catch(e) { console.warn("Invalid GeoJSON for", item.sector_nombre, e); }
                    }

                    // 2. Icono HTML personalizado (Burbuja premium con contador si hay > 1)
                    let htmlIcon = `
                        <div class="relative flex items-center justify-center w-9 h-9 shadow-lg rounded-full border-2 border-white cursor-pointer transition-transform hover:scale-110" style="background-color: ${color}">
                            <div class="w-1.5 h-1.5 rounded-full bg-white/50"></div>
                            ${totalJacs > 1 ? `<div class="absolute -top-1 -right-1 bg-white text-[10px] font-black px-1.5 py-0.5 rounded-full shadow-sm text-gray-800 border border-gray-100">${totalJacs}</div>` : ''}
                        </div>
                    `;
                    
                    let icon = L.divIcon({
                        html: htmlIcon,
                        className: 'custom-bubble',
                        iconSize: [36, 36],
                        iconAnchor: [18, 18],
                        popupAnchor: [0, -18]
                    });
                    
                    let marker = L.marker([parseFloat(item.lat), parseFloat(item.lng)], { icon: icon });
                    
                    // 3. Popup HTML Enriquecido (Mismo estilo que Mapa de Interes)
                    let planchaActivaHtml = '';
                    if (firstJac.plancha) {
                        planchaActivaHtml = `
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Plancha Activa</p>
                                    <p class="text-xs font-bold text-green-700">${firstJac.plancha.nombre_plancha}</p>
                                    <p class="text-[10px] text-gray-500 font-medium mt-0.5">${firstJac.plancha.miembros.length} miembros listados</p>
                                </div>
                            </div>
                        `;
                    } else {
                        planchaActivaHtml = `
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Plancha Activa</p>
                                    <p class="text-xs text-gray-400 italic">Sin plancha activa</p>
                                </div>
                            </div>
                        `;
                    }

                    let popupHtml = `
                        <div class="bg-white map-popup-custom m-0 shadow-xl overflow-hidden rounded-xl border border-gray-100" style="min-width:260px;">
                            <div class="p-4 pb-3" style="background-color: ${color}">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="text-white">
                                        <h4 class="font-black text-xs uppercase opacity-80 mb-0.5 tracking-wider">${item.sector_nombre || 'Ubicación'}</h4>
                                        <h3 class="font-black text-sm uppercase leading-tight">${firstJac.nombre}</h3>
                                    </div>
                                    <div class="bg-white/20 px-2 py-1 rounded text-center">
                                        <span class="block text-xs font-black text-white">${firstJac.tipo_organizacion}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4 space-y-3 bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Presidente / Líder</p>
                                        <p class="text-xs font-bold text-[#1e3a5f]">${firstJac.presidente || 'No asignado'}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Dirección</p>
                                        <p class="text-xs font-bold text-[#1e3a5f]">${firstJac.direccion || 'No registrada'}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Email</p>
                                        <p class="text-xs font-bold text-[#1e3a5f]">${firstJac.email || 'Sin correo'}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Observaciones</p>
                                        <p class="text-[10px] text-gray-500 italic leading-tight line-clamp-2">${firstJac.observaciones || 'Sin notas'}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-50">
                                    <div class="bg-blue-50/50 p-2 rounded-lg text-center">
                                        <span class="block text-sm font-black text-blue-600">${firstJac.meta_votos || 0}</span>
                                        <span class="block text-[8px] font-black text-blue-400 uppercase">Meta Votos</span>
                                    </div>
                                    <div class="bg-purple-50/50 p-2 rounded-lg text-center">
                                        <span class="block text-sm font-black text-purple-600">${firstJac.afiliados || 0}</span>
                                        <span class="block text-[8px] font-black text-purple-400 uppercase">Afiliados</span>
                                    </div>
                                </div>
                            </div>
                            ${planchaActivaHtml}
                        </div>
                    `;
                    
                    marker.bindPopup(popupHtml, {
                        className: 'map-popup-custom',
                        maxWidth: 280,
                        minWidth: 280
                    });
                    
                    this.markerGroup.addLayer(marker);
                }
            });
            
            // Auto-encuadrar el mapa si hay layers
            if (hasGeometries && this.geoLayerGroup.getLayers().length > 0) {
                this.mapInstance.fitBounds(this.geoLayerGroup.getBounds(), { padding: [30, 30] });
            } else if (this.markerGroup.getLayers().length > 0) {
                 this.mapInstance.fitBounds(L.featureGroup(this.markerGroup.getLayers()).getBounds(), { padding: [50, 50], maxZoom: 14 });
            } else {
                // Return to Valle del Cauca default if empty
                this.mapInstance.setView([3.6218, -76.3533], 9);
            }
        },
        
        updateStats() {
            let tJacs = 0;
            let tPlanchas = 0;
            this.mapData.forEach(r => {
                tJacs += r.jacs.length;
                r.jacs.forEach(j => {
                    if(j.plancha) tPlanchas++;
                });
            });
            this.mapStats.organizaciones = tJacs;
            this.mapStats.planchas = tPlanchas;
        },
        
        get sortedMapData() {
            // Sort por numero de JACs descendente
            return [...this.mapData].sort((a, b) => b.jacs.length - a.jacs.length);
        },
        
        getMarkerColorCssByType(tipo) {
            switch(tipo) {
                case 'JAC': return 'bg-[#3b82f6]'; // Blue
                case 'Deporte': return 'bg-[#f97316]'; // Orange
                case 'Cultura': return 'bg-[#ec4899]'; // Pink
                case 'Ambiente': return 'bg-[#22c55e]'; // Green
                case 'Social': return 'bg-[#8b5cf6]'; // Violet
                default: return 'bg-[#64748b]'; // Slate
            }
        },
        
        getMarkerColorClassByType(tipo) {
            return this.getMarkerColorCssByType(tipo);
        },
        
        getMarkerColorHexByType(tipo) {
            switch(tipo) {
                case 'JAC': return '#3b82f6';
                case 'Deporte': return '#f97316';
                case 'Cultura': return '#ec4899';
                case 'Ambiente': return '#22c55e';
                case 'Social': return '#8b5cf6';
                default: return '#64748b';
            }
        }

    }));
});
</script>
    <script>
        lucide.createIcons();
    </script>
<?php if (isset($isIncluded)): ?>
    </div> <!-- Cierra div x-data -->
<?php endif; ?>

<?php if (!isset($isIncluded)): ?>
</body>
</html>
<?php endif; ?>
