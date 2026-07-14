<div class="max-w-7xl mx-auto" x-data="phoneBanking()" x-init="init()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Phone Banking</h2>
            <p class="text-sm text-gray-500 mt-1">Gestión de llamadas a colaboradores</p>
        </div>
        <div class="flex items-center gap-4">
            <select x-model="campanaId" @change="cambiarCampana()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm max-w-xs">
                <option value="">Seleccionar campaña...</option>
                <template x-for="c in campanas" :key="c.id">
                    <option :value="c.id" x-text="c.nombre + ' (' + c.en_cola + ' cola)'" :disabled="c.estado === 'completada'"></option>
                </template>
            </select>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6">
        <button @click="tab = 'agente'" :class="tab === 'agente' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="phone-call" class="w-4 h-4 inline mr-1"></i> Llamar
        </button>
        <button @click="tab = 'campanas'; loadCampanas()" :class="tab === 'campanas' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="list" class="w-4 h-4 inline mr-1"></i> Campañas
        </button>
        <button @click="tab = 'historial'; loadHistorial()" :class="tab === 'historial' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="clock" class="w-4 h-4 inline mr-1"></i> Historial
        </button>
        <button @click="tab = 'stats'; loadStats()" :class="tab === 'stats' ? 'bg-white shadow-sm' : 'hover:bg-white/50'" class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all">
            <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-1"></i> Estadísticas
        </button>
    </div>

    <!-- AGENTE TAB -->
    <div x-show="tab === 'agente'">
        <template x-if="!campanaId">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex items-center justify-center p-12">
                <div class="text-center text-gray-400">
                    <i data-lucide="phone" class="w-16 h-16 mx-auto mb-4 opacity-30"></i>
                    <p class="text-lg font-medium">Selecciona una campaña</p>
                    <p class="text-sm mt-1">Elige una campaña activa para empezar a llamar</p>
                </div>
            </div>
        </template>

        <template x-if="campanaId && !actual">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
                <div class="text-center">
                    <i data-lucide="phone-forwarded" class="w-16 h-16 mx-auto mb-4 text-primary/50"></i>
                    <p class="text-lg font-medium text-gray-900 mb-2">Listo para llamar</p>
                    <p class="text-sm text-gray-500 mb-6" x-text="pendientesEnCola + ' colaboradores en cola'"></p>
                    <button @click="siguienteLlamada()" class="px-8 py-3 bg-primary text-white rounded-xl font-medium hover:opacity-90 transition">
                        <i data-lucide="phone-call" class="w-5 h-5 inline mr-2"></i>
                        Siguiente llamada
                    </button>
                </div>
            </div>
        </template>

        <template x-if="actual">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Colaborador Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-xl" x-text="(actual.nombres || '?')[0]"></div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="actual.nombres + ' ' + actual.apellidos"></h3>
                            <p class="text-sm text-gray-500" x-text="actual.perfil + ' · ' + actual.documento"></p>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                            <a :href="'tel:' + (actual.telefono_whatsapp || actual.telefono)" class="text-blue-600 hover:underline" x-text="actual.telefono_whatsapp || actual.telefono"></a>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                            <span x-text="actual.municipio + (actual.barrio ? ' · ' + actual.barrio : '')"></span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
                            <span x-text="actual.perfil"></span>
                        </div>
                    </div>

                    <!-- Timer -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-xl text-center">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Duración</p>
                        <p class="text-3xl font-bold text-gray-900 font-mono" x-text="formatearTiempo(timerSeg)"></p>
                    </div>
                </div>

                <!-- Script & Actions -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Script -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Guión de llamada</h4>
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-900 whitespace-pre-line" x-text="actual.objetivo || 'Sin guión definido para esta campaña.'"></div>
                    </div>

                    <!-- Result buttons -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Registrar resultado</h4>

                        <template x-if="mostrarNotas">
                            <div class="mb-4">
                                <textarea x-model="notas" placeholder="Notas de la llamada..." class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" rows="3"></textarea>
                                <div class="flex gap-2 mt-2">
                                    <button @click="registrarResultado('contestó')" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">Contestó</button>
                                    <button @click="mostrarNotas = false; notas = ''" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">Cancelar</button>
                                </div>
                            </div>
                        </template>

                        <template x-if="!mostrarNotas">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <button @click="registrarResultado('contestó')" class="p-3 bg-green-100 text-green-700 rounded-xl text-sm font-medium hover:bg-green-200 transition flex flex-col items-center gap-1">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    <span>Contestó</span>
                                </button>
                                <button @click="registrarResultado('no_contesta')" class="p-3 bg-yellow-100 text-yellow-700 rounded-xl text-sm font-medium hover:bg-yellow-200 transition flex flex-col items-center gap-1">
                                    <i data-lucide="phone-missed" class="w-5 h-5"></i>
                                    <span>No contestó</span>
                                </button>
                                <button @click="registrarResultado('llamar_despues')" class="p-3 bg-blue-100 text-blue-700 rounded-xl text-sm font-medium hover:bg-blue-200 transition flex flex-col items-center gap-1">
                                    <i data-lucide="calendar" class="w-5 h-5"></i>
                                    <span>Llamar después</span>
                                </button>
                                <button @click="registrarResultado('ocupado')" class="p-3 bg-orange-100 text-orange-700 rounded-xl text-sm font-medium hover:bg-orange-200 transition flex flex-col items-center gap-1">
                                    <i data-lucide="clock" class="w-5 h-5"></i>
                                    <span>Ocupado</span>
                                </button>
                                <button @click="registrarResultado('no_interesado')" class="p-3 bg-red-100 text-red-700 rounded-xl text-sm font-medium hover:bg-red-200 transition flex flex-col items-center gap-1">
                                    <i data-lucide="thumbs-down" class="w-5 h-5"></i>
                                    <span>No interesado</span>
                                </button>
                                <button @click="registrarResultado('equivocado')" class="p-3 bg-red-100 text-red-700 rounded-xl text-sm font-medium hover:bg-red-200 transition flex flex-col items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                                    <span>Equivocado</span>
                                </button>
                                <button @click="mostrarNotas = true" class="p-3 bg-purple-100 text-purple-700 rounded-xl text-sm font-medium hover:bg-purple-200 transition flex flex-col items-center gap-1">
                                    <i data-lucide="edit" class="w-5 h-5"></i>
                                    <span>Otro</span>
                                </button>
                                <button @click="saltarLlamada()" class="p-3 bg-gray-100 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-200 transition flex flex-col items-center gap-1">
                                    <i data-lucide="skip-forward" class="w-5 h-5"></i>
                                    <span>Saltar</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Mi cola actual -->
        <div x-show="campanaId && miCola.length > 0" class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900">Mi cola actual (<span x-text="miCola.length"></span>)</h4>
            </div>
            <div class="divide-y divide-gray-100">
                <template x-for="item in miCola" :key="item.id">
                    <div class="px-6 py-3 flex items-center justify-between text-sm">
                        <div>
                            <span class="font-medium text-gray-900" x-text="item.nombres + ' ' + item.apellidos"></span>
                            <span class="text-gray-400 ml-2" x-text="item.telefono"></span>
                        </div>
                        <span :class="item.estado === 'en_progreso' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-0.5 rounded-full text-xs font-medium" x-text="item.estado"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- CAMPANAS TAB -->
    <div x-show="tab === 'campanas'">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Campañas de llamadas</h3>
            <button @click="mostrarFormCampana = !mostrarFormCampana" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90 transition">
                <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i>
                Nueva campaña
            </button>
        </div>

        <!-- Create form -->
        <div x-show="mostrarFormCampana" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h4 class="text-sm font-semibold text-gray-900 mb-4">Nueva campaña de llamadas</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" x-model="formCampana.nombre" placeholder="Ej: Bienvenida abril 2026" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Perfil</label>
                    <select x-model="formCampana.filtros.perfil" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">Todos los perfiles</option>
                        <option value="Lider">Líder</option>
                        <option value="Simpatizante">Simpatizante</option>
                        <option value="Movilizador">Movilizador</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <input type="text" x-model="formCampana.descripcion" placeholder="Propósito de la campaña" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Guión / Objetivo</label>
                <textarea x-model="formCampana.objetivo" placeholder="Escribe el guión que el agente leerá durante la llamada..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" rows="4"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Municipio (opcional)</label>
                    <input type="text" x-model="formCampana.filtros.municipio" placeholder="Filtrar por municipio" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Territorio ID (opcional)</label>
                    <input type="number" x-model="formCampana.filtros.territorio_id" placeholder="ID de territorio" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Líder ID (opcional)</label>
                    <input type="number" x-model="formCampana.filtros.lider_id" placeholder="ID del líder" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="crearCampana()" :disabled="!formCampana.nombre.trim()" class="px-6 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90 disabled:opacity-50 transition">
                    Crear campaña
                </button>
                <button @click="mostrarFormCampana = false" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">Cancelar</button>
            </div>
        </div>

        <!-- Campaign list -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="divide-y divide-gray-100">
                <template x-for="c in campanas" :key="c.id">
                    <div class="p-6 hover:bg-gray-50 transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <h4 class="text-sm font-semibold text-gray-900" x-text="c.nombre"></h4>
                                <span :class="c.estado === 'activa' ? 'bg-green-100 text-green-700' : c.estado === 'pausada' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500'" class="px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="c.estado"></span>
                            </div>
                            <button @click="cargarEnAgente(c.id)" class="text-xs text-primary hover:underline">
                                <i data-lucide="phone-call" class="w-3 h-3 inline mr-1"></i>Llamar
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mb-2" x-text="c.descripcion || 'Sin descripción'"></p>
                        <div class="flex gap-4 text-xs text-gray-400">
                            <span>Cola: <strong x-text="c.en_cola"></strong></span>
                            <span>Llamadas: <strong x-text="c.total_llamadas"></strong></span>
                            <span>Completadas: <strong x-text="c.llamadas_completadas"></strong></span>
                        </div>
                    </div>
                </template>
                <div x-show="campanas.length === 0" class="p-12 text-center text-gray-400">
                    <i data-lucide="phone" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No hay campañas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- HISTORIAL TAB -->
    <div x-show="tab === 'historial'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Historial de llamadas</h3>
                <div class="flex gap-2">
                    <select x-model="historialFiltro.resultado" @change="loadHistorial()" class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs">
                        <option value="">Todos los resultados</option>
                        <template x-for="(label, val) in resultadosLabels" :key="val">
                            <option :value="val" x-text="label"></option>
                        </template>
                    </select>
                </div>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Colaborador</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Campaña</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Agente</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Resultado</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Duración</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="entry in historial" :key="entry.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap" x-text="formatearFecha(entry.creado_en)"></td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900" x-text="entry.nombres + ' ' + entry.apellidos"></span>
                                    <span class="text-gray-400 text-xs block" x-text="entry.telefono"></span>
                                </td>
                                <td class="px-6 py-4 text-gray-700" x-text="entry.campana_nombre"></td>
                                <td class="px-6 py-4 text-gray-500" x-text="entry.agente_nombre || '—'"></td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="claseResultado(entry.resultado)" class="px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="resultadosLabels[entry.resultado] || entry.resultado"></span>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-500" x-text="formatearDuracion(entry.duracion_seg)"></td>
                                <td class="px-6 py-4 text-gray-400 max-w-[200px] truncate" x-text="entry.notas || '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="historial.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">No hay llamadas registradas</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div x-show="historialPages > 1" class="px-6 py-4 border-t border-gray-100 flex justify-center gap-2">
                <button @click="historialPage--; loadHistorial()" x-show="historialPage > 1" class="px-3 py-1.5 bg-gray-100 rounded-lg text-xs hover:bg-gray-200 transition">Anterior</button>
                <span class="px-3 py-1.5 text-xs text-gray-500" x-text="'Página ' + historialPage + ' de ' + historialPages"></span>
                <button @click="historialPage++; loadHistorial()" x-show="historialPage < historialPages" class="px-3 py-1.5 bg-gray-100 rounded-lg text-xs hover:bg-gray-200 transition">Siguiente</button>
            </div>
        </div>
    </div>

    <!-- STATS TAB -->
    <div x-show="tab === 'stats'">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Hoy</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.hoy || 0"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Esta semana</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.semana || 0"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Total</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.total || 0"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Pendientes</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1" x-text="stats.pendientes || 0"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Duración prom.</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" x-text="formatearDuracion(stats.duracion_promedio)"></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Resultados breakdown -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Resultados</h4>
                <div class="space-y-3">
                    <template x-for="(total, resultado) in stats.resultados" :key="resultado">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700" x-text="resultadosLabels[resultado] || resultado"></span>
                            <div class="flex items-center gap-3">
                                <div class="w-32 bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full" :class="claseBarra(resultado)" :style="'width: ' + (total / Math.max(...Object.values(stats.resultados)) * 100) + '%'"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 min-w-[3ch] text-right" x-text="total"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="!stats.resultados || Object.keys(stats.resultados).length === 0" class="text-center text-gray-400 py-4 text-sm">Sin datos</div>
                </div>
            </div>

            <!-- Top agentes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Agentes top</h4>
                <div class="space-y-3">
                    <template x-for="agente in stats.top_agentes" :key="agente.nombre">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700" x-text="agente.nombre"></span>
                            <span class="text-sm font-semibold text-gray-900" x-text="agente.total + ' llamadas'"></span>
                        </div>
                    </template>
                    <div x-show="!stats.top_agentes || stats.top_agentes.length === 0" class="text-center text-gray-400 py-4 text-sm">Sin datos</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function phoneBanking() {
    return {
        tab: 'agente',
        campanaId: '',
        campanas: [],
        actual: null,
        miCola: [],
        pendientesEnCola: 0,
        timerSeg: 0,
        timerInterval: null,
        notas: '',
        mostrarNotas: false,

        // Campaign form
        mostrarFormCampana: false,
        formCampana: {
            nombre: '',
            descripcion: '',
            objetivo: '',
            filtros: { perfil: '', municipio: '', territorio_id: '', lider_id: '' }
        },

        // History
        historial: [],
        historialPage: 1,
        historialPages: 1,
        historialFiltro: { resultado: '' },

        // Stats
        stats: {},

        resultadosLabels: {
            'contestó': 'Contestó',
            'no_contesta': 'No contestó',
            'llamar_despues': 'Llamar después',
            'no_interesado': 'No interesado',
            'equivocado': 'Equivocado',
            'ocupado': 'Ocupado',
            'otro': 'Otro'
        },

        init() {
            this.loadCampanas();
            lucide.createIcons();
        },

        async loadCampanas() {
            try {
                const resp = await fetch('api/llamadas.php?action=campanas');
                const json = await resp.json();
                if (json.success) this.campanas = json.data;
            } catch (e) { console.error('PB error:', e); }
        },

        cambiarCampana() {
            this.actual = null;
            this.miCola = [];
            this.pendientesEnCola = 0;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerSeg = 0;
            if (this.campanaId) {
                this.cargarMiCola();
                const c = this.campanas.find(c => c.id == this.campanaId);
                if (c) this.pendientesEnCola = c.en_cola;
            }
        },

        async cargarMiCola() {
            if (!this.campanaId) return;
            try {
                const resp = await fetch(`api/llamadas.php?action=mi_cola&campana_id=${this.campanaId}`);
                const json = await resp.json();
                if (json.success) this.miCola = json.data;
            } catch (e) { console.error('PB error:', e); }
        },

        async siguienteLlamada() {
            if (!this.campanaId) return;
            try {
                const resp = await fetch(`api/llamadas.php?action=siguiente&campana_id=${this.campanaId}`);
                const json = await resp.json();
                if (json.success) {
                    this.actual = json.data;
                    this.mostrarNotas = false;
                    this.notas = '';
                    this.timerSeg = 0;
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.timerInterval = setInterval(() => { this.timerSeg++; }, 1000);
                    this.cargarMiCola();
                    this.pendientesEnCola = Math.max(0, this.pendientesEnCola - 1);
                }
            } catch (e) { console.error('PB error:', e); }
        },

        async registrarResultado(resultado) {
            if (!this.actual) return;
            try {
                await fetch('api/llamadas.php?action=resultado', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        cola_id: this.actual.id,
                        resultado: resultado,
                        notas: this.notas,
                        duracion_seg: this.timerSeg
                    })
                });
                if (this.timerInterval) clearInterval(this.timerInterval);
                this.actual = null;
                this.miCola = [];
                this.notas = '';
                this.mostrarNotas = false;
                this.siguienteLlamada();
            } catch (e) { console.error('PB error:', e); }
        },

        async saltarLlamada() {
            this.actual = null;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerSeg = 0;
            this.siguienteLlamada();
        },

        async crearCampana() {
            if (!this.formCampana.nombre.trim()) return;
            try {
                const resp = await fetch('api/llamadas.php?action=crear_campana', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.formCampana)
                });
                const json = await resp.json();
                if (json.success) {
                    this.mostrarFormCampana = false;
                    this.formCampana = { nombre: '', descripcion: '', objetivo: '', filtros: { perfil: '', municipio: '', territorio_id: '', lider_id: '' } };
                    this.loadCampanas();
                }
            } catch (e) { console.error('PB error:', e); }
        },

        cargarEnAgente(id) {
            this.campanaId = id;
            this.tab = 'agente';
            this.cambiarCampana();
        },

        async loadHistorial() {
            let url = `api/llamadas.php?action=historial&page=${this.historialPage}`;
            if (this.historialFiltro.resultado) url += `&resultado=${this.historialFiltro.resultado}`;
            try {
                const resp = await fetch(url);
                const json = await resp.json();
                if (json.success) {
                    this.historial = json.data;
                    this.historialPages = json.pages;
                }
            } catch (e) { console.error('PB error:', e); }
        },

        async loadStats() {
            try {
                const resp = await fetch('api/llamadas.php?action=stats');
                const json = await resp.json();
                if (json.success) this.stats = json.data;
            } catch (e) { console.error('PB error:', e); }
        },

        formatearTiempo(seg) {
            const m = String(Math.floor(seg / 60)).padStart(2, '0');
            const s = String(seg % 60).padStart(2, '0');
            return `${m}:${s}`;
        },

        formatearFecha(ts) {
            if (!ts) return '';
            return new Date(ts.replace(' ', 'T')).toLocaleString('es-CO', {
                day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
            });
        },

        formatearDuracion(seg) {
            if (!seg) return '—';
            if (seg < 60) return seg + 's';
            const m = Math.floor(seg / 60);
            const s = seg % 60;
            return m + 'm ' + s + 's';
        },

        claseResultado(r) {
            const clases = {
                'contestó': 'bg-green-100 text-green-700',
                'no_contesta': 'bg-yellow-100 text-yellow-700',
                'llamar_despues': 'bg-blue-100 text-blue-700',
                'no_interesado': 'bg-red-100 text-red-700',
                'equivocado': 'bg-red-100 text-red-700',
                'ocupado': 'bg-orange-100 text-orange-700',
                'otro': 'bg-purple-100 text-purple-700'
            };
            return clases[r] || 'bg-gray-100 text-gray-500';
        },

        claseBarra(r) {
            const clases = {
                'contestó': 'bg-green-500',
                'no_contesta': 'bg-yellow-500',
                'llamar_despues': 'bg-blue-500',
                'no_interesado': 'bg-red-500',
                'equivocado': 'bg-red-500',
                'ocupado': 'bg-orange-500',
                'otro': 'bg-purple-500'
            };
            return clases[r] || 'bg-gray-400';
        }
    }
}
</script>
