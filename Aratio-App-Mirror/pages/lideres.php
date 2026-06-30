<div class="max-w-7xl mx-auto" x-data="lideresAdmin()" x-init="init()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Líderes 2.0</h2>
            <p class="text-sm text-gray-500 mt-1">Ranking, equipo y actividad de líderes</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">
                <span x-text="stats.total_lideres" class="font-bold text-primary"></span> líderes
            </span>
            <span class="w-px h-6 bg-gray-200"></span>
            <span class="text-sm text-gray-500">
                <span x-text="stats.total_seguidores" class="font-bold text-gray-900"></span> seguidores
            </span>
        </div>
    </div>

    <!-- Orden selector -->
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6">
        <button @click="orden = 'seguidores'; loadRanking()" :class="orden === 'seguidores' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="users" class="w-4 h-4 inline mr-1"></i> Seguidores
        </button>
        <button @click="orden = 'eventos'; loadRanking()" :class="orden === 'eventos' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i> Eventos
        </button>
        <button @click="orden = 'contactadas'; loadRanking()" :class="orden === 'contactadas' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="users-2" class="w-4 h-4 inline mr-1"></i> Contactadas
        </button>
        <button @click="orden = 'donaciones'; loadRanking()" :class="orden === 'donaciones' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="dollar-sign" class="w-4 h-4 inline mr-1"></i> Donaciones
        </button>
    </div>

    <!-- Ranking Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase w-8">#</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Líder</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Seguidores</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Activos</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Eventos</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Contactadas</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Donaciones</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Actividad 30d</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Potencial</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(l, i) in lideres" :key="l.documento">
                        <tr @click="verDetalle(l.documento)" class="hover:bg-gray-50 cursor-pointer transition-all">
                            <td class="px-4 py-3">
                                <span :class="i < 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500'" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" x-text="i + 1"></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-sm" x-text="(l.nombres || '?')[0]"></div>
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="l.nombres + ' ' + l.apellidos"></p>
                                        <p class="text-xs text-gray-400" x-text="l.municipio + (l.barrio ? ' · ' + l.barrio : '')"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold text-gray-900" x-text="l.total_seguidores"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold text-green-600" x-text="l.num_seguidores"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-medium" :class="l.total_eventos > 0 ? 'text-blue-600' : 'text-gray-400'" x-text="l.total_eventos || 0"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-medium" :class="l.personas_contactadas > 0 ? 'text-purple-600' : 'text-gray-400'" x-text="l.personas_contactadas || 0"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-medium" :class="l.donaciones_gestionadas > 0 ? 'text-yellow-600' : 'text-gray-400'" x-text="'$' + formatNum(l.donaciones_gestionadas)"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1" :class="(l.actividad_30d || 0) > 10 ? 'text-green-600' : (l.actividad_30d || 0) > 0 ? 'text-yellow-600' : 'text-gray-400'">
                                    <span :class="(l.actividad_30d || 0) > 10 ? 'bg-green-100' : (l.actividad_30d || 0) > 0 ? 'bg-yellow-100' : 'bg-gray-100'" class="px-2 py-0.5 rounded-full text-xs font-medium" x-text="l.actividad_30d || 0"></span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-medium text-gray-700" x-text="l.dato_potencial || '—'"></span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="lideres.length === 0">
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400">No hay líderes con seguidores en esta campaña</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Leader Detail Modal -->
    <div x-show="detalleOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" x-cloak>
        <div @click.away="detalleOpen = false" class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-lg" x-text="(detalle.nombres || '?')[0]"></div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900" x-text="detalle.nombres + ' ' + detalle.apellidos"></h3>
                        <p class="text-sm text-gray-500" x-text="detalle.municipio + ' · ' + (detalle.telefono || 'Sin teléfono')"></p>
                    </div>
                </div>
                <button @click="detalleOpen = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Team KPIs -->
                <div class="space-y-3">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Total seguidores</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="detalle.total_seguidores || 0"></p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4">
                        <p class="text-xs text-green-600">Activos</p>
                        <p class="text-2xl font-bold text-green-700" x-text="detalle.seguidores_activos || 0"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Nivel participación</p>
                        <p class="text-lg font-bold text-gray-900" x-text="detalle.nivel_participacion || '—'"></p>
                    </div>

                    <!-- Perfiles de seguidores -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Perfiles del equipo</p>
                        <div class="space-y-2">
                            <template x-for="p in detalle.perfiles_seguidores" :key="p.perfil">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600" x-text="p.perfil || 'Sin perfil'"></span>
                                    <span class="font-semibold" x-text="p.total"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Notificaciones -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Alertas</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2" x-show="notifs.cumpleanos_proximos > 0">
                                <i data-lucide="cake" class="w-4 h-4 text-pink-500"></i>
                                <span><b x-text="notifs.cumpleanos_proximos"></b> cumpleaños próximos</span>
                            </div>
                            <div class="flex items-center gap-2" x-show="notifs.nuevos_seguidores > 0">
                                <i data-lucide="user-plus" class="w-4 h-4 text-green-500"></i>
                                <span><b x-text="notifs.nuevos_seguidores"></b> nuevos esta semana</span>
                            </div>
                            <div class="flex items-center gap-2" x-show="notifs.actividad_reciente > 0">
                                <i data-lucide="activity" class="w-4 h-4 text-blue-500"></i>
                                <span><b x-text="notifs.actividad_reciente"></b> actividades esta semana</span>
                            </div>
                            <div x-show="!notifs.cumpleanos_proximos && !notifs.nuevos_seguidores && !notifs.actividad_reciente" class="text-gray-400 text-xs">
                                Sin alertas recientes
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team list -->
                <div class="lg:col-span-2">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3" x-text="'Equipo (' + (detalle.seguidores?.length || 0) + ')'"></h4>
                    <div class="space-y-2 max-h-[400px] overflow-y-auto">
                        <template x-for="s in detalle.seguidores" :key="s.id">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600" x-text="(s.nombres || '?')[0]"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="s.nombres + ' ' + s.apellidos"></p>
                                        <p class="text-xs text-gray-400" x-text="s.perfil + ' · ' + (s.municipio || '')"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span :class="s.nivel_participacion === 'Alto' ? 'text-green-600' : s.nivel_participacion === 'Medio' ? 'text-yellow-600' : 'text-gray-400'" x-text="s.nivel_participacion || '—'"></span>
                                    <span x-text="s.creado_en"></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="!detalle.seguidores || detalle.seguidores.length === 0" class="text-center text-gray-400 py-8 text-sm">
                            Este líder no tiene seguidores
                        </div>
                    </div>

                    <!-- Activity feed -->
                    <h4 class="text-sm font-semibold text-gray-900 mt-6 mb-3">Actividad del equipo</h4>
                    <div class="space-y-2 max-h-[300px] overflow-y-auto">
                        <template x-for="item in feed" :key="item.id">
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center flex-shrink-0">
                                    <i :data-lucide="iconoActividad(item.tipo)" class="w-4 h-4 text-primary"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900" x-text="item.nombres + ' ' + item.apellidos"></p>
                                    <p class="text-xs text-gray-500 truncate" x-text="item.descripcion || item.tipo"></p>
                                </div>
                                <span class="text-xs text-gray-400 flex-shrink-0" x-text="tiempoRelativo(item.creado_en)"></span>
                            </div>
                        </template>
                        <div x-show="feed.length === 0" class="text-center text-gray-400 py-8 text-sm">
                            Sin actividad reciente del equipo
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function lideresAdmin() {
    return {
        lideres: [],
        orden: 'seguidores',
        stats: { total_lideres: 0, total_seguidores: 0 },

        detalleOpen: false,
        detalle: {},
        feed: [],
        notifs: {},

        init() {
            this.loadRanking();
            lucide.createIcons();
        },

        async loadRanking() {
            try {
                const r = await fetch(`api/lideres.php?action=ranking&orden=${this.orden}`);
                const j = await r.json();
                if (j.success) {
                    this.lideres = j.data;
                    this.stats = { total_lideres: j.total_lideres, total_seguidores: j.total_seguidores };
                }
            } catch (e) { console.error('Lideres error:', e); }
        },

        async verDetalle(documento) {
            this.detalleOpen = true;
            this.detalle = {};
            this.feed = [];
            this.notifs = {};
            try {
                const [det, feedR, notifR] = await Promise.all([
                    fetch(`api/lideres.php?action=detalle&documento=${documento}`).then(r => r.json()),
                    fetch(`api/lideres.php?action=feed&documento=${documento}&page=1`).then(r => r.json()),
                    fetch(`api/lideres.php?action=notificaciones&documento=${documento}`).then(r => r.json()),
                ]);
                if (det.success) this.detalle = det.data;
                if (feedR.success) this.feed = feedR.data;
                if (notifR.success) this.notifs = notifR.data;
                this.$nextTick(() => lucide.createIcons());
            } catch (e) { console.error('Lideres detail error:', e); }
        },

        iconoActividad(tipo) {
            const map = {
                registro: 'user-plus', evento_asistio: 'calendar-check',
                compromiso_creado: 'handshake', donacion_hizo: 'dollar-sign',
                whatsapp_enviado: 'message-circle', whatsapp_recibido: 'message-square',
                estado_cambio: 'refresh-cw', evaluacion: 'star',
                lider_cambio: 'users', cumpleaños: 'cake',
                llamada_contestó: 'phone-call', llamada_no_contesta: 'phone-missed',
            };
            return map[tipo] || 'activity';
        },

        tiempoRelativo(ts) {
            if (!ts) return '';
            const d = new Date(ts.replace(' ', 'T'));
            const diff = (new Date() - d) / 1000;
            if (diff < 60) return 'ahora';
            if (diff < 3600) return Math.floor(diff / 60) + 'm';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h';
            return Math.floor(diff / 86400) + 'd';
        },

        formatNum(n) {
            if (!n) return '0';
            return Number(n).toLocaleString('es-CO');
        }
    }
}
</script>
