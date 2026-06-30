<?php

class Logger
{
    const DEBUG = 0;
    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;
    const CRITICAL = 4;

    private static ?Logger $instance = null;
    private string $logDir;
    private string $minLevel;
    private array $channels = [];

    public function __construct(string $logDir = null, string $minLevel = null)
    {
        $this->logDir = $logDir ?? (defined('UPLOAD_PATH') ? dirname(UPLOAD_PATH) . '/logs' : __DIR__ . '/../storage/logs');
        $this->minLevel = $minLevel ?? (defined('APP_ENV') && APP_ENV === 'development' ? self::DEBUG : self::WARNING);
        if (!is_dir($this->logDir)) @mkdir($this->logDir, 0775, true);
    }

    public static function getInstance(): self
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function channel(string $name): self
    {
        if (!isset($this->channels[$name])) {
            $clone = clone $this;
            $clone->channels[] = $name;
            $this->channels[$name] = $clone;
        }
        return $this->channels[$name];
    }

    public function debug(string $message, array $context = []): void { $this->log(self::DEBUG, $message, $context); }
    public function info(string $message, array $context = []): void { $this->log(self::INFO, $message, $context); }
    public function warning(string $message, array $context = []): void { $this->log(self::WARNING, $message, $context); }
    public function error(string $message, array $context = []): void { $this->log(self::ERROR, $message, $context); }
    public function critical(string $message, array $context = []): void { $this->log(self::CRITICAL, $message, $context); }

    public function log(int $level, string $message, array $context = []): void
    {
        if ($level < $this->minLevel) return;

        $levels = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];
        $levelName = $levels[$level] ?? 'UNKNOWN';
        $channel = !empty($this->channels) ? '[' . implode('.', $this->channels) . '] ' : '';
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($trace[1]) ? $trace[1]['file'] . ':' . $trace[1]['line'] : 'unknown';

        $line = sprintf(
            "[%s] %s: %s%s %s %s\n",
            date('Y-m-d H:i:s'),
            $levelName,
            $channel,
            $message,
            $contextStr,
            $caller
        );

        $filename = $this->logDir . '/aratio-' . date('Y-m-d') . '.log';
        file_put_contents($filename, $line, FILE_APPEND | LOCK_EX);

        // Also send to PHP error log for critical errors
        if ($level >= self::ERROR) {
            error_log("[Aratio] $levelName: $message");
        }
    }

    public static function rotate(int $maxDays = 30): int
    {
        $logDir = self::getInstance()->logDir;
        $deleted = 0;
        foreach (glob($logDir . '/aratio-*.log') as $file) {
            if (filemtime($file) < strtotime("-{$maxDays} days")) {
                unlink($file);
                $deleted++;
            }
        }
        return $deleted;
    }
}
