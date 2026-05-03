<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();
$tables = ['donaciones', 'eventos', 'acciones_comunitarias', 'compromisos', 'usuarios', 'campanas'];
foreach ($tables as $t) {
    try {
        $count = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "$t: $count registros\n";
    } catch (Exception $e) {
        echo "$t: ERROR - " . $e->getMessage() . "\n";
    }
}
