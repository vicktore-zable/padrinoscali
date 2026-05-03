<?php
/**
 * Vista de Edición de Curriculum
 */

use App\Utils\Security;

$colaborador = $colaborador ?? [];
$curriculum = $curriculum ?? [];
$old = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);

<?php
$pageTitle = 'Editar Curriculum';
$pageDescription = htmlspecialchars(\App\Utils\Helpers::formatFullName($colaborador['nombres'], $colaborador['apellidos'])) . ' - ' . htmlspecialchars($colaborador['documento']);
?>

<?php ob_start(); ?>
<script>
    function curriculumManager() {
        return {
            showExperienciaModal: false,
            showFormacionModal: false,
            showParticipacionModal: false,

            experienciaForm: {
                cargo: '',
                empresa: '',
                fecha_inicio: '',
                fecha_fin: '',
                descripcion: '',
                actual: false
            },

            formacionForm: {
                titulo: '',
                institucion: '',
                nivel: '',
                fecha_inicio: '',
                fecha_fin: '',
                en_curso: false
            },

            participacionForm: {
                tipo: '',
                cargo: '',
                organizacion: '',
                ambito: '',
                poblacion_beneficiada: '',
                fecha_inicio: '',
                fecha_fin: '',
                logros: '',
                redes_alianzas: '',
                descripcion: '',
                actual: false
            },

            openExperienciaModal() {
                this.experienciaForm = {
                    cargo: '',
                    empresa: '',
                    fecha_inicio: '',
                    fecha_fin: '',
                    descripcion: '',
                    actual: false
                };
                this.showExperienciaModal = true;
            },

            openFormacionModal() {
                this.formacionForm = {
                    titulo: '',
                    institucion: '',
                    nivel: '',
                    fecha_inicio: '',
                    fecha_fin: '',
                    en_curso: false
                };
                this.showFormacionModal = true;
            },

            openParticipacionModal() {
                this.participacionForm = {
                    tipo: '',
                    cargo: '',
                    organizacion: '',
                    ambito: '',
                    poblacion_beneficiada: '',
                    fecha_inicio: '',
                    fecha_fin: '',
                    logros: '',
                    redes_alianzas: '',
                    descripcion: '',
                    actual: false
                };
                this.showParticipacionModal = true;
            },

            submitExperiencia() {
                fetch('/curriculum/<?= $colaborador['id'] ?>/experiencia', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    },
                    body: JSON.stringify(this.experienciaForm)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo agregar la experiencia'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al agregar experiencia');
                });
            },

            submitFormacion() {
                fetch('/curriculum/<?= $colaborador['id'] ?>/formacion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    },
                    body: JSON.stringify(this.formacionForm)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo agregar la formación'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al agregar formación');
                });
            },

            submitParticipacion() {
                fetch('/curriculum/<?= $colaborador['id'] ?>/participacion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    },
                    body: JSON.stringify(this.participacionForm)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo agregar la participación'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al agregar participación');
                });
            },

            deleteExperiencia(index) {
                if (!confirm('¿Eliminar esta experiencia laboral?')) return;

                fetch(`/curriculum/<?= $colaborador['id'] ?>/experiencia/${index}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar experiencia');
                });
            },

            deleteFormacion(index) {
                if (!confirm('¿Eliminar esta formación académica?')) return;

                fetch(`/curriculum/<?= $colaborador['id'] ?>/formacion/${index}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar formación');
                });
            },

            deleteParticipacion(index) {
                if (!confirm('¿Eliminar esta participación política?')) return;

                fetch(`/curriculum/<?= $colaborador['id'] ?>/participacion/${index}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar participación');
                });
            }
        }
    }
</script>
<?php $scripts = ob_get_clean(); ?>

<!-- Alpine.js Curriculum Manager Wrapper -->
<div x-data="curriculumManager()" @curriculum-updated.window="location.reload()">
    <!-- Action Buttons -->
    <div class="mb-6 flex justify-end">
        <a href="/curriculum/<?= $colaborador['id'] ?>" class="btn btn-secondary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Cancelar
        </a>
    </div>

    <!-- Información General -->
    <form action="/curriculum/<?= $colaborador['id'] ?>/update" method="POST" class="space-y-6">
        <?= Security::csrfField() ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Información General</h2>
            </div>
            <div class="card-body space-y-4">
                <!-- Resumen Profesional -->
                <div>
                    <label for="resumen_profesional" class="block text-sm font-medium text-gray-700 mb-2">
                        Resumen Profesional
                    </label>
                    <textarea id="resumen_profesional"
                              name="resumen_profesional"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Breve descripción de tu perfil profesional..."><?= htmlspecialchars($old['resumen_profesional'] ?? $curriculum['resumen_profesional'] ?? '') ?></textarea>
                </div>

                <!-- Habilidades -->
                <div>
                    <label for="habilidades" class="block text-sm font-medium text-gray-700 mb-2">
                        Habilidades
                    </label>
                    <textarea id="habilidades"
                              name="habilidades"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Liderazgo, comunicación, análisis de datos..."><?= htmlspecialchars($old['habilidades'] ?? $curriculum['habilidades'] ?? '') ?></textarea>
                </div>

                <!-- Idiomas -->
                <div>
                    <label for="idiomas" class="block text-sm font-medium text-gray-700 mb-2">
                        Idiomas
                    </label>
                    <textarea id="idiomas"
                              name="idiomas"
                              rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Español (nativo), Inglés (intermedio)..."><?= htmlspecialchars($old['idiomas'] ?? $curriculum['idiomas'] ?? '') ?></textarea>
                </div>

                <!-- Reconocimientos -->
                <div>
                    <label for="reconocimientos" class="block text-sm font-medium text-gray-700 mb-2">
                        Reconocimientos y Premios
                    </label>
                    <textarea id="reconocimientos"
                              name="reconocimientos"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Certificaciones, premios, logros destacados..."><?= htmlspecialchars($old['reconocimientos'] ?? $curriculum['reconocimientos'] ?? '') ?></textarea>
                </div>

                <!-- Referencias -->
                <div>
                    <label for="referencias" class="block text-sm font-medium text-gray-700 mb-2">
                        Referencias
                    </label>
                    <textarea id="referencias"
                              name="referencias"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Contactos de referencias profesionales..."><?= htmlspecialchars($old['referencias'] ?? $curriculum['referencias'] ?? '') ?></textarea>
                </div>

                <!-- Observaciones -->
                <div>
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                        Observaciones
                    </label>
                    <textarea id="observaciones"
                              name="observaciones"
                              rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Notas adicionales..."><?= htmlspecialchars($old['observaciones'] ?? $curriculum['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Botón Guardar -->
        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar Cambios
            </button>
        </div>
    </form>

    <!-- Experiencia Laboral -->
    <div class="card mt-6">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h2 class="card-title">Experiencia Laboral</h2>
                <button @click="openExperienciaModal()" class="btn btn-sm btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($curriculum['experiencia_laboral'])): ?>
                <div class="space-y-4">
                    <?php foreach ($curriculum['experiencia_laboral'] as $index => $exp): ?>
                        <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($exp['cargo'] ?? '') ?></h4>
                                <p class="text-sm text-gray-700"><?= htmlspecialchars($exp['empresa'] ?? '') ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= date('m/Y', strtotime($exp['fecha_inicio'])) ?> -
                                    <?= isset($exp['actual']) && $exp['actual'] ? 'Actualidad' : date('m/Y', strtotime($exp['fecha_fin'])) ?>
                                </p>
                            </div>
                            <button @click="deleteExperiencia(<?= $index ?>)"
                                    class="ml-4 text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No hay experiencia laboral registrada</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formación Académica -->
    <div class="card mt-6">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h2 class="card-title">Formación Académica</h2>
                <button @click="openFormacionModal()" class="btn btn-sm btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($curriculum['formacion_academica'])): ?>
                <div class="space-y-4">
                    <?php foreach ($curriculum['formacion_academica'] as $index => $form): ?>
                        <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($form['titulo'] ?? '') ?></h4>
                                <p class="text-sm text-gray-700"><?= htmlspecialchars($form['institucion'] ?? '') ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= htmlspecialchars($form['nivel'] ?? '') ?> |
                                    <?= date('m/Y', strtotime($form['fecha_inicio'])) ?> -
                                    <?= isset($form['en_curso']) && $form['en_curso'] ? 'En curso' : date('m/Y', strtotime($form['fecha_fin'])) ?>
                                </p>
                            </div>
                            <button @click="deleteFormacion(<?= $index ?>)"
                                    class="ml-4 text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No hay formación académica registrada</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Participación Política -->
    <div class="card mt-6">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h2 class="card-title">Participación Política</h2>
                <button @click="openParticipacionModal()" class="btn btn-sm btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($curriculum['participacion_politica'])): ?>
                <div class="space-y-4">
                    <?php foreach ($curriculum['participacion_politica'] as $index => $part): ?>
                        <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($part['cargo'] ?? '') ?></h4>
                                <p class="text-sm text-gray-700"><?= htmlspecialchars($part['organizacion'] ?? '') ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= date('m/Y', strtotime($part['fecha_inicio'])) ?> -
                                    <?= isset($part['actual']) && $part['actual'] ? 'Actualidad' : date('m/Y', strtotime($part['fecha_fin'])) ?>
                                </p>
                            </div>
                            <button @click="deleteParticipacion(<?= $index ?>)"
                                    class="ml-4 text-red-600 hover:text-red-900">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No hay participación política registrada</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Experiencia Laboral -->
    <div x-show="showExperienciaModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showExperienciaModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showExperienciaModal = false"></div>

            <div class="relative bg-white rounded-lg max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Agregar Experiencia Laboral</h3>

                <form @submit.prevent="submitExperiencia()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Cargo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="experienciaForm.cargo" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Empresa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="experienciaForm.empresa" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" x-model="experienciaForm.fecha_inicio" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Fin
                                </label>
                                <input type="date" x-model="experienciaForm.fecha_fin"
                                       :disabled="experienciaForm.actual"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="experienciaForm.actual"
                                       class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Trabajo actual</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea x-model="experienciaForm.descripcion" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showExperienciaModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Formación Académica -->
    <div x-show="showFormacionModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showFormacionModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showFormacionModal = false"></div>

            <div class="relative bg-white rounded-lg max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Agregar Formación Académica</h3>

                <form @submit.prevent="submitFormacion()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Título <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="formacionForm.titulo" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Institución <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="formacionForm.institucion" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nivel <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formacionForm.nivel" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Seleccione...</option>
                                <option value="Bachillerato">Bachillerato</option>
                                <option value="Técnico">Técnico</option>
                                <option value="Tecnólogo">Tecnólogo</option>
                                <option value="Pregrado">Pregrado</option>
                                <option value="Especialización">Especialización</option>
                                <option value="Maestría">Maestría</option>
                                <option value="Doctorado">Doctorado</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" x-model="formacionForm.fecha_inicio" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Fin
                                </label>
                                <input type="date" x-model="formacionForm.fecha_fin"
                                       :disabled="formacionForm.en_curso"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="formacionForm.en_curso"
                                       class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">En curso</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showFormacionModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Participación Política -->
    <div x-show="showParticipacionModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showParticipacionModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showParticipacionModal = false"></div>

            <div class="relative bg-white rounded-lg max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Agregar Participación Política y Social</h3>

                <form @submit.prevent="submitParticipacion()">
                    <div class="space-y-4">
                        <!-- Tipo de Participación -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tipo de Participación <span class="text-red-500">*</span>
                            </label>
                            <select x-model="participacionForm.tipo" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Seleccione...</option>
                                <option value="Partido Político">Partido Político</option>
                                <option value="Movimiento Social">Movimiento Social</option>
                                <option value="ONG / Fundación">ONG / Fundación</option>
                                <option value="Sindicato">Sindicato</option>
                                <option value="Junta de Acción Comunal">Junta de Acción Comunal</option>
                                <option value="Asociación Comunitaria">Asociación Comunitaria</option>
                                <option value="Grupo Estudiantil">Grupo Estudiantil</option>
                                <option value="Colectivo Cultural">Colectivo Cultural</option>
                                <option value="Organización Ambiental">Organización Ambiental</option>
                                <option value="Grupo Religioso">Grupo Religioso</option>
                                <option value="Cooperativa">Cooperativa</option>
                                <option value="Organización Deportiva">Organización Deportiva</option>
                                <option value="Grupo de Mujeres">Grupo de Mujeres</option>
                                <option value="Organización Juvenil">Organización Juvenil</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Cargo / Rol <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="participacionForm.cargo" required
                                       placeholder="Ej: Líder, Miembro, Coordinador, Voluntario"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Organización <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="participacionForm.organizacion" required
                                       placeholder="Nombre de la organización"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <!-- Ámbito de Acción -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Ámbito de Acción
                                </label>
                                <select x-model="participacionForm.ambito"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Seleccione...</option>
                                    <option value="Local">Local (Barrio / Vereda)</option>
                                    <option value="Municipal">Municipal</option>
                                    <option value="Departamental">Departamental</option>
                                    <option value="Nacional">Nacional</option>
                                    <option value="Internacional">Internacional</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Población Beneficiada (aprox.)
                                </label>
                                <input type="number" x-model="participacionForm.poblacion_beneficiada"
                                       placeholder="Número aproximado de personas"
                                       min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" x-model="participacionForm.fecha_inicio" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Fin
                                </label>
                                <input type="date" x-model="participacionForm.fecha_fin"
                                       :disabled="participacionForm.actual"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="participacionForm.actual"
                                       class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Participación actual</span>
                            </label>
                        </div>

                        <!-- Logros y Actividades -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Logros y Actividades Destacadas
                            </label>
                            <textarea x-model="participacionForm.logros" rows="2"
                                      placeholder="Campañas, proyectos, iniciativas exitosas..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>

                        <!-- Redes y Alianzas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Redes y Alianzas
                            </label>
                            <textarea x-model="participacionForm.redes_alianzas" rows="2"
                                      placeholder="Organizaciones aliadas, redes con las que colabora..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>

                        <!-- Descripción General -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción de Funciones y Responsabilidades
                            </label>
                            <textarea x-model="participacionForm.descripcion" rows="3"
                                      placeholder="Detalle sus funciones, responsabilidades y contribuciones..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showParticipacionModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    function curriculumManager() {
        return {
            showExperienciaModal: false,
            showFormacionModal: false,
            showParticipacionModal: false,

            experienciaForm: {
                cargo: '',
                empresa: '',
                fecha_inicio: '',
                fecha_fin: '',
                descripcion: '',
                actual: false
            },

            formacionForm: {
                titulo: '',
                institucion: '',
                nivel: '',
                fecha_inicio: '',
                fecha_fin: '',
                en_curso: false
            },

            participacionForm: {
                tipo: '',
                cargo: '',
                organizacion: '',
                ambito: '',
                poblacion_beneficiada: '',
                fecha_inicio: '',
                fecha_fin: '',
                logros: '',
                redes_alianzas: '',
                descripcion: '',
                actual: false
            },

            openExperienciaModal() {
                this.experienciaForm = {
                    cargo: '',
                    empresa: '',
                    fecha_inicio: '',
                    fecha_fin: '',
                    descripcion: '',
                    actual: false
                };
                this.showExperienciaModal = true;
            },

            openFormacionModal() {
                this.formacionForm = {
                    titulo: '',
                    institucion: '',
                    nivel: '',
                    fecha_inicio: '',
                    fecha_fin: '',
                    en_curso: false
                };
                this.showFormacionModal = true;
            },

            openParticipacionModal() {
                this.participacionForm = {
                    tipo: '',
                    cargo: '',
                    organizacion: '',
                    ambito: '',
                    poblacion_beneficiada: '',
                    fecha_inicio: '',
                    fecha_fin: '',
                    logros: '',
                    redes_alianzas: '',
                    descripcion: '',
                    actual: false
                };
                this.showParticipacionModal = true;
            },

            submitExperiencia() {
                fetch('/curriculum/<?= $colaborador['id'] ?>/experiencia', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    },
                    body: JSON.stringify(this.experienciaForm)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo agregar la experiencia'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al agregar experiencia');
                });
            },

            submitFormacion() {
                fetch('/curriculum/<?= $colaborador['id'] ?>/formacion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    },
                    body: JSON.stringify(this.formacionForm)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo agregar la formación'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al agregar formación');
                });
            },

            submitParticipacion() {
                fetch('/curriculum/<?= $colaborador['id'] ?>/participacion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    },
                    body: JSON.stringify(this.participacionForm)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo agregar la participación'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al agregar participación');
                });
            },

            deleteExperiencia(index) {
                if (!confirm('¿Eliminar esta experiencia laboral?')) return;

                fetch(`/curriculum/<?= $colaborador['id'] ?>/experiencia/${index}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar experiencia');
                });
            },

            deleteFormacion(index) {
                if (!confirm('¿Eliminar esta formación académica?')) return;

                fetch(`/curriculum/<?= $colaborador['id'] ?>/formacion/${index}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar formación');
                });
            },

            deleteParticipacion(index) {
                if (!confirm('¿Eliminar esta participación política?')) return;

                fetch(`/curriculum/<?= $colaborador['id'] ?>/participacion/${index}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= Security::generateCsrfToken() ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar participación');
                });
            }
        }
    }
</script>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
</div>
<!-- End Alpine.js Curriculum Manager Wrapper -->

        </main>
