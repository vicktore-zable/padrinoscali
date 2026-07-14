<?php
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<style type="text/css">
    #mynetwork {
        width: 100%;
        height: calc(100vh - 280px);
        background: rgba(0, 0, 0, 0.2);
    }
    .vis-network:focus { outline: none; }
    [x-cloak] { display: none !important; }
</style>

<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">Mi Red <span class="gradient-text">Estratégica</span></h2>
            <p class="text-gray-500">Mapa jerárquico de tu estructura electoral en tiempo real.</p>
        </div>
        <div class="flex gap-3">
             <button onclick="network.fit()" class="px-6 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="maximize" class="w-4 h-4"></i>
                Centrar Red
            </button>
            <a href="?page=portal_registrar_simpatizante" class="px-6 py-2 bg-white border border-[#DAA520] text-[#DAA520] rounded-xl text-sm font-bold hover:bg-[#DAA520] hover:text-white transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Registrar
            </a>
            <a href="?page=portal_dashboard" class="px-6 py-2 bg-gradient-to-r from-[#002244] to-[#004488] rounded-xl text-sm font-bold text-white shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                Panel Principal
            </a>
        </div>
    </div>
</div>

<div class="glass-card overflow-hidden p-1 relative shadow-md bg-white border-gray-100 mb-8">
    <div id="loading-network" class="absolute inset-0 flex items-center justify-center bg-white/90 z-10 transition-opacity duration-500">
        <div class="text-center">
            <div class="w-16 h-16 border-4 border-[#002244]/20 border-t-[#002244] rounded-full animate-spin mx-auto mb-4"></div>
            <span class="text-[#002244] font-bold uppercase tracking-widest text-xs">Mapeando estructura...</span>
        </div>
    </div>
    <div id="mynetwork"></div>
    <div class="p-4 border-t border-gray-100 bg-gray-50/50 grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full bg-[#002244] shadow-[0_0_10px_rgba(0,34,68,0.4)]"></span>
            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Líderes Aratio</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full bg-[#DAA520] shadow-[0_0_10px_rgba(218,165,32,0.4)]"></span>
            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Simpatizantes</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full bg-cyan-600"></span>
            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Referidos Directos</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full bg-slate-400"></span>
            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Otros Niveles</span>
        </div>
    </div>
</div>

<div x-data="miRedTable()" x-init="init()" x-cloak>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-[#002244]"></i>
                Miembros de la Red
                <span class="text-sm font-normal text-gray-400" x-text="`(${miembros.length} total)`"></span>
            </h3>
            <div class="relative w-full md:w-72">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" x-model="search" @input="filterMembers()" placeholder="Buscar por nombre o documento..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none transition-all">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Documento</th>
                        <th class="py-3 px-4">Nombre</th>
                        <th class="py-3 px-4">Perfil</th>
                        <th class="py-3 px-4">Nivel</th>
                        <th class="py-3 px-4">Seguidores</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(m, i) in filtered" :key="m.documento">
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                            <td class="py-3 px-4 font-mono text-xs font-medium text-gray-700" x-text="m.documento"></td>
                            <td class="py-3 px-4 font-medium text-gray-900" x-text="`${m.nombres} ${m.apellidos}`"></td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                    :class="perfilClass(m.perfil)"
                                    x-text="m.perfil"></span>
                            </td>
                            <td class="py-3 px-4 text-gray-600" x-text="m.nivel !== undefined ? `Nivel ${m.nivel}` : '-'"></td>
                            <td class="py-3 px-4" x-text="m.seguidores_directos || 0"></td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(m)" class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-600 transition-colors" title="Editar">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <button @click="openChangeLeader(m)" class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600 transition-colors" title="Cambiar líder">
                                        <i data-lucide="arrow-up-down" class="w-4 h-4"></i>
                                    </button>
                                    <button @click="confirmDelete(m)" class="p-1.5 rounded-lg hover:bg-red-50 text-red-600 transition-colors" title="Eliminar de la red">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filtered.length === 0">
                        <td colspan="6" class="py-12 text-center text-gray-400">
                            <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                            <p class="text-sm font-medium" x-text="search ? 'Sin resultados para esa búsqueda' : 'No hay miembros en tu red'"></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-start justify-center pt-10 pb-10 overflow-y-auto bg-black/40 backdrop-blur-sm"
         @click.self="showEditModal = false" @keydown.escape.window="showEditModal = false">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="sticky top-0 z-10 flex items-center justify-between p-6 bg-white border-b border-gray-100 rounded-t-2xl">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i data-lucide="user-cog" class="w-5 h-5 text-[#002244]"></i>
                    Editar: <span class="text-[#002244]" x-text="editForm.nombres + ' ' + editForm.apellidos"></span>
                </h3>
                <button @click="showEditModal = false" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
            <div class="p-6 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Nombres *</label>
                        <input type="text" x-model="editForm.nombres" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Apellidos *</label>
                        <input type="text" x-model="editForm.apellidos" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Tipo Documento</label>
                        <select x-model="editForm.tipo_documento" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                            <option value="CC">Cédula de Ciudadanía</option>
                            <option value="TI">Tarjeta de Identidad</option>
                            <option value="CE">Cédula de Extranjería</option>
                            <option value="PA">Pasaporte</option>
                            <option value="RC">Registro Civil</option>
                            <option value="NIT">NIT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Documento</label>
                        <input type="text" x-model="editForm.documento" disabled class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Fecha Nacimiento</label>
                        <input type="date" x-model="editForm.fecha_nacimiento" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Género</label>
                        <select x-model="editForm.genero" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                            <option value="Prefiero no decir">Prefiero no decir</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
                        <input type="email" x-model="editForm.email" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Teléfono</label>
                        <input type="tel" x-model="editForm.telefono" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">WhatsApp</label>
                        <input type="tel" x-model="editForm.telefono_whatsapp" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i> Ubicación
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Departamento</label>
                            <select x-model="editForm.departamento" @change="loadMunicipios()" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                                <option value="">Seleccionar...</option>
                                <template x-for="d in depts" :key="d">
                                    <option x-text="d" :value="d" x-bind:selected="editForm.departamento === d"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Municipio</label>
                            <select x-model="editForm.municipio" @change="loadTerritorios(); loadPuestos()" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                                <option value="">Seleccionar...</option>
                                <template x-for="m in municipios" :key="m.municipio">
                                    <option x-text="m.municipio" :value="m.municipio" x-bind:selected="editForm.municipio === m.municipio"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Tipo de Territorio</label>
                            <select x-model="editForm.tipo_territorio" @change="loadTerritorios()" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                                <option value="">Seleccionar...</option>
                                <option value="Urbano">Urbano</option>
                                <option value="Rural">Rural</option>
                                <option value="Urbano-Rural">Urbano-Rural</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Territorio (Comuna / Corregimiento)</label>
                            <select x-model="editForm.territorio" @change="loadBarrios()" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                                <option value="">Seleccionar...</option>
                                <template x-for="t in territorios" :key="t">
                                    <option x-text="t" :value="t" x-bind:selected="editForm.territorio === t"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Barrio / Vereda</label>
                            <select x-model="editForm.barrio" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                                <option value="">Seleccionar...</option>
                                <template x-for="b in barrios" :key="b">
                                    <option x-text="b" :value="b" x-bind:selected="editForm.barrio === b"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Dirección</label>
                            <input type="text" x-model="editForm.direccion" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Detalle de Ubicación</label>
                            <textarea x-model="editForm.detalle_ubicacion" rows="2" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-gray-400"></i> Datos Electorales
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Perfil</label>
                            <select x-model="editForm.perfil" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                                <option value="">Seleccionar...</option>
                                <template x-for="(label, val) in perfiles" :key="val">
                                    <option x-text="label" :value="val" x-bind:selected="editForm.perfil === val"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Nivel de Participación</label>
                            <select x-model="editForm.nivel_participacion" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                                <option value="">Seleccionar...</option>
                                <template x-for="n in niveles" :key="n">
                                    <option x-text="n" :value="n" x-bind:selected="editForm.nivel_participacion === n"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Dato Potencial (votos)</label>
                            <input type="number" x-model="editForm.dato_potencial" min="0" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Dato Histórico (votos)</label>
                            <input type="number" x-model="editForm.dato_historico" min="0" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Puesto de Votación</label>
                            <input type="text" x-model="editForm.puesto_votacion" list="puestos-list" @input="buscarPuestos()" placeholder="Buscar puesto..."
                                class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                            <datalist id="puestos-list">
                                <template x-for="p in puestos" :key="p.id">
                                    <option :value="p.puesto" x-text="p.puesto"></option>
                                </template>
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Mesa de Votación</label>
                            <input type="text" x-model="editForm.mesa_votacion" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                        <i data-lucide="badge-check" class="w-4 h-4 text-gray-400"></i> Áreas de Interés
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="area in areaOptions" :key="area">
                            <button type="button" @click="toggleArea(area)"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all"
                                :class="editForm.areas_interes.includes(area)
                                    ? 'bg-[#002244] text-white border-[#002244]'
                                    : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300'"
                                x-text="area">
                            </button>
                        </template>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Observaciones</label>
                        <textarea x-model="editForm.observaciones" rows="3" maxlength="500" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none resize-none"></textarea>
                    </div>
                </div>
            </div>
            <div class="sticky bottom-0 flex items-center justify-end gap-3 p-6 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <span x-show="saveStatus" class="text-sm font-medium" :class="saveStatus.includes('Error') ? 'text-red-500' : 'text-green-600'" x-text="saveStatus"></span>
                <button @click="showEditModal = false" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-all">Cancelar</button>
                <button @click="saveMember()" :disabled="saving" class="px-5 py-2.5 bg-gradient-to-r from-[#002244] to-[#004488] rounded-xl text-sm font-bold text-white shadow-lg hover:scale-105 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span x-show="saving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span x-text="saving ? 'Guardando...' : 'Guardar Cambios'"></span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="showLeaderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @click.self="showLeaderModal = false" @keydown.escape.window="showLeaderModal = false">
        <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl shadow-2xl border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Cambiar Líder</h3>
            <p class="text-sm text-gray-500 mb-5">
                Reasignar a: <span class="font-bold text-gray-700" x-text="leaderTarget?.nombres + ' ' + leaderTarget?.apellidos"></span>
            </p>
            <div class="relative mb-4">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" x-model="leaderSearch" @input="buscarLideres()" placeholder="Buscar líder por documento o nombre..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none">
            </div>
            <div class="max-h-60 overflow-y-auto space-y-1 mb-5">
                <template x-for="l in leaderResults" :key="l.documento">
                    <button type="button" @click="selectLeader(l)"
                        class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm hover:bg-blue-50 transition-colors flex items-center justify-between"
                        :class="selectedLeader?.documento === l.documento ? 'bg-blue-50 ring-1 ring-blue-200' : ''">
                        <div>
                            <span class="font-medium text-gray-900" x-text="`${l.nombres} ${l.apellidos}`"></span>
                            <span class="text-xs text-gray-400 ml-2" x-text="l.documento"></span>
                        </div>
                        <span class="text-xs text-gray-400" x-text="l.perfil || ''"></span>
                    </button>
                </template>
                <p x-show="leaderSearch && leaderResults.length === 0" class="text-sm text-gray-400 text-center py-4">Sin resultados</p>
            </div>
            <div class="flex justify-end gap-3">
                <button @click="showLeaderModal = false" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancelar</button>
                <button @click="saveChangeLeader()" :disabled="!selectedLeader || leaderSaving"
                    class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl text-sm font-bold text-white shadow-lg hover:scale-105 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span x-show="leaderSaving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span x-text="leaderSaving ? 'Asignando...' : 'Asignar Líder'"></span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         @click.self="showDeleteModal = false" @keydown.escape.window="showDeleteModal = false">
        <div class="relative w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 text-center">
            <div class="w-14 h-14 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-7 h-7 text-red-500"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">¿Eliminar de la red?</h3>
            <p class="text-sm text-gray-500 mb-6">
                Se desvinculará a <span class="font-bold" x-text="deleteTarget?.nombres + ' ' + deleteTarget?.apellidos"></span>
                y todos sus subordinados quedarán huérfanos.
            </p>
            <div class="flex justify-center gap-3">
                <button @click="showDeleteModal = false" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancelar</button>
                <button @click="saveDelete()" :disabled="deleteSaving"
                    class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-red-600 rounded-xl text-sm font-bold text-white shadow-lg hover:scale-105 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span x-show="deleteSaving" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <span x-text="deleteSaving ? 'Eliminando...' : 'Sí, Eliminar'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const liderId = <?= json_encode($liderId) ?>;
    const container = document.getElementById("mynetwork");
    const loader = document.getElementById("loading-network");
    let network = null;

    const options = {
        nodes: { shape: "dot", size: 24, font: { size: 12, color: "#1e293b", face: "'Outfit', sans-serif", strokeWidth: 0 }, borderWidth: 3, shadow: { enabled: true, color: 'rgba(0,0,0,0.1)', size: 10, x: 5, y: 5 } },
        edges: { width: 2, color: { color: "#cbd5e1", highlight: "#004488", hover: "#DAA520" }, smooth: { type: "cubicBezier", forceDirection: "vertical", roundness: 0.5 }, arrows: { to: { enabled: true, scaleFactor: 0.6 } } },
        layout: { hierarchical: { direction: "UD", sortMethod: "directed", nodeSpacing: 200, levelSeparation: 180, edgeMinimization: true, parentCentralization: true } },
        physics: false,
        interaction: { hover: true, tooltipDelay: 100, zoomView: true, dragView: true }
    };

    (async function() {
        try {
            const r = await fetch(`/aratio/api/leader-network.php`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (r.status === 401) { const e = await r.json(); if (e.redirect) { window.location.href = e.redirect; return; } }
            if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
            let txt = await r.text();
            if (txt.indexOf('{') > 0) txt = txt.substring(txt.indexOf('{'));
            const data = JSON.parse(txt);
            if (data.success) {
                const stylized = data.data.nodes.map(n => {
                    let c = "#3b82f6";
                    if (n.perfil === 'Líder') c = "#002244";
                    if (n.perfil === 'Simpatizante') c = "#DAA520";
                    return { ...n, color: { background: c, border: "#ffffff", highlight: { background: "#ffffff", border: c }, hover: { background: c, border: "#ffffff" } } };
                });
                const nds = new vis.DataSet(stylized);
                const edgs = new vis.DataSet(data.data.edges);
                network = new vis.Network(container, { nodes: nds, edges: edgs }, options);
                network.on("stabilizationIterationsDone", function() { loader.style.opacity = '0'; setTimeout(() => loader.style.display = 'none', 500); });
                setTimeout(() => { loader.style.opacity = '0'; setTimeout(() => loader.style.display = 'none', 500); }, 1000);
                if (data.data.nodes.length === 0) {
                    container.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-gray-500"><i data-lucide="share-2" class="w-12 h-12 mb-4 text-gray-300"></i><p class="text-sm font-medium">No hay estructura jerárquica para mostrar</p></div>';
                    loader.style.display = 'none'; if (window.lucide) lucide.createIcons();
                }
                if (data.data.miembros) {
                    const niveles = data.data.nodes.reduce((acc, n) => { acc[n.id] = n.level; return acc; }, {});
                    const miembrosConNivel = data.data.miembros.map(m => ({ ...m, nivel: niveles[m.documento] }));
                    Alpine.store('miRedData', miembrosConNivel);
                    window.dispatchEvent(new CustomEvent('miRedLoaded', { detail: miembrosConNivel }));
                }
            } else {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500 bg-red-50/50"><span class="font-medium">Error al procesar red estratégica.</span></div>';
                loader.style.display = 'none';
            }
        } catch (e) {
            console.error(e);
            container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500 bg-red-50/50"><span class="font-medium">Fallo de conexión con el núcleo.</span></div>';
            loader.style.display = 'none';
        }
    })();
});

document.addEventListener('alpine:init', () => {
    Alpine.store('miRedData', []);

    const DEPARTAMENTOS = ["Amazonas","Antioquia","Arauca","Atlántico","Bolívar","Boyacá","Caldas","Caquetá","Casanare","Cauca","Cesar","Chocó","Córdoba","Cundinamarca","Guainía","Guaviare","Huila","La Guajira","Magdalena","Meta","Nariño","Norte de Santander","Putumayo","Quindío","Risaralda","San Andrés y Providencia","Santander","Sucre","Tolima","Valle del Cauca","Vaupés","Vichada","Bogotá D.C."];

    const PERFILES = {
        "Lider Comunitario / Social":"Líder Comunitario / Social",
        "Lider Ambiental":"Líder Ambiental",
        "Lider Gremial / Empresarial":"Líder Gremial / Empresarial",
        "Lider Juvenil / Deportivo":"Líder Juvenil / Deportivo",
        "Lider Poblacional / Diferencial":"Líder Poblacional / Diferencial",
        "Lider Religioso":"Líder Religioso",
        "Influencer / Medios":"Influencer / Medios",
        "Vinculo Personal":"Vínculo Personal"
    };

    const NIVELES = ["Simpatizante","Aportante","Activista de Opinión","Movilizador","Contradictor"];
    const AREAS = ["Educación","Salud","Medio Ambiente","Economía","Seguridad","Cultura","Deportes","Tecnología","Derechos Humanos","Juventud","Comercio","Plan Centro","Proyectos"];

    Alpine.data('miRedTable', () => ({
        miembros: [],
        filtered: [],
        search: '',
        showEditModal: false,
        showLeaderModal: false,
        showDeleteModal: false,
        saving: false,
        saveStatus: '',
        leaderSaving: false,
        deleteSaving: false,
        perfiles: PERFILES,
        niveles: NIVELES,
        areaOptions: AREAS,
        depts: DEPARTAMENTOS,
        municipios: [],
        territorios: [],
        barrios: [],
        puestos: [],
        leaderTarget: null,
        deleteTarget: null,
        leaderSearch: '',
        leaderResults: [],
        selectedLeader: null,

        editForm: {
            documento: '', nombres: '', apellidos: '', tipo_documento: 'CC',
            fecha_nacimiento: '', genero: '', email: '', telefono: '',
            telefono_whatsapp: '', departamento: '', municipio: '',
            tipo_territorio: '', territorio: '', barrio: '', direccion: '',
            detalle_ubicacion: '', perfil: '', nivel_participacion: '',
            dato_potencial: 0, dato_historico: 0, puesto_votacion: '',
            mesa_votacion: '', areas_interes: [], observaciones: ''
        },

        init() {
            const stored = Alpine.store('miRedData');
            if (stored && stored.length) {
                this.miembros = stored;
                this.filtered = [...stored];
            }
            window.addEventListener('miRedLoaded', (e) => {
                this.miembros = e.detail;
                this.filtered = [...e.detail];
            });
        },

        filterMembers() {
            const q = this.search.toLowerCase().trim();
            if (!q) { this.filtered = [...this.miembros]; return; }
            this.filtered = this.miembros.filter(m =>
                m.documento.toLowerCase().includes(q) ||
                `${m.nombres} ${m.apellidos}`.toLowerCase().includes(q)
            );
        },

        perfilClass(p) {
            const map = {
                'Lider Comunitario / Social':'bg-blue-100 text-blue-700',
                'Lider Ambiental':'bg-green-100 text-green-700',
                'Lider Gremial / Empresarial':'bg-amber-100 text-amber-700',
                'Lider Juvenil / Deportivo':'bg-teal-100 text-teal-700',
                'Lider Poblacional / Diferencial':'bg-indigo-100 text-indigo-700',
                'Lider Religioso':'bg-yellow-100 text-yellow-700',
                'Influencer / Medios':'bg-red-100 text-red-700',
                'Vinculo Personal':'bg-pink-100 text-pink-700'
            };
            return map[p] || 'bg-gray-100 text-gray-600';
        },

        openEdit(m) {
            this.saveStatus = '';
            this.editForm = {
                documento: m.documento || '',
                nombres: m.nombres || '',
                apellidos: m.apellidos || '',
                tipo_documento: m.tipo_documento || 'CC',
                fecha_nacimiento: m.fecha_nacimiento || '',
                genero: m.genero || '',
                email: m.email || '',
                telefono: m.telefono || '',
                telefono_whatsapp: m.telefono_whatsapp || '',
                departamento: m.departamento || '',
                municipio: m.municipio || '',
                tipo_territorio: m.tipo_territorio || '',
                territorio: m.territorio || '',
                barrio: m.barrio || '',
                direccion: m.direccion || '',
                detalle_ubicacion: m.detalle_ubicacion || '',
                perfil: m.perfil || '',
                nivel_participacion: m.nivel_participacion || '',
                dato_potencial: m.dato_potencial || 0,
                dato_historico: m.dato_historico || 0,
                puesto_votacion: m.puesto_votacion || '',
                mesa_votacion: m.mesa_votacion || '',
                areas_interes: (typeof m.areas_interes === 'string' ? (m.areas_interes ? JSON.parse(m.areas_interes) : []) : (m.areas_interes || [])),
                observaciones: m.observaciones || ''
            };
            this.municipios = [];
            this.territorios = [];
            this.barrios = [];
            this.puestos = [];
            this.showEditModal = true;
            if (this.editForm.departamento) this.loadMunicipios();
        },

        toggleArea(area) {
            const idx = this.editForm.areas_interes.indexOf(area);
            if (idx > -1) this.editForm.areas_interes.splice(idx, 1);
            else this.editForm.areas_interes.push(area);
        },

        async loadMunicipios() {
            if (!this.editForm.departamento) { this.municipios = []; return; }
            try {
                const r = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.editForm.departamento)}`);
                const d = await r.json();
                this.municipios = d.data || d || [];
            } catch(e) { this.municipios = []; }
        },

        async loadTerritorios() {
            if (!this.editForm.departamento || !this.editForm.municipio) { this.territorios = []; return; }
            try {
                const tipo = this.editForm.tipo_territorio;
                const params = `accion=territorios&departamento=${encodeURIComponent(this.editForm.departamento)}&municipio=${encodeURIComponent(this.editForm.municipio)}`;
                const r = await fetch(`/aratio/api/territorios.php?${tipo ? params + `&tipo_territorio=${encodeURIComponent(tipo)}` : params}`);
                const d = await r.json();
                this.territorios = d.data || d || [];
            } catch(e) { this.territorios = []; }
        },

        async loadBarrios() {
            if (!this.editForm.departamento || !this.editForm.municipio || !this.editForm.territorio) { this.barrios = []; return; }
            try {
                const params = `accion=barrios&departamento=${encodeURIComponent(this.editForm.departamento)}&municipio=${encodeURIComponent(this.editForm.municipio)}&territorio=${encodeURIComponent(this.editForm.territorio)}`;
                const r = await fetch(`/aratio/api/territorios.php?${params}`);
                const d = await r.json();
                this.barrios = d.data || d || [];
            } catch(e) { this.barrios = []; }
        },

        async loadPuestos() {
            if (!this.editForm.departamento || !this.editForm.municipio) { this.puestos = []; return; }
            try {
                const r = await fetch(`/aratio/api/territorios.php?accion=puestos&departamento=${encodeURIComponent(this.editForm.departamento)}&municipio=${encodeURIComponent(this.editForm.municipio)}`);
                const d = await r.json();
                this.puestos = d.data || d || [];
            } catch(e) { this.puestos = []; }
        },

        async buscarPuestos() {
            const q = this.editForm.puesto_votacion;
            if (!q || q.length < 2) return;
            try {
                const r = await fetch(`/aratio/api/territorios.php?accion=buscar_puestos&q=${encodeURIComponent(q)}`);
                const d = await r.json();
                if (d.data) this.puestos = d.data;
            } catch(e) {}
        },

        async saveMember() {
            this.saving = true;
            this.saveStatus = '';
            try {
                const body = new FormData();
                body.append('_method', 'PUT');
                body.append('csrf_token', '<?= $csrfToken ?>');
                const fields = ['nombres','apellidos','tipo_documento','fecha_nacimiento','genero','email','telefono','telefono_whatsapp','departamento','municipio','tipo_territorio','territorio','barrio','direccion','detalle_ubicacion','perfil','nivel_participacion','dato_potencial','dato_historico','puesto_votacion','mesa_votacion','observaciones'];
                fields.forEach(f => body.append(f, this.editForm[f] || ''));
                if (this.editForm.areas_interes.length) body.append('areas_interes', JSON.stringify(this.editForm.areas_interes));
                const r = await fetch(`/aratio/api/colaboradores/${this.editForm.documento}/update`, { method: 'POST', body });
                const txt = await r.text();
                let data = { success: false };
                try { data = JSON.parse(txt); } catch(e) {}
                if (data.success || r.ok) {
                    this.saveStatus = '✓ Guardado correctamente';
                    Object.assign(this.miembros.find(m => m.documento === this.editForm.documento) || {}, this.editForm);
                    this.filterMembers();
                    setTimeout(() => { this.showEditModal = false; }, 1200);
                } else {
                    this.saveStatus = 'Error al guardar: ' + (data.message || data.error || 'desconocido');
                }
            } catch(e) {
                this.saveStatus = 'Error de conexión al guardar';
            } finally { this.saving = false; }
        },

        openChangeLeader(m) {
            this.leaderTarget = m;
            this.selectedLeader = null;
            this.leaderSearch = '';
            this.leaderResults = [];
            this.showLeaderModal = true;
        },

        async buscarLideres() {
            if (this.leaderSearch.length < 2) { this.leaderResults = []; return; }
            try {
                const r = await fetch(`/aratio/api/colaboradores.php?action=lideres&search=${encodeURIComponent(this.leaderSearch)}`);
                const d = await r.json();
                this.leaderResults = d.data || d || [];
            } catch(e) { this.leaderResults = []; }
        },

        selectLeader(l) { this.selectedLeader = l; },

        async saveChangeLeader() {
            if (!this.selectedLeader) return;
            this.leaderSaving = true;
            try {
                const body = new FormData();
                body.append('csrf_token', '<?= $csrfToken ?>');
                body.append('nuevo_lider', this.selectedLeader.documento);
                body.append('motivo', 'Reasignación desde portal del líder');
                const r = await fetch(`/aratio/api/colaboradores/${this.leaderTarget.documento}/change-leader`, { method: 'POST', body });
                const d = await r.json();
                if (d.success || r.ok) {
                    alert('Líder cambiado correctamente. La página se recargará.');
                    location.reload();
                } else {
                    alert('Error: ' + (d.message || 'No se pudo cambiar el líder'));
                }
            } catch(e) {
                alert('Error de conexión');
            } finally { this.leaderSaving = false; }
        },

        confirmDelete(m) {
            this.deleteTarget = m;
            this.showDeleteModal = true;
        },

        async saveDelete() {
            if (!this.deleteTarget) return;
            this.deleteSaving = true;
            try {
                const body = new FormData();
                body.append('_method', 'DELETE');
                body.append('csrf_token', '<?= $csrfToken ?>');
                const r = await fetch(`/aratio/api/colaboradores/${this.deleteTarget.documento}/delete`, { method: 'POST', body });
                const d = await r.json();
                if (d.success || r.ok) {
                    alert('Colaborador eliminado de la red. La página se recargará.');
                    location.reload();
                } else {
                    alert('Error: ' + (d.message || 'No se pudo eliminar'));
                }
            } catch(e) {
                alert('Error de conexión');
            } finally { this.deleteSaving = false; }
        }
    }));
});
</script>
