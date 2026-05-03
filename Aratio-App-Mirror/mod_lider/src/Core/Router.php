<?php
/**
 * Clase Router
 * Sistema de enrutamiento de la aplicación
 *
 * @package App\Core
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Core;

class Router {
    /**
     * Rutas registradas
     * @var array
     */
    private array $routes = [];

    /**
     * Middleware global
     * @var array
     */
    private array $globalMiddleware = [];

    /**
     * Prefijo actual de grupo
     * @var string
     */
    private string $groupPrefix = '';

    /**
     * Middleware actual de grupo
     * @var array
     */
    private array $groupMiddleware = [];

    /**
     * Registrar ruta GET
     *
     * @param string $path
     * @param mixed $handler
     * @param array $middleware
     */
    public function get(string $path, $handler, array $middleware = []): void {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Registrar ruta POST
     *
     * @param string $path
     * @param mixed $handler
     * @param array $middleware
     */
    public function post(string $path, $handler, array $middleware = []): void {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Registrar ruta PUT
     *
     * @param string $path
     * @param mixed $handler
     * @param array $middleware
     */
    public function put(string $path, $handler, array $middleware = []): void {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Registrar ruta DELETE
     *
     * @param string $path
     * @param mixed $handler
     * @param array $middleware
     */
    public function delete(string $path, $handler, array $middleware = []): void {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Registrar múltiples métodos para una ruta
     *
     * @param array $methods
     * @param string $path
     * @param mixed $handler
     * @param array $middleware
     */
    public function match(array $methods, string $path, $handler, array $middleware = []): void {
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler, $middleware);
        }
    }

    /**
     * Agregar ruta
     *
     * @param string $method
     * @param string $path
     * @param mixed $handler
     * @param array $middleware
     */
    private function addRoute(string $method, string $path, $handler, array $middleware = []): void {
        $path = $this->groupPrefix . $path;
        $middleware = array_merge($this->groupMiddleware, $middleware);

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
            'pattern' => $this->convertToPattern($path),
        ];
    }

    /**
     * Convertir path a patrón regex
     *
     * @param string $path
     * @return string
     */
    private function convertToPattern(string $path): string {
        // Convertir {param} a (?P<param>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Crear grupo de rutas
     *
     * @param string $prefix
     * @param callable $callback
     * @param array $middleware
     */
    public function getRoutes(): array {
        return $this->routes;
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = $previousPrefix . $prefix;
        $this->groupMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * Agregar middleware global
     *
     * @param string $middleware
     */
    public function addMiddleware(string $middleware): void {
        $this->globalMiddleware[] = $middleware;
    }

    /**
     * Despachar ruta
     *
     * @param string $uri
     * @param string $method
     */
    public function dispatch(string $uri, string $method): void {
        $method = strtoupper($method);
        // Si es HEAD, tratarlo como GET para efectos de matching
        $matchMethod = ($method === 'HEAD') ? 'GET' : $method;

        // Buscar ruta coincidente
        foreach ($this->routes as $route) {
            if ($route['method'] === $matchMethod && preg_match($route['pattern'], $uri, $matches)) {
                // Extraer parámetros de la URL
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Ejecutar middleware global
                foreach ($this->globalMiddleware as $middlewareClass) {
                    $this->executeMiddleware($middlewareClass);
                }

                // Ejecutar middleware de ruta
                foreach ($route['middleware'] as $middlewareClass) {
                    $this->executeMiddleware($middlewareClass);
                }

                // Ejecutar handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // No se encontró la ruta - 404
        $this->notFound();
    }

    /**
     * Ejecutar middleware
     *
     * @param string|object $middlewareClass
     */
    private function executeMiddleware(string|object $middlewareClass): void {
        // Si ya es una instancia, usarla directamente
        if (is_object($middlewareClass)) {
            $middleware = $middlewareClass;
        } else {
            // Si es string, verificar que la clase exista e instanciarla
            if (!class_exists($middlewareClass)) {
                throw new \Exception("Middleware no encontrado: {$middlewareClass}");
            }
            $middleware = new $middlewareClass();
        }

        // Verificar que tenga el método handle
        if (!method_exists($middleware, 'handle')) {
            $className = is_object($middlewareClass) ? get_class($middlewareClass) : $middlewareClass;
            throw new \Exception("Middleware debe tener método handle(): {$className}");
        }

        $middleware->handle();
    }

    /**
     * Ejecutar handler
     *
     * @param mixed $handler
     * @param array $params
     */
    private function executeHandler($handler, array $params = []): void {
        if (is_callable($handler)) {
            // Es una función anónima
            call_user_func_array($handler, $params);
        } elseif (is_string($handler) && strpos($handler, '@') !== false) {
            // Es un string "Controller@method"
            [$controllerClass, $method] = explode('@', $handler);

            // Agregar namespace si no lo tiene
            if (strpos($controllerClass, '\\') === false) {
                $controllerClass = "App\\Controllers\\{$controllerClass}";
            }

            if (!class_exists($controllerClass)) {
                throw new \Exception("Controlador no encontrado: {$controllerClass}");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $method)) {
                throw new \Exception("Método no encontrado: {$controllerClass}@{$method}");
            }

            call_user_func_array([$controller, $method], $params);
        } else {
            throw new \Exception("Handler inválido");
        }
    }

    /**
     * Página no encontrada
     */
    private function notFound(): void {
        http_response_code(404);

        if (file_exists(APP_PATH . '/Views/errors/404.php')) {
            require APP_PATH . '/Views/errors/404.php';
        } else {
            echo "<h1>404 - Página No Encontrada</h1>";
            echo "<p>La página que buscas no existe.</p>";
        }
        exit;
    }

    /**
     * Redirigir a una URL
     *
     * @param string $url
     * @param int $code
     */
    public static function redirect(string $url, int $code = 302): void {
        header("Location: $url", true, $code);
        exit;
    }

    /**
     * Obtener URL base
     *
     * @return string
     */
    public static function baseUrl(): string {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return "{$protocol}://{$host}/colaboradores";
    }

    /**
     * Generar URL
     *
     * @param string $path
     * @return string
     */
    public static function url(string $path): string {
        return self::baseUrl() . '/' . ltrim($path, '/');
    }
}
