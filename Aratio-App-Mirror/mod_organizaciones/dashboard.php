<?php
/**
 * mod_organizaciones - Dashboard Público (Versión PRO 100%)
 * Organizaciones y Entidades — Valle del Cauca
 * Acceso SIN autenticación requerida
 */
require_once __DIR__ . '/../config/config.php';

$campanaId = intval($_GET['campana_id'] ?? ($_SESSION['campana_activa'] ?? 0));

// Obtener datos iniciales
$campana  = null;
$stats    = ['total' => 0, 'activas' => 0, 'total_votos' => 0, 'total_afiliados' => 0, 'territorios_cubiertos' => 0, 'municipios_cubiertos' => 0];

if ($campanaId) {
    try {
        $db = getDB();
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

    } catch (Exception $e) { $error = $e->getMessage(); }
}

$campanaNombre = $campana['nombre'] ?? 'Aratio Pro';
$campanaSlogan = $campana['slogan'] ?? 'Inteligencia Territorial';

if (isset($isIncluded)) {
    ?>
    <style>
        .premium-bg { background: radial-gradient(circle at top right, #1e3a5f, #0f172a); }
        .glass-panel { background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid rgba(212, 175, 55, 0.2); }
        .pulse-live { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
        #map { height: 600px; border-radius: 1.5rem; transition: all 0.5s ease; }
        .map-fullscreen { position: fixed !important; top: 0; left: 0; width: 100% !important; height: 100% !important; z-index: 9999; border-radius: 0 !important; }
        .search-input:focus { box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1); }
    </style>
    <div x-data="organizacionesDashboard()">
    <?php
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pro — Entidades Georreferenciadas</title>
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
    <!-- Librerías de Mapas -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet-measure@3.1.0/dist/leaflet-measure.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.css" />

    <!-- Carga secuencial obligatoria -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-measure@3.1.0/dist/leaflet-measure.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.js" charset="utf-8"></script>

    <style>
    /* Personalización de herramientas espaciales */
    .leaflet-control-measure {
        border: none !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
        border-radius: 12px !important;
    }
    .leaflet-control-measure .leaflet-control-measure-toggle {
        border-radius: 12px !important;
        width: 34px !important;
        height: 34px !important;
        background-size: 16px 16px !important;
    }
    </style>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap');
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .hero-premium { background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%); }
        .glass-panel { background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid rgba(212, 175, 55, 0.1); box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.05); }
        .pulse-live { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
        #map { height: 650px; border-radius: 1.5rem; border: 1px solid rgba(212, 175, 55, 0.1); }
        .map-fullscreen { position: fixed !important; top: 0; left: 0; width: 100% !important; height: 100% !important; z-index: 9999; border-radius: 0 !important; }
        [x-cloak] { display: none !important; }
        .custom-popup .leaflet-popup-content-wrapper { border-radius: 1.25rem; padding: 0; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .custom-popup .leaflet-popup-content { margin: 0; width: 320px !important; }
    </style>
</head>
<body x-data="organizacionesDashboard()">
<?php } ?>

<!-- Header / Hero Section -->
<div class="hero-premium text-white pb-40 pt-12 overflow-hidden relative">
    <!-- Decorative Elements -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-secondary/10 rounded-full blur-[100px] -mr-20 -mt-20"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-accent/10 rounded-full blur-[80px] -ml-20 -mb-20"></div>
    
    <div class="w-full relative z-10 px-6 sm:px-10">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="space-y-6">
                <!-- Top Nav / Back -->
                <?php if (!isset($isIncluded)): ?>
                <a href="<?= url('?page=dashboard') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 rounded-full text-sm font-bold transition-all border border-white/10 group">
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
                    Panel de Control
                </a>
                <?php endif; ?>

                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-secondary to-orange-400 rounded-3xl flex items-center justify-center shadow-2xl transform shadow-secondary/20">
                        <i data-lucide="map-pin" class="w-10 h-10 text-primary"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="flex items-center gap-2 px-2.5 py-1 bg-green-500/20 text-green-400 rounded-full text-[10px] font-black uppercase tracking-wider">
                                <span class="pulse-live"></span>
                                Transmisión en Vivo
                            </span>
                            <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest" x-text="currentTime"></span>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-black tracking-tighter leading-none mb-1">
                            Aratio Pro — <span class="text-secondary italic">Entidades</span>
                        </h1>
                        <p class="text-white/50 text-lg font-medium leading-tight">
                            <?= htmlspecialchars($campanaSlogan) ?> — <?= htmlspecialchars($campanaNombre) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="flex flex-wrap gap-4 lg:gap-8">
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl min-w-[160px] relative overflow-hidden group hover:border-secondary/30 transition-all">
                    <div class="absolute -right-2 -bottom-2 opacity-5 scale-150 rotate-12 group-hover:rotate-0 transition-transform">
                        <i data-lucide="building-2" class="w-20 h-20"></i>
                    </div>
                    <p class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Total Entidades</p>
                    <p class="text-4xl font-black text-white leading-none" x-text="formatNum(<?= $stats['total'] ?>)"></p>
                </div>
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl min-w-[160px] relative overflow-hidden group hover:border-secondary/30 transition-all">
                    <div class="absolute -right-2 -bottom-2 opacity-5 scale-150 rotate-12 group-hover:rotate-0 transition-transform">
                        <i data-lucide="users" class="w-20 h-20"></i>
                    </div>
                    <p class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Impacto (Votos)</p>
                    <p class="text-4xl font-black text-secondary leading-none" x-text="formatNum(<?= $stats['total_votos'] ?>)"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Section -->
<div class="w-full px-4 sm:px-6 -mt-24 relative z-20 pb-32">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Sidebar Controls -->
        <div class="lg:col-span-3 space-y-6">
            <div class="glass-panel p-6 rounded-[2rem] border border-gray-100 shadow-xl overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16"></div>
                
                <div class="flex items-center justify-between mb-8">
                    <h3 class="font-black text-primary uppercase text-xs tracking-widest">Navegador Territorial</h3>
                    <button @click="resetFilters" class="p-2 hover:bg-gray-100 rounded-xl transition-colors" title="Reiniciar">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-gray-400"></i>
                    </button>
                </div>

                <div class="space-y-5">
                    <!-- Filters Grid -->
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 mb-1.5 block">Municipio</label>
                            <select x-model="filters.municipio" @change="fetchTipos" class="w-full bg-slate-50 border border-slate-200 text-primary font-bold text-sm rounded-2xl p-4 outline-none focus:ring-4 focus:ring-secondary/10 transition-all appearance-none cursor-pointer">
                                <option value="">Todos los Municipios</option>
                                <template x-for="m in dropdowns.municipios" :key="m">
                                    <option :value="m" x-text="m"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 mb-1.5 block">Tipo Territorio</label>
                            <select x-model="filters.tipo_territorio" @change="fetchSectores" :disabled="!filters.municipio" class="w-full bg-slate-50 border border-slate-200 text-primary font-bold text-sm rounded-2xl p-4 outline-none focus:ring-4 focus:ring-secondary/10 transition-all appearance-none cursor-pointer disabled:opacity-50">
                                <option value="">Todos</option>
                                <template x-for="t in dropdowns.tipos" :key="t">
                                    <option :value="t" x-text="t"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 mb-1.5 block">Sector / Zona</label>
                            <select x-model="filters.sector" @change="fetchBarrios" :disabled="!filters.tipo_territorio" class="w-full bg-slate-50 border border-slate-200 text-primary font-bold text-sm rounded-2xl p-4 outline-none focus:ring-4 focus:ring-secondary/10 transition-all appearance-none cursor-pointer disabled:opacity-50">
                                <option value="">Zonas (Todas)</option>
                                <template x-for="s in dropdowns.sectores" :key="s">
                                    <option :value="s" x-text="s"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 mb-1.5 block">Barrio / Vereda</label>
                            <select x-model="filters.barrio" @change="fetchMapaData" :disabled="!filters.sector" class="w-full bg-slate-50 border border-slate-200 text-primary font-bold text-sm rounded-2xl p-4 outline-none focus:ring-4 focus:ring-secondary/10 transition-all appearance-none cursor-pointer disabled:opacity-50">
                                <option value="">Todos los barrios</option>
                                <template x-for="b in dropdowns.barrios" :key="b">
                                    <option :value="b" x-text="b"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-primary/60 uppercase tracking-widest ml-1 mb-1.5 block">Categoría Entidad</label>
                            <div class="grid grid-cols-2 gap-2">
                                <template x-for="cat in categories" :key="cat.id">
                                    <button 
                                        @click="filters.tipo_organizacion = filters.tipo_organizacion === cat.id ? '' : cat.id; fetchMapaData()"
                                        :class="filters.tipo_organizacion === cat.id ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20' : 'bg-white text-gray-600 border-gray-100 hover:border-secondary'"
                                        class="flex flex-col items-center justify-center p-3 rounded-2xl border transition-all gap-1.5"
                                    >
                                        <i :data-lucide="cat.icon" class="w-4 h-4"></i>
                                        <span class="text-[9px] font-black uppercase" x-text="cat.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <div class="bg-gradient-to-br from-primary to-slate-900 rounded-3xl p-5 text-white shadow-2xl relative overflow-hidden group mb-4">
                            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] font-black uppercase tracking-widest opacity-60">Resultados En Pantalla</span>
                                <i data-lucide="radar" class="w-4 h-4 text-secondary pulse-live"></i>
                            </div>
                            <div class="flex items-end gap-3">
                                <span class="text-4xl font-black text-secondary leading-none" x-text="mapStats.organizaciones">0</span>
                                <span class="text-xs font-bold opacity-60 mb-1">Registros</span>
                            </div>
                        </div>
                        
                        <!-- Conteo por categoría -->
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="cat in categories" :key="cat.id">
                                <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-between border border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <i :data-lucide="cat.icon" class="w-3.5 h-3.5 text-gray-400"></i>
                                        <span class="text-[9px] font-bold text-gray-500 uppercase" x-text="cat.name"></span>
                                    </div>
                                    <span class="text-xs font-black text-primary" x-text="mapStats.byCategory[cat.id] || 0"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Area -->
        <div class="lg:col-span-9 space-y-6">
            <div class="glass-panel p-2 rounded-[2.5rem] shadow-2xl relative overflow-hidden group">
                <!-- Overlays for Map Control -->
                <div class="absolute top-6 left-6 z-30 flex flex-col gap-2">
                    <button @click="toggleFullScreen" class="w-12 h-12 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100 flex items-center justify-center hover:bg-white transition-all text-primary active:scale-90">
                        <i :data-lucide="isFullscreen ? 'minimize-2' : 'maximize-2'" class="w-5 h-5"></i>
                    </button>
                    <button @click="resetMapView" class="w-12 h-12 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100 flex items-center justify-center hover:bg-white transition-all text-primary active:scale-90">
                        <i data-lucide="navigation" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <div x-show="loadingGrid" x-transition class="absolute inset-0 z-50 bg-white/80 backdrop-blur-xl rounded-[2.5rem] flex flex-col items-center justify-center">
                    <div class="relative">
                        <div class="w-16 h-16 border-4 border-slate-100 border-t-secondary rounded-full animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-2 h-2 bg-secondary rounded-full animate-ping"></div>
                        </div>
                    </div>
                    <p class="mt-6 font-black text-primary uppercase text-[10px] tracking-widest animate-pulse">Consultando Satélite Aratio...</p>
                </div>

                <div id="map" :class="isFullscreen ? 'map-fullscreen' : ''"></div>
                
                <!-- Bottom Floating Labels -->
                <div class="absolute bottom-6 left-6 z-20 flex gap-3 pointer-events-none">
                    <div class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-full shadow-lg border border-gray-100 flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        <span class="text-[10px] font-black text-gray-700 uppercase">Juntas de Acción</span>
                    </div>
                    <div class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-full shadow-lg border border-gray-100 flex items-center gap-2">
                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                        <span class="text-[10px] font-black text-gray-700 uppercase">Deportivos</span>
                    </div>
                </div>
            </div>

            <!-- Detailed Table List -->
            <div class="glass-panel rounded-[2rem] shadow-xl overflow-hidden animate-fadeIn">
                <div class="p-8 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white">
                    <div class="space-y-1">
                        <h3 class="text-xl font-black text-primary tracking-tighter">Inventario Detallado</h3>
                        <p class="text-sm font-bold text-gray-400">Listado georreferenciado por sector y liderazgo.</p>
                    </div>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-secondary transition-colors">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>
                        <input 
                            x-model="searchTerm" 
                            type="text" 
                            placeholder="Buscar por organización o líder..." 
                            class="pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl w-full md:w-80 outline-none focus:ring-4 focus:ring-secondary/10 focus:border-secondary transition-all font-bold text-sm search-input"
                        >
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="text-left px-8 py-5 text-[10px] font-black text-primary/40 uppercase tracking-widest">Ubicación</th>
                                <th class="text-left px-5 py-5 text-[10px] font-black text-primary/40 uppercase tracking-widest">Entidad / Organización</th>
                                <th class="text-left px-5 py-5 text-[10px] font-black text-primary/40 uppercase tracking-widest">Liderazgo</th>
                                <th class="text-right px-8 py-5 text-[10px] font-black text-primary/40 uppercase tracking-widest">Potencial</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="item in filteredMapData" :key="item.sector_nombre + item.municipio">
                                <tr class="group hover:bg-slate-50/80 transition-all cursor-pointer" @click="focusOnItem(item)">
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-primary" x-text="item.municipio"></span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase" x-text="item.sector_nombre"></span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 shadow-sm" :style="'background-color: ' + getMarkerColorHexByType(item.jacs[0].tipo_organizacion) + '20'">
                                                <i :data-lucide="getCategoryIcon(item.jacs[0].tipo_organizacion)" class="w-5 h-5" :style="'color: ' + getMarkerColorHexByType(item.jacs[0].tipo_organizacion)"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-black text-primary group-hover:text-secondary transition-colors" x-text="item.jacs[0].nombre"></span>
                                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter" x-text="item.jacs[0].tipo_organizacion"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-5">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-700" x-text="item.jacs[0].presidente || 'Líder no asignado'"></span>
                                            <div class="flex items-center gap-3 mt-1">
                                                <template x-if="item.jacs[0].plancha">
                                                    <span class="flex items-center gap-1.5 px-2 py-0.5 bg-green-50 text-green-600 rounded-md text-[9px] font-black border border-green-100 uppercase">
                                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                        Plancha Activa
                                                    </span>
                                                </template>
                                                <template x-if="!item.jacs[0].plancha">
                                                    <span class="text-[9px] font-bold text-gray-400 italic">Sin estructura electoral</span>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex flex-col items-end">
                                            <span class="text-lg font-black text-primary" x-text="formatNum(item.jacs[0].meta_votos)"></span>
                                            <div class="w-16 h-1.5 bg-slate-100 rounded-full mt-1.5 overflow-hidden">
                                                <div class="h-full bg-secondary transition-all duration-1000" :style="'width: ' + Math.min(100, (item.jacs[0].meta_votos/500)*100) + '%'"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="filteredMapData.length === 0" class="p-20 text-center space-y-4 bg-white">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto">
                            <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
                        </div>
                        <h4 class="font-black text-primary uppercase text-sm tracking-widest">No se encontraron registros</h4>
                        <p class="text-gray-400 text-sm font-medium">Intenta ajustar los filtros o el término de búsqueda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    const API_BASE = '<?= url('api/') ?>';
    Alpine.data('organizacionesDashboard', () => ({
        campanaId: <?= $campanaId ?>,
        loadingGrid: true,
        mapData: [],
        searchTerm: '',
        currentTime: '',
        isFullscreen: false,
        initialBoundsSet: false,
        
        mapInstance: null,
        markerGroup: null,
        geoLayerGroup: null,

        categories: [
            { id: 'JAC', name: 'Juntas', icon: 'home' },
            { id: 'Deporte', name: 'Deporte', icon: 'trophy' },
            { id: 'Cultura', name: 'Cultura', icon: 'palette' },
            { id: 'Ambiente', name: 'Ambiente', icon: 'leaf' },
            { id: 'Social', name: 'Social', icon: 'heart' },
            { id: 'Otro', name: 'Otros', icon: 'hash' }
        ],

        filters: {
            departamento: 'VALLE DEL CAUCA',
            municipio: '',
            tipo_territorio: '',
            sector: '',
            barrio: '',
            tipo_organizacion: ''
        },
        
        dropdowns: { municipios: [], tipos: [], sectores: [], barrios: [] },
        mapStats: { organizaciones: 0, byCategory: {} },
        visibleData: [],
        totalCircle: null,

        init() {
            this.initMap();
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
            
            this.fetchMunicipios();
            this.fetchMapaData();
            
            this.$watch('loadingGrid', () => this.$nextTick(() => lucide.createIcons()));
            this.$watch('searchTerm', () => this.$nextTick(() => lucide.createIcons()));
        },

        updateTime() {
            this.currentTime = new Intl.DateTimeFormat('es-CO', { 
                hour: '2-digit', minute: '2-digit', second: '2-digit' 
            }).format(new Date());
        },

        formatNum(n) { return new Intl.NumberFormat('es-CO').format(n); },

        initMap() {
            // Mapas Base
            const light = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 19 });
            const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
            const dark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 19 });

            this.mapInstance = L.map('map', { 
                zoomControl: false, 
                scrollWheelZoom: false,
                attributionControl: false,
                layers: [light] // Capa inicial
            }).setView([3.6218, -76.3533], 9);
            
            this.markerGroup = L.layerGroup().addTo(this.mapInstance);
            this.geoLayerGroup = L.featureGroup().addTo(this.mapInstance);

            // Control de Capas
            const baseMaps = {
                "<span class='text-xs font-bold text-slate-600'>Mapa Claro</span>": light,
                "<span class='text-xs font-bold text-slate-600'>Satélite</span>": satellite,
                "<span class='text-xs font-bold text-slate-600'>Modo Oscuro</span>": dark
            };

            const overlays = {
                "<span class='text-xs font-bold text-primary'>Entidades (Puntos)</span>": this.markerGroup,
                "<span class='text-xs font-bold text-primary'>Polígonos Territoriales</span>": this.geoLayerGroup
            };

            L.control.layers(baseMaps, overlays, { 
                position: 'topright',
                collapsed: true 
            }).addTo(this.mapInstance);

            // 1. Herramienta de Localización (GPS)
            L.control.locate({
                position: 'topleft',
                strings: { title: "Mostrar mi ubicación" },
                flyTo: true,
                keepCurrentZoomLevel: false,
                circleStyle: { color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.15 },
                markerStyle: { color: '#3b82f6', fillColor: '#3b82f6' }
            }).addTo(this.mapInstance);

            // 2. Herramienta de Medición (Regla)
            const measureControl = new L.Control.Measure({
                position: 'topleft',
                primaryLengthUnit: 'meters',
                secondaryLengthUnit: 'kilometers',
                primaryAreaUnit: 'sqmeters',
                secondaryAreaUnit: 'hectares',
                activeColor: '#f97316',
                completedColor: '#ea580c',
                localization: 'es'
            });
            measureControl.addTo(this.mapInstance);

            // 3. Escala
            L.control.scale({ imperial: false, position: 'bottomright' }).addTo(this.mapInstance);

            // 4. Control de Zoom
            L.control.zoom({ position: 'topleft' }).addTo(this.mapInstance);
            
            this.mapInstance.on('moveend', () => {
                this.updateVisibleStats();
            });
        },

        resetMapView() {
            this.mapInstance.setView([3.6218, -76.3533], 9);
        },

        toggleFullScreen() {
            this.isFullscreen = !this.isFullscreen;
            setTimeout(() => this.mapInstance.invalidateSize(), 500);
        },

        async fetchMunicipios() {
            try {
                const res = await fetch(`${API_BASE}territorios.php?accion=municipios&departamento=${encodeURIComponent(this.filters.departamento)}`);
                const json = await res.json();
                if(json.success) this.dropdowns.municipios = [...new Set(json.data.map(m => typeof m === 'object' ? m.municipio : m))];
            } catch(e) { console.error(e); }
        },

        async fetchTipos() {
            this.filters.tipo_territorio = '';
            this.filters.sector = '';
            this.filters.barrio = '';
            this.dropdowns.tipos = [];
            this.dropdowns.sectores = [];
            this.dropdowns.barrios = [];
            this.fetchMapaData();
            if (!this.filters.municipio) return;
            try {
                const res = await fetch(`${API_BASE}territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.filters.departamento)}&municipio=${encodeURIComponent(this.filters.municipio)}`);
                const json = await res.json();
                if(json.success) this.dropdowns.tipos = json.data;
            } catch(e) { console.error(e); }
        },

        async fetchSectores() {
            this.filters.sector = '';
            this.filters.barrio = '';
            this.dropdowns.sectores = [];
            this.dropdowns.barrios = [];
            this.fetchMapaData();
            if (!this.filters.municipio || !this.filters.tipo_territorio) return;
            try {
                const res = await fetch(`${API_BASE}territorios.php?accion=territorios&departamento=${encodeURIComponent(this.filters.departamento)}&municipio=${encodeURIComponent(this.filters.municipio)}&tipo_territorio=${encodeURIComponent(this.filters.tipo_territorio)}`);
                const json = await res.json();
                if(json.success) this.dropdowns.sectores = json.data;
            } catch(e) { console.error(e); }
        },

        async fetchBarrios() {
            this.filters.barrio = '';
            this.dropdowns.barrios = [];
            this.fetchMapaData();
            if (!this.filters.municipio || !this.filters.tipo_territorio || !this.filters.sector) return;
            try {
                const res = await fetch(`${API_BASE}territorios.php?accion=barrios&departamento=${encodeURIComponent(this.filters.departamento)}&municipio=${encodeURIComponent(this.filters.municipio)}&tipo_territorio=${encodeURIComponent(this.filters.tipo_territorio)}&territorio=${encodeURIComponent(this.filters.sector)}`);
                const json = await res.json();
                if(json.success) this.dropdowns.barrios = json.data;
            } catch(e) { console.error(e); }
        },

        async fetchMapaData() {
            if (!this.campanaId) return;
            this.loadingGrid = true;
            
            let query = `${API_BASE}organizaciones.php?action=mapa&campana_id=${this.campanaId}`;
            if (this.filters.municipio) query += `&municipio=${encodeURIComponent(this.filters.municipio)}`;
            if (this.filters.tipo_territorio) query += `&tipo=${encodeURIComponent(this.filters.tipo_territorio)}`;
            if (this.filters.sector)    query += `&territorio=${encodeURIComponent(this.filters.sector)}`;
            if (this.filters.barrio)    query += `&barrio=${encodeURIComponent(this.filters.barrio)}`;
            if (this.filters.tipo_organizacion) query += `&tipo_organizacion=${encodeURIComponent(this.filters.tipo_organizacion)}`;
            
            try {
                const res = await fetch(query);
                const json = await res.json();
                if (json.success) {
                    this.mapData = json.data;
                    this.renderMapItems();
                    this.updateVisibleStats();
                }

                // Cargar capa de polígonos GeoJSON
                // Si no hay municipio seleccionado, intentamos cargar CALI por defecto para que el mapa no se vea vacío
                let municipioParaGeo = this.filters.municipio || 'CALI';
                
                let geoQuery = `api_territorios_geojson.php?municipio=${encodeURIComponent(municipioParaGeo)}`;
                if (this.filters.tipo_territorio) geoQuery += `&tipo=${encodeURIComponent(this.filters.tipo_territorio)}`;
                if (this.filters.sector) geoQuery += `&territorio=${encodeURIComponent(this.filters.sector)}`;
                if (this.filters.barrio) geoQuery += `&barrio=${encodeURIComponent(this.filters.barrio)}`;
                
                console.log("Cargando polígonos:", geoQuery);
                const geoRes = await fetch(geoQuery);
                const geoJson = await geoRes.json();
                console.log("Respuesta GeoJSON:", geoJson);
                
                this.geoLayerGroup.clearLayers();
                if (geoJson.features && geoJson.features.length > 0) {
                    const newLayer = L.geoJSON(geoJson, {
                        style: (feature) => {
                            const color = this.getColorByComuna(feature.properties.Territorio);
                            return {
                                fillColor: color,
                                weight: 1.5,      // Borde un poco más grueso
                                opacity: 1,
                                color: 'white',
                                dashArray: '4, 4', // Líneas punteadas más cortas para definición
                                fillOpacity: 0.45  // Un poco más de relleno para el contraste
                            };
                        },
                        onEachFeature: (feature, layer) => {
                            layer.bindPopup(`
                                <div class="p-4 font-sans min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-3 h-3 rounded-full" style="background-color: ${this.getColorByComuna(feature.properties.Territorio)}"></div>
                                        <h4 class="text-xs font-black text-primary uppercase tracking-tighter">${feature.properties.Territorio || 'Comuna'}</h4>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-800 uppercase leading-none mb-1">${feature.properties.barrio || 'Barrio'}</h3>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">${feature.properties.Municipio}</p>
                                    <hr class="my-3 border-gray-100">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-slate-50 p-2 rounded-lg">
                                            <span class="block text-[8px] font-black text-slate-400 uppercase">Tipo</span>
                                            <span class="text-[10px] font-black text-primary">${feature.properties.Tipo_territorio}</span>
                                        </div>
                                        <div class="bg-slate-50 p-2 rounded-lg text-right">
                                            <span class="block text-[8px] font-black text-slate-400 uppercase">ID</span>
                                            <span class="text-[10px] font-black text-primary">#${feature.properties.id}</span>
                                        </div>
                                    </div>
                                </div>
                            `);
                            layer.on('mouseover', function (e) {
                                this.setStyle({ fillOpacity: 0.7, weight: 2 });
                            });
                            layer.on('mouseout', function (e) {
                                this.setStyle({ fillOpacity: 0.4, weight: 1 });
                            });
                        }
                    });
                    this.geoLayerGroup.addLayer(newLayer);
                    
                    // Solo ajustamos bounds si el usuario cambió el filtro o si es la carga inicial
                    if (this.filters.municipio || (this.mapData.length === 0 && !this.initialBoundsSet)) {
                        this.mapInstance.fitBounds(newLayer.getBounds(), { padding: [40, 40], maxZoom: 16 });
                        this.initialBoundsSet = true;
                    }
                }

            } catch (e) { console.error(e); }
            finally { this.loadingGrid = false; }
        },

        renderMapItems() {
            this.markerGroup.clearLayers();
            
            this.mapData.forEach(item => {
                if (!item.lat || !item.lng) return;
                const firstJac = item.jacs[0];
                const color = this.getMarkerColorHexByType(firstJac.tipo_organizacion);
                
                const icon = L.divIcon({
                    html: `<div class="relative flex items-center justify-center w-10 h-10 shadow-2xl rounded-2xl border-4 border-white transition-all hover:scale-125 cursor-pointer shadow-primary/20" style="background-color: ${color}">
                             <div class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
                           </div>`,
                    className: 'custom-bubble',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });

                const marker = L.marker([item.lat, item.lng], { icon: icon });
                
                marker.bindPopup(`
                    <div class="custom-popup">
                        <div class="p-6" style="background-color: ${color}">
                            <p class="text-[10px] font-black text-white/60 mb-0.5 uppercase tracking-widest leading-none">${firstJac.tipo_organizacion}</p>
                            <h3 class="text-lg font-black text-white leading-tight">${firstJac.nombre}</h3>
                            <p class="text-[10px] font-bold text-white/80 uppercase mt-1 tracking-tighter">${item.municipio} — ${item.sector_nombre}</p>
                        </div>
                        <div class="p-5 space-y-4 bg-white">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-slate-50 flex items-center justify-center rounded-xl text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1">Líder</span>
                                    <span class="text-sm font-black text-primary">${firstJac.presidente}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-slate-50 flex items-center justify-center rounded-xl text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-400 uppercase leading-none mb-1">Dirección</span>
                                    <span class="text-sm font-bold text-slate-600">${firstJac.direccion || 'Sin registrar'}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <div class="bg-blue-50/50 p-2.5 rounded-xl border border-blue-100/50 text-center">
                                    <span class="block text-sm font-black text-blue-600">${firstJac.meta_votos}</span>
                                    <span class="text-[8px] font-black text-blue-400 uppercase">Potencial</span>
                                </div>
                                <div class="bg-emerald-50/50 p-2.5 rounded-xl border border-emerald-100/50 text-center">
                                    <span class="block text-sm font-black text-emerald-600">${firstJac.afiliados}</span>
                                    <span class="text-[8px] font-black text-emerald-400 uppercase">Afiliados</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `, { className: 'custom-popup' });
                
                this.markerGroup.addLayer(marker);
            });

            if (this.markerGroup.getLayers().length) {
                this.mapInstance.fitBounds(L.featureGroup(this.markerGroup.getLayers()).getBounds(), { padding: [50, 50], maxZoom: 16 });
            }
        },

        updateVisibleStats() {
            if (!this.mapInstance || !this.mapData) return;
            const bounds = this.mapInstance.getBounds();
            let count = 0;
            let countsByCategory = {};
            this.categories.forEach(c => countsByCategory[c.id] = 0);
            
            this.visibleData = this.mapData.filter(item => {
                if (!item.lat || !item.lng) return false;
                const isVisible = bounds.contains([item.lat, item.lng]);
                if (isVisible) {
                    item.jacs.forEach(jac => {
                        count++;
                        if (countsByCategory[jac.tipo_organizacion] !== undefined) {
                            countsByCategory[jac.tipo_organizacion]++;
                        } else {
                            countsByCategory['Otro'] = (countsByCategory['Otro'] || 0) + 1;
                        }
                    });
                }
                return isVisible;
            });
            
            this.mapStats.organizaciones = count;
            this.mapStats.byCategory = countsByCategory;
            
            if (this.totalCircle) {
                this.mapInstance.removeLayer(this.totalCircle);
            }
            if (count > 0) {
                const center = this.mapInstance.getCenter();
                const icon = L.divIcon({
                    html: `<div class="bg-secondary text-primary font-black rounded-full w-14 h-14 flex items-center justify-center border-4 border-white shadow-2xl text-lg">${count}</div>`,
                    className: 'total-circle',
                    iconSize: [56, 56],
                    iconAnchor: [28, 28]
                });
                this.totalCircle = L.marker(center, { icon: icon, interactive: false, zIndexOffset: 1000 }).addTo(this.mapInstance);
            }
        },

        focusOnItem(item) {
            this.mapInstance.flyTo([item.lat, item.lng], 16, { duration: 1.5 });
            this.markerGroup.eachLayer(layer => {
                if (layer.getLatLng().lat == item.lat && layer.getLatLng().lng == item.lng) {
                    layer.openPopup();
                }
            });
            window.scrollTo({ top: document.getElementById('map').offsetTop - 100, behavior: 'smooth' });
        },

        resetFilters() {
            this.filters = { departamento: 'VALLE DEL CAUCA', municipio: '', tipo_territorio: '', sector: '', barrio: '', tipo_organizacion: '' };
            this.searchTerm = '';
            this.dropdowns.tipos = [];
            this.dropdowns.sectores = [];
            this.dropdowns.barrios = [];
            this.fetchMapaData();
            this.resetMapView();
        },

        get filteredMapData() {
            let dataToFilter = this.visibleData || [];
            if (!this.searchTerm) return dataToFilter;
            if (this.searchTerm.length < 3) return dataToFilter;
            const term = this.searchTerm.toLowerCase();
            return dataToFilter.filter(item => 
                item.jacs[0].nombre.toLowerCase().includes(term) || 
                item.jacs[0].presidente.toLowerCase().includes(term) ||
                item.sector_nombre.toLowerCase().includes(term) ||
                item.municipio.toLowerCase().includes(term)
            );
        },

        getCategoryIcon(tipo) {
            const cat = this.categories.find(c => c.id === tipo);
            return cat ? cat.icon : 'hash';
        },

        getMarkerColorHexByType(tipo) {
            const colors = { JAC: '#3b82f6', Deporte: '#f97316', Cultura: '#ec4899', Ambiente: '#10b981', Social: '#8b5cf6', Otro: '#64748b' };
            return colors[tipo] || '#64748b';
        },

        getColorByComuna(comuna) {
            if (!comuna) return '#64748b';
            
            // Paleta de colores de alto contraste (Vibrantes)
            const palette = [
                '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', 
                '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe', 
                '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000', 
                '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
            ];
            
            // Generar un índice basado en el nombre de la comuna
            let hash = 0;
            for (let i = 0; i < comuna.length; i++) {
                hash = comuna.charCodeAt(i) + ((hash << 5) - hash);
            }
            const index = Math.abs(hash) % palette.length;
            return palette[index];
        }

    }));
});
</script>

<?php if (!isset($isIncluded)): ?>
</body>
</html>
<?php endif; ?>
