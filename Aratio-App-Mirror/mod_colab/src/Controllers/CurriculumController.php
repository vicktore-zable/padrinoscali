<?php
/**
 * Controlador de Curriculum
 * Maneja la gestión de curriculum vitae de colaboradores
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Curriculum;
use App\Models\Colaborador;
use App\Utils\Validator;

class CurriculumController extends Controller {
    private Curriculum $curriculumModel;
    private Colaborador $colaboradorModel;

    public function __construct() {
        parent::__construct();
        $this->curriculumModel = new Curriculum();
        $this->colaboradorModel = new Colaborador();
    }

    /**
     * Mostrar curriculum de un colaborador
     */
    public function show(string $colaboradorId): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_ver');

        // Obtener colaborador
        $colaborador = $this->colaboradorModel->getById((int)$colaboradorId);
        if (!$colaborador) {
            $this->setFlash('Colaborador no encontrado', 'error');
            $this->redirect('/colaboradores');
            return;
        }

        // Obtener o crear curriculum
        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        // Si no existe curriculum, crear uno vacío
        if (!$curriculum) {
            try {
                $curriculumId = $this->curriculumModel->create([
                    'colaborador_id' => $colaboradorId,
                    'experiencia_laboral' => [],
                    'formacion_academica' => [],
                    'participacion_politica' => []
                ]);

                $curriculum = $this->curriculumModel->getById($curriculumId);
            } catch (\Exception $e) {
                $this->setFlash('Error al crear curriculum: ' . $e->getMessage(), 'error');
                $this->redirect('/colaboradores');
                return;
            }
        }

        $this->view('curriculum/show', [
            'colaborador' => $colaborador,
            'curriculum' => $curriculum,
            'title' => 'Curriculum - ' . $colaborador['nombres'] . ' ' . $colaborador['apellidos'],
            'pageTitle' => 'Curriculum Vitae',
            'pageDescription' => 'Documento: ' . $colaborador['documento'] . ' | Email: ' . ($colaborador['email'] ?? 'Sin email')
        ]);
    }

    /**
     * Mostrar formulario de edición de curriculum
     */
    public function edit(string $colaboradorId): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        // Obtener colaborador
        $colaborador = $this->colaboradorModel->getById((int)$colaboradorId);
        if (!$colaborador) {
            $this->setFlash('Colaborador no encontrado', 'error');
            $this->redirect('/colaboradores');
            return;
        }

        // Obtener o crear curriculum
        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        // Si no existe curriculum, crear uno vacío
        if (!$curriculum) {
            try {
                $curriculumId = $this->curriculumModel->create([
                    'colaborador_id' => $colaboradorId,
                    'experiencia_laboral' => [],
                    'formacion_academica' => [],
                    'participacion_politica' => []
                ]);

                $curriculum = $this->curriculumModel->getById($curriculumId);
            } catch (\Exception $e) {
                $this->setFlash('Error al crear curriculum: ' . $e->getMessage(), 'error');
                $this->redirect('/colaboradores/' . $colaboradorId);
                return;
            }
        }

        $this->view('curriculum/edit_v2', [
            'colaborador' => $colaborador,
            'curriculum' => $curriculum,
            'title' => 'Editar Curriculum - ' . $colaborador['nombres'] . ' ' . $colaborador['apellidos'],
            'pageTitle' => 'Editar Curriculum',
            'pageDescription' => $colaborador['nombres'] . ' ' . $colaborador['apellidos'] . ' - ' . $colaborador['documento']
        ]);
    }

    /**
     * Actualizar curriculum completo
     */
    public function update(string $colaboradorId): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithMessage("/curriculum/{$colaboradorId}/edit", 'Método no permitido', 'error');
            return;
        }

        $data = $this->post();

        // Obtener o crear curriculum
        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        // Si no existe curriculum, crear uno vacío
        if (!$curriculum) {
            try {
                $curriculumId = $this->curriculumModel->create([
                    'colaborador_id' => $colaboradorId,
                    'experiencia_laboral' => [],
                    'formacion_academica' => [],
                    'participacion_politica' => []
                ]);

                $curriculum = $this->curriculumModel->getById($curriculumId);
            } catch (\Exception $e) {
                $this->setFlash('Error al crear curriculum: ' . $e->getMessage(), 'error');
                $this->redirect('/colaboradores/' . $colaboradorId);
                return;
            }
        }

        try {
            $updateData = [];

            // Actualizar solo campos presentes
            if (isset($data['resumen_profesional'])) {
                $updateData['resumen_profesional'] = trim($data['resumen_profesional']);
            }

            if (isset($data['habilidades'])) {
                $updateData['habilidades'] = trim($data['habilidades']);
            }

            if (isset($data['idiomas'])) {
                $updateData['idiomas'] = trim($data['idiomas']);
            }

            if (isset($data['reconocimientos'])) {
                $updateData['reconocimientos'] = trim($data['reconocimientos']);
            }

            if (isset($data['referencias'])) {
                $updateData['referencias'] = trim($data['referencias']);
            }

            if (isset($data['observaciones'])) {
                $updateData['observaciones'] = trim($data['observaciones']);
            }

            // Si no hay datos para actualizar, considerar éxito
            if (empty($updateData)) {
                $this->setFlash('No hay cambios para guardar', 'info');
                $this->redirect("/curriculum/{$colaboradorId}");
                return;
            }

            $success = $this->curriculumModel->update($curriculum['id'], $updateData);

            if ($success) {
                $this->setFlash('Curriculum actualizado exitosamente', 'success');
                $this->redirect("/curriculum/{$colaboradorId}");
            } else {
                $this->setFlash('No se pudo actualizar el curriculum', 'error');
                $this->redirect("/curriculum/{$colaboradorId}/edit");
            }
        } catch (\Exception $e) {
            $this->setFlash('Error al actualizar curriculum: ' . $e->getMessage(), 'error');
            $this->redirect("/curriculum/{$colaboradorId}/edit");
        }
    }

    /**
     * Agregar experiencia laboral
     */
    public function addExperiencia(string $colaboradorId): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', null, 405);
            return;
        }

        $data = $this->post();

        // Obtener curriculum
        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        if (!$curriculum) {
            $this->jsonResponse(['success' => false, 'message' => 'Curriculum no encontrado']);
            return;
        }

        // Validar experiencia
        $errors = $this->curriculumModel->validateExperiencia($data);
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors]);
            return;
        }

        try {
            $experiencia = [
                'cargo' => trim($data['cargo']),
                'empresa' => trim($data['empresa']),
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'descripcion' => trim($data['descripcion'] ?? ''),
                'actual' => isset($data['actual']) ? true : false
            ];

            $success = $this->curriculumModel->addExperienciaLaboral($curriculum['id'], $experiencia);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Experiencia laboral agregada exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo agregar la experiencia laboral'
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Agregar formación académica
     */
    public function addFormacion(string $colaboradorId): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', null, 405);
            return;
        }

        $data = $this->post();

        // Obtener curriculum
        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        if (!$curriculum) {
            $this->jsonResponse(['success' => false, 'message' => 'Curriculum no encontrado']);
            return;
        }

        // Validar formación
        $errors = $this->curriculumModel->validateFormacion($data);
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors]);
            return;
        }

        try {
            $formacion = [
                'titulo' => trim($data['titulo']),
                'institucion' => trim($data['institucion']),
                'nivel' => $data['nivel'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'en_curso' => isset($data['en_curso']) ? true : false
            ];

            $success = $this->curriculumModel->addFormacionAcademica($curriculum['id'], $formacion);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Formación académica agregada exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo agregar la formación académica'
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Agregar participación política
     */
    public function addParticipacion(string $colaboradorId): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', null, 405);
            return;
        }

        $data = $this->post();

        // Obtener curriculum
        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        if (!$curriculum) {
            $this->jsonResponse(['success' => false, 'message' => 'Curriculum no encontrado']);
            return;
        }

        // Validar participación
        $errors = $this->curriculumModel->validateParticipacion($data);
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors]);
            return;
        }

        try {
            $participacion = [
                'tipo' => trim($data['tipo'] ?? ''),
                'cargo' => trim($data['cargo']),
                'organizacion' => trim($data['organizacion']),
                'ambito' => trim($data['ambito'] ?? ''),
                'poblacion_beneficiada' => trim($data['poblacion_beneficiada'] ?? ''),
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'logros' => trim($data['logros'] ?? ''),
                'redes_alianzas' => trim($data['redes_alianzas'] ?? ''),
                'descripcion' => trim($data['descripcion'] ?? ''),
                'actual' => isset($data['actual']) ? true : false
            ];

            $success = $this->curriculumModel->addParticipacionPolitica($curriculum['id'], $participacion);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Participación política agregada exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo agregar la participación política'
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar experiencia laboral
     */
    public function deleteExperiencia(string $colaboradorId, string $index): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', null, 405);
            return;
        }

        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        if (!$curriculum) {
            $this->jsonResponse(['success' => false, 'message' => 'Curriculum no encontrado']);
            return;
        }

        try {
            $experiencias = $curriculum['experiencia_laboral'] ?? [];

            if (!isset($experiencias[(int)$index])) {
                $this->jsonResponse(['success' => false, 'message' => 'Experiencia no encontrada']);
                return;
            }

            unset($experiencias[(int)$index]);
            $experiencias = array_values($experiencias); // Reindexar

            $success = $this->curriculumModel->update($curriculum['id'], [
                'experiencia_laboral' => $experiencias
            ]);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Experiencia laboral eliminada exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo eliminar la experiencia laboral'
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar formación académica
     */
    public function deleteFormacion(string $colaboradorId, string $index): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', null, 405);
            return;
        }

        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        if (!$curriculum) {
            $this->jsonResponse(['success' => false, 'message' => 'Curriculum no encontrado']);
            return;
        }

        try {
            $formaciones = $curriculum['formacion_academica'] ?? [];

            if (!isset($formaciones[(int)$index])) {
                $this->jsonResponse(['success' => false, 'message' => 'Formación no encontrada']);
                return;
            }

            unset($formaciones[(int)$index]);
            $formaciones = array_values($formaciones); // Reindexar

            $success = $this->curriculumModel->update($curriculum['id'], [
                'formacion_academica' => $formaciones
            ]);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Formación académica eliminada exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo eliminar la formación académica'
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar participación política
     */
    public function deleteParticipacion(string $colaboradorId, string $index): void {
        $this->requireAuth();
        $this->requirePermission('colaboradores_editar');

        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Método no permitido', null, 405);
            return;
        }

        $curriculum = $this->curriculumModel->getByColaboradorId((int)$colaboradorId);

        if (!$curriculum) {
            $this->jsonResponse(['success' => false, 'message' => 'Curriculum no encontrado']);
            return;
        }

        try {
            $participaciones = $curriculum['participacion_politica'] ?? [];

            if (!isset($participaciones[(int)$index])) {
                $this->jsonResponse(['success' => false, 'message' => 'Participación no encontrada']);
                return;
            }

            unset($participaciones[(int)$index]);
            $participaciones = array_values($participaciones); // Reindexar

            $success = $this->curriculumModel->update($curriculum['id'], [
                'participacion_politica' => $participaciones
            ]);

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Participación política eliminada exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo eliminar la participación política'
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
