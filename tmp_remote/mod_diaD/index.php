<?php
require_once __DIR__ . '/../config/config.php';
// requireAuth(); // Formulario abierto por ahora por petición del usuario

$user = getSessionUser();
$rol = $user['rol'] ?? 'colaborador';
$isAdmin = in_array($rol, ['admin_diad', 'super-admin', 'admin', 'supervisor_diad', 'supervisor']);

// Solo lideres y admins deberían poder ver el form, pero lo dejamos abierto a usuarios autenticados relacionados
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Día D — Aratio Pro</title>
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '<?= COLOR_PRIMARY ?>', 
                        secondary: '<?= COLOR_SECONDARY ?>',
                        accent: '<?= COLOR_ACCENT ?>'
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .gradient-top {
            /* Roman Navy to Roman Gold gradient */
            background: linear-gradient(135deg, <?= COLOR_PRIMARY ?> 0%, <?= COLOR_SECONDARY ?> 100%);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="diaDForm()" x-init="init()">
    
    <!-- Header / Hero (MATCHING REGISTRO SIMPATIZANTE) -->
    <div class="gradient-top text-white pb-24 pt-12 px-4 shadow-lg">
        <div class="w-full text-center">
            <div class="inline-flex items-center justify-center p-3 bg-white/10 backdrop-blur-md rounded-2xl mb-8 border border-white/20">
                <i data-lucide="vote" class="w-10 h-10 text-secondary"></i>
            </div>
            <h1 class="text-5xl font-black mb-4 tracking-tighter uppercase">Día D <span class="text-secondary">—</span> CONTROL ELECTORAL</h1>
            <p class="text-xl opacity-90 mb-8 font-light italic">Reporte Ciudadano & Monitoreo Territorial en Tiempo Real</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <?php if($isAdmin): ?>
                <a href="<?= url('mod_diaD/dashboard.php') ?>" class="inline-flex items-center bg-white text-secondary hover:bg-gray-100 transition-colors rounded-full px-4 py-2 text-sm font-medium shadow-lg">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 mr-2"></i>
                    Ir al Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="w-full px-4 sm:px-6 -mt-16 mb-12 relative z-10">
        
        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-full">
            <div class="p-8">
                
                <!-- Mensaje de Éxito/Error flotante -->
                <div x-show="message.text" 
                    class="p-4 mb-6 text-center font-bold text-sm rounded border"
                    :class="message.type === 'error' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-green-100 border-green-500 text-green-700'"
                    x-transition x-cloak>
                    <span x-text="message.text"></span>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">

                    <!-- SECCIÓN: Quién reporta (Referido Por) -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="user-check" class="w-5 h-5 mr-2 text-primary"></i> Líder Reportando
                        </h3>
                        
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Buscador de Líderes</label>
                            <div class="relative">
                                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-3.5 text-gray-400"></i>
                                <input type="text" x-model="liderQuery" @input.debounce.300ms="searchLider" 
                                    placeholder="Busca por nombre del líder..." 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary bg-white shadow-sm">
                                
                                <!-- Dropdown Resultados -->
                                <div x-show="liderResults.length > 0" @click.away="liderResults = []" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto" x-cloak>
                                    <template x-for="lider in liderResults" :key="lider.id">
                                        <div @click="selectLider(lider)" class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                            <p class="font-bold text-gray-800 uppercase" x-text="lider.nombre_completo"></p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Líder
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <!-- Líder Seleccionado Chip -->
                            <div x-show="selectedLiderId" class="mt-3 flex items-center bg-blue-50 text-secondary px-4 py-2 rounded-full inline-flex border border-blue-100" x-cloak>
                                <i data-lucide="user-check" class="w-4 h-4 mr-2"></i>
                                <span class="font-medium mr-2">Líder:</span>
                                <span x-text="liderSelectedName" class="font-bold uppercase"></span>
                                <button type="button" @click="clearLider()" class="ml-3 hover:text-blue-900">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- DIVIDER -->
                    <div class="border-t border-gray-200 my-6"></div>

                    <!-- SECCIÓN: Información de Votación -->
                    <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 mb-6">
                        <h3 class="text-lg font-bold text-secondary mb-4 flex items-center">
                            <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-secondary"></i> Ubicación del Reporte
                        </h3>
                        <div class="md:grid md:grid-cols-2 md:gap-6 space-y-4 md:space-y-0">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Municipio *</label>
                                <select x-model="selectedMunicipio" @change="loadPuestos" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary disabled:bg-gray-100 shadow-sm">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="mun in municipios" :key="mun">
                                        <option :value="mun" x-text="mun"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Puesto de Votación *</label>
                                <div class="relative">
                                    <i data-lucide="search" class="w-5 h-5 absolute left-3 top-3 text-gray-400"></i>
                                    <input type="text" x-model="puestoQuery" @input.debounce.300ms="searchPuesto" 
                                        :disabled="!selectedMunicipio"
                                        placeholder="Escribe al menos 3 letras para buscar..." 
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary bg-white shadow-sm disabled:bg-gray-100">
                                    <span x-show="puestoQuery.length > 0 && puestoQuery.length < 3" class="text-[10px] text-amber-500 font-bold mt-1 block" x-cloak>Escribe al menos 3 caracteres</span>
                                    
                                    <!-- Dropdown Resultados Puestos -->
                                    <div x-show="puestoResults.length > 0" @click.away="puestoResults = []" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto" x-cloak>
                                        <template x-for="puesto in puestoResults" :key="puesto.id">
                                            <div @click="selectPuesto(puesto)" class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                                <p class="font-bold text-gray-800 uppercase text-sm" x-text="puesto.nombre"></p>
                                                <p class="text-[10px] text-gray-400" x-text="puesto.municipio"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Puesto Seleccionado Chip -->
                                <div x-show="selectedPuesto" class="mt-2 flex items-center bg-teal-50 text-primary px-3 py-1 rounded-full inline-flex border border-teal-100 text-xs" x-cloak>
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                    <span x-text="puestoSelectedName" class="font-bold uppercase"></span>
                                    <button type="button" @click="clearPuesto()" class="ml-2 hover:text-teal-900">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mesa de Votación *</label>
                                <select x-model="selectedMesa" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary shadow-sm font-bold text-lg">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="mesa in mesas" :key="mesa.id_mesa">
                                        <option :value="mesa.id_mesa" x-text="'Mesa ' + mesa.numero"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- DIVIDER -->
                    <div class="border-t border-gray-200 my-6"></div>

                    <!-- SECCIÓN: Recuento de Votos -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="plus-circle" class="w-5 h-5 mr-2 text-primary"></i> Votos Reportados
                        </h3>
                        <div class="md:grid md:grid-cols-2 md:gap-6 space-y-4 md:space-y-0">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nuevos Votos (Esta hora) *</label>
                                <input type="number" x-model.number="votosNuevos" min="0" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg font-bold text-center focus:ring-2 focus:ring-primary shadow-sm" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <!-- Checklist / Enviar -->
                    <div class="mt-8">
                        <label class="flex items-start mb-6 cursor-pointer">
                            <input type="checkbox" required class="mt-1 w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                            <span class="ml-3 text-sm text-gray-600">
                                Certifico que estos datos son reales y tengo el comprobante fotográfico en mi dispositivo para enviarlo al supervisor.
                            </span>
                        </label>

                        <button type="submit" 
                                :disabled="isSubmitting" 
                                class="w-full py-4 px-6 rounded-xl text-white font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed gradient-top">
                            <span x-show="!isSubmitting">Guardar Reporte</span>
                            <span x-show="isSubmitting" class="flex items-center justify-center">
                                <i data-lucide="loader-2" class="animate-spin w-5 h-5 mr-2"></i> Procesando...
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    
    <footer class="bg-white border-t border-gray-100 pt-16 pb-12 px-4 mt-20">
        <div class="w-full max-w-4xl mx-auto text-center">
            <div class="flex items-center justify-center gap-3 mb-6 text-primary">
                <div class="w-10 h-10 bg-primary/5 rounded-xl flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <span class="text-sm font-black uppercase tracking-[0.3em]">Aratio <span class="text-secondary">Pro</span> Digital</span>
            </div>
            <p class="text-xs text-gray-400 mb-6 font-medium">Sistemas de Inteligencia Territorial — Ingeniería Aratio</p>
            <div class="flex justify-center gap-6 text-[10px] font-black text-primary uppercase tracking-widest">
                <a href="<?= url('mod_diaD/CHANGELOG.md') ?>" class="hover:text-secondary transition-colors flex items-center gap-2">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Registro de Versiones
                </a>
                <span class="text-gray-200">|</span>
                <span class="text-gray-400">© 2025 Aratio Intelligence Systems</span>
            </div>
        </div>
    </footer>

    <script>
        // Inicializar iconos
        lucide.createIcons();

        function diaDForm() {
            const API_BASE = '<?= url('api/') ?>';
            return {
                API_BASE: API_BASE,
                liderQuery: '',
                selectedLiderId: null,
                liderSelectedName: '',
                liderResults: [],
                
                municipios: [],
                selectedMunicipio: '',
                puestos: [],
                puestoQuery: '',
                puestoResults: [],
                selectedPuesto: '',
                puestoSelectedName: '',
                mesas: [],
                selectedMesa: '',
                
                votosNuevos: null,
                isSubmitting: false,
                message: { text: '', type: '' },

                init() {
                    // Cargar municipios disponibles
                    fetch(`${this.API_BASE}territorios.php?accion=municipios&departamento=VALLE DEL CAUCA`)
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                this.municipios = data.data.map(m => typeof m === 'object' ? m.municipio : m).sort();
                            }
                        });
                    
                    this.$watch('selectedMunicipio', (val) => {
                        // Bug fix: la variable correcta es 'selectedPuesto', no 'selectedPuestoId'
                        this.selectedPuesto = '';
                        this.puestoSelectedName = '';
                        this.puestoQuery = '';
                        this.puestoResults = [];
                        this.mesas = [];
                        this.selectedMesa = '';
                        if (val) {
                            this.fetchPuestosByMunicipio(val);
                        }
                    });
                },

                async fetchPuestosByMunicipio(muni) {
                    const res = await fetch(`${this.API_BASE}diaD_datos.php?action=puestos&municipio=${encodeURIComponent(muni)}`);
                    const data = await res.json();
                    if(data.success) {
                        this.puestos = data.data;
                    }
                },

                searchLider() {
                    if (this.liderQuery.length < 3) {
                        this.liderResults = [];
                        return;
                    }
                    fetch(`${this.API_BASE}diaD_datos.php?action=buscar_lider&q=${encodeURIComponent(this.liderQuery)}`)
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) this.liderResults = data.data;
                        });
                },

                selectLider(lider) {
                    this.liderSelectedName = lider.nombre_completo;
                    this.selectedLiderId = lider.id;
                    this.liderQuery = '';
                    this.liderResults = [];
                },
                
                clearLider() {
                    this.selectedLiderId = null;
                    this.liderSelectedName = '';
                },

                searchPuesto() {
                    if (!this.selectedMunicipio) return;
                    if (this.puestoQuery.length < 3) {
                        this.puestoResults = [];
                        return;
                    }
                    
                    // Búsqueda contextual dentro del municipio
                    const query = this.puestoQuery.toLowerCase();
                    this.puestoResults = this.puestos.filter(p => 
                        p.municipio === this.selectedMunicipio && 
                        p.nombre.toLowerCase().includes(query)
                    ).slice(0, 10);
                },

                selectPuesto(puesto) {
                    this.selectedPuesto = puesto.id;
                    this.puestoSelectedName = puesto.nombre;
                    this.puestoQuery = '';
                    this.puestoResults = [];
                    this.loadMesas(puesto.id);
                },

                loadMesas(puestoId) {
                    this.mesas = [];
                    this.selectedMesa = '';
                    fetch(`${this.API_BASE}diaD_datos.php?action=mesas&puesto_id=${puestoId}`)
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) this.mesas = data.data;
                        });
                },

                clearPuesto() {
                    this.selectedPuesto = '';
                    this.puestoSelectedName = '';
                    this.puestoQuery = '';
                },

                loadPuestos() {
                    this.clearPuesto();
                    // Optional: keep this if you want to support direct select in future
                },

                async submitForm() {
                    // Validaciones básicas
                    if (!this.selectedLiderId) return this.showMessage('Selecciona un líder válido.', 'error');
                    if (!this.selectedPuesto) return this.showMessage('Selecciona un puesto de votación.', 'error');
                    if (!this.selectedMesa) return this.showMessage('Selecciona una mesa.', 'error');
                    if (this.votosNuevos === null) return this.showMessage('Ingresa los votos.', 'error');

                    this.isSubmitting = true;
                    this.message = { text: '', type: '' };

                    try {
                        const payload = {
                            id_campaña: '02',
                            id_colaborador: this.selectedLiderId,
                            id_puesto: this.selectedPuesto,
                            id_mesa: this.selectedMesa,
                            votos_nuevos: this.votosNuevos
                        };

                        const res = await fetch(`${this.API_BASE}diaD_reportes.php`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();

                        if (data.success) {
                            alert('Reporte guardado exitosamente.');
                            
                            // Resetear campos numéricos
                            this.votosNuevos = null;
                            this.isSubmitting = false;
                        } else {
                            this.showMessage(data.message || 'Error al guardar', 'error');
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        this.showMessage('Error de red', 'error');
                        this.isSubmitting = false;
                    }
                },

                showMessage(text, type) {
                    this.message = { text, type };
                    if (type === 'error') setTimeout(() => this.message.text = '', 5000);
                },

                closeModalAndWhatsApp() {
                    this.showSuccessModal = false;
                    if (this.pendingWaUrl) {
                        window.open(this.pendingWaUrl, '_blank');
                    }
                }
            }
        }
    </script>
</body>
</html>
