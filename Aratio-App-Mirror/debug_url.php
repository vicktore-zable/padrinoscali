<?php
require_once __DIR__ . '/config/config.php';
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "getBaseUrl(): " . RouteHelper::getBaseUrl() . "\n";
echo "url('api/'): " . url('api/') . "\n";
