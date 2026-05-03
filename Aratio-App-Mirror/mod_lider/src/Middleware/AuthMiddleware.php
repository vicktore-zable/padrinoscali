<?php
/**
 * Middleware de Autenticación
 * Verifica que el usuario esté autenticado
 *
 * @package App\Middleware
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Middleware;

use App\Utils\Logger;

class AuthMiddleware {
    /**
     * Manejar la petición
     */
    public function handle(): void {
        // DEBUG: Log inicial del estado de la sesión
        Logger::debug('[DEBUG AUTH] Iniciando verificación de autenticación', [
            'session_exists' => isset($_SESSION),
            'user_exists' => isset($_SESSION['user']),
            'user_data' => $_SESSION['user'] ?? null,
            'session_token_exists' => isset($_SESSION['session_token']),
            'session_token' => $_SESSION['session_token'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? ''
        ]);

        // Verificar si hay sesión iniciada
        if (!isset($_SESSION['user'])) {
            Logger::warning('[DEBUG AUTH] Usuario no autenticado - sin sesión user', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'session_data' => $_SESSION ?? []
            ]);

            // Redirigir a login
            header('Location: /login');
            exit;
        }

        // Verificar validez de la sesión en BD
        $usuarioModel = new \App\Models\Usuario();
        $sessionToken = $_SESSION['session_token'] ?? null;

        Logger::debug('[DEBUG AUTH] Verificando token de sesión', [
            'user_id' => $_SESSION['user']['id'] ?? null,
            'session_token' => $sessionToken
        ]);

        if (!$sessionToken) {
            Logger::warning('[DEBUG AUTH] Token de sesión no existe', [
                'user_id' => $_SESSION['user']['id'] ?? null
            ]);
        }

        if (!$sessionToken || !$usuarioModel->verifySession($sessionToken)) {
            // Sesión inválida o expirada
            session_destroy();

            Logger::warning('[DEBUG AUTH] Sesión inválida o expirada', [
                'user_id' => $_SESSION['user']['id'] ?? 'unknown',
                'session_token' => $sessionToken,
                'verify_result' => $sessionToken ? $usuarioModel->verifySession($sessionToken) : 'no_token'
            ]);

            header('Location: /login');
            exit;
        }

        Logger::debug('[DEBUG AUTH] Autenticación exitosa', [
            'user_id' => $_SESSION['user']['id'],
            'session_token' => $sessionToken
        ]);
    }
}
