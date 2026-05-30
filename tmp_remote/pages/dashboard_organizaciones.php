<?php
/**
 * Dashboard Organizaciones - Mapa con 2 capas
 * Capa 1: Polígonos de barrios (solo cuando se filtra)
 * Capa 2: Circunferencias de organizaciones (siempre visible)
 */
$campanaId = intval($_GET['campana_id'] ?? ($_SESSION['campana_activa'] ?? 0));
$isIncluded = true;
?>
<style>
.dashboard-org { font-family: 'Outfit', sans-serif; }
#map-org { height: 600px; border-radius: 1.5rem; }
</style>

<div class="dashboard-org space-y-6 pb-20" x-data="orgDashboard()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-secondary rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 13l5.447-2.724A1 1 0 0021 16.382v-10.764a1 1 0 00-.553-.894l-4.894-2.447L12 2"></path></svg>
            </div>
            <div>
                <h1 class="text-2xl font-black text-primary">Organizaciones <span class="text-secondary">Territorial</span></h1>
                <p class="text-sm text-gray-400">Polígonos + Circunferencias</p>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <p class="text-[10px] text-gray-400 uppercase tracking-widest">Organizaciones</p>
                <p class="text-3xl font-black text-primary" x-text="stats.total">0</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-gray-400 uppercase tracking-widest">Votos</p>
                <p class="text-3xl font-black text-secondary" x-text="formatNum(stats.votos)">0</p>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap items-center gap-3 bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <select x-model="filters.departamento" @change="loadMunicipios()" class="px-4 py-2.5 bg-gray-50 rounded-xl font-bold text-sm border-0 focus:ring-2 focus:ring-secondary">
            <option value="">Departamento</option>
            <template x-for="d in dropdowns.deptos" :key="d"><option :value="d" x-text="d"></option></template>
        </select>
        
        <select x-model="filters.municipio" @change="loadTerritorios()" :disabled="!filters.departamento" class="px-4 py-2.5 bg-gray-50 rounded-xl font-bold text-sm border-0 focus:ring-2 focus:ring-secondary disabled:opacity-50">
            <option value="">Municipio</option>
            <template x-for="m in dropdowns.municipios" :key="m"><option :value="m" x-text="m"></option></template>
        </select>
        
        <select x-model="filters.territorio" @change="loadBarrios()" :disabled="!filters.municipio" class="px-4 py-2.5 bg-gray-50 rounded-xl font-bold text-sm border-0 focus:ring-2 focus:ring-secondary disabled:opacity-50">
            <option value="">Comuna / Sector</option>
            <template x-for="t in dropdowns.territorios" :key="t"><option :value="t" x-text="t"></option></template>
        </select>
        
        <select x-model="filters.barrio" @change="updateMap()" :disabled="!filters.territorio" class="px-4 py-2.5 bg-gray-50 rounded-xl font-bold text-sm border-0 focus:ring-2 focus:ring-secondary disabled:opacity-50">
            <option value="">Barrio</option>
            <template x-for="b in dropdowns.barrios" :key="b"><option :value="b" x-text="b"></option></template>
        </select>

        <button @click="resetFilters()" class="p-2.5 bg-gray-100 rounded-xl text-gray-500 hover:bg-gray-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        </button>
    </div>

    <!-- Mapa -->
    <div class="bg-white rounded-3xl shadow-lg overflow-hidden relative">
        <div id="map-org"></div>
        
        <!-- Leyenda -->
        <div class="absolute bottom-4 left-4 z-[1000] bg-white/95 backdrop-blur-sm rounded-2xl p-4 shadow-lg">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tipo</p>
            <div class="space-y-1 text-xs font-bold">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span> JAC</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-500"></span> Deportivo</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-pink-500"></span> Cultural</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500"></span> Ambiental</div>
            </div>
            <template x-if="filters.territorio">
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Polígonos</p>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-[#FF00FF] opacity-50"></span> Urbano</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-[#ae9454] opacity-50"></span> Rural</div>
                </div>
            </template>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="absolute inset-0 bg-white/80 flex items-center justify-center z-[1000]">
            <div class="text-center">
                <div class="w-10 h-10 border-4 border-gray-100 border-t-secondary rounded-full animate-spin mx-auto"></div>
                <p class="text-sm font-bold text-gray-500 mt-2">Cargando...</p>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const API_TERRIT_GEO = 'api_territorios_geojson.php';

document.addEventListener('alpine:init', () => {
    Alpine.data('orgDashboard', () => ({
        campanaId: <?= $campanaId ?>,
        loading: true,
        mapInstance: null,
        polygonsLayer: null,
        circlesLayer: null,
        
        filters: { departamento: 'VALLE DEL CAUCA', municipio: '', territorio: '', barrio: '' },
        dropdowns: { deptos: [], municipios: [], territorios: [], barrios: [] },
        stats: { total: 0, votos: 0 },
        
        colors: { 'JAC': '#3b82f6', 'Deporte': '#f97316', 'Cultura': '#ec4899', 'Ambiente': '#10b981', 'Social': '#8b5cf6', 'Otro': '#64748b' },
        
        init() {
            this.initMap();
            this.loadDepartamentos();
            this.loadOrganizaciones();
        },
        
        formatNum(n) { return new Intl.NumberFormat('es-CO').format(n || 0); },
        
        initMap() {
            this.mapInstance = L.map('map-org', { zoomControl: true })
                .setView([3.4516, -76.5320], 10);
            
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(this.mapInstance);
            
            this.polygonsLayer = L.layerGroup().addTo(this.mapInstance);
            this.circlesLayer = L.layerGroup().addTo(this.mapInstance);
        },
        
        async loadDepartamentos() {
            const r = await fetch('api/territorios.php?accion=departamentos');
            const d = await r.json();
            if (d.success) this.dropdowns.deptos = d.data;
        },
        
        async loadMunicipios() {
            this.resetLowerFilters();
            if (!this.filters.departamento) return;
            
            const r = await fetch('api/territorios.php?accion=municipios&departamento=' + encodeURIComponent(this.filters.departamento));
            const d = await r.json();
            if (d.success) this.dropdowns.municipios = [...new Set(d.data.map(m => typeof m === 'object' ? m.municipio : m))];
        },
        
        async loadTerritorios() {
            this.filters.territorio = '';
            this.filters.barrio = '';
            this.dropdowns.territorios = [];
            this.dropdowns.barrios = [];
            if (!this.filters.municipio) return;
            
            const r = await fetch('api/territorios.php?accion=tipos_territorio&departamento=' + encodeURIComponent(this.filters.departamento) + '&municipio=' + encodeURIComponent(this.filters.municipio));
            const d = await r.json();
            if (d.success) {
                this.dropdowns.territorios = d.data;
                this.updateMap();
            }
        },
        
        async loadBarrios() {
            this.filters.barrio = '';
            this.dropdowns.barrios = [];
            if (!this.filters.territorio) return;
            
            const r = await fetch('api/territorios.php?accion=territorios&departamento=' + encodeURIComponent(this.filters.departamento) + '&municipio=' + encodeURIComponent(this.filters.municipio) + '&tipo_territorio=' + encodeURIComponent(this.filters.territorio));
            const d = await r.json();
            if (d.success) this.dropdowns.barrios = d.data;
            this.updateMap();
        },
        
        resetLowerFilters() {
            this.filters.municipio = '';
            this.filters.territorio = '';
            this.filters.barrio = '';
            this.dropdowns.municipios = [];
            this.dropdowns.territorios = [];
            this.dropdowns.barrios = [];
            this.polygonsLayer.clearLayers();
        },
        
        async updateMap() {
            this.loading = true;
            
            // Cargar polígonos solo si hay filtro de territorio
            this.polygonsLayer.clearLayers();
            if (this.filters.municipio) {
                await this.loadPolygons();
            }
            
            // Siempre cargar organizaciones
            await this.loadOrganizaciones();
            
            this.loading = false;
        },
        
        async loadPolygons() {
            try {
                const params = new URLSearchParams({
                    municipio: this.filters.municipio,
                    territorio: this.filters.territorio || undefined,
                    tipo: this.filters.barrio ? undefined : (this.filters.territorio || undefined)
                });
                
                const r = await fetch(API_TERRIT_GEO + '?' + params.toString());
                const data = await r.json();
                
                if (data.features && data.features.length > 0) {
                    L.geoJSON(data, {
                        style: feat => ({
                            fillColor: feat.properties.Tipo_territorio === 'Urbano' ? '#FF00FF' : '#ae9454',
                            weight: 1,
                            color: 'white',
                            dashArray: '3',
                            fillOpacity: 0.2
                        }),
                        onEachFeature: (feat, layer) => {
                            layer.bindPopup('<strong>' + feat.properties.barrio + '</strong><br><span class="text-xs">' + feat.properties.Tipo_territorio + '</span>');
                        }
                    }).addTo(this.polygonsLayer);
                    
                    const bounds = this.polygonsLayer.getLayers()[0]?.getBounds();
                    if (bounds) this.mapInstance.fitBounds(bounds, { padding: [30, 30] });
                }
            } catch(e) { console.error('Error polígonos:', e); }
        },
        
        async loadOrganizaciones() {
            if (!this.campanaId) return;
            
            let url = 'api/organizaciones.php?action=list&campana_id=' + this.campanaId;
            if (this.filters.municipio) url += '&municipio=' + encodeURIComponent(this.filters.municipio);
            
            try {
                const r = await fetch(url);
                const d = await r.json();
                if (d.success) this.renderCircles(d.data);
            } catch(e) { console.error(e); }
        },
        
        renderCircles(data) {
            this.circlesLayer.clearLayers();
            
            if (!data || data.length === 0) {
                this.stats.total = 0;
                this.stats.votos = 0;
                return;
            }
            
            let totalVotos = 0;
            data.forEach(o => totalVotos += parseInt(o.votos_comprometidos || 0));
            this.stats.votos = totalVotos;
            this.stats.total = data.length;
            
            const maxVotos = Math.max(...data.map(o => parseInt(o.votos_comprometidos || 1)), 1);
            const scaleFactor = 200;
            
            data.forEach(org => {
                const coords = this.getCenter(org.territorio_valle_nombre, org.sector, org.municipio);
                const votos = parseInt(org.votos_comprometidos || 1);
                const radius = Math.sqrt(votos / maxVotos) * scaleFactor + 30;
                const color = this.colors[org.tipo_organizacion] || '#64748b';
                
                const circle = L.circleMarker([coords.lat, coords.lng], {
                    radius: radius,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.7
                }).bindPopup(
                    '<div class="p-2 min-w-[180px]">' +
                        '<p class="text-xs font-bold text-gray-500 uppercase">' + (org.tipo_organizacion || 'Organización') + '</p>' +
                        '<h3 class="text-base font-black text-gray-800">' + (org.nombre_jac || 'Sin nombre') + '</h3>' +
                        '<p class="text-sm text-gray-600">' + (org.municipio || '') + ' - ' + (org.territorio_valle_nombre || org.sector || '') + '</p>' +
                        '<div class="mt-2 grid grid-cols-2 gap-2 text-sm">' +
                            '<div><span class="text-xs text-gray-400">Presidente</span><p class="font-bold">' + (org.presidente || 'N/A') + '</p></div>' +
                            '<div class="text-right"><span class="text-xs text-gray-400">Votos</span><p class="font-black text-orange-600">' + votos + '</p></div>' +
                        '</div>' +
                    '</div>'
                );
                
                circle.addTo(this.circlesLayer);
            });
            
            if (this.circlesLayer.getLayers().length > 0 && !this.filters.territorio) {
                const bounds = L.featureGroup(this.circlesLayer.getLayers()).getBounds();
                this.mapInstance.fitBounds(bounds, { padding: [30, 30] });
            }
        },
        
        getCenter(territorio, sector, municipio) {
            const mpio = (municipio || 'CALI').toUpperCase();
            const terr = (territorio || sector || '').toUpperCase();
            
            const terrCoords = {
                'COMUNA 1': { lat: 3.4637, lng: -76.5420 },
                'COMUNA 2': { lat: 3.4695, lng: -76.5225 },
                'COMUNA 3': { lat: 3.4680, lng: -76.5010 },
                'COMUNA 4': { lat: 3.4560, lng: -76.4810 },
                'COMUNA 5': { lat: 3.4455, lng: -76.4620 },
                'COMUNA 6': { lat: 3.4320, lng: -76.4420 },
                'COMUNA 7': { lat: 3.4200, lng: -76.5020 },
                'COMUNA 8': { lat: 3.4150, lng: -76.5220 },
                'COMUNA 9': { lat: 3.4000, lng: -76.5400 },
                'COMUNA 10': { lat: 3.4850, lng: -76.5500 },
                'COMUNA 11': { lat: 3.4950, lng: -76.5300 },
                'COMUNA 12': { lat: 3.4880, lng: -76.5100 },
                'COMUNA 13': { lat: 3.4750, lng: -76.4900 },
                'COMUNA 14': { lat: 3.4600, lng: -76.4700 },
                'COMUNA 15': { lat: 3.4400, lng: -76.4500 },
                'COMUNA 16': { lat: 3.4250, lng: -76.4300 },
                'RURAL': { lat: 3.5200, lng: -76.3000 }
            };
            
            if (terrCoords[terr]) return terrCoords[terr];
            
            const mpioCoords = {
                'CALI': { lat: 3.4516, lng: -76.5320 },
                'PALMIRA': { lat: 3.5324, lng: -76.2988 },
                'YUMBO': { lat: 3.5933, lng: -76.4267 },
                'GINEBRA': { lat: 3.7186, lng: -76.2198 },
                'PRADERA': { lat: 3.4053, lng: -76.2387 },
                'JAMUNDI': { lat: 3.2414, lng: -76.6423 }
            };
            
            return mpioCoords[mpio] || { lat: 3.4516, lng: -76.5320 };
        },
        
        resetFilters() {
            this.filters = { departamento: 'VALLE DEL CAUCA', municipio: '', territorio: '', barrio: '' };
            this.updateMap();
        }
    }));
});
</script>