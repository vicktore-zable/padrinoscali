<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$db = Database::getInstance();

try {
    $cols = $db->fetchAll("DESCRIBE usuarios");
    echo "Columns in 'usuarios' table:\n";
    foreach ($cols as $col) {
        $extra = ($col['Null'] == 'NO' ? 'NOT NULL' : 'NULL');
        if ($col['Default'] !== null) $extra .= " DEFAULT '{$col['Default']}'";
        echo "- {$col['Field']} ({$col['Type']}) $extra\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
