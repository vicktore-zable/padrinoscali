<div class="max-w-7xl mx-auto" x-data="biHub()" x-init="init()" x-cloak>
    <!-- Filter bar -->
    <div class="flex items-center justify-between mb-4 bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
        <div class="flex items-center gap-2">
            <i data-lucide="bar-chart-3" class="w-5 h-5 text-primary"></i>
            <h2 class="text-lg font-bold text-gray-900">BI Hub</h2>
            <span class="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Fase 1</span>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <select x-model="periodo" @change="refreshAll()" class="px-2 py-1 border border-gray-200 rounded-lg text-xs">
                <option value="12">12 meses</option>
                <option value="6">6 meses</option>
                <option value="3">3 meses</option>
            </select>
            <button @click="refreshAll()" class="p-1.5 text-gray-400 hover:text-primary rounded hover:bg-gray-100" title="Recargar">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }"></i>
            </button>
        </div>
    </div>

    <!-- Tab bar -->
    <div class="flex items-center gap-1 mb-4 bg-white rounded-xl shadow-sm border border-gray-200 px-3 py-2">
        <button @click="cambiarTab('panorama')" :class="tab == 'panorama' ? 'bg-primary/10 text-primary border-primary/30' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent transition-colors flex items-center gap-1.5">
            <i data-lucide="eye" class="w-3.5 h-3.5"></i> Panorama
        </button>
        <button @click="cambiarTab('territorio')" :class="tab == 'territorio' ? 'bg-primary/10 text-primary border-primary/30' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent transition-colors flex items-center gap-1.5">
            <i data-lucide="globe" class="w-3.5 h-3.5"></i> Territorio
        </button>
        <button @click="cambiarTab('red')" :class="tab == 'red' ? 'bg-primary/10 text-primary border-primary/30' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent transition-colors flex items-center gap-1.5">
            <i data-lucide="users" class="w-3.5 h-3.5"></i> Red Social
        </button>
    </div>

    <!-- ════════════════ PANORAMA ════════════════ -->
    <template x-if="tab == 'panorama'">
        <div>
            <!-- KPI row -->
            <div class="grid grid-cols-3 lg:grid-cols-7 gap-2 mb-4">
                <template x-for="(k, key) in ['colaboradores','lideres','activos','inactivos','donaciones_periodo','eventos_periodo','whatsapp_tasa']" :key="key">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider" x-text="{
                            colaboradores: 'Colaboradores',
                            lideres: 'Líderes',
                            activos: 'Activos',
                            inactivos: 'Inactivos',
                            donaciones_periodo: 'Donaciones',
                            eventos_periodo: 'Eventos',
                            whatsapp_tasa: 'WhatsApp'
                        }[k]"></p>
                        <p class="text-lg font-bold text-gray-900 mt-0.5" x-text="formatNum(data.panorama?.kpi?.[k]?.valor ?? 0)"></p>
                        <p class="text-[10px] mt-0.5" :class="(data.panorama?.kpi?.[k]?.tendencia ?? 'stable') == 'up' ? 'text-green-600' : (data.panorama?.kpi?.[k]?.tendencia ?? 'stable') == 'down' ? 'text-red-600' : 'text-gray-400'">
                            <template x-if="data.panorama?.kpi?.[k]?.vs_periodo !== undefined">
                                <span x-text="(data.panorama.kpi[k].vs_periodo > 0 ? '▲ +' : data.panorama.kpi[k].vs_periodo < 0 ? '▼ ' : '― ') + data.panorama.kpi[k].vs_periodo + '%'"></span>
                            </template>
                        </p>
                    </div>
                </template>
            </div>

            <!-- Alertas -->
            <template x-if="data.panorama?.alertas?.length">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 mb-4">
                    <p class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5"><i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-500"></i> Alertas</p>
                    <div class="space-y-1">
                        <template x-for="a in data.panorama.alertas" :key="a.mensaje">
                            <div class="flex items-center gap-2 text-xs" :class="a.tipo == 'warning' ? 'text-amber-700' : 'text-green-700'">
                                <i :data-lucide="a.tipo == 'warning' ? 'alert-circle' : 'check-circle'" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                <span x-text="a.mensaje"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Cards: Rol + Estado + Género -->
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3">
                    <h4 class="text-xs font-semibold text-gray-900 mb-2">Rol</h4>
                    <div class="space-y-1">
                        <template x-for="d in data.panorama?.distribuciones?.rol" :key="d.label">
                            <div class="flex items-center justify-between text-xs py-0.5 border-b border-gray-50 last:border-0">
                                <span class="text-gray-600 truncate" x-text="d.label || 'Sin rol'"></span>
                                <span class="font-semibold text-gray-900 ml-2" x-text="formatNum(d.total)"></span>
                            </div>
                        </template>
                        <p x-show="!data.panorama?.distribuciones?.rol?.length" class="text-xs text-gray-400 text-center py-2">Sin datos</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3">
                    <h4 class="text-xs font-semibold text-gray-900 mb-2">Estado</h4>
                    <div class="space-y-1">
                        <template x-for="d in data.panorama?.distribuciones?.estado" :key="d.label">
                            <div class="flex items-center justify-between text-xs py-0.5 border-b border-gray-50 last:border-0">
                                <span class="text-gray-600 truncate" x-text="d.label"></span>
                                <span class="font-semibold text-gray-900 ml-2" x-text="formatNum(d.total)"></span>
                            </div>
                        </template>
                        <p x-show="!data.panorama?.distribuciones?.estado?.length" class="text-xs text-gray-400 text-center py-2">Sin datos</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3">
                    <h4 class="text-xs font-semibold text-gray-900 mb-2">Género</h4>
                    <div class="space-y-1">
                        <template x-for="d in data.panorama?.distribuciones?.genero" :key="d.label">
                            <div class="flex items-center justify-between text-xs py-0.5 border-b border-gray-50 last:border-0">
                                <span class="text-gray-600 truncate" x-text="d.label"></span>
                                <span class="font-semibold text-gray-900 ml-2" x-text="formatNum(d.total)"></span>
                            </div>
                        </template>
                        <p x-show="!data.panorama?.distribuciones?.genero?.length" class="text-xs text-gray-400 text-center py-2">Sin datos</p>
                    </div>
                </div>
            </div>

            <!-- Cards: Nivel Participación -->
            <template x-if="data.panorama?.distribuciones?.nivel_participacion?.length">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 mb-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-2">Nivel de Participación</h4>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="d in data.panorama.distribuciones.nivel_participacion" :key="d.label">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                <span x-text="d.label"></span>
                                <span class="font-bold" x-text="formatNum(d.total)"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Charts row: Tendencia + Top Territorios + Top Barrios -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-3">Tendencia Colaboradores (12m)</h4>
                    <div class="relative" style="height:200px">
                        <canvas x-ref="chartTendencia" class="absolute inset-0 w-full h-full"></canvas>
                        <div x-show="!data.panorama?.tendencia?.length" class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">Sin datos</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-3">Top 10 Territorios</h4>
                    <div class="relative" style="height:200px">
                        <canvas x-ref="chartTopTerritorios" class="absolute inset-0 w-full h-full"></canvas>
                        <div x-show="!data.panorama?.topTerritorios?.length" class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">Sin datos</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-3">Top 10 Barrios</h4>
                    <div class="relative" style="height:200px">
                        <canvas x-ref="chartTopBarrios" class="absolute inset-0 w-full h-full"></canvas>
                        <div x-show="!data.panorama?.topBarrios?.length" class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">Sin datos</div>
                    </div>
                </div>
            </div>

            <!-- Top 10 Líderes Directos -->
            <template x-if="data.panorama?.topLideres?.length">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-3">Top 10 Líderes Directos</h4>
                    <div class="relative" style="height:200px">
                        <canvas x-ref="chartTopLideresPanorama" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- ════════════════ TERRITORIO ════════════════ -->
    <template x-if="tab == 'territorio'">
        <div>
            <!-- KPI row -->
            <div class="grid grid-cols-4 gap-2 mb-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Comunas</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5" x-text="data.territorio?.kpis?.comunas ?? 0"></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Barrios</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5" x-text="data.territorio?.kpis?.barrios ?? 0"></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Urbano</p>
                    <p class="text-lg font-bold text-green-600 mt-0.5" x-text="data.territorio?.kpis?.urbano_pct ?? 0 + '%'"></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Rural</p>
                    <p class="text-lg font-bold text-amber-600 mt-0.5" x-text="data.territorio?.kpis?.rural_pct ?? 0 + '%'"></p>
                </div>
            </div>

            <!-- Mapa único con capas toggle -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
                <div class="p-3 border-b border-gray-100 flex items-center gap-3 flex-wrap">
                    <template x-for="(capa, key) in {
                        viven: {label:'Dónde viven',color:'#3b82f6'},
                        zonas: {label:'Zonas trabajo',color:'#22c55e'},
                        actividad: {label:'Actividad',color:'#8b5cf6'},
                        compromisos: {label:'Compromisos',color:'#8b5cf6'},
                        eventos: {label:'Eventos',color:'#f97316'},
                        instagram: {label:'Instagram',color:'#ec4899'}
                    }" :key="key">
                        <label class="flex items-center gap-1.5 text-xs cursor-pointer select-none">
                            <input type="checkbox" :checked="capasActivas[key]" @change="toggleCapa(key)" class="rounded">
                            <span class="w-2 h-2 rounded-full inline-block" :style="'background:'+capa.color"></span>
                            <span x-text="capa.label"></span>
                        </label>
                    </template>
                    <button @click="recargarTerritorio()" class="ml-auto p-1 text-gray-400 hover:text-primary rounded hover:bg-gray-100" title="Recargar">
                        <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                    </button>
                </div>
                <div id="mapaTerritorio" style="height:400px" class="bg-gray-50 relative">
                    <div x-show="!mapaListo" class="absolute inset-0 bg-gray-100 animate-pulse flex items-center justify-center z-[999]">
                        <div class="text-center">
                            <i data-lucide="loader-2" class="w-6 h-6 text-gray-400 mx-auto animate-spin"></i>
                            <p class="text-xs text-gray-400 mt-1">Cargando mapa...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brechas de cobertura -->
            <template x-if="data.territorio?.brechas?.length">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-3">Brechas de Cobertura</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead><tr class="text-left text-gray-500 border-b border-gray-100">
                                <th class="py-2 pr-3 font-medium">Territorio</th>
                                <th class="py-2 pr-3 font-medium">Tipo</th>
                                <th class="py-2 pr-3 font-medium text-right">Colab</th>
                                <th class="py-2 pr-3 font-medium text-right">Líderes</th>
                                <th class="py-2 pr-3 font-medium text-right">Con zona</th>
                                <th class="py-2 pr-3 font-medium text-right">Sin zona</th>
                                <th class="py-2 pr-3 font-medium text-right">Cobertura</th>
                            </tr></thead>
                            <tbody>
                                <template x-for="b in data.territorio.brechas" :key="b.territorio">
                                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                                        <td class="py-2 pr-3 font-medium text-gray-900" x-text="b.territorio"></td>
                                        <td class="py-2 pr-3 text-gray-500" x-text="b.tipo"></td>
                                        <td class="py-2 pr-3 text-right" x-text="b.colaboradores"></td>
                                        <td class="py-2 pr-3 text-right" x-text="b.lideres"></td>
                                        <td class="py-2 pr-3 text-right text-green-600" x-text="b.con_zona"></td>
                                        <td class="py-2 pr-3 text-right" :class="b.sin_zona > 0 ? 'text-red-600 font-semibold' : 'text-gray-500'" x-text="b.sin_zona"></td>
                                        <td class="py-2 pr-3 text-right">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium"
                                                  :class="b.cobertura_pct >= 80 ? 'bg-green-100 text-green-700' : b.cobertura_pct >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'"
                                                  x-text="b.cobertura_pct + '%'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- ════════════════ RED SOCIAL ════════════════ -->
    <template x-if="tab == 'red'">
        <div>
            <!-- KPI row -->
            <div class="grid grid-cols-5 gap-2 mb-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Líderes</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5" x-text="data.red?.kpis?.lideres ?? 0"></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Seguidores</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5" x-text="data.red?.kpis?.seguidores_totales ?? 0"></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Seg/Líder</p>
                    <p class="text-lg font-bold text-primary mt-0.5" x-text="data.red?.kpis?.seguidores_por_lider ?? 0"></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Profundidad</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5" x-text="data.red?.kpis?.profundidad_max ?? 0"></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
                    <p class="text-[10px] text-gray-500 uppercase">Rotación</p>
                    <p class="text-lg font-bold text-amber-600 mt-0.5" x-text="(data.red?.kpis?.rotacion_mensual ?? 0) + ' cambios'"></p>
                </div>
            </div>

            <!-- Charts row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-3">Distribución líderes por rango</h4>
                    <div class="relative" style="height:220px">
                        <canvas x-ref="chartDistribucion" class="absolute inset-0 w-full h-full"></canvas>
                        <div x-show="!data.red?.distribucion?.length" class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">Sin datos</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-3">Top 15 Padrinos</h4>
                    <div class="relative" style="height:220px">
                        <canvas x-ref="chartTopLideres" class="absolute inset-0 w-full h-full"></canvas>
                        <div x-show="!data.red?.topLideres?.length" class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">Sin datos</div>
                    </div>
                </div>
            </div>

            <!-- Tabla top líderes -->
            <template x-if="data.red?.topLideres?.length">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h4 class="text-xs font-semibold text-gray-900 mb-3">Detalle Padrinos</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead><tr class="text-left text-gray-500 border-b border-gray-100">
                                <th class="py-2 pr-3 font-medium">#</th>
                                <th class="py-2 pr-3 font-medium">Nombre</th>
                                <th class="py-2 pr-3 font-medium">Municipio</th>
                                <th class="py-2 pr-3 font-medium text-right">Seguidores</th>
                                <th class="py-2 pr-3 font-medium text-right">Activos</th>
                                <th class="py-2 pr-3 font-medium text-right">% Activos</th>
                                <th class="py-2 pr-3 font-medium text-right">Participación</th>
                            </tr></thead>
                            <tbody>
                                <template x-for="(l, i) in data.red.topLideres" :key="l.id">
                                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                                        <td class="py-2 pr-3 text-gray-400" x-text="i+1"></td>
                                        <td class="py-2 pr-3 font-medium text-gray-900">
                                            <a :href="'?page=colaborador_detalle&id='+l.id" class="text-primary hover:underline" x-text="l.nombres+' '+l.apellidos"></a>
                                        </td>
                                        <td class="py-2 pr-3 text-gray-500" x-text="l.municipio || '—'"></td>
                                        <td class="py-2 pr-3 text-right font-medium" x-text="l.seguidores"></td>
                                        <td class="py-2 pr-3 text-right text-green-600" x-text="l.activos"></td>
                                        <td class="py-2 pr-3 text-right">
                                            <span x-text="l.seguidores > 0 ? Math.round(l.activos / l.seguidores * 100) + '%' : '—'"></span>
                                        </td>
                                        <td class="py-2 pr-3 text-right" x-text="l.participacion_promedio || '—'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>

