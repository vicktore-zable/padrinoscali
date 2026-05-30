<?php
/**
 * Página: Detalle de Colaborador
 * Vista completa con tabs: Información, Curriculum, Seguidores, Historial
 */

$colaboradorId = $_GET['id'] ?? null;
$campanaId = $_SESSION['campana_activa'] ?? null;

if (!$colaboradorId) {
    echo '<div class="p-6"><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
        <p>ID de colaborador no especificado.</p>
    </div></div>';
    return;
}
?>

<div x-data="colaboradorDetalle()" x-init="init()" class="p-6">
    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-fuchsia-600"></div>
    </div>

    <!-- Contenido -->
    <div x-show="!loading && colaborador">
        <!-- Header con breadcrumb -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                    <a href="index.php?page=colaboradores" class="hover:text-fuchsia-600">Colaboradores</a>
                    <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                    <span x-text="colaborador.nombres + ' ' + colaborador.apellidos"></span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg mr-3"
                         :style="'background: linear-gradient(135deg, #FF00FF, #FFD700)'">
                        <span x-text="colaborador.nombres ? colaborador.nombres.charAt(0) : ''"></span>
                    </div>
                    <div>
                        <span x-text="colaborador.nombres + ' ' + colaborador.apellidos"></span>
                        <p class="text-sm font-normal text-gray-500" x-text="colaborador.perfil"></p>
                    </div>
                </h1>
            </div>

            <div class="flex gap-2 mt-4 md:mt-0">
                <button @click="showModal = 'cambiarLider'" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center">
                    <i data-lucide="user-check" class="w-4 h-4 mr-2"></i>
                    Cambiar Líder
                </button>
                <button @click="showModal = 'reevaluar'" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                    Reevaluar
                </button>
                <a :href="'index.php?page=colaboradores_red&focus=' + colaborador.id"
                   class="px-4 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700 flex items-center">
                    <i data-lucide="network" class="w-4 h-4 mr-2"></i>
                    Ver en Red
                </a>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100">
                <nav class="flex -mb-px">
                    <button @click="activeTab = 'info'"
                            :class="activeTab === 'info' ? 'border-fuchsia-500 text-fuchsia-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-6 py-4 border-b-2 font-medium text-sm flex items-center">
                        <i data-lucide="user" class="w-4 h-4 mr-2"></i>
                        Información
                    </button>
                    <button @click="activeTab = 'curriculum'; loadCurriculum()"
                            :class="activeTab === 'curriculum' ? 'border-fuchsia-500 text-fuchsia-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-6 py-4 border-b-2 font-medium text-sm flex items-center">
                        <i data-lucide="briefcase" class="w-4 h-4 mr-2"></i>
                        Curriculum
                    </button>
                    <button @click="activeTab = 'seguidores'; loadSeguidores()"
                            :class="activeTab === 'seguidores' ? 'border-fuchsia-500 text-fuchsia-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-6 py-4 border-b-2 font-medium text-sm flex items-center">
                        <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                        Seguidores
                        <span x-show="seguidoresCount > 0" class="ml-2 px-2 py-0.5 bg-fuchsia-100 text-fuchsia-600 rounded-full text-xs" x-text="seguidoresCount"></span>
                    </button>
                    <button @click="activeTab = 'historial'; loadHistorial()"
                            :class="activeTab === 'historial' ? 'border-fuchsia-500 text-fuchsia-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-6 py-4 border-b-2 font-medium text-sm flex items-center">
                        <i data-lucide="history" class="w-4 h-4 mr-2"></i>
                        Historial
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Tab: Información -->
                <div x-show="activeTab === 'info'">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Datos Personales -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i data-lucide="user-circle" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                                Datos Personales
                            </h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Documento:</dt>
                                    <dd class="font-medium" x-text="colaborador.tipo_documento + ' ' + colaborador.documento"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Fecha Nac.:</dt>
                                    <dd class="font-medium" x-text="colaborador.fecha_nacimiento"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Género:</dt>
                                    <dd class="font-medium" x-text="colaborador.genero"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Grupo Etáreo:</dt>
                                    <dd class="font-medium" x-text="colaborador.grupo_etareo"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Email:</dt>
                                    <dd class="font-medium" x-text="colaborador.email || 'No registrado'"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Teléfono:</dt>
                                    <dd class="font-medium" x-text="colaborador.telefono || 'No registrado'"></dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Perfil Político -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i data-lucide="award" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                                Perfil Político
                            </h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Perfil:</dt>
                                    <dd class="font-medium" x-text="colaborador.perfil"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Nivel:</dt>
                                    <dd class="font-medium" x-text="colaborador.nivel_participacion"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Dato Potencial:</dt>
                                    <dd class="font-medium" x-text="colaborador.dato_potencial"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Dato Histórico:</dt>
                                    <dd class="font-medium" x-text="colaborador.dato_historico"></dd>
                                </div>
                                <div class="flex justify-between items-center">
                                    <dt class="text-gray-500">Estado:</dt>
                                    <dd>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-blue-100 text-blue-700': colaborador.estado === 'Nuevo',
                                                  'bg-green-100 text-green-700': colaborador.estado === 'Crecio',
                                                  'bg-gray-100 text-gray-700': colaborador.estado === 'Igual',
                                                  'bg-red-100 text-red-700': colaborador.estado === 'Decrece',
                                                  'bg-yellow-100 text-yellow-700': colaborador.estado === 'Desvinculado'
                                              }"
                                              x-text="colaborador.estado"></span>
                                    </dd>
                                </div>
                            </dl>
                            <!-- Progress bar -->
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Progreso</span>
                                    <span x-text="Math.round((colaborador.dato_historico / Math.max(colaborador.dato_potencial, 1)) * 100) + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-fuchsia-600 h-2 rounded-full transition-all"
                                         :style="'width: ' + Math.min((colaborador.dato_historico / Math.max(colaborador.dato_potencial, 1)) * 100, 100) + '%'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i data-lucide="map-pin" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                                Ubicación
                            </h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Departamento:</dt>
                                    <dd class="font-medium" x-text="colaborador.departamento"></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Municipio:</dt>
                                    <dd class="font-medium" x-text="colaborador.municipio"></dd>
                                </div>
                                <div x-show="colaborador.tipo_territorio" class="flex justify-between">
                                    <dt class="text-gray-500">Tipo:</dt>
                                    <dd class="font-medium" x-text="colaborador.tipo_territorio"></dd>
                                </div>
                                <div x-show="colaborador.territorio" class="flex justify-between">
                                    <dt class="text-gray-500">Territorio:</dt>
                                    <dd class="font-medium" x-text="colaborador.territorio"></dd>
                                </div>
                                <div x-show="colaborador.barrio" class="flex justify-between">
                                    <dt class="text-gray-500">Barrio:</dt>
                                    <dd class="font-medium" x-text="colaborador.barrio"></dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Líder Directo -->
                    <div class="mt-6 bg-gradient-to-r from-fuchsia-50 to-amber-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i data-lucide="user-check" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                            Líder Directo
                        </h3>
                        <div x-show="colaborador.lider_directo" class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-fuchsia-600 text-white flex items-center justify-center font-bold mr-3">
                                    <span x-text="liderInfo.nombres ? liderInfo.nombres.charAt(0) : '?'"></span>
                                </div>
                                <div>
                                    <a :href="'?page=colaborador_detalle&id=' + liderInfo.id" x-show="liderInfo.id" class="font-medium text-gray-900 hover:text-fuchsia-600 transition-colors" x-text="liderInfo.nombres + ' ' + liderInfo.apellidos"></a>
                                    <p x-show="!liderInfo.id" class="font-medium" x-text="liderInfo.nombres + ' ' + (liderInfo.apellidos || '')"></p>
                                    <p class="text-sm text-gray-500" x-text="'Doc: ' + colaborador.lider_directo"></p>
                                </div>
                            </div>
                            <button @click="showModal = 'cambiarLider'" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">
                                Cambiar
                            </button>
                        </div>
                        <div x-show="!colaborador.lider_directo" class="flex items-center justify-between">
                            <p class="text-gray-500">Sin líder asignado</p>
                            <button @click="showModal = 'cambiarLider'" class="px-4 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700 text-sm">
                                Asignar Líder
                            </button>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div x-show="colaborador.observaciones" class="mt-6">
                        <h3 class="font-semibold text-gray-800 mb-2">Observaciones</h3>
                        <p class="text-gray-600 bg-gray-50 rounded-lg p-4" x-text="colaborador.observaciones"></p>
                    </div>
                </div>

                <!-- Tab: Curriculum -->
                <div x-show="activeTab === 'curriculum'">
                    <div x-show="loadingCurriculum" class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-fuchsia-600 mx-auto"></div>
                    </div>
                    <div x-show="!loadingCurriculum" class="space-y-6">
                        <!-- Información Personal -->
                        <div>
                            <h3 class="font-semibold text-gray-800 flex items-center mb-4">
                                <i data-lucide="user" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                                Información Personal / Familiar
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Equipo de Fútbol</label>
                                    <input type="text" x-model="curriculum.equipo_futbol" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Práctica Deportiva</label>
                                    <input type="text" x-model="curriculum.practica_deportiva" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="flex items-center text-sm font-medium text-gray-700 cursor-pointer">
                                        <input type="checkbox" x-model="curriculum.hijos_discapacidad" class="w-4 h-4 text-fuchsia-600 border-gray-300 rounded focus:ring-fuchsia-500 mr-2">
                                        ¿Tiene hijos con discapacidad?
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Gestión de Hijos (JSON dinámico) -->
                            <div class="mt-4 bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-medium text-gray-700">Hijos y Fechas de Nacimiento</h4>
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-gray-600">Cantidad:</label>
                                        <input type="number" min="0" 
                                               :value="curriculum.hijos_data.length"
                                               @change="updateHijosCount($event.target.value)" 
                                               class="w-20 px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(hijo, idx) in curriculum.hijos_data" :key="idx">
                                        <div class="flex items-center gap-4 bg-white p-2 rounded border border-gray-200">
                                            <span class="text-sm font-medium text-gray-500 w-20" x-text="'Hijo ' + (idx + 1)"></span>
                                            <input type="date" x-model="hijo.fecha" class="flex-1 px-3 py-1 text-sm border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                                            <span class="text-xs font-bold text-fuchsia-600 w-16 text-right" x-text="calcularEdad(hijo.fecha)"></span>
                                            <button @click="curriculum.hijos_data.splice(idx, 1)" class="text-red-500 hover:text-red-700 p-1">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Experiencia Laboral -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-800 flex items-center">
                                    <i data-lucide="briefcase" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                                    Experiencia Laboral
                                </h3>
                                <button @click="addCurriculumItem('experiencia_laboral')" class="text-sm text-fuchsia-600 hover:text-fuchsia-700">
                                    + Agregar
                                </button>
                            </div>
                            <div x-show="curriculum.experiencia_laboral.length === 0" class="text-gray-500 text-sm">
                                No hay experiencia laboral registrada.
                            </div>
                            <template x-for="(exp, idx) in curriculum.experiencia_laboral" :key="idx">
                                <div class="bg-gray-50 rounded-lg p-4 mb-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium" x-text="exp.cargo"></p>
                                            <p class="text-sm text-gray-600" x-text="exp.empresa"></p>
                                            <p class="text-xs text-gray-500" x-text="exp.fecha_inicio + ' - ' + (exp.fecha_fin || 'Actual')"></p>
                                            <p x-show="exp.descripcion" class="text-sm text-gray-600 mt-1" x-text="exp.descripcion"></p>
                                        </div>
                                        <button @click="removeCurriculumItem('experiencia_laboral', idx)" class="text-red-500 hover:text-red-700">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Formación Académica -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-800 flex items-center">
                                    <i data-lucide="graduation-cap" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                                    Formación Académica
                                </h3>
                                <button @click="addCurriculumItem('formacion_academica')" class="text-sm text-fuchsia-600 hover:text-fuchsia-700">
                                    + Agregar
                                </button>
                            </div>
                            <div x-show="curriculum.formacion_academica.length === 0" class="text-gray-500 text-sm">
                                No hay formación académica registrada.
                            </div>
                            <template x-for="(form, idx) in curriculum.formacion_academica" :key="idx">
                                <div class="bg-gray-50 rounded-lg p-4 mb-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium" x-text="form.titulo"></p>
                                            <p class="text-sm text-gray-600" x-text="form.institucion"></p>
                                            <p class="text-xs text-gray-500">
                                                <span x-text="form.nivel"></span> |
                                                <span x-text="form.fecha_inicio + ' - ' + (form.fecha_fin || 'En curso')"></span>
                                            </p>
                                        </div>
                                        <button @click="removeCurriculumItem('formacion_academica', idx)" class="text-red-500 hover:text-red-700">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Participación Política -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-800 flex items-center">
                                    <i data-lucide="flag" class="w-4 h-4 mr-2 text-fuchsia-600"></i>
                                    Participación Política
                                </h3>
                                <button @click="addCurriculumItem('participacion_politica')" class="text-sm text-fuchsia-600 hover:text-fuchsia-700">
                                    + Agregar
                                </button>
                            </div>
                            <div x-show="curriculum.participacion_politica.length === 0" class="text-gray-500 text-sm">
                                No hay participación política registrada.
                            </div>
                            <template x-for="(part, idx) in curriculum.participacion_politica" :key="idx">
                                <div class="bg-gray-50 rounded-lg p-4 mb-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium" x-text="part.cargo"></p>
                                            <p class="text-sm text-gray-600" x-text="part.organizacion"></p>
                                            <p class="text-xs text-gray-500" x-text="part.fecha_inicio + ' - ' + (part.fecha_fin || 'Actual')"></p>
                                            <p x-show="part.descripcion" class="text-sm text-gray-600 mt-1" x-text="part.descripcion"></p>
                                        </div>
                                        <button @click="removeCurriculumItem('participacion_politica', idx)" class="text-red-500 hover:text-red-700">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button @click="saveCurriculum()" class="px-4 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700">
                                Guardar Curriculum
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tab: Gestión de Red (Seguidores) -->
                <div x-show="activeTab === 'seguidores'" class="animate-fade-in-up">
                    <div x-show="loadingSeguidores" class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-fuchsia-600 mx-auto"></div>
                    </div>
                    
                    <div x-show="!loadingSeguidores">
                        <div x-show="seguidores.length === 0" class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <i data-lucide="users" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                            <h4 class="text-lg font-bold text-gray-700 mb-2">Sin Red Activa</h4>
                            <p class="text-gray-500">Este colaborador no tiene líderes ni simpatizantes directos asignados a su estructura.</p>
                        </div>
                        
                        <div x-show="seguidores.length > 0" class="space-y-8">
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Equipo Directo -->
                                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between border-l-4 border-l-fuchsia-600">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Equipo Directo</p>
                                        <div class="flex items-end gap-3">
                                            <span class="text-4xl font-black text-gray-800" x-text="statsSeguidores.total_directos || 0"></span>
                                            <span class="text-sm text-gray-500 mb-1 leading-none">Nivel 1</span>
                                        </div>
                                    </div>
                                    <div class="w-12 h-12 rounded-full bg-fuchsia-50 flex items-center justify-center text-fuchsia-600">
                                        <i data-lucide="users" class="w-6 h-6"></i>
                                    </div>
                                </div>
                                
                                <!-- Red Total -->
                                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between border-l-4 border-l-amber-500">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Red Consolidada</p>
                                        <div class="flex items-end gap-3">
                                            <span class="text-4xl font-black text-gray-800" x-text="statsSeguidores.total_red || 0"></span>
                                            <span class="text-sm text-gray-500 mb-1 leading-none">Total Estructura</span>
                                        </div>
                                    </div>
                                    <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                                        <i data-lucide="network" class="w-6 h-6"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Territorial Breakdown -->
                            <div x-show="statsSeguidores.desglose_territorio && statsSeguidores.desglose_territorio.length > 0">
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i data-lucide="map" class="w-5 h-5 text-gray-400"></i>
                                    Impacto Territorial
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <template x-for="zona in statsSeguidores.desglose_territorio.slice(0, 6)" :key="zona.municipio + zona.barrio">
                                        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:border-fuchsia-200 transition-colors">
                                            <div class="flex justify-between items-start mb-2">
                                                <span class="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-bold text-gray-500 uppercase" x-text="zona.municipio"></span>
                                                <span class="text-lg font-black text-fuchsia-900" x-text="zona.total"></span>
                                            </div>
                                            <h4 class="text-sm font-bold text-gray-800 truncate" x-text="zona.barrio"></h4>
                                            <div class="w-full bg-gray-100 rounded-full h-1 mt-3">
                                                <div class="bg-fuchsia-600 h-1 rounded-full opacity-60" :style="'width: ' + Math.min(100, (zona.total / Math.max(statsSeguidores.total_red, 1)) * 100) + '%'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Team List -->
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i data-lucide="users-2" class="w-5 h-5 text-gray-400"></i>
                                    Registro de Seguidores (Nivel 1)
                                </h3>
                                
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr class="text-[10px] text-gray-500 uppercase tracking-widest border-b border-gray-100 bg-gray-50/50">
                                                    <th class="px-6 py-4 font-semibold">Integrantes</th>
                                                    <th class="px-6 py-4 font-semibold">Perfil</th>
                                                    <th class="px-6 py-4 font-semibold">Ubicación</th>
                                                    <th class="px-6 py-4 font-semibold text-right">Métricas</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <template x-for="seg in seguidores" :key="seg.id">
                                                    <tr class="hover:bg-gray-50 transition-colors group">
                                                        <td class="px-6 py-4">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-fuchsia-100 to-fuchsia-200 flex items-center justify-center text-sm font-bold text-fuchsia-700">
                                                                    <span x-text="seg.nombres.charAt(0) + (seg.apellidos ? seg.apellidos.charAt(0) : '')"></span>
                                                                </div>
                                                                <div>
                                                                    <a :href="'index.php?page=colaborador_detalle&id=' + seg.id" class="font-bold text-gray-800 hover:text-fuchsia-600 transition-colors" x-text="seg.nombre_completo"></a>
                                                                    <div class="text-[10px] text-gray-400 flex items-center mt-0.5">
                                                                        <i data-lucide="credit-card" class="w-3 h-3 mr-1"></i>
                                                                        <span x-text="seg.documento"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <span class="px-2.5 py-1 rounded border text-[10px] font-bold uppercase tracking-wider bg-white shadow-sm" x-text="seg.perfil"></span>
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-600">
                                                            <span class="block truncate max-w-[150px]" x-text="seg.municipio"></span>
                                                            <span class="text-xs text-gray-400 truncate max-w-[150px] inline-block" x-text="seg.barrio"></span>
                                                        </td>
                                                        <td class="px-6 py-4 text-right">
                                                            <div class="flex flex-col items-end">
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                                                      :class="{
                                                                          'bg-green-100 text-green-700': seg.estado === 'Nuevo' || seg.estado === 'Crecio',
                                                                          'bg-gray-100 text-gray-700': seg.estado === 'Igual',
                                                                          'bg-red-100 text-red-700': seg.estado === 'Decrece' || seg.estado === 'Desvinculado'
                                                                      }"
                                                                      x-text="seg.estado"></span>
                                                                <span class="text-[10px] text-gray-400 mt-1 font-mono" x-text="'Potencial: ' + seg.dato_potencial"></span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Historial -->
                <div x-show="activeTab === 'historial'">
                    <div x-show="loadingHistorial" class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-fuchsia-600 mx-auto"></div>
                    </div>
                    <div x-show="!loadingHistorial">
                        <div x-show="historial.length === 0" class="text-center py-8 text-gray-500">
                            No hay historial de cambios registrado.
                        </div>
                        <div x-show="historial.length > 0" class="relative">
                            <!-- Timeline -->
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                            <div class="space-y-4 ml-12">
                                <template x-for="(item, idx) in historial" :key="idx">
                                    <div class="relative">
                                        <!-- Dot -->
                                        <div class="absolute -left-10 mt-1.5 w-4 h-4 rounded-full border-2 border-white"
                                             :class="item.tipo === 'cambio_lider' ? 'bg-blue-500' : item.tipo === 'reevaluacion' ? 'bg-amber-500' : 'bg-gray-400'"></div>
                                        <!-- Content -->
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex justify-between items-start mb-2">
                                                <span class="font-medium text-gray-800" x-text="item.descripcion"></span>
                                                <span class="text-xs text-gray-500" x-text="new Date(item.fecha).toLocaleString()"></span>
                                            </div>
                                            <template x-if="item.tipo === 'cambio_lider'">
                                                <p class="text-sm text-gray-600">
                                                    <span x-text="item.detalle.lider_anterior"></span>
                                                    <i data-lucide="arrow-right" class="w-3 h-3 inline mx-1"></i>
                                                    <span x-text="item.detalle.lider_nuevo"></span>
                                                </p>
                                            </template>
                                            <template x-if="item.tipo === 'reevaluacion' || item.tipo === 'actualizacion'">
                                                <div class="text-sm text-gray-600 space-y-1">
                                                    <p x-text="'Potencial: ' + item.detalle.dato_potencial"></p>
                                                    <p x-text="'Estado: ' + item.detalle.estado"></p>
                                                </div>
                                            </template>
                                            <p x-show="item.motivo" class="text-sm text-gray-500 mt-2 italic" x-text="'Motivo: ' + item.motivo"></p>
                                            <p class="text-xs text-gray-400 mt-1" x-text="'Por: ' + item.usuario"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Cambiar Líder -->
    <div x-show="showModal === 'cambiarLider'" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" @click="showModal = null"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cambiar Líder Directo</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Líder</label>
                        <input type="text" x-model="liderSearch" @input="searchLideres()"
                               placeholder="Nombre o documento..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                        <!-- Resultados de búsqueda -->
                        <div x-show="lideresSearchResults.length > 0" class="mt-2 border border-gray-200 rounded-lg max-h-48 overflow-y-auto">
                            <template x-for="lid in lideresSearchResults" :key="lid.documento">
                                <button @click="selectLider(lid)" type="button"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                    <p class="font-medium" x-text="lid.nombre_completo"></p>
                                    <p class="text-xs text-gray-500" x-text="lid.documento + ' - ' + lid.perfil"></p>
                                </button>
                            </template>
                        </div>
                        <!-- Líder seleccionado -->
                        <div x-show="selectedLider" class="mt-2 bg-fuchsia-50 rounded-lg p-3 flex items-center justify-between">
                            <div>
                                <p class="font-medium" x-text="selectedLider?.nombre_completo"></p>
                                <p class="text-xs text-gray-500" x-text="selectedLider?.documento"></p>
                            </div>
                            <button @click="selectedLider = null" class="text-gray-400 hover:text-gray-600">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del cambio</label>
                        <textarea x-model="cambioLiderMotivo" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500"
                                  placeholder="Explique el motivo del cambio..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showModal = null" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button @click="confirmarCambioLider()" class="px-4 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700">
                        Confirmar Cambio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Reevaluar -->
    <div x-show="showModal === 'reevaluar'" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" @click="showModal = null"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Reevaluar Dato Potencial</h3>
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Valor actual:</span>
                            <span class="font-medium" x-text="colaborador.dato_potencial"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Dato histórico:</span>
                            <span class="font-medium" x-text="colaborador.dato_historico"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Dato Potencial</label>
                        <input type="number" x-model.number="nuevoPotencial" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de la reevaluación</label>
                        <textarea x-model="reevaluarMotivo" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500"
                                  placeholder="Explique el motivo de la reevaluación..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showModal = null" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button @click="confirmarReevaluacion()" class="px-4 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700">
                        Confirmar Reevaluación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Agregar Item Curriculum -->
    <div x-show="showModal === 'addCurriculum'" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" @click="showModal = null"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4" x-text="curriculumItemType === 'experiencia_laboral' ? 'Agregar Experiencia' : curriculumItemType === 'formacion_academica' ? 'Agregar Formación' : 'Agregar Participación'"></h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-text="curriculumItemType === 'formacion_academica' ? 'Título' : 'Cargo'"></label>
                        <input type="text" x-model="curriculumForm.cargo"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" x-text="curriculumItemType === 'formacion_academica' ? 'Institución' : curriculumItemType === 'participacion_politica' ? 'Organización' : 'Empresa'"></label>
                        <input type="text" x-model="curriculumForm.empresa"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                    </div>
                    <div x-show="curriculumItemType === 'formacion_academica'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
                        <select x-model="curriculumForm.nivel"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                            <option value="Primaria">Primaria</option>
                            <option value="Secundaria">Secundaria</option>
                            <option value="Técnico">Técnico</option>
                            <option value="Tecnológico">Tecnológico</option>
                            <option value="Pregrado">Pregrado</option>
                            <option value="Especialización">Especialización</option>
                            <option value="Maestría">Maestría</option>
                            <option value="Doctorado">Doctorado</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                            <input type="date" x-model="curriculumForm.fecha_inicio"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                            <input type="date" x-model="curriculumForm.fecha_fin"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500">
                        </div>
                    </div>
                    <div x-show="curriculumItemType !== 'formacion_academica'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea x-model="curriculumForm.descripcion" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fuchsia-500 focus:border-fuchsia-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showModal = null" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button @click="confirmAddCurriculumItem()" class="px-4 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700">
                        Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function colaboradorDetalle() {
    return {
        colaboradorId: <?= json_encode($colaboradorId) ?>,
        campanaId: <?= json_encode($campanaId) ?>,
        loading: true,
        colaborador: null,
        liderInfo: { nombres: '', apellidos: '' },
        activeTab: 'info',
        showModal: null,

        // Curriculum
        loadingCurriculum: false,
        curriculum: {
            experiencia_laboral: [],
            formacion_academica: [],
            participacion_politica: [],
            hijos_data: [],
            hijos_discapacidad: false,
            equipo_futbol: '',
            practica_deportiva: ''
        },
        curriculumItemType: null,
        curriculumForm: {
            cargo: '',
            empresa: '',
            nivel: 'Pregrado',
            fecha_inicio: '',
            fecha_fin: '',
            descripcion: ''
        },

        // Seguidores
        loadingSeguidores: false,
        seguidores: [],
        statsSeguidores: {
            total_directos: 0,
            total_red: 0,
            desglose_territorio: []
        },
        seguidoresCount: 0,

        // Historial
        loadingHistorial: false,
        historial: [],

        // Cambiar líder
        liderSearch: '',
        lideresSearchResults: [],
        selectedLider: null,
        cambioLiderMotivo: '',

        // Reevaluar
        nuevoPotencial: 0,
        reevaluarMotivo: '',

        async init() {
            await this.loadColaborador();
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        },

        async loadColaborador() {
            this.loading = true;
            try {
                const response = await fetch(`api/colaboradores.php?id=${this.colaboradorId}`);
                const result = await response.json();
                if (result.success) {
                    this.colaborador = result.data;
                    this.nuevoPotencial = this.colaborador.dato_potencial;
                    // Cargar info del líder si existe
                    if (this.colaborador.lider_directo) {
                        await this.loadLiderInfo();
                    }
                    // Contar seguidores
                    await this.countSeguidores();
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadLiderInfo() {
            try {
                const response = await fetch(`api/colaboradores.php?action=lideres&campana_id=${this.campanaId}&search=${this.colaborador.lider_directo}`);
                const result = await response.json();
                if (result.success && result.data.length > 0) {
                    this.liderInfo = result.data[0];
                }
            } catch (error) {
                console.error('Error cargando líder:', error);
            }
        },

        async countSeguidores() {
            try {
                const response = await fetch(`api/colaboradores.php?action=seguidores&id=${this.colaboradorId}`);
                const result = await response.json();
                if (result.success) {
                    this.seguidoresCount = result.count;
                }
            } catch (error) {
                console.error('Error contando seguidores:', error);
            }
        },

        async loadCurriculum() {
            if (this.curriculum.experiencia_laboral.length > 0) return;
            this.loadingCurriculum = true;
            try {
                const response = await fetch(`api/colaboradores.php?action=curriculum&id=${this.colaboradorId}`);
                const result = await response.json();
                if (result.success) {
                    this.curriculum = {
                        experiencia_laboral: result.data.experiencia_laboral || [],
                        formacion_academica: result.data.formacion_academica || [],
                        participacion_politica: result.data.participacion_politica || [],
                        hijos_data: result.data.hijos_data || [],
                        hijos_discapacidad: result.data.hijos_discapacidad == 1,
                        equipo_futbol: result.data.equipo_futbol || '',
                        practica_deportiva: result.data.practica_deportiva || ''
                    };
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loadingCurriculum = false;
            }
        },

        async loadSeguidores() {
            if (this.seguidores.length > 0) return;
            this.loadingSeguidores = true;
            try {
                const response = await fetch(`api/colaboradores.php?action=seguidores&id=${this.colaboradorId}`);
                const result = await response.json();
                if (result.success) {
                    this.seguidores = result.data;
                    this.statsSeguidores = result.stats || this.statsSeguidores;
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loadingSeguidores = false;
            }
        },

        async loadHistorial() {
            if (this.historial.length > 0) return;
            this.loadingHistorial = true;
            try {
                const response = await fetch(`api/colaboradores.php?action=historial&id=${this.colaboradorId}`);
                const result = await response.json();
                if (result.success) {
                    this.historial = result.data;
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loadingHistorial = false;
            }
        },

        updateHijosCount(countStr) {
            const count = parseInt(countStr) || 0;
            const currentLen = this.curriculum.hijos_data.length;
            if (count > currentLen) {
                for (let i = currentLen; i < count; i++) {
                    this.curriculum.hijos_data.push({ fecha: '' });
                }
            } else if (count < currentLen && count >= 0) {
                this.curriculum.hijos_data.splice(count);
            }
        },

        calcularEdad(fechaStr) {
            if (!fechaStr) return '';
            const fecha = new Date(fechaStr);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fecha.getFullYear();
            const m = hoy.getMonth() - fecha.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) {
                edad--;
            }
            return edad >= 0 ? edad + ' años' : '';
        },

        addCurriculumItem(type) {
            this.curriculumItemType = type;
            this.curriculumForm = {
                cargo: '',
                empresa: '',
                nivel: 'Pregrado',
                fecha_inicio: '',
                fecha_fin: '',
                descripcion: ''
            };
            this.showModal = 'addCurriculum';
        },

        confirmAddCurriculumItem() {
            const item = this.curriculumItemType === 'formacion_academica' ? {
                titulo: this.curriculumForm.cargo,
                institucion: this.curriculumForm.empresa,
                nivel: this.curriculumForm.nivel,
                fecha_inicio: this.curriculumForm.fecha_inicio,
                fecha_fin: this.curriculumForm.fecha_fin
            } : {
                cargo: this.curriculumForm.cargo,
                empresa: this.curriculumForm.empresa,
                organizacion: this.curriculumForm.empresa,
                fecha_inicio: this.curriculumForm.fecha_inicio,
                fecha_fin: this.curriculumForm.fecha_fin,
                descripcion: this.curriculumForm.descripcion
            };

            this.curriculum[this.curriculumItemType].push(item);
            this.showModal = null;
        },

        removeCurriculumItem(type, idx) {
            this.curriculum[type].splice(idx, 1);
        },

        async saveCurriculum() {
            try {
                const response = await fetch('api/colaboradores.php?action=curriculum', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        colaborador_id: this.colaboradorId,
                        ...this.curriculum
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Curriculum guardado exitosamente');
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error al guardar curriculum');
            }
        },

        async searchLideres() {
            if (this.liderSearch.length < 2) {
                this.lideresSearchResults = [];
                return;
            }
            try {
                const response = await fetch(`api/colaboradores.php?action=lideres&campana_id=${this.campanaId}&search=${encodeURIComponent(this.liderSearch)}`);
                const result = await response.json();
                if (result.success) {
                    // Excluir al colaborador actual
                    this.lideresSearchResults = result.data.filter(l => l.documento !== this.colaborador.documento);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        selectLider(lider) {
            this.selectedLider = lider;
            this.lideresSearchResults = [];
            this.liderSearch = '';
        },

        async confirmarCambioLider() {
            try {
                const response = await fetch('api/colaboradores.php?action=cambiar_lider', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        colaborador_id: this.colaboradorId,
                        nuevo_lider: this.selectedLider?.documento || null,
                        motivo: this.cambioLiderMotivo
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Líder actualizado exitosamente');
                    this.showModal = null;
                    await this.loadColaborador();
                    // Recargar historial si está visible
                    this.historial = [];
                    if (this.activeTab === 'historial') {
                        await this.loadHistorial();
                    }
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error al cambiar líder');
            }
        },

        async confirmarReevaluacion() {
            try {
                const response = await fetch('api/colaboradores.php?action=reevaluar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        colaborador_id: this.colaboradorId,
                        dato_potencial: this.nuevoPotencial,
                        motivo: this.reevaluarMotivo
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Reevaluación realizada exitosamente');
                    this.showModal = null;
                    await this.loadColaborador();
                    // Recargar historial si está visible
                    this.historial = [];
                    if (this.activeTab === 'historial') {
                        await this.loadHistorial();
                    }
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error en la reevaluación');
            }
        }
    };
}
</script>
