<!-- Página de Confirmación de Inscripción -->
<div class="max-w-2xl mx-auto">

    <div class="card text-center">
        <div class="card-body py-12">
            <!-- Icono de éxito -->
            <div class="w-24 h-24 mx-auto mb-6 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <!-- Título -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                ¡Inscripción Exitosa!
            </h1>

            <!-- Mensaje de bienvenida -->
            <p class="text-xl text-gray-600 mb-6">
                Bienvenido/a, <strong class="text-primary-700"><?= htmlspecialchars($inscripcion['nombre'] ?? 'Colaborador') ?></strong>
            </p>

            <!-- Detalles -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8 max-w-md mx-auto">
                <p class="text-gray-600 mb-2">
                    <span class="font-medium">Número de documento:</span>
                    <span class="text-gray-900"><?= htmlspecialchars($inscripcion['documento'] ?? '') ?></span>
                </p>
                <p class="text-gray-600 mb-2">
                    <span class="font-medium">Número de registro:</span>
                    <span class="text-gray-900">#<?= htmlspecialchars($inscripcion['id'] ?? '') ?></span>
                </p>
                <?php if (!empty($inscripcion['tiene_curriculum'])): ?>
                <p class="text-green-600 mt-3 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Curriculum registrado correctamente
                </p>
                <?php endif; ?>
            </div>

            <!-- Resumen de lo registrado -->
            <div class="grid grid-cols-2 gap-4 mb-8 max-w-md mx-auto text-left">
                <div class="bg-primary-50 rounded-lg p-4">
                    <div class="text-primary-700 font-semibold text-sm">Datos Personales</div>
                    <div class="text-primary-900 text-lg font-bold">Completos</div>
                </div>
                <div class="<?= !empty($inscripcion['tiene_curriculum']) ? 'bg-green-50' : 'bg-gray-100' ?> rounded-lg p-4">
                    <div class="<?= !empty($inscripcion['tiene_curriculum']) ? 'text-green-700' : 'text-gray-500' ?> font-semibold text-sm">Curriculum</div>
                    <div class="<?= !empty($inscripcion['tiene_curriculum']) ? 'text-green-900' : 'text-gray-600' ?> text-lg font-bold">
                        <?= !empty($inscripcion['tiene_curriculum']) ? 'Registrado' : 'No agregado' ?>
                    </div>
                </div>
            </div>

            <!-- Mensaje informativo -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-left">
                        <p class="text-blue-800 font-medium mb-2">¿Qué sigue?</p>
                        <ul class="text-blue-700 text-sm space-y-1">
                            <li>• Su información ha sido registrada en nuestro sistema</li>
                            <li>• Un coordinador se pondrá en contacto con usted pronto</li>
                            <li>• Puede participar en las actividades y eventos programados</li>
                            <?php if (!empty($inscripcion['tiene_curriculum'])): ?>
                            <li>• Su hoja de vida está disponible para consulta</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/inscripcion" class="btn btn-primary px-8 py-3">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Registrar Otro Colaborador
                </a>
                <a href="/" class="btn btn-secondary px-8 py-3">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Ir al Inicio
                </a>
            </div>
        </div>
    </div>

    <!-- Información de contacto -->
    <div class="mt-8 text-center text-gray-500 text-sm">
        <p>¿Tiene preguntas? Contáctenos:</p>
        <p class="mt-1">
            <a href="mailto:contacto@ejemplo.com" class="text-primary-600 hover:underline">contacto@ejemplo.com</a>
        </p>
    </div>

</div>
