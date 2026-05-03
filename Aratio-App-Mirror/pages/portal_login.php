<?php
// pages/portal_login.php

require_once __DIR__ . '/../mod_lider/src/bootstrap.php';
// Cargar configuracion del modulo
require_once __DIR__ . '/../mod_lider/config/config.php';
require_once __DIR__ . '/../mod_lider/config/database.php';


use App\Controllers\PortalAuthController;

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__ . '/../mod_lider/src'));
}

$controller = new PortalAuthController();
$controller->showLogin();
