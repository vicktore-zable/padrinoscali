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
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="{ 'animate-spin': loading }"></i>
            </button>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Donaciones</p>
            <p class="text-xl font-bold text-yellow-600 mt-0.5" x-text="kpi.donaciones || 0"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Recaudado</p>
            <p class="text-xl font-bold text-yellow-600 mt-0.5" x-text="'$' + formatNum(kpi.recaudado)"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Eventos</p>
            <p class="text-xl font-bold text-blue-600 mt-0.5" x-text="kpi.eventos || 0"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Asistentes</p>
            <p class="text-xl font-bold text-blue-600 mt-0.5" x-text="kpi.asistentes || 0"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-500">Compromisos</p>
            <p class="text-xl font-bold text-purple-600 mt-0.5" x-text="kpi.compromisos || 0"></p>
        </div>
    </div>

    <!-- Distribución territorial + Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Mapa Leaflet -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-900">Distribución por Municipio</h4>
                <span class="text-xs text-gray-400" x-text="kpi.total_municipios + ' municipios'"></span>
            </div>
            <div id="mapaDashboard" style="height:360px" class="bg-gray-50"></div>
            <div class="p-3 bg-gray-50 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <template x-for="item in geo.slice(0, 4)" :key="item.municipio">
                    <div class="flex items-center justify-between px-2 py-1 bg-white rounded">
                        <span class="text-gray-600 truncate" x-text="item.municipio"></span>
                        <span class="font-semibold text-gray-900 ml-1" x-text="item.total"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Perfiles + Top municipios -->
        <div class="space-y-6">
            <!-- Perfiles -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Perfiles</h4>
                <div class="space-y-2.5">
                    <template x-for="p in kpi.perfiles" :key="p.perfil">
                        <div>
                            <div class="flex justify-between text-xs mb-0.5">
                                <span class="text-gray-600" x-text="p.perfil || 'Sin perfil'"></span>
                                <span class="font-semibold" x-text="p.total"></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full" :style="'width:' + (p.total / Math.max(...kpi.perfiles.map(x=>x.total)) * 100) + '%'"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Top municipios -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Top Municipios</h4>
                <div class="space-y-2">
                    <template x-for="(item, i) in kpi.top_municipios" :key="item.municipio">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-500" x-text="i + 1"></span>
                                <span class="text-gray-700 truncate max-w-[120px]" x-text="item.municipio"></span>
                            </div>
                            <span class="font-semibold text-gray-900" x-text="item.total"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Tendencias -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Colaboradores por mes</h4>
            <canvas x-ref="chartColab" height="180"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Donaciones por mes</h4>
            <canvas x-ref="chartDonaciones" height="180"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Eventos por mes</h4>
            <canvas x-ref="chartEventos" height="180"></canvas>
        </div>
    </div>

    <!-- ALAS Stats + Recientes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- ALAS Stats -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">ALAS — Estado del Sistema</h4>
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-green-50 rounded-xl">
                    <p class="text-xs text-green-600">WhatsApp</p>
                    <p class="text-lg font-bold text-green-800" x-text="alas.whatsapp.conversaciones + ' convs'"></p>
                    <p class="text-xs text-green-500" x-text="alas.whatsapp.no_leidos + ' no leídos'"></p>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl">
                    <p class="text-xs text-blue-600">Workflows</p>
                    <p class="text-lg font-bold text-blue-800" x-text="alas.workflows.ejecuciones_hoy + ' hoy'"></p>
                    <p class="text-xs text-blue-500" x-text="alas.workflows.reglas_activas + ' reglas'"></p>
                </div>
                <div class="p-3 bg-purple-50 rounded-xl">
                    <p class="text-xs text-purple-600">Phone Banking</p>
                    <p class="text-lg font-bold text-purple-800" x-text="alas.phone.llamadas_hoy + ' hoy'"></p>
                    <p class="text-xs text-purple-500" x-text="alas.phone.pendientes + ' pendientes'"></p>
                </div>
                <div class="p-3 bg-orange-50 rounded-xl">
                    <p class="text-xs text-orange-600">Email</p>
                    <p class="text-lg font-bold text-orange-800" x-text="alas.email.enviados_hoy + ' hoy'"></p>
                    <p class="text-xs text-orange-500" x-text="alas.email.pendientes + ' pendientes'"></p>
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900">Actividad Reciente</h4>
            </div>
            <div class="divide-y divide-gray-50 max-h-[240px] overflow-y-auto">
                <template x-for="item in recientes.actividad" :key="item.id">
                    <div class="px-4 py-2.5 flex items-start gap-2 hover:bg-gray-50">
                        <i :data-lucide="iconoActividad(item.tipo)" class="w-3.5 h-3.5 mt-0.5 text-gray-400 flex-shrink-0"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-900 truncate" x-text="item.nombres + ' ' + item.apellidos"></p>
                            <p class="text-xs text-gray-400 truncate" x-text="item.descripcion || item.tipo"></p>
                        </div>
                        <span class="text-xs text-gray-400 flex-shrink-0" x-text="tiempoRelativo(item.creado_en)"></span>
                    </div>
                </template>
                <div x-show="!recientes.actividad || recientes.actividad.length === 0" class="p-6 text-center text-gray-400 text-sm">
                    Sin actividad reciente
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardTerritorial() {
    return {
        loading: false,
        periodo: '12',
        kpi: {},
        geo: [],
        tendencias: { colaboradores: [], donaciones: [], eventos: [] },
        recientes: { colaboradores: [], donaciones: [], eventos: [], actividad: [] },
        alas: { whatsapp: {}, workflows: {}, phone: {}, email: {} },
        mapInstance: null,
        markersLayer: null,
        chartInstances: {},

        async init() {
            await this.refreshAll();
            setTimeout(() => { lucide.createIcons(); }, 100);
        },

        async refreshAll() {
            this.loading = true;
            try {
                await Promise.all([
                    this.loadKpi(),
                    this.loadGeo(),
                    this.loadTendencias(),
                    this.loadRecientes(),
                    this.loadAlasStats(),
                ]);
            } catch(e) { console.error('Dashboard error:', e); }
            this.loading = false;
            this.$nextTick(() => {
                this.initMapa();
                lucide.createIcons();
                this.initChart('chartColab', 'Colaboradores', this.tendencias.colaboradores, '#2563eb');
                this.initChart('chartDonaciones', 'Donaciones', this.tendencias.donaciones, '#ca8a04', 'monto');
                this.initChart('chartEventos', 'Eventos', this.tendencias.eventos, '#2563eb');
            });
        },

        async loadKpi() {
            const r = await fetch('api/dashboard.php?action=kpi');
            const j = await r.json();
            if (j.success) this.kpi = j.data;
        },

        async loadGeo() {
            const r = await fetch('api/dashboard.php?action=geo');
            const j = await r.json();
            if (j.success) this.geo = j.data;
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

        initMapa() {
            const el = document.getElementById('mapaDashboard');
            if (!el || el._leaflet_id) return;
            if (typeof L === 'undefined') return;

            this.mapInstance = L.map('mapaDashboard', { zoomControl: true }).setView([3.4516, -76.5320], 11);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.mapInstance);

            const markers = [];
            for (const item of this.geo) {
                if (item.total > 0) {
                    markers.push({
                        municipio: item.municipio,
                        total: item.total,
                        lideres: item.lideres,
                        movilizadores: item.movilizadores,
                        simpatizantes: item.simpatizantes,
                    });
                }
            }

            if (markers.length > 0) {
                fetch('/aratio/api_territorios_geojson.php')
                    .then(r => r.json())
                    .then(geoJson => {
                        if (geoJson && geoJson.features) {
                            const filtered = {
                                ...geoJson,
                                features: geoJson.features.filter(f => {
                                    const props = f.properties || {};
                                    const nombre = (props.Municipio || props.municipio || props.NOMBRE || '').toLowerCase();
                                    return markers.some(m => m.municipio.toLowerCase() === nombre);
                                })
                            };

                            const geoLayer = L.geoJSON(filtered, {
                                style: () => ({ color: '#7c3aed', weight: 1, fillOpacity: 0.15 }),
                                onEachFeature: (feature, layer) => {
                                    const props = feature.properties || {};
                                    const nombre = props.Municipio || props.municipio || props.NOMBRE || '';
                                    const m = markers.find(x => x.municipio.toLowerCase() === nombre.toLowerCase());
                                    if (m) {
                                        layer.bindTooltip(`<b>${m.municipio}</b><br>${m.total} colaboradores`, { sticky: true });
                                        layer.bindPopup(`
                                            <div style="min-width:160px">
                                                <h4 style="margin:0 0 4px;font-weight:700">${m.municipio}</h4>
                                                <hr style="margin:4px 0">
                                                <p style="margin:2px 0">Colaboradores: <b>${m.total}</b></p>
                                                <p style="margin:2px 0">Líderes: <b>${m.lideres}</b></p>
                                                <p style="margin:2px 0">Movilizadores: <b>${m.movilizadores}</b></p>
                                                <p style="margin:2px 0">Simpatizantes: <b>${m.simpatizantes}</b></p>
                                            </div>
                                        `);
                                    }
                                }
                            }).addTo(this.mapInstance);

                            const bounds = geoLayer.getBounds();
                            if (bounds.isValid()) this.mapInstance.fitBounds(bounds, { padding: [30, 30] });
                        }
                    })
                    .catch(() => {
                        const bounds = [];
                        markers.forEach(m => {
                            const marker = L.circleMarker([3.45, -76.53], {
                                radius: Math.max(6, Math.min(20, m.total / 5)),
                                color: '#7c3aed',
                                fillColor: '#7c3aed',
                                fillOpacity: 0.4,
                            }).addTo(this.mapInstance);
                            marker.bindTooltip(`<b>${m.municipio}</b>: ${m.total}`, { sticky: true });
                            bounds.push([3.45 + (Math.random() - 0.5) * 0.5, -76.53 + (Math.random() - 0.5) * 0.5]);
                        });
                        if (bounds.length > 0) this.mapInstance.fitBounds(bounds, { padding: [30, 30] });
                    });
            }
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
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                    }
                }
            });
        },

        iconoActividad(tipo) {
            const map = {
                registro: 'user-plus', evento_asistio: 'calendar-check',
                compromiso_creado: 'handshake', donacion_hizo: 'dollar-sign',
                whatsapp_enviado: 'message-circle', whatsapp_recibido: 'message-square',
                estado_cambio: 'refresh-cw', evaluacion: 'star',
                lider_cambio: 'users', cumpleaños: 'cake',
                llamada_contestó: 'phone-call', llamada_no_contesta: 'phone-missed',
            };
            return map[tipo] || 'activity';
        },

        tiempoRelativo(ts) {
            if (!ts) return '';
            const d = new Date(ts.replace(' ', 'T'));
            const diff = (new Date() - d) / 1000;
            if (diff < 60) return 'ahora';
            if (diff < 3600) return Math.floor(diff / 60) + 'm';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h';
            return Math.floor(diff / 86400) + 'd';
        },

        formatNum(n) {
            if (!n) return '0';
            return Number(n).toLocaleString('es-CO');
        }
    }
}
</script>
