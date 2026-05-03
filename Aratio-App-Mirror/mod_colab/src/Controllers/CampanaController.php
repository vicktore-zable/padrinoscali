<?php
/**
 * Controlador de Campañas
 * Maneja la administración de múltiples campañas
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Campana;
use App\Models\Usuario;
use App\Utils\Validator;
use App\Utils\Logger;
use App\Utils\Helpers;

class CampanaController extends Controller {
    private Campana $campanaModel;

    public function __construct() {
        parent::__construct();
        $this->campanaModel = new Campana();
    }

    /**
     * Listar campañas
     */
    public function index(): void {
        $this->requireAuth();
        $this->requirePermission('campanas_ver');

        $campanas = $this->campanaModel->getAll();

        // Enriquecer con estadísticas
        foreach ($campanas as &$campana) {
            $campana['stats'] = $this->campanaModel->getStats($campana['id']);
        }

        $this->view('campanas.index', [
            'pageTitle' => 'Gestión de Campañas',
            'pageDescription' => 'Administrar campañas y grupos de colaboradores',
            'campanas' => $campanas,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/dashboard'],
                ['label' => 'Campañas']
            ]
        ]);
    }

    /**
     * Ver detalle de campaña
     */
    public function show(string $id): void {
        $this->requireAuth();
        $this->requirePermission('campanas_ver');

        $campana = $this->campanaModel->getById((int)$id);

        if (!$campana) {
            $this->setFlash('Campaña no encontrada', 'error');
            $this->redirect('/campanas');
        }

        $stats = $this->campanaModel->getStats((int)$id);
        $administradores = $this->campanaModel->getAdministradores((int)$id);

        $this->view('campanas.show', [
            'pageTitle' => $campana['nombre'],
            'campana' => $campana,
            'stats' => $stats,
            'administradores' => $administradores,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/dashboard'],
                ['label' => 'Campañas', 'url' => '/campanas'],
                ['label' => 'Detalle']
            ]
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create(): void {
        $this->requireAuth();
        $this->requirePermission('campanas_crear');

        $this->view('campanas.create', [
            'pageTitle' => 'Nueva Campaña',
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/dashboard'],
                ['label' => 'Campañas', 'url' => '/campanas'],
                ['label' => 'Nueva']
            ]
        ]);
    }

    /**
     * Guardar campaña
     */
    public function store(): void {
        $this->requireAuth();
        $this->requirePermission('campanas_crear');

        $data = $this->post();

        $validator = new Validator($data);
        $validator->required(['nombre', 'codigo_externo'])
                  ->maxLength('nombre', 150)
                  ->unique('codigo_externo', 'campanas', 'codigo_externo');

        if ($validator->fails()) {
            $_SESSION['old_input'] = $data;
            $_SESSION['errors'] = $validator->errors();
            $this->redirect('/campanas/create');
        }

        try {
            $id = $this->campanaModel->create([
                'codigo_externo' => Security::sanitize($data['codigo_externo']),
                'nombre' => Security::sanitize($data['nombre']),
                'descripcion' => Security::sanitize($data['descripcion'] ?? ''),
                'candidato_nombre' => Security::sanitize($data['candidato_nombre'] ?? ''),
                'territorio' => Security::sanitize($data['territorio'] ?? ''),
                'fecha_inicio' => !empty($data['fecha_inicio']) ? $data['fecha_inicio'] : null,
                'fecha_fin' => !empty($data['fecha_fin']) ? $data['fecha_fin'] : null,
                'activo' => isset($data['activo']) ? 1 : 0
            ]);

            Logger::info('Campaña creada', ['campana_id' => $id, 'user_id' => $this->user['id']]);
            $this->setFlash('Campaña creada exitosamente', 'success');
            $this->redirect('/campanas');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'crear_campana']);
            $this->setFlash('Error: ' . $e->getMessage(), 'error');
            $this->redirect('/campanas/create');
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(string $id): void {
        $this->requireAuth();
        $this->requirePermission('campanas_editar');

        $campana = $this->campanaModel->getById((int)$id);

        if (!$campana) {
            $this->setFlash('Campaña no encontrada', 'error');
            $this->redirect('/campanas');
        }

        $this->view('campanas.edit', [
            'pageTitle' => 'Editar Campaña',
            'campana' => $campana,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/dashboard'],
                ['label' => 'Campañas', 'url' => '/campanas'],
                ['label' => 'Editar']
            ]
        ]);
    }

    /**
     * Actualizar campaña
     */
    public function update(string $id): void {
        $this->requireAuth();
        $this->requirePermission('campanas_editar');

        $data = $this->post();

        $validator = new Validator($data);
        $validator->required(['nombre', 'codigo_externo'])
                  ->unique('codigo_externo', 'campanas', 'codigo_externo', (int)$id);

        if ($validator->fails()) {
            $_SESSION['old_input'] = $data;
            $_SESSION['errors'] = $validator->errors();
            $this->redirect("/campanas/{$id}/edit");
        }

        try {
            $this->campanaModel->update((int)$id, [
                'codigo_externo' => Security::sanitize($data['codigo_externo']),
                'nombre' => Security::sanitize($data['nombre']),
                'descripcion' => Security::sanitize($data['descripcion'] ?? ''),
                'candidato_nombre' => Security::sanitize($data['candidato_nombre'] ?? ''),
                'territorio' => Security::sanitize($data['territorio'] ?? ''),
                'fecha_inicio' => !empty($data['fecha_inicio']) ? $data['fecha_inicio'] : null,
                'fecha_fin' => !empty($data['fecha_fin']) ? $data['fecha_fin'] : null,
                'activo' => isset($data['activo']) ? 1 : 0
            ]);

            Logger::info('Campaña actualizada', ['campana_id' => $id, 'user_id' => $this->user['id']]);
            $this->setFlash('Campaña actualizada exitosamente', 'success');
            $this->redirect('/campanas');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'actualizar_campana', 'id' => $id]);
            $this->setFlash('Error: ' . $e->getMessage(), 'error');
            $this->redirect("/campanas/{$id}/edit");
        }
    }
}
