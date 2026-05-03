<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();
$stmt = $db->query("DESCRIBE colaboradores");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
