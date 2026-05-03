<?php
/**
 * Controlador de Usuarios
 * Maneja la administración de usuarios del sistema
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Colaborador;
use App\Utils\Validator;
use App\Utils\Logger;

class UsuarioController extends Controller {
    private Usuario $usuarioModel;
    private Colaborador $colaboradorModel;

    public function __construct() {
        parent::__construct();
        $this->usuarioModel = new Usuario();
        $this->colaboradorModel = new Colaborador();
    }

    /**
     * Listar usuarios
     */
    public function index(): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_ver');

        $page = max(1, (int)($this->input('page') ?? 1));
        $perPage = 25;

        // Determinar si es líder para mostrar vista simplificada
        $isLider = $this->user['tipo_usuario'] === 'lider';

        if ($isLider) {
            // Para líderes, mostrar vista simplificada con estadísticas
            $this->showLiderView();
            return;
        }

        // Para administradores, vista completa
        $filters = $this->buildUserFilters();
        $usuarios = $this->usuarioModel->getAll($page, $perPage, $filters);

        $this->view('usuarios.index', [
            'pageTitle' => 'Gestión de Usuarios',
            'pageDescription' => 'Administrar usuarios del sistema',
            'usuarios' => $usuarios,
            'page' => $page,
            'perPage' => $perPage,
            'tipos' => TIPOS_USUARIO,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/dashboard'],
                ['label' => 'Usuarios']
            ]
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_crear');

        // Obtener campañas
        $campanaModel = new \App\Models\Campana();
        $campanas = $campanaModel->getAll(true);

        $this->view('usuarios.create', [
            'pageTitle' => 'Nuevo Usuario',
            'pageDescription' => 'Crear un nuevo usuario del sistema',
            'tipos' => TIPOS_USUARIO,
            'colaboradores' => $colaboradores,
            'campanas' => $campanas,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/dashboard'],
                ['label' => 'Usuarios', 'url' => '/usuarios'],
                ['label' => 'Nuevo']
            ]
        ]);
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_crear');

        $data = $this->post();

        // Validar
        $validator = new Validator($data);
        $validator->required(['usuario', 'email', 'password', 'tipo_usuario'])
                  ->minLength('usuario', 4)
                  ->maxLength('usuario', 50)
                  ->pattern('usuario', '/^[a-zA-Z0-9_]+$/', 'Usuario solo puede contener letras, números y guión bajo')
                  ->email('email')
                  ->minLength('password', PASSWORD_CONFIG['min_length'])
                  ->unique('usuario', 'usuarios', 'usuario')
                  ->unique('email', 'usuarios', 'email')
                  ->in('tipo_usuario', array_keys(TIPOS_USUARIO));

        // Validaciones adicionales de contraseña
        if (PASSWORD_CONFIG['require_uppercase'] && !preg_match('/[A-Z]/', $data['password'] ?? '')) {
            $validator->addError('password', 'Debe contener al menos una mayúscula');
        }
        if (PASSWORD_CONFIG['require_lowercase'] && !preg_match('/[a-z]/', $data['password'] ?? '')) {
            $validator->addError('password', 'Debe contener al menos una minúscula');
        }
        if (PASSWORD_CONFIG['require_number'] && !preg_match('/[0-9]/', $data['password'] ?? '')) {
            $validator->addError('password', 'Debe contener al menos un número');
        }
        if (PASSWORD_CONFIG['require_special'] && !preg_match('/[@$!%*?&]/', $data['password'] ?? '')) {
            $validator->addError('password', 'Debe contener al menos un carácter especial');
        }

        if ($validator->fails()) {
            $_SESSION['old_input'] = $data;
            $_SESSION['errors'] = $validator->errors();
            $this->redirect('/usuarios/create');
        }

        try {
            // Preparar datos
            $userData = [
                'usuario' => $data['usuario'],
                'email' => $data['email'],
                'password' => $data['password'],
                'tipo_usuario' => $data['tipo_usuario'],
                'campana_id' => !empty($data['campana_id']) ? (int)$data['campana_id'] : null,
                'documento_colaborador' => $data['documento_colaborador'] ?? null,
                'activo' => isset($data['activo']) ? 1 : 0,
                'require_2fa' => isset($data['require_2fa']) ? 1 : 0
            ];

            $id = $this->usuarioModel->create($userData);

            Logger::info('Usuario creado', [
                'usuario_id' => $id,
                'usuario' => $data['usuario'],
                'tipo' => $data['tipo_usuario'],
                'created_by' => $this->user['id']
            ]);

            $this->setFlash('Usuario creado exitosamente', 'success');
            $this->redirect('/usuarios');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'crear_usuario']);
            $_SESSION['old_input'] = $data;
            $this->setFlash('Error al crear usuario: ' . $e->getMessage(), 'error');
            $this->redirect('/usuarios/create');
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(string $id): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_editar');

        $usuario = $this->usuarioModel->getById((int)$id);

        if (!$usuario) {
            $this->setFlash('Usuario no encontrado', 'error');
            $this->redirect('/usuarios');
        }

        // Obtener colaboradores
        $colaboradores = $this->colaboradorModel->getAll([], 1000, 0);

        // Obtener sesiones activas
        $sesiones = $this->usuarioModel->getActiveSessions((int)$id);

        // Obtener campañas
        $campanaModel = new \App\Models\Campana();
        $campanas = $campanaModel->getAll(true);

        $this->view('usuarios.edit', [
            'pageTitle' => 'Editar Usuario',
            'pageDescription' => 'Modificar información del usuario',
            'usuario' => $usuario,
            'tipos' => TIPOS_USUARIO,
            'colaboradores' => $colaboradores,
            'campanas' => $campanas,
            'sesiones' => $sesiones,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/dashboard'],
                ['label' => 'Usuarios', 'url' => '/usuarios'],
                ['label' => 'Editar']
            ]
        ]);
    }

    /**
     * Actualizar usuario
     */
    public function update(string $id): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_editar');

        $usuario = $this->usuarioModel->getById((int)$id);

        if (!$usuario) {
            $this->setFlash('Usuario no encontrado', 'error');
            $this->redirect('/usuarios');
        }

        $data = $this->post();

        // Validar
        $validator = new Validator($data);
        $validator->required(['usuario', 'email', 'tipo_usuario'])
                  ->minLength('usuario', 4)
                  ->maxLength('usuario', 50)
                  ->pattern('usuario', '/^[a-zA-Z0-9_]+$/', 'Usuario solo puede contener letras, números y guión bajo')
                  ->email('email')
                  ->unique('usuario', 'usuarios', 'usuario', (int)$id)
                  ->unique('email', 'usuarios', 'email', (int)$id)
                  ->in('tipo_usuario', array_keys(TIPOS_USUARIO));

        // Validar contraseña solo si se proporciona
        if (!empty($data['password'])) {
            $validator->minLength('password', PASSWORD_CONFIG['min_length']);

            if (PASSWORD_CONFIG['require_uppercase'] && !preg_match('/[A-Z]/', $data['password'])) {
                $validator->addError('password', 'Debe contener al menos una mayúscula');
            }
            if (PASSWORD_CONFIG['require_lowercase'] && !preg_match('/[a-z]/', $data['password'])) {
                $validator->addError('password', 'Debe contener al menos una minúscula');
            }
            if (PASSWORD_CONFIG['require_number'] && !preg_match('/[0-9]/', $data['password'])) {
                $validator->addError('password', 'Debe contener al menos un número');
            }
            if (PASSWORD_CONFIG['require_special'] && !preg_match('/[@$!%*?&]/', $data['password'])) {
                $validator->addError('password', 'Debe contener al menos un carácter especial');
            }
        }

        if ($validator->fails()) {
            $_SESSION['old_input'] = $data;
            $_SESSION['errors'] = $validator->errors();
            $this->redirect("/usuarios/{$id}/edit");
        }

        try {
            // Preparar datos
            $userData = [
                'usuario' => $data['usuario'],
                'email' => $data['email'],
                'tipo_usuario' => $data['tipo_usuario'],
                'campana_id' => !empty($data['campana_id']) ? (int)$data['campana_id'] : null,
                'documento_colaborador' => $data['documento_colaborador'] ?? null,
                'activo' => isset($data['activo']) ? 1 : 0,
                'require_2fa' => isset($data['require_2fa']) ? 1 : 0
            ];

            // Solo incluir contraseña si se proporcionó
            if (!empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $this->usuarioModel->update((int)$id, $userData);

            Logger::info('Usuario actualizado', [
                'usuario_id' => $id,
                'usuario' => $data['usuario'],
                'updated_by' => $this->user['id']
            ]);

            $this->setFlash('Usuario actualizado exitosamente', 'success');
            $this->redirect('/usuarios');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'actualizar_usuario', 'id' => $id]);
            $_SESSION['old_input'] = $data;
            $this->setFlash('Error al actualizar usuario: ' . $e->getMessage(), 'error');
            $this->redirect("/usuarios/{$id}/edit");
        }
    }

    /**
     * Eliminar usuario
     */
    public function delete(string $id): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_eliminar');

        $usuario = $this->usuarioModel->getById((int)$id);

        if (!$usuario) {
            $this->jsonError('Usuario no encontrado', null, 404);
        }

        // No permitir eliminar el propio usuario
        if ((int)$id === $this->user['id']) {
            $this->jsonError('No puedes eliminar tu propio usuario');
        }

        try {
            // Cerrar todas las sesiones primero
            $this->usuarioModel->destroyAllSessions((int)$id);

            // Eliminar usuario
            $this->usuarioModel->delete((int)$id);

            Logger::warning('Usuario eliminado', [
                'usuario_id' => $id,
                'usuario' => $usuario['usuario'],
                'deleted_by' => $this->user['id']
            ]);

            $this->jsonSuccess(null, 'Usuario eliminado exitosamente');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'eliminar_usuario', 'id' => $id]);
            $this->jsonError('Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Bloquear/Desbloquear usuario
     */
    public function toggleBlock(string $id): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_editar');

        $usuario = $this->usuarioModel->getById((int)$id);

        if (!$usuario) {
            $this->jsonError('Usuario no encontrado', null, 404);
        }

        // No permitir bloquear el propio usuario
        if ((int)$id === $this->user['id']) {
            $this->jsonError('No puedes bloquear tu propio usuario');
        }

        try {
            $nuevoEstado = !$usuario['activo'];
            $this->usuarioModel->setActivo((int)$id, $nuevoEstado);

            // Si se desactiva, cerrar todas las sesiones
            if (!$nuevoEstado) {
                $this->usuarioModel->destroyAllSessions((int)$id);
            }

            Logger::info('Usuario ' . ($nuevoEstado ? 'activado' : 'desactivado'), [
                'usuario_id' => $id,
                'usuario' => $usuario['usuario'],
                'changed_by' => $this->user['id']
            ]);

            $this->jsonSuccess([
                'activo' => $nuevoEstado
            ], 'Usuario ' . ($nuevoEstado ? 'activado' : 'desactivado') . ' exitosamente');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'toggle_block_usuario', 'id' => $id]);
            $this->jsonError('Error al cambiar estado del usuario: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar todas las sesiones de un usuario
     */
    public function closeSessions(string $id): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_editar');

        $usuario = $this->usuarioModel->getById((int)$id);

        if (!$usuario) {
            $this->jsonError('Usuario no encontrado', null, 404);
        }

        try {
            $this->usuarioModel->destroyAllSessions((int)$id);

            Logger::info('Sesiones cerradas', [
                'usuario_id' => $id,
                'usuario' => $usuario['usuario'],
                'closed_by' => $this->user['id']
            ]);

            $this->jsonSuccess(null, 'Todas las sesiones han sido cerradas');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'cerrar_sesiones', 'id' => $id]);
            $this->jsonError('Error al cerrar sesiones: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar contraseña (por admin)
     */
    public function changePassword(string $id): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_editar');

        $usuario = $this->usuarioModel->getById((int)$id);

        if (!$usuario) {
            $this->jsonError('Usuario no encontrado', null, 404);
        }

        $data = $this->post();

        // Validar
        $validator = new Validator($data);
        $validator->required(['password', 'password_confirmation'])
                  ->minLength('password', PASSWORD_CONFIG['min_length'])
                  ->same('password', 'password_confirmation', 'Las contraseñas no coinciden');

        if ($validator->fails()) {
            $this->jsonError('Datos inválidos', $validator->errors());
        }

        try {
            $this->usuarioModel->resetPassword((int)$id, $data['password']);

            Logger::warning('Contraseña reseteada por administrador', [
                'usuario_id' => $id,
                'usuario' => $usuario['usuario'],
                'reset_by' => $this->user['id']
            ]);

            $this->jsonSuccess(null, 'Contraseña actualizada exitosamente');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'cambiar_password', 'id' => $id]);
            $this->jsonError('Error al cambiar contraseña: ' . $e->getMessage());
        }
    }

    /**
     * Habilitar/Deshabilitar 2FA
     */
    public function toggle2FA(string $id): void {
        $this->requireAuth();
        $this->requirePermission('usuarios_editar');

        $usuario = $this->usuarioModel->getById((int)$id);

        if (!$usuario) {
            $this->jsonError('Usuario no encontrado', null, 404);
        }

        try {
            $nuevoEstado = !$usuario['require_2fa'];
            $this->usuarioModel->set2FA((int)$id, $nuevoEstado);

            Logger::info('2FA ' . ($nuevoEstado ? 'habilitado' : 'deshabilitado'), [
                'usuario_id' => $id,
                'usuario' => $usuario['usuario'],
                'changed_by' => $this->user['id']
            ]);

            $this->jsonSuccess([
                'require_2fa' => $nuevoEstado
            ], '2FA ' . ($nuevoEstado ? 'habilitado' : 'deshabilitado') . ' exitosamente');

        } catch (\Exception $e) {
            Logger::exception($e, ['action' => 'toggle_2fa', 'id' => $id]);
            $this->jsonError('Error al cambiar configuración de 2FA: ' . $e->getMessage());
        }
    }

    /**
     * Construir filtros según el perfil del usuario actual
     *
     * @return array
     */
    private function buildUserFilters(): array {
        $filters = [];

        // Si es super administrador, ve todos los usuarios
        if ($this->user['tipo_usuario'] === 'admin') {
            return $filters; // Sin filtros, ve todo
        }

        // Si es líder, solo ve usuarios asociados a sus colaboradores
        if ($this->user['tipo_usuario'] === 'lider' && !empty($this->user['documento_colaborador'])) {
            $filters['lider_documento'] = $this->user['documento_colaborador'];
        }

        // Si es consulta, podría tener restricciones adicionales
        if ($this->user['tipo_usuario'] === 'consulta') {
            // Por ahora, mismo comportamiento que líder si tiene documento
            if (!empty($this->user['documento_colaborador'])) {
                $filters['lider_documento'] = $this->user['documento_colaborador'];
            }
        }

        return $filters;
    }

    /**
     * Mostrar vista simplificada para líderes
     */
    private function showLiderView(): void {
        // Obtener estadísticas del líder
        $liderDocumento = $this->user['documento_colaborador'];

        if (empty($liderDocumento)) {
            // Si no tiene documento asociado, mostrar mensaje
            $this->view('usuarios.lider_no_colaborador', [
                'pageTitle' => 'Mi Equipo',
                'pageDescription' => 'Información de tu equipo de colaboradores',
                'breadcrumbs' => [
                    ['label' => 'Inicio', 'url' => '/dashboard'],
                    ['label' => 'Mi Equipo']
                ]
            ]);
            return;
        }

        // Obtener datos del colaborador líder
        $lider = $this->colaboradorModel->getByDocumento($liderDocumento);

        if (!$lider) {
            $this->view('usuarios.lider_no_encontrado', [
                'pageTitle' => 'Mi Equipo',
                'pageDescription' => 'Información de tu equipo de colaboradores',
                'breadcrumbs' => [
                    ['label' => 'Inicio', 'url' => '/dashboard'],
                    ['label' => 'Mi Equipo']
                ]
            ]);
            return;
        }

        // Obtener colaboradores directos
        $colaboradoresDirectos = $this->colaboradorModel->getSeguidoresDirectos($lider['id']);

        // Obtener red jerárquica completa
        $redCompleta = $this->colaboradorModel->getRedJerarquica($liderDocumento);

        // Estadísticas
        $stats = [
            'total_directos' => count($colaboradoresDirectos),
            'total_red' => count($redCompleta),
            'activos' => count(array_filter($redCompleta, fn($c) => $c['estado'] === 'Creciendo')),
            'inactivos' => count(array_filter($redCompleta, fn($c) => $c['estado'] === 'Decreciendo')),
            'lider' => $lider
        ];

        $this->view('usuarios.lider_dashboard', [
            'pageTitle' => 'Mi Equipo',
            'pageDescription' => 'Información de tu equipo de colaboradores',
            'stats' => $stats,
            'colaboradores_directos' => $colaboradoresDirectos,
            'red_completa' => $redCompleta,
            'lider' => $lider,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/dashboard'],
                ['label' => 'Mi Equipo']
            ]
        ]);
    }
}
