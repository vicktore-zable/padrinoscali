<?php
/**
 * PÁGINA PÚBLICA: Registro de Simpatizantes - Edición Aratio Gold v2.6.8
 * Diseño Premium Magenta/Gold + Refactorización Profesional de Alpine.js
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apoyo Ciudadano - Aratio Gold</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 
                        primary: '#FF00FF', 
                        secondary: '#FFD700', 
                        dark: '#1e1b4b',
                        magenta: { 50: '#fdf2f8', 100: '#fce7f3', 500: '#FF00FF', 600: '#e100e1' }
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .gradient-gold-magenta { background: linear-gradient(135deg, #FF00FF 0%, #FFD700 100%); }
        .card-premium { 
            background-color: white; 
            border-radius: 2rem; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); 
            border: 1px solid #f1f5f9; 
            overflow: hidden; 
        }
        .input-gold { 
            width: 100%; 
            padding: 0.875rem 1.25rem; 
            background-color: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 0.75rem; 
            transition: all 0.2s; 
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); 
        }
        .input-gold:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 0, 255, 0.2);
            border-color: #FF00FF;
        }
        .label-gold { 
            display: block; 
            font-size: 0.875rem; 
            font-weight: 700; 
            color: #334155; 
            margin-bottom: 0.5rem; 
            margin-left: 0.25rem; 
        }
        [x-cloak] { display: none !important; }
        .animate-fade-up { animation: fadeUp 0.5s ease-out; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-10" x-data="registrationForm()" x-init="init()">

    <!-- Barra de Navegación Profesional -->
    <nav class="fixed top-0 left-0 w-full z-50 px-4 py-4 pointer-events-none">
        <div class="container mx-auto max-w-4xl flex justify-between items-center bg-white/80 backdrop-blur-md rounded-2xl px-6 py-3 shadow-lg border border-white/20 pointer-events-auto">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 gradient-gold-magenta rounded-lg flex items-center justify-center text-white font-black text-xs">A</div>
                <span class="font-black text-slate-800 tracking-tighter hidden md:block">ARATIO GOLD</span>
            </div>
            <div class="flex items-center gap-1 md:gap-4">
                <a href="/" class="flex items-center gap-2 px-3 py-2 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-primary transition-all text-xs font-bold">
                    <i data-lucide="home" class="w-4 h-4"></i> <span class="hidden sm:inline">Inicio</span>
                </a>

                <a href="/aratio/index.php?page=portal_landing" class="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition-all text-xs font-bold shadow-md shadow-slate-200">
                    <i data-lucide="log-in" class="w-4 h-4"></i> <span>Módulo Líder</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Header Hero -->
    <header class="gradient-gold-magenta text-white pb-32 pt-28 px-6 shadow-2xl text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white"></path>
            </svg>
        </div>
        <div class="container mx-auto max-w-4xl relative z-10 animate-fade-up">
            <div class="inline-flex px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full text-[10px] font-black uppercase tracking-[0.3em] mb-4 border border-white/30">
                Portal de Simpatizantes
            </div>
            <h1 class="text-5xl md:text-6xl font-black mb-4 tracking-tight drop-shadow-md">¡Apoya el Cambio!</h1>
            <p class="text-xl md:text-2xl opacity-90 font-light max-w-2xl mx-auto">Tu presencia fortalece nuestro compromiso con el futuro.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto max-w-3xl px-4 -mt-20 mb-20 relative z-20">
        <div class="card-premium animate-fade-up" style="animation-delay: 0.1s">
            <div class="p-8 md:p-12">
                <form @submit.prevent="submitForm" class="space-y-10">
                    
                    <!-- SECCIÓN 1: IDENTIDAD DE CAMPAÑA -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-10 h-10 rounded-full bg-magenta-50 flex items-center justify-center text-primary shadow-inner">
                                <i data-lucide="flag" class="w-5 h-5"></i>
                            </span>
                            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Campaña y Referencia</h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="label-gold">Campaña a la que apoyas *</label>
                                <select x-model="form.campana_id" @change="handleCampanaChange" required class="input-gold font-semibold">
                                    <option value="">-- Selecciona una campaña --</option>
                                    <?php foreach ($campanas as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Buscador de Líder -->
                            <div x-show="form.campana_id" x-cloak class="pt-6 border-t border-slate-100 transition-all">
                                <label class="label-gold">¿Quién te invitó? (Líder Referente)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
                                    </div>
                                    <input type="text" 
                                           x-model="searchQuery" 
                                           @input.debounce.300ms="searchLideres" 
                                           placeholder="Busca por nombre o apellido..." 
                                           class="input-gold pl-12 bg-slate-50 focus:bg-white">
                                    
                                    <!-- Resultados -->
                                    <div x-show="searchResults.length > 0" 
                                         class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl max-h-72 overflow-y-auto overflow-x-hidden divide-y divide-slate-50 ring-4 ring-black/5"
                                         @click.away="searchResults = []">
                                        <template x-for="lider in searchResults" :key="lider.documento">
                                            <button type="button" 
                                                    @click="selectLider(lider)" 
                                                    class="w-full p-4 text-left hover:bg-magenta-50 transition-colors flex items-center justify-between group">
                                                <div>
                                                    <p class="font-bold text-slate-800 group-hover:text-primary transition-colors" x-text="lider.nombres + ' ' + lider.apellidos"></p>
                                                    <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest" x-text="lider.perfil"></p>
                                                </div>
                                                <i data-lucide="plus-circle" class="w-5 h-5 text-slate-200 group-hover:text-primary transition-all transform group-hover:scale-110"></i>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Lider Seleccionado -->
                                <div x-show="form.lider_directo" 
                                     class="mt-4 p-4 bg-blue-50/50 rounded-2xl border border-blue-100 flex items-center gap-4 animate-fade-up">
                                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 shadow-sm border border-blue-200">
                                        <i data-lucide="user-check" class="w-6 h-6"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-0.5 tracking-tighter">Referente de Campaña</p>
                                        <p class="font-bold text-blue-900" x-text="selectedLiderName"></p>
                                    </div>
                                    <button type="button" @click="clearLider" class="p-2 hover:bg-blue-100 rounded-full text-blue-300 hover:text-red-500 transition-all">
                                        <i data-lucide="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: DATOS PERSONALES -->
                    <div x-show="form.campana_id" x-cloak class="space-y-6 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full bg-magenta-50 flex items-center justify-center text-primary shadow-inner">
                                <i data-lucide="smile" class="w-5 h-5"></i>
                            </span>
                            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Tus Datos</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><label class="label-gold">Nombres *</label><input type="text" x-model="form.nombres" required class="input-gold"></div>
                            <div><label class="label-gold">Apellidos *</label><input type="text" x-model="form.apellidos" required class="input-gold"></div>
                            <div><label class="label-gold">Cédula *</label><input type="text" x-model="form.documento" required class="input-gold"></div>
                            <div><label class="label-gold">WhatsApp / Celular *</label><input type="tel" x-model="form.telefono" required class="input-gold" placeholder="3XX XXX XXXX"></div>
                            <div><label class="label-gold">Fecha de Nacimiento</label><input type="date" x-model="form.fecha_nacimiento" class="input-gold"></div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: UBICACIÓN GEOGRÁFICA (4 NIVELES GOLD) -->
                    <div x-show="form.campana_id" x-cloak class="bg-slate-50/70 p-8 rounded-[2rem] border border-slate-100 space-y-8">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-primary shadow-sm">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                            </span>
                            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Ubicación</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="label-gold">Departamento *</label>
                                <select x-model="form.departamento" @change="loadMunicipios" required class="input-gold font-semibold">
                                    <option value="">-- Selecciona --</option>
                                    <?php foreach ($departamentos as $d): ?>
                                    <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="label-gold">Municipio *</label>
                                <select x-model="rawMunicipio" @change="handleMunicipioSelect" required :disabled="!form.departamento" class="input-gold font-semibold disabled:opacity-50">
                                    <option value="">-- Selecciona --</option>
                                    <template x-for="mun in listMunicipios" :key="mun.cod_mpio">
                                        <option :value="JSON.stringify(mun)" x-text="mun.municipio"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="label-gold">Zona</label>
                                    <select x-model="form.tipo_territorio" @change="loadNivel3" :disabled="!form.cod_mpio" class="input-gold disabled:opacity-50">
                                        <option value="">-- Selecciona --</option>
                                        <template x-for="t in listTipos" :key="t">
                                            <option :value="t" x-text="t"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="label-gold">Sector / Territorio</label>
                                    <select x-model="form.territorio" @change="loadNivel4" :disabled="!form.tipo_territorio" class="input-gold disabled:opacity-50">
                                        <option value="">-- Selecciona --</option>
                                        <template x-for="ter in listTerritorios" :key="ter">
                                            <option :value="ter" x-text="ter"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-full">
                                    <label class="label-gold font-black text-primary uppercase text-[10px] tracking-widest">Barrio / Vereda de Residencia</label>
                                    <select x-model="form.barrio" :disabled="!form.territorio" class="input-gold border-primary/20 font-bold text-primary disabled:opacity-50">
                                        <option value="">-- Confirma tu ubicación --</option>
                                        <template x-for="b in listBarrios" :key="b">
                                            <option :value="b" x-text="b"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- PUESTO DE VOTACIÓN -->
                        <div class="pt-6 border-t border-slate-200">
                            <label class="label-gold text-slate-800 font-black mb-4">Información de Votación *</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2">
                                    <label class="text-[10px] font-bold text-slate-400 ml-1 mb-1 block uppercase">Puesto</label>
                                    <select x-model="form.puesto_votacion" required :disabled="!form.cod_mpio" class="input-gold bg-white">
                                        <option value="">-- ¿Dónde votas? --</option>
                                        <template x-for="p in listPuestos" :key="p">
                                            <option :value="p" x-text="p"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 ml-1 mb-1 block uppercase">Mesa</label>
                                    <input type="text" x-model="form.mesa_votacion" placeholder="Ej: 10" class="input-gold text-center">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: ÁREAS DE INTERÉS -->
                    <div x-show="form.campana_id" x-cloak class="space-y-6 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full bg-magenta-50 flex items-center justify-center text-primary shadow-inner">
                                <i data-lucide="heart" class="w-5 h-5"></i>
                            </span>
                            <h2 class="text-2xl font-black text-slate-800 tracking-tight">¿Cómo quieres participar?</h2>
                        </div>
                        
                        <div class="flex flex-wrap gap-3">
                            <template x-for="area in allAreas" :key="area">
                                <button type="button" 
                                        @click="toggleInterest(area)" 
                                        class="px-6 py-3 rounded-2xl text-xs font-black border-2 transition-all transform active:scale-95 shadow-sm uppercase tracking-tighter"
                                        :class="form.areas_interes.includes(area) 
                                                ? 'bg-primary border-primary text-white shadow-primary/30' 
                                                : 'bg-white border-slate-100 text-slate-400 hover:border-primary/40 hover:text-primary'">
                                    <span x-text="area"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- SUBMIT -->
                    <div x-show="form.campana_id" x-cloak class="pt-8 text-center">
                        <button type="submit" 
                                :disabled="isSubmitting" 
                                class="w-full py-6 rounded-[1.5rem] text-white font-black text-2xl shadow-2xl gradient-gold-magenta transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 ring-4 ring-primary/10">
                            <span x-show="!isSubmitting">Confirmar mi Apoyo</span>
                            <div x-show="isSubmitting" class="flex items-center justify-center gap-3">
                                <div class="w-8 h-8 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Procesando...</span>
                            </div>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <footer class="text-center pt-8 pb-12 text-slate-400 relative z-10">
        <div class="mb-4 flex flex-col items-center gap-2">
            <span class="text-[10px] font-black uppercase tracking-[0.5em] text-slate-300">Aratio Political Tech</span>
            <div class="h-1 w-10 bg-secondary rounded-full opacity-40"></div>
        </div>
        <p class="text-[11px] font-bold">Modulo de Simpatizantes <span class="text-primary tracking-widest px-2 py-1 bg-white rounded-md border border-slate-100 ml-1">v2.6.8 Gold</span></p>
        <div class="mt-4 inline-flex items-center gap-2 text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 px-4 py-1.5 rounded-full border border-emerald-100 shadow-sm">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Digital Security Verified - 2026
        </div>
    </footer>

<script>
function registrationForm() {
    return {
        isSubmitting: false,
        searchQuery: '',
        searchResults: [],
        selectedLiderName: '',
        rawMunicipio: '',
        allAreas: ['Voto de Opinión', 'Movilización', 'Redes Sociales', 'Logística y Eventos', 'Publicidad y Vallas', 'Sistemas e IT', 'Apoyo Legal'],
        
        listMunicipios: [],
        listTipos: [],
        listTerritorios: [],
        listBarrios: [],
        listPuestos: [],

        form: { 
            campana_id: '', nombres: '', apellidos: '', documento: '', telefono: '', fecha_nacimiento: '',
            departamento: '', municipio: '', cod_mpio: '',
            tipo_territorio: '', territorio: '', barrio: '',
            puesto_votacion: '', mesa_votacion: '',
            lider_directo: '', areas_interes: [], habeas_data: true
        },

        init() { lucide.createIcons(); },

        async apiFetch(url, params = {}) {
            const query = new URLSearchParams(params).toString();
            try {
                const response = await fetch(`${url}${query ? '?' + query : ''}`);
                if (!response.ok) throw new Error('API Sync Error');
                return await response.json();
            } catch (error) { return { success: false }; }
        },

        async searchLideres() {
            if (this.searchQuery.length < 3) { this.searchResults = []; return; }
            const data = await this.apiFetch('/api/colaboradores/lideres', {
                campana_id: this.form.campana_id,
                search: this.searchQuery
            });
            if (data.success) { this.searchResults = data.data; setTimeout(() => lucide.createIcons(), 50); }
        },

        selectLider(lider) {
            this.form.lider_directo = lider.documento;
            this.selectedLiderName = `${lider.nombres} ${lider.apellidos}`;
            this.searchQuery = '';
            this.searchResults = [];
            setTimeout(() => lucide.createIcons(), 50);
        },

        clearLider() {
            this.form.lider_directo = '';
            this.selectedLiderName = '';
            this.searchQuery = '';
        },

        handleCampanaChange() { this.clearLider(); },

        async loadMunicipios() {
            this.resetGeoState();
            const data = await this.apiFetch('/territorios/municipios-cascada', { departamento: this.form.departamento });
            if (data.success) this.listMunicipios = data.data;
        },

        async handleMunicipioSelect() {
            if (!this.rawMunicipio) return;
            const mun = JSON.parse(this.rawMunicipio);
            this.form.municipio = mun.municipio;
            this.form.cod_mpio = mun.cod_mpio;
            this.resetGeoState(true);
            const data = await this.apiFetch('/territorios/tipos', { cod_mpio: this.form.cod_mpio });
            if (data.success) this.listTipos = data.data;
            this.loadPuestos();
        },

        async loadNivel3() {
            this.listTerritorios = []; this.listBarrios = []; this.form.territorio = ''; this.form.barrio = '';
            const data = await this.apiFetch('/territorios/territorios', { cod_mpio: this.form.cod_mpio, tipo: this.form.tipo_territorio });
            if (data.success) this.listTerritorios = data.data;
        },

        async loadNivel4() {
            this.listBarrios = []; this.form.barrio = '';
            const data = await this.apiFetch('/territorios/barrios', { cod_mpio: this.form.cod_mpio, tipo: this.form.tipo_territorio, territorio: this.form.territorio });
            if (data.success) this.listBarrios = data.data;
        },

        async loadPuestos() {
            const data = await this.apiFetch('/territorios/puestos', { cod_mpio: this.form.cod_mpio });
            if (data.success) this.listPuestos = data.data;
        },

        resetGeoState(keepMun = false) {
            if (!keepMun) { this.form.municipio = ''; this.form.cod_mpio = ''; }
            this.form.tipo_territorio = ''; this.form.territorio = ''; this.form.barrio = '';
            this.listTipos = []; this.listTerritorios = []; this.listBarrios = [];
        },

        toggleInterest(area) {
            const index = this.form.areas_interes.indexOf(area);
            if (index > -1) this.form.areas_interes.splice(index, 1);
            else this.form.areas_interes.push(area);
        },

        async submitForm() {
            this.isSubmitting = true;
            try {
                const response = await fetch('/registro-simpatizante', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await response.json();
                if (data.success) { alert('¡Gracias por tu apoyo!'); location.href = '/'; } 
                else alert('Atención: ' + (data.message || 'Error'));
            } catch (e) { alert('Error de red'); }
            this.isSubmitting = false;
        }
    }
}
</script>
</body>
</html>
