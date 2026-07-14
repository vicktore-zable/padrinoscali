<div class="space-y-6" x-data="whatsappData()" x-init="init()">
    <div class="page-header">
        <div class="flex items-center gap-4">
            <div class="page-header-icon icon-gradient-pink">
                <i data-lucide="cake" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="page-header-title">Cumpleaños</h1>
                <p class="page-header-desc">Gestión de mensajes de cumpleaños por WhatsApp</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-label">Enviados Hoy</p>
            <p class="stat-value" x-text="stats.hoy?.total_exitosos ?? 0"></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Este Mes</p>
            <p class="stat-value" x-text="stats.mes?.total_exitosos ?? 0"></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Tasa de Éxito</p>
            <p class="stat-value text-green-600" x-text="(stats.tasa_exito ?? 0) + '%'"></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Próximos 7 Días</p>
            <p class="stat-value text-blue-600" x-text="stats.proximos_7_dias ?? 0"></p>
        </div>
    </div>

    <div class="flex gap-2 border-b border-gray-200">
        <button @click="tab = 'calendario'"
            :class="tab === 'calendario' ? 'tab tab-active' : 'tab tab-inactive'">
            <i data-lucide="calendar" class="w-4 h-4"></i> Calendario
        </button>
        <button @click="tab = 'historial'; cargarHistorial()"
            :class="tab === 'historial' ? 'tab tab-active' : 'tab tab-inactive'">
            <i data-lucide="clock" class="w-4 h-4"></i> Historial
        </button>
    </div>

    <div x-show="tab === 'calendario'" class="space-y-4">
        <div class="flex flex-wrap gap-2">
            <button @click="rango = 'hoy'; cargarCumpleanos()"
                :class="rango === 'hoy' ? 'btn-primary' : 'btn-secondary'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition">
                Hoy
            </button>
            <button @click="rango = 'semana'; cargarCumpleanos()"
                :class="rango === 'semana' ? 'btn-primary' : 'btn-secondary'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition">
                Esta Semana
            </button>
            <button @click="rango = 'mes'; cargarCumpleanos()"
                :class="rango === 'mes' ? 'btn-primary' : 'btn-secondary'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition">
                Este Mes
            </button>
            <button @click="rango = 'personalizado'; cargarCumpleanos()"
                :class="rango === 'personalizado' ? 'btn-primary' : 'btn-secondary'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition">
                Personalizado
            </button>
        </div>

        <div x-show="rango === 'personalizado'" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Desde (MM-DD)</label>
                <input type="text" x-model="fechaDesde" placeholder="01-15" class="input w-32">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Hasta (MM-DD)</label>
                <input type="text" x-model="fechaHasta" placeholder="02-15" class="input w-32">
            </div>
            <button @click="buscarPersonalizado()" class="btn-primary">
                <i data-lucide="search" class="w-4 h-4 inline"></i> Buscar
            </button>
        </div>

        <div x-show="loading" class="text-center py-8">
            <div class="spinner mx-auto"></div>
        </div>

        <template x-if="!loading && cumpleaneros.length === 0">
            <div class="empty-state">
                <i data-lucide="cake" class="empty-state-icon"></i>
                <h3 class="empty-state-title">Sin cumpleaños</h3>
                <p class="empty-state-desc">No hay colaboradores con cumpleaños en este rango</p>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="(p, idx) in cumpleaneros" :key="p.id">
                <div class="card p-0 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="h-2" :class="diasColor(p.dias_faltantes)"></div>
                    <div class="p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-lg"
                                :class="diasColorBubble(p.dias_faltantes)">
                                <span x-text="p.nombres?.charAt(0) + p.apellidos?.charAt(0)"></span>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate" x-text="p.nombres + ' ' + p.apellidos"></p>
                                <span class="badge text-xs" :class="'badge-' + (p.perfil?.toLowerCase().includes('lider') ? 'primary' : 'info')" x-text="p.perfil || 'Sin perfil'"></span>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-3 mb-3 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Cumple</p>
                                    <p class="text-lg font-bold text-gray-800" x-text="p.fecha_exacta"></p>
                                </div>
                                <div class="w-px h-8 bg-gray-200"></div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Edad</p>
                                    <p class="text-lg font-bold text-gray-800"><span x-text="p.edad"></span></p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-3 mb-3">
                            <template x-if="parseInt(p.dias_faltantes) === 0">
                                <div class="flex items-center gap-2 bg-gradient-to-r from-pink-500 to-rose-500 text-white px-4 py-2 rounded-full shadow-lg shadow-rose-500/30">
                                    <i data-lucide="party-popper" class="w-5 h-5"></i>
                                    <span class="font-bold text-sm">HOY</span>
                                </div>
                            </template>
                            <template x-if="parseInt(p.dias_faltantes) > 0 && parseInt(p.dias_faltantes) <= 3">
                                <div class="flex items-center gap-2 bg-gradient-to-r from-amber-400 to-orange-500 text-white px-4 py-2 rounded-full shadow-lg shadow-orange-500/30">
                                    <i data-lucide="bell" class="w-5 h-5"></i>
                                    <span class="font-bold text-sm">Faltan <span x-text="p.dias_faltantes"></span> <span x-text="p.dias_faltantes === '1' ? 'día' : 'días'"></span></span>
                                </div>
                            </template>
                            <template x-if="parseInt(p.dias_faltantes) > 3">
                                <div class="flex items-center gap-2 bg-gray-100 text-gray-600 px-4 py-2 rounded-full">
                                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                                    <span class="font-semibold text-sm">Faltan <span x-text="p.dias_faltantes"></span> <span x-text="p.dias_faltantes === '1' ? 'día' : 'días'"></span></span>
                                </div>
                            </template>
                            <template x-if="parseInt(p.dias_faltantes) < 0">
                                <div class="flex items-center gap-2 bg-gray-100 text-gray-400 px-4 py-2 rounded-full">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span class="text-sm">Ya pasó</span>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                <i data-lucide="phone" class="w-3 h-3"></i>
                                <span x-text="p.telefono_whatsapp || p.telefono || 'Sin teléfono'"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <template x-if="p.ultimo_estado === 'enviado'">
                                    <span class="badge badge-success flex items-center gap-1 text-xs">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Enviado
                                    </span>
                                </template>
                                <template x-if="p.ultimo_estado === 'fallido'">
                                    <span class="badge badge-error flex items-center gap-1 text-xs">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i> Fallido
                                    </span>
                                </template>
                                <template x-if="!p.ultimo_estado">
                                    <span class="badge badge-warning flex items-center gap-1 text-xs">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Pendiente
                                    </span>
                                </template>
                                <button @click="reenviar(p.id)"
                                    class="w-8 h-8 flex items-center justify-center rounded-full text-white transition"
                                    :class="enviando === p.id ? 'bg-gray-400' : 'bg-primary hover:bg-primary-dark'"
                                    :disabled="enviando === p.id"
                                    :title="enviando === p.id ? 'Enviando...' : 'Enviar WhatsApp'">
                                    <template x-if="enviando === p.id">
                                        <div class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></div>
                                    </template>
                                    <template x-if="enviando !== p.id">
                                        <i data-lucide="send" class="w-4 h-4"></i>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="tab === 'historial'" class="space-y-4">
        <div class="card">
            <div class="flex flex-wrap gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium mb-1">Estado</label>
                    <select x-model="hf.estado" class="input text-sm py-1.5">
                        <option value="">Todos</option>
                        <option value="enviado">Enviado</option>
                        <option value="fallido">Fallido</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="sin_whatsapp">Sin WhatsApp</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Desde</label>
                    <input type="date" x-model="hf.desde" class="input text-sm py-1.5">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Hasta</label>
                    <input type="date" x-model="hf.hasta" class="input text-sm py-1.5">
                </div>
                <div class="flex items-end gap-2">
                    <button @click="hf.page = 1; cargarHistorial()" class="btn-primary text-sm">
                        <i data-lucide="search" class="w-4 h-4 inline"></i> Filtrar
                    </button>
                    <button @click="exportarCSV()" class="btn-secondary text-sm flex items-center gap-1">
                        <i data-lucide="download" class="w-4 h-4"></i> Exportar CSV
                    </button>
                </div>
            </div>

            <div x-show="loading" class="text-center py-4">
                <div class="spinner-sm mx-auto"></div>
            </div>

            <div class="table-container" x-show="!loading">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Colaborador</th>
                            <th>Teléfono</th>
                            <th>Perfil</th>
                            <th>Template</th>
                            <th class="text-center">Estado</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in historial" :key="row.id">
                            <tr>
                                <td class="text-gray-600" x-text="row.sent_at ? new Date(row.sent_at).toLocaleDateString('es-CO', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '-'"></td>
                                <td class="font-medium" x-text="row.colaborador_nombre || '-'"></td>
                                <td class="text-gray-600" x-text="row.telefono_whatsapp || '-'"></td>
                                <td><span class="badge badge-info" x-text="row.perfil || '-'"></span></td>
                                <td class="text-gray-500 text-xs font-mono" x-text="row.template_used || '-'"></td>
                                <td class="text-center">
                                    <span :class="{
                                        'badge badge-success': row.estado === 'enviado',
                                        'badge badge-error': row.estado === 'fallido',
                                        'badge badge-warning': row.estado === 'pendiente',
                                        'badge': row.estado === 'sin_whatsapp'
                                    }" x-text="row.estado"></span>
                                </td>
                                <td class="text-red-500 text-xs max-w-xs truncate" x-text="row.error_msg || '-'" :title="row.error_msg"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <template x-if="!loading && historial.length === 0">
                <div class="empty-state">
                    <p class="empty-state-desc">No hay registros de envíos</p>
                </div>
            </template>

            <div x-show="hf.pages > 1" class="flex justify-center gap-2 mt-4">
                <button @click="hf.page > 1 && (hf.page--, cargarHistorial())" class="btn-secondary text-sm px-3 py-1" :disabled="hf.page <= 1">
                    Anterior
                </button>
                <span class="flex items-center text-sm text-gray-600" x-text="'Página ' + hf.page + ' de ' + hf.pages"></span>
                <button @click="hf.page < hf.pages && (hf.page++, cargarHistorial())" class="btn-secondary text-sm px-3 py-1" :disabled="hf.page >= hf.pages">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

    <div x-show="notificacion" x-transition.duration.300ms
        class="toast"
        :class="notificacionTipo === 'success' ? 'toast-success' : 'toast-error'"
        x-text="notificacion">
    </div>
