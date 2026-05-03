<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();
echo "--- Campañas en reportes_diaD ---\n";
$stmt = $db->query("SELECT id_campaña, COUNT(*) as total FROM reportes_diaD GROUP BY id_campaña");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['id_campaña']}: {$row['total']}\n";
}
