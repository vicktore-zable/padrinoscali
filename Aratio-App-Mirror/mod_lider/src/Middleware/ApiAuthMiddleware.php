<?php
/**
 * Middleware de Autenticación de API
 * Verifica que el request contenga una API Key válida
 *
 * @package App\Middleware
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Middleware;

use App\Utils\Logger;

class ApiAuthMiddleware {
    /**
     * Manejar la petición
     */
    public function handle(): void {
        // Buscar API Key en headers
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? (getallheaders()['X-API-Key'] ?? null);

        // Si no hay key, buscar en parámetros (opcional, menos seguro pero util para pruebas)
        if (!$apiKey) {
            $apiKey = $_GET['api_key'] ?? null;
        }

        if (!$apiKey || $apiKey !== API_CONFIG['key']) {
            Logger::warning('Intento de acceso a API no autorizado', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'api_key_provided' => $apiKey ? 'present but invalid' : 'missing'
            ]);

            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'No autorizado. API Key inválida o faltante.'
            ]);
            exit;
        }
    }
}
