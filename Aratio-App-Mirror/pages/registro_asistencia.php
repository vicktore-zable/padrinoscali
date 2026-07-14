<?php
/**
 * PÁGINA PÚBLICA: Registro de Asistencia a Eventos
 * Accesible vía código QR sin autenticación
 */

require_once 'config/config.php';

$db = getDB();
$error = null;
$success = false;
$evento = null;
$cerrado = false;

// Obtener ID del evento desde QR
$eventoId = $_GET['evento'] ?? null;

if (!$eventoId) {
    $error = 'No se especificó un evento válido';
} else {
    // Cargar información del evento
    try {
        $stmt = $db->prepare("
            SELECT e.*, c.nombre as campana_nombre, c.color_primario, c.color_secundario
            FROM eventos e
            LEFT JOIN campanas c ON e.campana_id = c.id
            WHERE e.id = ?
        ");
        $stmt->execute([$eventoId]);
        $evento = $stmt->fetch();

        if (!$evento) {
            $error = 'Evento no encontrado';
        } else {
            // Verificar si el evento está cerrado (fecha_fin + 24 horas)
            $fechaCierre = new DateTime($evento['fecha_fin'] ?? $evento['fecha_inicio']);
            $fechaCierre->modify('+24 hours');
            $ahora = new DateTime();

            if ($ahora > $fechaCierre) {
                $cerrado = true;
                $error = 'El registro de asistencia para este evento ha sido cerrado (24 horas después de finalización)';
            }
        }
    } catch (Exception $e) {
        error_log("Error loading evento: " . $e->getMessage());
        $error = 'Error al cargar el evento';
    }
}

// Procesar registro (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$cerrado && $evento) {
    // La validación y el guardado se hará por AJAX en la API
}

