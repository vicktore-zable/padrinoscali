<?php
$campanaId = $_SESSION['campana_activa'] ?? null;
$campanaName = $_SESSION['campana_nombre'] ?? 'Sin campaña';
$pageTitle = 'Zonas de Trabajo Social';
?>
<div x-data="zonasTrabajoPage()" x-init="init()" class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i data-lucide="map-pin" class="w-7 h-7 mr-2 text-fuchsia-600"></i>
                Zonas de Trabajo Social
            </h1>
            <p class="text-gray-600 mt-1">Mapa de zonas donde los líderes realizan trabajo social</p>
        </div>
        <div class="flex gap-2 mt-4 md:mt-0">
            <select x-model="filtroColaborador" @change="cargarGeo()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos los líderes</option>
                <template x-for="c in colaboradores" :key="c.colaborador_id">
                    <option :value="c.colaborador_id" x-text="c.nombre"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div x-show="loading" class="h-[500px] flex items-center justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-fuchsia-600"></div>
                </div>
                <div id="mapaZonasPage" class="h-[500px] w-full" :class="{ 'hidden': loading }"></div>
            </div>
        </div>
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                    Estadísticas
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Líderes con zonas:</span><span class="font-medium" x-text="stats.lideres"></span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Zonas registradas:</span><span class="font-medium" x-text="stats.zonas"></span></div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i data-lucide="palette" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                    Leyenda
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full mr-2" style="background:#6b21a8"></span><span class="text-gray-700">Zona de trabajo social</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function zonasTrabajoPage() {
    return {
        loading: true,
        mapa: null,
        colaboradores: [],
        filtroColaborador: '',
        stats: { lideres: 0, zonas: 0 },

        async init() {
            try {
                await Promise.all([this.cargarColaboradores(), this.cargarGeo()]);
            } catch (e) {
                console.error('Error en init:', e);
            } finally {
                this.loading = false;
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        async cargarColaboradores() {
            try {
                const r = await fetch('api/zonas_trabajo.php?action=colaboradores_con_zonas', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const j = await r.json();
                if (j.success) this.colaboradores = j.data;
            } catch (e) { console.error('Error cargando colaboradores:', e); }
        },

        async cargarGeo() {
            this.loading = true;
            const params = this.filtroColaborador ? 'colaborador_id=' + this.filtroColaborador : '';
            try {
                const url = 'api/zonas_trabajo.php?action=geo' + (params ? '&' + params : '');
                const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const geo = await r.json();
                this.renderMapa(geo);
            } catch (e) { console.error('Error cargando geo:', e); }
            finally { this.loading = false; }
        },

        renderMapa(geo) {
            if (this.mapa) { this.mapa.remove(); this.mapa = null; }
            var container = document.getElementById('mapaZonasPage');
            if (!container) { console.error('No container'); return; }
            if (typeof L === 'undefined') { console.error('Leaflet not loaded'); return; }
            this.mapa = L.map('mapaZonasPage', { zoomControl: true }).setView([3.4516, -76.5320], 12);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap', maxZoom: 18
            }).addTo(this.mapa);
            if (!geo.features || geo.features.length === 0) {
                this.stats = { lideres: 0, zonas: 0 };
                return;
            }
            var colores = ['#6b21a8', '#2563eb', '#059669', '#d97706', '#dc2626', '#0891b2', '#7c3aed', '#db2777'];
            var colabColores = {};
            var idx = 0;
            var lideresSet = new Set();
            geo.features.forEach(function(f) {
                lideresSet.add(f.properties.colaborador_id);
                if (!colabColores[f.properties.colaborador_id]) colabColores[f.properties.colaborador_id] = colores[idx++ % colores.length];
            });
            var layer = L.geoJSON(geo, {
                style: function(f) { return { color: colabColores[f.properties.colaborador_id], weight: 2, fillOpacity: 0.12 }; },
                onEachFeature: function(f, l) {
                    l.bindPopup('<b>' + f.properties.colaborador_nombre + '</b><br>' + f.properties.barrio + '<br>' + f.properties.Territorio + '<br>Impacto: ' + f.properties.impacto_estimado + ' pers.');
                }
            }).addTo(this.mapa);
            this.mapa.fitBounds(layer.getBounds().pad(0.1));
            this.stats = { lideres: lideresSet.size, zonas: geo.features.length };
        }
    };
}
</script>