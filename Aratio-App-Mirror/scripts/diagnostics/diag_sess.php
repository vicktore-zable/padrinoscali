<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../../config/config.php';
session_start();
echo "--- ROOT SESSION DIAG ---\n";
echo "Session Name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Path: " . session_save_path() . "\n";
echo "Session Data: ";
print_r($_SESSION);
?>
