<?php
require_once __DIR__ . '/config/config.php';
echo "Project Root: " . str_replace('\\', '/', realpath(__DIR__)) . "\n";
echo "Script Filename: " . str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '') . "\n";
echo "Script Name: " . str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo "RouteHelper BaseUrl: " . RouteHelper::getBaseUrl() . "\n";
echo "RouteHelper URL('api/'): " . url('api/') . "\n";
