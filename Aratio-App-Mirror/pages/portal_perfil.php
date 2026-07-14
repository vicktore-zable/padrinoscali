<?php
// pages/portal_perfil.php

require_once __DIR__ . '/../mod_lider/src/bootstrap.php';
require_once __DIR__ . '/../mod_lider/config/config.php';
require_once __DIR__ . '/../mod_lider/config/database.php';

use App\Controllers\LeaderPortalController;

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__ . '/../mod_lider/src'));
}

$controller = new LeaderPortalController();
$controller->profile();
