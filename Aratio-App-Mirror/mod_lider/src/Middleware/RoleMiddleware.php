<?php
/**
 * Middleware de Roles
 * Verifica que el usuario tenga el rol adecuado
 *
 * @package App\Middleware
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Middleware;

use App\Utils\Logger;

class RoleMiddleware {
    /**
     * Roles permitidos
     * @var array
     */
    private array $allowedRoles;

    /**
     * Constructor
     *
     * @param array $allowedRoles
     */
    public function __construct(array $allowedRoles = []) {
        $this->allowedRoles = $allowedRoles;
    }

    /**
     * Manejar la petición
     */
    public function handle(): void {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $userRole = $_SESSION['user']['tipo_usuario'] ?? null;

        // Si no hay roles especificados, permitir todos los autenticados
        if (empty($this->allowedRoles)) {
            return;
        }

        // Verificar si el rol del usuario está permitido
        if (!in_array($userRole, $this->allowedRoles)) {
            Logger::warning('Intento de acceso sin permisos', [
                'user_id' => $_SESSION['user']['id'],
                'user_role' => $userRole,
                'required_roles' => $this->allowedRoles,
                'uri' => $_SERVER['REQUEST_URI']
            ]);

            http_response_code(403);

            if (file_exists(APP_PATH . '/Views/errors/403.php')) {
                require APP_PATH . '/Views/errors/403.php';
            } else {
                echo "<h1>403 - Acceso Prohibido</h1>";
                echo "<p>No tiene permisos para acceder a esta página.</p>";
            }
            exit;
        }
    }

    /**
     * Crear middleware con roles específicos
     *
     * @param array $roles
     * @return self
     */
    public static function only(array $roles): self {
        return new self($roles);
    }
}