$colorPrimario = $evento['color_primario'] ?? '#FF00FF';
$colorSecundario = $evento['color_secundario'] ?? '#FFD700';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia - <?= htmlspecialchars($evento['nombre'] ?? 'Evento') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --color-primary: <?= $colorPrimario ?>;
            --color-secondary: <?= $colorSecundario ?>;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        }
        .signature-pad {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            touch-action: none;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-3xl" x-data="registroData()">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                     style="background: linear-gradient(135deg, <?= $colorPrimario ?>, <?= $colorSecundario ?>)">
                    <i data-lucide="calendar-check" class="w-8 h-8 text-white"></i>
                </div>
                <?php if ($evento): ?>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        <?= htmlspecialchars($evento['nombre']) ?>
                    </h1>
                    <p class="text-gray-600 mb-4"><?= htmlspecialchars($evento['campana_nombre']) ?></p>
                    <div class="flex items-center justify-center gap-6 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <?= formatDate($evento['fecha_inicio'], 'd/m/Y H:i') ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            <?= htmlspecialchars($evento['ubicacion']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($error): ?>
            <!-- Error Message -->
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg mb-6">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>
                    <div>
                        <h3 class="font-bold text-red-900">Error</h3>
                        <p class="text-red-700"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            </div>
        <?php elseif ($cerrado): ?>
            <!-- Closed Message -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg">
                <div class="flex items-center gap-3">
                    <i data-lucide="lock" class="w-6 h-6 text-yellow-600"></i>
                    <div>
                        <h3 class="font-bold text-yellow-900">Registro Cerrado</h3>
                        <p class="text-yellow-700">El plazo para registrar asistencia a este evento ha finalizado.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Registration Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Registro de Asistencia</h2>

                <form @submit.prevent="submitForm()" class="space-y-6">
                    <!-- Información Personal -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="user" class="w-5 h-5"></i>
                            Información Personal
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo *</label>
                                <input type="text" x-model="form.nombre" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Documento *</label>
                                <select x-model="form.tipo_documento" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option value="cc">Cédula de Ciudadanía</option>
                                    <option value="ce">Cédula de Extranjería</option>
                                    <option value="ti">Tarjeta de Identidad</option>
                                    <option value="pasaporte">Pasaporte</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Número de Documento *</label>
                                <input type="text" x-model="form.documento" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                <input type="tel" x-model="form.telefono"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" x-model="form.email"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>
                    </div>

                    <!-- Datos Demográficos -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            Datos Demográficos
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Nacimiento</label>
                                <input type="date" x-model="form.fecha_nacimiento" @change="calcularEdad()"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Edad</label>
                                <input type="number" x-model="form.edad" min="0" max="120" readonly
                                       class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Género *</label>
                                <select x-model="form.genero" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option value="">Seleccionar...</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="femenino">Femenino</option>
                                    <option value="otro">Otro</option>
                                    <option value="prefiero-no-decir">Prefiero no decir</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                            ¿Dónde Vive?
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departamento *</label>
                                <input type="text" x-model="form.departamento" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                       placeholder="Ej: Cundinamarca">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Municipio *</label>
                                <input type="text" x-model="form.municipio" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                       placeholder="Ej: Bogotá">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Territorio</label>
                                <select x-model="form.tipo_territorio"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option value="">Seleccionar...</option>
                                    <option value="Localidad">Localidad</option>
                                    <option value="Comuna">Comuna</option>
                                    <option value="Corregimiento">Corregimiento</option>
                                    <option value="Zona">Zona</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Territorio</label>
                                <input type="text" x-model="form.territorio"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                       placeholder="Ej: Localidad 5 - Usme">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Barrio/Vereda</label>
                                <input type="text" x-model="form.barrio"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                       placeholder="Ej: La Aurora">
                            </div>
                        </div>
                    </div>

                    <!-- Áreas de Interés -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="heart" class="w-5 h-5"></i>
                            Áreas de Interés
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Educación" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Educación</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Salud" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Salud</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Seguridad" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Seguridad</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Empleo" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Empleo</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Infraestructura" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Infraestructura</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Cultura" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Cultura</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Deporte" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Deporte</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Medio Ambiente" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Medio Ambiente</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Vivienda" x-model="form.areas_interes"
                                       class="rounded text-purple-600 focus:ring-purple-500">
                                <span class="text-sm">Vivienda</span>
                            </label>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="message-square" class="w-5 h-5"></i>
                            Observaciones
                        </h3>
                        <textarea x-model="form.observaciones" rows="3"
                                  placeholder="Comparta su opinión, sugerencias o comentarios sobre el evento..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                        <p class="text-xs text-gray-500">Sus comentarios nos ayudan a mejorar nuestros eventos</p>
                    </div>

                    <!-- Firma Digital -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="pen-tool" class="w-5 h-5"></i>
                            Firma Digital *
                        </h3>
                        <div class="relative">
                            <canvas id="signaturePad" width="600" height="200"
                                    class="signature-pad w-full bg-white cursor-crosshair"></canvas>
                            <button type="button" @click="clearSignature()"
                                    class="absolute top-2 right-2 px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-xs font-medium">
                                <i data-lucide="x" class="w-3 h-3 inline"></i> Limpiar
                            </button>
                        </div>
                        <p class="text-xs text-gray-500">Dibuje su firma en el recuadro usando el mouse o el dedo (táctil)</p>
                    </div>

                    <!-- Habeas Data y Consentimientos -->
                    <div class="space-y-3 pt-4 border-t">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            Consentimiento y Privacidad
                        </h3>
                        <label class="flex items-start gap-3 cursor-pointer p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <input type="checkbox" x-model="form.habeas_data" required
                                   class="mt-1 rounded text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-gray-700">
                                <strong class="text-red-500">*</strong> Acepto el tratamiento de mis datos personales de acuerdo con la
                                <a href="#" class="text-purple-600 hover:underline font-medium">Política de Privacidad</a> y
                                la Ley 1581 de 2012 de Protección de Datos Personales.
                            </span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <input type="checkbox" x-model="form.acepta_comunicaciones"
                                   class="mt-1 rounded text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-gray-700">
                                Acepto recibir información y comunicaciones sobre la campaña, eventos y propuestas políticas.
                            </span>
                        </label>
                        <p class="text-xs text-gray-500">
                            Sus datos serán tratados con total confidencialidad y solo serán utilizados para fines relacionados con esta campaña política.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" :disabled="loading"
                                class="w-full btn-primary text-white font-bold py-3 px-6 rounded-lg hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">
                                <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i>
                                Registrar Asistencia
                            </span>
                            <span x-show="loading">
                                <i data-lucide="loader-2" class="w-5 h-5 inline mr-2 animate-spin"></i>
                                Guardando...
                            </span>
                        </button>
                    </div>

                    <!-- Message -->
                    <div x-show="message" :class="messageType === 'success' ? 'bg-green-50 border-green-500 text-green-800' : 'bg-red-50 border-red-500 text-red-800'"
                         class="border-l-4 p-4 rounded" style="display: none;">
                        <p x-text="message"></p>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-600 text-sm">
            <p>&copy; <?= date('Y') ?> Aratio - Sistema de Gestión Electoral</p>
        </div>
    </div>

    <script>
    function registroData() {
        return {
            loading: false,
            message: '',
            messageType: 'success',
            canvas: null,
            ctx: null,
            isDrawing: false,
            form: {
                evento_id: <?= $eventoId ?>,
                nombre: '',
                documento: '',
                tipo_documento: 'cc',
                telefono: '',
                email: '',
                fecha_nacimiento: '',
                edad: '',
                genero: '',
                departamento: '',
                municipio: '',
                tipo_territorio: '',
                territorio: '',
                barrio: '',
                areas_interes: [],
                observaciones: '',
                firma: '',
                habeas_data: false,
                acepta_comunicaciones: false
            },

            init() {
                this.initSignaturePad();
                lucide.createIcons();
            },

            initSignaturePad() {
                this.canvas = document.getElementById('signaturePad');
                this.ctx = this.canvas.getContext('2d');
                this.ctx.lineWidth = 2;
                this.ctx.lineCap = 'round';
                this.ctx.strokeStyle = '#000';

                // Mouse events
                this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
                this.canvas.addEventListener('mousemove', (e) => this.draw(e));
                this.canvas.addEventListener('mouseup', () => this.stopDrawing());
                this.canvas.addEventListener('mouseout', () => this.stopDrawing());

                // Touch events
                this.canvas.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    this.startDrawing(e.touches[0]);
                });
                this.canvas.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    this.draw(e.touches[0]);
                });
                this.canvas.addEventListener('touchend', () => this.stopDrawing());
            },

            startDrawing(e) {
                this.isDrawing = true;
                const rect = this.canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                this.ctx.beginPath();
                this.ctx.moveTo(x, y);
            },

            draw(e) {
                if (!this.isDrawing) return;
                const rect = this.canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                this.ctx.lineTo(x, y);
                this.ctx.stroke();
            },

            stopDrawing() {
                this.isDrawing = false;
            },

            clearSignature() {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            },

            calcularEdad() {
                if (!this.form.fecha_nacimiento) {
                    this.form.edad = '';
                    return;
                }
                const hoy = new Date();
                const nacimiento = new Date(this.form.fecha_nacimiento);
                let edad = hoy.getFullYear() - nacimiento.getFullYear();
                const mes = hoy.getMonth() - nacimiento.getMonth();
                if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                    edad--;
                }
                this.form.edad = edad;
            },

            async submitForm() {
                // Validar firma
                const signatureData = this.canvas.toDataURL('image/png');
                if (signatureData === this.canvas.toDataURL('image/png', 0)) {
                    this.message = 'Por favor, agregue su firma';
                    this.messageType = 'error';
                    return;
                }

                this.form.firma = signatureData;
                this.loading = true;
                this.message = '';

                try {
                    const response = await fetch('/aratio/api/asistencia_eventos.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.message = '¡Registro exitoso! Gracias por su asistencia.';
                        this.messageType = 'success';
                        // Limpiar formulario después de 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        this.message = 'Error: ' + result.message;
                        this.messageType = 'error';
                    }
                } catch (error) {
                    this.message = 'Error de conexión. Por favor intente nuevamente.';
                    this.messageType = 'error';
                }

                this.loading = false;
            }
        }
    }
    </script>
</body>
</html>
