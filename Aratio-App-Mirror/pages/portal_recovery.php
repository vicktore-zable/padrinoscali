<?php
// pages/portal_recovery.php

require_once __DIR__ . '/../mod_lider/src/bootstrap.php';
require_once __DIR__ . '/../mod_lider/config/config.php';
require_once __DIR__ . '/../mod_lider/config/database.php';

use App\Controllers\PortalAuthController;

$controller = new PortalAuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->processRecovery();
} else {
    $controller->showRecovery();
}