</div>

<script>
function whatsappData() {
    return {
        tab: 'calendario',
        rango: 'hoy',
        fechaDesde: '',
        fechaHasta: '',
        cumpleaneros: [],
        historial: [],
        stats: {},
        loading: false,
        enviando: null,
        notificacion: '',
        notificacionTipo: 'success',
        hf: {
            estado: '',
            desde: '',
            hasta: '',
            page: 1,
            pages: 1
        },

        diasColor(dias) {
            if (!dias) return 'bg-gray-200';
            const d = parseInt(dias);
            if (d === 0) return 'bg-gradient-to-r from-pink-500 to-rose-500';
            if (d <= 3) return 'bg-gradient-to-r from-amber-400 to-orange-500';
            if (d <= 7) return 'bg-gradient-to-r from-blue-400 to-cyan-500';
            return 'bg-gradient-to-r from-gray-300 to-gray-400';
        },

        diasColorBubble(dias) {
            if (!dias) return 'bg-gradient-to-br from-gray-300 to-gray-400';
            const d = parseInt(dias);
            if (d === 0) return 'bg-gradient-to-br from-pink-500 to-rose-500';
            if (d <= 3) return 'bg-gradient-to-br from-amber-400 to-orange-500';
            if (d <= 7) return 'bg-gradient-to-br from-blue-400 to-cyan-500';
            return 'bg-gradient-to-br from-gray-400 to-gray-500';
        },

        async init() {
            await Promise.all([
                this.cargarCumpleanos(),
                this.cargarStats()
            ]);
            setTimeout(() => lucide.createIcons(), 100);
        },

        async cargarCumpleanos() {
            this.loading = true;
            try {
                const r = await fetch('api/whatsapp.php?action=cumpleanos&rango=' + this.rango);
                const d = await r.json();
                this.cumpleaneros = d.data || [];
            } catch (e) {
                console.error('Error cargando cumpleaños:', e);
                this.notificar('Error al cargar cumpleaños', 'error');
            } finally {
                this.loading = false;
                setTimeout(() => lucide.createIcons(), 100);
            }
        },

        buscarPersonalizado() {
            if (!this.fechaDesde || !this.fechaHasta) {
                this.notificar('Ingresa fechas desde y hasta (formato MM-DD)', 'error');
                return;
            }
            this.cargarCumpleanos();
        },

        async cargarHistorial() {
            this.loading = true;
            try {
                let url = 'api/whatsapp.php?action=historial&page=' + this.hf.page;
                if (this.hf.estado) url += '&estado=' + this.hf.estado;
                if (this.hf.desde) url += '&desde=' + this.hf.desde;
                if (this.hf.hasta) url += '&hasta=' + this.hf.hasta;
                const r = await fetch(url);
                const d = await r.json();
                this.historial = d.data || [];
                this.hf.page = d.page || 1;
                this.hf.pages = d.pages || 1;
            } catch (e) {
                console.error('Error cargando historial:', e);
                this.notificar('Error al cargar historial', 'error');
            } finally {
                this.loading = false;
            }
        },

        async cargarStats() {
            try {
                const r = await fetch('api/whatsapp.php?action=stats');
                const d = await r.json();
                this.stats = d.data || {};
            } catch (e) {
                console.error('Error cargando stats:', e);
            }
        },

        async reenviar(id) {
            this.enviando = id;
            try {
                const r = await fetch('api/whatsapp.php?action=reenviar&id=' + id);
                const d = await r.json();
                if (d.success) {
                    this.notificar('Mensaje enviado correctamente', 'success');
                    this.cargarCumpleanos();
                    this.cargarStats();
                } else {
                    this.notificar(d.message || 'Error al enviar', 'error');
                }
            } catch (e) {
                this.notificar('Error de conexión', 'error');
            } finally {
                this.enviando = null;
            }
        },

        exportarCSV() {
            if (!this.historial || this.historial.length === 0) {
                this.notificar('No hay datos para exportar', 'error');
                return;
            }
            let csv = "Fecha,Colaborador,Telefono,Perfil,Template,Estado,Error\n";
            this.historial.forEach(r => {
                csv += `"${r.sent_at || ''}","${r.colaborador_nombre || ''}","${r.telefono_whatsapp || ''}","${r.perfil || ''}","${r.template_used || ''}","${r.estado || ''}","${(r.error_msg || '').replace(/"/g, '""')}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'historial_whatsapp_' + new Date().toISOString().slice(0,10) + '.csv';
            link.click();
            URL.revokeObjectURL(link);
            this.notificar('CSV exportado correctamente', 'success');
        },

        notificar(msg, tipo = 'success') {
            this.notificacion = msg;
            this.notificacionTipo = tipo;
            setTimeout(() => { this.notificacion = ''; }, 3000);
        }
    }
}
</script>
