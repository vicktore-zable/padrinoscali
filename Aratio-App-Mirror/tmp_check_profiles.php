<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();
echo "--- Perfiles en colaboradores ---\n";
$stmt = $db->query("SELECT perfil, COUNT(*) as total FROM colaboradores GROUP BY perfil");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['perfil']}: {$row['total']}\n";
}

echo "\n--- Perfiles de Líderes según buscar_lider filter ---\n";
$stmt = $db->query("SELECT perfil, COUNT(*) as total FROM colaboradores WHERE (perfil IN ('Lider Comunitario', 'Lider / Coordinador')) GROUP BY perfil");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['perfil']}: {$row['total']}\n";
}
