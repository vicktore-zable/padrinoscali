<div class="max-w-7xl mx-auto" x-data="emailCampaigns()" x-init="init()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Email Campaigns</h2>
            <p class="text-sm text-gray-500 mt-1">Campañas de correo electrónico a colaboradores</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">
                <span x-text="stats.enviados" class="font-bold text-primary"></span> enviados
            </span>
            <span class="w-px h-6 bg-gray-200"></span>
            <span class="text-sm text-gray-500">
                <span x-text="stats.pendientes" class="font-bold text-yellow-600"></span> pendientes
            </span>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6">
        <button @click="tab = 'campanas'" :class="tab === 'campanas' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="list" class="w-4 h-4 inline mr-1"></i> Campañas
        </button>
        <button @click="tab = 'nueva'" :class="tab === 'nueva' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Nueva
        </button>
        <button @click="tab = 'plantillas'; loadPlantillas()" :class="tab === 'plantillas' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i> Plantillas
        </button>
        <button @click="tab = 'historial'; loadHistorial()" :class="tab === 'historial' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="clock" class="w-4 h-4 inline mr-1"></i> Historial
        </button>
        <button @click="tab = 'stats'; loadStats()" :class="tab === 'stats' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-1"></i> Stats
        </button>
    </div>

    <!-- CAMPANAS TAB -->
    <div x-show="tab === 'campanas'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="divide-y divide-gray-100">
                <template x-for="c in campanas" :key="c.id">
                    <div class="p-6 hover:bg-gray-50 transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <h4 class="text-sm font-semibold text-gray-900" x-text="c.nombre"></h4>
                                <span :class="c.estado === 'activa' ? 'bg-green-100 text-green-700' : c.estado === 'completada' ? 'bg-blue-100 text-blue-700' : c.estado === 'enviando' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500'" class="px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="c.estado"></span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 line-clamp-1" x-text="c.asunto"></p>
                        <div class="flex gap-4 mt-2 text-xs text-gray-400">
                            <span>Destinatarios: <strong x-text="c.total_destinatarios"></strong></span>
                            <span>Enviados: <strong x-text="c.enviados"></strong></span>
                            <span>Abiertos: <strong x-text="c.abiertos"></strong></span>
                            <span x-show="c.pendientes > 0">Pendientes: <strong class="text-yellow-600" x-text="c.pendientes"></strong></span>
                        </div>
                    </div>
                </template>
                <div x-show="campanas.length === 0" class="p-12 text-center text-gray-400">
                    <i data-lucide="mail" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No hay campañas de email</p>
                </div>
            </div>
        </div>
    </div>

    <!-- NUEVA TAB -->
    <div x-show="tab === 'nueva'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Crear campaña de email</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" x-model="form.nombre" placeholder="Ej: Boletín Junio 2026" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usar plantilla</label>
                    <select @change="cargarPlantilla($el.value)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">Escribir manualmente...</option>
                        <template x-for="p in plantillas" :key="p.id">
                            <option :value="p.id" x-text="p.nombre"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Asunto *</label>
                <input type="text" x-model="form.asunto" placeholder="Asunto del correo..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cuerpo HTML *</label>
                <textarea x-model="form.cuerpo_html" placeholder="&lt;h2&gt;Bienvenido&lt;/h2&gt;&lt;p&gt;Hola {{nombre}}...&lt;/p&gt;" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono" rows="12"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Filtros de segmentación</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-500">Perfil</label>
                        <select x-model="form.filtros.perfil" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                            <option value="">Todos</option>
                            <option value="Lider">Líder</option>
                            <option value="Simpatizante">Simpatizante</option>
                            <option value="Movilizador">Movilizador</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Municipio</label>
                        <input type="text" x-model="form.filtros.municipio" placeholder="Filtrar por municipio" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Territorio ID</label>
                        <input type="number" x-model="form.filtros.territorio_id" placeholder="ID opcional" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button @click="enviarAhora()" :disabled="!form.nombre.trim() || !form.asunto.trim() || !form.cuerpo_html.trim()" class="px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90 disabled:opacity-50 transition">
                    <i data-lucide="send" class="w-4 h-4 inline mr-1"></i>
                    Crear y enviar
                </button>
                <button @click="tab = 'campanas'" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- PLANTILLAS TAB -->
    <div x-show="tab === 'plantillas'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Plantillas de email</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <template x-for="p in plantillas" :key="p.id">
                    <div class="p-6 hover:bg-gray-50 transition-all">
                        <h4 class="text-sm font-semibold text-gray-900" x-text="p.nombre"></h4>
                        <p class="text-xs text-gray-500 mt-1" x-text="p.asunto"></p>
                        <div class="mt-2 flex gap-2">
                            <button @click="usarPlantilla(p)" class="text-xs text-primary hover:underline px-2 py-1 bg-primary/5 rounded">Usar</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- HISTORIAL TAB -->
    <div x-show="tab === 'historial'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Historial de envíos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Destino</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Campaña</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="entry in historial" :key="entry.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap" x-text="formatearFecha(entry.creado_en)"></td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-900" x-text="entry.nombres ? entry.nombres + ' ' + entry.apellidos : entry.email_destino"></span>
                                    <span class="text-gray-400 block text-xs" x-text="entry.email_destino"></span>
                                </td>
                                <td class="px-6 py-4 text-gray-700" x-text="entry.campana_nombre"></td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="entry.estado === 'enviado' ? 'bg-green-100 text-green-700' : entry.estado === 'abierto' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700'" class="px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="entry.estado"></span>
                                </td>
                                <td class="px-6 py-4 text-gray-400 max-w-[200px] truncate text-xs" x-text="entry.error || '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="historial.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">Sin actividad</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div x-show="historialPages > 1" class="px-6 py-4 border-t border-gray-100 flex justify-center gap-2">
                <button @click="historialPage--; loadHistorial()" x-show="historialPage > 1" class="px-3 py-1.5 bg-gray-100 rounded-lg text-xs">Anterior</button>
                <span class="px-3 py-1.5 text-xs text-gray-500" x-text="'Página ' + historialPage + ' de ' + historialPages"></span>
                <button @click="historialPage++; loadHistorial()" x-show="historialPage < historialPages" class="px-3 py-1.5 bg-gray-100 rounded-lg text-xs">Siguiente</button>
            </div>
        </div>
    </div>

    <!-- STATS TAB -->
    <div x-show="tab === 'stats'">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Enviados hoy</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.hoy || 0"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Total enviados</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.enviados || 0"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Pendientes</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1" x-text="stats.pendientes || 0"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Abiertos</p>
                <p class="text-2xl font-bold text-blue-600 mt-1" x-text="stats.abiertos || 0"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Tasa apertura</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.tasa_apertura + '%'"></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-2">Distribución</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Enviados</span>
                        <span class="font-semibold" x-text="stats.enviados || 0"></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" :style="'width:' + (stats.total > 0 ? (stats.enviados / stats.total * 100) : 0) + '%'"></div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Fallidos</span>
                        <span class="font-semibold" x-text="stats.fallidos || 0"></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-red-500 h-3 rounded-full" :style="'width:' + (stats.total > 0 ? (stats.fallidos / stats.total * 100) : 0) + '%'"></div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Abiertos</span>
                        <span class="font-semibold" x-text="stats.abiertos || 0"></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full" :style="'width:' + (stats.total > 0 ? (stats.abiertos / stats.total * 100) : 0) + '%'"></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-2">Campañas activas</h4>
                <p class="text-3xl font-bold text-primary" x-text="stats.campanas_activas || 0"></p>
                <p class="text-xs text-gray-500 mt-1">Campañas en estado activa o enviando</p>
            </div>
        </div>
    </div>
