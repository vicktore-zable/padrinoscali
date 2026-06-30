<div class="max-w-7xl mx-auto" x-data="socialTerritorial()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Dashboard Territorial Social</h2>
            <p class="text-sm text-gray-500 mt-1">Distribución geográfica de actividad en Instagram y Facebook</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <span class="w-2 h-2 rounded-full bg-green-500" x-show="loaded"></span>
            <span x-text="loaded ? totalPosts + ' posts geolocalizados' : ''"></span>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Posts con geo</p>
            <p class="text-2xl font-extrabold text-gray-900" x-text="stats.con_geo || 0"></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">En Cali</p>
            <p class="text-2xl font-extrabold text-primary" x-text="stats.en_cali || 0"></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Likes totales</p>
            <p class="text-2xl font-extrabold text-red-500" x-text="formatear(stats.likes_total || 0)"></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Comentarios</p>
            <p class="text-2xl font-extrabold text-purple-600" x-text="formatear(stats.comments_total || 0)"></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Engagement Rate</p>
            <p class="text-2xl font-extrabold text-amber-600" x-text="stats.engagement_rate || 0"></p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6 flex-wrap">
        <button @click="tab = 'mapa'; $nextTick(() => initMap())" :class="tab === 'mapa' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[80px]">
            <i data-lucide="map" class="w-4 h-4 inline mr-1"></i> Mapa
        </button>
        <button @click="tab = 'categorias'; loadCategories()" :class="tab === 'categorias' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[80px]">
            <i data-lucide="pie-chart" class="w-4 h-4 inline mr-1"></i> Categorías
        </button>
        <button @click="tab = 'temporal'; loadTimeline()" :class="tab === 'temporal' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[80px]">
            <i data-lucide="trending-up" class="w-4 h-4 inline mr-1"></i> Temporal
        </button>
        <button @click="tab = 'menciones'; loadMenciones()" :class="tab === 'menciones' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[80px]">
            <i data-lucide="at-sign" class="w-4 h-4 inline mr-1"></i> Menciones
        </button>
        <button @click="tab = 'facebook'; loadFb()" :class="tab === 'facebook' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[80px]">
            <i data-lucide="facebook" class="w-4 h-4 inline mr-1"></i> Facebook
        </button>
    </div>

    <!-- MAPA TAB -->
    <div x-show="tab === 'mapa'">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div id="social-map" style="height: 600px;"></div>
                </div>
            </div>
            <div class="space-y-4">
                <!-- Filtros -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Filtros</h4>
                    <div class="space-y-2">
                        <template x-for="(count, cat) in categoryCounts" :key="cat">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" :checked="activeCategories.includes(cat)" @change="toggleCategory(cat)"
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-xs font-medium text-gray-700 capitalize" x-text="cat"></span>
                                <span class="text-[10px] text-gray-400 ml-auto" x-text="count"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <!-- Leyenda -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Categorías</h4>
                    <div class="space-y-2">
                        <template x-for="(color, cat) in categoryColors" :key="cat">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background:' + color"></span>
                                <span class="text-xs text-gray-600 capitalize" x-text="cat"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <!-- Top ubicaciones -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4" x-show="topUbicaciones.length > 0">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Top Ubicaciones</h4>
                    <div class="space-y-2">
                        <template x-for="(count, ubi) in topUbicaciones" :key="ubi">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-700 truncate" x-text="ubi"></span>
                                <span class="font-bold text-gray-900 ml-2" x-text="count"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CATEGORIAS TAB -->
    <div x-show="tab === 'categorias'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Posts por Categoría</h3>
                <div id="chart-categorias" style="height: 300px;"></div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Engagement por Categoría</h3>
                <div id="chart-engagement" style="height: 300px;"></div>
            </div>
        </div>
        <!-- Tabla detalle -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mt-4">
            <div class="p-4 border-b border-gray-100">
                <h4 class="text-sm font-bold text-gray-700">Detalle por Categoría</h4>
            </div>
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[10px] text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3 font-semibold">Categoría</th>
                        <th class="px-4 py-3 font-semibold">Total</th>
                        <th class="px-4 py-3 font-semibold">Con Geo</th>
                        <th class="px-4 py-3 font-semibold">% Geo</th>
                        <th class="px-4 py-3 font-semibold">Likes</th>
                        <th class="px-4 py-3 font-semibold">Comments</th>
                        <th class="px-4 py-3 font-semibold">Engagement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="(cat, name) in stats.por_categoria" :key="name">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium capitalize" x-text="name"></td>
                            <td class="px-4 py-3" x-text="cat.total"></td>
                            <td class="px-4 py-3" x-text="cat.con_geo"></td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-bold" x-text="cat.total > 0 ? Math.round(cat.con_geo / cat.total * 100) + '%' : '0%'"></span>
                            </td>
                            <td class="px-4 py-3" x-text="formatear(cat.likes)"></td>
                            <td class="px-4 py-3" x-text="formatear(cat.comments)"></td>
                            <td class="px-4 py-3" x-text="cat.total > 0 ? ((cat.likes + cat.comments) / cat.total).toFixed(1) : '0'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TEMPORAL TAB -->
    <div x-show="tab === 'temporal'">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase mb-1">Total publicaciones</p>
                <p class="text-2xl font-extrabold text-gray-900" x-text="stats.total_posts"></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase mb-1">Periodos cubiertos</p>
                <p class="text-2xl font-extrabold text-primary" x-text="(stats.periodos || []).length + ' meses'"></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase mb-1">Posts relevantes</p>
                <p class="text-2xl font-extrabold text-amber-600" x-text="stats.relevantes || mostrarRelevantes()"></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Actividad por Mes</h3>
            <div id="chart-timeline" style="height: 350px;"></div>
        </div>
        <!-- Timeline compacto -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm mt-4 p-4">
            <h4 class="text-sm font-bold text-gray-700 mb-3">Posts Recientes</h4>
            <div class="space-y-2 max-h-80 overflow-y-auto">
                <template x-for="p in postsRecientes" :key="p.url">
                    <a :href="p.url" target="_blank" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-all">
                        <span class="w-2 h-2 rounded-full flex-shrink-0" :style="'background:' + (categoryColors[p.categoria] || '#6b7280')"></span>
                        <span class="text-xs text-gray-500 w-16 flex-shrink-0" x-text="p.fecha"></span>
                        <span class="text-xs text-gray-700 truncate flex-1" x-text="p.texto?.substring(0, 120)"></span>
                        <span class="text-[10px] text-gray-400 flex-shrink-0" x-text="(p.likes || 0) + ' ❤'"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- MENCIONES TAB -->
    <div x-show="tab === 'menciones'">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase mb-1">Total menciones</p>
                <p class="text-2xl font-extrabold text-gray-900" x-text="mencionesStats.total || 0"></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase mb-1">Usuarios únicos</p>
                <p class="text-2xl font-extrabold text-blue-600" x-text="mencionesStats.usuarios || 0"></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase mb-1">Identificados</p>
                <p class="text-2xl font-extrabold text-green-600" x-text="mencionesStats.tasa_match || '0%'"></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase mb-1">Categorías</p>
                <p class="text-2xl font-extrabold text-purple-600" x-text="(mencionesStats.por_categoria || []).length"></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h4 class="text-sm font-bold text-gray-700">Menciones por Categoría</h4>
            </div>
            <template x-for="m in mencionesStats.por_categoria" :key="m.categoria">
                <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full" :style="'background:' + (categoryColors[m.categoria] || '#6b7280')"></span>
                        <span class="text-sm font-medium capitalize" x-text="m.categoria"></span>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <span><strong x-text="m.total"></strong> menciones</span>
                        <span class="text-gray-400"><strong x-text="m.usuarios"></strong> usuarios</span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- FACEBOOK TAB -->
    <div x-show="tab === 'facebook'">
        <div x-show="!fbConfigured" class="bg-amber-50 border border-amber-200 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <div class="p-2 rounded-lg bg-amber-100 text-amber-600">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-amber-800 mb-1">Facebook no conectado</h3>
                    <p class="text-sm text-amber-700">Configura <code>FB_PAGE_TOKEN</code> en <code>root_config.php</code> para ver datos de Facebook.</p>
                </div>
            </div>
        </div>
        <div x-show="fbConfigured">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase mb-1">Posts</p>
                    <p class="text-2xl font-extrabold text-gray-900" x-text="fbStats.posts || 0"></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase mb-1">Reacciones</p>
                    <p class="text-2xl font-extrabold text-red-500" x-text="fbStats.reactions || 0"></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase mb-1">Comentarios</p>
                    <p class="text-2xl font-extrabold text-purple-600" x-text="fbStats.comments || 0"></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <p class="text-xs text-gray-400 uppercase mb-1">Match CRM</p>
                    <p class="text-2xl font-extrabold text-green-600" x-text="fbStats.matched || 0"></p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Actividad Facebook por Mes</h3>
                <div id="chart-fb" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js + Leaflet + MarkerCluster -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.leaflet-popup-content { margin: 12px 16px; font-size: 12px; line-height: 1.4; }
