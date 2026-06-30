<div class="max-w-7xl mx-auto" x-data="igGraph()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Instagram Comentarios</h2>
            <p class="text-sm text-gray-500 mt-1">Comentarios reales con usuarios via Instagram Graph API</p>
        </div>
        <div class="flex items-center gap-3">
            <template x-if="configured">
                <button @click="syncNow()" :disabled="syncing"
                        class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg text-sm font-bold hover:opacity-90 transition-all disabled:opacity-50 flex items-center gap-2">
                    <i data-lucide="refresh-cw" class="w-4 h-4" :class="{'animate-spin': syncing}"></i>
                    <span x-text="syncing ? 'Sincronizando...' : 'Sincronizar'"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Setup alert -->
    <div x-show="!configured" class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="p-2 rounded-lg bg-amber-100 text-amber-600">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-amber-800 mb-1">Instagram Graph API no configurado</h3>
                <p class="text-sm text-amber-700 mb-2">Para obtener comentarios reales con usuarios:</p>
                <ol class="text-sm text-amber-700 space-y-1 ml-4 list-decimal">
                    <li>Convertir cuenta de Instagram a <strong>Business</strong> o <strong>Creador</strong></li>
                    <li>Conectarla a la página de Facebook <code>edisonconcejal</code></li>
                    <li>Obtener el <strong>Instagram Business ID</strong> via Graph API Explorer</li>
                    <li>Configurar <code>IG_BUSINESS_ID</code> y <code>FB_PAGE_TOKEN</code> en <code>root_config.php</code></li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div x-show="configured" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Media</p>
            <p class="text-2xl font-extrabold text-gray-900" x-text="stats.total_media || 0"></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Comentarios</p>
            <p class="text-2xl font-extrabold text-purple-600" x-text="stats.total_comments || 0"></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Comentaristas</p>
            <p class="text-2xl font-extrabold text-blue-600" x-text="stats.total_commenters || 0"></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Menciones históricas</p>
            <p class="text-2xl font-extrabold text-amber-600" x-text="stats.total_menciones || 0"></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase mb-1">Matched</p>
            <p class="text-2xl font-extrabold text-green-600" x-text="stats.matched_menciones || 0"></p>
        </div>
    </div>

    <!-- Tabs -->
    <div x-show="configured" class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6 flex-wrap">
        <button @click="tab = 'comentarios'; loadComments()" :class="tab === 'comentarios' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[100px]">
            <i data-lucide="message-circle" class="w-4 h-4 inline mr-1"></i> Comentarios
        </button>
        <button @click="tab = 'commenters'; loadCommenters()" :class="tab === 'commenters' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[100px]">
            <i data-lucide="users" class="w-4 h-4 inline mr-1"></i> Comentaristas
        </button>
        <button @click="tab = 'menciones'; loadMenciones()" :class="tab === 'menciones' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[100px]">
            <i data-lucide="at-sign" class="w-4 h-4 inline mr-1"></i> Menciones
        </button>
    </div>

    <!-- COMENTARIOS TAB -->
    <div x-show="tab === 'comentarios'">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-50">
                <template x-for="c in comments" :key="c.id">
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white text-xs font-bold">
                                <span x-text="(c.username || '?').charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="c.username"></p>
                                <p class="text-[10px] text-gray-400" x-text="c.timestamp ? new Date(c.timestamp).toLocaleString('es-CO') : ''"></p>
                            </div>
                            <template x-if="c.nombres">
                                <span class="ml-auto text-[10px] bg-green-50 text-green-700 px-2 py-0.5 rounded-full font-medium">
                                    Match: <span x-text="c.nombres + ' ' + (c.apellidos || '')"></span>
                                </span>
                            </template>
                        </div>
                        <p class="text-sm text-gray-700 ml-10" x-text="c.texto"></p>
                    </div>
                </template>
                <div x-show="comments.length === 0" class="p-12 text-center text-gray-400">
                    <i data-lucide="message-circle" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No hay comentarios. Sincroniza para obtenerlos.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- COMMENTERS TAB -->
    <div x-show="tab === 'commenters'">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-50">
                <template x-for="c in commenters" :key="c.username">
                    <div class="px-6 py-4 hover:bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white text-xs font-bold">
                                <span x-text="(c.username || '?').charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="c.username"></p>
                                <p class="text-xs text-gray-400">
                                    <span x-text="c.total_comments + ' comentarios'"></span>
                                    <span x-show="c.nombres" class="text-green-600"> · Match: <span x-text="c.nombres + ' ' + (c.apellidos || '')"></span></span>
                                </p>
                            </div>
                        </div>
                        <div class="text-xs text-gray-400">
                            <span x-text="c.posts_commentados + ' posts'"></span>
                        </div>
                    </div>
                </template>
                <div x-show="commenters.length === 0" class="p-12 text-center text-gray-400">
                    <p>No hay comentaristas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MENCIONES TAB -->
    <div x-show="tab === 'menciones'">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-50">
                <template x-for="m in menciones" :key="m.id">
                    <div class="px-6 py-4 hover:bg-gray-50 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-semibold text-gray-900" x-text="'@' + m.username"></span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 capitalize" x-text="m.categoria"></span>
                            </div>
                            <p class="text-xs text-gray-500 truncate max-w-md" x-text="m.texto_contexto"></p>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="m.fecha"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="m.nombres">
                                <span class="text-[10px] bg-green-50 text-green-700 px-2 py-0.5 rounded-full font-medium">
                                    <span x-text="m.nombres + ' ' + (m.apellidos || '')"></span>
                                </span>
                            </template>
                            <a :href="m.post_url" target="_blank" class="text-gray-400 hover:text-purple-600 transition-colors">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </template>
                <div x-show="menciones.length === 0" class="p-12 text-center text-gray-400">
                    <i data-lucide="at-sign" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No hay menciones cargadas</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function igGraph() {
    return {
        tab: 'comentarios',
        configured: false,
        syncing: false,
        stats: {},
        comments: [],
        commenters: [],
        menciones: [],

        async init() {
            await this.checkStatus();
            if (this.configured) {
                await this.loadStats();
                await this.loadComments();
            }
        },

        async checkStatus() {
            try {
                const res = await fetch('api/instagram_graph.php?action=status');
                const json = await res.json();
                this.configured = json.data?.configured || false;
            } catch (e) { this.configured = false; }
        },

        async loadStats() {
            try {
                const res = await fetch('api/instagram_graph.php?action=stats');
                const json = await res.json();
                if (json.success) this.stats = json.data;
            } catch (e) { console.error(e); }
        },

        async syncNow() {
            this.syncing = true;
            try {
                const res = await fetch('api/instagram_graph.php?action=sync&max=30');
                const json = await res.json();
                if (json.success) {
                    await this.loadComments();
                    await this.loadStats();
                }
            } catch (e) { console.error(e); }
            finally { this.syncing = false; }
        },

        async loadComments() {
            try {
                const res = await fetch('api/instagram_graph.php?action=comments');
                const json = await res.json();
                if (json.success) this.comments = json.data;
            } catch (e) { console.error(e); }
        },

        async loadCommenters() {
            try {
                const res = await fetch('api/instagram_graph.php?action=commenters');
                const json = await res.json();
                if (json.success) this.commenters = json.data;
            } catch (e) { console.error(e); }
        },

        async loadMenciones() {
            try {
                const res = await fetch('api/social_crm.php?action=instagram_menciones');
                const json = await res.json();
                if (json.success) this.menciones = json.data;
            } catch (e) { console.error(e); }
        }
    }
}
</script>
