<?php
/**
 * pages/portal_landing.php
 * Punto de entrada modular para la landing del portal de líderes
 */

require_once __DIR__ . '/../mod_lider/src/bootstrap.php';
require_once __DIR__ . '/../mod_lider/config/config.php';
require_once __DIR__ . '/../mod_lider/config/database.php';

use App\Controllers\PortalAuthController;

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__ . '/../mod_lider/src'));
}

$controller = new PortalAuthController();
$controller->landing();
