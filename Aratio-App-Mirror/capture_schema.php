<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$db = Database::getInstance();

try {
    $cols = $db->fetchAll("DESCRIBE usuarios");
    $output = "Schema for 'usuarios' table:\n";
    foreach ($cols as $col) {
        $output .= print_r($col, true) . "\n";
    }
    file_put_contents('usuarios_schema_clean.txt', $output);
} catch (Exception $e) {
    file_put_contents('usuarios_schema_clean.txt', "Error: " . $e->getMessage());
}
