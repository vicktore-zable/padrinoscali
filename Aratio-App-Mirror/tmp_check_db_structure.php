<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();
echo "--- Estructura de colaboradores (perfil) ---\n";
$stmt = $db->query("SHOW COLUMNS FROM colaboradores LIKE 'perfil'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);

echo "\n--- Estructura de reportes_diaD ---\n";
$stmt = $db->query("SHOW COLUMNS FROM reportes_diaD");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "{$r['Field']} - {$r['Type']}\n";
}

echo "\n--- Foreign Keys de reportes_diaD ---\n";
$stmt = $db->query("
    SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'reportes_diaD' AND TABLE_SCHEMA = DATABASE()
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "{$r['COLUMN_NAME']} -> {$r['REFERENCED_TABLE_NAME']}({$r['REFERENCED_COLUMN_NAME']})\n";
}
