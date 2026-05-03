<?php
/**
 * Entry point para mod_elecciones (Público)
 */
define('MOD_PATH', __DIR__ . '/..');
$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
    default:
        require_once MOD_PATH . '/src/Views/dashboard.php';
        break;
}
