<?php
require_once __DIR__ . '/../config/config.php';
// requireAuth(); // Dashboard ahora público por petición del usuario

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
    <title>Dashboard Día D — Aratio Pro</title>
    
    <!-- CSS -->
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
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .gradient-top {
            background: linear-gradient(135deg, <?= COLOR_PRIMARY ?> 0%, <?= COLOR_SECONDARY ?> 100%);
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
        <div class="w-full px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-4xl font-black flex items-center gap-4 tracking-tighter">
                        <div class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20">
                            <i data-lucide="bar-chart-3" class="w-7 h-7 text-secondary"></i>
                        </div>
                        DÍA D — DASHBOARD
                    </h1>
                    <p class="text-white/70 mt-2 font-bold uppercase tracking-widest text-sm flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4 text-secondary"></i>
                        Monitoreo Electoral de Precisión
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="fetchData()" class="flex items-center gap-2 bg-white/20 hover:bg-white/30 transition-colors rounded-full px-5 py-2.5 text-sm font-bold backdrop-blur-md border border-white/20">
                        <i data-lucide="refresh-cw" class="w-4 h-4" :class="{'animate-spin': loading}"></i>
                        Actualizar Datos
                    </button>
                    <a href="<?= url('/') ?>" class="bg-white text-secondary hover:bg-gray-100 transition-colors rounded-full px-5 py-2.5 text-sm font-bold shadow-lg flex items-center gap-2">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full px-6 -mt-20 mb-12 relative z-20">
        
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
                    <span x-text="showMatrix ? 'Ver Mapa / Lista' : 'Ver Matriz de Cobertura'"></span>
                </button>
            </div>
        
        <!-- KPIs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 uppercase">
            
            <!-- KPI 1: VOTOS -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border-b-4 border-secondary card-stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="p-2 bg-blue-50 rounded-lg">
                        <i data-lucide="check-circle-2" class="w-6 h-6 text-secondary"></i>
                    </span>
                    <span class="text-[10px] font-black text-gray-400 tracking-widest leading-none">Votación Total</span>
                </div>
                <p class="text-sm font-black text-gray-500">Votos Reportados</p>
                <p class="text-4xl font-black text-secondary mt-1 tracking-tighter" x-text="formatNumber(filteredKpis.total_votos)">...</p>
            </div>

            <!-- KPI 2: PUESTOS -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border-b-4 border-primary card-stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="p-2 bg-emerald-50 rounded-lg">
                        <i data-lucide="map-pin" class="w-6 h-6 text-primary"></i>
                    </span>
                    <span class="text-[10px] font-black text-gray-400 tracking-widest leading-none">Cobertura Puestos</span>
                </div>
                <p class="text-sm font-black text-gray-500">Puestos con Reporte</p>
                <p class="text-4xl font-black text-primary mt-1 tracking-tighter" x-text="filteredKpis.puestos_activos">...</p>
            </div>

            <!-- KPI 3: MESAS -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border-b-4 border-accent card-stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="p-2 bg-yellow-50 rounded-lg">
                        <i data-lucide="clipboard-list" class="w-6 h-6 text-yellow-600"></i>
                    </span>
                    <span class="text-[10px] font-black text-gray-400 tracking-widest leading-none">Cobertura Mesas</span>
                </div>
                <p class="text-sm font-black text-gray-500">Mesas con Reporte</p>
                <p class="text-4xl font-black text-gray-800 mt-1 tracking-tighter" x-text="filteredKpis.mesas_reportadas">...</p>
            </div>

            <!-- KPI 4: LIDERES -->
            <div class="bg-white rounded-2xl shadow-xl p-6 border-b-4 border-teal-500 card-stat">
                <div class="flex items-center justify-between mb-2">
                    <span class="p-2 bg-teal-50 rounded-lg">
                        <i data-lucide="users" class="w-6 h-6 text-teal-500"></i>
                    </span>
                    <span class="text-[10px] font-black text-gray-400 tracking-widest leading-none">Participación</span>
                </div>
                <p class="text-sm font-black text-gray-500">Líderes Únicos</p>
                <p class="text-4xl font-black text-teal-600 mt-1 tracking-tighter" x-text="filteredKpis.lideres_unicos">...</p>
            </div>

        </div>

        <!-- CHARTS SECTION -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
             <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                    <i data-lucide="trophy" class="w-4 h-4 text-accent"></i> Ranking Líderes con más Votos
                </h3>
                <div class="h-64">
                    <canvas id="leadersChart"></canvas>
                </div>
             </div>
             <div class="bg-white rounded-3xl shadow-xl p-6 border border-gray-100">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-primary"></i> Votos por Comuna (Yumbo)
                </h3>
                <div class="h-64">
                    <canvas id="comunasChart"></canvas>
                </div>
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
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                            <i data-lucide="list" class="w-5 h-5 text-primary"></i>
                            Detalle por Mesa
                        </h3>
                        <a :href="'<?= url('api/diaD_datos.php?action=export') ?>'" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black uppercase px-4 py-2 rounded-xl transition-all shadow-md">
                            <i data-lucide="download" class="w-3 h-3"></i> Exportar XLSX
                        </a>
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
            
            <!-- Sidebar Lists -->
            <div class="space-y-6">
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
                <div class="flex gap-4 items-center">
                    <!-- Sort Button -->
                    <button @click="matrixSortBy = (matrixSortBy === 'ALFA' ? 'VOTOS' : 'ALFA')" 
                            class="flex items-center gap-2 bg-white text-secondary px-4 py-2 rounded-xl text-[10px] font-black uppercase shadow-lg hover:bg-gray-100 transition-all border border-white/20">
                        <i :data-lucide="matrixSortBy === 'ALFA' ? 'sort-desc' : 'type'" class="w-3 h-3"></i>
                        <span x-text="matrixSortBy === 'ALFA' ? 'Ordenar por Votos' : 'Ordenar Alfabético'"></span>
                    </button>

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
                            <template x-for="p in sortedPuestosMatrix" :key="p.id">
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
                                <template x-for="p in sortedPuestosMatrix" :key="p.id">
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

    <footer class="bg-white border-t border-gray-100 pt-16 pb-12 px-4 mt-20 uppercase tracking-widest text-primary">
        <div class="w-full px-6 text-center">
            <div class="flex items-center justify-center gap-3 mb-6">
                <div class="w-10 h-10 bg-primary/5 rounded-xl flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <span class="text-sm font-black uppercase">Aratio <span class="text-secondary">Pro</span> Digital Monitoring</span>
            </div>
            <p class="text-xs text-gray-400 mb-6 font-medium">Election Control System — Intelligence Systems</p>
            <div class="flex justify-center gap-6 text-[10px] font-black uppercase">
                <a href="<?= url('mod_diaD/CHANGELOG.md') ?>" class="hover:text-secondary transition-colors flex items-center gap-2">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Changelog
                </a>
                <span class="text-gray-200">|</span>
                <span class="text-gray-400">© 2025 Aratio Intelligence Systems</span>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        function dashboardData() {
            const API_BASE = '<?= url('api/') ?>';
            return {
                API_BASE: API_BASE,
                loading: false,
                map: null,
                markers: [],
                kpis: {
                    total_votos: 0,
                    mesas_reportadas: 0,
                    puestos_activos: 0,
                    lideres_unicos: 0
                },
                lideresTop: [],
                comunasStats: [],
                charts: { leaders: null, comunas: null },
                alertas: [],
                puestosStats: [],
                municipiosStats: [],
                mesasStats: [],
                selectedMun: 'TODOS',
                showMatrix: false,
                matrixSortBy: 'ALFA', // 'ALFA' or 'VOTOS'
                
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
                        const res = await fetch(`${this.API_BASE}diaD_datos.php?action=dashboard&municipio=${encodeURIComponent(this.selectedMun)}`);
                        const data = await res.json();
                        
                        if (data.success) {
                            this.kpis = data.kpis;
                            this.alertas = data.alertas || [];
                            this.puestosStats = data.puestos_stats || [];
                            this.mesasStats = data.mesas_stats || [];
                            this.lideresTop = data.lideres_top || [];
                            this.comunasStats = data.comunas_stats || [];
                            this.municipiosStats = (data.municipios_stats || []).sort((a,b) => b.total_votos - a.total_votos);
                            
                            this.updateDisplay();
                            this.renderCharts();
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

                get sortedPuestosMatrix() {
                    let sorted = [...this.filteredPuestos];
                    if (this.matrixSortBy === 'VOTOS') {
                        return sorted.sort((a, b) => (b.total_votos || 0) - (a.total_votos || 0));
                    }
                    // Default Alphabetical
                    return sorted.sort((a, b) => a.nombre.localeCompare(b.nombre));
                },

                get filteredMesas() {
                    if (this.selectedMun === 'TODOS') return this.mesasStats;
                    return this.mesasStats.filter(m => m.municipio === this.selectedMun);
                },

                get filteredKpis() {
                    const fm = this.filteredMesas;
                    // Count unique leaders in current filtered data
                    const uniqueLid = new Set(fm.map(m => m.id_colaborador).filter(id => id));
                    
                    return {
                        total_votos: fm.reduce((sum, m) => sum + parseInt(m.votos_total || 0), 0),
                        mesas_reportadas: fm.length,
                        puestos_activos: new Set(fm.map(m => m.id_puesto)).size,
                        lideres_unicos: uniqueLid.size || this.kpis.lideres_unicos
                    };
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('es-CO').format(num || 0);
                },

                renderCharts() {
                    // This creates or updates Chart.js instances
                    try {
                        this.renderLeadersChart();
                        this.renderComunasChart();
                    } catch (err) {
                        console.error("Error rendering charts:", err);
                    }
                },

                renderLeadersChart() {
                    const canvas = document.getElementById('leadersChart');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    if (this.charts.leaders) this.charts.leaders.destroy();

                    this.charts.leaders = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.lideresTop.map(l => l.nombre),
                            datasets: [{
                                label: 'Votos Reportados',
                                data: this.lideresTop.map(l => l.votos),
                                backgroundColor: '<?= COLOR_SECONDARY ?>',
                                borderRadius: 8
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { x: { beginAtZero: true, grid: { display: false } } }
                        }
                    });
                },

                renderComunasChart() {
                    const canvas = document.getElementById('comunasChart');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    if (this.charts.comunas) this.charts.comunas.destroy();

                    const data = this.comunasStats.slice(0, 10);

                    this.charts.comunas = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.map(c => 'C' + c.comuna),
                            datasets: [{
                                label: 'Votos',
                                data: data.map(c => c.votos),
                                backgroundColor: '<?= COLOR_PRIMARY ?>',
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, grid: { borderDash: [2, 2] } } }
                        }
                    });
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
                    if (!this.map) return;
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

                        let markerColor = '#26A69A'; 
                        if (p.estado_general === 'ALERTA') markerColor = '#ef4444'; 
                        
                        const radius = Math.min(8 + (parseInt(p.total_votos || 0) / 100), 30);

                        const iconHtml = `<div class="relative flex items-center justify-center">
                                <div class="absolute rounded-full opacity-20 animate-ping" style="background-color: ${markerColor}; width: ${radius*2.5}px; height: ${radius*2.5}px"></div>
                                <div class="relative rounded-full border-2 border-white shadow-xl flex items-center justify-center transition-all bg-white" 
                                     style="background-color: ${markerColor}; width: ${radius*2}px; height: ${radius*2}px">
                                    <span class="text-[9px] font-black text-white" style="font-size: ${Math.max(7, radius/2)}px">${p.total_votos}</span>
                                </div>
                            </div>`;
                        
                        const customIcon = L.divIcon({
                            html: iconHtml,
                            className: 'custom-marker',
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
                        });
                        
                        const popupContent = `<div class="p-2 min-w-[150px]">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mb-1">${p.municipio}</div>
                                <h4 class="font-bold border-b border-gray-100 pb-1 mb-2 text-gray-800 text-sm">${p.nombre}</h4>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs text-gray-500">Votos Totales</span>
                                    <span class="font-black text-secondary text-base">${p.total_votos}</span>
                                </div>
                            </div>`;
                        
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
            };
        }
    </script>
</body>
</html>
