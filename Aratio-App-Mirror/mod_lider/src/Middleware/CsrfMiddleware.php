<?php
/**
 * Middleware CSRF
 * Verifica tokens CSRF en peticiones POST/PUT/DELETE
 *
 * @package App\Middleware
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Middleware;

use App\Utils\Security;
use App\Utils\Logger;

class CsrfMiddleware {
    /**
     * Manejar la petición
     */
    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        // Solo verificar en métodos que modifican datos
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            // Excluir rutas de API
            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, '/api/') !== false) {
                return;
            }
            try {
                Security::checkCsrf();
            } catch (\Exception $e) {
                Logger::security('Token CSRF inválido detectado', [
                    'method' => $method,
                    'uri' => $_SERVER['REQUEST_URI'],
                    'ip' => Security::getClientIp()
                ]);

                http_response_code(419);

                // Si es AJAX, responder JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Token de seguridad inválido. Por favor recargue la página.'
                    ]);
                } else {
                    echo "<h1>419 - Token CSRF Inválido</h1>";
                    echo "<p>El token de seguridad ha expirado o es inválido. Por favor recargue la página e intente nuevamente.</p>";
                }

                exit;
            }
        }
    }
}
