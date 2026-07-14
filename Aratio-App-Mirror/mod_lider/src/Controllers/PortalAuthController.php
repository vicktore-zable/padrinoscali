<?php
/**
 * Controlador de Autenticación del Portal de Líderes
 * 100% independiente del sistema de admin (usuarios)
 *
 * Login: documento + teléfono (siempre funciona)
 *        documento + contraseña (si ya tiene contraseña seteada)
 *
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Colaborador;
use App\Models\SesionLider;
use App\Utils\Logger;
use App\Utils\Security;

class PortalAuthController extends Controller {
    private Colaborador $colaboradorModel;
    private SesionLider $sesionModel;

    public function __construct() {
        parent::__construct();
        $this->colaboradorModel = new Colaborador();
        $this->sesionModel = new SesionLider();
    }

    /**
     * Mostrar landing page
     */
    public function landing() {
        if ($this->isAuthenticated()) {
            if (isset($_SESSION['user']['tipo_usuario']) && $_SESSION['user']['tipo_usuario'] === 'lider') {
                $this->redirect('?page=portal_dashboard');
            }
        }

        $this->view('portal.landing', ['title' => 'Portal de Líderes'], 'portal_public');
    }

    /**
     * Mostrar login para líderes
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

    /**
     * Procesar login de líder
     *
     * Acepta:
     *  - documento + teléfono (siempre funciona)
     *  - documento + contraseña (si ya tiene contraseña seteada)
     */
    public function login() {
        $documento = trim($this->input('documento'));
        $password  = trim($this->input('password'));

        if (empty($documento) || empty($password)) {
            $this->setFlash('Por favor ingrese su documento y teléfono', 'error');
            $this->redirect('?page=portal_login');
            return;
        }

        if (!Security::checkCsrf()) {
            $this->setFlash('Token de seguridad inválido', 'error');
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
                $this->setFlash('El líder no tiene un teléfono registrado. Contacte al administrador.', 'error');
                $this->redirect('?page=portal_login');
                return;
            }

            $dbHash = $colaborador['password'] ?? null;
            $autenticado = false;

            if (!empty($dbHash)) {
                // Ya tiene contraseña seteada → verificar con password_verify
                // Acepta tanto la contraseña como el teléfono
                if (password_verify($password, $dbHash) || $password === $dbTelefono) {
                    $autenticado = true;
                }
            } else {
                // Primer login → solo acepta teléfono
                if ($password === $dbTelefono) {
                    $autenticado = true;
                    // Hashear y guardar para que la próxima pueda usar contraseña
                    $this->colaboradorModel->updatePassword($colaborador['id'], $dbTelefono);
                }
            }

            if (!$autenticado) {
                $this->setFlash('Las credenciales no coinciden con nuestros registros.', 'error');
                $this->redirect('?page=portal_login');
                return;
            }

            $this->loginUser($colaborador);

        } catch (\Exception $e) {
            Logger::exception($e);
            $this->setFlash('Error técnico en el acceso. Intente más tarde.', 'error');
            $this->redirect('?page=portal_login');
        }
    }

    /**
     * Crear sesión y redirigir al dashboard
     */
    private function loginUser(array $colaborador): void {
        $_SESSION['user'] = [
            'id'                    => 'colab_' . $colaborador['id'],
            'colaborador_id'        => $colaborador['id'],
            'documento_colaborador' => $colaborador['documento'],
            'tipo_usuario'          => 'lider',
            'nombres'               => $colaborador['nombres'],
            'apellidos'             => $colaborador['apellidos'],
            'nombre'                => $colaborador['nombres'] . ' ' . $colaborador['apellidos'],
            'email'                 => $colaborador['email'] ?? '',
            'usuario'               => $colaborador['documento'],
            'activo'                => 1,
            'campana_id'            => $colaborador['campana_id'] ?? null,
        ];
        $_SESSION['user_id'] = 'colab_' . $colaborador['id'];

        // Crear sesión en tabla independiente (NO en sesiones de admin)
        $_SESSION['session_token'] = $this->sesionModel->create(
            $colaborador['id'],
            Security::getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        $this->redirect('?page=portal_dashboard');
    }

    /**
     * Cerrar sesión del líder
     */
    public function logout() {
        $token = $_SESSION['session_token'] ?? null;
        if ($token) {
            $this->sesionModel->destroy($token);
        }
        session_destroy();
        $this->redirect('?page=portal_landing');
    }

    /**
     * Cambiar contraseña del líder (desde el portal)
     */
    public function changePassword() {
        if (!$this->isAuthenticated()) {
            $this->redirect('?page=portal_login');
            return;
        }

        $currentPassword = trim($this->input('current_password'));
        $newPassword     = trim($this->input('new_password'));
        $confirmPassword = trim($this->input('confirm_password'));

        // Validaciones
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->setFlash('Todos los campos son obligatorios', 'error');
            $this->redirect('?page=portal_change_password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('La nueva contraseña y la confirmación no coinciden', 'error');
            $this->redirect('?page=portal_change_password');
            return;
        }

        if (strlen($newPassword) < 8) {
            $this->setFlash('La nueva contraseña debe tener al menos 8 caracteres', 'error');
            $this->redirect('?page=portal_change_password');
            return;
        }

        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $this->setFlash('La contraseña debe contener al menos una mayúscula, una minúscula y un número', 'error');
            $this->redirect('?page=portal_change_password');
            return;
        }

        try {
            $colaboradorId = $_SESSION['user']['colaborador_id'];
            $colaborador = $this->colaboradorModel->getById($colaboradorId);

            if (!$colaborador) {
                $this->setFlash('Error: no se encontró el perfil', 'error');
                $this->redirect('?page=portal_dashboard');
                return;
            }

            $dbHash  = $colaborador['password'] ?? null;
            $dbTelefono = trim($colaborador['telefono'] ?? '');

            // Verificar contraseña actual
            $valida = false;
            if (!empty($dbHash)) {
                $valida = password_verify($currentPassword, $dbHash);
            }
            // Si no tiene hash o no verificó con hash, aceptar el teléfono
            if (!$valida && $currentPassword === $dbTelefono) {
                $valida = true;
            }

            if (!$valida) {
                $this->setFlash('La contraseña actual es incorrecta', 'error');
                $this->redirect('?page=portal_change_password');
                return;
            }

            // Guardar nueva contraseña
            $this->colaboradorModel->updatePassword($colaboradorId, $newPassword);

            // Invalidar todas las sesiones anteriores y crear nueva
            $token = $_SESSION['session_token'] ?? null;
            if ($token) {
                $this->sesionModel->destroyAll($colaboradorId);
            }
            $_SESSION['session_token'] = $this->sesionModel->create(
                $colaboradorId,
                Security::getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            );

            $this->setFlash('Contraseña actualizada correctamente', 'success');
            $this->redirect('?page=portal_dashboard');

        } catch (\Exception $e) {
            Logger::exception($e);
            $this->setFlash('Error al cambiar la contraseña. Intente más tarde.', 'error');
            $this->redirect('?page=portal_change_password');
        }
    }

    /**
     * Verificar si el usuario está autenticado como líder
     */
    public function isAuthenticated(): bool {
        return isset($_SESSION['user'])
            && isset($_SESSION['user']['tipo_usuario'])
            && $_SESSION['user']['tipo_usuario'] === 'lider'
            && isset($_SESSION['session_token']);
    }
}
