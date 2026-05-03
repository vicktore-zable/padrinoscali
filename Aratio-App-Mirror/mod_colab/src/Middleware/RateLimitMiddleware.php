<?php
/**
 * Middleware Rate Limiting
 * Limita el número de peticiones por IP
 *
 * @package App\Middleware
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Middleware;

use App\Utils\Security;
use App\Utils\Logger;

class RateLimitMiddleware {
    /**
     * Máximo de intentos
     * @var int
     */
    private int $maxAttempts;

    /**
     * Ventana de tiempo en segundos
     * @var int
     */
    private int $timeWindow;

    /**
     * Constructor
     *
     * @param int $maxAttempts
     * @param int $timeWindow
     */
    public function __construct(int $maxAttempts = 60, int $timeWindow = 60) {
        $this->maxAttempts = $maxAttempts;
        $this->timeWindow = $timeWindow;
    }

    /**
     * Manejar la petición
     */
    public function handle(): void {
        $ip = Security::getClientIp();
        $uri = $_SERVER['REQUEST_URI'];
        $key = 'rate_limit_' . md5($ip . $uri);

        if (!Security::checkRateLimit($key, $this->maxAttempts, $this->timeWindow)) {
            Logger::security('Rate limit excedido', [
                'ip' => $ip,
                'uri' => $uri,
                'max_attempts' => $this->maxAttempts,
                'time_window' => $this->timeWindow
            ]);

            http_response_code(429);

            // Calcular tiempo de espera
            $retryAfter = $this->timeWindow;

            header("Retry-After: $retryAfter");

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Demasiadas peticiones. Por favor espere e intente nuevamente.',
                    'retry_after' => $retryAfter
                ]);
            } else {
                echo "<h1>429 - Demasiadas Peticiones</h1>";
                echo "<p>Ha excedido el límite de peticiones permitidas.</p>";
                echo "<p>Por favor espere {$retryAfter} segundos antes de intentar nuevamente.</p>";
            }

            exit;
        }
    }

    /**
     * Crear middleware con límites personalizados
     *
     * @param int $maxAttempts
     * @param int $timeWindow
     * @return self
     */
    public static function limit(int $maxAttempts, int $timeWindow): self {
        return new self($maxAttempts, $timeWindow);
    }
}
