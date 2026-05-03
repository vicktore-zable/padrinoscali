<?php
require 'config/config.php';
$db = getDB();
$stmt = $db->query('DESCRIBE colaboradores');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