</div>

<script>
function emailCampaigns() {
    return {
        tab: 'campanas',
        campanas: [],
        plantillas: [],
        historial: [],
        historialPage: 1,
        historialPages: 1,
        stats: {},

        form: {
            nombre: '',
            asunto: '',
            cuerpo_html: '',
            filtros: { perfil: '', municipio: '', territorio_id: '' }
        },

        init() {
            this.loadCampanas();
            this.loadPlantillas();
            this.loadStats();
            lucide.createIcons();
        },

        async loadCampanas() {
            try {
                const r = await fetch('api/emails.php?action=campanas');
                const j = await r.json();
                if (j.success) this.campanas = j.data;
            } catch (e) { console.error('EC error:', e); }
        },

        async loadPlantillas() {
            try {
                const r = await fetch('api/emails.php?action=plantillas');
                const j = await r.json();
                if (j.success) this.plantillas = j.data;
            } catch (e) { console.error('EC error:', e); }
        },

        cargarPlantilla(id) {
            if (!id) return;
            const p = this.plantillas.find(p => p.id == id);
            if (p) {
                this.form.asunto = p.asunto;
                this.form.cuerpo_html = p.cuerpo_html;
            }
        },

        usarPlantilla(p) {
            this.form.asunto = p.asunto;
            this.form.cuerpo_html = p.cuerpo_html;
            this.form.nombre = '';
            this.tab = 'nueva';
        },

        async enviarAhora() {
            try {
                const r = await fetch('api/emails.php?action=crear_campana', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        nombre: this.form.nombre,
                        asunto: this.form.asunto,
                        cuerpo_html: this.form.cuerpo_html,
                        filtros: this.form.filtros,
                        programada_para: null
                    })
                });
                const j = await r.json();
                if (j.success) {
                    this.form = { nombre: '', asunto: '', cuerpo_html: '', filtros: { perfil: '', municipio: '', territorio_id: '' } };
                    this.loadCampanas();
                    this.tab = 'campanas';
                }
            } catch (e) { console.error('EC error:', e); }
        },

        async loadHistorial() {
            try {
                const r = await fetch(`api/emails.php?action=historial&page=${this.historialPage}`);
                const j = await r.json();
                if (j.success) { this.historial = j.data; this.historialPages = j.pages; }
            } catch (e) { console.error('EC error:', e); }
        },

        async loadStats() {
            try {
                const r = await fetch('api/emails.php?action=stats');
                const j = await r.json();
                if (j.success) this.stats = j.data;
            } catch (e) { console.error('EC error:', e); }
        },

        formatearFecha(ts) {
            if (!ts) return '';
            return new Date(ts.replace(' ', 'T')).toLocaleString('es-CO', {
                day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
            });
        }
    }
}
</script>
