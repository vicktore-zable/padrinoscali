<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$user = getSessionUser();
$rol = $user['rol'] ?? '';
$rolesDiaD_Dashboard = ['supervisor_diad', 'admin_diad', 'supervisor', 'admin', 'super-admin'];

/*
if (!in_array($rol, $rolesDiaD_Dashboard)) {
    header('Location: /mod_diaD/index.php');
    exit;
}
*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_TITLE; ?> Dashboard - <?php echo CLIENT_NAME; ?></title>
    
    <!-- CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '<?php echo THEME_PRIMARY; ?>',
                        secondary: '<?php echo THEME_SECONDARY; ?>',
                        accent: '<?php echo THEME_ACCENT; ?>',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .gradient-top {
            background: <?php echo THEME_GRADIENT; ?>;
        }
        #map { height: 500px; width: 100%; border-radius: 1rem; z-index: 10; }
        [x-cloak] { display: none !important; }
        
        .card-stat {
            transition: all 0.3s ease;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="dashboardData()" x-init="initDashboard()">
    
    <!-- Header / Hero Section -->
    <div class="gradient-top text-white pb-32 pt-10 px-4 shadow-lg">
        <div class="container mx-auto max-w-7xl">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-3xl font-extrabold flex items-center gap-3">
                        <i data-lucide="bar-chart-2" class="w-8 h-8"></i>
                        <?php echo APP_TITLE; ?> Dashboard
                    </h1>
                    <p class="text-white/80 mt-1">Monitoreo territorial y recuento de votos en tiempo real</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="fetchData()" class="flex items-center gap-2 bg-white/20 hover:bg-white/30 transition-colors rounded-full px-5 py-2.5 text-sm font-bold backdrop-blur-md border border-white/20">
                        <i data-lucide="refresh-cw" class="w-4 h-4" :class="{'animate-spin': loading}"></i>
                        Actualizar Datos
                    </button>
                    <a href="/index.php" class="bg-white text-secondary hover:bg-gray-100 transition-colors rounded-full px-5 py-2.5 text-sm font-bold shadow-lg flex items-center gap-2">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto max-w-7xl px-4 -mt-20 mb-12 relative z-20">
        
        <!-- TOP FILTER BAR -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-black uppercase text-secondary tracking-tighter bg-secondary/10 px-3 py-1 rounded-full">Filtrar Territorio</span>
                    <select x-model="selectedMun" @change="updateDisplay()" class="bg-gray-50 border-none rounded-xl px-4 py-2 font-bold text-gray-700 outline-none focus:ring-2 focus:ring-secondary min-w-[200px] shadow-inner text-sm">
                        <option value="TODOS">Dpto. Valle del Cauca (Todos)</option>
                        <template x-for="m in municipiosStats" :key="m.nombre">
                            <option :value="m.nombre" x-text="m.nombre"></option>
                        </template>
                    </select>
                </div>
                <button @click="showMatrix = !showMatrix" 
                        class="flex items-center gap-2 px-6 py-2 rounded-xl font-bold text-sm transition-all shadow-md"
                        :class="showMatrix ? 'bg-secondary text-white' : 'bg-white text-secondary border border-secondary/20 hover:bg-secondary/5'">
                    <i data-lucide="grid" class="w-4 h-4"></i>
                    <span x-text="showMatrix ? 'Ver Mapa / Lista' : 'Ver Matriz Matriz'"></span>
                </button>
            </div>
        
        <!-- KPIs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- KPI 1 -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border-b-4 border-secondary card-stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="p-2 bg-blue-50 rounded-lg">
                        <i data-lucide="users" class="w-6 h-6 text-secondary"></i>
                    </span>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Acumulado</span>
                </div>
                <p class="text-sm font-medium text-gray-500">Total Votos Reportados</p>
                <p class="text-3xl font-black text-secondary mt-1" x-text="filteredKpis.total_votos">...</p>
            </div>

            <!-- KPI 2 -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border-b-4 border-primary card-stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="p-2 bg-emerald-50 rounded-lg">
                        <i data-lucide="clipboard-check" class="w-6 h-6 text-primary"></i>
                    </span>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cobertura</span>
                </div>
                <p class="text-sm font-medium text-gray-500">Mesas Reportadas</p>
                <p class="text-3xl font-black text-primary mt-1" x-text="filteredKpis.mesas_reportadas">...</p>
            </div>

            <!-- KPI 3 -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border-b-4 border-accent card-stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="p-2 bg-yellow-50 rounded-lg">
                        <i data-lucide="map-pin" class="w-6 h-6 text-yellow-600"></i>
                    </span>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Actividad</span>
                </div>
                <p class="text-sm font-medium text-gray-500">Puestos Activos</p>
                <p class="text-3xl font-black text-gray-800 mt-1" x-text="filteredKpis.puestos_activos">...</p>
            </div>

            <!-- KPI 4 -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border-b-4 border-red-500 card-stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="p-2 bg-red-50 rounded-lg">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>
                    </span>
                    <span class="text-xs font-bold text-red-400 uppercase tracking-wider">Alerta</span>
                </div>
                <p class="text-sm font-medium text-gray-500">Alertas Críticas</p>
                <p class="text-3xl font-black text-red-600 mt-1" x-text="alertas.filter(a => selectedMun === 'TODOS' || a.municipio === selectedMun).length">...</p>
            </div>

        </div>

        <!-- View Toggle Logic -->
        <div x-show="!showMatrix" x-transition class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Map Section -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i data-lucide="map" class="w-5 h-5 text-secondary"></i>
                            Vista Geográfica
                        </h2>
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">EN VIVO</span>
                    </div>
                    <div class="p-4">
                        <div id="map" class="shadow-inner"></div>
                    </div>
                </div>

                <!-- Detalle de Mesas Table -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                            <i data-lucide="list" class="w-5 h-5 text-primary"></i>
                            Detalle por Mesa
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Puesto / Mesa</th>
                                    <th class="px-6 py-4">Municipio</th>
                                    <th class="px-6 py-4 text-center">Último Incr.</th>
                                    <th class="px-6 py-4 text-center">Acumulado</th>
                                    <th class="px-6 py-4 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="m in filteredMesas" :key="m.id_puesto + '-' + m.id_mesa">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-gray-800" x-text="m.puesto_nombre"></p>
                                            <p class="text-[10px] text-gray-400" x-text="'Mesa #' + m.id_mesa"></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-md text-[10px] font-bold" x-text="m.municipio"></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-secondary font-bold" x-text="'+' + m.ultimo_reporte"></span>
                                        </td>
                                        <td class="px-6 py-4 text-center font-black text-gray-800 text-lg" x-text="m.votos_total"></td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="{
                                                'bg-green-100 text-green-600': m.estado_semaforo === 'VERDE',
                                                'bg-yellow-100 text-yellow-600': m.estado_semaforo === 'AMARILLO',
                                                'bg-red-100 text-red-600': m.estado_semaforo === 'ROJO'
                                            }" class="px-3 py-1 rounded-full text-[10px] font-black uppercase" x-text="m.estado_semaforo"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div x-show="filteredMesas.length === 0" class="p-12 text-center text-gray-400">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-4 opacity-20"></i>
                        <p class="font-bold">No hay reportes para esta selección</p>
                    </div>
                </div>
            </div>

            <!-- Alerts & List Section -->
            <div class="space-y-6">
                
                <!-- Critical Alerts Box -->
                <div class="bg-red-600 rounded-3xl shadow-xl p-6 text-white overflow-hidden relative" x-show="alertas.length > 0">
                    <div class="relative z-10">
                        <h3 class="text-lg font-bold flex items-center gap-2 mb-4">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            Atención Requerida
                        </h3>
                        <div class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            <template x-for="a in alertas" :key="a.id_mesa">
                                <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/20 flex justify-between items-center transition-all hover:bg-white/20">
                                    <div class="overflow-hidden">
                                        <p class="text-xs font-bold opacity-70 uppercase truncate" x-text="a.puesto_nombre"></p>
                                        <p class="font-bold text-sm">Mesa <span x-text="a.id_mesa"></span></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-black" x-text="a.votos_total"></p>
                                        <p class="text-[10px] uppercase font-bold opacity-70">Votos</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <!-- Background Icon Decoration -->
                    <i data-lucide="trending-down" class="absolute -right-10 -bottom-10 w-48 h-48 opacity-10 rotate-12"></i>
                </div>

                <!-- Municipios Summary List -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 mb-6">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between gradient-top">
                         <h3 class="text-xs font-bold text-white uppercase tracking-widest">Resumen Municipios</h3>
                    </div>
                    <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto custom-scrollbar">
                        <template x-for="m in municipiosStats" :key="m.nombre">
                            <div class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-secondary font-bold text-[10px]" x-text="m.nombre.substring(0,2).toUpperCase()"></div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm" x-text="m.nombre"></p>
                                        <p class="text-[10px] text-gray-400" x-text="m.puestos_activos + ' puestos reportando'"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-secondary" x-text="m.total_votos"></p>
                                    <p class="text-[9px] uppercase font-bold text-gray-400">Votos</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Puestos Summary List -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                         <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Detalle Puestos</h3>
                    </div>
                    <div class="divide-y divide-gray-50 max-h-[500px] overflow-y-auto custom-scrollbar">
                        <template x-for="p in filteredPuestos" :key="p.id">
                            <div class="p-4 hover:bg-primary/5 transition-colors flex items-center justify-between group">
                                <div class="flex items-center gap-3 w-full overflow-hidden">
                                    <div class="w-1.5 h-8 rounded-full flex-shrink-0" :class="p.estado_general === 'ALERTA' ? 'bg-red-500' : 'bg-primary'"></div>
                                    <div class="overflow-hidden">
                                        <p class="font-bold text-gray-800 text-xs truncate group-hover:text-secondary transition-colors" x-text="p.nombre"></p>
                                        <p class="text-[10px] text-gray-400 truncate" x-text="p.municipio + ' • ' + p.mesas_activas + ' mesas'"></p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <p class="font-black text-gray-700 text-sm" x-text="p.total_votos"></p>
                                    <p class="text-[8px] uppercase font-bold text-gray-400">Total</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>

        <!-- MATRIX VIEW -->
        <div x-show="showMatrix" x-transition class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-100 flex items-center justify-between gradient-top text-white">
                <div>
                    <h3 class="text-2xl font-black">Matriz Territorial de Cobertura</h3>
                    <p class="text-white/70 text-sm">Eje X: Puestos de Votación • Eje Y: Mesas</p>
                </div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg border border-white/20">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-[10px] font-bold uppercase">Meta OK</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg border border-white/20">
                        <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                        <span class="text-[10px] font-bold uppercase">Meta Baja</span>
                    </div>
                </div>
            </div>
            
            <div class="overflow-auto max-h-[70vh] custom-scrollbar">
                <table class="border-collapse w-full">
                    <thead>
                        <tr class="bg-gray-50 sticky top-0 z-30">
                            <th class="p-4 border-b border-r bg-gray-100 sticky left-0 z-40 min-w-[100px] text-center text-[10px] font-black text-gray-400 uppercase">Mesa</th>
                            <template x-for="p in filteredPuestos" :key="p.id">
                                <th class="p-4 border-b border-r min-w-[120px] text-center bg-gray-50">
                                    <div class="vertical-text mx-auto font-black text-[9px] text-gray-600 uppercase tracking-tighter" x-text="p.nombre"></div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="mesaNum in matrixMesas" :key="mesaNum">
                            <tr>
                                <td class="p-4 border-b border-r bg-gray-100 sticky left-0 z-20 text-center font-black text-secondary" x-text="'#' + mesaNum"></td>
                                <template x-for="p in filteredPuestos" :key="p.id">
                                    <td class="p-2 border-b border-r text-center transition-all hover:scale-110">
                                        <template x-if="getMesaStatus(p.id, mesaNum)">
                                            <div :class="{
                                                'bg-green-500 shadow-green-200': getMesaStatus(p.id, mesaNum) === 'VERDE',
                                                'bg-yellow-400 shadow-yellow-200': getMesaStatus(p.id, mesaNum) === 'AMARILLO',
                                                'bg-red-500 shadow-red-200 animate-pulse': getMesaStatus(p.id, mesaNum) === 'ROJO'
                                            }" class="w-10 h-10 rounded-lg mx-auto shadow-lg flex flex-col items-center justify-center text-white" 
                                               :title="'Mesa ' + mesaNum + ' - ' + p.nombre">
                                               <span class="text-[11px] font-black" x-text="getMesaVotes(p.id, mesaNum)"></span>
                                               <span class="text-[7px] opacity-70 uppercase font-bold" x-text="'incr: +' + getMesaLastIncr(p.id, mesaNum)"></span>
                                            </div>
                                        </template>
                                        <template x-if="!getMesaStatus(p.id, mesaNum)">
                                            <div class="w-8 h-8 rounded-lg mx-auto bg-gray-50 border border-dashed border-gray-200"></div>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div x-show="filteredPuestos.length === 0" class="p-20 text-center text-gray-300">
                <p class="font-black text-xl">Selecciona un municipio para visualizar la matriz</p>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="py-12 text-center relative z-20">
        <p class="text-[10px] font-black uppercase text-slate-400 tracking-[0.4em] mb-4">Aratio Intelligent Systems - Election Control</p>
    </footer>

    <script>
        lucide.createIcons();

        function dashboardData() {
            return {
                loading: false,
                map: null,
                markers: [],
                kpis: {
                    total_votos: 0,
                    mesas_reportadas: 0,
                    puestos_activos: 0
                },
                alertas: [],
                puestosStats: [],
                municipiosStats: [],
                mesasStats: [],
                selectedMun: 'TODOS',
                showMatrix: false,
                
                initDashboard() {
                    // Init Map over Yumbo, Valle del Cauca (3.585, -76.495)
                    this.map = L.map('map', {
                        scrollWheelZoom: false
                    }).setView([3.585, -76.495], 13);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://carto.com/">Carto</a>'
                    }).addTo(this.map);
                    
                    this.fetchData();
                    
                    // Simple polling every 30s
                    setInterval(() => this.fetchData(), 30000);
                },
                
                async fetchData() {
                    this.loading = true;
                    try {
                        const res = await fetch('/api/diaD_datos.php?action=dashboard');
                        const data = await res.json();
                        
                        if (data.success) {
                            this.kpis = data.kpis;
                            this.alertas = data.alertas || [];
                            this.puestosStats = data.puestos_stats || [];
                            this.mesasStats = data.mesas_stats || [];
                            this.municipiosStats = (data.municipios_stats || []).sort((a,b) => b.total_votos - a.total_votos);
                            this.updateDisplay();
                        }
                    } catch (e) {
                        console.error("Error fetching dashboard data", e);
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => lucide.createIcons());
                    }
                },

                updateDisplay() {
                    this.updateMapMarkers(this.filteredPuestos);
                },

                get filteredPuestos() {
                    if (this.selectedMun === 'TODOS') return this.puestosStats;
                    return this.puestosStats.filter(p => p.municipio === this.selectedMun);
                },

                get filteredMesas() {
                    if (this.selectedMun === 'TODOS') return this.mesasStats;
                    return this.mesasStats.filter(m => m.municipio === this.selectedMun);
                },

                get filteredKpis() {
                    if (this.selectedMun === 'TODOS') return this.kpis;
                    
                    const fm = this.filteredMesas;
                    return {
                        total_votos: fm.reduce((sum, m) => sum + parseInt(m.votos_total), 0),
                        mesas_reportadas: fm.length,
                        puestos_activos: new Set(fm.map(m => m.id_puesto)).size
                    };
                },

                get matrixMesas() {
                    const mesas = this.filteredMesas.map(m => parseInt(m.id_mesa));
                    if (mesas.length === 0) return [];
                    const maxMesa = Math.max(...mesas);
                    return Array.from({length: maxMesa}, (_, i) => i + 1);
                },

                getMesaStatus(puestoId, mesaNum) {
                    const m = this.mesasStats.find(m => m.id_puesto == puestoId && m.id_mesa == mesaNum);
                    return m ? m.estado_semaforo : null;
                },

                getMesaVotes(puestoId, mesaNum) {
                    const m = this.mesasStats.find(m => m.id_puesto == puestoId && m.id_mesa == mesaNum);
                    return m ? m.votos_total : '';
                },

                getMesaLastIncr(puestoId, mesaNum) {
                    const m = this.mesasStats.find(m => m.id_puesto == puestoId && m.id_mesa == mesaNum);
                    return m ? m.ultimo_reporte : '0';
                },
                
                updateMapMarkers(puestosStats) {
                    // Clear old markers
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.markers = [];
                    
                    if (puestosStats.length === 0) return;

                    const bounds = L.latLngBounds();
                    let hasValidCoords = false;

                    puestosStats.forEach(p => {
                        if (!p.latitud || !p.longitud) return;
                        hasValidCoords = true;
                        
                        const latLng = [parseFloat(p.latitud), parseFloat(p.longitud)];
                        bounds.extend(latLng);

                        // Color Logic
                        let markerColor = '#26A69A'; // Aguamarina (Default OK)
                        if (p.estado_general === 'ALERTA') markerColor = '#ef4444'; // Red
                        
                        // Custom Marker Icon with pulsing animation
                        const iconHtml = `
                            <div class="relative flex items-center justify-center">
                                <div class="absolute w-8 h-8 rounded-full opacity-40 animate-ping" style="background-color: ${markerColor}"></div>
                                <div class="relative w-6 h-6 rounded-full border-2 border-white shadow-xl flex items-center justify-center" style="background-color: ${markerColor}">
                                    <span class="text-[8px] font-bold text-white">${p.total_votos}</span>
                                </div>
                            </div>
                        `;
                        
                        const customIcon = L.divIcon({
                            html: iconHtml,
                            className: 'custom-marker',
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
                        });
                        
                        const popupContent = `
                            <div class="p-2 min-w-[150px]">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mb-1">${p.municipio}</div>
                                <h4 class="font-bold border-b border-gray-100 pb-1 mb-2 text-gray-800 text-sm">${p.nombre}</h4>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs text-gray-500">Votos Totales</span>
                                    <span class="font-black text-secondary text-base">${p.total_votos}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Mesas Reportadas</span>
                                    <span class="font-bold text-gray-700">${p.mesas_activas}</span>
                                </div>
                                <div class="mt-2 text-[9px] text-center text-gray-400 italic">Clic para ver detalle en tablas</div>
                            </div>
                        `;
                        
                        const marker = L.marker(latLng, { icon: customIcon })
                            .bindPopup(popupContent, {
                                closeButton: false,
                                className: 'premium-popup'
                            })
                            .addTo(this.map);
                            
                        this.markers.push(marker);
                    });

                    if (hasValidCoords) {
                        this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                    }
                }
            }
        }
    </script>
</body>
</html>
