<?php
require_once __DIR__ . '/../../config/config.php';
$db = getDB();
$stmt = $db->query("DESCRIBE colaboradores");
foreach ($stmt->fetchAll() as $row) {
    echo "Field: {$row['Field']} - Type: {$row['Type']}\n";
}
?>
