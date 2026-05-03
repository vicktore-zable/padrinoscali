<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$db = Database::getInstance();

try {
    $user = $db->fetchOne("SELECT * FROM usuarios LIMIT 1");
    if ($user) {
        foreach (array_keys($user) as $key) {
            echo "- $key\n";
        }
    } else {
        echo "No records.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
