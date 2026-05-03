<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../config/config.php';
// Forzar el mismo nombre de sesión que el root si hay duda
if (defined('SESSION_NAME')) session_name(SESSION_NAME);
session_start();
echo "--- MOD_COLAB SESSION DIAG ---\n";
echo "Session Name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Path: " . session_save_path() . "\n";
echo "Session Data: ";
print_r($_SESSION);
?>
