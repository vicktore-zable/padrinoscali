<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

echo "--- Campaign 2 (Yumbo) ---\n";
$stmt = $db->query("SELECT documento, lider_directo FROM colaboradores WHERE campana_id = 2 LIMIT 5");
foreach ($stmt->fetchAll() as $row) {
    echo "Doc: {$row['documento']} | Lider: {$row['lider_directo']}\n";
}

echo "\n--- Campaign 4 (Jaimito) ---\n";
$stmt = $db->query("SELECT documento, lider_directo FROM colaboradores WHERE campana_id = 4 LIMIT 10");
foreach ($stmt->fetchAll() as $row) {
    echo "Doc: {$row['documento']} | Lider: {$row['lider_directo']}\n";
}
?>