<script>
function biHub() {
    return {
        loading: false,
        tab: 'panorama',
        periodo: '12',
        data: { panorama: null, territorio: null, red: null },

        // === MAPA TERRITORIO ===
        mapaTerritorio: null,
        capasTerritorio: {},
        mapaListo: false,
        capasActivas: { viven: true, zonas: false, actividad: false, compromisos: false, eventos: false, instagram: false },

        async init() {
            await this.refreshAll();
            setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 100);
        },

        async refreshAll() {
            this.loading = true;
            try {
                await Promise.all([
                    this.loadPanorama(),
                    this.loadTerritorio(),
                    this.loadRed(),
                ]);
            } catch(e) { console.error('BI Hub error:', e); }
            this.loading = false;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
                if (this.tab == 'panorama') this.initGraficosPanorama();
                if (this.tab == 'territorio') this.initMapaTerritorio();
                if (this.tab == 'red') this.initGraficosRed();
            });
        },

        cambiarTab(t) {
            this.tab = t;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
                if (t == 'panorama') this.initGraficosPanorama();
                if (t == 'territorio') this.initMapaTerritorio();
                if (t == 'red') this.initGraficosRed();
            });
        },

        // === API LOADERS ===
        async loadPanorama() {
            const r = await fetch('api/bi.php?action=panorama');
            const j = await r.json();
            if (j.success) this.data.panorama = j.data;
        },

        async loadTerritorio() {
            const r = await fetch('api/bi.php?action=territorio');
            const j = await r.json();
            if (j.success) this.data.territorio = j.data;
        },

        async loadRed() {
            const r = await fetch('api/bi.php?action=red');
            const j = await r.json();
            if (j.success) this.data.red = j.data;
        },

        // === GRÁFICOS PANORAMA ===
        initGraficosPanorama() {
            this.initTendencia();
            this.initTopTerritorios();
            this.initTopBarrios();
            this.initTopLideresPanorama();
        },

        initTendencia() {
            if (typeof Chart === 'undefined') return;
            const canvas = this.$refs.chartTendencia;
            if (!canvas) return;
            if (this._chartTendencia) { this._chartTendencia.destroy(); this._chartTendencia = null; }
            const datos = this.data.panorama?.tendencia;
            if (!datos || !datos.length) return;
            this._chartTendencia = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: datos.map(d => { const p = d.mes.split('-'); return p[1] + '/' + p[0].slice(2); }),
                    datasets: [{
                        label: 'Colaboradores',
                        data: datos.map(d => +d.total),
                        borderColor: '#2563eb',
                        backgroundColor: '#2563eb15',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 2,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, animation: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 9 } } }, x: { grid: { display: false }, ticks: { font: { size: 8 } } } }
                }
            });
        },

        initTopTerritorios() {
            if (typeof Chart === 'undefined') return;
            const canvas = this.$refs.chartTopTerritorios;
            if (!canvas) return;
            if (this._chartTopTerritorios) { this._chartTopTerritorios.destroy(); this._chartTopTerritorios = null; }
            const datos = this.data.panorama?.topTerritorios;
            if (!datos || !datos.length) return;
            this._chartTopTerritorios = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: datos.map(d => d.territorio),
                    datasets: [{
                        label: 'Total',
                        data: datos.map(d => +d.total),
                        backgroundColor: '#22c55e40',
                        borderColor: '#22c55e',
                        borderWidth: 1,
                        borderRadius: 3,
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, animation: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 9 } } }, y: { grid: { display: false }, ticks: { font: { size: 8 } } } }
                }
            });
        },

        initTopBarrios() {
            if (typeof Chart === 'undefined') return;
            const canvas = this.$refs.chartTopBarrios;
            if (!canvas) return;
            if (this._chartTopBarrios) { this._chartTopBarrios.destroy(); this._chartTopBarrios = null; }
            const datos = this.data.panorama?.topBarrios;
            if (!datos || !datos.length) return;
            this._chartTopBarrios = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: datos.map(d => d.barrio),
                    datasets: [{
                        label: 'Total',
                        data: datos.map(d => +d.total),
                        backgroundColor: '#f59e0b40',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                        borderRadius: 3,
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, animation: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 9 } } }, y: { grid: { display: false }, ticks: { font: { size: 8 } } } }
                }
            });
        },

        initTopLideresPanorama() {
            if (typeof Chart === 'undefined') return;
            const canvas = this.$refs.chartTopLideresPanorama;
            if (!canvas) return;
            if (this._chartTopLideresPanorama) { this._chartTopLideresPanorama.destroy(); this._chartTopLideresPanorama = null; }
            const datos = this.data.panorama?.topLideres;
            if (!datos || !datos.length) return;
            this._chartTopLideresPanorama = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: datos.map(d => (d.nombres || '') + ' ' + (d.apellidos || '')),
                    datasets: [{
                        label: 'Seguidores',
                        data: datos.map(d => +d.seguidores),
                        backgroundColor: '#8b5cf640',
                        borderColor: '#8b5cf6',
                        borderWidth: 1,
                        borderRadius: 3,
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, animation: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 9 } } }, y: { grid: { display: false }, ticks: { font: { size: 8 } } } }
                }
            });
        },

        // === MAPA TERRITORIO ===
        initMapaTerritorio() {
            const el = document.getElementById('mapaTerritorio');
            if (!el || el._leaflet_id) { this.mapaListo = true; return; }
            if (typeof L === 'undefined') return;
            this.mapaTerritorio = L.map('mapaTerritorio', { zoomControl: true }).setView([3.4516, -76.5320], 11);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.mapaTerritorio);
            this.mapaListo = true;
            Object.keys(this.capasActivas).forEach(k => this.toggleCapa(k));
        },

        toggleCapa(key) {
            this.capasActivas[key] = !this.capasActivas[key];
            if (!this.mapaTerritorio || !this.data.territorio?.mapas?.[key]) return;
            if (this.capasTerritorio[key]) {
                this.mapaTerritorio.removeLayer(this.capasTerritorio[key]);
                this.capasTerritorio[key] = null;
                return;
            }
            const geojson = this.data.territorio.mapas[key];
            if (!geojson.features || !geojson.features.length) return;
            this.capasTerritorio[key] = L.geoJSON(geojson, {
                style: (f) => ({ color: f.properties.color, weight: 2, fillColor: f.properties.fillColor, fillOpacity: 0.3 }),
                onEachFeature: (f, l) => {
                    if (f.properties.tooltip) l.bindTooltip(f.properties.tooltip, { sticky: true });
                    if (f.properties.popup) l.bindPopup(f.properties.popup);
                }
            }).addTo(this.mapaTerritorio);
        },

        async recargarTerritorio() {
            await this.loadTerritorio();
            if (this.mapaTerritorio) {
                Object.keys(this.capasTerritorio).forEach(k => {
                    if (this.capasTerritorio[k]) { this.mapaTerritorio.removeLayer(this.capasTerritorio[k]); this.capasTerritorio[k] = null; }
                });
                Object.keys(this.capasActivas).forEach(k => { if (this.capasActivas[k]) this.toggleCapa(k); });
            }
        },

        // === GRÁFICOS RED ===
        initGraficosRed() {
            this.initDistribucion();
            this.initTopLideresChart();
        },

        initDistribucion() {
            if (typeof Chart === 'undefined') return;
            const canvas = this.$refs.chartDistribucion;
            if (!canvas) return;
            if (this._chartDist) { this._chartDist.destroy(); this._chartDist = null; }
            const datos = this.data.red?.distribucion;
            if (!datos || !datos.length) return;
            this._chartDist = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: datos.map(d => d.rango),
                    datasets: [{
                        label: 'Líderes',
                        data: datos.map(d => +d.total),
                        backgroundColor: datos.map(d => {
                            if (d.rango.startsWith('0')) return '#ef444460';
                            if (d.rango.startsWith('1-4')) return '#f59e0b60';
                            return '#22c55e60';
                        }),
                        borderColor: datos.map(d => {
                            if (d.rango.startsWith('0')) return '#ef4444';
                            if (d.rango.startsWith('1-4')) return '#f59e0b';
                            return '#22c55e';
                        }),
                        borderWidth: 1,
                        borderRadius: 3,
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, animation: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 9 } } }, y: { grid: { display: false }, ticks: { font: { size: 8 } } } }
                }
            });
        },

        initTopLideresChart() {
            if (typeof Chart === 'undefined') return;
            const canvas = this.$refs.chartTopLideres;
            if (!canvas) return;
            if (this._chartTop) { this._chartTop.destroy(); this._chartTop = null; }
            const datos = this.data.red?.topLideres;
            if (!datos || !datos.length) return;
            this._chartTop = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: datos.map(d => (d.nombres || '').split(' ')[0] + ' ' + (d.apellidos || '').split(' ')[0]),
                    datasets: [
                        { label: 'Seguidores', data: datos.map(d => +d.seguidores), backgroundColor: '#2563eb60', borderColor: '#2563eb', borderWidth: 1, borderRadius: 2 },
                        { label: 'Activos', data: datos.map(d => +d.activos), backgroundColor: '#22c55e60', borderColor: '#22c55e', borderWidth: 1, borderRadius: 2 },
                    ]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, animation: false,
                    plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 8, font: { size: 8 } } } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 8 } } }, y: { grid: { display: false }, ticks: { font: { size: 7 } } } }
                }
            });
        },

        formatNum(n) {
            if (!n && n !== 0) return '0';
            return Number(n).toLocaleString('es-CO');
        }
    }
}
</script>
