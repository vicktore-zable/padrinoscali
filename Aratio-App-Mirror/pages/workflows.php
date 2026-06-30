<div class="max-w-7xl mx-auto" x-data="alasWorkflows()" x-init="init()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">⚙️ Monitoreo ALAS</h2>
            <p class="text-sm text-gray-500 mt-1">Workflow Engine — Automatización de reglas de seguimiento</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">
                <span x-text="stats.reglas_activas" class="font-bold text-green-600"></span> reglas activas
            </span>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Hoy</p>
            <p class="text-2xl font-bold text-gray-900 mt-1" x-text="(stats.hoy?.total || 0)"></p>
            <p class="text-xs mt-1" :class="(stats.hoy?.exitosos || 0) > 0 ? 'text-green-600' : 'text-gray-400'">
                <span x-text="stats.hoy?.exitosos || 0"></span> exitosos
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Este mes</p>
            <p class="text-2xl font-bold text-gray-900 mt-1" x-text="(stats.mes?.total || 0)"></p>
            <p class="text-xs mt-1 text-green-600" x-text="(stats.mes?.exitosos || 0) + ' exitosos'"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Reglas activas</p>
            <p class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.reglas_activas || 0"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Pendientes</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1" x-text="pendientes.length"></p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6">
        <button @click="tab = 'reglas'" :class="tab === 'reglas' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="list" class="w-4 h-4 inline mr-1"></i> Reglas
        </button>
        <button @click="tab = 'log'" :class="tab === 'log' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="activity" class="w-4 h-4 inline mr-1"></i> Bitácora
        </button>
        <button @click="tab = 'pendientes'" :class="tab === 'pendientes' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="clock" class="w-4 h-4 inline mr-1"></i> Pendientes
            <span x-show="pendientes.length > 0" class="ml-1 bg-yellow-500 text-white text-xs rounded-full px-1.5 py-0.5" x-text="pendientes.length"></span>
        </button>
    </div>

    <!-- REGLAS TAB -->
    <div x-show="tab === 'reglas'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Reglas de Automatización</h3>
                <p class="text-sm text-gray-500 mt-1">Las reglas predefinidas ejecutan acciones automáticas cuando se dispara un evento</p>
            </div>
            <div class="divide-y divide-gray-100">
                <template x-for="regla in reglas" :key="regla.id">
                    <div class="p-6 hover:bg-gray-50 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h4 class="text-sm font-semibold text-gray-900" x-text="regla.nombre"></h4>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-mono" x-text="regla.trigger_evento"></span>
                                    <span :class="regla.activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="regla.activo ? 'Activo' : 'Inactivo'"></span>
                                </div>
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <span>Ejecuciones: <strong x-text="regla.ejecuciones_total"></strong></span>
                                    <span>Exitosas: <strong class="text-green-600" x-text="regla.ejecuciones_exitosas"></strong></span>
                                    <span>Tasa: <strong x-text="regla.tasa_exito + '%'"></strong></span>
                                </div>
                                <div class="mt-2">
                                    <template x-for="accion in safeJson(regla.acciones_json)" :key="accion.tipo">
                                        <span class="inline-flex items-center gap-1 mr-2 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">
                                            <i data-lucide="zap" class="w-3 h-3"></i>
                                            <span x-text="accion.tipo + ': ' + (accion.plantilla || accion.estado || '—')"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="toggleRegla(regla.id)" :class="regla.activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all hover:opacity-80">
                                    <span x-text="regla.activo ? 'Desactivar' : 'Activar'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- LOG TAB -->
    <div x-show="tab === 'log'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Bitácora de Ejecuciones</h3>
                <p class="text-sm text-gray-500 mt-1">Registro detallado de cada ejecución de reglas</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Regla</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Colaborador</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Trigger</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Resultado</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="entry in log" :key="entry.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap" x-text="formatDate(entry.ejecutado_en)"></td>
                                <td class="px-6 py-4 font-medium text-gray-900" x-text="entry.regla_nombre"></td>
                                <td class="px-6 py-4 text-gray-700" x-text="entry.nombres ? entry.nombres + ' ' + (entry.apellidos || '') : '—'"></td>
                                <td class="px-6 py-4"><span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded" x-text="entry.trigger_evento"></span></td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="entry.resultado === 'exitoso' ? 'bg-green-100 text-green-700' : entry.resultado === 'fallido' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'" class="px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="entry.resultado"></span>
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500">
                                    <span x-text="entry.acciones_ejecutadas + '/' + (entry.acciones_ejecutadas + entry.acciones_fallidas)"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="log.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay ejecuciones registradas</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PENDIENTES TAB -->
    <div x-show="tab === 'pendientes'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Acciones Pendientes</h3>
                <p class="text-sm text-gray-500 mt-1">Tareas programadas para ejecutar</p>
            </div>
            <div class="divide-y divide-gray-100">
                <template x-for="p in pendientes" :key="p.id">
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="p.regla_nombre"></p>
                                <p class="text-xs text-gray-500">
                                    <span x-text="p.nombres ? p.nombres + ' ' + p.apellidos : 'Global'"></span>
                                    · <span class="font-mono" x-text="p.accion_tipo"></span>
                                </p>
                            </div>
                            <span class="text-xs text-gray-400" x-text="p.programado_para ? 'Programado: ' + p.programado_para : 'Inmediato'"></span>
                        </div>
                    </div>
                </template>
                <div x-show="pendientes.length === 0" class="p-12 text-center text-gray-400">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-3 text-green-400"></i>
                    <p>No hay acciones pendientes</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function alasWorkflows() {
    return {
        tab: 'reglas',
        reglas: [],
        log: [],
        pendientes: [],
        stats: {},
        pollInterval: null,

        init() {
            this.loadReglas();
            this.loadLog();
            this.loadPendientes();
            this.loadStats();
            this.pollInterval = setInterval(() => {
                this.loadStats();
                this.loadPendientes();
            }, 15000);
        },

        destroy() {
            if (this.pollInterval) clearInterval(this.pollInterval);
        },

        async loadReglas() {
            try {
                const resp = await fetch('api/workflows.php?action=reglas');
                const json = await resp.json();
                if (json.success) this.reglas = json.data;
            } catch (e) { console.error('ALAS: error loading reglas', e); }
        },

        async loadLog() {
            try {
                const resp = await fetch('api/workflows.php?action=log');
                const json = await resp.json();
                if (json.success) this.log = json.data;
            } catch (e) { console.error('ALAS: error loading log', e); }
        },

        async loadPendientes() {
            try {
                const resp = await fetch('api/workflows.php?action=pendientes');
                const json = await resp.json();
                if (json.success) this.pendientes = json.data;
            } catch (e) { console.error('ALAS: error loading pendientes', e); }
        },

        async loadStats() {
            try {
                const resp = await fetch('api/workflows.php?action=stats');
                const json = await resp.json();
                if (json.success) this.stats = json.data;
            } catch (e) { console.error('ALAS: error loading stats', e); }
        },

        async toggleRegla(id) {
            try {
                await fetch(`api/workflows.php?action=toggle&id=${id}`);
                this.loadReglas();
            } catch (e) { console.error('ALAS: error toggling rule', e); }
        },

        safeJson(json) {
            if (!json) return [];
            if (typeof json === 'object') return json;
            try { return JSON.parse(json); } catch(e) { return []; }
        },

        formatDate(ts) {
            if (!ts) return '';
            const d = new Date(ts.replace(' ', 'T'));
            return d.toLocaleString('es-CO', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        }
    }
}
</script>
