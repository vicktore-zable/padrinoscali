<?php
/**
 * Clase Controller Base
 * Controlador base con funcionalidades comunes
 *
 * @package App\Core
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Core;

use App\Utils\Helpers;

class Controller
{
    /**
     * Datos compartidos con las vistas
     * @var array
     */
    protected array $data = [];

    /**
     * Usuario autenticado
     * @var array|null
     */
    protected ?array $user = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Obtener usuario de sesión si existe
        $this->user = $_SESSION['user'] ?? null;

        // Datos globales para las vistas
        $this->data['user'] = $this->user;
        $this->data['campana_id'] = $this->user['campana_id'] ?? null;
        $this->data['app_name'] = APP_NAME;
        $this->data['app_url'] = APP_URL;
    }

    /**
     * Renderizar vista
     *
     * @param string $view Nombre de la vista (sin extensión)
     * @param array $data Datos para la vista
     * @param string|null $layout Layout a usar (null = sin layout)
     */
    protected function view(string $view, array $data = [], ?string $layout = 'default'): void
    {
        // Combinar datos
        $data = array_merge($this->data, $data);

        // Extraer variables
        extract($data);

        // Buffer de salida
        ob_start();

        // Cargar la vista
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("Vista no encontrada: {$view}");
        }

        require $viewFile;

        $content = ob_get_clean();

        // Si hay layout, renderizar con layout
        if ($layout !== null) {
            $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';

            if (!file_exists($layoutFile)) {
                throw new \Exception("Layout no encontrado: {$layout}");
            }

            require $layoutFile;
        }
        else {
            // Sin layout, solo el contenido
            echo $content;
        }
    }

    /**
     * Renderizar vista parcial (sin layout)
     *
     * @param string $view
     * @param array $data
     */
    protected function partial(string $view, array $data = []): void
    {
        $this->view($view, $data, null);
    }

    /**
     * Respuesta JSON
     *
     * @param mixed $data
     * @param int $status
     */
    protected function json($data, int $status = 200): void
    {
        Helpers::jsonResponse($data, $status);
    }

    /**
     * Respuesta JSON de éxito
     *
     * @param mixed $data
     * @param string $message
     */
    protected function jsonSuccess($data = null, string $message = 'Operación exitosa'): void
    {
        Helpers::jsonSuccess($data, $message);
    }

    /**
     * Respuesta JSON de error
     *
     * @param string $message
     * @param mixed $errors
     * @param int $status
     */
    protected function jsonError(string $message, $errors = [], int $status = 400): void
    {
        Helpers::jsonError($message, is_array($errors) ? $errors : [], $status);
    }

    /**
     * Respuesta JSON genérica
     *
     * @param mixed $data
     * @param int $status
     */
    protected function jsonResponse($data, int $status = 200): void
    {
        Helpers::jsonResponse($data, $status);
    }

    /**
     * Redirigir
     *
     * @param string $url
     * @param int $code
     */
    protected function redirect(string $url, int $code = 302): void
    {
        Router::redirect($url, $code);
    }

    /**
     * Redirigir con mensaje flash
     *
     * @param string $url
     * @param string $message
     * @param string $type success|error|warning|info
     */
    protected function redirectWithMessage(string $url, string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
        $this->redirect($url);
    }

    /**
     * Verificar si la petición es AJAX o API
     *
     * @return bool
     */
    protected function isAjax(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    /**
     * Obtener input de la petición
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    protected function input(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST + $_GET;
        }

        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Obtener todos los inputs POST
     * Soporta tanto application/x-www-form-urlencoded como application/json
     *
     * @return array
     */
    protected function post(): array
    {
        // Si es JSON, parsear el body
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            return is_array($data) ? $data : [];
        }

        // Si no, retornar $_POST normal
        return $_POST;
    }

    /**
     * Obtener todos los inputs GET
     *
     * @return array
     */
    protected function get(): array
    {
        return $_GET;
    }

    /**
     * Obtener archivo subido
     *
     * @param string $key
     * @return array|null
     */
    protected function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Verificar si hay un archivo
     *
     * @param string $key
     * @return bool
     */
    protected function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Verificar si el usuario está autenticado
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return $this->user !== null;
    }

    /**
     * Requerir autenticación
     *
     * @throws \Exception
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            if ($this->isAjax()) {
                $this->jsonError('No autenticado', null, 401);
            }
            else {
                $this->redirectWithMessage('/login', 'Debe iniciar sesión', 'warning');
            }
        }
    }

    /**
     * Verificar permiso del usuario
     *
     * @param string $permission
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        // Separar permiso en módulo y acción (ej: "colaboradores_ver" -> "colaboradores", "ver")
        $parts = explode('_', $permission, 2);
        $modulo = $parts[0] ?? '';
        $accion = isset($parts[1]) ? str_replace(['_'], '', $parts[1]) : 'view';

        // Mapear acciones comunes
        $accionMap = [
            'ver' => 'view',
            'crear' => 'create',
            'editar' => 'edit',
            'eliminar' => 'delete',
            'exportar' => 'export',
            'importar' => 'import'
        ];

        $accion = $accionMap[$accion] ?? $accion;

        return has_permission($this->user['tipo_usuario'] ?? null, $modulo, $accion);
    }

    /**
     * Requerir permiso
     *
     * @param string $permission
     * @throws \Exception
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            if ($this->isAjax()) {
                $this->jsonError('No autorizado', null, 403);
            }
            else {
                $this->redirectWithMessage('/', 'No tiene permisos para esta acción', 'error');
            }
        }
    }

    /**
     * Validar datos
     *
     * @param array $data
     * @param array $rules
     * @return \App\Utils\Validator
     */
    protected function validate(array $data, array $rules): \App\Utils\Validator
    {
        $validator = new \App\Utils\Validator($data);
        $validator->rules($rules);
        return $validator;
    }

    /**
     * Obtener mensaje flash y eliminarlo
     *
     * @return array|null
     */
    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Establecer mensaje flash
     *
     * @param string $message
     * @param string $type
     */
    protected function setFlash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Método 404
     */
    protected function notFound(): void
    {
        http_response_code(404);
        $this->view('errors.404', [], null);
        exit;
    }

    /**
     * Método 403
     */
    protected function forbidden(): void
    {
        http_response_code(403);
        $this->view('errors.403', [], null);
        exit;
    }

    /**
     * Obtener Database
     *
     * @return \Database
     */
    protected function db(): \Database
    {
        $db = \Database::getInstance();

        // Configurar contexto de usuario si está autenticado
        if ($this->isAuthenticated()) {
            $db->setUserContext(
                $this->user['id'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            );
        }

        return $db;
    }
}
