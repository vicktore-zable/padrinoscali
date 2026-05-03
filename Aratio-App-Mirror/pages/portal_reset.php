<?php
// pages/portal_reset.php

require_once __DIR__ . '/../mod_lider/src/bootstrap.php';
require_once __DIR__ . '/../mod_lider/config/config.php';
require_once __DIR__ . '/../mod_lider/config/database.php';

use App\Controllers\PortalAuthController;

$controller = new PortalAuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->processReset();
} else {
    $controller->showReset();
}
