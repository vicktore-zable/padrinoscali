<?php
require_once __DIR__ . '/../mod_lider/src/bootstrap.php';
require_once __DIR__ . '/../mod_lider/config/config.php';
require_once __DIR__ . '/../mod_lider/config/database.php';

use App\Controllers\VoluntariadoController;

if (!defined('APP_PATH')) {
    define('APP_PATH', realpath(__DIR__ . '/../mod_lider/src'));
}

if (isset($_GET['action']) && $_GET['action'] === 'avanzar_fase') {
    $controller = new VoluntariadoController();
    $controller->avanzarFase();
    exit;
}

$controller = new VoluntariadoController();
$controller->misVoluntarios();
