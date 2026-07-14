<?php
$campanaId = $campanaId ?? null;
$departamentos = $departamentos ?? [];
?>
<!-- Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900">
                Registrar <span class="gradient-text">Simpatizante</span>
            </h2>
            <p class="text-gray-500">Incorpora un nuevo simpatizante a tu red estratégica.</p>
        </div>
        <a href="?page=portal_red" class="px-6 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Volver a Mi Red
        </a>
    </div>
</div>

<div x-data="registroSimpatizantePortal()" x-init="init()">
<div class="glass-card overflow-hidden shadow-md bg-white border-gray-100 p-6 md:p-8">

    <!-- Badge Líder Asignado -->
    <div class="bg-[#002244]/5 border border-[#002244]/10 rounded-2xl p-5 mb-8 flex items-center gap-4">
        <div class="w-12 h-12 bg-[#002244] text-white rounded-full flex items-center justify-center font-bold text-sm shrink-0">
            <?= htmlspecialchars(substr($lider['nombres'] ?? 'L', 0, 1)) ?>
        </div>
        <div>
            <p class="text-[10px] uppercase tracking-widest font-bold text-gray-500">Registrado por</p>
            <p class="font-bold text-gray-900 text-lg"><?= htmlspecialchars(($lider['nombres'] ?? '') . ' ' . ($lider['apellidos'] ?? '')) ?></p>
            <p class="text-xs text-gray-500">Campaña asignada automáticamente</p>
        </div>
        <div class="ml-auto hidden sm:block">
            <span class="px-3 py-1.5 bg-[#002244]/10 text-[#002244] text-[10px] font-bold uppercase tracking-widest rounded-full">
                <i data-lucide="shield-check" class="w-3 h-3 inline mr-1"></i>Auto-asignado
            </span>
        </div>
    </div>

    <?php if (!$campanaId): ?>
    <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-5 rounded-2xl mb-8">
        <div class="flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-6 h-6 shrink-0"></i>
            <div>
                <p class="font-bold">Sin campaña asignada</p>
                <p class="text-sm">No tienes una campaña activa. Contacta al administrador para asignarte una antes de registrar simpatizantes.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form @submit.prevent="submit()" @paste.prevent="handlePaste($event)" class="space-y-6">

        <!-- Datos Personales -->
        <div class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 relative">
            <h3 class="text-xl font-display font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-[#002244]/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="user" class="w-4 h-4 text-[#002244]"></i>
                </span>
                Datos Personales
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Nombres *</label>
                    <input type="text" x-model="form.nombres" required
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all shadow-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Apellidos *</label>
                    <input type="text" x-model="form.apellidos" required
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all shadow-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Tipo Documento</label>
                    <select x-model="form.tipo_documento"
                            class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all appearance-none shadow-sm">
                        <option value="CC">Cédula de Ciudadanía</option>
                        <option value="TI">Tarjeta Identidad</option>
                        <option value="CE">Cédula Extranjería</option>
                        <option value="PA">Pasaporte</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Documento Número *</label>
                    <input type="text" x-model="form.documento" required
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all shadow-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Teléfono / WhatsApp</label>
                    <input type="tel" x-model="form.telefono"
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all shadow-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" x-model="form.email"
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all shadow-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Fecha Nacimiento</label>
                    <input type="date" x-model="form.fecha_nacimiento"
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all shadow-sm text-gray-600">
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-semibold text-gray-700">Género *</label>
                    <select x-model="form.genero" required
                            class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all appearance-none shadow-sm">
                        <option value="">Seleccionar...</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Foto -->
        <div class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5">
                <i data-lucide="camera" class="w-20 h-20"></i>
            </div>
            <h3 class="text-xl font-display font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-[#002244]/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="camera" class="w-4 h-4 text-[#002244]"></i>
                </span>
                Foto de Identificación
            </h3>
            <div class="flex flex-col items-center">
                <div class="relative w-56 h-56 bg-gray-200 rounded-3xl overflow-hidden mb-6 border-8 border-white shadow-2xl ring-1 ring-gray-100">
                    <template x-if="!form.foto">
                        <div class="flex flex-col items-center justify-center h-full text-gray-400 bg-gray-100">
                            <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center shadow-sm mb-3">
                                <i data-lucide="user" class="w-10 h-10 opacity-30"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-40">Sin Imagen</span>
                        </div>
                    </template>
                    <template x-if="form.foto">
                        <img :src="form.foto" class="w-full h-full object-cover">
                    </template>
                    <div x-show="mostrandoCamara" class="absolute inset-0 bg-black z-20">
                        <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center gap-3 w-full">
                    <template x-if="!mostrandoCamara">
                        <button type="button" @click="abrirCamara()"
                                class="flex items-center px-6 py-3 bg-[#002244] text-white rounded-2xl hover:bg-[#003366] transition-all text-sm font-bold shadow-lg shadow-[#002244]/20">
                            <i data-lucide="camera" class="w-4 h-4 mr-2"></i> Abrir Cámara
                        </button>
                    </template>
                    <template x-if="mostrandoCamara">
                        <div class="flex gap-2">
                            <button type="button" @click="capturarFoto()"
                                    class="flex items-center px-6 py-3 bg-[#DAA520] text-white rounded-2xl hover:bg-[#c49520] transition-all text-sm font-bold shadow-lg shadow-[#DAA520]/20">
                                <i data-lucide="aperture" class="w-4 h-4 mr-2"></i> Capturar
                            </button>
                            <button type="button" @click="detenerCamara()"
                                    class="flex items-center px-6 py-3 bg-red-500 text-white rounded-2xl hover:bg-red-600 transition-all text-sm font-bold shadow-lg">
                                <i data-lucide="square" class="w-4 h-4 mr-2"></i> Cancelar
                            </button>
                        </div>
                    </template>
                    <label class="flex items-center px-6 py-3 bg-white text-gray-700 border border-gray-200 rounded-2xl hover:bg-gray-50 transition-all text-sm font-bold shadow-sm cursor-pointer">
                        <i data-lucide="upload" class="w-4 h-4 mr-2 text-[#002244]"></i> Subir Foto
                        <input type="file" class="hidden" @change="handleFileUpload" accept="image/*">
                    </label>
                    <template x-if="form.foto">
                        <button type="button" @click="form.foto = null"
                                class="flex items-center px-6 py-3 text-red-600 hover:bg-red-50 rounded-2xl transition-all text-sm font-bold">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Eliminar
                        </button>
                    </template>
                </div>
                <p class="text-xs text-gray-500 mt-6 text-center">
                    <i data-lucide="clipboard" class="w-3 h-3 inline mr-1 text-[#002244]"></i>
                    También puedes pegar una imagen desde el portapapeles
                    <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded text-[10px] font-mono">Ctrl+V</kbd>
                </p>
            </div>
        </div>

        <!-- Ubicación -->
        <div class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 relative">
            <h3 class="text-xl font-display font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-[#002244]/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#002244]"></i>
                </span>
                Ubicación Geográfica
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Departamento *</label>
                    <div class="relative">
                        <select x-model="form.departamento" @change="cargarMunicipios()" required
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all appearance-none shadow-sm">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($departamentos as $dep): ?>
                                <option value="<?= htmlspecialchars($dep['departamento'] ?? $dep) ?>"><?= htmlspecialchars($dep['departamento'] ?? $dep) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Municipio *</label>
                    <div class="relative">
                        <select x-model="municipio_raw" @change="selectMunicipio()" required :disabled="!form.departamento"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all appearance-none shadow-sm disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="">Seleccionar...</option>
                            <template x-for="mun in listas.municipios" :key="mun.cod_mpio">
                                <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                            </template>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Comuna / Corregimiento</label>
                    <div class="relative">
                        <select x-model="form.territorio" @change="cargarBarrios()" :disabled="!form.municipio"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all appearance-none shadow-sm disabled:bg-gray-100">
                            <option value="">Seleccionar...</option>
                            <template x-for="terr in listas.territorios" :key="terr">
                                <option :value="terr" x-text="terr"></option>
                            </template>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Barrio / Vereda</label>
                    <div class="relative">
                        <select x-model="form.barrio" :disabled="!form.territorio"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all appearance-none shadow-sm disabled:bg-gray-100">
                            <option value="">Seleccionar...</option>
                            <template x-for="b in listas.barrios" :key="b">
                                <option :value="b" x-text="b"></option>
                            </template>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Electoral -->
        <div class="bg-[#002244]/5 p-6 md:p-8 rounded-2xl border border-[#002244]/10">
            <h3 class="text-xl font-display font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="w-8 h-8 bg-[#002244]/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="vote" class="w-4 h-4 text-[#002244]"></i>
                </span>
                Información Electoral
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Puesto de Votación</label>
                    <div class="relative">
                        <select x-model="form.puesto_votacion" :disabled="!form.municipio"
                                class="w-full px-4 py-4 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all appearance-none shadow-sm disabled:bg-gray-100">
                            <option value="">Seleccionar...</option>
                            <template x-for="p in listas.puestos" :key="p.id">
                                <option :value="p.puesto" x-text="p.puesto"></option>
                            </template>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Mesa de Votación</label>
                    <input type="text" x-model="form.mesa_votacion" placeholder="Ej. 14"
                           class="w-full px-4 py-4 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#002244]/5 focus:border-[#002244] transition-all shadow-sm">
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="mt-8 bg-slate-50 p-6 md:p-8 rounded-3xl border border-slate-100">
            <label class="flex items-start mb-8 cursor-pointer group">
                <div class="relative flex items-center justify-center">
                    <input type="checkbox" required
                           class="peer w-6 h-6 text-[#002244] border-gray-300 rounded-lg focus:ring-[#002244]/20 transition-all cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                </div>
                <span class="ml-4 text-sm text-gray-600 leading-relaxed group-hover:text-gray-900 transition-colors">
                    Acepto que los datos serán registrados bajo mi responsabilidad como líder de red.
                </span>
            </label>

            <button type="submit"
                    :disabled="loading || <?= $campanaId ? 'false' : 'true' ?>"
                    class="w-full py-5 px-8 rounded-2xl text-white font-extrabold text-xl shadow-xl bg-gradient-to-r from-[#002244] to-[#004488] hover:scale-[1.01] active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                <span x-show="!loading">Registrar Simpatizante</span>
                <i x-show="!loading" data-lucide="user-plus" class="w-6 h-6"></i>
                <span x-show="loading" class="flex items-center justify-center">
                    <i data-lucide="loader-2" class="animate-spin w-6 h-6 mr-2"></i> Registrando...
                </span>
            </button>
        </div>

    </form>
</div>

<!-- Modal Success -->
<div x-show="successModal"
     style="display: none;"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#002244]/40 backdrop-blur-md"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="bg-white rounded-[2.5rem] shadow-2xl p-10 max-w-md w-full text-center relative overflow-hidden"
         @click.away="resetForm()"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="scale-90 opacity-0 translate-y-10"
         x-transition:enter-end="scale-100 opacity-100 translate-y-0">

        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#DAA520]/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-[#002244]/5 rounded-full blur-3xl"></div>

        <div class="relative">
            <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-xl shadow-green-200">
                <i data-lucide="check" class="w-12 h-12 text-white"></i>
            </div>
            <h2 class="text-3xl font-display font-black text-gray-900 mb-4">¡Registro Exitoso!</h2>
            <p class="text-gray-600 mb-10 leading-relaxed">
                El simpatizante ha sido agregado a tu red estratégica.
            </p>
            <div class="flex gap-3">
                <a href="?page=portal_red"
                   class="flex-1 py-4 px-6 bg-gray-100 text-gray-700 rounded-2xl font-bold hover:bg-gray-200 transition-all">
                    Ver Mi Red
                </a>
                <button @click="resetForm()"
                        class="flex-1 py-4 px-6 bg-[#002244] text-white rounded-2xl font-bold hover:bg-[#003366] transition-all shadow-lg shadow-[#002244]/20">
                    Registrar Otro
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
function registroSimpatizantePortal() {
    return {
        loading: false,
        successModal: false,
        mostrandoCamara: false,
        videoStream: null,

        form: {
            nombres: '',
            apellidos: '',
            tipo_documento: 'CC',
            documento: '',
            fecha_nacimiento: '',
            genero: '',
            email: '',
            telefono: '',
            departamento: '',
            municipio: '',
            cod_mpio: '',
            tipo_territorio: '',
            territorio: '',
            barrio: '',
            puesto_votacion: '',
            mesa_votacion: '',
            foto: null
        },
        municipio_raw: '',

        listas: {
            municipios: [],
            territorios: [],
            barrios: [],
            puestos: []
        },

        _territorioTipoMap: {},

        init() {
            lucide.createIcons();
        },

        async cargarMunicipios() {
            this.listas.municipios = [];
            this.form.municipio = '';
            this.form.cod_mpio = '';
            this.municipio_raw = '';
            this.form.puesto_votacion = '';
            this.listas.puestos = [];

            if (!this.form.departamento) return;

            const res = await fetch(`/aratio/api/territorios.php?accion=municipios&departamento=${encodeURIComponent(this.form.departamento)}`);
            const data = await res.json();
            if (data.success) this.listas.municipios = data.data;
        },

        async selectMunicipio() {
            if (!this.municipio_raw) return;
            try {
                const munObj = JSON.parse(this.municipio_raw);
                this.form.municipio = munObj.municipio;
                this.form.cod_mpio = munObj.cod_mpio;
                this.cargarTiposTerritorio();
                this.cargarPuestos();
            } catch (e) { console.error(e); }
        },

        async cargarPuestos() {
            this.listas.puestos = [];
            this.form.puesto_votacion = '';
            if (!this.form.cod_mpio) return;
            const res = await fetch(`/aratio/api/territorios.php?accion=puestos&cod_mpio=${encodeURIComponent(this.form.cod_mpio)}`);
            const data = await res.json();
            if (data.success) this.listas.puestos = data.data;
        },

        async cargarTiposTerritorio() {
            this.form.territorio = '';
            this.listas.territorios = [];
            if (!this.form.municipio) return;

            const res = await fetch(`/aratio/api/territorios.php?accion=tipos_territorio&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}`);
            const data = await res.json();

            if (data.success && data.data.length > 0) {
                let allTerritorios = [];
                for (let tipo of data.data) {
                    const r = await fetch(`/aratio/api/territorios.php?accion=territorios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(tipo)}`);
                    const d = await r.json();
                    if (d.success) {
                        d.data.forEach(t => allTerritorios.push({nombre: t, tipo: tipo}));
                    }
                }
                this.listas.territorios = allTerritorios.map(t => t.nombre);
                this._territorioTipoMap = allTerritorios.reduce((acc, item) => { acc[item.nombre] = item.tipo; return acc; }, {});
            }
        },

        async cargarBarrios() {
            this.listas.barrios = [];
            this.form.barrio = '';
            if (!this.form.territorio) return;

            const tipo = this._territorioTipoMap[this.form.territorio];
            this.form.tipo_territorio = tipo;

            const res = await fetch(`/aratio/api/territorios.php?accion=barrios&departamento=${encodeURIComponent(this.form.departamento)}&municipio=${encodeURIComponent(this.form.municipio)}&tipo_territorio=${encodeURIComponent(tipo)}&territorio=${encodeURIComponent(this.form.territorio)}`);
            const data = await res.json();
            if (data.success) this.listas.barrios = data.data;
        },

        async abrirCamara() {
            try {
                this.mostrandoCamara = true;
                this.videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "user", width: 480, height: 480 }
                });
                this.$nextTick(() => {
                    this.$refs.video.srcObject = this.videoStream;
                });
            } catch (err) {
                alert("No se pudo acceder a la cámara: " + err.message);
                this.mostrandoCamara = false;
            }
        },

        capturarFoto() {
            const video = this.$refs.video;
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.form.foto = canvas.toDataURL('image/jpeg', 0.8);
            this.detenerCamara();
        },

        detenerCamara() {
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }
            this.mostrandoCamara = false;
        },

        handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.form.foto = event.target.result;
            };
            reader.readAsDataURL(file);
        },

        handlePaste(e) {
            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const file = items[i].getAsFile();
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.form.foto = event.target.result;
                    };
                    reader.readAsDataURL(file);
                    break;
                }
            }
        },

        async submit() {
            this.loading = true;
            try {
                const res = await fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    this.successModal = true;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('Error de conexión');
            }
            this.loading = false;
        },

        resetForm() {
            this.successModal = false;
            this.form.nombres = '';
            this.form.apellidos = '';
            this.form.documento = '';
            this.form.fecha_nacimiento = '';
            this.form.genero = '';
            this.form.telefono = '';
            this.form.puesto_votacion = '';
            this.form.mesa_votacion = '';
            this.form.foto = null;
            window.scrollTo(0, 0);
        }
    }
}
</script>
