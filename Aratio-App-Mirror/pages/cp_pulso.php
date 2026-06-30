<div class="max-w-6xl mx-auto" x-data="cpPulso()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">CP — Pulso de Campaña</h2>
            <p class="text-sm text-gray-500 mt-1">Embudo redes → captura → organización → poder</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="scanNow()" :disabled="scanning"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                <svg x-show="!scanning" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span x-show="scanning" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span x-text="scanning ? 'Escaneando...' : 'Escanear ahora'"></span>
            </button>
            <button @click="loadAll()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-gray-400"></i>
            </button>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="p-12 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-sm text-gray-400 mt-2">Cargando embudo...</p>
    </div>

    <template x-if="!loading">
    <div class="space-y-6">

        <!-- Config Alert -->
        <div x-show="!config.configured" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-700">
            ⚠️ Facebook no está configurado. Configura <code class="bg-amber-100 px-1.5 rounded text-xs">FB_PAGE_TOKEN</code> en root_config.php para activar la automatización.
        </div>

        <!-- Funnel -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-5">Embudo de Conversión</h3>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl text-center">
                    <p class="text-2xl font-black text-blue-600" x-text="funnel.total || 0"></p>
                    <p class="text-[10px] text-blue-500 uppercase font-semibold">Comentarios gatillo</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl text-center">
                    <p class="text-2xl font-black text-indigo-600" x-text="funnel.dm_sent || 0"></p>
                    <p class="text-[10px] text-indigo-500 uppercase font-semibold">DM enviados</p>
                    <p class="text-[10px] text-indigo-400" x-text="funnel.tasa_dm + '%'"></p>
                </div>
                <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl text-center">
                    <p class="text-2xl font-black text-purple-600" x-text="funnel.link_clicked || 0"></p>
                    <p class="text-[10px] text-purple-500 uppercase font-semibold">Click en link</p>
                    <p class="text-[10px] text-purple-400" x-text="funnel.tasa_click + '%'"></p>
                </div>
                <div class="p-4 bg-gradient-to-br from-rose-50 to-rose-100 rounded-xl text-center">
                    <p class="text-2xl font-black text-rose-600" x-text="funnel.formulario_completado || 0"></p>
                    <p class="text-[10px] text-rose-500 uppercase font-semibold">Formularios</p>
                    <p class="text-[10px] text-rose-400" x-text="funnel.tasa_conversion + '%'"></p>
                </div>
                <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl text-center">
                    <p class="text-2xl font-black text-green-600" x-text="funnel.con_colaborador || 0"></p>
                    <p class="text-[10px] text-green-500 uppercase font-semibold">Colaboradores</p>
                </div>
            </div>

            <!-- Funnel Progress Bar -->
            <div class="h-3 bg-gray-100 rounded-full overflow-hidden flex">
                <div class="bg-blue-500 h-full transition-all duration-700" :style="'width:' + (funnel.total ? (funnel.dm_sent/funnel.total*100) : 0) + '%'"></div>
                <div class="bg-indigo-500 h-full transition-all duration-700" :style="'width:' + (funnel.dm_sent ? (funnel.link_clicked/funnel.dm_sent*100) : 0) + '%'"></div>
                <div class="bg-purple-500 h-full transition-all duration-700" :style="'width:' + (funnel.link_clicked ? (funnel.formulario_completado/funnel.link_clicked*100) : 0) + '%'"></div>
                <div class="bg-rose-500 h-full transition-all duration-700" :style="'width:' + (funnel.formulario_completado ? (funnel.con_colaborador/funnel.formulario_completado*100) : 0) + '%'"></div>
                <div class="bg-green-500 h-full transition-all duration-700" :style="'width:' + (funnel.con_colaborador ? 3 : 0) + '%'"></div>
            </div>
            <div class="flex justify-between text-[10px] text-gray-400 mt-1.5">
                <span>Comentarios</span>
                <span>DM</span>
                <span>Click</span>
                <span>Formulario</span>
                <span>Colaborador</span>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200">
            <button @click="activeTab='triggers'" :class="activeTab==='triggers' ? 'border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2">
                <i data-lucide="key" class="w-4 h-4"></i> Palabras Gatillo
            </button>
            <button @click="activeTab='comunas'" :class="activeTab==='comunas' ? 'border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4"></i> Por Comuna
            </button>
            <button @click="activeTab='recientes'" :class="activeTab==='recientes' ? 'border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4"></i> Capturas Recientes
            </button>
        </div>

        <!-- Triggers Tab -->
        <div x-show="activeTab === 'triggers'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Palabras Gatillo</h3>
                <button @click="showAddTrigger = true" class="text-xs text-blue-600 font-bold hover:underline">+ Agregar</button>
            </div>

            <!-- Add trigger form -->
            <div x-show="showAddTrigger" class="mb-4 p-4 bg-gray-50 rounded-xl space-y-3">
                <input x-model="newTrigger.keyword" placeholder="Palabra gatillo" class="w-full px-3 py-2 rounded-lg border text-sm">
                <input x-model="newTrigger.label" placeholder="Etiqueta (ej: Voluntariado)" class="w-full px-3 py-2 rounded-lg border text-sm">
                <textarea x-model="newTrigger.auto_reply_template" placeholder="Template de respuesta (usa {{nombre}}, {{landing_url}})" rows="2" class="w-full px-3 py-2 rounded-lg border text-sm"></textarea>
                <div class="flex gap-2">
                    <button @click="saveTrigger()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700">Guardar</button>
                    <button @click="showAddTrigger = false" class="px-4 py-2 text-gray-500 text-xs font-bold">Cancelar</button>
                </div>
            </div>

            <div class="space-y-2">
                <template x-for="t in triggers" :key="t.id">
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold" x-text="t.keyword"></span>
                            <span class="text-sm text-gray-500" x-text="t.label"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px]" :class="t.enabled ? 'text-green-500' : 'text-red-500'" x-text="t.enabled ? 'Activo' : 'Inactivo'"></span>
                            <button @click="deleteTrigger(t.id)" class="text-xs text-red-500 hover:underline">Eliminar</button>
                        </div>
                    </div>
                </template>
                <p x-show="triggers.length === 0" class="text-sm text-gray-400 text-center py-4">Sin palabras gatillo configuradas</p>
            </div>
        </div>

        <!-- Comunas Tab -->
        <div x-show="activeTab === 'comunas'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Capturas por Comuna</h3>
            <div class="space-y-2">
                <template x-for="c in funnel.por_comuna" :key="c.comuna">
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50">
                        <span class="text-sm font-semibold text-gray-900" x-text="c.comuna"></span>
                        <div class="flex items-center gap-2">
                            <div class="h-2 bg-blue-100 rounded-full w-32">
                                <div class="h-full bg-blue-500 rounded-full" :style="'width:' + (c.total / Math.max(...funnel.por_comuna.map(x=>x.total)) * 100) + '%'"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-900 w-8 text-right" x-text="c.total"></span>
                        </div>
                    </div>
                </template>
                <p x-show="!funnel.por_comuna || funnel.por_comuna.length === 0" class="text-sm text-gray-400 text-center py-4">Sin capturas territoriales todavía</p>
            </div>
        </div>

        <!-- Recientes Tab -->
        <div x-show="activeTab === 'recientes'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Capturas Recientes</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[10px] text-gray-500 uppercase border-b">
                            <th class="px-3 py-2 text-left font-semibold">Usuario</th>
                            <th class="px-3 py-2 text-left font-semibold">Keyword</th>
                            <th class="px-3 py-2 text-left font-semibold">Comuna</th>
                            <th class="px-3 py-2 text-center font-semibold">DM</th>
                            <th class="px-3 py-2 text-center font-semibold">Click</th>
                            <th class="px-3 py-2 text-center font-semibold">Form</th>
                            <th class="px-3 py-2 text-right font-semibold">Colaborador</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-for="r in funnel.recientes" :key="r.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2" x-text="r.user_name"></td>
                                <td class="px-3 py-2"><span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-xs font-bold" x-text="r.trigger_keyword"></span></td>
                                <td class="px-3 py-2 text-gray-500" x-text="r.comuna || '—'"></td>
                                <td class="px-3 py-2 text-center">
                                    <span class="text-xs" :class="r.dm_sent ? 'text-green-500' : 'text-red-400'" x-text="r.dm_sent ? '✅' : '❌'"></span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="text-xs" :class="r.link_clicked ? 'text-green-500' : 'text-red-400'" x-text="r.link_clicked ? '✅' : '❌'"></span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="text-xs" :class="r.formulario_completado ? 'text-green-500' : 'text-red-400'" x-text="r.formulario_completado ? '✅' : '❌'"></span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <span x-text="r.col_nombres || '—'" class="text-sm" :class="r.col_nombres ? 'text-green-600 font-semibold' : 'text-gray-400'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <p x-show="!funnel.recientes || funnel.recientes.length === 0" class="text-sm text-gray-400 text-center py-4">Sin capturas recientes</p>
            </div>
        </div>

    </div>
    </template>
