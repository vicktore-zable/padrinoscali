<?php
/**
 * Clase Logger
 * Sistema de logging para aplicación y seguridad
 *
 * @package App\Utils
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Utils;

class Logger {
    /**
     * Niveles de log
     */
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';

    /**
     * Archivo de log por defecto
     * @var string
     */
    private static string $defaultLogFile;

    /**
     * Archivo de log de seguridad
     * @var string
     */
    private static string $securityLogFile;

    /**
     * Inicializar logger
     */
    public static function init(): void {
        self::$defaultLogFile = LOG_CONFIG['file'];
        self::$securityLogFile = LOG_CONFIG['security_file'];

        // Crear directorio si no existe
        $logDir = dirname(self::$defaultLogFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log de debug
     *
     * @param string $message
     * @param array $context
     */
    public static function debug(string $message, array $context = []): void {
        if (APP_DEBUG) {
            self::log(self::DEBUG, $message, $context);
        }
    }

    /**
     * Log de información
     *
     * @param string $message
     * @param array $context
     */
    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }

    /**
     * Log de advertencia
     *
     * @param string $message
     * @param array $context
     */
    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }

    /**
     * Log de error
     *
     * @param string $message
     * @param array $context
     */
    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }

    /**
     * Log crítico
     *
     * @param string $message
     * @param array $context
     */
    public static function critical(string $message, array $context = []): void {
        self::log(self::CRITICAL, $message, $context);
    }

    /**
     * Log de seguridad
     *
     * @param string $message
     * @param array $context
     */
    public static function security(string $message, array $context = []): void {
        self::init();

        $context = array_merge($context, [
            'ip' => Security::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? '',
        ]);

        self::writeLog(self::$securityLogFile, 'SECURITY', $message, $context);
    }

    /**
     * Log de autenticación
     *
     * @param string $action 'login' | 'logout' | 'failed'
     * @param string $usuario
     * @param bool $success
     */
    public static function auth(string $action, string $usuario, bool $success = true): void {
        $message = sprintf(
            "%s %s para usuario: %s",
            ucfirst($action),
            $success ? 'exitoso' : 'fallido',
            $usuario
        );

        self::security($message, [
            'action' => $action,
            'usuario' => $usuario,
            'success' => $success
        ]);
    }

    /**
     * Log principal
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    private static function log(string $level, string $message, array $context = []): void {
        self::init();

        // Verificar nivel de log configurado
        $configuredLevel = strtoupper(LOG_CONFIG['level']);
        $levels = [
            'DEBUG' => 0,
            'INFO' => 1,
            'WARNING' => 2,
            'ERROR' => 3,
            'CRITICAL' => 4
        ];

        if ($levels[$level] < $levels[$configuredLevel]) {
            return;
        }

        self::writeLog(self::$defaultLogFile, $level, $message, $context);
    }

    /**
     * Escribir en archivo de log
     *
     * @param string $file
     * @param string $level
     * @param string $message
     * @param array $context
     */
    private static function writeLog(string $file, string $level, string $message, array $context = []): void {
        // Formato: [2025-01-15 10:30:45] [LEVEL] Message {context}
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';

        $logLine = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;

        // Escribir de forma segura
        file_put_contents($file, $logLine, FILE_APPEND | LOCK_EX);

        // Rotar log si es muy grande
        self::rotateIfNeeded($file);
    }

    /**
     * Rotar archivo de log si excede el tamaño máximo
     *
     * @param string $file
     */
    private static function rotateIfNeeded(string $file): void {
        if (!file_exists($file)) {
            return;
        }

        $maxSize = LOG_CONFIG['max_size'];

        if (filesize($file) > $maxSize) {
            $timestamp = date('Y-m-d_H-i-s');
            $newName = $file . '.' . $timestamp;
            rename($file, $newName);

            // Comprimir el archivo antiguo
            if (function_exists('gzopen')) {
                $gz = gzopen($newName . '.gz', 'w9');
                gzwrite($gz, file_get_contents($newName));
                gzclose($gz);
                unlink($newName);
            }
        }
    }

    /**
     * Limpiar logs antiguos
     *
     * @param int $days Días a mantener
     */
    public static function cleanOldLogs(int $days = 30): void {
        $logDir = dirname(self::$defaultLogFile);
        $cutoffTime = time() - ($days * 86400);

        $files = glob($logDir . '/*.log.*');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }

    /**
     * Obtener últimas líneas del log
     *
     * @param string $logType 'app' | 'security'
     * @param int $lines
     * @return array
     */
    public static function tail(string $logType = 'app', int $lines = 100): array {
        self::init();

        $file = $logType === 'security' ? self::$securityLogFile : self::$defaultLogFile;

        if (!file_exists($file)) {
            return [];
        }

        $content = file($file);
        return array_slice($content, -$lines);
    }

    /**
     * Buscar en logs
     *
     * @param string $query
     * @param string $logType 'app' | 'security'
     * @param int $limit
     * @return array
     */
    public static function search(string $query, string $logType = 'app', int $limit = 100): array {
        self::init();

        $file = $logType === 'security' ? self::$securityLogFile : self::$defaultLogFile;

        if (!file_exists($file)) {
            return [];
        }

        $results = [];
        $handle = fopen($file, 'r');

        while (($line = fgets($handle)) !== false) {
            if (stripos($line, $query) !== false) {
                $results[] = $line;

                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        fclose($handle);

        return $results;
    }

    /**
     * Filtrar logs por nivel
     *
     * @param string $level
     * @param int $limit
     * @return array
     */
    public static function filterByLevel(string $level, int $limit = 100): array {
        return self::search("[$level]", 'app', $limit);
    }

    /**
     * Filtrar logs por fecha
     *
     * @param string $date Formato: Y-m-d
     * @param string $logType
     * @return array
     */
    public static function filterByDate(string $date, string $logType = 'app'): array {
        return self::search($date, $logType, PHP_INT_MAX);
    }

    /**
     * Obtener estadísticas de logs
     *
     * @param string $logType
     * @return array
     */
    public static function getStats(string $logType = 'app'): array {
        self::init();

        $file = $logType === 'security' ? self::$securityLogFile : self::$defaultLogFile;

        if (!file_exists($file)) {
            return [
                'total_lines' => 0,
                'size' => 0,
                'last_modified' => null,
                'levels' => []
            ];
        }

        $stats = [
            'total_lines' => 0,
            'size' => filesize($file),
            'size_formatted' => self::formatBytes(filesize($file)),
            'last_modified' => date('Y-m-d H:i:s', filemtime($file)),
            'levels' => [
                'DEBUG' => 0,
                'INFO' => 0,
                'WARNING' => 0,
                'ERROR' => 0,
                'CRITICAL' => 0,
                'SECURITY' => 0
            ]
        ];

        $handle = fopen($file, 'r');

        while (($line = fgets($handle)) !== false) {
            $stats['total_lines']++;

            foreach ($stats['levels'] as $level => $count) {
                if (strpos($line, "[$level]") !== false) {
                    $stats['levels'][$level]++;
                }
            }
        }

        fclose($handle);

        return $stats;
    }

    /**
     * Formatear bytes a formato legible
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private static function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Exportar logs a archivo
     *
     * @param string $outputFile
     * @param string $logType
     * @param string|null $startDate
     * @param string|null $endDate
     * @return bool
     */
    public static function export(
        string $outputFile,
        string $logType = 'app',
        ?string $startDate = null,
        ?string $endDate = null
    ): bool {
        self::init();

        $file = $logType === 'security' ? self::$securityLogFile : self::$defaultLogFile;

        if (!file_exists($file)) {
            return false;
        }

        $handle = fopen($file, 'r');
        $output = fopen($outputFile, 'w');

        while (($line = fgets($handle)) !== false) {
            // Filtrar por fechas si se especifican
            if ($startDate || $endDate) {
                preg_match('/\[([\d-]+\s[\d:]+)\]/', $line, $matches);
                if (isset($matches[1])) {
                    $lineDate = $matches[1];

                    if ($startDate && $lineDate < $startDate) continue;
                    if ($endDate && $lineDate > $endDate) continue;
                }
            }

            fwrite($output, $line);
        }

        fclose($handle);
        fclose($output);

        return true;
    }

    /**
     * Log de excepción
     *
     * @param \Throwable $e
     * @param array $context
     */
    public static function exception(\Throwable $e, array $context = []): void {
        $message = sprintf(
            "%s: %s in %s:%d",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        $context['exception'] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];

        self::error($message, $context);
    }

    /**
     * Log de query SQL (solo en debug)
     *
     * @param string $query
     * @param array $params
     * @param float $executionTime
     */
    public static function query(string $query, array $params = [], float $executionTime = 0): void {
        if (APP_DEBUG) {
            $message = "SQL Query executed in {$executionTime}s: $query";
            self::debug($message, ['params' => $params]);
        }
    }

    /**
     * Log de solicitud HTTP
     *
     * @param string $method
     * @param string $uri
     * @param int $statusCode
     * @param float $executionTime
     */
    public static function request(
        string $method,
        string $uri,
        int $statusCode,
        float $executionTime
    ): void {
        $message = sprintf(
            "%s %s - %d (%ss)",
            $method,
            $uri,
            $statusCode,
            number_format($executionTime, 3)
        );

        self::info($message, [
            'method' => $method,
            'uri' => $uri,
            'status' => $statusCode,
            'time' => $executionTime,
            'ip' => Security::getClientIp()
        ]);
    }
}

// Inicializar logger
Logger::init();
