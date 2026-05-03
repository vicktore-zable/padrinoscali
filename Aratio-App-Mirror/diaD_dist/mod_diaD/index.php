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
    <title>Captura Día D - Aratio</title>
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a5f', // Roman Navy
                        secondary: '#d4af37', // Roman Gold
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
            background: linear-gradient(135deg, #1e3a5f 0%, #d4af37 100%);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="diaDForm()" x-init="init()">
    
    <!-- Header / Hero (MATCHING REGISTRO SIMPATIZANTE) -->
    <div class="gradient-top text-white pb-24 pt-12 px-4 shadow-lg">
        <div class="container mx-auto max-w-4xl text-center">
            <h1 class="text-4xl font-extrabold mb-4">Reporte Día D</h1>
            <p class="text-xl opacity-90 mb-6">Ingresa el recuento de tu mesa asignada. ¡Gracias por tu esfuerzo!</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <?php if($isAdmin): ?>
                <a href="/mod_diaD/dashboard.php" class="inline-flex items-center bg-white text-secondary hover:bg-gray-100 transition-colors rounded-full px-4 py-2 text-sm font-medium shadow-lg">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 mr-2"></i>
                    Ir al Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container mx-auto max-w-3xl px-4 -mt-16 mb-12 relative z-10">
        
        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
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
                                <select x-model="selectedPuesto" required :disabled="!selectedMunicipio" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary disabled:bg-gray-100 shadow-sm">
                                    <option value="">Seleccionar...</option>
                                    <template x-for="puesto in filteredPuestos" :key="puesto.id">
                                        <option :value="puesto.id" x-text="puesto.nombre"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mesa de Votación *</label>
                                <input type="number" x-model="selectedMesa" required min="1" max="99" 
                                    placeholder="00" maxlength="2"
                                    oninput="if(this.value.length > 2) this.value = this.value.slice(0, 2)"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary shadow-sm text-center font-bold text-lg">
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
    
    </footer>

    <script>
        // Inicializar iconos
        lucide.createIcons();

        function diaDForm() {
            return {
                liderQuery: '',
                selectedLiderId: null,
                liderSelectedName: '',
                liderResults: [],
                
                municipios: [],
                selectedMunicipio: '',
                puestos: [],
                filteredPuestos: [],
                selectedPuesto: '',
                mesas: [],
                selectedMesa: '',
                
                votosNuevos: null,
                isSubmitting: false,
                message: { text: '', type: '' },

                init() {
                    // Cargar puestos de votación y extraer municipios únicos
                    fetch('/api/diaD_datos.php?action=puestos')
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                this.puestos = data.data;
                                // Extraer municipios únicos
                                const muns = new Set(this.puestos.map(p => p.municipio).filter(Boolean));
                                this.municipios = Array.from(muns).sort();
                            }
                        })
                        .catch(err => console.error(err));
                },

                searchLider() {
                    if (this.liderQuery.length < 3) {
                        this.liderResults = [];
                        return;
                    }
                    fetch(`/api/diaD_datos.php?action=buscar_lider&q=${encodeURIComponent(this.liderQuery)}`)
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

                loadPuestos() {
                    this.selectedPuesto = '';
                    this.filteredPuestos = this.puestos.filter(p => p.municipio === this.selectedMunicipio);
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

                        const res = await fetch('/api/diaD_reportes.php', {
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