</div>

<script>
function cpPulso() {
    return {
        loading: true,
        scanning: false,
        config: {},
        funnel: {},
        triggers: [],
        activeTab: 'triggers',
        showAddTrigger: false,
        newTrigger: { keyword: '', label: '', auto_reply_template: '', landing_url: '' },

        async init() {
            await this.loadAll();
        },

        async loadAll() {
            this.loading = true;
            try {
                const res = await fetch('api/cp_pulso.php?action=status');
                const json = await res.json();
                if (json.success) {
                    this.config = json.data.config;
                    this.funnel = json.data.funnel;
                }
                const tres = await fetch('api/cp_pulso.php?action=triggers');
                const tjson = await tres.json();
                if (tjson.success) this.triggers = tjson.data;
            } catch (e) { console.error(e); }
            finally {
                this.loading = false;
                this.$nextTick(() => lucide.createIcons());
            }
        },

        async scanNow() {
            this.scanning = true;
            try {
                await fetch('api/cp_pulso.php?action=scan&hours=24');
                await this.loadAll();
            } catch (e) { console.error(e); }
            finally { this.scanning = false; }
        },

        async saveTrigger() {
            await fetch('api/cp_pulso.php?action=save_trigger', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.newTrigger),
            });
            this.showAddTrigger = false;
            this.newTrigger = { keyword: '', label: '', auto_reply_template: '', landing_url: '' };
            const res = await fetch('api/cp_pulso.php?action=triggers');
            const json = await res.json();
            if (json.success) this.triggers = json.data;
        },

        async deleteTrigger(id) {
            if (!confirm('Eliminar este trigger?')) return;
            await fetch('api/cp_pulso.php?action=delete_trigger&id=' + id);
            const res = await fetch('api/cp_pulso.php?action=triggers');
            const json = await res.json();
            if (json.success) this.triggers = json.data;
        },
    }
}
</script>
