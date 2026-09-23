<?php
require_once __DIR__ . '/../../config/config.php';

$eventoId = $_GET['evento'] ?? null;

if (!$eventoId) {
    header('Location: /');
    exit;
}

$db = getDB();

$stmt = $db->prepare("SELECT id, nombre, fecha_inicio, ubicacion FROM eventos WHERE id = ?");
$stmt->execute([$eventoId]);
$evento = $stmt->fetch();

if (!$evento) {
    echo '<h1>Evento no encontrado</h1>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia — <?= htmlspecialchars($evento['nombre']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .signature-pad {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            touch-action: none;
            background: #FAFAFA;
            transition: border-color 0.2s;
        }
        .signature-pad:hover {
            border-color: #5B2A86;
        }
        .signature-pad.active {
            border-color: #E6007E;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4" x-data="registroData()" x-init="initFirma()">
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <img src="/aratio/assets/logo-small.png" alt="Padrinos Cali"
                 class="h-14 mx-auto mb-4 object-contain"
                 onerror="this.style.display='none'">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #E6007E, #5B2A86);">
                <i data-lucide="calendar-check" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold" style="color: #5B2A86;">Registro de Asistencia</h1>
            <p class="text-gray-600 mt-2 font-medium"><?= htmlspecialchars($evento['nombre']) ?></p>
            <p class="text-sm text-gray-500 flex items-center justify-center gap-1 mt-1">
                <i data-lucide="calendar" class="w-3.5 h-3.5 inline"></i>
                <?= date('d/m/Y H:i', strtotime($evento['fecha_inicio'])) ?>
                <span class="mx-1">—</span>
                <i data-lucide="map-pin" class="w-3.5 h-3.5 inline"></i>
                <?= htmlspecialchars($evento['ubicacion']) ?>
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <div x-show="!registrado">
                <form @submit.prevent="registrar()" class="space-y-4">
                    <input type="hidden" x-model="form.evento_id" value="<?= $eventoId ?>">

                        <div>
                            <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Número Documento *</label>
                            <input type="text" x-model="form.documento" required
                                   @blur="buscarDocumento()"
                                   @keyup.enter.prevent="buscarDocumento()"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all"
                                   style="focus-ring-color: #E6007E;">
                        </div>
                    </div>

                    <!-- Banner Bienvenida (autocompletado) -->
                    <div x-show="datosAutocompletados && mensajeBienvenida" x-cloak
                         class="rounded-xl p-4 flex items-start gap-3 border"
                         style="background: rgba(65,184,83,0.08); border-color: rgba(65,184,83,0.25);">
                        <i data-lucide="user-check" class="w-5 h-5 mt-0.5 flex-shrink-0" style="color: #41B853;"></i>
                        <div>
                            <p class="text-sm font-bold" style="color: #16a34a;">¡Bienvenido(a)!</p>
                            <p class="text-xs text-gray-600 mt-1" x-text="mensajeBienvenida"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Tipo Documento *</label>
                            <select x-model="form.tipo_documento" required :disabled="datosAutocompletados" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                                <option value="cc">Cédula de Ciudadanía</option>
                                <option value="ce">Cédula de Extranjería</option>
                                <option value="ti">Tarjeta de Identidad</option>
                                <option value="pasaporte">Pasaporte</option>
                            </select>
                        </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Nombre Completo *</label>
                        <input type="text" x-model="form.nombre" required
                               :readonly="datosAutocompletados"
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all"
                               style="focus-ring-color: #E6007E;">
                    </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Teléfono</label>
                            <input type="tel" x-model="form.telefono"
                                   :readonly="datosAutocompletados"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Email</label>
                            <input type="email" x-model="form.email"
                                   :readonly="datosAutocompletados"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Género *</label>
                            <select x-model="form.genero" required :disabled="datosAutocompletados" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                                <option value="">Seleccionar...</option>
                                <option value="masculino">Masculino</option>
                                <option value="femenino">Femenino</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Fecha de Nacimiento</label>
                            <input type="date" x-model="form.fecha_nacimiento"
                                   :readonly="datosAutocompletados"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Departamento *</label>
                            <input type="text" x-model="form.departamento" required
                                   :readonly="datosAutocompletados"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2" style="color: #5B2A86;">Municipio *</label>
                            <input type="text" x-model="form.municipio" required
                                   :readonly="datosAutocompletados"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Firma Digital -->
                    <div class="border-t pt-5" style="border-color: #f0f0f0;">
                        <label class="block text-sm font-semibold mb-3 flex items-center gap-2" style="color: #5B2A86;">
                            <i data-lucide="pen-tool" class="w-4 h-4" style="color: #E6007E;"></i>
                            Firma Digital *
                        </label>
                        <div class="relative">
                            <canvas id="signaturePad" width="600" height="200"
                                    class="signature-pad w-full cursor-crosshair"></canvas>
                            <button type="button" @click="limpiarFirma()"
                                    class="absolute top-2 right-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1"
                                    style="background: rgba(230,0,126,0.08); color: #E6007E;"
                                    onmouseover="this.style.background='rgba(230,0,126,0.15)'" onmouseout="this.style.background='rgba(230,0,126,0.08)'">
                                <i data-lucide="eraser" class="w-3 h-3"></i> Limpiar
                            </button>
                        </div>
                        <p class="text-xs mt-2 flex items-center gap-1" style="color: #9CA3AF;">
                            <i data-lucide="info" class="w-3 h-3 inline"></i>
                            Dibuje su firma usando el mouse o el dedo (pantalla táctil)
                        </p>
                    </div>

                    <div class="space-y-3 pt-2 border-t" style="border-color: #f0f0f0;">
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl transition-all" style="background: rgba(91,42,134,0.04);" onmouseover="this.style.background='rgba(91,42,134,0.08)'" onmouseout="this.style.background='rgba(91,42,134,0.04)'">
                            <input type="checkbox" x-model="form.habeas_data" id="habeas" class="w-4 h-4 rounded mt-0.5" style="accent-color: #E6007E;">
                            <span class="text-xs text-gray-600"><strong class="text-red-500">*</strong> Autorizo el tratamiento de mis datos personales de acuerdo con la Ley 1581 de 2012</span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl transition-all" style="background: rgba(91,42,134,0.04);" onmouseover="this.style.background='rgba(91,42,134,0.08)'" onmouseout="this.style.background='rgba(91,42,134,0.04)'">
                            <input type="checkbox" x-model="form.acepta_comunicaciones" id="comunicaciones" class="w-4 h-4 rounded mt-0.5" style="accent-color: #E6007E;">
                            <span class="text-xs text-gray-600">Acepto recibir información sobre eventos, actividades y comunicaciones de la campaña</span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl transition-all" style="background: rgba(91,42,134,0.04);" onmouseover="this.style.background='rgba(91,42,134,0.08)'" onmouseout="this.style.background='rgba(91,42,134,0.04)'">
                            <input type="checkbox" x-model="form.autorizacion_imagenes" id="imagenes" class="w-4 h-4 rounded mt-0.5" style="accent-color: #E6007E;">
                            <span class="text-xs text-gray-600">Autorizo el uso de mi imagen, voz y datos en materiales promocionales y de la campaña</span>
                        </label>
                    </div>

                    <button type="submit" :disabled="cargando"
                            class="w-full py-3 rounded-xl font-medium text-sm text-white transition-all shadow-lg disabled:opacity-50 flex items-center justify-center gap-2"
                            style="background: linear-gradient(135deg, #E6007E, #5B2A86);"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <i data-lucide="pen-tool" class="w-4 h-4" x-show="!cargando"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="cargando"></i>
                        <span x-text="cargando ? 'Registrando...' : 'Firmar y Registrar'"></span>
                    </button>
                </form>
            </div>

            <div x-show="registrado" class="text-center py-8">
                <img src="/aratio/assets/logo-small.png" alt="Padrinos Cali"
                     class="h-10 mx-auto mb-6 object-contain opacity-60"
                     onerror="this.style.display='none'">
                <div class="w-20 h-20 mx-auto mb-5 rounded-full flex items-center justify-center shadow-lg" style="background: rgba(65,184,83,0.1);">
                    <i data-lucide="check-circle" class="w-10 h-10" style="color: #41B853;"></i>
                </div>
                <h2 class="text-2xl font-bold" style="color: #5B2A86;">¡Asistencia Registrada!</h2>
                <p class="text-gray-600 mt-3">Gracias por registrar tu asistencia, <span class="font-semibold" style="color: #5B2A86;" x-text="form.nombre"></span>.</p>
                <p class="text-sm text-gray-500 mt-1">Tu firma digital ha sido guardada exitosamente.</p>
                <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-medium" style="background: rgba(91,42,134,0.08); color: #5B2A86;">
                    <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                    <?= htmlspecialchars($evento['nombre']) ?>
                </div>
            </div>

            <div x-show="error" class="mt-4 p-4 rounded-xl text-sm text-center" style="background: rgba(239,68,68,0.08); color: #dc2626;" x-text="mensajeError"></div>
        </div>
    </div>

    <script>
        function registroData() {
            return {
                form: {
                    evento_id: <?= $eventoId ?>,
                    nombre: '',
                    tipo_documento: 'cc',
                    documento: '',
                    telefono: '',
                    email: '',
                    genero: '',
                    fecha_nacimiento: '',
                    departamento: '',
                    municipio: '',
                    habeas_data: false,
                    acepta_comunicaciones: false,
                    autorizacion_imagenes: false,
                    firma: ''
                },
                registrado: false,
                cargando: false,
                error: false,
                mensajeError: '',
                datosAutocompletados: false,
                mensajeBienvenida: '',
                buscando: false,
                firmaCanvas: null,
                firmaCtx: null,
                firmaDibujando: false,

                async buscarDocumento() {
                    const doc = (this.form.documento || '').trim();
                    if (doc.length < 5) return;
                    if (this.datosAutocompletados && this.form.documento === this._docBuscado) return;

                    this.buscando = true;
                    try {
                        const res = await fetch(`/aratio/api/colaboradores.php?action=buscar_por_documento&documento=${encodeURIComponent(doc)}&evento_id=<?= $eventoId ?>`);
                        const result = await res.json();
                        if (result.encontrado && result.data) {
                            this.form.nombre = result.data.nombre || this.form.nombre;
                            this.form.tipo_documento = result.data.tipo_documento || this.form.tipo_documento;
                            this.form.telefono = result.data.telefono || '';
                            this.form.email = result.data.email || '';
                            this.form.genero = result.data.genero || '';
                            this.form.fecha_nacimiento = result.data.fecha_nacimiento || '';
                            this.form.departamento = result.data.departamento || '';
                            this.form.municipio = result.data.municipio || '';
                            this.datosAutocompletados = true;
                            this.mensajeBienvenida = result.mensaje_bienvenida || '¡Tus datos están en la plataforma! Solo firma para confirmar tu asistencia.';
                            this._docBuscado = doc;
                            this.$nextTick(() => {
                                const canvas = document.getElementById('signaturePad');
                                if (canvas) canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            });
                        } else if (this.datosAutocompletados) {
                            // Documento cambió y ya no coincide — permitir edición
                            this.datosAutocompletados = false;
                            this.mensajeBienvenida = '';
                            this._docBuscado = null;
                        }
                    } catch (e) {
                        // silencioso: si falla, usuario puede llenar manualmente
                    } finally {
                        this.buscando = false;
                    }
                },

                async registrar() {
                    if (!this.form.habeas_data) {
                        this.error = true;
                        this.mensajeError = 'Debes aceptar la política de tratamiento de datos';
                        return;
                    }

                    const sigData = this.firmaCanvas.toDataURL('image/png');
                    const emptyCanvas = document.createElement('canvas');
                    emptyCanvas.width = this.firmaCanvas.width;
                    emptyCanvas.height = this.firmaCanvas.height;
                    const emptyData = emptyCanvas.toDataURL('image/png');

                    if (sigData === emptyData) {
                        this.error = true;
                        this.mensajeError = 'Por favor, dibuje su firma digital';
                        return;
                    }

                    this.form.firma = sigData;
                    this.cargando = true;
                    this.error = false;
                    this.mensajeError = '';

                    try {
                        const response = await fetch('/aratio/mod_eventos/api/asistencia.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.form)
                        });
                        const result = await response.json();

                        if (result.success) {
                            this.registrado = true;
                        } else {
                            this.error = true;
                            this.mensajeError = result.message || 'Error al registrar';
                        }
                    } catch (e) {
                        this.error = true;
                        this.mensajeError = 'Error de conexión';
                    } finally {
                        this.cargando = false;
                    }
                },

                initFirma() {
                    this.$nextTick(() => {
                        this.firmaCanvas = document.getElementById('signaturePad');
                        if (!this.firmaCanvas) return;
                        this.firmaCtx = this.firmaCanvas.getContext('2d');
                        this.firmaCtx.lineWidth = 2.5;
                        this.firmaCtx.lineCap = 'round';
                        this.firmaCtx.lineJoin = 'round';
                        this.firmaCtx.strokeStyle = '#2F2F35';

                        this.firmaCanvas.addEventListener('mousedown', (e) => this.startDibujo(e));
                        this.firmaCanvas.addEventListener('mousemove', (e) => this.dibujar(e));
                        this.firmaCanvas.addEventListener('mouseup', () => this.pararDibujo());
                        this.firmaCanvas.addEventListener('mouseleave', () => this.pararDibujo());

                        this.firmaCanvas.addEventListener('touchstart', (e) => {
                            e.preventDefault();
                            this.firmaCanvas.classList.add('active');
                            this.startDibujo(e.touches[0]);
                        }, { passive: false });
                        this.firmaCanvas.addEventListener('touchmove', (e) => {
                            e.preventDefault();
                            this.dibujar(e.touches[0]);
                        }, { passive: false });
                        this.firmaCanvas.addEventListener('touchend', () => {
                            this.firmaCanvas.classList.remove('active');
                            this.pararDibujo();
                        });
                    });
                },

                startDibujo(e) {
                    this.firmaDibujando = true;
                    const rect = this.firmaCanvas.getBoundingClientRect();
                    const x = (e.clientX - rect.left) * (this.firmaCanvas.width / rect.width);
                    const y = (e.clientY - rect.top) * (this.firmaCanvas.height / rect.height);
                    this.firmaCtx.beginPath();
                    this.firmaCtx.moveTo(x, y);
                },

                dibujar(e) {
                    if (!this.firmaDibujando) return;
                    const rect = this.firmaCanvas.getBoundingClientRect();
                    const x = (e.clientX - rect.left) * (this.firmaCanvas.width / rect.width);
                    const y = (e.clientY - rect.top) * (this.firmaCanvas.height / rect.height);
                    this.firmaCtx.lineTo(x, y);
                    this.firmaCtx.stroke();
                },

                pararDibujo() {
                    this.firmaDibujando = false;
                },

                limpiarFirma() {
                    this.firmaCtx.clearRect(0, 0, this.firmaCanvas.width, this.firmaCanvas.height);
                    this.form.firma = '';
                }
            }
        }
        lucide.createIcons();
    </script>
</body>
</html>
