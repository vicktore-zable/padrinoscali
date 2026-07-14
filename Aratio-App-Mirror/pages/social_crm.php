<div class="max-w-7xl mx-auto" x-data="socialCRM()" x-init="init()">
    <!-- Header -->
    <div class="page-header mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Social CRM</h2>
            <p class="text-sm text-gray-500 mt-1">Integración Facebook + Instagram con CRM</p>
        </div>
        <div class="flex items-center gap-3">
            <template x-if="!configured">
                <a href="https://developers.facebook.com" target="_blank"
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-all">
                    Configurar Facebook API
                </a>
            </template>
            <template x-if="configured">
                <button @click="syncNow()" :disabled="syncing"
                        class="btn-primary disabled:opacity-50 flex items-center gap-2">
                    <i data-lucide="refresh-cw" class="w-4 h-4" :class="{'animate-spin': syncing}"></i>
                    <span x-text="syncing ? 'Sincronizando...' : 'Sincronizar ahora'"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Config Alert -->
    <div x-show="!configured && !checking" class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="p-2 rounded-lg bg-amber-100 text-amber-600">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-amber-800 mb-1">Facebook no configurado</h3>
                <p class="text-sm text-amber-700 mb-4">Para conectar la página de Facebook de Edison necesitas:</p>
                <ol class="text-sm text-amber-700 space-y-2 ml-4 list-decimal">
                    <li>Ir a <a href="https://developers.facebook.com" target="_blank" class="font-bold underline">developers.facebook.com</a></li>
                    <li>Crear una App (tipo "Business") o usar una existente</li>
                    <li>Agregar producto "Facebook Page API"</li>
                    <li>Generar un <strong>Page Access Token</strong> con permisos <code>pages_read_engagement</code></li>
                    <li>Copiar el token en <code>root_config.php</code> como <code>FB_PAGE_TOKEN</code></li>
                </ol>
                <p class="text-xs text-amber-600 mt-3">Page ID: <code>edisonconcejal</code> (ya configurado)</p>
            </div>
        </div>
    </div>

    <!-- Stats Bar -->
    <div x-show="configured" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="stat-card p-4 border border-gray-100">
            <p class="stat-label uppercase">Posts</p>
            <p class="stat-value" x-text="stats.total_posts || 0"></p>
        </div>
        <div class="stat-card p-4 border border-gray-100">
            <p class="stat-label uppercase">Reacciones</p>
            <p class="text-2xl font-extrabold text-blue-600" x-text="stats.total_reactions || 0"></p>
        </div>
        <div class="stat-card p-4 border border-gray-100">
            <p class="stat-label uppercase">Comentarios</p>
            <p class="text-2xl font-extrabold text-purple-600" x-text="stats.total_comments || 0"></p>
        </div>
        <div class="stat-card p-4 border border-gray-100">
            <p class="stat-label uppercase">Match Rate</p>
            <p class="text-2xl font-extrabold text-green-600" x-text="(stats.match_rate || 0) + '%'"></p>
        </div>
        <div class="stat-card p-4 border border-gray-100">
            <p class="stat-label uppercase">Leads</p>
            <p class="text-2xl font-extrabold text-amber-600" x-text="stats.leads_nuevos || 0"></p>
        </div>
    </div>

    <!-- Tabs -->
    <div x-show="configured" class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6 flex-wrap">
        <button @click="tab = 'feed'; loadPosts()" :class="tab === 'feed' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[100px]">
            <i data-lucide="list" class="w-4 h-4 inline mr-1"></i> Feed FB
        </button>
        <button @click="tab = 'commenters'; loadCommenters('unmatched')" :class="tab === 'commenters' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[100px]">
            <i data-lucide="users" class="w-4 h-4 inline mr-1"></i> Comentaristas
        </button>
        <button @click="tab = 'leads'; loadLeads()" :class="tab === 'leads' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[100px]">
            <i data-lucide="user-plus" class="w-4 h-4 inline mr-1"></i> Leads
        </button>
        <button @click="tab = 'match'; loadSuggestions()" :class="tab === 'match' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[100px]">
            <i data-lucide="git-compare" class="w-4 h-4 inline mr-1"></i> Match
        </button>
        <button @click="tab = 'stats'; loadStats()" :class="tab === 'stats' ? 'bg-white shadow-sm' : 'hover:bg-white/50'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all min-w-[100px]">
            <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-1"></i> Stats
        </button>
    </div>

    <!-- FEED TAB -->
    <div x-show="tab === 'feed'">
        <div class="space-y-4">
            <template x-for="post in posts" :key="post.id">
                <div class="card border border-gray-100 overflow-hidden">
                    <div>
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                                    <i data-lucide="facebook" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Página de Edison</p>
                                    <p class="text-xs text-gray-400" x-text="formatearFecha(post.fecha)"></p>
                                </div>
                            </div>
                            <a :href="post.url" target="_blank" class="text-gray-400 hover:text-blue-600 transition-colors">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                        </div>
                        <p class="text-sm text-gray-700 mb-4 whitespace-pre-line line-clamp-4" x-text="post.texto"></p>
                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                                <strong x-text="post.likes_count"></strong> reacciones
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <strong x-text="post.comments_count"></strong> comentarios
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="repeat-2" class="w-3.5 h-3.5"></i>
                                <strong x-text="post.shares_count || 0"></strong> compartido
                            </span>
                        </div>
                    </div>
                    <div x-show="post.expanded" class="border-t border-gray-100 divide-y divide-gray-50">
                        <template x-for="r in post.reactions_data" :key="r.id">
                            <div class="px-6 py-3 flex items-center justify-between text-sm hover:bg-gray-50">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700" x-text="r.user_name"></span>
                                    <span x-show="r.nombres" class="badge badge-success px-2 py-0.5">
                                        Match: <span x-text="r.nombres + ' ' + r.apellidos"></span>
                                    </span>
                                </div>
                                <span :class="{
                                    'text-red-500': r.tipo_reaccion === 'LIKE',
                                    'text-yellow-500': r.tipo_reaccion === 'LOVE',
                                    'text-blue-500': r.tipo_reaccion === 'HAHA' || r.tipo_reaccion === 'WOW',
                                    'text-purple-500': r.tipo_reaccion === 'CARE',
                                    'text-red-600': r.tipo_reaccion === 'ANGRY',
                                }" class="text-xs font-bold uppercase" x-text="r.tipo_reaccion"></span>
                            </div>
                        </template>
                        <template x-for="c in post.comments_data" :key="c.id">
                            <div class="px-6 py-3 hover:bg-gray-50">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-semibold text-gray-800" x-text="c.user_name"></span>
                                    <span x-show="c.nombres" class="badge badge-success px-2 py-0.5">
                                        Match: <span x-text="c.nombres + ' ' + c.apellidos"></span>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600" x-text="c.texto"></p>
                            </div>
                        </template>
                    </div>
                    <button @click="togglePost(post)" class="w-full py-2.5 text-xs font-bold text-gray-500 hover:text-primary hover:bg-gray-50 transition-all border-t border-gray-100">
                        <span x-text="post.expanded ? 'Ocultar interacciones' : 'Ver interacciones (' + (post.reactions_count || 0) + ' reacciones, ' + (post.comments_real_count || 0) + ' comentarios)'"></span>
                    </button>
                </div>
            </template>
            <div x-show="posts.length === 0 && !loading" class="empty-state">
                <i data-lucide="facebook" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p>No hay posts sincronizados. Haz clic en "Sincronizar ahora"</p>
            </div>
            <div x-show="loading" class="empty-state">
                <div class="spinner mx-auto"></div>
            </div>
        </div>
    </div>

    <!-- COMMENTERS TAB -->
    <div x-show="tab === 'commenters'">
        <div class="flex gap-2 mb-4">
            <button @click="commenterFilter = 'unmatched'; loadCommenters('unmatched')"
                    :class="commenterFilter === 'unmatched' ? 'bg-primary text-white' : ''"
                    class="btn-secondary py-1.5 text-xs">Sin match</button>
            <button @click="commenterFilter = 'pending'; loadCommenters('pending')"
                    :class="commenterFilter === 'pending' ? 'bg-primary text-white' : ''"
                    class="btn-secondary py-1.5 text-xs">Pendientes</button>
            <button @click="commenterFilter = 'matched'; loadCommenters('matched')"
                    :class="commenterFilter === 'matched' ? 'bg-primary text-white' : ''"
                    class="btn-secondary py-1.5 text-xs">Matched</button>
        </div>
        <div class="card border border-gray-100 overflow-hidden">
            <template x-for="c in commenters" :key="c.id">
                <div class="px-6 py-4 hover:bg-gray-50 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3 flex-1">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold">
                            <span x-text="(c.user_name || '?').charAt(0)"></span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900" x-text="c.user_name"></p>
                            <p class="text-xs text-gray-400">
                                <span x-text="c.total_reactions + ' reacciones'"></span>
                                <span x-show="c.total_comments"> · <span x-text="c.total_comments + ' comentarios'"></span></span>
                                <span x-show="c.nombres" class="text-green-600"> · Match: <span x-text="c.nombres + ' ' + c.apellidos"></span></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span :class="{
                            'badge-success': c.estado === 'convertido',
                            'badge-warning': c.estado === 'pendiente_revision',
                            'badge-info': c.estado === 'nuevo' || !c.estado,
                        }" class="badge text-[10px] font-bold uppercase px-2 py-0.5" x-text="c.estado || 'nuevo'"></span>
                        <button @click="openMatchModal(c)" x-show="commenterFilter !== 'matched'"
                                class="px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-bold hover:bg-primary/20 transition-all">
                            Asignar
                        </button>
                    </div>
                </div>
            </template>
            <div x-show="commenters.length === 0" class="empty-state">
                <p>No hay comentaristas en este estado</p>
            </div>
        </div>
    </div>

    <!-- LEADS TAB -->
    <div x-show="tab === 'leads'">
        <div class="card border border-gray-100 overflow-hidden">
            <template x-for="l in leads" :key="l.id">
                <div class="px-6 py-4 hover:bg-gray-50 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900" x-text="l.user_name"></p>
                        <p class="text-xs text-gray-400">
                            Red: <strong x-text="l.red"></strong> ·
                            <span x-text="l.total_interacciones + ' interacciones'"></span>
                            <span x-show="l.nombres" class="text-green-600"> · Match: <span x-text="l.nombres + ' ' + l.apellidos"></span></span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span :class="{
                            'badge-success': l.estado === 'convertido',
                            'badge-warning': l.estado === 'pendiente_revision',
                            'badge-info': l.estado === 'nuevo',
                        }" class="badge text-[10px] font-bold uppercase px-2 py-0.5" x-text="l.estado"></span>
                        <button @click="openMatchModal(l, 'lead')" x-show="!l.colaborador_id"
                                class="px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-bold hover:bg-primary/20 transition-all">
                            Asignar a colaborador
                        </button>
                    </div>
                </div>
            </template>
            <div x-show="leads.length === 0" class="empty-state">
                <i data-lucide="user-plus" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p>No hay leads pendientes</p>
            </div>
        </div>
    </div>

    <!-- MATCH TAB -->
    <div x-show="tab === 'match'">
        <div class="card border border-gray-100 mb-4">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Match Automático</h3>
            <p class="text-sm text-gray-500 mb-4">Busca coincidencias entre usuarios de Facebook y colaboradores del CRM por nombre</p>
            <button @click="runAutoMatch()" :disabled="autoMatching"
                    class="btn-primary px-6 disabled:opacity-50">
                <span x-text="autoMatching ? 'Procesando...' : 'Ejecutar Match Automático'"></span>
            </button>
            <div x-show="autoMatchResult" class="mt-4 p-4 bg-green-50 rounded-xl text-sm text-green-700">
                <p x-text="autoMatchResult"></p>
            </div>
        </div>

        <div class="card border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-bold text-gray-700">Sugerencias pendientes</h4>
            </div>
            <template x-for="s in suggestions" :key="s.id">
                <div class="px-6 py-4 hover:bg-gray-50 border-b border-gray-50">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-900" x-text="s.user_name"></p>
                            <p class="text-xs text-gray-400" x-text="(s.total_reactions + s.total_comments) + ' interacciones'"></p>
                        </div>
                        <button @click="openMatchModal(s)" class="px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-bold hover:bg-primary/20 transition-all">
                            Buscar match
                        </button>
                    </div>
                </div>
            </template>
            <div x-show="suggestions.length === 0" class="empty-state">
                <p>No hay sugerencias pendientes</p>
            </div>
        </div>
    </div>

    <!-- STATS TAB -->
    <div x-show="tab === 'stats'">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card border border-gray-100">
                <p class="stat-label uppercase">Posts</p>
                <p class="text-3xl font-extrabold text-gray-900" x-text="stats.total_posts || 0"></p>
            </div>
            <div class="card border border-gray-100">
                <p class="stat-label uppercase">Reacciones totales</p>
                <p class="text-3xl font-extrabold text-blue-600" x-text="stats.total_reactions || 0"></p>
            </div>
            <div class="card border border-gray-100">
                <p class="stat-label uppercase">Comentarios totales</p>
                <p class="text-3xl font-extrabold text-purple-600" x-text="stats.total_comments || 0"></p>
            </div>
            <div class="card border border-gray-100">
                <p class="stat-label uppercase">Comentaristas únicos</p>
                <p class="text-3xl font-extrabold text-amber-600" x-text="stats.total_commenters || 0"></p>
            </div>
            <div class="card border border-gray-100">
                <p class="stat-label uppercase">Match rate</p>
                <p class="text-3xl font-extrabold text-green-600" x-text="(stats.match_rate || 0) + '%'"></p>
                <p class="text-xs text-gray-400 mt-1" x-text="'(' + (stats.matched || 0) + ' de ' + (stats.total_commenters || 0) + ')'"></p>
            </div>
            <div class="card border border-gray-100">
                <p class="stat-label uppercase">Pendientes revisión</p>
                <p class="text-3xl font-extrabold text-amber-600" x-text="stats.pending_review || 0"></p>
            </div>
            <div class="card border border-gray-100">
                <p class="stat-label uppercase">Leads nuevos</p>
                <p class="text-3xl font-extrabold text-red-600" x-text="stats.leads_nuevos || 0"></p>
            </div>
        </div>
    </div>

    <!-- Match Modal -->
    <div x-show="matchModal" x-cloak class="modal z-[100] backdrop-blur-sm"
         @keydown.escape.window="matchModal = false">
        <div class="modal-content max-w-lg shadow-2xl animate-fade-in-up rounded-3xl p-8 overflow-visible" @click.away="matchModal = false">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-gray-900">Asignar a colaborador</h3>
                <button @click="matchModal = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-400"></i>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500">Usuario de Facebook:</p>
                <p class="text-lg font-bold text-gray-900" x-text="matchTarget?.user_name"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar colaborador</label>
                <input type="text" x-model="searchQuery" @input.debounce="searchCollaborator()"
                       placeholder="Nombre, documento o email..."
                       class="input rounded-xl">
            </div>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                <template x-for="c in searchResults" :key="c.id">
                    <div @click="confirmMatch(c.id)"
                         class="p-3 rounded-xl hover:bg-primary/5 cursor-pointer border border-gray-100 transition-all">
                        <p class="text-sm font-semibold text-gray-900" x-text="c.nombres + ' ' + c.apellidos"></p>
                        <p class="text-xs text-gray-400" x-text="c.documento + ' · ' + (c.perfil || 'Sin perfil') + ' · ' + (c.municipio || '')"></p>
                    </div>
                </template>
                <div x-show="searchResults.length === 0 && searchQuery.length > 0" class="empty-state !py-4 text-sm">
                    Sin resultados
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function socialCRM() {
    return {
        tab: 'feed',
        configured: false,
        checking: true,
        syncing: false,
        loading: false,
        autoMatching: false,
        autoMatchResult: '',
        posts: [],
        commenters: [],
        leads: [],
        suggestions: [],
        commenterFilter: 'unmatched',
        stats: {},
        matchModal: false,
        matchTarget: null,
        searchQuery: '',
        searchResults: [],

        async init() {
            await this.checkStatus();
            if (this.configured) {
                await this.loadStats();
                await this.loadPosts();
            }
        },

        async checkStatus() {
            try {
                const res = await fetch('api/facebook.php?action=status');
                const json = await res.json();
                this.configured = json.data?.configured || false;
            } catch (e) {
                this.configured = false;
            } finally {
                this.checking = false;
            }
        },

        async syncNow() {
            this.syncing = true;
            try {
                const res = await fetch('api/facebook.php?action=sync&max=20');
                const json = await res.json();
                if (json.success) {
                    await this.loadPosts();
                    await this.loadStats();
                }
            } catch (e) {
                console.error('Sync error:', e);
            } finally {
                this.syncing = false;
            }
        },

        async loadPosts() {
            this.loading = true;
            try {
                const res = await fetch('api/facebook.php?action=posts');
                const json = await res.json();
                if (json.success) {
                    this.posts = json.data.map(p => ({...p, expanded: false, reactions_data: [], comments_data: []}));
                }
            } catch (e) {
                console.error('Load posts error:', e);
            } finally {
                this.loading = false;
            }
        },

        async togglePost(post) {
            if (post.expanded) {
                post.expanded = false;
                return;
            }
            try {
                const res = await fetch(`api/facebook.php?action=post_detail&fb_post_id=${post.fb_post_id}`);
                const json = await res.json();
                if (json.success) {
                    post.reactions_data = json.data.reactions || [];
                    post.comments_data = json.data.comments || [];
                    post.expanded = true;
                }
            } catch (e) {
                console.error('Toggle post error:', e);
            }
        },

        async loadCommenters(filter) {
            this.commenterFilter = filter;
            try {
                const res = await fetch(`api/facebook.php?action=commenters&filter=${filter}`);
                const json = await res.json();
                if (json.success) this.commenters = json.data;
            } catch (e) {
                console.error('Load commenters error:', e);
            }
        },

        async loadLeads() {
            try {
                const res = await fetch('api/social_crm.php?action=leads');
                const json = await res.json();
                if (json.success) this.leads = json.data;
            } catch (e) {
                console.error('Load leads error:', e);
            }
        },

        async loadSuggestions() {
            try {
                const res = await fetch('api/facebook.php?action=commenters&filter=unmatched&limit=20');
                const json = await res.json();
                if (json.success) this.suggestions = json.data;
            } catch (e) {
                console.error('Load suggestions error:', e);
            }
        },

        async loadStats() {
            try {
                const res = await fetch('api/facebook.php?action=stats');
                const json = await res.json();
                if (json.success) this.stats = json.data;
            } catch (e) {
                console.error('Load stats error:', e);
            }
        },

        async runAutoMatch() {
            this.autoMatching = true;
            this.autoMatchResult = '';
            try {
                const res = await fetch('api/facebook.php?action=auto_match');
                const json = await res.json();
                if (json.success) {
                    const d = json.data.matched;
                    this.autoMatchResult = `Match automático completado: ${d.matched || 0} asignados, ${d.pending || 0} pendientes de revisión`;
                    await this.loadStats();
                    await this.loadSuggestions();
                }
            } catch (e) {
                this.autoMatchResult = 'Error en match automático';
            } finally {
                this.autoMatching = false;
            }
        },

        openMatchModal(target) {
            this.matchTarget = target;
            this.searchQuery = '';
            this.searchResults = [];
            this.matchModal = true;
        },

        async searchCollaborator() {
            if (this.searchQuery.length < 2) { this.searchResults = []; return; }
            try {
                const res = await fetch(`api/colaboradores.php?action=search&q=${encodeURIComponent(this.searchQuery)}&limit=10`);
                const json = await res.json();
                this.searchResults = json.data || [];
            } catch (e) {
                this.searchResults = [];
            }
        },

        async confirmMatch(colaboradorId) {
            try {
                const res = await fetch(`api/facebook.php?action=match&commenter_id=${this.matchTarget.id}&colaborador_id=${colaboradorId}`);
                const json = await res.json();
                if (json.success) {
                    this.matchModal = false;
                    await this.loadCommenters(this.commenterFilter);
                    await this.loadStats();
                }
            } catch (e) {
                console.error('Match error:', e);
            }
        },

        formatearFecha(fecha) {
            if (!fecha) return '';
            const f = new Date(fecha.replace(' ', 'T'));
            return f.toLocaleDateString('es-CO', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
    }
}
</script>
