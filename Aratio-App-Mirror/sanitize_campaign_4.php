<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

echo "Sanitizing Campaign 4 data...\n";

// Update all records to have user_id = 1 and clean names
$stmt = $db->prepare("UPDATE colaboradores SET 
    usuario_registro_id = 1,
    nombres = TRIM(nombres),
    apellidos = TRIM(apellidos),
    perfil = TRIM(perfil),
    lider_directo = TRIM(lider_directo)
WHERE campana_id = 4");
$stmt->execute();

echo "Updated " . $stmt->rowCount() . " rows.\n";

// Check for any non-UTF8 again just in case
$stmt = $db->query("SELECT id, nombres, apellidos FROM colaboradores WHERE campana_id = 4");
foreach ($stmt->fetchAll() as $row) {
    if (!mb_check_encoding($row['nombres'], 'UTF-8') || !mb_check_encoding($row['apellidos'], 'UTF-8')) {
        echo "FIXING ID {$row['id']} encoding...\n";
        $cleanN = mb_convert_encoding($row['nombres'], 'UTF-8', 'ISO-8859-1');
        $cleanA = mb_convert_encoding($row['apellidos'], 'UTF-8', 'ISO-8859-1');
        $upd = $db->prepare("UPDATE colaboradores SET nombres = ?, apellidos = ? WHERE id = ?");
        $upd->execute([$cleanN, $cleanA, $row['id']]);
    }
}

echo "Sanitization finished.\n";
?>
