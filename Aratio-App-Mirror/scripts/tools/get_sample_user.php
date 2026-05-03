<?php
require_once __DIR__ . '/mod_colab/config/config.php';
require_once __DIR__ . '/mod_colab/config/database.php';

$db = Database::getInstance();

try {
    $user = $db->fetchOne("SELECT * FROM usuarios LIMIT 1");
    if ($user) {
        $keys = array_keys($user);
        echo "COLUMNS: " . implode(", ", $keys) . "\n\n";
        echo "SAMPLE DATA:\n";
        print_r($user);
    } else {
        echo "No records in 'usuarios' table.\n";
        // If no records, let's at least show DESCRIBE
        $cols = $db->fetchAll("DESCRIBE usuarios");
        echo "DESCRIBE:\n";
        print_r($cols);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
