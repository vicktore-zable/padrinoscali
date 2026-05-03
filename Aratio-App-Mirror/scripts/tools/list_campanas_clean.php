<?php
require 'config/config.php';
$db = getDB();
foreach($db->query('SELECT id, nombre, estado FROM campanas')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo $r['id'] . ' | ' . $r['nombre'] . ' | ' . $r['estado'] . "\n";
}
