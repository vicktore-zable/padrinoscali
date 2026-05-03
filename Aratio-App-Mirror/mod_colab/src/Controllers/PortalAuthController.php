<?php
/**
 * Controlador de Autenticación del Portal del Líder
 *
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Colaborador;
use App\Utils\Security;
use App\Utils\Validator;
use App\Utils\Logger;

class PortalAuthController extends Controller {
    private Usuario $usuarioModel;
    private Colaborador $colaboradorModel;

    public function __construct() {
        parent::__construct();
        $this->usuarioModel = new Usuario();
        $this->colaboradorModel = new Colaborador();
    }

    /**
     * Mostrar login específico para líderes
     */
    public function showLogin() {
        if ($this->isAuthenticated()) {
            // Si ya es lider, ir al portal
            if (isset($_SESSION['user']['tipo_usuario']) && $_SESSION['user']['tipo_usuario'] === 'lider') {
                $this->redirect('?page=portal_dashboard');
            }
            // Si es admin, ir al dashboard general
            $this->redirect('?page=dashboard');
        }

        $this->view('portal.auth.login', [], null); // Sin layout, la vista tiene su propia estructura HTML
    }

    /**
     * Mostrar Landing Page del Portal
     */
    public function landing() {
        if ($this->isAuthenticated()) {
            // Si ya es lider, ir al portal
            if (isset($_SESSION['user']['tipo_usuario']) && $_SESSION['user']['tipo_usuario'] === 'lider') {
                $this->redirect('?page=portal_dashboard');
            }
             // Si es admin, ir al dashboard general (opcional, o dejar ver la landing)
             // $this->redirect('?page=dashboard');
        }

        $this->view('portal.landing', [], null);
    }

    /**
     * Procesar login de líder (Documento/Documento)
     */
    public function login() {
        $documento = $this->input('documento');
        $password = $this->input('password');

        // Validar
        if (empty($documento) || empty($password)) {
            $this->setFlash('Por favor ingrese su documento', 'error');
            $this->redirect('?page=portal_login');
            return;
        }

        try {
            // 1. Verificar si ya existe un usuario con este documento como nombre de usuario
            $user = $this->usuarioModel->getByUsuario($documento);

            if ($user) {
                // Usuario existe, validar contraseña normal
                if (password_verify($password, $user['password'])) {
                    $this->loginUser($user);
                    return;
                } else {
                    $this->setFlash('Credenciales incorrectas', 'error');
                    $this->redirect('?page=portal_login');
                    return;
                }
            }

            // 2. Si no existe usuario, buscar en colaboradores
            $colaborador = $this->colaboradorModel->getByDocumento($documento);

            if (!$colaborador) {
                $this->setFlash('No se encontró un colaborador con este documento.', 'error');
                $this->redirect('?page=portal_login');
                return;
            }

            // 3. Verificar si es el primer acceso (Password == Documento)
            if ($password === $documento) {
                // Crear usuario automáticamente
                $userId = $this->usuarioModel->create([
                    'usuario' => $documento, // Usuario es el documento
                    'email' => 'lider_' . $documento . '@aratio.tmp', // Email temporal si no tiene
                    'password' => password_hash($documento, PASSWORD_BCRYPT), // Password inicial = documento
                    'nombres' => $colaborador['nombres'],
                    'apellidos' => $colaborador['apellidos'],
                    'tipo_usuario' => 'lider',
                    'colaborador_id' => $colaborador['id'],
                    'activo' => 1,
                    'documento_colaborador' => $colaborador['documento']
                ]);

                // Loguear
                $newUser = $this->usuarioModel->getById($userId);
                $this->loginUser($newUser);
                
                // Opcional: Redirigir a cambiar contraseña
                // $this->setFlash('Por seguridad, cambie su contraseña.', 'warning');
                // $this->redirect('/portal/profile/change-password');
                return;
            } else {
                // Usuario no existe y password no es documento -> Error
                $this->setFlash('Credenciales incorrectas.', 'error');
                $this->redirect('?page=portal_login');
            }

        } catch (\Exception $e) {
            Logger::exception($e);
            $this->setFlash('Error en el sistema: ' . $e->getMessage(), 'error');
            $this->redirect('?page=portal_login');
        }
    }

    private function loginUser($user) {
        // Verificar bloqueos, actividad, etc. (reutilizar lógica de UsuarioModel si es posible o AuthController)
        if (!$user['activo']) {
            $this->setFlash('Su cuenta está inactiva.', 'error');
            $this->redirect('?page=portal_login');
            return;
        }

        // Crear sesión
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id']; // Legacy compatibility
        $_SESSION['session_token'] = $this->usuarioModel->createSession(
            $user['id'], 
            Security::getClientIp(), 
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        $this->redirect('?page=portal_dashboard');
    }
}
