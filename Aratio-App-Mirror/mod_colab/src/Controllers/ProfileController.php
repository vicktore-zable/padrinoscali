<?php
/**
 * Controlador de Perfil
 * Maneja el perfil del usuario actual
 *
 * @package App\Controllers
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;

class ProfileController extends Controller {
    public function index(): void {
        $usuarioModel = new \App\Models\Usuario();
        $userData = $usuarioModel->getById($this->user['id']);

        // Obtener sesiones activas
        $activeSessions = $usuarioModel->getActiveSessions($this->user['id']);

        $this->view('profile.index', [
            'title' => 'Mi Perfil',
            'user' => $userData,
            'activeSessions' => $activeSessions
        ], null);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
        }

        $usuarioModel = new \App\Models\Usuario();

        $data = [
            'email' => $_POST['email'] ?? '',
            'tipo_usuario' => $_POST['tipo_usuario'] ?? $this->user['tipo_usuario']
        ];

        // Validar datos
        $errors = $usuarioModel->validate($data, true);

        if (!empty($errors)) {
            $this->setFlash('Errores en la validación: ' . implode(', ', $errors), 'error');
            $this->redirect('/profile');
        }

        try {
            $usuarioModel->update($this->user['id'], $data);
            \App\Utils\Logger::info('Perfil actualizado', ['user_id' => $this->user['id']]);
            $this->setFlash('Perfil actualizado exitosamente', 'success');
        } catch (\Exception $e) {
            \App\Utils\Logger::error('Error al actualizar perfil', ['error' => $e->getMessage()]);
            $this->setFlash('Error al actualizar el perfil', 'error');
        }

        $this->redirect('/profile');
    }

    public function changePassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
        }

        $usuarioModel = new \App\Models\Usuario();

        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('Las contraseñas no coinciden', 'error');
            $this->redirect('/profile');
        }

        try {
            $usuarioModel->changePassword($this->user['id'], $oldPassword, $newPassword);
            \App\Utils\Logger::security('Contraseña cambiada', ['user_id' => $this->user['id']]);
            $this->setFlash('Contraseña cambiada exitosamente', 'success');
        } catch (\Exception $e) {
            $this->setFlash($e->getMessage(), 'error');
        }

        $this->redirect('/profile');
    }

    public function show2FA(): void {
        $usuarioModel = new \App\Models\Usuario();
        $userData = $usuarioModel->getById($this->user['id']);

        $this->view('profile.2fa', [
            'title' => 'Configuración 2FA',
            'user' => $userData
        ], null);
    }

    public function enable2FA(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile/2fa');
        }

        $usuarioModel = new \App\Models\Usuario();

        // Generar token 2FA (simulado - en producción usar biblioteca como Google Authenticator)
        $token = bin2hex(random_bytes(16));

        try {
            $usuarioModel->set2FA($this->user['id'], true, $token);
            \App\Utils\Logger::security('2FA habilitado', ['user_id' => $this->user['id']]);
            $this->setFlash('Autenticación de dos factores habilitada', 'success');
        } catch (\Exception $e) {
            $this->setFlash('Error al habilitar 2FA', 'error');
        }

        $this->redirect('/profile');
    }

    public function disable2FA(): void {
        $usuarioModel = new \App\Models\Usuario();

        try {
            $usuarioModel->set2FA($this->user['id'], false);
            \App\Utils\Logger::security('2FA deshabilitado', ['user_id' => $this->user['id']]);
            $this->setFlash('Autenticación de dos factores deshabilitada', 'success');
        } catch (\Exception $e) {
            $this->setFlash('Error al deshabilitar 2FA', 'error');
        }

        $this->redirect('/profile');
    }

    public function sessions(): void {
        $usuarioModel = new \App\Models\Usuario();
        $activeSessions = $usuarioModel->getActiveSessions($this->user['id']);

        $this->view('profile.sessions', [
            'title' => 'Sesiones Activas',
            'activeSessions' => $activeSessions
        ], null);
    }

    public function closeSession(string $id): void {
        $usuarioModel = new \App\Models\Usuario();

        try {
            // Solo permitir cerrar sesiones propias o si es admin
            if ($id !== session_id() && $this->user['tipo_usuario'] !== 'admin') {
                throw new \Exception('No tiene permisos para cerrar esta sesión');
            }

            $usuarioModel->destroySession($id);
            \App\Utils\Logger::security('Sesión cerrada', ['session_id' => $id, 'user_id' => $this->user['id']]);

            if ($id === session_id()) {
                // Cerrar sesión actual
                session_destroy();
                $this->redirect('/login');
            } else {
                $this->setFlash('Sesión cerrada exitosamente', 'success');
            }
        } catch (\Exception $e) {
            $this->setFlash($e->getMessage(), 'error');
        }

        $this->redirect('/profile/sessions');
    }
}
