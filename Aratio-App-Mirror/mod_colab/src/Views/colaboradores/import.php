<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">

        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Importación Masiva</h1>
                <p class="text-gray-600">Carga múltiples colaboradores desde un archivo Excel</p>
            </div>
            <a href="/colaboradores" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>

        <!-- Card Principal -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden" x-data="importForm()">
            <div class="p-6">

                <!-- Paso 1: Descargar Plantilla -->
                <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-800">Paso 1: Descargar Plantilla</h3>
                            <p class="mt-2 text-sm text-blue-700">
                                Para asegurar una importación correcta, utiliza nuestra plantilla oficial.
                                Contiene los encabezados requeridos y ejemplos de datos.
                            </p>
                            <div class="mt-4">
                                <a href="/colaboradores/import/template"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Descargar Plantilla Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paso 2: Subir Archivo -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Paso 2: Subir Archivo Diligenciado</h3>

                    <form @submit.prevent="submit" class="space-y-6">

                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors"
                            @dragover.prevent="dragover = true" @dragleave.prevent="dragover = false"
                            @drop.prevent="handleDrop" :class="{ 'border-blue-500 bg-blue-50': dragover }">

                            <div x-show="!file">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="mt-4 flex text-sm text-gray-600 justify-center">
                                    <label for="file-upload"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Sube un archivo</span>
                                        <input id="file-upload" name="file-upload" type="file" class="sr-only"
                                            accept=".xlsx, .xls" @change="handleFileSelect">
                                    </label>
                                    <p class="pl-1">o arrastra y suelta</p>
                                </div>
                                <p class="text-xs text-gray-500">Excel (.xlsx, .xls) hasta 10MB</p>
                            </div>

                            <div x-show="file" class="text-left">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-8 h-8 text-green-500 mr-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900" x-text="file ? file.name : ''">
                                            </p>
                                            <p class="text-xs text-gray-500" x-text="file ? formatSize(file.size) : ''">
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="file = null" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Progreso -->
                        <div x-show="uploading" class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                :style="'width: ' + progress + '%'"></div>
                        </div>

                        <!-- Botón Submit -->
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary w-full sm:w-auto"
                                :disabled="!file || uploading">
                                <span x-show="!uploading">Importar Colaboradores</span>
                                <span x-show="uploading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Procesando...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Resultados -->
                <div x-show="result" class="mt-8 animate-fade-in">
                    <div :class="result.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
                        class="rounded-lg border p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg x-show="result.success" class="h-5 w-5 text-green-400" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <svg x-show="!result.success" class="h-5 w-5 text-red-400" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium"
                                    :class="result.success ? 'text-green-800' : 'text-red-800'" x-text="result.message">
                                </h3>

                                <div x-show="result.data" class="mt-2 text-sm"
                                    :class="result.success ? 'text-green-700' : 'text-red-700'">
                                    <p>Exitosos: <span class="font-bold" x-text="result.data.exitosos"></span></p>
                                    <p>Fallidos: <span class="font-bold" x-text="result.data.fallidos"></span></p>
                                </div>

                                <div x-show="result.data && result.data.errores && result.data.errores.length > 0"
                                    class="mt-4">
                                    <p class="font-medium mb-2">Detalle de errores:</p>
                                    <ul class="list-disc pl-5 space-y-1 max-h-40 overflow-y-auto">
                                        <template x-for="error in result.data.errores">
                                            <li>
                                                Fila <span x-text="error.fila"></span>: <span
                                                    x-text="error.error"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function importForm() {
        return {
            file: null,
            dragover: false,
            uploading: false,
            progress: 0,
            result: null,

            handleFileSelect(e) {
                if (e.target.files.length > 0) {
                    this.file = e.target.files[0];
                    this.result = null;
                }
            },

            handleDrop(e) {
                this.dragover = false;
                if (e.dataTransfer.files.length > 0) {
                    this.file = e.dataTransfer.files[0];
                    this.result = null;
                }
            },

            formatSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },

            async submit() {
                if (!this.file) return;

                this.uploading = true;
                this.progress = 0;
                this.result = null;

                const formData = new FormData();
                formData.append('archivo', this.file);

                try {
                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            this.progress = Math.round((e.loaded * 100) / e.total);
                        }
                    });

                    const response = await new Promise((resolve, reject) => {
                        xhr.open('POST', '/colaboradores/import');
                        xhr.setRequestHeader('X-CSRF-Token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

                        xhr.onload = () => {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                resolve(JSON.parse(xhr.responseText));
                            } else {
                                reject(new Error('Error en la subida'));
                            }
                        };

                        xhr.onerror = () => reject(new Error('Error de red'));
                        xhr.send(formData);
                    });

                    this.result = response;
                    if (response.success) {
                        this.file = null;
                        // Opcional: recargar o redirigir
                    }

                } catch (error) {
                    this.result = {
                        success: false,
                        message: error.message || 'Ocurrió un error inesperado'
                    };
                } finally {
                    this.uploading = false;
                }
            }
        }
    }
</script>