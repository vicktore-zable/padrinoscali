<!-- Formulario de Edición de Colaborador - AratioPRO Premium -->
<?php 
$nombreCompleto = \App\Utils\Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos']);
$areasSeleccionadas = [];
if (!empty($colaborador['areas_interes'])) {
    $areasSeleccionadas = is_string($colaborador['areas_interes'])
        ? json_decode($colaborador['areas_interes'], true)
        : $colaborador['areas_interes'];
}
?>

<!-- Header Hero -->
<div class="gradient-top text-white pb-16 pt-10 px-4 shadow-lg -mx-4 -mt-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold mb-2">Editar Colaborador</h1>
                <p class="text-white/80 text-sm"><?= htmlspecialchars($nombreCompleto) ?> - <?= htmlspecialchars($colaborador['documento']) ?></p>
            </div>
            <div class="flex gap-2">
                <a href="/colaboradores/<?= $colaborador['documento'] ?>" class="inline-flex items-center bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-full text-sm font-medium transition-all border border-white/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Ver Detalle
                </a>
                <a href="/colaboradores" class="inline-flex items-center bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-full text-sm font-medium transition-all border border-white/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Container -->
<div class="max-w-5xl mx-auto px-4 -mt-10 mb-12 relative z-10">

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'success' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-red-50 border-red-500 text-red-700' ?> border-l-4" role="alert">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Form Card Premium -->
    <div class="glass rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 sm:p-8">
            
            <form action="/colaboradores/<?= $colaborador['documento'] ?>/update" method="POST" class="space-y-8" x-data="colaboradorFormEdit()">
                <?= \App\Utils\Security::csrfField() ?>
                <input type="hidden" name="_method" value="PUT">

                <!-- 1. Información Personal -->
                <div class="bg-gray-50 p-5 sm:p-6 rounded-xl border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#1e3a5f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Información Personal
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombres *</label>
                            <input type="text" name="nombres" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= htmlspecialchars($colaborador['nombres']) ?>" x-model="form.nombres" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos *</label>
                            <input type="text" name="apellidos" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= htmlspecialchars($colaborador['apellidos']) ?>" x-model="form.apellidos" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento *</label>
                            <select name="tipo_documento" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" x-model="form.tipo_documento" required>
                                <option value="CC" <?= $colaborador['tipo_documento'] === 'CC' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                                <option value="CE" <?= $colaborador['tipo_documento'] === 'CE' ? 'selected' : '' ?>>Cédula de Extranjería</option>
                                <option value="PA" <?= $colaborador['tipo_documento'] === 'PA' ? 'selected' : '' ?>>Pasaporte</option>
                                <option value="TI" <?= $colaborador['tipo_documento'] === 'TI' ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número Documento</label>
                            <input type="text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-100 text-gray-500" value="<?= htmlspecialchars($colaborador['documento']) ?>" readonly>
                            <p class="text-xs text-gray-400 mt-1">No puede ser modificado</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= htmlspecialchars($colaborador['fecha_nacimiento']) ?>" x-model="form.fecha_nacimiento" :max="maxDate" required>
                            <p class="text-xs text-gray-500 mt-1" x-show="edad" x-text="'Edad: ' + idade + ' años'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Género *</label>
                            <select name="genero" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" x-model="form.genero" required>
                                <option value="M" <?= $colaborador['genero'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= $colaborador['genero'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                                <option value="O" <?= $colaborador['genero'] === 'O' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2. Información de Contacto -->
                <div class="bg-gray-50 p-5 sm:p-6 rounded-xl border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#1e3a5f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Información de Contacto
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= htmlspecialchars($colaborador['email'] ?? '') ?>" x-model="form.email">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                            <input type="tel" name="telefono" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= htmlspecialchars($colaborador['telefono'] ?? '') ?>" x-model="form.telefono" required>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                            <input type="tel" name="telefono_whatsapp" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= htmlspecialchars($colaborador['telefono_whatsapp'] ?? '') ?>" x-model="form.telefono_whatsapp">
                        </div>
                    </div>
                </div>

                <!-- 3. Ubicación -->
                <div class="bg-gray-50 p-5 sm:p-6 rounded-xl border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#1e3a5f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Ubicación
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Departamento *</label>
                            <select name="departamento" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" x-model="form.departamento" @change="loadMunicipios()" required>
                                <option value="">Seleccionar...</option>
                                <template x-for="depto in geografia.departamentos" :key="depto">
                                    <option :value="depto" x-text="depto"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Municipio *</label>
                            <select name="municipio" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent disabled:bg-gray-100" x-model="form.municipio" @change="loadTiposTerritorio()" :disabled="!form.departamento || geografia.loadingMunicipios" required>
                                <option value="">
                                    <span x-show="geografia.loadingMunicipios">Cargando...</span>
                                    <span x-show="!geografia.loadingMunicipios">Seleccionar...</span>
                                </option>
                                <template x-for="mun in geografia.municipios" :key="mun">
                                    <option :value="mun" x-text="mun"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Zona (Urbana/Rural)</label>
                            <select name="tipo_territorio" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent disabled:bg-gray-100" x-model="form.tipo_territorio" @change="loadTerritorios()" :disabled="!form.municipio || geografia.loadingTipos">
                                <option value="">
                                    <span x-show="geografia.loadingTipos">Cargando...</span>
                                    <span x-show="!geografia.loadingTipos">Seleccionar...</span>
                                </option>
                                <template x-for="tipo in geografia.tipos" :key="tipo">
                                    <option :value="tipo" x-text="tipo"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comuna / Corregimiento</label>
                            <select name="territorio" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent disabled:bg-gray-100" x-model="form.territorio" @change="loadBarrios()" :disabled="!form.tipo_territorio || geografia.loadingTerritorios">
                                <option value="">
                                    <span x-show="geografia.loadingTerritorios">Cargando...</span>
                                    <span x-show="!geografia.loadingTerritorios">Seleccionar...</span>
                                </option>
                                <template x-for="terr in geografia.territorios" :key="terr">
                                    <option :value="terr" x-text="terr"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barrio / Vereda</label>
                            <select name="barrio" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent disabled:bg-gray-100" x-model="form.barrio" :disabled="!form.territorio || geografia.loadingBarrios">
                                <option value="">
                                    <span x-show="geografia.loadingBarrios">Cargando...</span>
                                    <span x-show="!geografia.loadingBarrios">Seleccionar...</span>
                                </option>
                                <template x-for="bar in geografia.barrios" :key="bar">
                                    <option :value="bar" x-text="bar"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text" name="direccion" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= htmlspecialchars($colaborador['direccion'] ?? '') ?>" x-model="form.direccion">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Detalles de Ubicación</label>
                            <textarea name="detalle_ubicacion" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" x-model="form.detalle_ubicacion"><?= htmlspecialchars($colaborador['detalle_ubicacion'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- 4. Información Electoral -->
                <div class="bg-blue-50 p-5 sm:p-6 rounded-xl border border-blue-100">
                    <h3 class="text-lg font-bold text-blue-900 mb-5 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Información Electoral
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Puesto de Votación</label>
                            <div class="relative">
                                <input type="text" 
                                       name="puesto_votacion" 
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent"
                                       value="<?= htmlspecialchars($colaborador['puesto_votacion'] ?? '') ?>"
                                       x-model="form.puesto_votacion"
                                       @input="buscarPuestos()"
                                       list="lista-puestos-editar"
                                       :disabled="!form.municipio">
                                <datalist id="lista-puestos-editar">
                                    <template x-for="p in geografia.puestos" :key="p">
                                        <option :value="p"></option>
                                    </template>
                                </datalist>
                                <div x-show="geografia.buscandoPuesto" class="absolute right-3 top-3">
                                    <svg class="animate-spin w-5 h-5 text-[#1e3a5f]" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Resultados: <span x-text="geografia.puestos.length"></span> (escribe 2+ letras)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mesa de Votación</label>
                            <input type="text" name="mesa_votacion" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= htmlspecialchars($colaborador['mesa_votacion'] ?? '') ?>" x-model="form.mesa_votacion">
                        </div>
                    </div>
                </div>

                <!-- 5. Perfil Político -->
                <div class="bg-gray-50 p-5 sm:p-6 rounded-xl border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#1e3a5f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Perfil Político
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Perfil *</label>
                            <select name="perfil" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" x-model="form.perfil" required>
                                <?php foreach (PERFILES as $perfil): ?>
                                    <option value="<?= $perfil ?>" <?= $colaborador['perfil'] === $perfil ? 'selected' : '' ?>><?= $perfil ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel Participación *</label>
                            <select name="nivel_participacion" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" x-model="form.nivel_participacion" required>
                                <?php foreach (NIVELES_PARTICIPACION as $nivel): ?>
                                    <option value="<?= $nivel ?>" <?= $colaborador['nivel_participacion'] === $nivel ? 'selected' : '' ?>><?= $nivel ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dato Potencial</label>
                            <input type="number" name="dato_potencial" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= (int)$colaborador['dato_potencial'] ?>" min="0" x-model="form.dato_potencial">
                            <p class="text-xs text-gray-500 mt-1">Votos estimados que puede aportar</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dato Histórico</label>
                            <input type="number" name="dato_historico" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" value="<?= (int)$colaborador['dato_historico'] ?>" min="0" x-model="form.dato_historico">
                            <p class="text-xs text-gray-500 mt-1">Votos históricos</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Líder Directo</label>
                            <select name="lider_directo" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" x-model="form.lider_directo">
                                <option value="">Sin líder (es líder raíz)</option>
                                <?php if (isset($lideres) && !empty($lideres)): ?>
                                    <?php foreach ($lideres as $lider): ?>
                                        <?php if ($lider['documento'] !== $colaborador['documento']): ?>
                                            <option value="<?= $lider['documento'] ?>" <?= $colaborador['lider_directo'] === $lider['documento'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lider['nombres'] . ' ' . $lider['apellidos']) ?> - <?= htmlspecialchars($lider['perfil'] ?? '') ?> (<?= $lider['documento'] ?>)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Áreas de Interés</label>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (AREAS_INTERES as $area): ?>
                                    <button type="button" 
                                            @click="toggleArea('<?= $area ?>')" 
                                            class="px-4 py-2 rounded-full text-sm font-medium border-2 transition-all"
                                            :class="form.areas_interes.includes('<?= $area ?>') 
                                                ? 'bg-[#1e3a5f] border-[#1e3a5f] text-white shadow-lg' 
                                                : 'bg-white border-gray-200 text-gray-600 hover:border-[#1e3a5f]'">
                                        <span><?= $area ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea name="observaciones" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent" x-model="form.observaciones" @keyup="_obsLimit()"><?= htmlspecialchars($colaborador['observaciones'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1" x-text="form.observaciones.length + '/500 caracteres'"></p>
                        </div>
                    </div>
                </div>

                <!-- 6. Perfil Profesional -->
                <div class="bg-purple-50 p-5 sm:p-6 rounded-xl border border-purple-100">
                    <h3 class="text-lg font-bold text-purple-900 mb-5 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Perfil Profesional
                    </h3>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Resumen Profesional</label>
                        <textarea x-model="form.resumen_profesional" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600"><?= htmlspecialchars($colaborador['resumen_profesional'] ?? '') ?></textarea>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="flex items-center justify-between border-b border-purple-100 pb-2">
                            <h4 class="text-sm font-bold text-gray-700">Formación Académica</h4>
                            <button type="button" @click="addFormacion()" class="text-xs font-bold text-[#1e3a5f] hover:text-[#d4af37]">+ Añadir</button>
                        </div>
                        <template x-for="(edu, index) in form.formaciones" :key="index">
                            <div class="p-4 bg-white rounded-xl border border-gray-200 relative">
                                <button type="button" @click="removeFormacion(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="text" x-model="edu.titulo" placeholder="Título / Carrera" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <input type="text" x-model="edu.institucion" placeholder="Institución" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="flex items-center justify-between border-b border-purple-100 pb-2">
                            <h4 class="text-sm font-bold text-gray-700">Experiencia Laboral</h4>
                            <button type="button" @click="addExperiencia()" class="text-xs font-bold text-[#1e3a5f] hover:text-[#d4af37]">+ Añadir</button>
                        </div>
                        <template x-for="(exp, index) in form.experiencias" :key="'exp'+index">
                            <div class="p-4 bg-white rounded-xl border border-gray-200 relative">
                                <button type="button" @click="removeExperiencia(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                <div class="grid grid-cols-1 gap-3">
                                    <input type="text" x-model="exp.cargo" placeholder="Cargo / Rol" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <input type="text" x-model="exp.empresa" placeholder="Empresa / Organización" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Habilidades Adicionales</label>
                        <input type="text" x-model="form.habilidades" placeholder="Ej. Oratoria, Gestión de equipos..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600" value="<?= htmlspecialchars($colaborador['habilidades'] ?? '') ?>">
                    </div>
                </div>

                <!-- 7. Info Sistema (solo lectura) -->
                <div class="bg-gray-50 p-5 sm:p-6 rounded-xl border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#1e3a5f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Información del Sistema
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Estado</p>
                            <p class="mt-1"><?= \App\Utils\Helpers::estadoBadge($colaborador['estado'], 'estado') ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Grupo Etario</p>
                            <p class="mt-1 text-gray-900"><?= htmlspecialchars($colaborador['grupo_etareo'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Fecha de Registro</p>
                            <p class="mt-1 text-gray-900"><?= isset($colaborador['created_at']) ? \App\Utils\Helpers::formatDate($colaborador['created_at']) : 'N/A' ?></p>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex flex-col sm:flex-row justify-between gap-4 pt-4 border-t border-gray-200">
                    <button type="button" @click="if(confirm('¿Está seguro de eliminar este colaborador?')) { submitDelete() }" class="px-6 py-3 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-all text-center">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                    </button>
                    <div class="flex gap-4">
                        <a href="/colaboradores/<?= $colaborador['documento'] ?>" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all text-center">Cancelar</a>
                        <button type="submit" :disabled="submitting" class="px-8 py-3 bg-gradient-to-r from-[#1e3a5f] to-[#d4af37] text-white rounded-xl font-bold shadow-lg hover:shadow-xl hover:scale-[1.02] transform transition-all disabled:opacity-50">
                            <span x-show="!submitting">Guardar Cambios</span>
                            <span x-show="submitting" class="flex items-center justify-center">
                                <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Form para DELETE -->
<form id="deleteForm" action="/colaboradores/<?= $colaborador['documento'] ?>/delete" method="POST" style="display: none;">
    <?= \App\Utils\Security::csrfField() ?>
    <input type="hidden" name="_method" value="DELETE">
</form>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function colaboradorFormEdit() {
        const areasIni = <?= json_encode($areasSeleccionadas) ?>;
        
        return {
            form: {
                nombres: '<?= htmlspecialchars($colaborador['nombres']) ?>',
                apellidos: '<?= htmlspecialchars($colaborador['apellidos']) ?>',
                tipo_documento: '<?= htmlspecialchars($colaborador['tipo_documento']) ?>',
                fecha_nacimiento: '<?= htmlspecialchars($colaborador['fecha_nacimiento']) ?>',
                genero: '<?= htmlspecialchars($colaborador['genero']) ?>',
                email: '<?= htmlspecialchars($colaborador['email'] ?? '') ?>',
                telefono: '<?= htmlspecialchars($colaborador['telefono'] ?? '') ?>',
                telefono_whatsapp: '<?= htmlspecialchars($colaborador['telefono_whatsapp'] ?? '') ?>',
                departamento: '<?= htmlspecialchars($colaborador['departamento']) ?>',
                municipio: '<?= htmlspecialchars($colaborador['municipio']) ?>',
                tipo_territorio: '<?= htmlspecialchars($colaborador['tipo_territorio'] ?? '') ?>',
                territorio: '<?= htmlspecialchars($colaborador['territorio'] ?? '') ?>',
                barrio: '<?= htmlspecialchars($colaborador['barrio'] ?? '') ?>',
                direccion: '<?= htmlspecialchars($colaborador['direccion'] ?? '') ?>',
                detalle_ubicacion: '<?= htmlspecialchars($colaborador['detalle_ubicacion'] ?? '') ?>',
                perfil: '<?= htmlspecialchars($colaborador['perfil']) ?>',
                nivel_participacion: '<?= htmlspecialchars($colaborador['nivel_participacion']) ?>',
                dato_potencial: '<?= (int)($colaborador['dato_potencial'] ?? 0) ?>',
                dato_historico: '<?= (int)($colaborador['dato_historico'] ?? 0) ?>',
                lider_directo: '<?= htmlspecialchars($colaborador['lider_directo'] ?? '') ?>',
                areas_interes: areasIni,
                observaciones: '<?= htmlspecialchars($colaborador['observaciones'] ?? '') ?>',
                puesto_votacion: '<?= htmlspecialchars($colaborador['puesto_votacion'] ?? '') ?>',
                mesa_votacion: '<?= htmlspecialchars($colaborador['mesa_votacion'] ?? '') ?>',
                resumen_profesional: '<?= htmlspecialchars($colaborador['resumen_profesional'] ?? '') ?>',
                formaciones: [],
                experiencias: [],
                habilidades: '<?= htmlspecialchars($colaborador['habilidades'] ?? '') ?>'
            },
            allAreas: ['Gestión Territorial', 'Logística', 'Comunicaciones', 'Redes Sociales', 'Electoral', 'Jurídico', 'Sistemas IT', 'Finanzas', 'Prensa', 'Poblacional', 'Publicidad'],
            geografia: {
                departamentos: [],
                municipios: [],
                tipos: [],
                territorios: [],
                barrios: [],
                puestos: [],
                loadingMunicipios: false,
                loadingTipos: false,
                loadingTerritorios: false,
                loadingBarrios: false,
                buscandoPuesto: false
            },
            submitting: false,

            get maxDate() {
                const today = new Date();
                today.setFullYear(today.getFullYear() - 18);
                return today.toISOString().split('T')[0];
            },

            get idade() {
                if (!this.form.fecha_nacimiento) return null;
                const hoy = new Date();
                const nacimiento = new Date(this.form.fecha_nacimiento);
                let edad = hoy.getFullYear() - nacimiento.getFullYear();
                const mes = hoy.getMonth() - nacimiento.getMonth();
                if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                    edad--;
                }
                return edad;
            },

            _obsLimit() {
                if (this.form.observaciones.length > 500) {
                    this.form.observaciones = this.form.observaciones.substring(0, 500);
                }
            },

            async loadDepartamentos() {
                try {
                    const response = await fetch('/territorios/departamentos');
                    const data = await response.json();
                    if (data.success) {
                        this.geografia.departamentos = data.data;
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },

            async loadMunicipios() {
                this.form.municipio = '';
                this.form.tipo_territorio = '';
                this.form.territorio = '';
                this.form.barrio = '';
                this.geografia.municipios = [];
                this.geografia.tipos = [];
                this.geografia.territorios = [];
                this.geografia.barrios = [];
                this.geografia.puestos = [];

                if (!this.form.departamento) return;
                this.geografia.loadingMunicipios = true;
                try {
                    const response = await fetch(`/territorios/municipios-cascada?departamento=${encodeURIComponent(this.form.departamento)}`);
                    const data = await response.json();
                    if (data.success) {
                        this.geografia.municipios = data.data;
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.geografia.loadingMunicipios = false;
                }
            },

            async loadTiposTerritorio() {
                this.form.tipo_territorio = '';
                this.form.territorio = '';
                this.form.barrio = '';
                this.geografia.tipos = [];
                this.geografia.territorios = [];
                this.geografia.barrios = [];

                if (!this.form.departamento || !this.form.municipio) return;
                this.geografia.loadingTipos = true;
                try {
                    const response = await fetch(`/territorios/tipos?departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
                    const data = await response.json();
                    if (data.success) {
                        this.geografia.tipos = data.data;
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.geografia.loadingTipos = false;
                }
            },

            async loadTerritorios() {
                this.form.territorio = '';
                this.form.barrio = '';
                this.geografia.territorios = [];
                this.geografia.barrios = [];

                if (!this.form.departamento || !this.form.municipio || !this.form.tipo_territorio) return;
                this.geografia.loadingTerritorios = true;
                try {
                    const response = await fetch(`/territorios/territorios?departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo=${encodeURIComponent(this.form.tipo_territorio)}`);
                    const data = await response.json();
                    if (data.success) {
                        this.geografia.territorios = data.data;
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.geografia.loadingTerritorios = false;
                }
            },

            async loadBarrios() {
                this.form.barrio = '';
                this.geografia.barrios = [];

                if (!this.form.departamento || !this.form.municipio || !this.form.tipo_territorio || !this.form.territorio) return;
                this.geografia.loadingBarrios = true;
                try {
                    const response = await fetch(`/territorios/barrios?departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo=${encodeURIComponent(this.form.tipo_territorio)}&territorio=${encodeURIComponent(this.form.territorio)}`);
                    const data = await response.json();
                    if (data.success) {
                        this.geografia.barrios = data.data;
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.geografia.loadingBarrios = false;
                }
            },

            async buscarPuestos() {
                if (!this.form.municipio) return;
                const search = this.form.puesto_votacion;
                if (search.length < 2) {
                    this.geografia.puestos = [];
                    return;
                }
                this.geografia.buscandoPuesto = true;
                try {
                    const response = await fetch(`/territorios/buscar-puestos?q=${encodeURIComponent(search)}&municipio=${encodeURIComponent(this.form.municipio)}`);
                    const data = await response.json();
                    if (data.success) {
                        this.geografia.puestos = data.data;
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.geografia.buscandoPuesto = false;
                }
            },

            toggleArea(area) {
                if(this.form.areas_interes.includes(area)) {
                    this.form.areas_interes = this.form.areas_interes.filter(a => a !== area);
                } else {
                    this.form.areas_interes.push(area);
                }
            },

            addFormacion() { 
                this.form.formaciones.push({titulo: '', institucion: ''}); 
            },
            removeFormacion(i) { 
                this.form.formaciones.splice(i, 1); 
            },
            addExperiencia() { 
                this.form.experiencias.push({cargo: '', empresa: ''}); 
            },
            removeExperiencia(i) { 
                this.form.experiencias.splice(i, 1); 
            },

            submitDelete() {
                document.getElementById('deleteForm').submit();
            },

            init() {
                this.loadDepartamentos();
            }
        }
    }
</script>