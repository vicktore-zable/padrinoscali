<div class="max-w-5xl mx-auto" x-data="setup()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Estado del Sistema</h2>
            <p class="text-sm text-gray-500 mt-1">Diagnóstico unificado de servicios y configuración</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full" :class="overallHealth ? 'bg-green-500' : 'bg-red-500'"></span>
            <span class="text-sm font-medium" x-text="overallHealth ? 'OK' : 'Requiere atención'"></span>
            <button @click="refreshAll()" class="ml-2 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-gray-400"></i>
            </button>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="p-12 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
        <p class="text-sm text-gray-400 mt-2">Verificando servicios...</p>
    </div>

    <template x-if="!loading">
    <div class="space-y-6">

        <!-- System -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i data-lucide="server" class="w-4 h-4"></i> Sistema
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">PHP</p>
                    <p class="text-lg font-bold text-gray-900" x-text="health.php_version"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">MySQL</p>
                    <p class="text-lg font-bold" :class="health.db?.ok ? 'text-green-600' : 'text-red-600'" x-text="health.db?.info || 'Error'"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">Entorno</p>
                    <p class="text-lg font-bold text-gray-900" x-text="health.app_env"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">URL</p>
                    <p class="text-sm font-bold text-gray-900 truncate" x-text="health.app_url"></p>
                </div>
            </div>
        </div>

        <!-- Extensions -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i data-lucide="puzzle" class="w-4 h-4"></i> Extensiones PHP
            </h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="(ok, name) in health.extensions" :key="name">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium"
                         :class="ok ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
                        <i :data-lucide="ok ? 'check-circle' : 'x-circle'" class="w-3.5 h-3.5"></i>
                        <span x-text="name"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Services -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i data-lucide="radio" class="w-4 h-4"></i> Servicios
            </h3>
            <div class="space-y-3">
                <template x-for="(svc, key) in health.configs" :key="key">
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                  :class="svc.configured ? 'bg-green-500' : 'bg-gray-300'"></span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="svc.label"></p>
                                <p class="text-xs text-gray-400" x-text="svc.configured ? 'Configurado' : 'No configurado'"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="key === 'facebook' && svc.configured">
                                <button @click="testService('fb')" :disabled="testing"
                                        class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-all">
                                    Probar
                                </button>
                            </template>
                            <template x-if="key === 'instagram_graph' && svc.configured">
                                <button @click="testService('ig')" :disabled="testing"
                                        class="px-3 py-1 bg-purple-50 text-purple-600 rounded-lg text-xs font-bold hover:bg-purple-100 transition-all">
                                    Probar
                                </button>
                            </template>
                            <template x-if="key === 'email' && svc.configured">
                                <button @click="testService('email')" :disabled="testing"
                                        class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-xs font-bold hover:bg-amber-100 transition-all">
                                    Enviar test
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            <!-- Test result -->
            <div x-show="testResult" class="mt-4 p-4 rounded-xl" :class="testResult.ok ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
                <p class="text-sm font-medium" x-text="testResult.message"></p>
                <template x-if="testResult.data && testResult.data.length > 0">
                    <div class="mt-2 space-y-1">
                        <template x-for="d in testResult.data" :key="d.id">
                            <p class="text-xs" x-text="d.texto || d.caption || JSON.stringify(d)"></p>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Database Tables -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center justify-between">
                <span class="flex items-center gap-2"><i data-lucide="database" class="w-4 h-4"></i> Base de Datos</span>
                <button @click="runMigrations()" class="text-xs text-primary font-bold hover:underline">
                    Ejecutar migraciones
                </button>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[10px] text-gray-500 uppercase border-b">
                            <th class="px-3 py-2 text-left font-semibold">Tabla</th>
                            <th class="px-3 py-2 text-right font-semibold">Registros</th>
                            <th class="px-3 py-2 text-right font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-for="t in health.tables" :key="t.table">
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium" x-text="t.label"></td>
                                <td class="px-3 py-2 text-right font-mono" x-text="t.count.toLocaleString()"></td>
                                <td class="px-3 py-2 text-right">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                                          :class="t.ok ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'"
                                          x-text="t.ok ? 'OK' : 'Falta'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <!-- Migration result -->
            <div x-show="migrationResult" class="mt-4 p-3 bg-green-50 rounded-xl text-xs text-green-700">
                <p x-text="migrationResult"></p>
            </div>
        </div>

        <!-- Instagram Legacy -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i data-lucide="camera" class="w-4 h-4"></i> Instagram Legacy
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">JSON</p>
                    <p class="text-lg font-bold" :class="health.ig_json?.exists ? 'text-green-600' : 'text-red-600'">
                        <span x-text="health.ig_json?.exists ? health.ig_json.size_kb + ' KB' : 'No encontrado'"></span>
                    </p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">Menciones DB</p>
                    <p class="text-lg font-bold text-gray-900" x-text="getCount('ig_menciones')"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">Último sync</p>
                    <p class="text-sm font-bold text-gray-900" x-text="health.last_sync?.instagram || 'Nunca'"></p>
                </div>
            </div>
        </div>

        <!-- FB Last Sync -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6" x-show="health.configs?.facebook?.configured">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i data-lucide="facebook" class="w-4 h-4"></i> Facebook
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">Posts</p>
                    <p class="text-lg font-bold text-gray-900" x-text="getCount('fb_posts')"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">Reacciones</p>
                    <p class="text-lg font-bold text-blue-600" x-text="getCount('fb_reactions')"></p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-[10px] text-gray-400 uppercase">Último post</p>
                    <p class="text-sm font-bold text-gray-900" x-text="health.last_sync?.facebook || 'Nunca'"></p>
                </div>
            </div>
        </div>

    </div>
    </template>
</div>

<script>
function setup() {
    return {
        loading: true,
        health: null,
        overallHealth: false,
        testing: false,
        testResult: null,
        migrationResult: null,

        async init() {
            await this.refreshAll();
        },

        async refreshAll() {
            this.loading = true;
            try {
                const res = await fetch('api/setup.php?action=health');
                const json = await res.json();
                if (json.success) {
                    this.health = json.data;
                    this.overallHealth = this.calcHealth(json.data);
                }
            } catch (e) {
                console.error('Health check error:', e);
            } finally {
                this.loading = false;
                this.$nextTick(() => lucide.createIcons());
            }
        },

        calcHealth(data) {
            if (!data?.configs) return false;
            const services = Object.values(data.configs);
            const critical = services.filter(s => s.configured);
            // Health is OK if at least DB is running
            return data.db?.ok === true;
        },

        async testService(type) {
            this.testing = true;
            this.testResult = null;
            try {
                const res = await fetch(`api/setup.php?action=test_${type}`);
                const json = await res.json();
                this.testResult = json.data || { ok: false, message: 'Error en prueba' };
            } catch (e) {
                this.testResult = { ok: false, message: e.message };
            } finally {
                this.testing = false;
            }
        },

        async runMigrations() {
            try {
                const res = await fetch('api/setup.php?action=install_migrations');
                const json = await res.json();
                if (json.success) {
                    const count = json.data?.migrations?.length || 0;
                    this.migrationResult = `Ejecutadas ${count} migraciones pendientes`;
                    await this.refreshAll();
                }
            } catch (e) {
                this.migrationResult = 'Error: ' + e.message;
            }
        },

        getCount(table) {
            if (!this.health?.tables) return '—';
            const t = this.health.tables.find(t => t.table === table);
            return t ? t.count.toLocaleString() : '—';
        }
    }
}
</script>
