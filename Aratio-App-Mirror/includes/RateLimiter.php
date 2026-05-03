<?php
/**
 * RateLimiter - Control de tasa de peticiones
 * Previene sobrecarga de requests a la BD
 */

class RateLimiter
{
    private $cacheDir;
    private $maxRequests = 60; // Requests por ventana
    private $windowSeconds = 60; // Ventana de tiempo (1 minuto)
    private $enabled = true;

    /**
     * Constructor
     */
    public function __construct($cacheDir = null, $maxRequests = 60, $windowSeconds = 60)
    {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../cache/rate_limit';
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;

        // Crear directorio si no existe (con supresión de errores)
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }

        // Deshabilitar automáticamente si el directorio no es escribible
        if (!is_dir($this->cacheDir) || !is_writable($this->cacheDir)) {
            $this->enabled = false;
        }
    }

    /**
     * Verificar si el request está permitido
     * 
     * @param string $identifier Identificador único (IP, user_id, etc.)
     * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int]
     */
    public function check($identifier)
    {
        if (!$this->enabled) {
            return [
                'allowed' => true,
                'remaining' => $this->maxRequests
            ];
        }

        $filename = $this->getFilename($identifier);
        $now = time();

        // Leer datos actuales
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $data = json_decode($content, true);
        } else {
            $data = [
                'requests' => [],
                'window_start' => $now
            ];
        }

        // Limpiar requests fuera de la ventana de tiempo
        $data['requests'] = array_filter($data['requests'], function ($timestamp) use ($now) {
            return ($now - $timestamp) < $this->windowSeconds;
        });

        // Verificar límite
        $currentCount = count($data['requests']);

        if ($currentCount >= $this->maxRequests) {
            $oldestRequest = min($data['requests']);
            $retryAfter = $this->windowSeconds - ($now - $oldestRequest);

            return [
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => max(1, $retryAfter),
                'message' => "Límite de requests excedido. Intente en {$retryAfter} segundos."
            ];
        }

        // Agregar request actual
        $data['requests'][] = $now;

        // Guardar datos
        file_put_contents($filename, json_encode($data), LOCK_EX);

        return [
            'allowed' => true,
            'remaining' => $this->maxRequests - count($data['requests']),
            'reset_at' => $now + $this->windowSeconds
        ];
    }

    /**
     * Resetear límite para un identificador
     */
    public function reset($identifier)
    {
        $filename = $this->getFilename($identifier);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * Limpiar archivos antiguos
     */
    public function cleanup()
    {
        $files = glob($this->cacheDir . '/*.json');
        $cleaned = 0;
        $now = time();

        foreach ($files as $file) {
            $mtime = filemtime($file);

            // Eliminar archivos más antiguos que 2 ventanas
            if (($now - $mtime) > ($this->windowSeconds * 2)) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Obtener estadísticas
     */
    public function getStats($identifier)
    {
        $filename = $this->getFilename($identifier);

        if (!file_exists($filename)) {
            return [
                'requests_count' => 0,
                'remaining' => $this->maxRequests,
                'limit' => $this->maxRequests
            ];
        }

        $content = file_get_contents($filename);
        $data = json_decode($content, true);
        $now = time();

        // Filtrar requests válidos
        $validRequests = array_filter($data['requests'], function ($timestamp) use ($now) {
            return ($now - $timestamp) < $this->windowSeconds;
        });

        return [
            'requests_count' => count($validRequests),
            'remaining' => max(0, $this->maxRequests - count($validRequests)),
            'limit' => $this->maxRequests,
            'window_seconds' => $this->windowSeconds
        ];
    }

    /**
     * Obtener nombre de archivo
     */
    private function getFilename($identifier)
    {
        return $this->cacheDir . '/' . md5($identifier) . '.json';
    }

    /**
     * Habilitar/deshabilitar rate limiting
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}
