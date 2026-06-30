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
                    <template x-if="colaborador.foto">
                        <img :src="'<?= url('') ?>' + colaborador.foto" class="w-14 h-14 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-gray-100 mr-3">
                    </template>
                    <template x-if="!colaborador.foto">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-lg mr-3"
                             :style="'background: linear-gradient(135deg, #FF00FF, #FFD700)'">
                            <span x-text="colaborador.nombres ? colaborador.nombres.charAt(0) : ''"></span>
                        </div>
                    </template>
                    <div>
                        <span x-text="colaborador.nombres + ' ' + colaborador.apellidos"></span>
                        <p class="text-sm font-normal text-gray-500" x-text="colaborador.perfil"></p>
                    </div>
                </h1>
            </div>

            <div class="flex gap-2 mt-4 md:mt-0">
                <button @click="abrirEditar()" class="px-4 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700 flex items-center shadow-lg shadow-fuchsia-500/20">
                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                    Editar
                </button>
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
                    <button @click="activeTab = 'actividad'; loadActividad()"
                            :class="activeTab === 'actividad' ? 'border-fuchsia-500 text-fuchsia-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-6 py-4 border-b-2 font-medium text-sm flex items-center">
                        <i data-lucide="activity" class="w-4 h-4 mr-2"></i>
                        Actividad
                        <span x-show="actividadCount > 0" class="ml-2 px-2 py-0.5 bg-fuchsia-100 text-fuchsia-600 rounded-full text-xs" x-text="actividadCount"></span>
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Tab: Información -->
                <div x-show="activeTab === 'info'">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Foto Grande -->
                        <div x-show="colaborador.foto" class="bg-gray-50 rounded-lg p-4 flex flex-col items-center justify-start">
                            <img :src="'<?= url('') ?>' + colaborador.foto" 
                                 class="w-full max-w-[200px] aspect-square rounded-2xl object-cover shadow-xl border-4 border-white ring-1 ring-gray-100">
                            <p class="text-sm font-medium text-gray-700 mt-3 text-center" x-text="colaborador.nombres + ' ' + colaborador.apellidos"></p>
                        </div>
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
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                            <div class="space-y-4 ml-12">
                                <template x-for="(item, idx) in historial" :key="idx">
                                    <div class="relative">
                                        <div class="absolute -left-10 mt-1.5 w-4 h-4 rounded-full border-2 border-white"
                                             :class="item.tipo === 'cambio_lider' ? 'bg-blue-500' : item.tipo === 'reevaluacion' ? 'bg-amber-500' : 'bg-gray-400'"></div>
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

                <!-- Tab: Actividad (ALAS Timeline) -->
                <div x-show="activeTab === 'actividad'">
                    <div class="flex items-center gap-3 mb-4">
                        <template x-for="t in actividadTipos" :key="t">
                            <button @click="toggleActividadFiltro(t)"
                                    :class="actividadFiltros.includes(t) ? 'bg-fuchsia-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all">
                                <span x-text="t.replace(/_/g, ' ')"></span>
                            </button>
                        </template>
                    </div>
                    <div x-show="loadingActividad" class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-fuchsia-600 mx-auto"></div>
                    </div>
                    <div x-show="!loadingActividad">
                        <div x-show="actividad.length === 0" class="text-center py-8 text-gray-500">
                            No hay actividad registrada. Las acciones del colaborador aparecerán aquí automáticamente.
                        </div>
                        <div x-show="actividad.length > 0" class="space-y-3">
                            <template x-for="item in actividad" :key="item.id">
                                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all">
                                    <div class="w-8 h-8 rounded-full bg-fuchsia-100 flex items-center justify-center flex-shrink-0">
                                        <i :data-lucide="iconoActividad(item.tipo)" class="w-4 h-4 text-fuchsia-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800" x-text="item.descripcion"></p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs px-2 py-0.5 bg-gray-200 text-gray-600 rounded" x-text="item.tipo.replace(/_/g, ' ')"></span>
                                            <span class="text-xs text-gray-400" x-text="formatDateRelativo(item.creado_en)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="actividad.length >= actividadPerPage" class="text-center pt-4">
                                <button @click="actividadPage++; loadActividad()" class="text-sm text-fuchsia-600 hover:text-fuchsia-800 font-medium">
                                    Cargar más...
                                </button>
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

    <!-- Modal: Editar Colaborador -->
    <div x-show="showModal === 'editar'" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" @paste.prevent="editHandlePaste($event)" tabindex="0">
        <div class="flex items-center justify-center min-h-screen px-4" @click.self="cerrarEditar()">
            <div class="fixed inset-0 bg-black/50 transition-opacity" @click="cerrarEditar()"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between z-10 rounded-t-xl">
                    <h2 class="text-xl font-bold">Editar Colaborador</h2>
                    <button @click="cerrarEditar()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form @submit.prevent="guardarEdicion()" class="p-6 space-y-6">
                    <!-- Foto -->
                    <div class="bg-slate-50 p-6 rounded-3xl border-2 border-dashed border-slate-200">
                        <h3 class="font-black text-gray-700 mb-4 flex items-center uppercase text-xs tracking-widest">
                            <i data-lucide="camera" class="w-4 h-4 mr-2 text-fuchsia-500"></i>Fotografía
                        </h3>
                        <div class="flex flex-col md:flex-row items-center gap-8">
                            <div class="relative w-40 h-40 shrink-0">
                                <div class="w-full h-full rounded-[2rem] bg-white shadow-2xl border-4 border-white overflow-hidden relative group">
                                    <template x-if="!editMostrandoCamara">
                                        <div class="w-full h-full flex items-center justify-center bg-slate-100">
                                            <template x-if="editForm.foto || editExistingFoto">
                                                <img :src="editForm.foto || ('<?= url('') ?>' + editExistingFoto)" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!editForm.foto && !editExistingFoto">
                                                <div class="text-center p-4">
                                                    <i data-lucide="user" class="w-12 h-12 text-slate-300 mx-auto mb-2"></i>
                                                    <p class="text-[9px] font-black text-slate-400 uppercase">Sin Foto</p>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="editMostrandoCamara">
                                        <video x-ref="editVideo" class="w-full h-full object-cover bg-black mirror" autoplay playsinline></video>
                                    </template>
                                </div>
                                <template x-if="editMostrandoCamara">
                                    <div class="absolute -top-2 -right-2 bg-red-500 text-white text-[9px] font-black px-2 py-1 rounded-full animate-pulse shadow-lg uppercase">En Vivo</div>
                                </template>
                            </div>
                            <div class="flex-1 space-y-4">
                                <p class="text-xs text-gray-500">Puedes tomar una foto, subir un archivo o pegar una imagen desde el portapapeles <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded text-[10px] font-mono">Ctrl+V</kbd></p>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="editAbrirCamara()" x-show="!editMostrandoCamara" class="px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-xs flex items-center gap-2 hover:bg-slate-900 transition-all shadow-lg shadow-primary/20">
                                        <i data-lucide="aperture" class="w-4 h-4"></i> USAR CÁMARA
                                    </button>
                                    <button type="button" @click="editCapturarFoto()" x-show="editMostrandoCamara" class="px-5 py-2.5 bg-green-600 text-white rounded-xl font-bold text-xs flex items-center gap-2 hover:bg-green-700 transition-all shadow-lg shadow-green-500/20">
                                        <i data-lucide="camera" class="w-4 h-4"></i> CAPTURAR
                                    </button>
                                    <button type="button" @click="editDetenerCamara()" x-show="editMostrandoCamara" class="px-4 py-2 bg-white border border-red-100 text-red-500 rounded-xl font-bold text-xs hover:bg-red-50 transition-all">
                                        CANCELAR
                                    </button>
                                    <input type="file" x-ref="editFileInput" @change="editHandleFileUpload($event)" accept="image/*" class="hidden">
                                    <button type="button" @click="$refs.editFileInput.click()" x-show="!editMostrandoCamara" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">
                                        SUBIR ARCHIVO
                                    </button>
                                    <template x-if="editForm.foto || editExistingFoto">
                                        <button type="button" @click="editForm.foto = null; editExistingFoto = null" class="px-3 py-2 text-red-400 hover:text-red-600 transition-colors" title="Eliminar foto">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos Personales -->
                    <div>
                        <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                            <i data-lucide="user" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Datos Personales
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nombres *</label>
                                <input type="text" x-model="editForm.nombres" required class="input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Apellidos *</label>
                                <input type="text" x-model="editForm.apellidos" required class="input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Fecha Nac. *</label>
                                <input type="date" x-model="editForm.fecha_nacimiento" required class="input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Tipo Doc.</label>
                                <select x-model="editForm.tipo_documento" class="input">
                                    <option value="CC">Cédula</option>
                                    <option value="TI">Tarjeta Identidad</option>
                                    <option value="CE">Cédula Extranjería</option>
                                    <option value="NIT">NIT</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Documento *</label>
                                <input type="text" x-model="editForm.documento" required class="input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Género *</label>
                                <select x-model="editForm.genero" required class="input">
                                    <option value="">Seleccionar...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Perfil -->
                    <div>
                        <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                            <i data-lucide="briefcase" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Perfil del Colaborador
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Perfil *</label>
                                <select x-model="editForm.perfil" required class="input">
                                    <option value="">Seleccionar...</option>
                                    <option value="Líder / Coordinador">Líder / Coordinador</option>
                                    <option value="Simpatizante">Simpatizante</option>
                                    <option value="Lider Ambiental">Lider Ambiental</option>
                                    <option value="Lider Gremial">Lider Gremial</option>
                                    <option value="Lider Social">Lider Social</option>
                                    <option value="Lider Empresarial">Lider Empresarial</option>
                                    <option value="Influencer">Influencer</option>
                                    <option value="Lider Juvenil">Lider Juvenil</option>
                                    <option value="Lider Poblacional">Lider Poblacional</option>
                                    <option value="Lider Diferencial">Lider Diferencial</option>
                                    <option value="Medios Tradicionales">Medios Tradicionales</option>
                                    <option value="Amigo">Amigo</option>
                                    <option value="Familia">Familia</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Nivel</label>
                                <select x-model="editForm.nivel_participacion" class="input">
                                    <option value="Simpatizante">Simpatizante</option>
                                    <option value="Multiplicador">Multiplicador</option>
                                    <option value="Lider">Lider</option>
                                    <option value="Coordinador">Coordinador</option>
                                    <option value="Contratista">Contratista</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Padrino Referente</label>
                                <div class="relative">
                                    <input type="text" x-model="editSearchLider" @input="editBuscarLideres()" placeholder="Buscar por nombre o documento..." class="input">
                                    <div x-show="editLideresEncontrados.length > 0" class="absolute z-10 w-full bg-white border rounded-lg shadow-lg mt-1 max-h-40 overflow-y-auto">
                                        <template x-for="l in editLideresEncontrados" :key="l.documento">
                                            <div @click="editSeleccionarLider(l)" class="p-2 hover:bg-gray-100 cursor-pointer">
                                                <p class="font-medium" x-text="l.nombres + ' ' + l.apellidos"></p>
                                                <p class="text-xs text-gray-500" x-text="l.documento + ' - ' + l.perfil"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" x-show="editForm.lider_directo">Doc: <span x-text="editForm.lider_directo"></span></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Dato Potencial</label>
                                <input type="number" x-model="editForm.dato_potencial" min="0" class="input">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Dato Histórico</label>
                                <input type="number" x-model="editForm.dato_historico" min="0" class="input">
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div>
                        <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                            <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Ubicación Geográfica
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Departamento *</label>
                                <select x-model="editForm.departamento" @change="editCargarMunicipios()" required class="input">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="dep in editListas.departamentos" :key="dep">
                                        <option :value="dep" x-text="dep"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Municipio *</label>
                                <select x-model="editMunicipioRaw" @change="editSelectMunicipio()" required class="input" :disabled="!editForm.departamento">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="mun in editListas.municipios" :key="mun.cod_mpio">
                                        <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Tipo Territorio</label>
                                <select x-model="editForm.tipo_territorio" @change="editCargarTerritorios()" class="input" :disabled="!editForm.municipio">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="tipo in editListas.tipos_territorio" :key="tipo">
                                        <option :value="tipo" x-text="tipo"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Territorio</label>
                                <select x-model="editForm.territorio" @change="editCargarBarrios()" class="input" :disabled="!editForm.tipo_territorio">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="terr in editListas.territorios" :key="terr">
                                        <option :value="terr" x-text="terr"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Barrio/Vereda</label>
                                <select x-model="editForm.barrio" class="input" :disabled="!editForm.territorio">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="barrio in editListas.barrios" :key="barrio">
                                        <option :value="barrio" x-text="barrio"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Info Electoral -->
                    <div>
                        <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                            <i data-lucide="vote" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Información Electoral
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Puesto de Votación</label>
                                <select x-model="editForm.puesto_votacion" class="input" :disabled="!editForm.cod_mpio">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="p in editListas.puestos" :key="p.id">
                                        <option :value="p.puesto" x-text="p.puesto"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Mesa</label>
                                <input type="text" x-model="editForm.mesa_votacion" class="input" placeholder="Ej: 14">
                            </div>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div>
                        <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                            <i data-lucide="phone" class="w-5 h-5 mr-2 text-fuchsia-500"></i>Contacto
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Email</label>
                                <input type="email" x-model="editForm.email" class="input" placeholder="correo@ejemplo.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Teléfono</label>
                                <input type="tel" x-model="editForm.telefono" class="input" placeholder="300 123 4567">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2">Observaciones</label>
                                <textarea x-model="editForm.observaciones" rows="3" class="input" placeholder="Notas adicionales..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pt-4 border-t">
                        <button type="submit" class="px-6 py-3 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700 font-medium flex items-center shadow-lg shadow-fuchsia-500/20" :disabled="editando">
                            <i data-lucide="save" class="w-5 h-5 mr-2"></i>
                            <span x-text="editando ? 'Guardando...' : 'Guardar Cambios'"></span>
                        </button>
                        <button type="button" @click="cerrarEditar()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                            Cancelar
                        </button>
                    </div>
                </form>
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

        // Actividad (ALAS Timeline)
        loadingActividad: false,
        actividad: [],
        actividadPage: 1,
        actividadPerPage: 20,
        actividadCount: 0,
        actividadTipos: ['registro','evento_asistio','compromiso_creado','donacion_hizo','whatsapp_enviado','whatsapp_recibido','estado_cambio','evaluacion','lider_cambio','cumpleaños'],
        actividadFiltros: [],

        // Cambiar líder
        liderSearch: '',
        lideresSearchResults: [],
        selectedLider: null,
        cambioLiderMotivo: '',

        // Reevaluar
        nuevoPotencial: 0,
        reevaluarMotivo: '',

        // Edit state
        editando: false,
        editForm: {
            id: null, campana_id: null, nombres: '', apellidos: '',
            tipo_documento: 'CC', documento: '', fecha_nacimiento: '', genero: '',
            perfil: '', nivel_participacion: 'Simpatizante', departamento: '', municipio: '',
            cod_mpio: '', tipo_territorio: '', territorio: '', barrio: '',
            lider_directo: '', puesto_votacion: '', mesa_votacion: '',
            dato_potencial: 0, dato_historico: 0, email: '', telefono: '',
            foto: null, observaciones: ''
        },
        editExistingFoto: null,
        editMostrandoCamara: false,
        editVideoStream: null,
        editMunicipioRaw: '',
        editSearchLider: '',
        editLideresEncontrados: [],
        editListas: {
            departamentos: [],
            municipios: [],
            tipos_territorio: [],
            territorios: [],
            barrios: [],
            puestos: []
        },

        async init() {
            await this.loadColaborador();
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        },

        async loadColaborador() {
            this.loading = true;
            try {
                const response = await fetch(`api/colaboradores.php?id=${this.colaboradorId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const text = await response.text();
                let result;
                try { result = JSON.parse(text); } catch(e) { console.error('API no JSON:', text.substring(0,200)); return; }
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
                const response = await fetch(`api/colaboradores.php?action=lideres&campana_id=${this.campanaId}&search=${this.colaborador.lider_directo}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
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
                const response = await fetch(`api/colaboradores.php?action=seguidores&id=${this.colaboradorId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const text = await response.text();
                let result;
                try { result = JSON.parse(text); } catch(e) { console.error('API seguidores no JSON:', text.substring(0,200)); return; }
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
                const response = await fetch(`api/colaboradores.php?action=curriculum&id=${this.colaboradorId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const text = await response.text();
                let result;
                try { result = JSON.parse(text); } catch(e) { console.error('API curriculum no JSON:', text.substring(0,200)); return; }
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
                const response = await fetch(`api/colaboradores.php?action=seguidores&id=${this.colaboradorId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const text = await response.text();
                let result;
                try { result = JSON.parse(text); } catch(e) { console.error('API seguidores list no JSON:', text.substring(0,200)); return; }
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
                const response = await fetch(`api/colaboradores.php?action=historial&id=${this.colaboradorId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const text = await response.text();
                let result;
                try { result = JSON.parse(text); } catch(e) { console.error('API historial no JSON:', text.substring(0,200)); return; }
                if (result.success) {
                    this.historial = result.data;
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loadingHistorial = false;
            }
        },

        async loadActividad() {
            if (this.loadingActividad) return;
            this.loadingActividad = true;
            try {
                let url = `api/timeline.php?action=list&colaborador_id=${this.colaboradorId}&page=${this.actividadPage}&per_page=${this.actividadPerPage}`;
                if (this.actividadFiltros.length > 0) {
                    url += '&' + this.actividadFiltros.map(t => `tipos[]=${t}`).join('&');
                }
                const resp = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await resp.json();
                if (json.success) {
                    if (this.actividadPage === 1) {
                        this.actividad = json.data.data;
                    } else {
                        this.actividad = [...this.actividad, ...json.data.data];
                    }
                    this.actividadCount = json.data.total;
                }
            } catch (e) { console.error('ALAS error cargando actividad:', e); }
            this.loadingActividad = false;
        },

        toggleActividadFiltro(tipo) {
            const idx = this.actividadFiltros.indexOf(tipo);
            if (idx >= 0) {
                this.actividadFiltros.splice(idx, 1);
            } else {
                this.actividadFiltros.push(tipo);
            }
            this.actividadPage = 1;
            this.actividad = [];
            this.loadActividad();
        },

        iconoActividad(tipo) {
            const iconos = {
                registro: 'user-plus',
                evento_asistio: 'calendar-check',
                compromiso_creado: 'handshake',
                donacion_hizo: 'dollar-sign',
                whatsapp_enviado: 'message-circle',
                whatsapp_recibido: 'message-square',
                estado_cambio: 'refresh-cw',
                evaluacion: 'bar-chart-2',
                lider_cambio: 'arrow-left-right',
                cumpleaños: 'gift'
            };
            return iconos[tipo] || 'circle';
        },

        formatDateRelativo(ts) {
            if (!ts) return '';
            const d = new Date(ts.replace(' ', 'T'));
            const now = new Date();
            const diff = (now - d) / 1000;
            if (diff < 60) return 'ahora';
            if (diff < 3600) return Math.floor(diff/60) + ' min';
            if (diff < 86400) return Math.floor(diff/3600) + 'h';
            if (diff < 604800) return Math.floor(diff/86400) + ' días';
            return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' });
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
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
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
                const response = await fetch(`api/colaboradores.php?action=lideres&campana_id=${this.campanaId}&search=${encodeURIComponent(this.liderSearch)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
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
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
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
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
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
        },

        // ============= EDIT METHODS =============

        async abrirEditar() {
            const c = this.colaborador;
            this.editForm = {
                id: c.id,
                campana_id: this.campanaId,
                nombres: c.nombres,
                apellidos: c.apellidos,
                tipo_documento: c.tipo_documento || 'CC',
                documento: c.documento,
                fecha_nacimiento: c.fecha_nacimiento,
                genero: c.genero,
                perfil: c.perfil || '',
                nivel_participacion: c.nivel_participacion || 'Simpatizante',
                departamento: '',
                municipio: '',
                cod_mpio: '',
                tipo_territorio: '',
                territorio: '',
                barrio: '',
                lider_directo: c.lider_directo || '',
                puesto_votacion: '',
                mesa_votacion: c.mesa_votacion || '',
                dato_potencial: c.dato_potencial || 0,
                dato_historico: c.dato_historico || 0,
                email: c.email || '',
                telefono: c.telefono || '',
                foto: null,
                observaciones: c.observaciones || ''
            };
            this.editExistingFoto = c.foto;
            this.editMunicipioRaw = '';
            this.editSearchLider = c.lider_nombre || c.lider_directo || '';
            this.showModal = 'editar';
            this.$nextTick(async () => {
                await this.initListasGeograficas(c);
                lucide.createIcons();
            });
        },

        async initListasGeograficas(c) {
            const h = { 'X-Requested-With': 'XMLHttpRequest' };
            const fetchJSON = (url) => fetch(url, { headers: h }).then(r => r.json()).catch(() => ({ success: false, data: [] }));

            const [deps, munis, tipos, terrs, barrios, puestos] = await Promise.all([
                fetchJSON('/aratio/api/territorios.php?accion=departamentos'),
                c.departamento ? fetchJSON(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(c.departamento)}`) : Promise.resolve({ success: true, data: [] }),
                (c.departamento && c.municipio) ? fetchJSON(`/aratio/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(c.departamento)}&municipio=${encodeURIComponent(c.municipio)}`) : Promise.resolve({ success: true, data: [] }),
                (c.departamento && c.municipio && c.tipo_territorio) ? fetchJSON(`/aratio/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(c.departamento)}&municipio=${encodeURIComponent(c.municipio)}&tipo_territorio=${encodeURIComponent(c.tipo_territorio)}`) : Promise.resolve({ success: true, data: [] }),
                (c.departamento && c.municipio && c.tipo_territorio && c.territorio) ? fetchJSON(`/aratio/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(c.departamento)}&municipio=${encodeURIComponent(c.municipio)}&tipo_territorio=${encodeURIComponent(c.tipo_territorio)}&territorio=${encodeURIComponent(c.territorio)}`) : Promise.resolve({ success: true, data: [] }),
                c.cod_mpio ? fetchJSON(`/aratio/api/territorios.php?accion=puestos&cod_mpio=${encodeURIComponent(c.cod_mpio)}`) : Promise.resolve({ success: true, data: [] })
            ]);

            this.editListas.departamentos = deps.data || [];
            this.editListas.municipios = munis.data || [];
            this.editListas.tipos_territorio = tipos.data || [];
            this.editListas.territorios = terrs.data || [];
            this.editListas.barrios = barrios.data || [];
            this.editListas.puestos = puestos.data || [];

            this.editForm.departamento = c.departamento || '';
            this.editForm.municipio = c.municipio || '';
            this.editForm.cod_mpio = c.cod_mpio || '';
            this.editForm.tipo_territorio = c.tipo_territorio || '';
            this.editForm.territorio = c.territorio || '';
            this.editForm.barrio = c.barrio || '';
            this.editForm.puesto_votacion = c.puesto_votacion || '';

            if (c.municipio && c.cod_mpio) {
                this.editMunicipioRaw = JSON.stringify({ municipio: c.municipio, cod_mpio: c.cod_mpio });
            }
        },

        cerrarEditar() {
            this.editDetenerCamara();
            this.showModal = null;
            this.editListas.municipios = [];
            this.editListas.tipos_territorio = [];
            this.editListas.territorios = [];
            this.editListas.barrios = [];
            this.editLideresEncontrados = [];
            this.editSearchLider = '';
        },

        async guardarEdicion() {
            this.editando = true;
            try {
                const payload = { ...this.editForm, _method: 'PUT' };
                const response = await fetch('/aratio/api/colaboradores.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (result.success) {
                    alert('Colaborador actualizado exitosamente');
                    this.showModal = null;
                    await this.loadColaborador();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error de conexión: ' + error.message);
            }
            this.editando = false;
        },

        // Photo: Camera
        async editAbrirCamara() {
            try {
                this.editMostrandoCamara = true;
                this.editVideoStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "user", width: 400, height: 400 }
                });
                this.$nextTick(() => {
                    this.$refs.editVideo.srcObject = this.editVideoStream;
                });
            } catch (err) {
                alert("No se pudo acceder a la cámara: " + err.message);
                this.editMostrandoCamara = false;
            }
        },

        editCapturarFoto() {
            const video = this.$refs.editVideo;
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.editForm.foto = canvas.toDataURL('image/jpeg', 0.8);
            this.editDetenerCamara();
        },

        editDetenerCamara() {
            if (this.editVideoStream) {
                this.editVideoStream.getTracks().forEach(track => track.stop());
                this.editVideoStream = null;
            }
            this.editMostrandoCamara = false;
        },

        // Photo: File upload
        editHandleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.editForm.foto = event.target.result;
            };
            reader.readAsDataURL(file);
        },

        // Photo: Paste
        editHandlePaste(e) {
            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const file = items[i].getAsFile();
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.editForm.foto = event.target.result;
                    };
                    reader.readAsDataURL(file);
                    break;
                }
            }
        },

        // Cascading selects
        async editCargarDepartamentos() {
            try {
                const response = await fetch('/aratio/api/territorios.php?accion=departamentos', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.success) {
                    this.editListas.departamentos = result.data;
                }
            } catch (error) {
                console.error('Error cargando departamentos:', error);
            }
        },

        async editCargarMunicipios() {
            this.editForm.municipio = '';
            this.editForm.cod_mpio = '';
            this.editMunicipioRaw = '';
            this.editForm.tipo_territorio = '';
            this.editForm.territorio = '';
            this.editForm.barrio = '';
            this.editForm.puesto_votacion = '';
            this.editListas.municipios = [];
            this.editListas.tipos_territorio = [];
            this.editListas.territorios = [];
            this.editListas.barrios = [];
            this.editListas.puestos = [];
            if (!this.editForm.departamento) return;
            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.editForm.departamento)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.success) {
                    this.editListas.municipios = result.data;
                }
            } catch (error) {
                console.error('Error cargando municipios:', error);
            }
        },

        async editSelectMunicipio() {
            if (!this.editMunicipioRaw) return;
            try {
                const munObj = JSON.parse(this.editMunicipioRaw);
                this.editForm.municipio = munObj.municipio;
                this.editForm.cod_mpio = munObj.cod_mpio;
                await Promise.all([
                    this.editCargarTiposTerritorio(),
                    this.editCargarPuestos()
                ]);
            } catch (e) {
                console.error('Error parsing municipio:', e);
            }
        },

        async editCargarTiposTerritorio() {
            this.editForm.tipo_territorio = '';
            this.editForm.territorio = '';
            this.editForm.barrio = '';
            this.editListas.tipos_territorio = [];
            this.editListas.territorios = [];
            this.editListas.barrios = [];
            if (!this.editForm.departamento || !this.editForm.municipio) return;
            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.editForm.departamento)}&municipio=${encodeURIComponent(this.editForm.municipio)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.success) {
                    this.editListas.tipos_territorio = result.data;
                }
            } catch (error) {
                console.error('Error cargando tipos:', error);
            }
        },

        async editCargarTerritorios() {
            this.editForm.territorio = '';
            this.editForm.barrio = '';
            this.editListas.territorios = [];
            this.editListas.barrios = [];
            if (!this.editForm.tipo_territorio) return;
            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.editForm.departamento)}&municipio=${encodeURIComponent(this.editForm.municipio)}&tipo_territorio=${encodeURIComponent(this.editForm.tipo_territorio)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.success) {
                    this.editListas.territorios = result.data;
                }
            } catch (error) {
                console.error('Error cargando territorios:', error);
            }
        },

        async editCargarBarrios() {
            this.editForm.barrio = '';
            this.editListas.barrios = [];
            if (!this.editForm.territorio) return;
            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.editForm.departamento)}&municipio=${encodeURIComponent(this.editForm.municipio)}&tipo_territorio=${encodeURIComponent(this.editForm.tipo_territorio)}&territorio=${encodeURIComponent(this.editForm.territorio)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.success) {
                    this.editListas.barrios = result.data;
                }
            } catch (error) {
                console.error('Error cargando barrios:', error);
            }
        },

        async editCargarPuestos() {
            this.editListas.puestos = [];
            if (!this.editForm.cod_mpio) return;
            try {
                const response = await fetch(`/aratio/api/territorios.php?accion=puestos&cod_mpio=${encodeURIComponent(this.editForm.cod_mpio)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.success) {
                    this.editListas.puestos = result.data;
                }
            } catch (error) {
                console.error('Error cargando puestos:', error);
            }
        },

        // Leader search
        async editBuscarLideres() {
            if (this.editSearchLider.length < 2) {
                this.editLideresEncontrados = [];
                return;
            }
            try {
                const response = await fetch(`/aratio/api/colaboradores.php?action=lideres&campana_id=${this.campanaId}&search=${encodeURIComponent(this.editSearchLider)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.success) {
                    this.editLideresEncontrados = result.data.filter(l => l.documento !== this.colaborador?.documento);
                }
            } catch (error) {
                console.error('Error buscando lideres:', error);
            }
        },

        editSeleccionarLider(lider) {
            this.editForm.lider_directo = lider.documento;
            this.editSearchLider = lider.nombres + ' ' + lider.apellidos;
            this.editLideresEncontrados = [];
        }
    };
}
</script>
