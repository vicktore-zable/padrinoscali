<?php
// pages/portal_change_password.php

require_once __DIR__ . '/../mod_lider/src/bootstrap.php';
require_once __DIR__ . '/../mod_lider/config/config.php';
require_once __DIR__ . '/../mod_lider/config/database.php';

use App\Controllers\PortalAuthController;

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__ . '/../mod_lider/src'));
}

$controller = new PortalAuthController();

// Si es POST → procesar cambio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->changePassword();
} else {
    // GET → mostrar formulario
    if (!$controller->isAuthenticated()) {
        header('Location: ?page=portal_landing');
        exit;
    }
    $controller->view('portal.change-password', ['title' => 'Cambiar Contraseña'], 'portal');
}
