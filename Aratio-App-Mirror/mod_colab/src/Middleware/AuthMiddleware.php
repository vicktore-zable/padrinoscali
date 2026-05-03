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
        // Log de auditoría de autenticación
        Logger::debug('[DEBUG AUTH] Verificando sesión', [
            'has_user' => isset($_SESSION['user']),
            'uri' => $_SERVER['REQUEST_URI'] ?? ''
        ]);

        // Verificar si hay sesión iniciada
        if (!isset($_SESSION['user'])) {
            Logger::warning('[DEBUG AUTH] Usuario no autenticado - sin sesión user', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'session_data' => $_SESSION ?? []
            ]);

            // Si es una petición AJAX o API, devolver JSON
            if ($this->isApiRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Sesión expirada o no válida', 'redirect' => '/login']);
                exit;
            }

            // Redirigir a login para peticiones normales
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

            // Si es una petición AJAX o API, devolver JSON
            if ($this->isApiRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Sesión inválida o expirada', 'redirect' => '/login']);
                exit;
            }

            header('Location: /login');
            exit;
        }

        Logger::debug('[DEBUG AUTH] Autenticación exitosa', [
            'user_id' => $_SESSION['user']['id'],
            'session_token' => $sessionToken
        ]);
    }

    /**
     * Verificar si es una petición API o AJAX
     */
    private function isApiRequest(): bool {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (strpos($uri, '/api/') !== false) ||
               (preg_match('/\.(json|xml|csv)$/i', $uri)) ||
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
