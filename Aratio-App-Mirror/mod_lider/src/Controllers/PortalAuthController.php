<?php
/**
 * Controlador de Autenticación del Portal del Líder
 * Simplificado: Login siempre con Documento y Teléfono
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
            if (isset($_SESSION['user']['tipo_usuario']) && $_SESSION['user']['tipo_usuario'] === 'lider') {
                $this->redirect('?page=portal_dashboard');
            }
            $this->redirect('?page=dashboard');
        }

        $this->view('portal.auth.login', ['title' => 'Iniciar Sesión'], 'portal_public');
    }

    public function landing() {
        if ($this->isAuthenticated()) {
            if (isset($_SESSION['user']['tipo_usuario']) && $_SESSION['user']['tipo_usuario'] === 'lider') {
                $this->redirect('?page=portal_dashboard');
            }
        }

        $this->view('portal.landing', ['title' => 'Portal de Líderes'], 'portal_public');
    }

    /**
     * Procesar login de líder (Documento y Teléfono como contraseña siempre)
     */
    public function login() {
        $documento = trim($this->input('documento'));
        $password = trim($this->input('password'));

        if (empty($documento) || empty($password)) {
            $this->setFlash('Por favor ingrese su documento y teléfono', 'error');
            $this->redirect('?page=portal_login');
            return;
        }

        try {
            $colaborador = $this->colaboradorModel->getByDocumento($documento);
            if (!$colaborador) {
                $this->setFlash('No se encontró un líder registrado con este documento.', 'error');
                $this->redirect('?page=portal_login');
                return;
            }

            $dbTelefono = trim($colaborador['telefono'] ?? '');
            if (empty($dbTelefono)) {
                $this->setFlash('El líder no tiene un teléfono registrado para el acceso. Contacte al administrador.', 'error');
                $this->redirect('?page=portal_login');
                return;
            }

            if ($password !== $dbTelefono) {
                $this->setFlash('El teléfono ingresado no coincide con nuestros registros.', 'error');
                $this->redirect('?page=portal_login');
                return;
            }

            // Construir sesión desde colaborador, intentando crear usuario de sistema
            $sessionUser = [
                'usuario' => $documento,
                'nombre' => ($colaborador['nombres'] ?? '') . ' ' . ($colaborador['apellidos'] ?? ''),
                'nombres' => $colaborador['nombres'] ?? '',
                'apellidos' => $colaborador['apellidos'] ?? '',
                'email' => $colaborador['email'] ?? '',
                'tipo_usuario' => 'lider',
                'colaborador_id' => $colaborador['id'],
                'documento_colaborador' => $colaborador['documento'],
                'activo' => 1,
            ];

            // Intentar encontrar o crear usuario de sistema, pero no bloquear si falla
            try {
                $user = $this->usuarioModel->getByUsuario($documento);
                if (!$user) {
                    $userId = $this->usuarioModel->create([
                        'usuario' => $documento,
                        'nombre' => $sessionUser['nombre'],
                        'email' => $colaborador['email'] ?: ($documento . '@aratio.com'),
                        'password' => $dbTelefono,
                        'nombres' => $sessionUser['nombres'],
                        'apellidos' => $sessionUser['apellidos'],
                        'tipo_usuario' => 'lider',
                        'colaborador_id' => $colaborador['id'],
                        'activo' => 1,
                        'documento_colaborador' => $colaborador['documento']
                    ]);
                    $user = $this->usuarioModel->getById($userId);
                }
                $sessionUser['id'] = $user['id'];
            } catch (\Exception $e) {
                // Si falla la creación en usuarios, usar ID sintético negativo
                Logger::warning("Login sin usuario de sistema: " . $e->getMessage());
                $sessionUser['id'] = -$colaborador['id'];
            }

            $this->loginUser($sessionUser);

        } catch (\Exception $e) {
            Logger::exception($e);
            $this->setFlash('Error técnico en el acceso. Intente más tarde.', 'error');
            $this->redirect('?page=portal_login');
        }
    }

    private function loginUser($user) {
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];

        try {
            $_SESSION['session_token'] = $this->usuarioModel->createSession(
                $user['id'],
                Security::getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            );
        } catch (\Exception $e) {
            Logger::error("No se pudo crear registro de sesión: " . $e->getMessage());
        }

        $this->redirect('?page=portal_dashboard');
    }

    public function logout() {
        session_destroy();
        $this->redirect('?page=portal_landing');
    }

}