.leaflet-popup-content strong { display: block; font-size: 13px; margin-bottom: 4px; }
.category-marker { border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
</style>

<script>
function socialTerritorial() {
    return {
        tab: 'mapa',
        loaded: false,
        stats: { por_categoria: {} },
        totalPosts: 0,
        geoData: null,
        map: null,
        markers: null,
        markerCluster: null,
        categoryCounts: {},
        activeCategories: [],
        categoryColors: {
            obra: '#dc2626', general: '#6b7280', social: '#f59e0b',
            evento: '#8b5cf6', territorio: '#10b981', reunion: '#3b82f6',
            proyecto: '#ec4899', gestion: '#14b8a6', denuncia: '#ef4444',
        },
        topUbicaciones: [],
        timelineData: null,
        postsRecientes: [],
        mencionesStats: { por_categoria: [] },
        fbConfigured: false,
        fbStats: {},

        charts: {},

        async init() {
            await this.loadIgStats();
            await this.loadIgGeo();
            this.loaded = true;
        },

        async loadIgStats() {
            try {
                const res = await fetch('api/social_territorial.php?action=ig_stats');
                const json = await res.json();
                if (json.success) {
                    this.stats = json.data;
                    this.totalPosts = json.data.total_posts;
                    this.categoryCounts = json.data.categorias || {};

                    // Build active categories from data
                    const cats = Object.keys(json.data.por_categoria || {});
                    this.activeCategories = [...cats];
                }
            } catch (e) { console.error(e); }
        },

        async loadIgGeo() {
            try {
                const res = await fetch('api/social_territorial.php?action=ig_geo');
                const json = await res.json();
                if (json.success) {
                    this.geoData = json.data;
                }
            } catch (e) { console.error(e); }
        },

        async loadCategories() {
            try {
                const res = await fetch('api/social_territorial.php?action=ig_categories');
                const json = await res.json();
                if (json.success) {
                    this.topUbicaciones = Object.entries(json.data.top_ubicaciones || {}).slice(0, 15);
                    this.renderCategoryChart(json.data.categorias);
                }
            } catch (e) { console.error(e); }
        },

        async loadTimeline() {
            try {
                const res = await fetch('api/social_territorial.php?action=ig_timeline');
                const json = await res.json();
                if (json.success) {
                    this.timelineData = json.data.timeline;
                    this.postsRecientes = json.data.recientes || [];
                    this.renderTimelineChart(json.data.timeline);
                }
            } catch (e) { console.error(e); }
        },

        async loadMenciones() {
            try {
                const res = await fetch('api/social_crm.php?action=instagram_stats');
                const json = await res.json();
                if (json.success) this.mencionesStats = json.data;
            } catch (e) { console.error(e); }
        },

        async loadFb() {
            try {
                const res = await fetch('api/social_territorial.php?action=fb_stats');
                const json = await res.json();
                if (json.success) {
                    this.fbConfigured = json.data.configured;
                    this.fbStats = json.data;
                    if (json.data.configured) {
                        this.$nextTick(() => this.renderFbChart(json.data.por_mes));
                    }
                }
            } catch (e) { console.error(e); }
        },

        toggleCategory(cat) {
            const idx = this.activeCategories.indexOf(cat);
            if (idx >= 0) this.activeCategories.splice(idx, 1);
            else this.activeCategories.push(cat);
            this.updateMapMarkers();
        },

        initMap() {
            if (this.map) { this.map.invalidateSize(); return; }
            if (!this.geoData) return;

            this.map = L.map('social-map').setView([3.45, -76.53], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 18,
            }).addTo(this.map);

            this.markerCluster = L.markerClusterGroup({
                maxClusterRadius: 60,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
            });

            this.updateMapMarkers();
            this.map.addLayer(this.markerCluster);
        },

        updateMapMarkers() {
            if (!this.markerCluster || !this.geoData) return;
            this.markerCluster.clearLayers();

            const features = this.geoData.features || [];
            features.forEach(f => {
                const cat = f.properties.categoria || 'general';
                if (!this.activeCategories.includes(cat)) return;

                const color = this.categoryColors[cat] || '#6b7280';
                const size = Math.max(8, Math.min(20, (f.properties.likes || 0) / 10 + 8));

                const icon = L.divIcon({
                    className: 'category-marker',
                    html: `<div style="width:${size}px;height:${size}px;border-radius:50%;background:${color};opacity:0.85;display:flex;align-items:center;justify-content:center;"><span style="color:white;font-size:8px;font-weight:bold;">${(f.properties.likes || 0) > 0 ? '❤' : ''}</span></div>`,
                    iconSize: [size, size],
                    iconAnchor: [size/2, size/2],
                });

                const popup = `
                    <strong>${f.properties.ubicacion || 'Sin ubicación'}</strong>
                    <span style="display:inline-block;padding:1px 6px;border-radius:4px;background:${color};color:white;font-size:10px;margin:4px 0;">${cat}</span>
                    <p style="margin:4px 0;">${f.properties.texto || ''}</p>
                    <div style="display:flex;gap:12px;font-size:11px;color:#666;margin-top:4px;">
                        <span>❤ ${f.properties.likes}</span>
                        <span>💬 ${f.properties.comentarios}</span>
                        <span>📅 ${f.properties.fecha || ''}</span>
                    </div>
                    ${f.properties.url ? `<a href="${f.properties.url}" target="_blank" style="display:inline-block;margin-top:4px;font-size:11px;color:#2563eb;">Ver en Instagram →</a>` : ''}
                `;

                const marker = L.marker([f.geometry.coordinates[1], f.geometry.coordinates[0]], { icon })
                    .bindPopup(popup);
                this.markerCluster.addLayer(marker);
            });
        },

        renderCategoryChart(categorias) {
            this.$nextTick(() => {
                const canvas = document.getElementById('chart-categorias');
                if (!canvas) return;
                if (this.charts.categorias) this.charts.categorias.destroy();

                const labels = Object.keys(categorias);
                const data = Object.values(categorias).map(c => c.total);
                const colors = labels.map(l => this.categoryColors[l] || '#6b7280');

                this.charts.categorias = new Chart(canvas, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Posts', data, backgroundColor: colors, borderRadius: 4 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });

                // Engagement chart
                const canvas2 = document.getElementById('chart-engagement');
                if (!canvas2) return;
                if (this.charts.engagement) this.charts.engagement.destroy();

                const engData = Object.values(categorias).map(c => c.total > 0 ? ((c.likes + c.comments) / c.total).toFixed(1) : 0);

                this.charts.engagement = new Chart(canvas2, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Engagement per post', data: engData, backgroundColor: '#3b82f6', borderRadius: 4 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } } }
                });
            });
        },

        renderTimelineChart(timeline) {
            this.$nextTick(() => {
                const canvas = document.getElementById('chart-timeline');
                if (!canvas) return;
                if (this.charts.timeline) this.charts.timeline.destroy();

                const months = Object.keys(timeline).sort();
                const posts = months.map(m => timeline[m].total);
                const likes = months.map(m => timeline[m].likes);

                this.charts.timeline = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [
                            { label: 'Posts', data: posts, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3, yAxisID: 'y' },
                            { label: 'Likes', data: likes, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.3, yAxisID: 'y1' },
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: { beginAtZero: true, position: 'left', ticks: { stepSize: 1 } },
                            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
                        }
                    }
                });
            });
        },

        renderFbChart(porMes) {
            this.$nextTick(() => {
                const canvas = document.getElementById('chart-fb');
                if (!canvas || !porMes || porMes.length === 0) return;
                if (this.charts.fb) this.charts.fb.destroy();

                const months = porMes.map(m => m.mes).reverse();
                const posts = porMes.map(m => parseInt(m.total)).reverse();
                const likes = porMes.map(m => parseInt(m.likes)).reverse();

                this.charts.fb = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [
                            { label: 'Posts', data: posts, backgroundColor: '#3b82f6', borderRadius: 4 },
                            { label: 'Likes', data: likes, backgroundColor: '#ef4444', borderRadius: 4 },
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            });
        },

        mostrarRelevantes() {
            return this.stats.relevantes || '—';
        },

        formatear(n) {
            return parseInt(n).toLocaleString('es-CO');
        }
    }
}
</script>
