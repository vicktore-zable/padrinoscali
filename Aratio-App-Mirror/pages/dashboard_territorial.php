<div class="max-w-7xl mx-auto" x-data="dashboardTerritorial()" x-init="init()" x-cloak>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Dashboard Territorial</h2>
            <p class="text-sm text-gray-500 mt-1">Indicadores clave y distribución geográfica de la campaña</p>
        </div>
        <div class="flex items-center gap-2">
            <select x-model="periodo" @change="loadTendencias()" class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs">
                <option value="12">12 meses</option>
                <option value="6">6 meses</option>
                <option value="3">3 meses</option>
            </select>
            <button @click="refreshAll()" class="p-2 text-gray-400 hover:text-primary rounded-lg hover:bg-gray-100">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Colaboradores</p>
            <p class="text-xl font-bold text-gray-900 mt-0.5" x-text="kpi.colaboradores || 0"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Activos</p>
            <p class="text-xl font-bold text-green-600 mt-0.5" x-text="kpi.activos || 0"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Líderes</p>
            <p class="text-xl font-bold text-primary mt-0.5" x-text="kpi.lideres || 0"></p>
        </div>
    </div>

    <!-- KPIs Trabajo Social -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Líderes Totales</p>
            <p class="text-xl font-bold text-gray-900 mt-0.5" x-text="semaforo.lideres_total || 0"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 bg-green-50 p-3 text-center">
            <p class="text-xs text-green-600">Con Trabajo Social</p>
            <p class="text-xl font-bold text-green-700 mt-0.5" x-text="semaforo.con_trabajo || 0"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 bg-red-50 p-3 text-center">
            <p class="text-xs text-red-600">Sin Trabajo Social</p>
            <p class="text-xl font-bold text-red-700 mt-0.5" x-text="semaforo.sin_trabajo || 0"></p>
        </div>
    </div>

    <!-- Mapas lado a lado -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Mapa Dónde Viven -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900">Dónde viven</h4>
                <p class="text-xs text-gray-400 mt-1">Colaboradores agrupados por barrio</p>
                <div class="flex items-center gap-2 mt-2 text-xs">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span> Alta (≥5)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-400 inline-block"></span> Media (3-4)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-200 inline-block"></span> Baja (1-2)</span>
                </div>
            </div>
            <div id="mapaViven" style="height:360px" class="bg-gray-50 relative">
                <div x-show="!lideresGeo.total_colaboradores" class="absolute inset-0 bg-gray-100 animate-pulse flex items-center justify-center z-[999]" style="border-radius:0 0 0.75rem 0.75rem;">
                    <div class="text-center">
                        <i data-lucide="loader-2" class="w-6 h-6 text-gray-400 mx-auto animate-spin"></i>
                        <p class="text-xs text-gray-400 mt-1">Cargando mapa...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa Zonas de Trabajo -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900">Zonas de trabajo</h4>
                        <p class="text-xs text-gray-400 mt-1">Responsables por zona</p>
                    </div>
                    <button @click="recargarZonasSemaforo()" class="p-1.5 text-gray-400 hover:text-primary rounded hover:bg-gray-100" title="Recargar">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <div class="flex items-center gap-2 mt-2 text-xs">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> Alta (≥5)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-yellow-500 inline-block"></span> Media (3-4)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Baja (1-2)</span>
                </div>
            </div>
            <div id="mapaZonas" style="height:360px" class="bg-gray-50 relative">
                <div x-show="!semaforo.lideres_total" class="absolute inset-0 bg-gray-100 animate-pulse flex items-center justify-center z-[999]" style="border-radius:0 0 0.75rem 0.75rem;">
                    <div class="text-center">
                        <i data-lucide="loader-2" class="w-6 h-6 text-gray-400 mx-auto animate-spin"></i>
                        <p class="text-xs text-gray-400 mt-1">Cargando mapa...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución: Barrios + Comunas + Tendencia -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Colab por Barrio</h4>
            <div class="relative" style="height:220px">
                <canvas x-ref="chartBarrio" class="absolute inset-0 w-full h-full"></canvas>
                <div x-show="!distribucion.barrios.length" class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">Sin datos</div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Colab por Territorio</h4>
            <div class="relative" style="height:220px">
                <canvas x-ref="chartTerritorio" class="absolute inset-0 w-full h-full"></canvas>
                <div x-show="!distribucion.territorios.length" class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">Sin datos</div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Colaboradores por mes</h4>
            <div class="relative" style="height:220px">
                <canvas x-ref="chartColab" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabbed Map: Compromisos / Eventos / Acciones / Instagram -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center gap-1">
                <button @click="cambiarTab('compromisos')" :class="tabOtros == 'compromisos' ? 'bg-purple-100 text-purple-700 border-purple-200' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent transition-colors">Compromisos</button>
                <button @click="cambiarTab('eventos')" :class="tabOtros == 'eventos' ? 'bg-orange-100 text-orange-700 border-orange-200' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent transition-colors">Eventos</button>
                <button @click="cambiarTab('acciones')" :class="tabOtros == 'acciones' ? 'bg-teal-100 text-teal-700 border-teal-200' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent transition-colors">Acciones</button>
                <button @click="cambiarTab('instagram')" :class="tabOtros == 'instagram' ? 'bg-pink-100 text-pink-700 border-pink-200' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent transition-colors">Instagram</button>
            </div>
        </div>
        <div id="mapaOtros" style="height:360px" class="bg-gray-50 relative">
            <div x-show="!otrosCargados[tabOtros]" class="absolute inset-0 bg-gray-100 animate-pulse flex items-center justify-center z-[999]" style="border-radius:0 0 0.75rem 0.75rem;">
                <div class="text-center">
                    <i data-lucide="loader-2" class="w-6 h-6 text-gray-400 mx-auto animate-spin"></i>
                    <p class="text-xs text-gray-400 mt-1">Cargando mapa...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardTerritorial() {
    return {
        loading: false,
        loadingZonas: false,
        periodo: '12',
        kpi: {},
        semaforo: { lideres_total: 0, con_trabajo: 0, sin_trabajo: 0, geojson: { features: [] } },
        lideresGeo: { total_colaboradores: 0, geojson: { features: [] } },
        tendencias: { colaboradores: [] },
        distribucion: { barrios: [], territorios: [] },
        mapaViven: null,
        mapaZonas: null,
        zonasLayer: null,
        lideresLayer: null,
        chartInstances: {},
        tabOtros: 'compromisos',
        otrosMapa: null,
        otrosLayer: null,
        otrosData: null,
        otrosCargados: {},

        async init() {
            await this.refreshAll();
            setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 100);
        },

        async refreshAll() {
            this.loading = true;
            try {
                await Promise.all([
                    this.loadKpi(),
                    this.loadTendencias(),
                    this.loadDistribucion(),
                    this.loadSemaforo(),
                    this.loadLideresGeo(),
                    this.cargarOtrosMapa(),
                ]);
            } catch(e) { console.error('Dashboard error:', e); }
            this.loading = false;
            this.$nextTick(() => {
                this.initMapaViven();
                if (typeof lucide !== 'undefined') lucide.createIcons();
                this.initChart('chartColab', 'Colaboradores', this.tendencias.colaboradores, '#2563eb');
                this.initBarChart();
                this.initTerritorioChart();
                setTimeout(() => { this.initMapaZonas(); }, 300);
                this.initMapaOtros();
            });
        },

        async loadKpi() {
            const r = await fetch('api/dashboard.php?action=kpi');
            const j = await r.json();
            if (j.success) this.kpi = j.data;
        },

        async loadTendencias() {
            const r = await fetch('api/dashboard.php?action=tendencias');
            const j = await r.json();
            if (j.success) this.tendencias = j.data;
        },

        async loadRecientes() {
            const r = await fetch('api/dashboard.php?action=recientes');
            const j = await r.json();
            if (j.success) this.recientes = j.data;
        },

        async loadAlasStats() {
            const r = await fetch('api/dashboard.php?action=alas_stats');
            const j = await r.json();
            if (j.success) this.alas = j.data;
        },

        async loadSemaforo() {
            const r = await fetch('api/dashboard.php?action=zonas_semaforo');
            const j = await r.json();
            if (j.success) this.semaforo = j.data;
        },

        async loadLideresGeo() {
            const r = await fetch('api/dashboard.php?action=lideres_por_territorio');
            const j = await r.json();
            if (j.success) this.lideresGeo = j.data;
        },

        async loadDistribucion() {
            const r = await fetch('api/dashboard.php?action=distribucion');
            const j = await r.json();
            if (j.success) this.distribucion = j.data;
        },

        async recargarZonasSemaforo() {
            this.loadingZonas = true;
            await this.loadSemaforo();
            if (this.zonasLayer && this.mapaZonas) {
                this.mapaZonas.removeLayer(this.zonasLayer);
                this.zonasLayer = null;
            }
            this.agregarCapaZonas();
            this.loadingZonas = false;
        },

        _crearMapa(containerId) {
            const el = document.getElementById(containerId);
            if (!el || el._leaflet_id) return null;
            if (typeof L === 'undefined') return null;
            const map = L.map(containerId, { zoomControl: true }).setView([3.4516, -76.5320], 11);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            return map;
        },

        initMapaViven() {
            this.mapaViven = this._crearMapa('mapaViven');
            if (!this.mapaViven) return;
            this.agregarCapaLideres();
        },

        initMapaZonas() {
            this.mapaZonas = this._crearMapa('mapaZonas');
            if (!this.mapaZonas) return;
            this.agregarCapaZonas();
        },

        agregarCapaZonas() {
            if (!this.mapaZonas || !this.semaforo.geojson || !this.semaforo.geojson.features || this.semaforo.geojson.features.length === 0) return;

            this.zonasLayer = L.geoJSON(this.semaforo.geojson, {
                style: (feature) => ({
                    color: feature.properties.color,
                    weight: 2,
                    fillColor: feature.properties.fillColor,
                    fillOpacity: 0.35
                }),
                onEachFeature: (feature, layer) => {
                    const p = feature.properties;
                    layer.bindTooltip(`<b>${p.Territorio || ''} ${p.barrio || ''}</b><br>${p.n_responsables} responsable(s) — ${p.nivel}`, { sticky: true });
                    layer.bindPopup(`
                        <div style="min-width:160px">
                            <h4 style="margin:0 0 4px;font-weight:700">${p.Territorio || ''} ${p.barrio || ''}</h4>
                            <hr style="margin:4px 0">
                            <p style="margin:2px 0">Municipio: <b>${p.municipio || ''}</b></p>
                            <p style="margin:2px 0">Tipo: <b>${p.Tipo_territorio || ''}</b></p>
                            <p style="margin:2px 0">Responsables: <b>${p.n_responsables}</b></p>
                            <p style="margin:2px 0">Cobertura: <b style="color:${p.color}">${p.nivel}</b></p>
                        </div>
                    `);
                }
            }).addTo(this.mapaZonas);
        },

        agregarCapaLideres() {
            if (!this.mapaViven || !this.lideresGeo.geojson || !this.lideresGeo.geojson.features || this.lideresGeo.geojson.features.length === 0) return;

            this.lideresLayer = L.geoJSON(this.lideresGeo.geojson, {
                style: (feature) => ({
                    color: feature.properties.color,
                    weight: 2,
                    fillColor: feature.properties.fillColor,
                    fillOpacity: 0.35
                }),
                onEachFeature: (feature, layer) => {
                    const p = feature.properties;
                    layer.bindTooltip(`<b>${p.Territorio || ''} ${p.barrio || ''}</b><br>${p.n_colaboradores} colaborador(es) — ${p.nivel}`, { sticky: true });
                    layer.bindPopup(`
                        <div style="min-width:160px">
                            <h4 style="margin:0 0 4px;font-weight:700">${p.Territorio || ''} ${p.barrio || ''}</h4>
                            <hr style="margin:4px 0">
                            <p style="margin:2px 0">Municipio: <b>${p.municipio || ''}</b></p>
                            <p style="margin:2px 0">Tipo: <b>${p.Tipo_territorio || ''}</b></p>
                            <p style="margin:2px 0">Colaboradores: <b>${p.n_colaboradores}</b></p>
                            <p style="margin:2px 0">Cobertura: <b style="color:${p.color}">${p.nivel}</b></p>
                        </div>
                    `);
                }
            }).addTo(this.mapaViven);
        },

        initChart(refName, label, data, color, valueKey) {
            if (typeof Chart === 'undefined') return;
            const canvas = this.$refs[refName];
            if (!canvas) return;
            if (this.chartInstances[refName]) this.chartInstances[refName].destroy();

            if (!data || data.length === 0) {
                canvas.parentElement.innerHTML = '<p class="text-center text-gray-400 text-sm py-8">Sin datos</p>';
                return;
            }

            this.chartInstances[refName] = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.map(d => {
                        const parts = (d.mes || '').split('-');
                        return parts.length === 2 ? parts[1] + '/' + parts[0].slice(2) : d.mes;
                    }),
                    datasets: [{
                        label: label,
                        data: data.map(d => valueKey ? (d[valueKey] || 0) : (d.total || 0)),
                        backgroundColor: color + '30',
                        borderColor: color,
                        borderWidth: 2,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                    }
                }
            });
        },

        initBarChart() {
            const data = this.distribucion.barrios;
            const canvas = this.$refs.chartBarrio;
            if (!canvas || !data || !data.length) return;
            if (this.chartInstances.chartBarrio) this.chartInstances.chartBarrio.destroy();

            const labels = data.map(d => (d.territorio || '') + ' - ' + d.barrio);
            this.chartInstances.chartBarrio = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Total', data: data.map(d => +d.total), backgroundColor: '#3b82f680', borderColor: '#3b82f6', borderWidth: 1, borderRadius: 2 },
                        { label: 'Líderes', data: data.map(d => +d.lideres), backgroundColor: '#22c55e80', borderColor: '#22c55e', borderWidth: 1, borderRadius: 2 },
                    ]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, animation: false,
                    plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 9 } } } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 9 } } },
                        y: { grid: { display: false }, ticks: { font: { size: 7 } } }
                    }
                }
            });
        },

        initTerritorioChart() {
            const data = this.distribucion.territorios;
            const canvas = this.$refs.chartTerritorio;
            if (!canvas || !data || !data.length) return;
            if (this.chartInstances.chartTerritorio) this.chartInstances.chartTerritorio.destroy();

            const labels = data.map(d => d.territorio + (d.tipo_territorio ? ' (' + d.tipo_territorio + ')' : ''));
            this.chartInstances.chartTerritorio = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Total', data: data.map(d => +d.total), backgroundColor: '#3b82f680', borderColor: '#3b82f6', borderWidth: 1, borderRadius: 2 },
                        { label: 'Líderes', data: data.map(d => +d.lideres), backgroundColor: '#22c55e80', borderColor: '#22c55e', borderWidth: 1, borderRadius: 2 },
                    ]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, animation: false,
                    plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 9 } } } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 9 } } },
                        y: { grid: { display: false }, ticks: { font: { size: 8 } } }
                    }
                }
            });
        },

        // === Tabbed Map: Compromisos / Eventos / Acciones / Instagram ===
        async loadOtrosMapas() {
            const r = await fetch('api/dashboard.php?action=otros_mapas');
            const j = await r.json();
            if (j.success) this.otrosData = j.data;
        },

        initMapaOtros() {
            this.otrosMapa = this._crearMapa('mapaOtros');
            if (!this.otrosMapa) return;
            this.mostrarCapaOtros(this.tabOtros);
        },

        async cargarOtrosMapa() {
            const tab = this.tabOtros;
            if (this.otrosCargados[tab] && this.otrosLayer) return;
            if (!this.otrosData) await this.loadOtrosMapas();
            this.otrosCargados[tab] = true;
            if (this.otrosMapa) this.mostrarCapaOtros(tab);
        },

        mostrarCapaOtros(tab) {
            if (!this.otrosMapa || !this.otrosData || !this.otrosData[tab]) return;
            if (this.otrosLayer) { this.otrosMapa.removeLayer(this.otrosLayer); this.otrosLayer = null; }
            const geojson = this.otrosData[tab];
            if (!geojson.features || !geojson.features.length) return;
            this.otrosLayer = L.geoJSON(geojson, {
                style: (f) => ({ color: f.properties.color, weight: 2, fillColor: f.properties.fillColor, fillOpacity: 0.35 }),
                onEachFeature: (f, l) => {
                    if (f.properties.tooltip) l.bindTooltip(f.properties.tooltip, { sticky: true });
                    if (f.properties.popup) l.bindPopup(f.properties.popup);
                }
            }).addTo(this.otrosMapa);
        },

        cambiarTab(tab) {
            this.tabOtros = tab;
            if (this.otrosMapa) this.$nextTick(() => this.cargarOtrosMapa());
        },

        formatNum(n) {
            if (!n) return '0';
            return Number(n).toLocaleString('es-CO');
        }
    }
}
</script>
