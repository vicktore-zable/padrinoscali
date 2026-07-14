<?php
/**
 * Vista: Mi Perfil del Líder (v3 — Premium UX)
 * Vista estática por defecto + modo edición con desplegables + curriculum completo
 */
$departamentos = $departamentos ?? [];
$curriculum = $curriculum ?? null;

$cur = [
    'formacion_academica'    => [],
    'experiencia_laboral'    => [],
    'participacion_politica' => [],
    'hijos_data'             => [],
    'hijos_discapacidad'     => 0,
    'equipo_futbol'          => '',
    'practica_deportiva'     => '',
    'resumen_profesional'    => '',
    'habilidades'            => '',
    'idiomas'                => '',
    'reconocimientos'        => '',
    'referencias'            => '',
];
if ($curriculum) {
    foreach ($cur as $k => $v) {
        $cur[$k] = $curriculum[$k] ?? $v;
    }
}
?>

<div class="space-y-6" x-data="perfilLider()" x-init="init()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Mi <span class="gradient-text">Perfil</span></h1>
            <p class="text-gray-500 mt-1">Tu información personal y hoja de vida en la campaña</p>
        </div>
        <template x-if="!editing">
        <button @click="startEdit()"
            class="px-5 py-2.5 bg-[#002244] text-white rounded-xl text-sm font-bold hover:bg-[#003366] transition-all flex items-center gap-2 shadow-lg shadow-[#002244]/20">
            <i data-lucide="pencil" class="w-4 h-4"></i> Editar Perfil
        </button>
        </template>
        <template x-if="editing">
        <button @click="cancelEdit()"
            class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all flex items-center gap-2 shadow-sm">
            <i data-lucide="x" class="w-4 h-4"></i> Cancelar
        </button>
        </template>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100' ?> flex items-center gap-3 animate-fade-in">
            <i data-lucide="<?= $flash['type'] === 'error' ? 'alert-circle' : 'check-circle' ?>" class="w-5 h-5"></i>
            <span class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></span>
        </div>
    <?php endif; ?>

    <!-- ==================== VISTA ESTÁTICA ==================== -->
    <template x-if="!editing">
    <div class="space-y-6">

        <!-- Header Card — Profile Hero -->
        <div class="glass-card overflow-hidden animate-fade-in-up">
            <div class="h-32 bg-gradient-to-br from-[#002244] via-[#003366] to-[#004488] relative">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djZoNnYtNmgtNnptMC0zMHY2aDZ2LTZoLTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-50"></div>
            </div>
            <div class="px-8 pb-8 -mt-16 relative z-10">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-6">
                    <div class="w-28 h-28 rounded-2xl bg-gradient-to-br from-[#002244] to-[#DAA520] p-[3px] shadow-xl">
                        <?php if (!empty($lider['foto'])): ?>
                            <img src="<?= url($lider['foto']) ?>" alt="Foto" class="w-full h-full rounded-2xl object-cover">
                        <?php else: ?>
                            <div class="w-full h-full rounded-2xl bg-white flex items-center justify-center">
                                <span class="text-4xl font-black text-[#002244]">
                                    <?= strtoupper(substr($lider['nombres'], 0, 1) . substr($lider['apellidos'], 0, 1)) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                            <?= htmlspecialchars($lider['nombres'] . ' ' . $lider['apellidos']) ?>
                        </h2>
                        <div class="flex flex-wrap gap-3 mt-3">
                            <span class="px-3 py-1 rounded-full bg-[#002244]/10 text-[#002244] text-xs font-bold uppercase tracking-wider">
                                <?= htmlspecialchars($lider['perfil'] ?? 'Líder') ?>
                            </span>
                            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold flex items-center gap-1">
                                <i data-lucide="credit-card" class="w-3 h-3"></i>
                                <?= htmlspecialchars($lider['documento']) ?>
                            </span>
                            <?php if (!empty($lider['municipio'])): ?>
                            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                <?= htmlspecialchars($lider['municipio']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <div class="text-center">
                            <p class="text-3xl font-black text-[#002244]"><?= $totalDirectos ?></p>
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Directos</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-[#DAA520]"><?= $totalRed ?></p>
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Red Total</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos Personales -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-[#002244]/10 text-[#002244]"><i data-lucide="user" class="w-5 h-5"></i></div>
                <div><h3 class="text-lg font-bold text-gray-900">Datos Personales</h3><p class="text-xs text-gray-500">Tu información personal registrada</p></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ([
                    'Nombres' => $lider['nombres'] ?? '—',
                    'Apellidos' => $lider['apellidos'] ?? '—',
                    'Documento' => $lider['documento'] ?? '—',
                    'Teléfono' => $lider['telefono'] ?? '—',
                    'Email' => $lider['email'] ?? '—',
                    'Perfil' => $lider['perfil'] ?? '—',
                ] as $label => $val): ?>
                <div class="stats-card-blue p-4 rounded-xl">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1"><?= $label ?></p>
                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($val) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Ubicación -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-[#DAA520]/10 text-[#DAA520]"><i data-lucide="map-pin" class="w-5 h-5"></i></div>
                <div><h3 class="text-lg font-bold text-gray-900">Ubicación</h3><p class="text-xs text-gray-500">Tu ubicación geográfica en la campaña</p></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ([
                    'Departamento' => $lider['departamento'] ?? '—',
                    'Municipio' => $lider['municipio'] ?? '—',
                    'Territorio / Comuna' => $lider['territorio'] ?? '—',
                    'Barrio / Vereda' => $lider['barrio'] ?? '—',
                    'Puesto de Votación' => $lider['puesto_votacion'] ?? '—',
                    'Mesa de Votación' => $lider['mesa_votacion'] ?? '—',
                ] as $label => $val): ?>
                <div class="stats-card-gold p-4 rounded-xl">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1"><?= $label ?></p>
                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($val) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Curriculum Resumen -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-purple-50 text-purple-600"><i data-lucide="briefcase" class="w-5 h-5"></i></div>
                <div><h3 class="text-lg font-bold text-gray-900">Curriculum</h3><p class="text-xs text-gray-500">Tu hoja de vida en la campaña</p></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-white border border-indigo-100 text-center hover:scale-[1.02] transition-transform">
                    <p class="text-4xl font-black text-indigo-600"><?= count($cur['formacion_academica']) ?></p>
                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Formaciones</p>
                </div>
                <div class="p-6 rounded-2xl bg-gradient-to-br from-amber-50 to-white border border-amber-100 text-center hover:scale-[1.02] transition-transform">
                    <p class="text-4xl font-black text-amber-600"><?= count($cur['experiencia_laboral']) ?></p>
                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Experiencias</p>
                </div>
                <div class="p-6 rounded-2xl bg-gradient-to-br from-red-50 to-white border border-red-100 text-center hover:scale-[1.02] transition-transform">
                    <p class="text-4xl font-black text-red-600"><?= count($cur['participacion_politica']) ?></p>
                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Participaciones</p>
                </div>
            </div>
            <?php if (!empty($cur['resumen_profesional'])): ?>
            <div class="mt-5 p-4 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1">Resumen Profesional</p>
                <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($cur['resumen_profesional'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Acciones Rápidas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-fade-in-up">
            <a href="?page=portal_change_password" class="glass-card p-6 hover:shadow-lg transition-all group flex items-center gap-4">
                <div class="p-3 rounded-xl bg-amber-50 text-amber-500 group-hover:bg-amber-100 transition-colors"><i data-lucide="key-round" class="w-6 h-6"></i></div>
                <div><h3 class="font-bold text-gray-900">Cambiar Contraseña</h3><p class="text-xs text-gray-500">Actualiza tu contraseña de acceso</p></div>
            </a>
            <a href="?page=portal_red" class="glass-card p-6 hover:shadow-lg transition-all group flex items-center gap-4">
                <div class="p-3 rounded-xl bg-blue-50 text-blue-500 group-hover:bg-blue-100 transition-colors"><i data-lucide="network" class="w-6 h-6"></i></div>
                <div><h3 class="font-bold text-gray-900">Ver Mi Red</h3><p class="text-xs text-gray-500">Grafo interactivo de tu estructura</p></div>
            </a>
        </div>
    </div>
    </template>

    <!-- ==================== MODO EDICIÓN ==================== -->
    <template x-if="editing">
    <div class="space-y-6">

        <!-- Datos Personales -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-[#002244]/10 text-[#002244]"><i data-lucide="user" class="w-5 h-5"></i></div>
                <div><h3 class="text-lg font-bold text-gray-900">Datos Personales</h3><p class="text-xs text-gray-500">Información básica de contacto</p></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Nombres *</label>
                    <input type="text" x-model="form.nombres" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Apellidos *</label>
                    <input type="text" x-model="form.apellidos" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Documento</label>
                    <input type="text" :value="<?= json_encode($lider['documento']) ?>" readonly class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Teléfono / WhatsApp</label>
                    <input type="tel" x-model="form.telefono" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Email</label>
                    <input type="email" x-model="form.email" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Perfil</label>
                    <input type="text" :value="<?= json_encode($lider['perfil'] ?? '') ?>" readonly class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed">
                </div>
            </div>
        </div>

        <!-- Ubicación -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-[#DAA520]/10 text-[#DAA520]"><i data-lucide="map-pin" class="w-5 h-5"></i></div>
                <div><h3 class="text-lg font-bold text-gray-900">Ubicación Geográfica</h3><p class="text-xs text-gray-500">Tu ubicación en la campaña</p></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Departamento</label>
                    <div class="relative">
                        <select x-model="form.departamento" @change="cargarMunicipios()" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none appearance-none transition-all">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($departamentos as $dep): ?>
                                <option value="<?= htmlspecialchars($dep['departamento'] ?? $dep) ?>"><?= htmlspecialchars($dep['departamento'] ?? $dep) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Municipio</label>
                    <div class="relative">
                        <select x-model="municipio_raw" @change="selectMunicipio()" :disabled="!form.departamento" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none appearance-none transition-all disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="">Seleccionar...</option>
                            <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                            </template>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Comuna / Corregimiento</label>
                    <div class="relative">
                        <select x-model="form.territorio" @change="cargarBarrios()" :disabled="!form.municipio" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none appearance-none transition-all disabled:bg-gray-100">
                            <option value="">Seleccionar...</option>
                            <template x-for="terr in listas.territorios" :key="terr"><option :value="terr" x-text="terr"></option></template>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Barrio / Vereda</label>
                    <div class="relative">
                        <select x-model="form.barrio" :disabled="!form.territorio" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none appearance-none transition-all disabled:bg-gray-100">
                            <option value="">Seleccionar...</option>
                            <template x-for="b in listas.barrios" :key="b"><option :value="b" x-text="b"></option></template>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Electoral -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-green-50 text-green-600"><i data-lucide="vote" class="w-5 h-5"></i></div>
                <div><h3 class="text-lg font-bold text-gray-900">Información Electoral</h3><p class="text-xs text-gray-500">Datos de votación</p></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Puesto de Votación</label>
                    <div class="relative">
                        <select x-model="form.puesto_votacion" :disabled="!form.municipio" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none appearance-none transition-all disabled:bg-gray-100">
                            <option value="">Seleccionar...</option>
                            <template x-for="p in listas.puestos" :key="p.id"><option :value="p.puesto" x-text="p.puesto"></option></template>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Mesa de Votación</label>
                    <input type="text" x-model="form.mesa_votacion" placeholder="Ej. 14" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#002244]/20 focus:border-[#002244] outline-none transition-all">
                </div>
            </div>
        </div>

        <!-- Formación Académica -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-indigo-50 text-indigo-600"><i data-lucide="graduation-cap" class="w-5 h-5"></i></div>
                    <div><h3 class="text-lg font-bold text-gray-900">Formación Académica</h3><p class="text-xs text-gray-500">Tu formación educativa</p></div>
                </div>
                <button type="button" @click="addFormacion()" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-bold hover:bg-indigo-100 transition-all flex items-center gap-1">
                    <i data-lucide="plus" class="w-3 h-3"></i> Agregar
                </button>
            </div>
            <template x-if="curriculum.formacion_academica.length === 0">
                <p class="text-gray-400 text-sm text-center py-4">Sin registros. Presiona "Agregar" para comenzar.</p>
            </template>
            <div class="space-y-3">
                <template x-for="(f, idx) in curriculum.formacion_academica" :key="idx">
                    <div class="p-5 rounded-2xl bg-indigo-50/50 border border-indigo-100 relative">
                        <button type="button" @click="curriculum.formacion_academica.splice(idx, 1)" class="absolute top-3 right-3 text-red-400 hover:text-red-600 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Título</label><input type="text" x-model="f.titulo" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none"></div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Institución</label><input type="text" x-model="f.institucion" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none"></div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Nivel</label>
                                <select x-model="f.nivel" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none appearance-none">
                                    <option value="">Seleccionar...</option>
                                    <option value="Básica Primaria">Básica Primaria</option>
                                    <option value="Básica Secundaria">Básica Secundaria</option>
                                    <option value="Media">Media</option>
                                    <option value="Técnica">Técnica</option>
                                    <option value="Tecnológica">Tecnológica</option>
                                    <option value="Profesional">Profesional</option>
                                    <option value="Especialización">Especialización</option>
                                    <option value="Maestría">Maestría</option>
                                    <option value="Doctorado">Doctorado</option>
                                </select>
                            </div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Año</label><input type="text" x-model="f.anio" placeholder="Ej. 2020" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Experiencia Laboral -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600"><i data-lucide="briefcase" class="w-5 h-5"></i></div>
                    <div><h3 class="text-lg font-bold text-gray-900">Experiencia Laboral</h3><p class="text-xs text-gray-500">Tu trayectoria profesional</p></div>
                </div>
                <button type="button" @click="addExperiencia()" class="px-4 py-2 bg-amber-50 text-amber-600 rounded-xl text-xs font-bold hover:bg-amber-100 transition-all flex items-center gap-1">
                    <i data-lucide="plus" class="w-3 h-3"></i> Agregar
                </button>
            </div>
            <template x-if="curriculum.experiencia_laboral.length === 0">
                <p class="text-gray-400 text-sm text-center py-4">Sin registros. Presiona "Agregar" para comenzar.</p>
            </template>
            <div class="space-y-3">
                <template x-for="(e, idx) in curriculum.experiencia_laboral" :key="idx">
                    <div class="p-5 rounded-2xl bg-amber-50/50 border border-amber-100 relative">
                        <button type="button" @click="curriculum.experiencia_laboral.splice(idx, 1)" class="absolute top-3 right-3 text-red-400 hover:text-red-600 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Cargo</label><input type="text" x-model="e.cargo" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none"></div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Empresa</label><input type="text" x-model="e.empresa" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none"></div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Fecha Inicio</label><input type="date" x-model="e.fecha_inicio" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none"></div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Fecha Fin</label><input type="date" x-model="e.fecha_fin" :disabled="e.actual" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none disabled:bg-gray-100"></div>
                            <div class="md:col-span-2 flex items-center gap-2"><input type="checkbox" x-model="e.actual" class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500"><label class="text-sm text-gray-600">Actualmente trabajando aquí</label></div>
                            <div class="md:col-span-2 space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Descripción</label><textarea x-model="e.descripcion" rows="2" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none resize-none"></textarea></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Participación Política -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-red-50 text-red-600"><i data-lucide="landmark" class="w-5 h-5"></i></div>
                    <div><h3 class="text-lg font-bold text-gray-900">Participación Política</h3><p class="text-xs text-gray-500">Tu experiencia política</p></div>
                </div>
                <button type="button" @click="addParticipacion()" class="px-4 py-2 bg-red-50 text-red-600 rounded-xl text-xs font-bold hover:bg-red-100 transition-all flex items-center gap-1">
                    <i data-lucide="plus" class="w-3 h-3"></i> Agregar
                </button>
            </div>
            <template x-if="curriculum.participacion_politica.length === 0">
                <p class="text-gray-400 text-sm text-center py-4">Sin registros. Presiona "Agregar" para comenzar.</p>
            </template>
            <div class="space-y-3">
                <template x-for="(p, idx) in curriculum.participacion_politica" :key="idx">
                    <div class="p-5 rounded-2xl bg-red-50/50 border border-red-100 relative">
                        <button type="button" @click="curriculum.participacion_politica.splice(idx, 1)" class="absolute top-3 right-3 text-red-400 hover:text-red-600 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Cargo / Rol</label><input type="text" x-model="p.cargo" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none"></div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Organización</label><input type="text" x-model="p.organizacion" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none"></div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Fecha Inicio</label><input type="date" x-model="p.fecha_inicio" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none"></div>
                            <div class="space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Fecha Fin</label><input type="date" x-model="p.fecha_fin" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none"></div>
                            <div class="md:col-span-2 space-y-1"><label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Resultado / Logro</label><input type="text" x-model="p.resultado" placeholder="Ej. Elegido concejal" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Información Personal (Hijos, Deporte) -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-pink-50 text-pink-600"><i data-lucide="heart" class="w-5 h-5"></i></div>
                <div><h3 class="text-lg font-bold text-gray-900">Información Personal</h3><p class="text-xs text-gray-500">Datos familiares y deportivos</p></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Equipo de Fútbol Favorito</label>
                    <input type="text" x-model="curriculum.equipo_futbol" placeholder="Ej. América de Cali" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 outline-none transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Práctica Deportiva</label>
                    <input type="text" x-model="curriculum.practica_deportiva" placeholder="Ej. Fútbol, Running, Gimnasio" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 outline-none transition-all">
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Hijos</label>
                    <button type="button" @click="addHijo()" class="text-xs text-pink-600 hover:text-pink-700 font-bold flex items-center gap-1"><i data-lucide="plus" class="w-3 h-3"></i> Agregar hijo</button>
                </div>
                <div class="flex items-center gap-3 mb-3">
                    <input type="checkbox" x-model="curriculum.hijos_discapacidad" class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500">
                    <label class="text-sm text-gray-600">Tengo hijos con discapacidad</label>
                </div>
                <template x-if="curriculum.hijos_data.length === 0">
                    <p class="text-gray-400 text-sm">Sin hijos registrados</p>
                </template>
                <div class="space-y-3">
                    <template x-for="(h, idx) in curriculum.hijos_data" :key="idx">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-pink-50/50 border border-pink-100">
                            <span class="text-xs font-bold text-gray-400" x-text="'Hijo ' + (idx + 1)"></span>
                            <input type="date" x-model="h.fecha" class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 outline-none">
                            <span x-show="h.fecha" class="text-xs text-gray-500" x-text="calcEdad(h.fecha)"></span>
                            <button type="button" @click="curriculum.hijos_data.splice(idx, 1)" class="text-red-400 hover:text-red-600 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Otros (Resumen, Habilidades, Idiomas) -->
        <div class="glass-card p-8 animate-fade-in-up">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 rounded-xl bg-teal-50 text-teal-600"><i data-lucide="file-text" class="w-5 h-5"></i></div>
                <div><h3 class="text-lg font-bold text-gray-900">Otros Datos</h3><p class="text-xs text-gray-500">Resumen, habilidades e idiomas</p></div>
            </div>
            <div class="space-y-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Resumen Profesional</label>
                    <textarea x-model="curriculum.resumen_profesional" rows="3" placeholder="Breve descripción de tu perfil profesional..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all resize-none"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Habilidades</label>
                    <textarea x-model="curriculum.habilidades" rows="2" placeholder="Liderazgo, trabajo en equipo, comunicación..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all resize-none"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Idiomas</label>
                    <textarea x-model="curriculum.idiomas" rows="2" placeholder="Español (nativo), Inglés (básico)..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all resize-none"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Reconocimientos</label>
                    <textarea x-model="curriculum.reconocimientos" rows="2" placeholder="Premios, menciones, certificados..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all resize-none"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Referencias</label>
                    <textarea x-model="curriculum.referencias" rows="2" placeholder="Personas de referencia..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row gap-4 animate-fade-in-up">
            <button type="button" @click="cancelEdit()" class="flex-1 text-center px-6 py-3.5 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all">Cancelar</button>
            <button type="submit" @click.prevent="submitProfile()" :disabled="saving" class="flex-1 px-6 py-3.5 rounded-xl bg-[#002244] text-white font-bold hover:bg-[#003366] transition-all shadow-lg shadow-[#002244]/20 flex items-center justify-center gap-2 disabled:opacity-50">
                <span x-show="!saving"><i data-lucide="save" class="w-4 h-4 inline"></i> Guardar Todo</span>
                <span x-show="saving"><i data-lucide="loader-2" class="w-4 h-4 inline animate-spin"></i> Guardando...</span>
            </button>
        </div>
    </div>
    </template>

</div>

<script>
function perfilLider() {
    return {
        editing: false,
        saving: false,
        form: {
            nombres: '', apellidos: '', telefono: '', email: '',
            departamento: '', municipio: '', cod_mpio: '', tipo_territorio: '',
            territorio: '', barrio: '', puesto_votacion: '', mesa_votacion: ''
        },
        municipio_raw: '',
        listas: { municipios: [], territorios: [], barrios: [], puestos: [] },
        _territorioTipoMap: {},

        curriculum: {
            formacion_academica: [], experiencia_laboral: [], participacion_politica: [],
            hijos_data: [], hijos_discapacidad: false,
            equipo_futbol: '', practica_deportiva: '', resumen_profesional: '',
            habilidades: '', idiomas: '', reconocimientos: '', referencias: ''
        },

        init() { lucide.createIcons(); },

        startEdit() {
            const l = <?= json_encode($lider) ?>;
            const c = <?= json_encode($cur) ?>;
            this.form = {
                nombres: l.nombres || '', apellidos: l.apellidos || '',
                telefono: l.telefono || '', email: l.email || '',
                departamento: l.departamento || '', municipio: l.municipio || '',
                cod_mpio: l.cod_mpio || '', tipo_territorio: l.tipo_territorio || '',
                territorio: l.territorio || '', barrio: l.barrio || '',
                puesto_votacion: l.puesto_votacion || '', mesa_votacion: l.mesa_votacion || ''
            };
            this.curriculum = {
                formacion_academica: c.formacion_academica || [],
                experiencia_laboral: c.experiencia_laboral || [],
                participacion_politica: c.participacion_politica || [],
                hijos_data: c.hijos_data || [],
                hijos_discapacidad: !!c.hijos_discapacidad,
                equipo_futbol: c.equipo_futbol || '', practica_deportiva: c.practica_deportiva || '',
                resumen_profesional: c.resumen_profesional || '', habilidades: c.habilidades || '',
                idiomas: c.idiomas || '', reconocimientos: c.reconocimientos || '', referencias: c.referencias || ''
            };
            this.editing = true;
            this.$nextTick(() => lucide.createIcons());
        },

        cancelEdit() { this.editing = false; this.$nextTick(() => lucide.createIcons()); },

        addFormacion() { this.curriculum.formacion_academica.push({ titulo: '', institucion: '', nivel: '', anio: '' }); this.$nextTick(() => lucide.createIcons()); },
        addExperiencia() { this.curriculum.experiencia_laboral.push({ cargo: '', empresa: '', fecha_inicio: '', fecha_fin: '', actual: false, descripcion: '' }); this.$nextTick(() => lucide.createIcons()); },
        addParticipacion() { this.curriculum.participacion_politica.push({ cargo: '', organizacion: '', fecha_inicio: '', fecha_fin: '', resultado: '' }); this.$nextTick(() => lucide.createIcons()); },
        addHijo() { this.curriculum.hijos_data.push({ fecha: '' }); },

        calcEdad(fecha) {
            if (!fecha) return '';
            const nac = new Date(fecha); const hoy = new Date();
            let edad = hoy.getFullYear() - nac.getFullYear();
            const m = hoy.getMonth() - nac.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;
            return edad + ' años';
        },

        async cargarMunicipios() {
            this.listas.municipios = []; this.form.municipio = ''; this.form.cod_mpio = '';
            this.municipio_raw = ''; this.form.puesto_votacion = ''; this.listas.puestos = [];
            if (!this.form.departamento) return;
            const res = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
            const data = await res.json(); if (data.success) this.listas.municipios = data.data;
        },
        async selectMunicipio() {
            if (!this.municipio_raw) return;
            try { const o = JSON.parse(this.municipio_raw); this.form.municipio = o.municipio; this.form.cod_mpio = o.cod_mpio; this.cargarTiposTerritorio(); this.cargarPuestos(); } catch (e) { console.error(e); }
        },
        async cargarPuestos() {
            this.listas.puestos = []; this.form.puesto_votacion = '';
            if (!this.form.cod_mpio) return;
            const res = await fetch(`/aratio/api/territorios.php?accion=puestos&cod_mpio=${encodeURIComponent(this.form.cod_mpio)}`);
            const data = await res.json(); if (data.success) this.listas.puestos = data.data;
        },
        async cargarTiposTerritorio() {
            this.form.territorio = ''; this.listas.territorios = [];
            if (!this.form.municipio) return;
            const res = await fetch(`/aratio/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
            const data = await res.json();
            if (data.success && data.data.length > 0) {
                let all = [];
                for (let tipo of data.data) {
                    const r = await fetch(`/aratio/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(tipo)}`);
                    const d = await r.json(); if (d.success) d.data.forEach(t => all.push({ nombre: t, tipo }));
                }
                this.listas.territorios = all.map(t => t.nombre);
                this._territorioTipoMap = all.reduce((a, i) => { a[i.nombre] = i.tipo; return a; }, {});
            }
        },
        async cargarBarrios() {
            this.listas.barrios = []; this.form.barrio = '';
            if (!this.form.territorio) return;
            const tipo = this._territorioTipoMap[this.form.territorio]; this.form.tipo_territorio = tipo;
            const res = await fetch(`/aratio/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(tipo)}&territorio=${encodeURIComponent(this.form.territorio)}`);
            const data = await res.json(); if (data.success) this.listas.barrios = data.data;
        },

        async submitProfile() {
            this.saving = true;
            try {
                const payload = { ...this.form, curriculum: this.curriculum };
                const res = await fetch('?page=portal_perfil', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (data.success) window.location.reload(); else alert('Error: ' + (data.message || 'No se pudo guardar'));
            } catch (e) { alert('Error de conexión'); }
            this.saving = false;
        }
    }
}
</script>
