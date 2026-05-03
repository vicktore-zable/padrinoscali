<?php
/**
 * CacheManager - Sistema de caché basado en archivos
 * Reduce consultas a BD cacheando resultados frecuentes
 */

class CacheManager
{
    private $cacheDir;
    private $defaultTTL = 3600; // 1 hora por defecto
    private $enabled = true;

    /**
     * Constructor
     */
    public function __construct($cacheDir = null, $enabled = true)
    {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../cache';
        $this->enabled = $enabled;

        // Crear directorio de caché si no existe
        if ($this->enabled && !is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);

            // Crear .htaccess para proteger el directorio
            $htaccess = $this->cacheDir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all");
            }
        }
    }

    /**
     * Obtener valor del caché
     */
    public function get($key)
    {
        if (!$this->enabled) {
            return null;
        }

        $filename = $this->getCacheFilename($key);

        if (!file_exists($filename)) {
            return null;
        }

        try {
            $content = file_get_contents($filename);
            $data = json_decode($content, true);

            if ($data === null) {
                // Archivo corrupto, eliminar
                $this->delete($key);
                return null;
            }

            // Verificar si expiró
            if ($data['expires_at'] < time()) {
                $this->delete($key);
                return null;
            }

            return $data['value'];
        } catch (Exception $e) {
            error_log("CacheManager Error (get): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Guardar en caché
     */
    public function set($key, $value, $ttl = null)
    {
        if (!$this->enabled) {
            return false;
        }

        $ttl = $ttl ?? $this->defaultTTL;
        $filename = $this->getCacheFilename($key);

        try {
            $data = [
                'key' => $key,
                'value' => $value,
                'expires_at' => time() + $ttl,
                'created_at' => time()
            ];

            $content = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents($filename, $content, LOCK_EX);

            return true;
        } catch (Exception $e) {
            error_log("CacheManager Error (set): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar del caché
     */
    public function delete($key)
    {
        if (!$this->enabled) {
            return false;
        }

        $filename = $this->getCacheFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return false;
    }

    /**
     * Limpiar caché expirado
     */
    public function clearExpired()
    {
        if (!$this->enabled) {
            return 0;
        }

        $files = glob($this->cacheDir . '/*.cache');
        $cleared = 0;

        foreach ($files as $file) {
            try {
                $content = file_get_contents($file);
                $data = json_decode($content, true);

                if ($data && isset($data['expires_at']) && $data['expires_at'] < time()) {
                    unlink($file);
                    $cleared++;
                }
            } catch (Exception $e) {
                // Ignorar errores en archivos individuales
                continue;
            }
        }

        return $cleared;
    }

    /**
     * Limpiar todo el caché
     */
    public function clearAll()
    {
        if (!$this->enabled) {
            return 0;
        }

        $files = glob($this->cacheDir . '/*.cache');
        $cleared = 0;

        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }

        return $cleared;
    }

    /**
     * Obtener o cachear resultado (patrón remember)
     * 
     * Uso:
     * $colaboradores = $cache->remember('colaboradores_1', function() use ($db) {
     *     return $db->query("SELECT * FROM colaboradores WHERE campana_id = 1");
     * }, 600);
     */
    public function remember($key, $callback, $ttl = null)
    {
        // Intentar obtener del caché
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        // No está en caché, ejecutar callback
        $value = $callback();

        // Guardar en caché
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Invalidar caché por patrón
     * Ejemplo: invalidatePattern('colaboradores_*') elimina todos los cachés de colaboradores
     */
    public function invalidatePattern($pattern)
    {
        if (!$this->enabled) {
            return 0;
        }

        $files = glob($this->cacheDir . '/*.cache');
        $cleared = 0;

        foreach ($files as $file) {
            try {
                $content = file_get_contents($file);
                $data = json_decode($content, true);

                if ($data && isset($data['key'])) {
                    // Convertir patrón a regex
                    $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';

                    if (preg_match($regex, $data['key'])) {
                        unlink($file);
                        $cleared++;
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $cleared;
    }

    /**
     * Obtener estadísticas del caché
     */
    public function getStats()
    {
        if (!$this->enabled) {
            return ['enabled' => false];
        }

        $files = glob($this->cacheDir . '/*.cache');
        $totalSize = 0;
        $expired = 0;
        $active = 0;

        foreach ($files as $file) {
            $totalSize += filesize($file);

            try {
                $content = file_get_contents($file);
                $data = json_decode($content, true);

                if ($data && isset($data['expires_at'])) {
                    if ($data['expires_at'] < time()) {
                        $expired++;
                    } else {
                        $active++;
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return [
            'enabled' => true,
            'total_items' => count($files),
            'active_items' => $active,
            'expired_items' => $expired,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ];
    }

    /**
     * Obtener nombre de archivo de caché
     */
    private function getCacheFilename($key)
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
