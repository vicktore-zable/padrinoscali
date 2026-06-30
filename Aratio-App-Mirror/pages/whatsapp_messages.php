<div class="max-w-7xl mx-auto" x-data="alasInbox()" x-init="init()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">ALAS — Inbox</h2>
            <p class="text-sm text-gray-500 mt-1">Automatización de Liderazgo, Acción y Seguimiento</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">
                <span x-text="stats.conversaciones_activas" class="font-bold text-primary"></span> activas
            </span>
            <span class="w-px h-6 bg-gray-200"></span>
            <span class="text-sm text-gray-500">
                <span x-text="stats.mensajes_no_leidos" class="font-bold text-yellow-600"></span> no leídos
            </span>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6">
        <button @click="tab = 'inbox'" :class="tab === 'inbox' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="message-circle" class="w-4 h-4 inline mr-1"></i> Inbox
        </button>
        <button @click="tab = 'broadcast'" :class="tab === 'broadcast' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="megaphone" class="w-4 h-4 inline mr-1"></i> Broadcast
        </button>
        <button @click="tab = 'plantillas'" :class="tab === 'plantillas' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i> Plantillas
        </button>
    </div>

    <!-- INBOX TAB -->
    <div x-show="tab === 'inbox'">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Conversation List -->
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <input type="text" x-model="search" @input.debounce="loadConversaciones()" placeholder="Buscar colaborador..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <div class="flex gap-2 mt-3">
                        <button @click="filtroEstado = 'activa'; loadConversaciones()" :class="filtroEstado === 'activa' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'" class="flex-1 py-1.5 px-3 rounded-lg text-xs font-medium transition-all">Activas</button>
                        <button @click="filtroEstado = 'archivada'; loadConversaciones()" :class="filtroEstado === 'archivada' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'" class="flex-1 py-1.5 px-3 rounded-lg text-xs font-medium transition-all">Archivadas</button>
                    </div>
                </div>
                <div class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
                    <template x-for="conv in conversaciones" :key="conv.id">
                        <div @click="selectConversacion(conv.id)" :class="convId === conv.id ? 'bg-primary/5 border-l-4 border-primary' : 'hover:bg-gray-50 border-l-4 border-transparent'" class="px-4 py-3 cursor-pointer transition-all">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center flex-shrink-0">
                                    <span class="text-white font-bold text-sm" x-text="(conv.nombres || '?')[0]"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="conv.nombres + ' ' + conv.apellidos"></p>
                                        <div class="flex items-center gap-1">
                                            <span x-show="conv.unread > 0" class="bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[18px] text-center font-bold" x-text="conv.unread"></span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="conv.municipio"></p>
                                    <p class="text-xs text-gray-400 mt-1 truncate" x-text="conv.ultimo_mensaje || 'Sin mensajes'"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="conversaciones.length === 0 && !loading" class="p-8 text-center text-gray-400">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                        <p class="text-sm">No hay conversaciones</p>
                    </div>
                    <div x-show="loading" class="p-8 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                    </div>
                </div>
            </div>

            <!-- Chat Panel -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col" x-show="convId">
                <!-- Chat Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                            <span class="text-white font-bold" x-text="(chatColaborador?.nombres || '?')[0]"></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900" x-text="chatColaborador?.nombres + ' ' + chatColaborador?.apellidos"></p>
                            <p class="text-xs text-gray-500" x-text="chatColaborador?.perfil + ' · ' + chatColaborador?.municipio"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="archivar(convId)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" title="Archivar">
                            <i data-lucide="archive" class="w-4 h-4"></i>
                        </button>
                        <a :href="'?page=colaborador_detalle&id=' + chatColaborador?.id" class="p-2 text-gray-400 hover:text-primary rounded-lg hover:bg-gray-100" title="Ver perfil">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4 max-h-[500px]" x-ref="chatMessages">
                    <template x-for="msg in mensajes" :key="msg.id">
                        <div :class="msg.direccion === 'enviado' ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="msg.direccion === 'enviado' ? 'bg-primary text-white rounded-2xl rounded-br-md' : 'bg-gray-100 text-gray-900 rounded-2xl rounded-bl-md'" class="max-w-[80%] px-4 py-2.5">
                                <p class="text-sm" x-text="msg.contenido"></p>
                                <p class="text-xs mt-1" :class="msg.direccion === 'enviado' ? 'text-white/70' : 'text-gray-400'" x-text="formatDate(msg.timestamp)"></p>
                            </div>
                        </div>
                    </template>
                    <div x-show="mensajes.length === 0" class="text-center text-gray-400 py-8">
                        <p class="text-sm">No hay mensajes. Inicia la conversación.</p>
                    </div>
                </div>

                <!-- Input -->
                <div class="px-6 py-4 border-t border-gray-100">
                    <form @submit.prevent="enviarMensaje()" class="flex gap-3">
                        <input type="text" x-model="nuevoMensaje" placeholder="Escribe un mensaje..." class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                        <button type="submit" :disabled="!nuevoMensaje.trim()" class="px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 disabled:opacity-50 transition-all">
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- No conversation selected -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 flex items-center justify-center" x-show="!convId">
                <div class="text-center text-gray-400">
                    <i data-lucide="message-square" class="w-16 h-16 mx-auto mb-4 opacity-30"></i>
                    <p class="text-lg font-medium">Selecciona una conversación</p>
                    <p class="text-sm mt-1">O espera a que los colaboradores te escriban</p>
                </div>
            </div>
        </div>
    </div>

    <!-- BROADCAST TAB -->
    <div x-show="tab === 'broadcast'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Enviar Broadcast</h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Plantilla</label>
                <select x-model="broadcastPlantilla" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    <option value="">Seleccionar plantilla...</option>
                    <template x-for="p in plantillas" :key="p.id">
                        <option :value="p.codigo" x-text="p.nombre"></option>
                    </template>
                </select>

                <template x-if="broadcastPlantillaPreview">
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-xs font-medium text-gray-500 mb-2">Vista previa:</p>
                        <p class="text-sm text-gray-700" x-text="broadcastPlantillaPreview"></p>
                    </div>
                </template>

                <label class="block text-sm font-medium text-gray-700 mt-4 mb-2">Programar para</label>
                <input type="datetime-local" x-model="broadcastProgramado" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">

                <label class="block text-sm font-medium text-gray-700 mt-4 mb-2">Segmentar por territorio</label>
                <input type="text" x-model="broadcastFiltros.territorio_id" placeholder="ID de territorio (opcional)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm mb-2">
                
                <label class="block text-sm font-medium text-gray-700 mb-2">Municipio</label>
                <input type="text" x-model="broadcastFiltros.municipio" placeholder="Municipio (opcional)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm mb-2">
                
                <label class="block text-sm font-medium text-gray-700 mb-2">Perfil</label>
                <select x-model="broadcastFiltros.perfil" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    <option value="">Todos los perfiles</option>
                    <option value="Lider">Líder</option>
                    <option value="Simpatizante">Simpatizante</option>
                    <option value="Movilizador">Movilizador</option>
                </select>
            </div>

            <div class="flex flex-col justify-between">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">Resumen del envío</h4>
                    <p class="text-sm text-blue-600">Plantilla: <span class="font-medium" x-text="broadcastPlantilla || '—'"></span></p>
                    <p class="text-sm text-blue-600">Programado: <span class="font-medium" x-text="broadcastProgramado || 'Inmediato'"></span></p>
                    <p class="text-sm text-blue-600">Destinatarios: según filtros seleccionados</p>
                </div>

                <button @click="enviarBroadcast()" :disabled="!broadcastPlantilla" class="w-full mt-4 px-6 py-3 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 disabled:opacity-50 transition-all">
                    <i data-lucide="send" class="w-4 h-4 inline mr-2"></i>
                    Enviar Broadcast
                </button>
            </div>
        </div>
    </div>

    <!-- PLANTILLAS TAB -->
    <div x-show="tab === 'plantillas'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Plantillas de Mensaje</h3>
            <p class="text-sm text-gray-500 mt-1">Templates registrados en Meta WhatsApp Business</p>
        </div>
        <div class="divide-y divide-gray-100">
            <template x-for="p in plantillas" :key="p.id">
                <div class="p-6 hover:bg-gray-50 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-gray-900" x-text="p.nombre"></h4>
                        <span :class="p.estado === 'approved' ? 'bg-green-100 text-green-700' : p.estado === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'" class="px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="p.estado"></span>
                    </div>
                    <p class="text-sm text-gray-600" x-text="p.cuerpo"></p>
                    <p class="text-xs text-gray-400 mt-2">Código: <span class="font-mono" x-text="p.codigo"></span> · Variables: <span x-text="p.variables || 'ninguna'"></span></p>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function alasInbox() {
    return {
        tab: 'inbox',
        loading: false,
        search: '',
        convId: null,
        filtroEstado: 'activa',
        conversaciones: [],
        mensajes: [],
        chatColaborador: null,
        nuevoMensaje: '',
        page: 1,
        stats: { conversaciones_activas: 0, mensajes_no_leidos: 0 },

        // Broadcast
        broadcastPlantilla: '',
        broadcastProgramado: '',
        broadcastFiltros: { territorio_id: '', municipio: '', perfil: '' },
        plantillas: [],

        init() {
            this.loadConversaciones();
            this.loadPlantillas();
            this.loadStats();
        },

        async loadConversaciones() {
            this.loading = true;
            try {
                const url = `api/whatsapp_messages.php?action=conversaciones&estado=${this.filtroEstado}&search=${encodeURIComponent(this.search)}&page=${this.page}`;
                const resp = await fetch(url);
                const json = await resp.json();
                if (json.success) {
                    this.conversaciones = json.data;
                }
            } catch (e) { console.error('ALAS: error loading conversaciones', e); }
            this.loading = false;
        },

        async selectConversacion(id) {
            this.convId = id;
            try {
                const resp = await fetch(`api/whatsapp_messages.php?action=conversacion&id=${id}`);
                const json = await resp.json();
                if (json.success) {
                    this.mensajes = json.data.mensajes;
                    this.chatColaborador = json.data.conversacion;
                }
                // Mark as read
                await fetch(`api/whatsapp_messages.php?action=marcar_leido&id=${id}`);
                this.loadConversaciones();
                this.loadStats();
                this.$nextTick(() => {
                    const el = this.$refs.chatMessages;
                    if (el) el.scrollTop = el.scrollHeight;
                });
            } catch (e) { console.error('ALAS: error loading conversacion', e); }
        },

        async enviarMensaje() {
            if (!this.nuevoMensaje.trim() || !this.convId) return;
            const texto = this.nuevoMensaje;
            this.nuevoMensaje = '';
            try {
                await fetch('api/whatsapp_messages.php?action=enviar', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ conversacion_id: this.convId, texto })
                });
                await this.selectConversacion(this.convId);
            } catch (e) { console.error('ALAS: error sending message', e); }
        },

        async archivar(id) {
            await fetch(`api/whatsapp_messages.php?action=archivar&id=${id}`);
            this.convId = null;
            this.chatColaborador = null;
            this.mensajes = [];
            this.loadConversaciones();
        },

        async loadPlantillas() {
            try {
                const resp = await fetch('api/whatsapp_messages.php?action=plantillas');
                const json = await resp.json();
                if (json.success) this.plantillas = json.data;
            } catch (e) { console.error('ALAS: error loading plantillas', e); }
        },

        async loadStats() {
            try {
                const resp = await fetch('api/whatsapp_messages.php?action=stats');
                const json = await resp.json();
                if (json.success) this.stats = json.data;
            } catch (e) { console.error('ALAS: error loading stats', e); }
        },

        async enviarBroadcast() {
            if (!this.broadcastPlantilla) return;
            try {
                const resp = await fetch('api/whatsapp_messages.php?action=broadcast', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        plantilla_codigo: this.broadcastPlantilla,
                        filtros: this.broadcastFiltros,
                        programado_para: this.broadcastProgramado || null
                    })
                });
                const json = await resp.json();
                if (json.success) {
                    alert(`Broadcast encolado para ${json.total} colaboradores`);
                    this.broadcastPlantilla = '';
                    this.broadcastProgramado = '';
                } else {
                    alert('Error: ' + (json.message || 'Desconocido'));
                }
            } catch (e) { console.error('ALAS: error en broadcast', e); }
        },

        get broadcastPlantillaPreview() {
            const p = this.plantillas.find(p => p.codigo === this.broadcastPlantilla);
            return p ? p.cuerpo.replace(/{{(\w+)}}/g, '[$1]') : null;
        },

        formatDate(ts) {
            if (!ts) return '';
            const d = new Date(ts.replace(' ', 'T'));
            const now = new Date();
            const diff = (now - d) / 1000;
            if (diff < 60) return 'ahora';
            if (diff < 3600) return Math.floor(diff/60) + 'm';
            if (diff < 86400) return Math.floor(diff/3600) + 'h';
            return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
        }
    }
}
</script>
