<?php
/**
 * PHPUnit bootstrap
 * v3.0.0
 */

// Define minimal constants for testing
if (!defined('APP_ENV')) define('APP_ENV', 'testing');

// Load config
$configPath = __DIR__ . '/../config/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

// Auto-load includes
$includes = glob(__DIR__ . '/../includes/*.php');
sort($includes);
foreach ($includes as $file) {
    require_once $file;
}

// Mock getDB for testing
if (!function_exists('getDB')) {
    function getDB(): ?PDO { return null; }
}
