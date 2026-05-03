<?php
// pages/portal_dashboard.php

require_once __DIR__ . '/../mod_lider/src/bootstrap.php';
// Cargar configuracion del modulo
require_once __DIR__ . '/../mod_lider/config/config.php';
require_once __DIR__ . '/../mod_lider/config/database.php';


use App\Controllers\LeaderPortalController;

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__ . '/../mod_lider/src'));
}

$controller = new LeaderPortalController();

// Manejar acciones vía GET (como exportación)
$action = $_GET['action'] ?? null;
if ($action === 'export_team') {
    $controller->exportTeamCSV();
} else {
    $controller->index();
}
