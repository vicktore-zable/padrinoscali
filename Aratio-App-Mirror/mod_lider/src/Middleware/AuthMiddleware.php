<?php
/**
 * Middleware de Autenticación del Portal de Líderes
 * Verifica sesión contra la tabla independiente sesiones_lideres
 *
 * @package App\Middleware
 */

namespace App\Middleware;

use App\Models\SesionLider;
use App\Utils\Logger;

class AuthMiddleware {
    /**
     * Manejar la petición
     */
    public function handle(): void {
        // Verificar si hay sesión iniciada
        if (!isset($_SESSION['user']) || $_SESSION['user']['tipo_usuario'] !== 'lider') {
            Logger::warning('[AUTH MIDDLEWARE] Usuario no autenticado o no es líder', [
                'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
                'uri'        => $_SERVER['REQUEST_URI'] ?? '',
                'session'    => $_SESSION['user'] ?? null
            ]);

            header('Location: ?page=portal_landing');
            exit;
        }

        // Verificar validez de la sesión en BD (tabla sesiones_lideres)
        $sesionModel = new SesionLider();
        $sessionToken = $_SESSION['session_token'] ?? null;

        if (!$sessionToken || !$sesionModel->verify($sessionToken)) {
            // Sesión inválida o expirada
            session_destroy();

            Logger::warning('[AUTH MIDDLEWARE] Sesión inválida o expirada', [
                'user_id'      => $_SESSION['user']['id'] ?? 'unknown',
                'session_token'=> $sessionToken
            ]);

            header('Location: ?page=portal_landing');
            exit;
        }
    }
}
